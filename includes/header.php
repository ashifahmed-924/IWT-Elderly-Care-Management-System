<?php
$user = currentUser();
$dash = $user ? dashboardUrl($user['role']) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?><?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(PUBLIC_URL) ?>assets/css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="container nav-inner">
    <a href="<?= e(PUBLIC_URL) ?>index.php" class="logo-link" aria-label="<?= e(APP_NAME) ?> home">
      <span class="logo-wordmark" aria-hidden="true">
        <span class="logo-word-care">Care</span><span class="logo-word-connect">Connect</span>
      </span>
    </a>
    <div class="nav-links">
      <a href="<?= e(PUBLIC_URL) ?>index.php">Home</a>
      <?php if ($user): ?>
        <a href="<?= e(PUBLIC_URL . $dash) ?>">Dashboard</a>
        <?php if ($user['role'] === 'admin'): ?>
          <a href="<?= e(PUBLIC_URL) ?>admin/appointments.php">Appointments</a>
        <?php endif; ?>
        <?php if ($user['role'] === 'elderly'): ?>
          <a href="<?= e(PUBLIC_URL) ?>elderly/profile.php">My Profile</a>
          <a href="<?= e(PUBLIC_URL) ?>elderly/appointments.php">Appointments</a>
        <?php endif; ?>
        <?php if ($user['role'] === 'caregiver'): ?>
          <a href="<?= e(PUBLIC_URL) ?>caregiver/dashboard.php">Caregiver Panel</a>
        <?php endif; ?>
        <span class="role-badge"><?= e($user['role']) ?></span>
        <a href="<?= e(PUBLIC_URL) ?>logout.php" class="btn-secondary btn-sm">Logout</a>
      <?php else: ?>
        <a href="<?= e(PUBLIC_URL) ?>login.php">Login</a>
        <a href="<?= e(PUBLIC_URL) ?>register.php" class="btn-primary btn-sm">Register</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
<main class="container main-content">
<?php if ($flash = getFlash()): ?>
  <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>
