<?php
require_once __DIR__ . '/../../includes/auth.php';
$user = requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token'] ?? '')) {
    redirect('admin/appointments.php');
}

$pdo = getDb();
$action = $_POST['action'] ?? 'save';
$id = (int) ($_POST['id'] ?? 0);

if ($action === 'delete' && $id) {
    $pdo->prepare('DELETE FROM appointments WHERE id = ?')->execute([$id]);
    flash('success', 'Appointment deleted.');
    redirect('admin/appointments.php');
}

$elderId = (int) ($_POST['elder_id'] ?? 0);
$caregiverId = (int) ($_POST['caregiver_id'] ?? 0) ?: null;
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$date = $_POST['appt_date'] ?? '';
$time = trim($_POST['appt_time'] ?? '');
$location = trim($_POST['location'] ?? '');
$status = $_POST['status'] ?? 'scheduled';

if (!$elderId || !$title || !$date || !$time) {
    flash('error', 'Please fill required appointment fields.');
    redirect('admin/appointments.php');
}

if ($id) {
    $stmt = $pdo->prepare('UPDATE appointments SET elder_id=?, caregiver_id=?, title=?, description=?, appt_date=?, appt_time=?, location=?, status=? WHERE id=?');
    $stmt->execute([$elderId, $caregiverId, $title, $description, $date, $time, $location, $status, $id]);
    flash('success', 'Appointment updated.');
} else {
    $stmt = $pdo->prepare('INSERT INTO appointments (elder_id, caregiver_id, title, description, appt_date, appt_time, location, status, created_by) VALUES (?,?,?,?,?,?,?,?,?)');
    $stmt->execute([$elderId, $caregiverId, $title, $description, $date, $time, $location, $status, $user['id']]);
    flash('success', 'Appointment created.');
}
redirect('admin/appointments.php');
