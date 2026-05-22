<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDb();

$stats = [
    'totalUsers' => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'elders' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'elderly'")->fetchColumn(),
    'caregivers' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'caregiver'")->fetchColumn(),
    'appointments' => (int) $pdo->query('SELECT COUNT(*) FROM appointments')->fetchColumn(),
];

$users = $pdo->query('SELECT id, name, email, role, is_active, phone FROM users ORDER BY created_at DESC')->fetchAll();

$elders = $pdo->query("
  SELECT e.id, u.name
  FROM elders e
  JOIN users u ON e.user_id = u.id
  ORDER BY u.name
")->fetchAll();

$caregivers = $pdo->query("SELECT id, name FROM users WHERE role = 'caregiver' ORDER BY name")->fetchAll();

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../../includes/header.php';
?>
<h1 class="page-title">Admin Dashboard</h1>
<p class="page-sub">Manage users, elders, and assignments</p>

<div class="grid-4 mt-8">
  <div class="stat-card"><div class="stat-icon primary">👥</div><div><p class="stat-label">Total Users</p><p class="stat-value"><?= (int) $stats['totalUsers'] ?></p></div></div>
  <div class="stat-card"><div class="stat-icon green">👴</div><div><p class="stat-label">Elderly Users</p><p class="stat-value"><?= (int) $stats['elders'] ?></p></div></div>
  <div class="stat-card"><div class="stat-icon amber">🩺</div><div><p class="stat-label">Caregivers</p><p class="stat-value"><?= (int) $stats['caregivers'] ?></p></div></div>
  <div class="stat-card"><div class="stat-icon purple">📅</div><div><p class="stat-label">Appointments</p><p class="stat-value"><?= (int) $stats['appointments'] ?></p></div></div>
</div>

<div class="dashboard-panel mt-8">
  <h2 style="font-size:1.125rem;font-weight:600;margin:0 0 1rem">Assign Caregiver to Elder</h2>
  <form method="post" action="<?= e(PUBLIC_URL) ?>actions/admin_assign.php" class="grid-3" style="align-items:end">
    <?= csrfField() ?>
    <div>
      <label class="label">Caregiver</label>
      <select name="caregiver_id" class="input-field" required>
        <option value="">Select Caregiver</option>
        <?php foreach ($caregivers as $c): ?>
          <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="label">Elder</label>
      <select name="elder_id" class="input-field" required>
        <option value="">Select Elder</option>
        <?php foreach ($elders as $el): ?>
          <option value="<?= (int) $el['id'] ?>"><?= e($el['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div><button type="submit" class="btn-primary">Assign</button></div>
  </form>
</div>

<div class="dashboard-panel mt-8">
  <h2 style="font-size:1.125rem;font-weight:600;margin:0 0 1rem">User Management</h2>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><strong><?= e($u['name']) ?></strong></td>
          <td><?= e($u['email']) ?></td>
          <td style="text-transform:capitalize"><?= e($u['role']) ?></td>
          <td><span class="<?= $u['is_active'] ? 'badge-green' : 'badge-red' ?>"><?= $u['is_active'] ? 'Active' : 'Inactive' ?></span></td>
          <td>
            <form method="post" action="<?= e(PUBLIC_URL) ?>actions/admin_user.php" style="display:inline">
              <?= csrfField() ?>
              <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
              <input type="hidden" name="action" value="toggle">
              <button type="submit" class="btn-link"><?= $u['is_active'] ? 'Deactivate' : 'Activate' ?></button>
            </form>
            <?php if ($u['role'] !== 'admin'): ?>
            <form method="post" action="<?= e(PUBLIC_URL) ?>actions/admin_user.php" style="display:inline;margin-left:0.5rem">
              <?= csrfField() ?>
              <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
              <input type="hidden" name="action" value="delete">
              <button type="submit" class="btn-link danger" data-confirm="Delete this user?">Delete</button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
