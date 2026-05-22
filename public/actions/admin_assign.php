<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token'] ?? '')) {
    flash('error', 'Invalid request.');
    redirect('admin/dashboard.php');
}

$caregiverId = (int) ($_POST['caregiver_id'] ?? 0);
$elderId = (int) ($_POST['elder_id'] ?? 0);

if (!$caregiverId || !$elderId) {
    flash('error', 'Select both caregiver and elder.');
    redirect('admin/dashboard.php');
}

$pdo = getDb();
$cg = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'caregiver'");
$cg->execute([$caregiverId]);
if (!$cg->fetch()) {
    flash('error', 'Invalid caregiver.');
    redirect('admin/dashboard.php');
}

try {
    assignCaregiverToElder($pdo, $caregiverId, $elderId);
    flash('success', 'Caregiver assigned successfully.');
} catch (Exception $e) {
    flash('error', 'Assignment failed.');
}
redirect('admin/dashboard.php');
