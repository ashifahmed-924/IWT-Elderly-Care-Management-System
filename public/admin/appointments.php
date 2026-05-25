<?php
require_once __DIR__ . '/../../includes/auth.php';
$user = requireRole('admin');
$pdo = getDb();

$editId = (int) ($_GET['edit'] ?? 0);
$editAppt = null;
if ($editId) {
    $stmt = $pdo->prepare('SELECT * FROM appointments WHERE id = ?');
    $stmt->execute([$editId]);
    $editAppt = $stmt->fetch();
}

$elders = $pdo->query('SELECT e.id, u.name FROM elders e JOIN users u ON e.user_id = u.id ORDER BY u.name')->fetchAll();
$caregivers = $pdo->query("SELECT id, name FROM users WHERE role = 'caregiver' ORDER BY name")->fetchAll();
$appointments = $pdo->query("
  SELECT a.*, eu.name AS elder_name, cu.name AS caregiver_name
  FROM appointments a
  JOIN elders e ON a.elder_id = e.id
  JOIN users eu ON e.user_id = eu.id
  LEFT JOIN users cu ON a.caregiver_id = cu.id
  ORDER BY a.appt_date ASC
")->fetchAll();

$showForm = isset($_GET['new']) || $editAppt;
$totalCount = count($appointments);
$scheduledCount = count(array_filter($appointments, fn($a) => $a['status'] === 'scheduled'));
$completedCount = count(array_filter($appointments, fn($a) => $a['status'] === 'completed'));

$initials = '';
foreach (array_slice(preg_split('/\s+/', trim($user['name'])), 0, 2) as $part) {
    $initials .= strtoupper($part[0] ?? '');
}

$pageTitle = 'Appointments';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="admin-appointments">
  <header class="adm-hero">
    <div class="adm-hero-main">
      <div class="adm-avatar" aria-hidden="true"><?= e($initials) ?></div>
      <div class="adm-hero-text">
        <p class="adm-hero-label">Admin Panel</p>
        <h1 class="adm-hero-name">Appointments</h1>
        <p class="adm-hero-sub">Manage all appointments across the platform</p>
      </div>
      <div class="adm-hero-actions">
        <?php if (!$showForm): ?>
          <a href="?new=1" class="btn-primary adm-btn-new"><i class="fa fa-plus" aria-hidden="true"></i> New Appointment</a>
        <?php else: ?>
          <a href="appointments.php" class="btn-secondary adm-btn-cancel"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
        <?php endif; ?>
      </div>
    </div>
    <div class="adm-stats">
      <div class="adm-stat">
        <span class="adm-stat-icon"><i class="fa fa-calendar" aria-hidden="true"></i></span>
        <div>
          <p class="adm-stat-value"><?= $totalCount ?></p>
          <p class="adm-stat-label">Total</p>
        </div>
      </div>
      <div class="adm-stat">
        <span class="adm-stat-icon adm-stat-icon--blue"><i class="fa fa-clock-o" aria-hidden="true"></i></span>
        <div>
          <p class="adm-stat-value"><?= $scheduledCount ?></p>
          <p class="adm-stat-label">Scheduled</p>
        </div>
      </div>
      <div class="adm-stat">
        <span class="adm-stat-icon adm-stat-icon--green"><i class="fa fa-check-circle" aria-hidden="true"></i></span>
        <div>
          <p class="adm-stat-value"><?= $completedCount ?></p>
          <p class="adm-stat-label">Completed</p>
        </div>
      </div>
    </div>
  </header>

  <?php if ($showForm): ?>
  <section class="adm-form-card">
    <div class="adm-form-head">
      <span class="adm-form-icon"><i class="fa fa-<?= $editAppt ? 'pencil' : 'plus-circle' ?>" aria-hidden="true"></i></span>
      <h2><?= $editAppt ? 'Edit Appointment' : 'Create Appointment' ?></h2>
    </div>
    <form method="post" action="<?= e(PUBLIC_URL) ?>actions/admin_appointment.php" class="adm-form-body">
      <?= csrfField() ?>
      <?php if ($editAppt): ?><input type="hidden" name="id" value="<?= (int) $editAppt['id'] ?>"><?php endif; ?>
      <div class="grid-2">
        <div>
          <label class="label">Elder</label>
          <select name="elder_id" class="input-field" required>
            <option value="">Select elder</option>
            <?php foreach ($elders as $el): ?>
              <option value="<?= (int) $el['id'] ?>" <?= ($editAppt && (int) $editAppt['elder_id'] === (int) $el['id']) ? 'selected' : '' ?>><?= e($el['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="label">Caregiver</label>
          <select name="caregiver_id" class="input-field">
            <option value="">Select caregiver</option>
            <?php foreach ($caregivers as $c): ?>
              <option value="<?= (int) $c['id'] ?>" <?= ($editAppt && (int) $editAppt['caregiver_id'] === (int) $c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="label">Title</label>
          <input type="text" name="title" class="input-field" required value="<?= e($editAppt['title'] ?? '') ?>">
        </div>
        <div>
          <label class="label">Date</label>
          <input type="date" name="appt_date" class="input-field" required value="<?= e($editAppt['appt_date'] ?? '') ?>">
        </div>
        <div>
          <label class="label">Time</label>
          <input type="text" name="appt_time" class="input-field" required value="<?= e($editAppt['appt_time'] ?? '') ?>">
        </div>
        <div>
          <label class="label">Location</label>
          <input type="text" name="location" class="input-field" value="<?= e($editAppt['location'] ?? '') ?>">
        </div>
        <div>
          <label class="label">Status</label>
          <select name="status" class="input-field">
            <?php foreach (['scheduled', 'completed', 'cancelled', 'rescheduled'] as $s): ?>
              <option value="<?= $s ?>" <?= ($editAppt['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="adm-form-wide">
          <label class="label">Description</label>
          <textarea name="description" class="input-field" rows="2"><?= e($editAppt['description'] ?? '') ?></textarea>
        </div>
      </div>
      <div class="adm-form-actions">
        <button type="submit" class="btn-primary adm-btn">
          <i class="fa fa-check" aria-hidden="true"></i> <?= $editAppt ? 'Update Appointment' : 'Create Appointment' ?>
        </button>
      </div>
    </form>
  </section>
  <?php endif; ?>

  <section class="adm-list-section">
    <div class="adm-list-head">
      <span class="adm-list-icon"><i class="fa fa-calendar-check-o" aria-hidden="true"></i></span>
      <h2>All Appointments</h2>
      <span class="adm-list-count"><?= $totalCount ?> total</span>
    </div>

    <?php if ($totalCount === 0): ?>
    <div class="adm-empty">
      <i class="fa fa-calendar-o" aria-hidden="true"></i>
      <p>No appointments yet. Create one to get started.</p>
      <?php if (!$showForm): ?>
      <a href="?new=1" class="btn-primary adm-btn"><i class="fa fa-plus" aria-hidden="true"></i> New Appointment</a>
      <?php endif; ?>
    </div>
    <?php else: ?>
    <ul class="adm-appt-list">
      <?php foreach ($appointments as $a): ?>
      <li class="adm-appt-card">
        <div class="adm-appt-card-main">
          <div class="adm-appt-title-row">
            <h3><?= e($a['title']) ?></h3>
            <span class="<?= statusBadgeClass($a['status']) ?>"><?= e($a['status']) ?></span>
          </div>
          <?php if ($a['description']): ?>
          <p class="adm-appt-desc"><?= e($a['description']) ?></p>
          <?php endif; ?>
          <div class="adm-appt-meta">
            <span><i class="fa fa-blind" aria-hidden="true"></i> <?= e($a['elder_name']) ?></span>
            <span><i class="fa fa-user-md" aria-hidden="true"></i> <?= e($a['caregiver_name'] ?? '—') ?></span>
            <span><i class="fa fa-calendar" aria-hidden="true"></i> <?= e($a['appt_date']) ?></span>
            <span><i class="fa fa-clock-o" aria-hidden="true"></i> <?= e($a['appt_time']) ?></span>
            <?php if ($a['location']): ?>
            <span><i class="fa fa-map-marker" aria-hidden="true"></i> <?= e($a['location']) ?></span>
            <?php endif; ?>
          </div>
        </div>
        <div class="adm-appt-actions">
          <a href="?edit=<?= (int) $a['id'] ?>" class="adm-action-btn adm-action-edit"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</a>
          <form method="post" action="<?= e(PUBLIC_URL) ?>actions/admin_appointment.php">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
            <button type="submit" class="adm-action-btn adm-action-delete" data-confirm="Delete this appointment?"><i class="fa fa-trash" aria-hidden="true"></i> Delete</button>
          </form>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </section>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
