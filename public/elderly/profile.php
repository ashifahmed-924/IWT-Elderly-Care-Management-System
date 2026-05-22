<?php
require_once __DIR__ . '/../../includes/auth.php';
$user = requireRole('elderly');
$pdo = getDb();

$stmt = $pdo->prepare("
  SELECT e.*, cg.name AS caregiver_name
  FROM elders e
  LEFT JOIN users cg ON e.assigned_caregiver_id = cg.id
  WHERE e.user_id = ?
");
$stmt->execute([$user['id']]);
$profile = $stmt->fetch();

if (!$profile) {
    flash('error', 'Profile not found.');
    redirect('index.php');
}

$elderId = (int) $profile['id'];

$appts = $pdo->prepare("
  SELECT a.*, cu.name AS caregiver_name
  FROM appointments a
  LEFT JOIN users cu ON a.caregiver_id = cu.id
  WHERE a.elder_id = ?
  ORDER BY a.appt_date ASC
  LIMIT 5
");
$appts->execute([$elderId]);
$appointments = $appts->fetchAll();

$rec = $pdo->prepare('SELECT hr.*, u.name AS recorder_name FROM health_records hr JOIN users u ON hr.recorded_by = u.id WHERE hr.elder_id = ? ORDER BY hr.record_date DESC LIMIT 5');
$rec->execute([$elderId]);
$records = $rec->fetchAll();

$pageTitle = 'My Profile';
require_once __DIR__ . '/../../includes/header.php';
?>
<h1 class="page-title">My Profile</h1>
<p class="page-sub">View and update your health information</p>

<div class="mt-4" style="display:flex;flex-wrap:wrap;gap:0.75rem;align-items:center">
  <span class="<?= statusBadgeClass($profile['health_status']) ?>">Health: <?= e($profile['health_status']) ?></span>
  <?php if ($profile['caregiver_name']): ?>
    <span style="font-size:0.875rem;color:var(--slate-600)">Caregiver: <strong><?= e($profile['caregiver_name']) ?></strong></span>
  <?php endif; ?>
</div>

<form method="post" action="<?= e(PUBLIC_URL) ?>actions/elderly_profile.php" class="mt-8">
  <?= csrfField() ?>
  <div class="grid-2">
    <div class="caregiver-panel form-stack">
      <h2 style="font-size:1.125rem;font-weight:600;margin:0">Personal Details</h2>
      <div>
        <label class="label">Date of Birth</label>
        <input type="date" name="date_of_birth" class="input-field" value="<?= e($profile['date_of_birth']) ?>">
      </div>
      <div>
        <label class="label">Address</label>
        <input type="text" name="address" class="input-field" value="<?= e($profile['address']) ?>">
      </div>
      <div>
        <label class="label">Blood Type</label>
        <input type="text" name="blood_type" class="input-field" value="<?= e($profile['blood_type']) ?>" placeholder="e.g. O+">
      </div>
    </div>

    <div class="caregiver-panel form-stack">
      <h2 style="font-size:1.125rem;font-weight:600;margin:0">Health Details</h2>
      <div>
        <label class="label">Allergies (comma-separated)</label>
        <input type="text" name="allergies" class="input-field" value="<?= e($profile['allergies']) ?>">
      </div>
      <div>
        <label class="label">Medications</label>
        <input type="text" name="medications" class="input-field" value="<?= e($profile['medications']) ?>">
      </div>
      <div>
        <label class="label">Medical Conditions</label>
        <input type="text" name="conditions" class="input-field" value="<?= e($profile['conditions']) ?>">
      </div>
    </div>

    <div class="caregiver-panel form-stack" style="grid-column:1/-1">
      <h2 style="font-size:1.125rem;font-weight:600;margin:0">Emergency Contact</h2>
      <div class="grid-3">
        <div><label class="label">Name</label><input name="ec_name" class="input-field" value="<?= e($profile['ec_name']) ?>"></div>
        <div><label class="label">Phone</label><input name="ec_phone" class="input-field" value="<?= e($profile['ec_phone']) ?>"></div>
        <div><label class="label">Relationship</label><input name="ec_relationship" class="input-field" value="<?= e($profile['ec_relationship']) ?>"></div>
      </div>
    </div>
  </div>
  <div class="mt-4"><button type="submit" class="btn-primary">Save Profile</button></div>
</form>

<div class="grid-2 mt-8">
  <div class="caregiver-panel">
    <div class="flex-between">
      <h2 style="font-size:1.125rem;font-weight:600;margin:0">My Appointments</h2>
      <a href="<?= e(PUBLIC_URL) ?>elderly/appointments.php" style="font-size:0.875rem">View all</a>
    </div>
    <?php if (count($appointments) === 0): ?>
      <p style="font-size:0.875rem;color:var(--slate-500);margin-top:1rem">No appointments scheduled.</p>
    <?php else: ?>
      <ul style="list-style:none;padding:0;margin-top:1rem">
        <?php foreach ($appointments as $a): ?>
        <li class="caregiver-panel-item" style="margin-bottom:0.75rem">
          <p style="font-weight:500;margin:0"><?= e($a['title']) ?></p>
          <p style="font-size:0.875rem;color:var(--slate-500);margin:0.25rem 0 0"><?= e($a['appt_date']) ?> at <?= e($a['appt_time']) ?></p>
          <span class="<?= statusBadgeClass($a['status']) ?>" style="margin-top:0.25rem"><?= e($a['status']) ?></span>
        </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <div class="caregiver-panel">
    <h2 style="font-size:1.125rem;font-weight:600;margin:0">Recent Health Records</h2>
    <?php if (count($records) === 0): ?>
      <p style="font-size:0.875rem;color:var(--slate-500);margin-top:1rem">No health records yet.</p>
    <?php else: ?>
      <ul style="list-style:none;padding:0;margin-top:1rem">
        <?php foreach ($records as $r): ?>
        <li class="caregiver-panel-item" style="margin-bottom:0.75rem">
          <p style="font-size:0.875rem;color:var(--slate-500);margin:0"><?= e(date('n/j/Y', strtotime($r['record_date']))) ?> — by <?= e($r['recorder_name']) ?></p>
          <p style="margin:0.25rem 0 0;font-size:0.875rem">
            BP: <?= e($r['blood_pressure'] ?: '—') ?> | HR: <?= e($r['heart_rate'] ?: '—') ?> | Temp: <?= e($r['temperature'] ?: '—') ?>°F
          </p>
          <?php if ($r['notes']): ?><p style="margin:0.25rem 0 0;font-size:0.875rem;color:var(--slate-600)"><?= e($r['notes']) ?></p><?php endif; ?>
        </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
