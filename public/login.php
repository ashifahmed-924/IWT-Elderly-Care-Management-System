<?php
require_once __DIR__ . '/../includes/auth.php';
if (currentUser()) {
    redirect(dashboardUrl(currentUser()['role']));
}
$pageTitle = 'Login';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="auth-card card">
  <h1 class="page-title">Welcome Back</h1>
  <p class="page-sub">Sign in to your ElderCare account</p>
  <form method="post" action="<?= e(PUBLIC_URL) ?>actions/auth_login.php" class="form-stack mt-6">
    <?= csrfField() ?>
    <div>
      <label class="label">Email</label>
      <input type="email" name="email" class="input-field" required placeholder="you@example.com">
    </div>
    <div>
      <label class="label">Password</label>
      <input type="password" name="password" class="input-field" required>
    </div>
    <button type="submit" class="btn-primary" style="width:100%">Sign In</button>
  </form>
  <p class="text-center mt-6" style="font-size:0.875rem;color:var(--slate-500)">
    Don't have an account? <a href="<?= e(PUBLIC_URL) ?>register.php">Register</a>
  </p>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
