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

$elderCount = count($elderList);
$recordCount = count($records);

$initials = '';
foreach (array_slice(preg_split('/\s+/', trim($user['name'])), 0, 2) as $part) {
    $initials .= strtoupper($part[0] ?? '');
}

$pageTitle = 'Caregiver Dashboard';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="caregiver-dashboard">
  <header class="cg-hero">
    <div class="cg-hero-main">
      <div class="cg-avatar" aria-hidden="true"><?= e($initials) ?></div>
      <div class="cg-hero-text">
        <p class="cg-hero-label">Caregiver Panel</p>
        <h1 class="cg-hero-name"><?= e($user['name']) ?></h1>
        <p class="cg-hero-sub">Manage assigned elders and health records</p>
      </div>
      <div class="cg-hero-tags">
        <span class="cg-tag"><i class="fa fa-users" aria-hidden="true"></i> <?= $elderCount ?> elder<?= $elderCount === 1 ? '' : 's' ?> assigned</span>
      </div>
    </div>
    <?php if ($selected): ?>
    <div class="cg-stats">
      <div class="cg-stat">
        <span class="cg-stat-icon"><i class="fa fa-user" aria-hidden="true"></i></span>
        <div>
          <p class="cg-stat-value"><?= e($selected['user_name']) ?></p>
          <p class="cg-stat-label">Selected elder</p>
        </div>
      </div>
      <div class="cg-stat">
        <span class="cg-stat-icon cg-stat-icon--green"><i class="fa fa-heartbeat" aria-hidden="true"></i></span>
        <div>
          <p class="cg-stat-value"><span class="<?= statusBadgeClass($selected['health_status']) ?>"><?= e($selected['health_status']) ?></span></p>
          <p class="cg-stat-label">Health status</p>
        </div>
      </div>
      <div class="cg-stat">
        <span class="cg-stat-icon cg-stat-icon--blue"><i class="fa fa-file-text-o" aria-hidden="true"></i></span>
        <div>
          <p class="cg-stat-value"><?= $recordCount ?></p>
          <p class="cg-stat-label">Health records</p>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </header>

  <?php if ($elderCount === 0): ?>
  <div class="cg-empty-page">
    <i class="fa fa-users" aria-hidden="true"></i>
    <h2>No elders assigned yet</h2>
    <p>Contact an admin to get elder assignments.</p>
  </div>
  <?php else: ?>
  <div class="cg-layout">
    <aside class="cg-sidebar">
      <div class="cg-sidebar-head">
        <span class="cg-sidebar-icon"><i class="fa fa-blind" aria-hidden="true"></i></span>
        <h2>Assigned Elders</h2>
      </div>
      <ul class="cg-elder-list">
        <?php foreach ($elderList as $e): ?>
        <li>
          <a href="?elder_id=<?= (int) $e['id'] ?>" class="cg-elder-item <?= $selectedId === (int) $e['id'] ? 'active' : '' ?>">
            <span class="cg-elder-avatar" aria-hidden="true"><?= e(strtoupper(($e['user_name'][0] ?? 'E'))) ?></span>
            <span class="cg-elder-info">
              <span class="cg-elder-name"><?= e($e['user_name']) ?></span>
              <span class="<?= statusBadgeClass($e['health_status']) ?>"><?= e($e['health_status']) ?></span>
            </span>
            <i class="fa fa-chevron-right cg-elder-arrow" aria-hidden="true"></i>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
    </aside>

    <?php if ($selected): ?>
    <main class="cg-main">
      <section class="cg-elder-header">
        <div class="cg-elder-header-top">
          <span class="cg-elder-header-icon"><i class="fa fa-id-card-o" aria-hidden="true"></i></span>
          <div>
            <h2><?= e($selected['user_name']) ?></h2>
            <p><?= e($selected['user_email']) ?></p>
          </div>
          <span class="<?= statusBadgeClass($selected['health_status']) ?> cg-elder-status"><?= e($selected['health_status']) ?></span>
        </div>
        <div class="cg-info-grid">
          <div class="cg-info-item"><i class="fa fa-tint" aria-hidden="true"></i><span><strong>Blood Type</strong><?= e($selected['blood_type'] ?: '—') ?></span></div>
          <div class="cg-info-item"><i class="fa fa-exclamation-circle" aria-hidden="true"></i><span><strong>Allergies</strong><?= e($selected['allergies'] ?: '—') ?></span></div>
          <div class="cg-info-item"><i class="fa fa-medkit" aria-hidden="true"></i><span><strong>Medications</strong><?= e($selected['medications'] ?: '—') ?></span></div>
          <div class="cg-info-item"><i class="fa fa-heartbeat" aria-hidden="true"></i><span><strong>Conditions</strong><?= e($selected['conditions'] ?: '—') ?></span></div>
        </div>
      </section>

      <form method="post" action="<?= e(PUBLIC_URL) ?>actions/caregiver_health.php" class="cg-card cg-card--teal">
        <?= csrfField() ?>
        <input type="hidden" name="elder_id" value="<?= (int) $selected['id'] ?>">
        <div class="cg-card-head">
          <span class="cg-card-icon"><i class="fa fa-heartbeat" aria-hidden="true"></i></span>
          <h2>Update Health Status &amp; Notes</h2>
        </div>
        <div class="cg-card-body">
          <div class="grid-2">
            <div>
              <label class="label">Health Status</label>
              <select name="health_status" class="input-field">
                <?php foreach (['stable', 'monitoring', 'critical', 'recovering'] as $s): ?>
                  <option value="<?= $s ?>" <?= $selected['health_status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="label">Care Notes</label>
              <input type="text" name="notes" class="input-field" value="<?= e($selected['notes']) ?>" placeholder="General care notes...">
            </div>
          </div>
          <div class="cg-card-actions">
            <button type="submit" class="btn-primary cg-btn"><i class="fa fa-check" aria-hidden="true"></i> Update Status</button>
          </div>
        </div>
      </form>

      <form method="post" action="<?= e(PUBLIC_URL) ?>actions/caregiver_record.php" class="cg-card cg-card--blue">
        <?= csrfField() ?>
        <input type="hidden" name="elder_id" value="<?= (int) $selected['id'] ?>">
        <div class="cg-card-head">
          <span class="cg-card-icon"><i class="fa fa-plus-circle" aria-hidden="true"></i></span>
          <h2>Add Health Record</h2>
        </div>
        <div class="cg-card-body form-stack">
          <div class="grid-3">
            <div><label class="label">Blood Pressure</label><input name="blood_pressure" class="input-field" placeholder="120/80"></div>
            <div><label class="label">Heart Rate</label><input name="heart_rate" type="number" class="input-field" placeholder="72"></div>
            <div><label class="label">Temperature (°F)</label><input name="temperature" type="number" step="0.1" class="input-field" placeholder="98.6"></div>
            <div><label class="label">Weight (lbs)</label><input name="weight" type="number" step="0.1" class="input-field" placeholder="150"></div>
            <div><label class="label">Blood Sugar</label><input name="blood_sugar" type="number" class="input-field" placeholder="100"></div>
            <div><label class="label">Oxygen %</label><input name="oxygen_level" type="number" class="input-field" placeholder="98"></div>
          </div>
          <div><label class="label">Notes</label><textarea name="notes" class="input-field" rows="2"></textarea></div>
          <div class="cg-card-actions">
            <button type="submit" class="btn-primary cg-btn"><i class="fa fa-plus" aria-hidden="true"></i> Add Record</button>
          </div>
        </div>
      </form>

      <section class="cg-feed">
        <div class="cg-feed-head">
          <span class="cg-feed-icon"><i class="fa fa-history" aria-hidden="true"></i></span>
          <h2>Health Record History</h2>
        </div>
        <?php if ($recordCount === 0): ?>
        <div class="cg-empty">
          <i class="fa fa-file-text-o" aria-hidden="true"></i>
          <p>No records yet.</p>
        </div>
        <?php else: ?>
        <ul class="cg-timeline">
          <?php foreach ($records as $r): ?>
          <li class="cg-timeline-item">
            <div class="cg-timeline-dot"></div>
            <div class="cg-timeline-content">
              <p class="cg-timeline-date">
                <i class="fa fa-calendar" aria-hidden="true"></i>
                <?= e(date('M j, Y · g:i A', strtotime($r['record_date']))) ?>
              </p>
              <div class="cg-vitals">
                <span><i class="fa fa-heart" aria-hidden="true"></i> BP <?= e($r['blood_pressure'] ?: '—') ?></span>
                <span><i class="fa fa-heartbeat" aria-hidden="true"></i> HR <?= e($r['heart_rate'] ?: '—') ?></span>
                <span><i class="fa fa-thermometer-half" aria-hidden="true"></i> <?= e($r['temperature'] ?: '—') ?>°F</span>
                <span><i class="fa fa-tint" aria-hidden="true"></i> O₂ <?= e($r['oxygen_level'] ?: '—') ?>%</span>
              </div>
              <?php if ($r['notes']): ?>
              <p class="cg-timeline-note"><?= e($r['notes']) ?></p>
              <?php endif; ?>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </section>
    </main>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
