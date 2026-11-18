<?php
// End-to-end tests against a real PHP built-in server and a throwaway SQLite file.
// Verifies what service tests cannot: cookies, session-ID regeneration, logout invalidation,
// CSRF, HTTP status codes, role/ownership guards on pages, inactivity timeout.  UM-02..UM-10.
// Run from the project root:  php tests/http-tests.php   (needs the PHP curl extension)
require_once __DIR__ . '/test-helpers.php';
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/services/requestService.php';
require_once dirname(__DIR__) . '/services/userService.php';
if (!function_exists('curl_init')) { abortTests('PHP curl extension is required for http-tests.php'); }

$root = dirname(__DIR__);
$dbFile = tempnam(sys_get_temp_dir(), 'pth-test-') . '.db';
$pdo = openDatabase($dbFile);
$ddl = [];
foreach (explode("\n", file_get_contents($root . '/database/setup-database.sql')) as $l) { if (stripos(ltrim($l), 'INSERT') !== 0) { $ddl[] = $l; } }
$pdo->exec(implode("\n", $ddl));

$adminId = createAdministratorAccount($pdo, 'Casey Admin', 'admin@example.edu', 'AdminPass123');
$alexId = registerUser($pdo, regInput('Alex Morgan', 'alex@example.edu', 'Student', 'AlexPass123'));
$priyaId = registerUser($pdo, regInput('Priya Shah', 'priya@example.edu', 'Student', 'PriyaPass123'));
$taylorId = registerUser($pdo, regInput('Taylor Lee', 'taylor@example.edu', 'Tutor', 'TaylorPass1'));
$idleId = registerUser($pdo, regInput('Idle User', 'idle@example.edu', 'Tutor', 'IdlePass123'));
$alexReq = submitRequest($pdo, requestInput('Alex Morgan', 'alex@example.edu'));
$priyaReq = submitRequest($pdo, requestInput('Priya Shah', 'priya@example.edu'));

// ---- start server (own process, own DB, short timeout) ----
$sock = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
$port = (int)substr(strrchr(stream_socket_get_name($sock, false), ':'), 1);
fclose($sock);
$env = array_merge($_ENV, ['PTH_DB_PATH' => $dbFile, 'PTH_SESSION_TIMEOUT' => '3', 'PATH' => getenv('PATH')]);
$proc = proc_open([PHP_BINARY, '-S', "127.0.0.1:$port", 'php/router.php'], [1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']], $pipes, $root, $env);
register_shutdown_function(function () use ($proc, $dbFile) { if (is_resource($proc)) { proc_terminate($proc); proc_close($proc); } @unlink($dbFile); @unlink(substr($dbFile, 0, -3)); });
$base = "http://127.0.0.1:$port";
$up = false;
for ($i = 0; $i < 50 && !$up; $i++) { $c = @fsockopen('127.0.0.1', $port, $e, $s, 0.2); if ($c) { fclose($c); $up = true; } else { usleep(100000); } }
if (!$up) { abortTests('PHP server did not start'); }

class Client {
    public $cookies = [];
    function req($method, $path, $fields = null) {
        $ch = curl_init($GLOBALS['base'] . $path);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_CUSTOMREQUEST => $method]);
        if ($fields !== null) { curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields)); }
        if ($this->cookies) { $p = []; foreach ($this->cookies as $k => $v) { $p[] = "$k=$v"; } curl_setopt($ch, CURLOPT_COOKIE, implode('; ', $p)); }
        $raw = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hsize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        $head = substr($raw, 0, $hsize);
        $body = substr($raw, $hsize);
        $setCookies = [];
        foreach (explode("\r\n", $head) as $line) {
            if (stripos($line, 'Set-Cookie:') === 0) {
                $setCookies[] = trim(substr($line, 11));
                $pair = explode(';', trim(substr($line, 11)))[0];
                list($k, $v) = array_pad(explode('=', $pair, 2), 2, '');
                if ($v === 'deleted' || $v === '') { unset($this->cookies[$k]); } else { $this->cookies[$k] = $v; }
            }
        }
        $loc = preg_match('/^Location:\s*(.+)$/mi', $head, $m) ? trim($m[1]) : '';
        return ['code' => $code, 'body' => $body, 'location' => $loc, 'setcookies' => $setCookies];
    }
    function get($p) { return $this->req('GET', $p); }
    function post($p, $f) { return $this->req('POST', $p, $f); }
    // Fetch a page that renders a form and return its CSRF token.
    function token($path) { $r = $this->get($path); return preg_match('/name="csrf_token" value="([a-f0-9]+)"/', $r['body'], $m) ? $m[1] : ''; }
    function login($email, $pw) { $t = $this->token('/php/login.php'); return $this->post('/php/login.php', ['csrf_token' => $t, 'email' => $email, 'password' => $pw]); }
}

