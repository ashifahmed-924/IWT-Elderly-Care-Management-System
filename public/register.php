<?php
require_once __DIR__ . '/../includes/auth.php';
if (currentUser()) {
    redirect(dashboardUrl(currentUser()['role']));
}
$pageTitle = 'Register';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="auth-card card">
  <h1 class="page-title">Create Account</h1>
  <p class="page-sub">Join the <?= e(APP_NAME) ?> platform</p>
  <form method="post" action="<?= e(PUBLIC_URL) ?>actions/auth_register.php" class="form-stack mt-6">
    <?= csrfField() ?>
    <div>
      <label class="label">Full Name</label>
      <input type="text" name="name" class="input-field" required>
    </div>
    <div>
      <label class="label">Email</label>
      <input type="email" name="email" class="input-field" required>
    </div>
    <div>
      <label class="label">Phone</label>
      <input type="text" name="phone" class="input-field">
    </div>
    <div>
      <label class="label">Role</label>
      <select name="role" class="input-field" required>
        <option value="elderly">Elderly User</option>
        <option value="caregiver">Caregiver</option>
      </select>
    </div>
    <div>
      <label class="label">Password</label>
      <input type="password" name="password" class="input-field" required minlength="6">
    </div>
    <div>
      <label class="label">Confirm Password</label>
      <input type="password" name="confirm_password" class="input-field" required>
    </div>
    <button type="submit" class="btn-primary" style="width:100%">Register</button>
  </form>
  <p class="text-center mt-6" style="font-size:0.875rem;color:var(--slate-500)">
    Already have an account? <a href="<?= e(PUBLIC_URL) ?>login.php">Sign In</a>
  </p>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
