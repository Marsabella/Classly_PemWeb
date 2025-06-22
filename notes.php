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
$username = $_SESSION['username'] ?? 'User';

// Tambah catatan
if (isset($_POST['buat_catatan'])) {
  $judul = $_POST['judul'];
  $isi = $_POST['isi'];
  $stmt = $db->prepare("INSERT INTO catatan (id_pengguna, judul, isi) VALUES (?, ?, ?)");
  $stmt->bind_param("iss", $id_pengguna, $judul, $isi);
  $stmt->execute();
}

// Hapus catatan
if (isset($_GET['hapus'])) {
  $id_catatan = $_GET['hapus'];
  $stmt = $db->prepare("DELETE FROM catatan WHERE id_catatan = ? AND id_pengguna = ?");
  $stmt->bind_param("ii", $id_catatan, $id_pengguna);
  $stmt->execute();
  header("Location: notes.php");
  exit;
}

// Ambil semua catatan
$stmt = $db->prepare("SELECT * FROM catatan WHERE id_pengguna = ? ORDER BY tanggal_dibuat DESC");
$stmt->bind_param("i", $id_pengguna);
$stmt->execute();
$result = $stmt->get_result();
$catatan_list = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html class="dark" lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Notes - Classly</title>
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
    .bg-custom-secondary 
    { 
    background-color: var(--bg-secondary);
    word-break: break-word;
    overflow-wrap: break-word;
    }
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
<body class="bg-custom-primary text-custom-primary h-screen font-sans">

<div class="flex h-screen">
  <!-- Sidebar -->
  <div class="sidebar-fixed bg-custom-secondary border-r border-custom flex flex-col">
    <div class="p-6">
      <h2 class="text-custom-primary text-xl font-bold">Classly</h2>
    </div>
    <nav class="flex-1 px-4">
      <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
<ul class="space-y-1">
  <li>
    <a href="dashboard.php"
      class="flex items-center px-4 py-2 rounded-md text-sm font-medium 
      <?= $currentPage == 'dashboard.php' ? 'bg-accent text-custom-primary' : 'text-custom-secondary hover:bg-custom-tertiary hover:text-custom-primary transition-colors' ?>">
      Dashboard
    </a>
  </li>
  <li>
    <a href="class_schedule.php"
      class="flex items-center px-4 py-2 rounded-md text-sm font-medium 
      <?= $currentPage == 'class_schedule.php' ? 'bg-accent text-custom-primary' : 'text-custom-secondary hover:bg-custom-tertiary hover:text-custom-primary transition-colors' ?>">
      Class Schedule
    </a>
  </li>
  <li>
    <a href="notes.php"
      class="flex items-center px-4 py-2 rounded-md text-sm font-medium 
      <?= $currentPage == 'notes.php' ? 'bg-accent text-custom-primary' : 'text-custom-secondary hover:bg-custom-tertiary hover:text-custom-primary transition-colors' ?>">
      Notes
    </a>
  </li>
  <li>
    <a href="folders.php"
      class="flex items-center px-4 py-2 rounded-md text-sm font-medium 
      <?= $currentPage == 'folders.php' ? 'bg-accent text-custom-primary' : 'text-custom-secondary hover:bg-custom-tertiary hover:text-custom-primary transition-colors' ?>">
      Folders
    </a>
  </li>
  <li>
    <a href="reminders.php"
      class="flex items-center px-4 py-2 rounded-md text-sm font-medium 
      <?= $currentPage == 'reminders.php' ? 'bg-accent text-custom-primary' : 'text-custom-secondary hover:bg-custom-tertiary hover:text-custom-primary transition-colors' ?>">
      Reminders
    </a>
  </li>
</ul>

    </nav>
        <!-- Theme Toggle & Logout -->
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
  <div class="main-content p-8">
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-2xl font-semibold">Catatan Saya</h1>
      <button onclick="document.getElementById('note-modal').classList.remove('hidden')" class="bg-accent text-white px-4 py-2 rounded-md hover:bg-accent-hover transition">Tambah Catatan</button>
    </div>

    <?php if (count($catatan_list) === 0): ?>
      <p class="text-custom-tertiary">Belum ada catatan. Yuk buat sekarang!</p>
    <?php else: ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($catatan_list as $catatan): ?>
          <div class="bg-custom-secondary border border-custom p-4 rounded-lg">
            <div class="flex justify-between items-center mb-2">
              <h2 class="text-lg font-semibold"><?= htmlspecialchars($catatan['judul']) ?></h2>
              <a href="?hapus=<?= $catatan['id_catatan'] ?>" onclick="return confirm('Yakin ingin menghapus catatan ini?')" class="text-red-500 hover:underline text-sm">Hapus</a>
            </div>
            <p class="text-custom-secondary text-sm"><?= nl2br(htmlspecialchars($catatan['isi'])) ?></p>
            <p class="mt-2 text-custom-tertiary text-xs">Dibuat pada <?= $catatan['tanggal_dibuat'] ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal -->
<div id="note-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
  <div class="bg-custom-secondary border border-custom p-6 rounded-lg w-full max-w-lg mx-4">
    <h2 class="text-xl font-semibold mb-4 text-custom-primary">Tambah Catatan</h2>
    <form method="POST">
      <input type="hidden" name="buat_catatan" value="1">
      <div class="mb-4">
        <label class="block mb-1 text-custom-secondary">Judul</label>
        <input type="text" name="judul" required class="w-full bg-custom-tertiary border border-custom rounded px-3 py-2 text-custom-primary">
      </div>
      <div class="mb-4">
        <label class="block mb-1 text-custom-secondary">Isi</label>
        <textarea name="isi" rows="6" required class="w-full bg-custom-tertiary border border-custom rounded px-3 py-2 text-custom-primary"></textarea>
      </div>
      <div class="flex justify-end gap-2">
        <button type="button" onclick="document.getElementById('note-modal').classList.add('hidden')" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">Batal</button>
        <button type="submit" class="px-4 py-2 bg-accent text-white rounded-md hover:bg-accent-hover">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
  function toggleTheme() {
    const html = document.documentElement;
    html.classList.toggle('dark');
    localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
    updateThemeText();
  }

  function updateThemeText() {
    const isDark = document.documentElement.classList.contains('dark');
    document.querySelector('.theme-text-light').classList.toggle('hidden', isDark);
    document.querySelector('.theme-text-dark').classList.toggle('hidden', !isDark);
  }

  function loadTheme() {
    const theme = localStorage.getItem('theme');
    if (theme === 'dark') {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
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
<?php include 'reminder_popup.php'; ?>
</body>
</html>
