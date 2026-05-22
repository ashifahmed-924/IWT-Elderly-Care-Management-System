<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // needed for login and CSRF
}

function currentUser(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $pdo = getDb();
    $stmt = $pdo->prepare('SELECT id, name, email, role, phone, is_active FROM users WHERE id = ? AND is_active = 1');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function requireLogin(): array
{
    $user = currentUser();
    if (!$user) {
        flash('error', 'Please sign in to continue.');
        redirect('login.php');
    }
    return $user;
}

// Block pages if user role does not match (e.g. only admin)
function requireRole(string ...$roles): array
{
    $user = requireLogin();
    if (!in_array($user['role'], $roles, true)) {
        flash('error', 'You do not have permission to access that page.');
        redirect('index.php');
    }
    return $user;
}

function loginUser(array $user): void
{
    // Store logged-in user in session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['name'];
}

function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