echo "== UM-09 Log In ==" . PHP_EOL;
$c = new Client();
$page = $c->get('/php/login.php');
check($page['code'] === 200 && strpos($page['body'], 'name="email"') !== false && strpos($page['body'], 'name="password"') !== false, 'UM-09-H01 Login page shows email and password fields');
$sidBefore = isset($c->cookies['PHPSESSID']) ? $c->cookies['PHPSESSID'] : '';
$cookieHdr = implode(' | ', $page['setcookies']);
check($sidBefore !== '' && stripos($cookieHdr, 'HttpOnly') !== false && stripos($cookieHdr, 'SameSite=Lax') !== false, 'UM-09-H02 Session cookie is HttpOnly and SameSite=Lax');
$tok = $c->token('/php/login.php');
$noCsrf = $c->post('/php/login.php', ['email' => 'alex@example.edu', 'password' => 'AlexPass123']);
check($noCsrf['code'] === 403, 'UM-09-H03 Login POST without a CSRF token is refused');
$bad = $c->post('/php/login.php', ['csrf_token' => $tok, 'email' => 'alex@example.edu', 'password' => 'nope']);
check($bad['code'] === 401 && strpos($bad['body'], 'Invalid email or password.') !== false, 'UM-09-H04 Wrong password shows the generic error');
$unknown = $c->post('/php/login.php', ['csrf_token' => $tok, 'email' => 'ghost@example.edu', 'password' => 'nope']);
check($unknown['code'] === 401 && strpos($unknown['body'], 'Invalid email or password.') !== false, 'UM-09-H05 Unknown email shows the same generic error');
check($c->get('/php/profile.php')['code'] === 302, 'UM-09-H06 Failed logins do not create an authenticated session');
$ok = $c->post('/php/login.php', ['csrf_token' => $tok, 'email' => 'alex@example.edu', 'password' => 'AlexPass123']);
$sidAfter = isset($c->cookies['PHPSESSID']) ? $c->cookies['PHPSESSID'] : '';
check($ok['code'] === 303 && $ok['location'] === 'dashboard.php', 'UM-09-H07 Valid login redirects to the dashboard');
check($sidAfter !== '' && $sidAfter !== $sidBefore, 'UM-09-H08 Session ID is regenerated at login (fixation defense)');
$dash = $c->get('/php/dashboard.php');
check($dash['code'] === 200 && strpos($dash['body'], 'Welcome, Alex Morgan') !== false, 'UM-09-H09 Dashboard shows the signed-in user');
$old = new Client(); $old->cookies['PHPSESSID'] = $sidBefore;
check($old->get('/php/dashboard.php')['code'] === 302, 'UM-09-H10 The pre-login session ID grants no access');
$pdo->exec("UPDATE users SET status='Inactive' WHERE user_id=$priyaId");
$ci = new Client(); $inact = $ci->login('priya@example.edu', 'PriyaPass123');
check($inact['code'] === 401 && strpos($inact['body'], 'Invalid email or password.') !== false && $ci->get('/php/dashboard.php')['code'] === 302, 'UM-09-H11 Inactive account is refused with the same generic message');
$pdo->exec("UPDATE users SET status='Active' WHERE user_id=$priyaId");

