<?php

declare(strict_types=1);

function is_input_empty(string $username, string $password, string $email, string $address)
{
    if (empty($username) || empty($password) || empty ($email) || empty ($address)) {
        return true;
    } else {
        return false;
    }
}

function is_email_invalid(string $email)
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    } else {
        return false;
    }
}

function is_username_taken(object $pdo, string $username) {
    if (get_username($pdo, $username)) {
        return true;
    } else {
        return false;
    }
}

function is_email_registered(object $pdo, string $email) {
    if (get_email($pdo, $email)) {
        return true;
    } else {
        return false;
    }
}

function password_match(string $password, string $passwordConfirmation) {
    if (($password != $passwordConfirmation)) {
        return true;
    } else {
        return false;
    }
}

function create_user(object $pdo, string $username, string $password, string $email, string $address) {
    set_user($pdo, $username, $password, $email, $address);
}

function create_buyer(object $pdo, string $username) {
    set_buyer($pdo, $username);
}
