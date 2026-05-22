<?php

// Escape output before printing in HTML (XSS protection)
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . PUBLIC_URL . ltrim($path, '/'));
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// CSRF token for POST forms
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(?string $token): bool
{
    return $token && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

function parseListField(?string $value): string
{
    if ($value === null || $value === '') {
        return '';
    }
    $parts = array_map('trim', explode(',', $value));
    return implode(', ', array_filter($parts));
}

function dashboardUrl(?string $role): string
{
    return match ($role) {
        'admin' => 'admin/dashboard.php',
        'caregiver' => 'caregiver/dashboard.php',
        'elderly' => 'elderly/profile.php',
        default => 'index.php',
    };
}

function canAccessElder(array $user, int $elderId, ?int $elderUserId, ?int $assignedCaregiverId): bool
{
    if ($user['role'] === 'admin') {
        return true;
    }
    if ($user['role'] === 'elderly') {
        return (int) $user['id'] === (int) $elderUserId;
    }
    if ($user['role'] === 'caregiver') {
        return (int) $user['id'] === (int) $assignedCaregiverId;
    }
    return false;
}

// Update elder's caregiver and keep caregiver_elders table in sync
function assignCaregiverToElder(PDO $pdo, int $caregiverId, int $elderId): void
{
    $stmt = $pdo->prepare('SELECT assigned_caregiver_id FROM elders WHERE id = ?');
    $stmt->execute([$elderId]);
    $elder = $stmt->fetch();
    if (!$elder) {
        throw new RuntimeException('Elder not found');
    }

    // Remove old assignment link if reassigning
    if ($elder['assigned_caregiver_id']) {
        $pdo->prepare('DELETE FROM caregiver_elders WHERE caregiver_id = ? AND elder_id = ?')
            ->execute([$elder['assigned_caregiver_id'], $elderId]);
    }

    $pdo->prepare('UPDATE elders SET assigned_caregiver_id = ? WHERE id = ?')
        ->execute([$caregiverId, $elderId]);

    $pdo->prepare('INSERT IGNORE INTO caregiver_elders (caregiver_id, elder_id) VALUES (?, ?)')
        ->execute([$caregiverId, $elderId]);
}

function getElderIdForUser(PDO $pdo, int $userId): ?int
{
    $stmt = $pdo->prepare('SELECT id FROM elders WHERE user_id = ?');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    return $row ? (int) $row['id'] : null;
}

function statusBadgeClass(string $status): string
{
    return match ($status) {
        'stable' => 'badge-green',
        'monitoring' => 'badge-amber',
        'critical' => 'badge-red',
        'recovering' => 'badge-blue',
        'scheduled' => 'badge-blue',
        'completed' => 'badge-green',
        'cancelled' => 'badge-red',
        'rescheduled' => 'badge-amber',
        default => 'badge-gray',
    };
}
