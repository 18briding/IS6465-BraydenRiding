<?php
// UM-03 View and Search User Accounts (WF-03). Administrator only.
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/userService.php';
require_once __DIR__ . '/../services/requestService.php';
$pdo = openDatabase();
$user = requireLogin($pdo);
requireRole($user, ['Administrator']);
$filters = [
    'q' => trim((string)(isset($_GET['q']) ? $_GET['q'] : '')),
    'role' => (string)(isset($_GET['role']) ? $_GET['role'] : ''),
    'status' => (string)(isset($_GET['status']) ? $_GET['status'] : ''),
];
try {
    $rows = searchUserAccounts($pdo, $user, $filters);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    exit(escapeHtml($e->getMessage()));
}
$flash = takeFlash();
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php'; ?>
<main class="container py-4">
    <h1>Manage users</h1>
<?php if ($flash !== ''): ?><div class="alert alert-success mt-3" role="status"><?= escapeHtml($flash) ?></div><?php endif; ?>
    <form method="get" class="row g-2 my-3 inline-form" style="display:flex;max-width:none">
        <div class="col"><input class="form-control" type="search" name="q" placeholder="Search name or email" value="<?= escapeHtml($filters['q']) ?>"></div>
        <div class="col"><select class="form-select" name="role"><option value="">All roles</option><?php foreach (allRoles() as $r): ?><option value="<?= escapeHtml($r) ?>" <?= $r === $filters['role'] ? 'selected' : '' ?>><?= escapeHtml($r) ?></option><?php endforeach; ?></select></div>
        <div class="col"><select class="form-select" name="status"><option value="">All statuses</option><?php foreach (allStatuses() as $s): ?><option value="<?= escapeHtml($s) ?>" <?= $s === $filters['status'] ? 'selected' : '' ?>><?= escapeHtml($s) ?></option><?php endforeach; ?></select></div>
        <div class="col-auto"><button class="btn btn-primary" type="submit">Search</button></div>
    </form>
<?php if (!$rows): ?><p>No matching accounts.</p><?php else: ?>
    <table class="table">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Manage</th></tr></thead>
        <tbody><?php foreach ($rows as $r): ?><tr>
            <td><?= escapeHtml($r['full_name']) ?></td>
            <td><?= escapeHtml($r['email']) ?></td>
            <td><?= escapeHtml($r['role']) ?></td>
            <td><?= escapeHtml($r['status']) ?></td>
            <td><a href="manage-user.php?user_id=<?= (int)$r['user_id'] ?>">Manage</a></td>
        </tr><?php endforeach; ?></tbody>
    </table>
<?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
