<?php

// Lab 2B: PHP Functions and Basic Objects

echo '<h1>Lab 2B: Functions and Objects</h1>';

// 1. Function with Parameters and Return Value
echo '<h2>1. Function with Parameters and Return Value</h2>';

    // TODO: Clean the capitalization of each name.
    // TODO: Return the complete name as one string.

function formatName($first, $middle, $last)
{
    $first = ucfirst(strtolower($first));
    $middle = ucfirst(strtolower($middle));
    $last = ucfirst(strtolower($last));

    return $first. ' '. $middle . ' ' .$last;
}

// TODO: Call formatName() using sample data.

$displayName = formatname('mARIA', 'lee', 'CHEN');
echo $displayName;      // with the function defined paramters we want the ouput Maria Lee Chen even though the capilization is goofy in the input
echo '<br>';


// 2. Function That Returns an Array
echo '<h2>2. Function That Returns an Array</h2>';

    // TODO: Return an array containing the cleaned name parts.

// TODO: Call cleanNameParts() and display one item from the array.


function cleanNameParts($first, $middle, $last)
{
    return [
        ucfirst(strtolower($first)),
        ucfirst(strtolower($middle)),
        ucfirst(strtolower($last)),
    ];
    }

    $nameParts = cleanNameParts('mARIA', 'lee', 'CHEN');
    echo $nameParts[0];    // 0 = first 1 = middle 2 = last (the order in the array starting w/ 0)
    echo '<br>';



// 3. Variable Scope
echo '<h2>3. Variable Scope</h2>';

$first = 'Maria';
$last = 'Chen';


// TODO: Use the global keyword to access $first and $last.
    // Return the complete name.
function buildFullName()
{
   global $first, $last;
   return "$first $last";
}

echo buildFullName();
echo '<br>';

// TODO: Call buildFullName() and display the result.
// TODO: Return the complete name using the parameters.

function buildFullName2($first, $last)
{
    return "$first $last";
}

echo buildFullName2($first, $last);
echo '<br>';

// TODO: Call buildFullName2() and display the result.

// 4. Static Variable
echo '<h2>4. Static Variable</h2>';

   // TODO: Use a static variable so this function returns
    // 1 on its first call, 2 on its second call, and so on.

function nextTicketNumber()
{
    static $number = 0;
    $number++;
    return $number;
}

// TODO: Call the function at least twice.
echo nextTicketNumber(); //1
    echo '<br>';
echo nextTicketNumber(); //2
    echo '<br>';
echo nextTicketNumber(); //3
    echo '<br>';


// 5. User Class
echo '<h2>5. User Class</h2>';

class User
{
    public $name;
    public $email;

    public function display()
    {
        return 'User: ' . $this->name;
    } 
    // TODO: Create a display() method that returns the user's name.
}

$user = new User();
$user->name = 'Maria Chen';
$user->email = 'maria@example.com';

echo $user->display();

// TODO: Create a User object.
// TODO: Assign a name and email.
// TODO: Display the result of the display() method.



// 6. Constructor and Password Hashing
echo '<h2>6. Constructor and Password Hashing</h2>';

class User2
{
    public $name;
    private $passwordHash;

    public function __construct($name, $password)
    {
        $this->name = $name;
        $this->passwordHash = 
            password_hash($password, PASSWORD_DEFAULT);
    }

    // TODO: Create a constructor that receives a name and password.
    // Store the name and create a password hash using password_hash().
}

$user = new User2('Maria Chen', 'temporary-password');
print_r($user);
echo '<br>';


// TODO: Create one User2 object and display it using print_r().
