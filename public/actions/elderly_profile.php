<?php
require_once __DIR__ . '/../../includes/auth.php';
$user = requireRole('elderly');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token'] ?? '')) {
    redirect('elderly/profile.php');
}

$pdo = getDb();
$stmt = $pdo->prepare('SELECT id FROM elders WHERE user_id = ?');
$stmt->execute([$user['id']]);
$elder = $stmt->fetch();
if (!$elder) {
    flash('error', 'Profile not found.');
    redirect('elderly/profile.php');
}

$dob = $_POST['date_of_birth'] ?: null;
$stmt = $pdo->prepare('UPDATE elders SET date_of_birth=?, address=?, blood_type=?, allergies=?, medications=?, conditions=?, ec_name=?, ec_phone=?, ec_relationship=? WHERE user_id=?');
$stmt->execute([
    $dob,
    trim($_POST['address'] ?? ''),
    trim($_POST['blood_type'] ?? ''),
    parseListField($_POST['allergies'] ?? ''),
    parseListField($_POST['medications'] ?? ''),
    parseListField($_POST['conditions'] ?? ''),
    trim($_POST['ec_name'] ?? ''),
    trim($_POST['ec_phone'] ?? ''),
    trim($_POST['ec_relationship'] ?? ''),
    $user['id'],
]);

flash('success', 'Profile updated successfully.');
redirect('elderly/profile.php');
