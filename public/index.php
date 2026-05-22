<?php
require_once __DIR__ . '/../includes/auth.php';
$user = currentUser();
$pageTitle = 'Home';
require_once __DIR__ . '/../includes/header.php';
$dash = $user ? dashboardUrl($user['role']) : null;
?>
<section class="hero">
  <img src="<?= e(PUBLIC_URL) ?>assets/images/hero1.jpg" alt="" class="hero-bg" aria-hidden="true">
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <h1>Compassionate Elder Care Management</h1>
    <p>A modern platform connecting elderly users, caregivers, and administrators for coordinated health monitoring and appointment management.</p>
    <div class="hero-actions">
      <?php if ($user): ?>
        <a href="<?= e(PUBLIC_URL . $dash) ?>" class="btn-white">Go to Dashboard</a>
      <?php else: ?>
        <a href="<?= e(PUBLIC_URL) ?>register.php" class="btn-white">Get Started</a>
        <a href="<?= e(PUBLIC_URL) ?>login.php" class="btn-outline">Sign In</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="mt-8">
  <h2 class="text-center page-title">Platform Features</h2>
  <p class="text-center page-sub">Everything you need for coordinated elder care</p>
  <div class="grid-4 mt-8">
    <?php
    $features = [
      ['card1.jpg', 'Elder Profile Management', 'Elderly users can view and update profiles, health details, and appointments.'],
      ['card2.jpg', 'Caregiver Support', 'Caregivers track assigned elders, update health status, and add care notes.'],
      ['card3.jpg', 'Appointment Scheduling', 'Admins manage appointments between elders and caregivers seamlessly.'],
      ['card4.jpg', 'Secure Access', 'Session-based authentication with role-based access for Admin, Caregiver, and Elderly.'],
    ];
    foreach ($features as $f):
    ?>
    <div class="feature-card">
      <img src="<?= e(PUBLIC_URL) ?>assets/images/<?= e($f[0]) ?>" alt="" aria-hidden="true">
      <div class="overlay"></div>
      <div class="inner">
        <h3><?= e($f[1]) ?></h3>
        <p style="font-size:0.875rem;margin-top:0.5rem"><?= e($f[2]) ?></p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="portals-section">
  <img src="<?= e(PUBLIC_URL) ?>assets/images/card5.jpg" alt="" aria-hidden="true">
  <div class="overlay"></div>
  <div class="inner">
    <h2 class="page-title" style="color:#fff">Three Role-Based Portals</h2>
    <div class="grid-3 mt-6">
      <?php foreach (['Admin', 'Caregiver', 'Elderly User'] as $role): ?>
        <div class="portal-pill"><?= e($role) ?></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
