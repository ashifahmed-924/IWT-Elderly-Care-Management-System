<?php
require_once __DIR__ . '/../../includes/auth.php';
$user = requireRole('admin');
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

$activeUsers = count(array_filter($users, fn($u) => $u['is_active']));

$initials = '';
foreach (array_slice(preg_split('/\s+/', trim($user['name'])), 0, 2) as $part) {
    $initials .= strtoupper($part[0] ?? '');
}

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="admin-dashboard">
  <header class="adm-dash-hero">
    <div class="adm-dash-hero-main">
      <div class="adm-dash-avatar" aria-hidden="true"><?= e($initials) ?></div>
      <div class="adm-dash-hero-text">
        <p class="adm-dash-hero-label">Admin Panel</p>
        <h1 class="adm-dash-hero-name">Dashboard</h1>
        <p class="adm-dash-hero-sub">Manage users, elders, and assignments</p>
      </div>
      <div class="adm-dash-hero-actions">
        <a href="<?= e(PUBLIC_URL) ?>admin/appointments.php" class="btn-secondary adm-dash-link">
          <i class="fa fa-calendar" aria-hidden="true"></i> Appointments
        </a>
      </div>
    </div>
    <div class="adm-dash-stats">
      <div class="adm-dash-stat">
        <span class="adm-dash-stat-icon"><i class="fa fa-user-circle-o" aria-hidden="true"></i></span>
        <div>
          <p class="adm-dash-stat-value"><?= (int) $stats['totalUsers'] ?></p>
          <p class="adm-dash-stat-label">Total Users</p>
        </div>
      </div>
      <div class="adm-dash-stat">
        <span class="adm-dash-stat-icon adm-dash-stat-icon--green"><i class="fa fa-blind" aria-hidden="true"></i></span>
        <div>
          <p class="adm-dash-stat-value"><?= (int) $stats['elders'] ?></p>
          <p class="adm-dash-stat-label">Elderly Users</p>
        </div>
      </div>
      <div class="adm-dash-stat">
        <span class="adm-dash-stat-icon adm-dash-stat-icon--amber"><i class="fa fa-female" aria-hidden="true"></i></span>
        <div>
          <p class="adm-dash-stat-value"><?= (int) $stats['caregivers'] ?></p>
          <p class="adm-dash-stat-label">Caregivers</p>
        </div>
      </div>
      <div class="adm-dash-stat">
        <span class="adm-dash-stat-icon adm-dash-stat-icon--purple"><i class="fa fa-heart" aria-hidden="true"></i></span>
        <div>
          <p class="adm-dash-stat-value"><?= (int) $stats['appointments'] ?></p>
          <p class="adm-dash-stat-label">Appointments</p>
        </div>
      </div>
    </div>
  </header>

  <section class="adm-dash-card adm-dash-card--assign">
    <div class="adm-dash-card-head">
      <span class="adm-dash-card-icon"><i class="fa fa-link" aria-hidden="true"></i></span>
      <h2>Assign Caregiver to Elder</h2>
    </div>
    <form method="post" action="<?= e(PUBLIC_URL) ?>actions/admin_assign.php" class="adm-dash-assign-form">
      <?= csrfField() ?>
      <div class="adm-dash-assign-fields">
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
      </div>
      <button type="submit" class="btn-primary adm-dash-btn"><i class="fa fa-check" aria-hidden="true"></i> Assign</button>
    </form>
  </section>

  <section class="adm-dash-users">
    <div class="adm-dash-users-head">
      <span class="adm-dash-users-icon"><i class="fa fa-users" aria-hidden="true"></i></span>
      <h2>User Management</h2>
      <span class="adm-dash-users-count"><?= count($users) ?> users · <?= $activeUsers ?> active</span>
    </div>

    <?php if (count($users) === 0): ?>
    <div class="adm-dash-empty">
      <i class="fa fa-users" aria-hidden="true"></i>
      <p>No users found.</p>
    </div>
    <?php else: ?>
    <ul class="adm-dash-user-list">
      <?php foreach ($users as $u):
        $roleIcon = match ($u['role']) {
          'admin' => 'fa-shield',
          'caregiver' => 'fa-user-md',
          'elderly' => 'fa-blind',
          default => 'fa-user',
        };
        $userInitial = strtoupper($u['name'][0] ?? 'U');
      ?>
      <li class="adm-dash-user-card">
        <div class="adm-dash-user-main">
          <span class="adm-dash-user-avatar" aria-hidden="true"><?= e($userInitial) ?></span>
          <div class="adm-dash-user-info">
            <p class="adm-dash-user-name"><?= e($u['name']) ?></p>
            <p class="adm-dash-user-email"><i class="fa fa-envelope-o" aria-hidden="true"></i> <?= e($u['email']) ?></p>
            <?php if ($u['phone']): ?>
            <p class="adm-dash-user-phone"><i class="fa fa-phone" aria-hidden="true"></i> <?= e($u['phone']) ?></p>
            <?php endif; ?>
          </div>
          <div class="adm-dash-user-tags">
            <span class="adm-dash-role adm-dash-role--<?= e($u['role']) ?>">
              <i class="fa <?= $roleIcon ?>" aria-hidden="true"></i> <?= e($u['role']) ?>
            </span>
            <span class="<?= $u['is_active'] ? 'badge-green' : 'badge-red' ?>"><?= $u['is_active'] ? 'Active' : 'Inactive' ?></span>
          </div>
        </div>
        <div class="adm-dash-user-actions">
          <form method="post" action="<?= e(PUBLIC_URL) ?>actions/admin_user.php">
            <?= csrfField() ?>
            <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
            <input type="hidden" name="action" value="toggle">
            <button type="submit" class="adm-action-btn adm-action-toggle">
              <i class="fa fa-<?= $u['is_active'] ? 'ban' : 'check' ?>" aria-hidden="true"></i>
              <?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>
            </button>
          </form>
          <?php if ($u['role'] !== 'admin'): ?>
          <form method="post" action="<?= e(PUBLIC_URL) ?>actions/admin_user.php">
            <?= csrfField() ?>
            <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
            <input type="hidden" name="action" value="delete">
            <button type="submit" class="adm-action-btn adm-action-delete" data-confirm="Delete this user?">
              <i class="fa fa-trash" aria-hidden="true"></i> Delete
            </button>
          </form>
          <?php endif; ?>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </section>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