echo "== UM-02 View Own Account ==" . PHP_EOL;
$prof = $c->get('/php/profile.php');
check($prof['code'] === 200 && strpos($prof['body'], 'alex@example.edu') !== false && strpos($prof['body'], 'Alex Morgan') !== false && strpos($prof['body'], 'Student') !== false && strpos($prof['body'], 'Active') !== false, 'UM-02-H01 Profile shows own name, email, role and status');
$tamper = $c->get('/php/profile.php?user_id=' . $priyaId);
check(strpos($tamper['body'], 'priya@example.edu') === false && strpos($tamper['body'], 'alex@example.edu') !== false, 'UM-02-H02 A user_id parameter cannot reveal another user\'s profile');
check(strpos($prof['body'], '$2y$') === false && stripos($prof['body'], 'password_hash') === false, 'UM-02-H03 No password hash appears on the profile page');
$anon = new Client();
$a = $anon->get('/php/profile.php');
check($a['code'] === 302 && $a['location'] === 'login.php', 'UM-02-H04 Anonymous visitor is redirected to login');

echo "== UM-01 Create User Account ==" . PHP_EOL;
$r = new Client(); $rt = $r->token('/php/register.php');
$regPage = $r->get('/php/register.php');
check(strpos($regPage['body'], 'name="confirm_password"') !== false && strpos($regPage['body'], 'value="Student"') !== false && strpos($regPage['body'], 'value="Tutor"') !== false && strpos($regPage['body'], 'value="Administrator"') === false, 'UM-01-H01 Registration form offers only Student and Tutor');
$reg = $r->post('/php/register.php', ['csrf_token' => $rt, 'full_name' => 'New Person', 'email' => 'new@example.edu', 'password' => 'NewPerson123', 'confirm_password' => 'NewPerson123', 'role' => 'Student']);
check($reg['code'] === 200 && strpos($reg['body'], 'created and is active') !== false, 'UM-01-H02 Valid registration shows a confirmation');
check($r->login('new@example.edu', 'NewPerson123')['code'] === 303, 'UM-01-H03 The new account can log in immediately');
$r2 = new Client(); $t2 = $r2->token('/php/register.php');
$adm = $r2->post('/php/register.php', ['csrf_token' => $t2, 'full_name' => 'Eve', 'email' => 'eve@example.edu', 'password' => 'EvePass1234', 'confirm_password' => 'EvePass1234', 'role' => 'Administrator']);
check($adm['code'] === 403 && findUserByEmail($pdo, 'eve@example.edu') === false, 'UM-01-H04 Forged Administrator registration is denied and creates nothing');
$dup = $r2->post('/php/register.php', ['csrf_token' => $t2, 'full_name' => 'Dup', 'email' => 'alex@example.edu', 'password' => 'DupPass1234', 'confirm_password' => 'DupPass1234', 'role' => 'Tutor']);
check($dup['code'] === 422 && strpos($dup['body'], 'already exists') !== false, 'UM-01-H05 Duplicate email shows a correction message');
$xss = $r2->post('/php/register.php', ['csrf_token' => $t2, 'full_name' => '<script>alert(1)</script>', 'email' => 'bad', 'password' => 'x', 'confirm_password' => 'y', 'role' => 'Tutor']);
check($xss['code'] === 422 && strpos($xss['body'], '<script>alert(1)</script>') === false && strpos($xss['body'], '&lt;script&gt;') !== false, 'UM-01-H06 Submitted text is HTML-encoded when redisplayed');
check($r2->post('/php/register.php', ['full_name' => 'No Token', 'email' => 'nt@example.edu', 'password' => 'NoToken1234', 'confirm_password' => 'NoToken1234', 'role' => 'Tutor'])['code'] === 403 && findUserByEmail($pdo, 'nt@example.edu') === false, 'UM-01-H07 Registration without a CSRF token is refused');

