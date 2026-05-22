<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token'] ?? '')) {
    redirect('admin/dashboard.php');
}

$pdo = getDb();
$userId = (int) ($_POST['user_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($action === 'toggle') {
    $stmt = $pdo->prepare('UPDATE users SET is_active = NOT is_active WHERE id = ?');
    $stmt->execute([$userId]);
    flash('success', 'User status updated.');
} elseif ($action === 'delete') {
    $stmt = $pdo->prepare('SELECT id, role FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $u = $stmt->fetch();
    if ($u && $u['role'] !== 'admin') { // do not delete admin accounts
        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]);
        flash('success', 'User removed.');
    } else {
        flash('error', 'Cannot delete this user.');
    }
}
redirect('admin/dashboard.php');
