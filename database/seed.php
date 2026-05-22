<?php
/**
 * Run from project root: php database/seed.php
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$pdo = getDb();
$password = password_hash('123456', PASSWORD_BCRYPT);

// Clear tables for fresh demo data
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
$pdo->exec('TRUNCATE TABLE health_records');
$pdo->exec('TRUNCATE TABLE appointments');
$pdo->exec('TRUNCATE TABLE caregiver_elders');
$pdo->exec('TRUNCATE TABLE elders');
$pdo->exec('TRUNCATE TABLE users');
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

function insertUser(PDO $pdo, string $name, string $email, string $role, string $hash, ?string $phone): int
{
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, phone) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$name, $email, $hash, $role, $phone]);
    return (int) $pdo->lastInsertId();
}

$adminId = insertUser($pdo, 'Sarah Admin', 'admin@eldercare.com', 'admin', $password, '+1-555-0100');
$cg1 = insertUser($pdo, 'James Wilson', 'james.care@eldercare.com', 'caregiver', $password, '+1-555-0201');
$cg2 = insertUser($pdo, 'Maria Garcia', 'maria.care@eldercare.com', 'caregiver', $password, '+1-555-0202');

$elderly = [
    ['Robert Thompson', 'robert@eldercare.com', '+1-555-0301'],
    ['Eleanor Davis', 'eleanor@eldercare.com', '+1-555-0302'],
    ['William Chen', 'william@eldercare.com', '+1-555-0303'],
    ['Margaret Lee', 'margaret@eldercare.com', '+1-555-0304'],
];

$elderUserIds = [];
foreach ($elderly as $e) {
    $elderUserIds[] = insertUser($pdo, $e[0], $e[1], 'elderly', $password, $e[2]);
}

$elderData = [
    [$elderUserIds[0], $cg1, '1945-03-12', '42 Oak Street, Springfield', 'O+', 'Penicillin, Peanuts', 'Lisinopril 10mg, Metformin 500mg', 'Hypertension, Type 2 Diabetes', 'stable', 'John Thompson', '+1-555-0399', 'Son', 'Prefers morning walks. Needs help with medication reminders.'],
    [$elderUserIds[1], $cg1, '1940-07-22', '18 Maple Avenue, Riverside', 'A-', 'Sulfa drugs', 'Amlodipine 5mg, Vitamin D', 'Arthritis, Osteoporosis', 'monitoring', 'Lisa Davis', '+1-555-0398', 'Daughter', 'Mobility assistance required. Weekly physiotherapy.'],
    [$elderUserIds[2], $cg2, '1938-11-05', '7 Pine Road, Lakewood', 'B+', '', 'Atorvastatin 20mg, Aspirin 81mg', 'High Cholesterol', 'recovering', 'David Chen', '+1-555-0397', 'Son', 'Recovering from hip surgery. Limited mobility for 4 weeks.'],
    [$elderUserIds[3], $cg2, '1943-01-18', '99 Cedar Lane, Hilltown', 'AB+', 'Latex', 'Levothyroxine 50mcg', 'Hypothyroidism', 'stable', 'Amy Lee', '+1-555-0396', 'Daughter', 'Independent with daily activities. Regular thyroid checkups.'],
];

$elderStmt = $pdo->prepare('INSERT INTO elders (user_id, assigned_caregiver_id, date_of_birth, address, blood_type, allergies, medications, conditions, health_status, ec_name, ec_phone, ec_relationship, notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
$ceStmt = $pdo->prepare('INSERT INTO caregiver_elders (caregiver_id, elder_id) VALUES (?,?)');
$elderIds = [];

foreach ($elderData as $row) {
    $elderStmt->execute([
        $row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8],
        $row[9], $row[10], $row[11], $row[12],
    ]);
    $eid = (int) $pdo->lastInsertId();
    $elderIds[] = $eid;
    $ceStmt->execute([$row[1], $eid]);
}

$apptStmt = $pdo->prepare('INSERT INTO appointments (elder_id, caregiver_id, title, description, appt_date, appt_time, location, status, created_by) VALUES (?,?,?,?,?,?,?,?,?)');
$appts = [
    [$elderIds[0], $cg1, 'Routine Health Checkup', 'Monthly vitals and medication review', date('Y-m-d', strtotime('+7 days')), '10:00 AM', 'Springfield Community Clinic'],
    [$elderIds[1], $cg1, 'Physiotherapy Session', 'Knee mobility exercises', date('Y-m-d', strtotime('+7 days')), '2:30 PM', 'Riverside Rehab Center'],
    [$elderIds[2], $cg2, 'Post-Surgery Follow-up', 'Hip recovery assessment with doctor', date('Y-m-d', strtotime('+14 days')), '11:00 AM', 'Lakewood Medical Center'],
    [$elderIds[3], $cg2, 'Thyroid Lab Review', 'Blood work results discussion', date('Y-m-d', strtotime('+14 days')), '9:30 AM', 'Hilltown Health Hub'],
];
foreach ($appts as $a) {
    $apptStmt->execute([...$a, 'scheduled', $adminId]);
}

$hrStmt = $pdo->prepare('INSERT INTO health_records (elder_id, blood_pressure, heart_rate, temperature, weight, blood_sugar, oxygen_level, notes, recorded_by, record_date) VALUES (?,?,?,?,?,?,?,?,?,?)');
$records = [
    [$elderIds[0], '128/82', 72, 98.4, 165, 110, 97, 'Vitals within normal range. Patient in good spirits.', $cg1, date('Y-m-d H:i:s', strtotime('-2 days'))],
    [$elderIds[1], '135/88', 78, 98.1, 142, null, 96, 'Slight knee discomfort reported. Continue physiotherapy.', $cg1, date('Y-m-d H:i:s', strtotime('-1 day'))],
    [$elderIds[2], '122/80', 70, 98.6, 158, null, 98, 'Hip mobility improving. Using walker less frequently.', $cg2, date('Y-m-d H:i:s', strtotime('-3 days'))],
    [$elderIds[3], '118/76', 68, 98.2, 130, 95, 99, 'All vitals excellent. No concerns.', $cg2, date('Y-m-d H:i:s')],
];
foreach ($records as $r) {
    $hrStmt->execute($r);
}

echo "Seed completed. Password for all users: 123456\n";