echo "== UM-04 Update Own Account / UM-05 Change Password ==" . PHP_EOL;
$t = $c->token('/php/edit-profile.php');
$e1 = $c->post('/php/edit-profile.php', ['csrf_token' => $t, 'full_name' => 'Alex M. Morgan', 'email' => 'alex@example.edu']);
check($e1['code'] === 303 && $e1['location'] === 'profile.php' && strpos($c->get('/php/profile.php')['body'], 'Alex M. Morgan') !== false, 'UM-04-H01 Name update is saved and shown');
$e2 = $c->post('/php/edit-profile.php', ['csrf_token' => $t, 'full_name' => 'Alex', 'email' => 'alex@example.edu', 'role' => 'Administrator']);
check($e2['code'] === 403 && findUserById($pdo, $alexId)['role'] === 'Student', 'UM-04-H02 Posting role=Administrator is denied and role is unchanged');
$e3 = $c->post('/php/edit-profile.php', ['csrf_token' => $t, 'full_name' => 'Alex', 'email' => 'priya@example.edu']);
check($e3['code'] === 422 && findUserById($pdo, $alexId)['email'] === 'alex@example.edu', 'UM-04-H03 Duplicate email is rejected, email unchanged');
check($c->post('/php/edit-profile.php', ['full_name' => 'Alex', 'email' => 'alex@example.edu'])['code'] === 403, 'UM-04-H04 Profile update without CSRF token is refused');
$tp = $c->token('/php/change-password.php');
$p1 = $c->post('/php/change-password.php', ['csrf_token' => $tp, 'current_password' => 'wrong', 'new_password' => 'BrandNew1234', 'confirm_password' => 'BrandNew1234']);
check($p1['code'] === 422 && strpos($p1['body'], 'Current password is incorrect') !== false, 'UM-05-H01 Wrong current password is rejected with a message');
$p2 = $c->post('/php/change-password.php', ['csrf_token' => $tp, 'current_password' => 'AlexPass123', 'new_password' => 'BrandNew1234', 'confirm_password' => 'BrandNew1234']);
check($p2['code'] === 303, 'UM-05-H02 Correct current password changes the password');

echo "== UM-10 Log Out ==" . PHP_EOL;
$liveSid = $c->cookies['PHPSESSID'];
$tl = $c->token('/php/profile.php');
check($c->get('/php/logout.php')['code'] === 405, 'UM-10-H01 Logout by GET is refused (POST only)');
check($c->post('/php/logout.php', [])['code'] === 403, 'UM-10-H02 Logout without CSRF token is refused');
$lo = $c->post('/php/logout.php', ['csrf_token' => $tl]);
check($lo['code'] === 303 && $lo['location'] === 'login.php?logged_out=1', 'UM-10-H03 Logout redirects to the login page');
$cookieCleared = false; foreach ($lo['setcookies'] as $sc) { if (stripos($sc, 'PHPSESSID=deleted') === 0 || stripos($sc, 'Max-Age=0') !== false || stripos($sc, '1970') !== false) { $cookieCleared = true; } }
check($cookieCleared, 'UM-10-H04 The authentication cookie is expired by the response');
$pa = $c->get('/php/profile.php');
check($pa['code'] === 302 && $pa['location'] === 'login.php', 'UM-10-H05 Protected page is denied after logout');
$replay = new Client(); $replay->cookies['PHPSESSID'] = $liveSid;
check($replay->get('/php/profile.php')['code'] === 302, 'UM-10-H06 Replaying the old session cookie is denied (server-side session destroyed)');
check($c->login('alex@example.edu', 'AlexPass123')['code'] === 401, 'UM-05-H03 The old password no longer works after the change');
check($c->login('alex@example.edu', 'BrandNew1234')['code'] === 303, 'UM-05-H04 The new password works');
$lo2 = new Client();
$exp = $lo2->post('/php/logout.php', []);
check($exp['code'] === 303 && $exp['location'] === 'login.php?expired=1', 'UM-10-H07 Logging out with an already-expired session redirects to login');

