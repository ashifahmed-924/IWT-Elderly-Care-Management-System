<?php
require_once __DIR__ . '/../../includes/auth.php';
$user = requireRole('caregiver');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token'] ?? '')) {
    redirect('caregiver/dashboard.php');
}

$elderId = (int) ($_POST['elder_id'] ?? 0);
$healthStatus = $_POST['health_status'] ?? 'stable';
$notes = trim($_POST['notes'] ?? '');

$pdo = getDb();
$stmt = $pdo->prepare('SELECT id, assigned_caregiver_id FROM elders WHERE id = ?');
$stmt->execute([$elderId]);
$elder = $stmt->fetch();

if (!$elder || (int) $elder['assigned_caregiver_id'] !== (int) $user['id']) {
    flash('error', 'Not assigned to this elder.');
    redirect('caregiver/dashboard.php');
}

$pdo->prepare('UPDATE elders SET health_status = ?, notes = ? WHERE id = ?')
    ->execute([$healthStatus, $notes, $elderId]);

flash('success', 'Health status updated.');
redirect('caregiver/dashboard.php?elder_id=' . $elderId);
