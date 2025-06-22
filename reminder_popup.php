<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
include_once 'service/database.php';

if (!isset($_SESSION['is_login'])) {
    echo json_encode(['reminders' => []]);
    exit;
}

$id_pengguna = $_SESSION['is_login'];

$dayTranslation = [
    'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
];

date_default_timezone_set("Asia/Jakarta");
$now = new DateTime();
$currentDay = $dayTranslation[$now->format('l')] ?? '';

// Ambil pengaturan reminder user
$stmt = $db->prepare("SELECT reminder_minutes, repeat_reminder, repeat_interval FROM pengguna WHERE id_pengguna = ?");
$stmt->bind_param("i", $id_pengguna);
$stmt->execute();
$setting = $stmt->get_result()->fetch_assoc();

$reminder_minutes = (int)($setting['reminder_minutes'] ?? 15);
$repeat_reminder = $setting['repeat_reminder'] ?? 'no';
$repeat_interval = (int)($setting['repeat_interval'] ?? 5);

// Ambil jadwal hari ini
$stmt = $db->prepare("SELECT * FROM jadwal WHERE id_pengguna = ? AND hari = ?");
$stmt->bind_param("is", $id_pengguna, $currentDay);
$stmt->execute();
$result = $stmt->get_result();
$jadwals = $result->fetch_all(MYSQLI_ASSOC);

$activeReminders = [];

foreach ($jadwals as $jadwal) {
    $jadwalTime = DateTime::createFromFormat('H:i:s', $jadwal['jam']);
    if (!$jadwalTime) continue;

    $selisih = ($jadwalTime->getTimestamp() - $now->getTimestamp()) / 60;
    if ($selisih <= $reminder_minutes && $selisih > 0) {
        $activeReminders[] = [
            'matakuliah' => $jadwal['matakuliah'],
            'jam' => substr($jadwal['jam'], 0, 5),
            'ruangan' => $jadwal['ruangan'],
            'kelas' => $jadwal['kelas'],
            'dosen' => $jadwal['dosen']
        ];
    }
}

echo json_encode([
    'reminders' => $activeReminders,
    'repeat_reminder' => $repeat_reminder,
    'repeat_interval' => $repeat_interval
]);
