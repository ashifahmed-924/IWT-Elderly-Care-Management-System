<?php
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}

if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
    flash('error', 'Invalid request. Please try again.');
    redirect('login.php');
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    flash('error', 'Please provide email and password.');
    redirect('login.php');
}

$pdo = getDb();
$stmt = $pdo->prepare('SELECT id, name, email, password_hash, role, is_active FROM users WHERE email = ?');
$stmt->execute([strtolower($email)]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    flash('error', 'Invalid email or password.');
    redirect('login.php');
}

if (!$user['is_active']) {
    flash('error', 'Account is deactivated.');
    redirect('login.php');
}

loginUser($user);
flash('success', 'Welcome back, ' . $user['name'] . '!');
redirect(dashboardUrl($user['role']));