echo "== UM-03 / UM-06 / UM-07 / UM-08 Administrator pages ==" . PHP_EOL;
$stu = new Client(); $stu->login('alex@example.edu', 'BrandNew1234');
$tut = new Client(); $tut->login('taylor@example.edu', 'TaylorPass1');
$adm = new Client(); $adm->login('admin@example.edu', 'AdminPass123');
check($stu->get('/php/manage-users.php')['code'] === 403, 'UM-03-H01 A Student is denied Manage Users');
check($tut->get('/php/manage-users.php')['code'] === 403, 'UM-03-H02 A Tutor is denied Manage Users');
check($anon->get('/php/manage-users.php')['code'] === 302, 'UM-03-H03 An anonymous visitor is redirected to login');
$list = $adm->get('/php/manage-users.php');
check($list['code'] === 200 && strpos($list['body'], 'priya@example.edu') !== false && strpos($list['body'], 'taylor@example.edu') !== false, 'UM-03-H04 Administrator sees the account list');
$srch = $adm->get('/php/manage-users.php?q=priya&role=Student&status=Active');
check(strpos($srch['body'], 'priya@example.edu') !== false && strpos($srch['body'], 'taylor@example.edu') === false, 'UM-03-H05 Search and filters narrow the list');
check(strpos($adm->get('/php/manage-users.php?q=nobody-here')['body'], 'No matching accounts.') !== false, 'UM-03-H06 No matches shows an empty-state message');
check($adm->get('/php/manage-users.php?role=Root')['code'] === 400, 'UM-03-H07 An invalid role filter is rejected');
check(strpos($adm->get('/php/manage-user.php?user_id=' . $priyaId)['body'], 'Related tutoring requests') !== false, 'UM-03-H08 Administrator can open an account\'s details');
check($stu->get('/php/manage-user.php?user_id=' . $priyaId)['code'] === 403, 'UM-03-H09 A Student cannot open account details');
$ta = $adm->token('/php/manage-user.php?user_id=' . $taylorId);
check($adm->post('/php/update-user-role.php', ['user_id' => $taylorId, 'role' => 'Student', 'confirm' => 'yes'])['code'] === 403 && findUserById($pdo, $taylorId)['role'] === 'Tutor', 'UM-06-H01 Role change without CSRF token is refused');
$ts = $stu->token('/php/profile.php');
check($stu->post('/php/update-user-role.php', ['csrf_token' => $ts, 'user_id' => $alexId, 'role' => 'Administrator', 'confirm' => 'yes'])['code'] === 403 && findUserById($pdo, $alexId)['role'] === 'Student', 'UM-06-H02 A Student POSTing a role change is denied and nothing changes');
check($adm->post('/php/update-user-role.php', ['csrf_token' => $ta, 'user_id' => $taylorId, 'role' => 'Student'])['code'] === 422 && findUserById($pdo, $taylorId)['role'] === 'Tutor', 'UM-06-H03 A role change without confirmation is not applied');
$rr = $adm->post('/php/update-user-role.php', ['csrf_token' => $ta, 'user_id' => $taylorId, 'role' => 'Student', 'confirm' => 'yes']);
check($rr['code'] === 303 && findUserById($pdo, $taylorId)['role'] === 'Student', 'UM-06-H04 Administrator changes a role');
check($adm->post('/php/update-user-role.php', ['csrf_token' => $ta, 'user_id' => $adminId, 'role' => 'Tutor', 'confirm' => 'yes'])['code'] === 409, 'UM-06-H05 Demoting the last active administrator returns Conflict');
check($stu->post('/php/update-user-status.php', ['csrf_token' => $ts, 'user_id' => $priyaId, 'status' => 'Inactive', 'confirm' => 'yes'])['code'] === 403 && findUserById($pdo, $priyaId)['status'] === 'Active', 'UM-07-H01 A Student cannot deactivate accounts');
$pr = new Client(); $pr->login('priya@example.edu', 'PriyaPass123');
check($pr->get('/php/dashboard.php')['code'] === 200, 'UM-07-H02 Priya is signed in before deactivation');
$ds = $adm->post('/php/update-user-status.php', ['csrf_token' => $ta, 'user_id' => $priyaId, 'status' => 'Inactive', 'confirm' => 'yes']);
check($ds['code'] === 303 && findUserById($pdo, $priyaId)['status'] === 'Inactive', 'UM-07-H03 Administrator deactivates Priya');
$after = $pr->get('/php/dashboard.php');
check($after['code'] === 302 && strpos($after['location'], 'login.php') === 0, 'UM-07-H04 Her already-open session is ended on the next request');
check((new Client())->login('priya@example.edu', 'PriyaPass123')['code'] === 401, 'UM-07-H05 The inactive user cannot log in again');
$adm->post('/php/update-user-status.php', ['csrf_token' => $ta, 'user_id' => $priyaId, 'status' => 'Active', 'confirm' => 'yes']);
check((new Client())->login('priya@example.edu', 'PriyaPass123')['code'] === 303, 'UM-07-H06 Reactivated user can log in');
$delPage = $adm->get('/php/delete-user.php?user_id=' . $priyaId);
check($delPage['code'] === 200 && strpos($delPage['body'], 'blocked') !== false, 'UM-08-H01 Delete confirmation warns about related tutoring requests');
$dd = $adm->post('/php/delete-user.php', ['csrf_token' => $ta, 'user_id' => $priyaId, 'confirm' => 'yes']);
check($dd['code'] === 409 && findUserById($pdo, $priyaId) !== false && tableCount($pdo, 'tutoring_requests') === 2, 'UM-08-H02 Deleting an account with requests is blocked; nothing removed');
check($adm->post('/php/delete-user.php', ['csrf_token' => $ta, 'user_id' => $idleId])['code'] === 409 && findUserById($pdo, $idleId) !== false, 'UM-08-H03 Deletion without confirmation changes nothing');
check($stu->post('/php/delete-user.php', ['csrf_token' => $ts, 'user_id' => $idleId, 'confirm' => 'yes'])['code'] === 403 && findUserById($pdo, $idleId) !== false, 'UM-08-H04 A Student cannot delete accounts');
check($adm->post('/php/delete-user.php', ['user_id' => $idleId, 'confirm' => 'yes'])['code'] === 403 && findUserById($pdo, $idleId) !== false, 'UM-08-H05 Delete without CSRF token is refused');
$ok = $adm->post('/php/delete-user.php', ['csrf_token' => $ta, 'user_id' => $idleId, 'confirm' => 'yes']);
check($ok['code'] === 303 && findUserById($pdo, $idleId) === false, 'UM-08-H06 An account with no related records is deleted after confirmation');
check($adm->get('/php/delete-user.php?user_id=424242')['code'] === 404, 'UM-08-H07 Deleting an unknown account shows Not Found');

