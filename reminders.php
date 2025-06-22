<?php
include 'service/database.php';
session_start();

if (!isset($_SESSION['is_login'])) {
  header("Location: login.php");
  exit;
}

if (isset($_POST['logout'])) {
        session_destroy();
        header("Location: index.php");
        exit;
    }

$id_pengguna = $_SESSION['is_login'];

// Ambil pengaturan reminder jika ada
$query = $db->prepare("SELECT reminder_minutes, repeat_reminder, repeat_interval FROM pengguna WHERE id_pengguna = ?");
$query->bind_param("i", $id_pengguna);
$query->execute();
$result = $query->get_result();
$setting = $result->fetch_assoc();

$reminder_minutes = $setting['reminder_minutes'] ?? 15;
$repeat_reminder = $setting['repeat_reminder'] ?? 'no';
$repeat_interval = $setting['repeat_interval'] ?? 5;

// Proses simpan jika form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $minutes = (int)$_POST['reminder_minutes'];
  $repeat = $_POST['repeat_reminder'];
  $interval = (int)($_POST['repeat_interval'] ?? 5);

  $stmt = $db->prepare("UPDATE pengguna SET reminder_minutes = ?, repeat_reminder = ?, repeat_interval = ? WHERE id_pengguna = ?");
  $stmt->bind_param("isii", $minutes, $repeat, $interval, $id_pengguna);
  $stmt->execute();

  header("Location: reminders.php?saved=1");
  exit;
}
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reminders - Classly</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    :root {
      --bg-primary: #ffffff;
      --bg-secondary: #f8fafc;
      --bg-tertiary: #f1f5f9;
      --text-primary: #1e293b;
      --text-secondary: #64748b;
      --text-tertiary: #94a3b8;
      --border-color: #e2e8f0;
      --accent-color: #4f46e5;
      --accent-hover: #3730a3;
    }

    .dark {
      --bg-primary: #0f0f0f;
      --bg-secondary: #1a1a1a;
      --bg-tertiary: #2d2d2d;
      --text-primary: #ffffff;
      --text-secondary: #cccccc;
      --text-tertiary: #aaaaaa;
      --border-color: #2d2d2d;
      --accent-color: #4f46e5;
      --accent-hover: #3730a3;
    }

    body {
      background-color: var(--bg-primary);
      color: var(--text-primary);
    }

    .bg-custom-primary { background-color: var(--bg-primary); }
    .bg-custom-secondary { background-color: var(--bg-secondary); }
    .bg-custom-tertiary { background-color: var(--bg-tertiary); }
    .text-custom-primary { color: var(--text-primary); }
    .text-custom-secondary { color: var(--text-secondary); }
    .text-custom-tertiary { color: var(--text-tertiary); }
    .border-custom { border-color: var(--border-color); }
    .bg-accent { background-color: var(--accent-color); }
    .bg-accent-hover:hover { background-color: var(--accent-hover); }
    .text-accent { color: var(--accent-color); }

    .sidebar-fixed {
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      width: 16rem;
      z-index: 40;
    }

    .main-content {
      margin-left: 16rem;
      min-height: 100vh;
    }

    @media (max-width: 1023px) {
      .main-content {
        margin-left: 0;
      }
    }
  </style>
