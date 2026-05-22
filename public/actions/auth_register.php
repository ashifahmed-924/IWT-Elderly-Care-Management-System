<?php
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('register.php');
}

if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
    flash('error', 'Invalid request.');
    redirect('register.php');
}

$name = trim($_POST['name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';
$role = $_POST['role'] ?? '';
$phone = trim($_POST['phone'] ?? '');

if (!$name || !$email || !$password || !$role) {
    flash('error', 'Please fill in all required fields.');
    redirect('register.php');
}

if ($password !== $confirm) {
    flash('error', 'Passwords do not match.');
    redirect('register.php');
}

if ($role === 'admin') {
    flash('error', 'Admin accounts cannot be created via registration.');
    redirect('register.php');
}

if (!in_array($role, ['caregiver', 'elderly'], true)) {
    flash('error', 'Invalid role selected.');
    redirect('register.php');
}

if (strlen($password) < 6) {
    flash('error', 'Password must be at least 6 characters.');
    redirect('register.php');
}

$pdo = getDb();
$check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$check->execute([$email]);
if ($check->fetch()) {
    flash('error', 'User already exists with this email.');
    redirect('register.php');
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, phone) VALUES (?, ?, ?, ?, ?)');
$stmt->execute([$name, $email, $hash, $role, $phone ?: null]);
$userId = (int) $pdo->lastInsertId();

if ($role === 'elderly') {
    $pdo->prepare('INSERT INTO elders (user_id) VALUES (?)')->execute([$userId]);
}

$stmt = $pdo->prepare('SELECT id, name, email, role FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

loginUser($user);
flash('success', 'Account created successfully!');
redirect(dashboardUrl($user['role']));
