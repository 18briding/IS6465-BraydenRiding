<?php

echo "Hello from the server";
echo "<br>";


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed");
}

$course = trim($_POST["course"] ?? "");
$topic = trim($_POST["topic"] ?? "");
$preferredTime = $_POST["preferredtime"] ?? "";
$preferredDate = $_POST["preferreddate"] ?? $_POST["date"] ?? "";

// this prints out what was selected on the request form

echo "Course: $course\n <br>";
echo "Topic: $topic\n <br>";
echo "Preferred Time: $preferredTime\n <br>";
echo "Preferred Date: $preferredDate\n <br>";


//this is the first check for initial errors, ensuring valid course and preferred time are selected

$errors = [];
$allowedCourses = ["IS 6465", "IS 6495", "IS 6570"];
$allowedTimes = ["Morning", "Afternoon", "Evening"];

if (!in_array($course, $allowedCourses, true)) {
    $errors["course"] = "Select a valid course.";
}

if (!in_array($preferredTime, $allowedTimes, true)) {
    $errors["preferred_time"] = "Select a valid preferred time.";
}

print_r($errors);
echo "<br>";

//this first checks if the topic is empty or over 120 characters.

if ($topic === "" || mb_strlen($topic) > 120) {
    $errors["topic"] = "Enter a topic of 1 to 120 characters.";
}

$date = DateTime::createFromFormat("Y-m-d", $preferredDate);        //this ensures that the date is in the right format
$validDate = $date
    && $date->format("Y-m-d") === $preferredDate
    && $date >= new DateTime("today");

if (!$validDate) {
    $errors["preferreddate"] = "Enter a valid preferred date that is today or later.";
}

print_r($errors);


//this says that "if there are no errors, then display the request received message with course, topic, and status

if (!$errors) {
    echo "<h1>Request received</h1>";
    echo "<p>Course: " . htmlspecialchars($course, ENT_QUOTES, "UTF-8") . "</p>";
    echo "<p>Topic: " . htmlspecialchars($topic, ENT_QUOTES, "UTF-8") . "</p>";
    echo "<p>Status: Pending review</p>";
}



?>