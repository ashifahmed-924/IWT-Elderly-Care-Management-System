<?php
require_once __DIR__ . '/../../includes/auth.php';
$user = requireRole('elderly');
$pdo = getDb();
$elderId = getElderIdForUser($pdo, $user['id']);

$stmt = $pdo->prepare("
  SELECT a.*, cu.name AS caregiver_name
  FROM appointments a
  LEFT JOIN users cu ON a.caregiver_id = cu.id
  WHERE a.elder_id = ?
  ORDER BY a.appt_date ASC
");
$stmt->execute([$elderId]);
$appointments = $stmt->fetchAll();

$pageTitle = 'My Appointments';
require_once __DIR__ . '/../../includes/header.php';
?>
<h1 class="page-title">Appointments</h1>
<p class="page-sub">View your scheduled appointments</p>

<div class="caregiver-panel mt-8">
  <?php if (count($appointments) === 0): ?>
    <p style="color:var(--slate-500)">No appointments scheduled.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Title</th><th>Date</th><th>Time</th><th>Location</th><th>Caregiver</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($appointments as $a): ?>
          <tr>
            <td><strong><?= e($a['title']) ?></strong><br><small style="color:var(--slate-500)"><?= e($a['description']) ?></small></td>
            <td><?= e($a['appt_date']) ?></td>
            <td><?= e($a['appt_time']) ?></td>
            <td><?= e($a['location'] ?: '—') ?></td>
            <td><?= e($a['caregiver_name'] ?: '—') ?></td>
            <td><span class="<?= statusBadgeClass($a['status']) ?>"><?= e($a['status']) ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
