<?php
declare(strict_types=1);


function sanitize_input(?string $value): string
{
    return trim((string) $value);
}

function validate_customer_id($value): ?string
{
    $value = sanitize_input($value);
    if ($value === '') {
        return 'Customer ID is required.';
    }
    if (!ctype_digit($value) || (int) $value <= 0) {
        return 'Customer ID must be a positive whole number.';
    }
    return null;
}


function validate_product_id($value): ?string
{
    $value = sanitize_input($value);
    if ($value === '') {
        return 'Product ID is required.';
    }
    if (!ctype_digit($value) || (int) $value <= 0) {
        return 'Product ID must be a positive whole number.';
    }
    return null;
}


function validate_quantity($value): ?string
{
    $value = sanitize_input($value);
    if ($value === '') {
        return 'Quantity is required.';
    }
    if (!ctype_digit($value) || (int) $value <= 0) {
        return 'Quantity must be a positive whole number.';
    }
    return null;
}


function validate_email($value): ?string
{
    $value = sanitize_input($value);
    if ($value === '') {
        return 'Email address is required.';
    }
    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
        return 'Email address is not valid.';
    }
    return null;
}


function validate_phone($value): ?string
{
    $value = sanitize_input($value);
    if ($value === '') {
        return 'Phone number is required.';
    }
    if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $value)) {
        return 'Phone number may only contain digits, spaces, +, -, and ().';
    }
    return null;
}




function validate_name($value): ?string
{
    $value = sanitize_input($value);
    if ($value === '') {
        return 'Name is required.';
    }
    if (!preg_match('/^[a-z\s.\'-]{2,60}$/i', $value)) {
        return 'Name may only contain letters, spaces, periods, apostrophes, and hyphens.';
    }
    return null;
}


function validate_address($value): ?string
{
    $value = sanitize_input($value);
    if ($value === '') {
        return 'Address is required.';
    }
    if (strlen($value) < 5 || strlen($value) > 120) {
        return 'Address should be between 5 and 120 characters.';
    }
    if (!preg_match('/^[a-z0-9\s.,#\-\/]+$/i', $value)) {
        return 'Address contains invalid characters.';
    }
    return null;
}