</head>
<body class="bg-custom-primary text-custom-primary font-sans h-screen overflow-hidden">
  <div class="flex h-screen">
    <!-- Sidebar -->
    <div class="sidebar-fixed bg-custom-secondary border-r border-custom flex flex-col">
      <div class="p-6">
        <h2 class="text-custom-primary text-xl font-bold">Classly</h2>
      </div>
      <nav class="flex-1 px-4">
        <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
        <ul class="space-y-1">
          <li><a href="dashboard.php" class="flex items-center px-4 py-2 rounded-md text-sm font-medium <?= $currentPage == 'dashboard.php' ? 'bg-accent text-custom-primary' : 'text-custom-secondary hover:bg-custom-tertiary hover:text-custom-primary transition-colors' ?>">Dashboard</a></li>
          <li><a href="class_schedule.php" class="flex items-center px-4 py-2 rounded-md text-sm font-medium <?= $currentPage == 'class_schedule.php' ? 'bg-accent text-custom-primary' : 'text-custom-secondary hover:bg-custom-tertiary hover:text-custom-primary transition-colors' ?>">Class Schedule</a></li>
          <li><a href="notes.php" class="flex items-center px-4 py-2 rounded-md text-sm font-medium <?= $currentPage == 'notes.php' ? 'bg-accent text-custom-primary' : 'text-custom-secondary hover:bg-custom-tertiary hover:text-custom-primary transition-colors' ?>">Notes</a></li>
          <li><a href="folders.php" class="flex items-center px-4 py-2 rounded-md text-sm font-medium <?= $currentPage == 'folders.php' ? 'bg-accent text-custom-primary' : 'text-custom-secondary hover:bg-custom-tertiary hover:text-custom-primary transition-colors' ?>">Folders</a></li>
          <li><a href="reminders.php" class="flex items-center px-4 py-2 rounded-md text-sm font-medium <?= $currentPage == 'reminders.php' ? 'bg-accent text-custom-primary' : 'text-custom-secondary hover:bg-custom-tertiary hover:text-custom-primary transition-colors' ?>">Reminders</a></li>
        </ul>
      </nav>
      <div class="p-4 border-t border-custom space-y-2">
        <button onclick="toggleTheme()" class="flex items-center w-full px-4 py-2 text-custom-secondary hover:bg-custom-tertiary hover:text-custom-primary rounded-md text-sm font-medium transition-colors">
          <svg id="theme-icon-dark-sidebar" class="w-4 h-4 mr-2 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
          </svg>
          <svg id="theme-icon-light-sidebar" class="w-4 h-4 mr-2 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
          </svg>
          <span class="theme-text-dark hidden dark:inline">Light Mode</span>
          <span class="theme-text-light inline dark:hidden">Dark Mode</span>
        </button>
        <form method="POST">
            <button type="submit" name="logout" class="flex items-center w-full px-4 py-2 text-custom-secondary hover:bg-custom-tertiary hover:text-custom-primary rounded-md text-sm font-medium transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Logout
            </button>
        </form>
      </div>
    </div>

    <!-- Main Content -->
    <div class="main-content p-6 lg:p-8">
      <div class="bg-custom-secondary border border-custom rounded-lg max-w-xl mx-auto p-8">
        <h1 class="text-2xl font-semibold text-center mb-8 text-custom-primary">Reminder Settings</h1>

        <?php if (isset($_GET['saved'])): ?>
          <div class="mb-4 text-green-400 font-medium text-center">Reminder settings saved successfully!</div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
          <div>
            <label for="reminder_minutes" class="block text-sm font-medium mb-2 text-custom-secondary">Notification before class starts:</label>
            <select name="reminder_minutes" id="reminder_minutes" class="w-full px-4 py-2 bg-custom-tertiary border border-custom rounded-md focus:outline-none text-custom-primary">
              <?php foreach ([30, 25, 20, 15, 10, 5] as $opt): ?>
                <option value="<?= $opt ?>" <?= $reminder_minutes == $opt ? 'selected' : '' ?>><?= $opt ?> minutes</option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label for="repeat_reminder" class="block text-sm font-medium mb-2 text-custom-secondary">Repeat reminder until class starts?</label>
            <select name="repeat_reminder" id="repeat_reminder" class="w-full px-4 py-2 bg-custom-tertiary border border-custom rounded-md focus:outline-none text-custom-primary" onchange="toggleRepeatInterval()">
              <option value="no" <?= $repeat_reminder == 'no' ? 'selected' : '' ?>>No, just once</option>
              <option value="yes" <?= $repeat_reminder == 'yes' ? 'selected' : '' ?>>Yes, keep repeating</option>
            </select>
          </div>

          <div id="repeatIntervalContainer" class="<?= $repeat_reminder === 'yes' ? '' : 'hidden' ?>">
            <label for="repeat_interval" class="block text-sm font-medium mb-2 text-custom-secondary">Repeat every:</label>
            <select name="repeat_interval" id="repeat_interval" class="w-full px-4 py-2 bg-custom-tertiary border border-custom rounded-md focus:outline-none text-custom-primary">
              <option value="5" <?= $repeat_interval == 5 ? 'selected' : '' ?>>5 minutes</option>
              <option value="10" <?= $repeat_interval == 10 ? 'selected' : '' ?>>10 minutes</option>
            </select>
          </div>

          <button type="submit" class="w-full py-3 bg-accent bg-accent-hover text-white rounded-md font-medium transition-colors">
            Save Settings
          </button>
        </form>
      </div>
    </div>
  </div>

  <script>
    function toggleRepeatInterval() {
      const repeatSelect = document.getElementById("repeat_reminder");
      const intervalContainer = document.getElementById("repeatIntervalContainer");
      intervalContainer.classList.toggle("hidden", repeatSelect.value !== "yes");
    }

    function toggleTheme() {
      const html = document.documentElement;
      html.classList.toggle('dark');
      localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
      updateThemeText();
    }

    function updateThemeText() {
      const isDark = document.documentElement.classList.contains('dark');
      document.getElementById('theme-icon-dark-sidebar')?.classList.toggle('hidden', !isDark);
      document.getElementById('theme-icon-light-sidebar')?.classList.toggle('hidden', isDark);
      document.querySelector('.theme-text-dark').textContent = isDark ? 'Light Mode' : '';
      document.querySelector('.theme-text-light').textContent = !isDark ? 'Dark Mode' : '';
    }

    function loadTheme() {
      const saved = localStorage.getItem('theme');
      if (saved === 'light') document.documentElement.classList.remove('dark');
      else document.documentElement.classList.add('dark');
      updateThemeText();
    }

    document.addEventListener('DOMContentLoaded', loadTheme);
    function showReminderPopup(data) {
    const reminderKey = data.matakuliah + "_" + data.jam;

    // Jika sudah ditutup sebelumnya, jangan tampilkan lagi
    if (localStorage.getItem("reminder_dismissed_" + reminderKey)) {
        return;
    }

    const div = document.createElement('div');
    div.className = 'fixed bottom-6 right-6 z-50 bg-accent text-white px-6 py-4 rounded-xl shadow-lg animate-bounce';
    div.style.maxWidth = '300px';
    div.innerHTML = `
        <strong class="block text-lg mb-1">Kelas Segera Dimulai!</strong>
        <p><strong>${data.matakuliah}</strong> (${data.kelas})</p>
        <p>Dosen: ${data.dosen}</p>
        <p>Jam: ${data.jam}</p>
        <p>Ruangan: ${data.ruangan}</p>
        <button onclick="dismissReminder('${reminderKey}', this)" class="mt-3 px-3 py-1 bg-white text-accent rounded shadow hover:bg-gray-200 text-sm">Tutup</button>
    `;
    document.body.appendChild(div);
    setTimeout(() => div.remove(), 15000);
}

function dismissReminder(key, button) {
    localStorage.setItem("reminder_dismissed_" + key, "1");
    button.parentElement.remove();
}


let shownReminders = new Set();

function fetchReminders() {
    fetch('reminder_popup.php')
        .then(response => response.json())
        .then(data => {
            const reminders = data.reminders || [];
            const repeatReminder = data.repeat_reminder === 'yes';
            const repeatInterval = parseInt(data.repeat_interval) || 5;

            // Tampilkan reminder yang belum muncul
            reminders.forEach(reminder => {
                const key = reminder.matakuliah + reminder.jam;
                if (!shownReminders.has(key)) {
                    showReminderPopup(reminder);
                    shownReminders.add(key);
                }
            });

            // Kalau repeat reminder "yes", reset shownReminders supaya popup bisa muncul lagi nanti
            if (repeatReminder) {
                setTimeout(() => {
                    shownReminders.clear();
                }, repeatInterval * 60000); // convert menit ke ms
            }
        })
        .catch(err => console.error("Error fetching reminders:", err));
}

// Poll setiap 1 menit
setInterval(fetchReminders, 60000);
// Jalankan juga sekali saat halaman load
document.addEventListener("DOMContentLoaded", fetchReminders);
  </script>
</body>
</html>