echo "== Tutoring pages: login, role and ownership guards (integration with UC01/UC02) ==" . PHP_EOL;
$stu = new Client(); $stu->login('alex@example.edu', 'BrandNew1234');
$tut = new Client(); $tut->login('taylor@example.edu', 'TaylorPass1');   // Taylor is now a Student (changed above)
check($anon->get('/php/requests.php')['code'] === 302 && $anon->get('/php/request-form.php')['code'] === 302, 'AUTH-H01 Request pages require login');
$form = $stu->get('/php/request-form.php');
check($form['code'] === 200 && strpos($form['body'], 'name="topics[]"') !== false && strpos($form['body'], 'name="preferredtime"') !== false && strpos($form['body'], 'alex@example.edu') !== false, 'AUTH-H02 Student sees the Lab 4 form with their own identity prefilled');
$mine = $stu->get('/php/requests.php');
check($mine['code'] === 200 && strpos($mine['body'], '>' . $alexReq . '<') !== false && strpos($mine['body'], '>' . $priyaReq . '<') === false, 'AUTH-H03 A Student lists only their own requests');
check($stu->get('/php/request-details.php?request_id=' . $priyaReq)['code'] === 404, 'AUTH-H04 A Student cannot view another student\'s request (404)');
check($stu->get('/php/request-details.php?request_id=' . $alexReq)['code'] === 200, 'AUTH-H05 A Student can view their own request');
$tc = $stu->token('/php/edit-request.php?request_id=' . $alexReq);
check($stu->post('/php/delete-request.php', ['request_id' => $priyaReq, 'csrf_token' => $tc])['code'] === 409 && findRequest($pdo, $priyaReq)['status'] === 'Open', 'AUTH-H06 A Student cannot cancel another student\'s request');
check($stu->post('/php/delete-request.php', ['request_id' => $alexReq])['code'] === 403, 'AUTH-H07 Cancelling without a CSRF token is refused');
$admList = $adm->get('/php/requests.php');
check(strpos($admList['body'], '>' . $alexReq . '<') !== false && strpos($admList['body'], '>' . $priyaReq . '<') !== false, 'AUTH-H08 An Administrator sees all requests');
check($adm->get('/php/request-form.php')['code'] === 403, 'AUTH-H09 An Administrator may not submit a request (Student only)');
$pdo->exec("UPDATE users SET role='Tutor' WHERE user_id=$taylorId");   // restore a Tutor for the matrix check
$tut2 = new Client(); $tut2->login('taylor@example.edu', 'TaylorPass1');
check($tut2->get('/php/requests.php')['code'] === 403 && $tut2->get('/php/request-form.php')['code'] === 403, 'AUTH-H10 A Tutor is denied the request workflow');
$ts2 = $stu->token('/php/request-form.php');
$new = $stu->post('/php/process-request.php', ['csrf_token' => $ts2, 'form_name' => 'tutoring_request', 'student_name' => 'FORGED', 'student_email' => 'priya@example.edu', 'course' => 'IS 6495', 'reason' => 'Need help with sessions and cookies.', 'topics' => ['Study plan'], 'preferreddate' => (new DateTimeImmutable('+5 days'))->format('Y-m-d'), 'preferredtime' => 'Evening']);
$created = $new['code'] === 303 ? (int)preg_replace('/\D/', '', $new['location']) : 0;
$row = $created ? findRequest($pdo, $created) : false;
check($row && (int)$row['student_id'] === (int)findStudentByUserId($pdo, $alexId)['student_id'], 'AUTH-H11 A submitted request is attached to the signed-in student, not to forged form values');
$del = $stu->post('/php/delete-request.php', ['request_id' => $alexReq, 'csrf_token' => $tc]);
check($del['code'] === 303 && findRequest($pdo, $alexReq)['status'] === 'Cancelled', 'AUTH-H12 A Student cancels their own Open request (record retained)');

foreach (['/tests/run-all.php', '/scripts/init-db.php', '/config/db.php', '/database/setup-database.sql', '/services/userService.php'] as $internal) {
    check($anon->get($internal)['code'] === 404, 'AUTH-H13 Router hides internal path ' . $internal);
}

echo "== Session inactivity timeout ==" . PHP_EOL;
$id = new Client(); $id->login('taylor@example.edu', 'TaylorPass1');
check($id->get('/php/profile.php')['code'] === 200, 'UM-09-H12 A fresh session works');
sleep(4);
$timed = $id->get('/php/profile.php');
check($timed['code'] === 302 && $timed['location'] === 'login.php?expired=1', 'UM-09-H13 A session idle beyond the timeout is ended (timeout set to 3 s for this test)');

finishTests();
