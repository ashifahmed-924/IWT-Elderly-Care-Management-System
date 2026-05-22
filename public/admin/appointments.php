<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
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
$pageTitle = 'Appointments';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="flex-between">
  <div>
    <h1 class="page-title">Appointments</h1>
    <p class="page-sub">Manage all appointments</p>
  </div>
  <?php if (!$showForm): ?>
    <a href="?new=1" class="btn-primary">+ New Appointment</a>
  <?php else: ?>
    <a href="appointments.php" class="btn-secondary">Cancel</a>
  <?php endif; ?>
</div>

<?php if ($showForm): ?>
<div class="dashboard-panel mt-6">
  <h2 style="font-size:1.125rem;font-weight:600"><?= $editAppt ? 'Edit Appointment' : 'Create Appointment' ?></h2>
  <form method="post" action="<?= e(PUBLIC_URL) ?>actions/admin_appointment.php" class="grid-2 mt-4">
    <?= csrfField() ?>
    <?php if ($editAppt): ?><input type="hidden" name="id" value="<?= (int) $editAppt['id'] ?>"><?php endif; ?>
    <div>
      <label class="label">Elder</label>
      <select name="elder_id" class="input-field" required>
        <option value="">Select elder</option>
        <?php foreach ($elders as $el): ?>
          <option value="<?= (int) $el['id'] ?>" <?= ($editAppt && (int)$editAppt['elder_id'] === (int)$el['id']) ? 'selected' : '' ?>><?= e($el['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="label">Caregiver</label>
      <select name="caregiver_id" class="input-field">
        <option value="">Select caregiver</option>
        <?php foreach ($caregivers as $c): ?>
          <option value="<?= (int) $c['id'] ?>" <?= ($editAppt && (int)$editAppt['caregiver_id'] === (int)$c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
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
        <?php foreach (['scheduled','completed','cancelled','rescheduled'] as $s): ?>
          <option value="<?= $s ?>" <?= ($editAppt['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="grid-column:1/-1">
      <label class="label">Description</label>
      <textarea name="description" class="input-field" rows="2"><?= e($editAppt['description'] ?? '') ?></textarea>
    </div>
    <div><button type="submit" class="btn-primary"><?= $editAppt ? 'Update' : 'Create' ?></button></div>
  </form>
</div>
<?php endif; ?>

<div class="dashboard-panel mt-8">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Title</th><th>Elder</th><th>Caregiver</th><th>Date</th><th>Time</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($appointments as $a): ?>
        <tr>
          <td><?= e($a['title']) ?></td>
          <td><?= e($a['elder_name']) ?></td>
          <td><?= e($a['caregiver_name'] ?? '—') ?></td>
          <td><?= e($a['appt_date']) ?></td>
          <td><?= e($a['appt_time']) ?></td>
          <td><span class="<?= statusBadgeClass($a['status']) ?>"><?= e($a['status']) ?></span></td>
          <td>
            <a href="?edit=<?= (int) $a['id'] ?>">Edit</a>
            <form method="post" action="<?= e(PUBLIC_URL) ?>actions/admin_appointment.php" style="display:inline;margin-left:0.5rem">
              <?= csrfField() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
              <button type="submit" class="btn-link danger" data-confirm="Delete this appointment?">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
