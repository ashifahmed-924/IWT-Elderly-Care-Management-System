<?php
require_once __DIR__ . '/../../includes/auth.php';
$user = requireRole('caregiver');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token'] ?? '')) {
    redirect('caregiver/dashboard.php');
}

$elderId = (int) ($_POST['elder_id'] ?? 0);
$pdo = getDb();
$stmt = $pdo->prepare('SELECT assigned_caregiver_id FROM elders WHERE id = ?');
$stmt->execute([$elderId]);
$elder = $stmt->fetch();

if (!$elder || (int) $elder['assigned_caregiver_id'] !== (int) $user['id']) {
    flash('error', 'Not assigned to this elder.');
    redirect('caregiver/dashboard.php');
}

$stmt = $pdo->prepare('INSERT INTO health_records (elder_id, blood_pressure, heart_rate, temperature, weight, blood_sugar, oxygen_level, notes, recorded_by) VALUES (?,?,?,?,?,?,?,?,?)');
$stmt->execute([
    $elderId,
    trim($_POST['blood_pressure'] ?? '') ?: null,
    $_POST['heart_rate'] !== '' ? (int) $_POST['heart_rate'] : null,
    $_POST['temperature'] !== '' ? (float) $_POST['temperature'] : null,
    $_POST['weight'] !== '' ? (float) $_POST['weight'] : null,
    $_POST['blood_sugar'] !== '' ? (int) $_POST['blood_sugar'] : null,
    $_POST['oxygen_level'] !== '' ? (int) $_POST['oxygen_level'] : null,
    trim($_POST['notes'] ?? '') ?: null,
    $user['id'],
]);

flash('success', 'Health record added.');
redirect('caregiver/dashboard.php?elder_id=' . $elderId);
