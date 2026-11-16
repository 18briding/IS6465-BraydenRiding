<?php

// Validates the Card Name: must not be empty and within a reasonable length (e.g., 2 to 100 chars).

function validateCardName($name) {
    $name = trim($name);
    if (empty($name)) {
        return "Card Name is required.";
    }
    if (strlen($name) < 2 || strlen($name) > 100) {
        return "Card Name must be between 2 and 100 characters.";
    }
    return "";
}

// Validates the Card Type: must be one of the allowed categories.
 
function validateCardType($type) {
    $allowedTypes = ['Retail', 'Dining', 'Electronics', 'Travel'];
    $type = trim($type);
    if (empty($type)) {
        return "Card Type is required.";
    }
    if (!in_array($type, $allowedTypes)) {
        return "Invalid Card Type selected.";
    }
    return "";
}

// Validates the Card Value: must be a positive number.
 
function validateCardValue($value) {
    if (!is_numeric($value) || (float)$value <= 0) {
        return "Card Value must be a positive number.";
    }
    return "";
}

// Validates Points Required: must be a positive whole integer.

function validatePoints($points) {
    if (!filter_var($points, FILTER_VALIDATE_INT) || (int)$points <= 0) {
        return "Points Required must be a positive whole number.";
    }
    return "";
}

// Validates a record ID (e.g., card_id): must be a positive integer.

function validateId($id) {
    if (!filter_var($id, FILTER_VALIDATE_INT) || (int)$id <= 0) {
        return false;
    }
    return (int)$id;
}
?>