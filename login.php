<?php
session_start();
require_once 'db.php';

// only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

// check both fields are filled
if (empty($username) || empty($password)) {
    header('Location: index.php?error=required');
    exit();
}

// look up user in database
$stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// verify password
if (!$user || !password_verify($password, $user['password'])) {
    header('Location: index.php?error=invalid');
    exit();
}

// login successful — save user info to session
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];

// redirect based on role
if ($user['role'] === 'teacher') {
    header('Location: dashboard.php');
} else {
    header('Location: dashboard.php');
}
exit();
?>