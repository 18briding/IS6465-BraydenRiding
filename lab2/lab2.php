<?php

// Lab 2: PHP Expressions and Control Structures

echo '<h1>Lab 2A: Expressions and Control Structures</h1>';

// 1. Expressions and Operators
echo '<h2>1. Expressions and Operators</h2>';

$x = -5;

// TODO: Write an expression that calculates:
// y = 3(abs(2x) + 4)
$y = 3 * (abs(2 * $x) +4);

echo "The value of y is: $y<br>";

// 2. Boolean Comparisons
echo '<h2>2. Comparisons</h2>';

// TODO: Use var_dump() to compare:
// 2 > 1
// 2 == 3
// "5" == 5
// "5" === 5

var_dump(2 > 1);          //true
var_dump(2 == 3);         //false
var_dump("5" == 5);       //true        
var_dump("5" === 5);      //false
echo '<br>';


// 3. Unary Operators
echo '<h2>3. Unary Operators</h2>';

// TODO: Create variables that demonstrate unary minus, unary plus,
// and pre-increment. Display the results.

$c = 5;
$d = -$c;       //this makes $c variable negative
$e = +$c;
$f = ++$c;      //this adds one to the value of $c variable

echo $d;
    echo '<br>';
echo $e;
    echo '<br>';
echo $f;
    echo '<br>';



// 4. Operator Precedence
echo '<h2>4. Operator Precedence</h2>';

// TODO: Compare the results of these two expressions:
// 2 + 3 * 4
// (2 + 3) * 4

$a = 2 + 3 * 4;
$b = (2 + 3) * 4;
                        //this shows that order of operation changes
echo $a;
    echo '<br>';
echo $b;
    echo '<br>';

// 5. Assignment Associativity
echo '<h2>5. Assignment Associativity</h2>';

// TODO: Assign 0 to $level, $score, and $time in one statement.
// Display each variable.

$level = $score = $time = 0;
echo $level;
    echo '<br>';
echo $score;
    echo '<br>';
echo $time;
    echo '<br>';

// 6. Equality and Assignment
echo '<h2>6. Equality and Assignment</h2>';

// TODO: Create variables with equal and unequal values.
// Use var_dump() to compare them with == and ===.
$a = 5;
$b = 5;
var_dump($a == $b);     // this will be true, 
var_dump($a === $b);    // this will be true, remember 3 ='s mean value type must match (integer, float, etc)
$c = 10;
$d = 20; 
var_dump($c == $d);         // will be false
var_dump($c === $d);        // will be false

    echo '<br>';



// 7. Logical Operators
echo '<h2>7. Logical Operators</h2>';

$a = 1;         //1 is typically true in coding
$b = 0;         //0 is typically false in coding
$c = 1;

// TODO: Use var_dump() to demonstrate &&, ||, and !.
var_dump($a && $b);
var_dump($a || $b);
var_dump(!$a);          //since value is 1 and ! does opposite it will be false (now 0)
var_dump(!$b);          //since value is 0 and ! does opposite it will be true (now 1)

    echo '<br>';

// 8. Scholarship Decision with if...else
echo '<h2>8. Scholarship Decision with if...else</h2>';

$gpa = 3.59;

// TODO: Use if...else to display whether the student receives a scholarship.

if ($gpa >= 3.5) {
    echo "Student gets scholarship";
}else{
    echo "Student does not get scholarship";
}

    echo '<br>';


// 9. Scholarship Decision with if...elseif...else
echo '<h2>9. Scholarship Decision with if...elseif...else</h2>';

$gpa = 3.75;

// TODO: Use if...elseif...else to display:
// Full scholarship for GPA 3.7 or higher
// Partial scholarship for GPA 3.5 through 3.69
// No scholarship otherwise

if ($gpa >= 3.8) {
    echo "Student gets scholarship";
}elseif ($gpa >= 3.6) {
    echo "Student receives partial scholarship";
}else{
    echo "Student does not get scholarship";
}

    echo '<br>';


// 10. Access Status
echo '<h2>10. Access Status</h2>';

$status = 'pending';

// TODO: Use a switch statement to display a message for:
// approved, pending, and all other statuses

switch ($status) {
    case "approved":
    echo "Access granted";
    break;
    case "pending":
    echo "Review in progress";
    break;
    default:
    echo "Access denied";
}

        echo '<br>';

// 11. While Loop
echo '<h2>11. While Loop</h2>';

$fuel = 10;

// TODO: Create a while loop.
// Display "There is enough fuel" while fuel is greater than 1.
// Decrease fuel during each repetition.

while ($fuel > 1) {
    echo "There is enough fuel<br>";
    $fuel--;        //this subtracts $fuel by 1 each repition (without it, loop runs forever.....)
}
 echo '<br>';


// 12. Multiplication Table
echo '<h2>12. Multiplication Table</h2>';

// TODO: Create a for loop that displays the 12-times table
// from 1 through 12.

for ($count = 1; $count <= 12; $count++) {
    echo $count . " times 12 is " . ($count * 12) . "<br>";
}
echo '<br>';


