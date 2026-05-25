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

$apptCount = $pdo->prepare('SELECT COUNT(*) FROM appointments WHERE elder_id = ?');
$apptCount->execute([$elderId]);
$totalAppointments = (int) $apptCount->fetchColumn();

$recordCount = $pdo->prepare('SELECT COUNT(*) FROM health_records WHERE elder_id = ?');
$recordCount->execute([$elderId]);
$totalRecords = (int) $recordCount->fetchColumn();

$initials = '';
foreach (array_slice(preg_split('/\s+/', trim($user['name'])), 0, 2) as $part) {
    $initials .= strtoupper($part[0] ?? '');
}

$pageTitle = 'My Profile';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="elderly-profile">
  <header class="profile-hero">
    <div class="profile-hero-main">
      <div class="profile-avatar" aria-hidden="true"><?= e($initials) ?></div>
      <div class="profile-hero-text">
        <p class="profile-hero-label">Welcome back</p>
        <h1 class="profile-hero-name"><?= e($user['name']) ?></h1>
        <p class="profile-hero-sub">View and update your health information</p>
      </div>
      <div class="profile-hero-tags">
        <span class="profile-tag profile-tag--health">
          <i class="fa fa-heartbeat" aria-hidden="true"></i>
          <span class="<?= statusBadgeClass($profile['health_status']) ?>"><?= e($profile['health_status']) ?></span>
        </span>
        <?php if ($profile['caregiver_name']): ?>
        <span class="profile-tag">
          <i class="fa fa-user-md" aria-hidden="true"></i>
          <?= e($profile['caregiver_name']) ?>
        </span>
        <?php endif; ?>
      </div>
    </div>
    <div class="profile-stats">
      <div class="profile-stat">
        <span class="profile-stat-icon"><i class="fa fa-calendar" aria-hidden="true"></i></span>
        <div>
          <p class="profile-stat-value"><?= $totalAppointments ?></p>
          <p class="profile-stat-label">Appointments</p>
        </div>
      </div>
      <div class="profile-stat">
        <span class="profile-stat-icon profile-stat-icon--green"><i class="fa fa-file-text-o" aria-hidden="true"></i></span>
        <div>
          <p class="profile-stat-value"><?= $totalRecords ?></p>
          <p class="profile-stat-label">Health Records</p>
        </div>
      </div>
      <?php if ($profile['blood_type']): ?>
      <div class="profile-stat">
        <span class="profile-stat-icon profile-stat-icon--red"><i class="fa fa-tint" aria-hidden="true"></i></span>
        <div>
          <p class="profile-stat-value"><?= e($profile['blood_type']) ?></p>
          <p class="profile-stat-label">Blood Type</p>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </header>

  <form method="post" action="<?= e(PUBLIC_URL) ?>actions/elderly_profile.php" class="profile-form">
    <?= csrfField() ?>
    <div class="profile-sections">
      <section class="profile-card profile-card--blue">
        <div class="profile-card-head">
          <span class="profile-card-icon"><i class="fa fa-user" aria-hidden="true"></i></span>
          <h2>Personal Details</h2>
        </div>
        <div class="profile-card-body form-stack">
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
      </section>

      <section class="profile-card profile-card--teal">
        <div class="profile-card-head">
          <span class="profile-card-icon"><i class="fa fa-medkit" aria-hidden="true"></i></span>
          <h2>Health Details</h2>
        </div>
        <div class="profile-card-body form-stack">
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
      </section>

      <section class="profile-card profile-card--amber profile-card--wide">
        <div class="profile-card-head">
          <span class="profile-card-icon"><i class="fa fa-phone" aria-hidden="true"></i></span>
          <h2>Emergency Contact</h2>
        </div>
        <div class="profile-card-body grid-3">
          <div><label class="label">Name</label><input name="ec_name" class="input-field" value="<?= e($profile['ec_name']) ?>"></div>
          <div><label class="label">Phone</label><input name="ec_phone" class="input-field" value="<?= e($profile['ec_phone']) ?>"></div>
          <div><label class="label">Relationship</label><input name="ec_relationship" class="input-field" value="<?= e($profile['ec_relationship']) ?>"></div>
        </div>
      </section>
    </div>

    <div class="profile-form-actions">
      <button type="submit" class="btn-primary profile-save-btn">
        <i class="fa fa-check" aria-hidden="true"></i> Save Profile
      </button>
    </div>
  </form>

  <div class="profile-feed-grid">
    <section class="profile-feed">
      <div class="profile-feed-head">
        <span class="profile-feed-icon"><i class="fa fa-calendar-check-o" aria-hidden="true"></i></span>
        <h2>My Appointments</h2>
        <a href="<?= e(PUBLIC_URL) ?>elderly/appointments.php" class="profile-feed-link">View all <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
      </div>
      <?php if (count($appointments) === 0): ?>
        <div class="profile-empty">
          <i class="fa fa-calendar-o" aria-hidden="true"></i>
          <p>No appointments scheduled.</p>
        </div>
      <?php else: ?>
        <ul class="profile-timeline">
          <?php foreach ($appointments as $a): ?>
          <li class="profile-timeline-item">
            <div class="profile-timeline-dot"></div>
            <div class="profile-timeline-content">
              <p class="profile-timeline-title"><?= e($a['title']) ?></p>
              <p class="profile-timeline-meta">
                <i class="fa fa-clock-o" aria-hidden="true"></i>
                <?= e($a['appt_date']) ?> at <?= e($a['appt_time']) ?>
              </p>
              <?php if ($a['caregiver_name']): ?>
              <p class="profile-timeline-meta"><i class="fa fa-user" aria-hidden="true"></i> <?= e($a['caregiver_name']) ?></p>
              <?php endif; ?>
              <span class="<?= statusBadgeClass($a['status']) ?>"><?= e($a['status']) ?></span>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <section class="profile-feed">
      <div class="profile-feed-head">
        <span class="profile-feed-icon profile-feed-icon--green"><i class="fa fa-heartbeat" aria-hidden="true"></i></span>
        <h2>Recent Health Records</h2>
      </div>
      <?php if (count($records) === 0): ?>
        <div class="profile-empty">
          <i class="fa fa-file-text-o" aria-hidden="true"></i>
          <p>No health records yet.</p>
        </div>
      <?php else: ?>
        <ul class="profile-timeline">
          <?php foreach ($records as $r): ?>
          <li class="profile-timeline-item">
            <div class="profile-timeline-dot profile-timeline-dot--green"></div>
            <div class="profile-timeline-content">
              <p class="profile-timeline-meta">
                <i class="fa fa-calendar" aria-hidden="true"></i>
                <?= e(date('M j, Y', strtotime($r['record_date']))) ?> — <?= e($r['recorder_name']) ?>
              </p>
              <div class="profile-vitals">
                <span><i class="fa fa-heart" aria-hidden="true"></i> BP <?= e($r['blood_pressure'] ?: '—') ?></span>
                <span><i class="fa fa-heartbeat" aria-hidden="true"></i> HR <?= e($r['heart_rate'] ?: '—') ?></span>
                <span><i class="fa fa-thermometer-half" aria-hidden="true"></i> <?= e($r['temperature'] ?: '—') ?>°F</span>
              </div>
              <?php if ($r['notes']): ?>
              <p class="profile-timeline-note"><?= e($r['notes']) ?></p>
              <?php endif; ?>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
  </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
