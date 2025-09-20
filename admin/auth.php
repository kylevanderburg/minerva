<?php
session_start();

// Load users
$usersFile = __DIR__ . '/config-users.json';
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

if (!is_array($users)) {
    die("User database corrupted.");
}

// CSRF protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Limit login attempts
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

// Logout handler
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Handle login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename($_SERVER['SCRIPT_NAME']) === 'login.php') {
    if ($_SESSION['login_attempts'] >= 5) {
        die("Too many login attempts.");
    }

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token.");
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (isset($users[$username]) && password_verify($password, $users[$username]['password'])) {
        $_SESSION['admin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $users[$username]['role'] ?? 'editor';
        $_SESSION['login_attempts'] = 0;
        header('Location: index.php');
        exit;
    } else {
        $_SESSION['login_attempts']++;
        $_SESSION['error'] = "Invalid username or password.";
        header('Location: login.php');
        exit;
    }
}

// Enforce login
if (!isset($_SESSION['admin']) && basename($_SERVER['SCRIPT_NAME']) !== 'login.php') {
    header('Location: login.php');
    exit;
}
