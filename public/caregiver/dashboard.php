<?php
require_once __DIR__ . '/../../includes/auth.php';
$user = requireRole('caregiver');
$pdo = getDb();

$elders = $pdo->prepare("
  SELECT e.*, u.name AS user_name, u.email AS user_email
  FROM elders e
  JOIN users u ON e.user_id = u.id
  WHERE e.assigned_caregiver_id = ?
  ORDER BY e.updated_at DESC
");
$elders->execute([$user['id']]);
$elderList = $elders->fetchAll();

$selectedId = (int) ($_GET['elder_id'] ?? 0);
if (!$selectedId && count($elderList) > 0) {
    $selectedId = (int) $elderList[0]['id'];
}

$selected = null;
$records = [];
if ($selectedId) {
    foreach ($elderList as $e) {
        if ((int) $e['id'] === $selectedId) {
            $selected = $e;
            break;
        }
    }
    if ($selected) {
        $rec = $pdo->prepare('SELECT hr.*, u.name AS recorder_name FROM health_records hr JOIN users u ON hr.recorded_by = u.id WHERE hr.elder_id = ? ORDER BY hr.record_date DESC');
        $rec->execute([$selectedId]);
        $records = $rec->fetchAll();
    }
}

$pageTitle = 'Caregiver Dashboard';
require_once __DIR__ . '/../../includes/header.php';
?>
<h1 class="page-title">Caregiver Dashboard</h1>
<p class="page-sub">Manage assigned elders and health records</p>

<?php if (count($elderList) === 0): ?>
<div class="caregiver-panel mt-8 text-center" style="color:var(--slate-500)">
  No elders assigned to you yet. Contact an admin for assignments.
</div>
<?php else: ?>
<div class="grid-2 lg-cols-4 mt-8" style="grid-template-columns:1fr">
  <div style="display:grid;gap:1.5rem;grid-template-columns:minmax(220px,1fr) 2fr">
    <div class="caregiver-panel">
      <h2 style="font-size:1rem;font-weight:600;margin:0 0 1rem">Assigned Elders</h2>
      <ul class="elder-list">
        <?php foreach ($elderList as $e): ?>
        <li>
          <a href="?elder_id=<?= (int) $e['id'] ?>" class="<?= $selectedId === (int)$e['id'] ? 'active' : '' ?>">
            <div style="font-weight:500"><?= e($e['user_name']) ?></div>
            <div class="sub"><?= e($e['health_status']) ?></div>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <?php if ($selected): ?>
    <div>
      <div class="caregiver-panel">
        <h2 style="font-size:1.125rem;font-weight:600;margin:0"><?= e($selected['user_name']) ?></h2>
        <p class="page-sub"><?= e($selected['user_email']) ?></p>
        <div class="grid-2 mt-4" style="font-size:0.875rem">
          <p><span style="color:var(--slate-500)">Blood Type:</span> <?= e($selected['blood_type'] ?: '—') ?></p>
          <p><span style="color:var(--slate-500)">Allergies:</span> <?= e($selected['allergies'] ?: '—') ?></p>
          <p><span style="color:var(--slate-500)">Medications:</span> <?= e($selected['medications'] ?: '—') ?></p>
          <p><span style="color:var(--slate-500)">Conditions:</span> <?= e($selected['conditions'] ?: '—') ?></p>
        </div>
      </div>

      <form method="post" action="<?= e(PUBLIC_URL) ?>actions/caregiver_health.php" class="caregiver-panel mt-6 form-stack">
        <?= csrfField() ?>
        <input type="hidden" name="elder_id" value="<?= (int) $selected['id'] ?>">
        <h2 style="font-size:1rem;font-weight:600;margin:0">Update Health Status &amp; Notes</h2>
        <div class="grid-2">
          <div>
            <label class="label">Health Status</label>
            <select name="health_status" class="input-field">
              <?php foreach (['stable','monitoring','critical','recovering'] as $s): ?>
                <option value="<?= $s ?>" <?= $selected['health_status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="label">Care Notes</label>
            <input type="text" name="notes" class="input-field" value="<?= e($selected['notes']) ?>" placeholder="General care notes...">
          </div>
        </div>
        <button type="submit" class="btn-primary">Update Status</button>
      </form>

      <form method="post" action="<?= e(PUBLIC_URL) ?>actions/caregiver_record.php" class="caregiver-panel mt-6 form-stack">
        <?= csrfField() ?>
        <input type="hidden" name="elder_id" value="<?= (int) $selected['id'] ?>">
        <h2 style="font-size:1rem;font-weight:600;margin:0">Add Health Record</h2>
        <div class="grid-3">
          <div><label class="label">Blood Pressure</label><input name="blood_pressure" class="input-field" placeholder="120/80"></div>
          <div><label class="label">Heart Rate</label><input name="heart_rate" type="number" class="input-field" placeholder="72"></div>
          <div><label class="label">Temperature (°F)</label><input name="temperature" type="number" step="0.1" class="input-field" placeholder="98.6"></div>
          <div><label class="label">Weight (lbs)</label><input name="weight" type="number" step="0.1" class="input-field" placeholder="150"></div>
          <div><label class="label">Blood Sugar</label><input name="blood_sugar" type="number" class="input-field" placeholder="100"></div>
          <div><label class="label">Oxygen %</label><input name="oxygen_level" type="number" class="input-field" placeholder="98"></div>
        </div>
        <div><label class="label">Notes</label><textarea name="notes" class="input-field" rows="2"></textarea></div>
        <button type="submit" class="btn-primary">Add Record</button>
      </form>

      <div class="caregiver-panel mt-6">
        <h2 style="font-size:1rem;font-weight:600;margin:0 0 1rem">Health Record History</h2>
        <?php if (count($records) === 0): ?>
          <p style="font-size:0.875rem;color:var(--slate-500)">No records yet.</p>
        <?php else: ?>
          <ul style="list-style:none;padding:0;margin:0">
            <?php foreach ($records as $r): ?>
            <li class="caregiver-panel-item" style="margin-bottom:0.75rem">
              <p style="font-weight:500;margin:0"><?= e(date('n/j/Y, g:i:s A', strtotime($r['record_date']))) ?></p>
              <p style="margin:0.25rem 0 0;font-size:0.875rem;color:var(--slate-600)">
                BP: <?= e($r['blood_pressure'] ?: '—') ?> | HR: <?= e($r['heart_rate'] ?: '—') ?> | Temp: <?= e($r['temperature'] ?: '—') ?>°F | O₂: <?= e($r['oxygen_level'] ?: '—') ?>%
              </p>
              <?php if ($r['notes']): ?><p style="margin:0.25rem 0 0;font-size:0.875rem;color:var(--slate-500)"><?= e($r['notes']) ?></p><?php endif; ?>
            </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
