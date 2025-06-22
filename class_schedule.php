<?php
include "service/database.php";
session_start();

if (!isset($_SESSION["is_login"])) {
  header("Location: login.php");
  exit;
}
    
    if (isset($_POST['logout'])) {
        session_destroy();
        header("Location: index.php");
        exit;
    }
    
$id_pengguna = $_SESSION["is_login"];

// Tambah jadwal
if (isset($_POST['create_jadwal'])) {
  $stmt = $db->prepare("INSERT INTO jadwal (id_pengguna, hari, jam, matakuliah, kelas, dosen, ruangan) VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("issssss", $id_pengguna, $_POST['hari'], $_POST['jam'], $_POST['matakuliah'], $_POST['kelas'], $_POST['dosen'], $_POST['ruangan']);
  $stmt->execute();
}

// Hapus jadwal via POST
if (isset($_POST['delete_jadwal'])) {
  $id = (int)$_POST['id_jadwal'];
  $stmt = $db->prepare("DELETE FROM jadwal WHERE id_Jadwal = ? AND id_pengguna = ?");
  $stmt->bind_param("ii", $id, $id_pengguna);
  $stmt->execute();
  // tidak redirect, tetap render ulang halaman
}

// Ambil semua jadwal user
$stmt = $db->prepare("SELECT * FROM jadwal WHERE id_pengguna = ?");
$stmt->bind_param("i", $id_pengguna);
$stmt->execute();
$result = $stmt->get_result();
$jadwals = $result->fetch_all(MYSQLI_ASSOC);
?>


<!DOCTYPE html>
<html class="dark" lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Class Schedule - Classly</title>
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
<body class="bg-custom-primary text-custom-primary h-screen overflow-hidden font-sans">
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

  <div class="main-content p-6 lg:p-8">
    <h1 class="text-2xl font-semibold mb-6 text-custom-primary">Class Schedule</h1>

    <!-- Schedule List -->
    <table class="w-full border-collapse border border-gray-600 text-sm">
  <thead>
    <tr class="bg-gray-700 text-black">
      <th class="border border-gray-600 px-3 py-2">Hari</th>
      <th class="border border-gray-600 px-3 py-2">Jam</th>
      <th class="border border-gray-600 px-3 py-2">Mata Kuliah</th>
      <th class="border border-gray-600 px-3 py-2">Kelas</th>
      <th class="border border-gray-600 px-3 py-2">Dosen</th>
      <th class="border border-gray-600 px-3 py-2">Ruangan</th>
      <th class="border border-gray-600 px-3 py-2">Aksi</th>
    </tr>
  </thead>
  <tbody class="text-center text-custom-primary">
    <?php if (empty($jadwals)): ?>
        <p class="text-center text-custom-tertiary py-4">Belum ada jadwal yang ditambahkan.</p>
    <?php endif; ?>

    <?php foreach ($jadwals as $jadwal): ?>
      <tr class="hover:bg-gray-800">
        <td class="border border-gray-700 px-2 py-1"><?= htmlspecialchars($jadwal['hari']) ?></td>
        <td class="border border-gray-700 px-2 py-1"><?= htmlspecialchars($jadwal['jam']) ?></td>
        <td class="border border-gray-700 px-2 py-1"><?= htmlspecialchars($jadwal['matakuliah']) ?></td>
        <td class="border border-gray-700 px-2 py-1"><?= htmlspecialchars($jadwal['kelas']) ?></td>
        <td class="border border-gray-700 px-2 py-1"><?= htmlspecialchars($jadwal['dosen']) ?></td>
        <td class="border border-gray-700 px-2 py-1"><?= htmlspecialchars($jadwal['ruangan']) ?></td>
        <td class="border border-gray-700 px-2 py-1">
          <form method="POST" onsubmit="return confirm('Yakin ingin menghapus?')" class="inline">
  <input type="hidden" name="id_jadwal" value="<?= $jadwal['id_Jadwal'] ?>">
  <button type="submit" name="delete_jadwal" class="text-red-500 hover:underline bg-transparent border-0 p-0 cursor-pointer">Hapus</button>
</form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>


    <!-- Floating Add Button -->
    <button onclick="openAddModal()" class="fixed bottom-6 right-6 w-14 h-14 bg-accent text-white rounded-full shadow-lg bg-accent-hover transition-colors flex items-center justify-center z-40">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
      </svg>
    </button>

    <!-- Add Modal -->
<div id="add-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
  <div class="bg-custom-secondary border border-custom p-6 rounded-lg w-full max-w-md mx-4">
    <h2 class="text-2xl font-bold mb-4 text-custom-primary">Tambah Jadwal Kuliah</h2>
    <form method="POST" class="space-y-4 max-w-md">
      <input type="hidden" name="create_jadwal" value="1">

      <div>
        <label class="block mb-1 text-custom-secondary">Hari:</label>
        <select name="hari" required class="w-full bg-custom-tertiary border border-custom rounded px-3 py-2 text-custom-primary">
          <option value="">Pilih Hari</option>
          <option>Senin</option>
          <option>Selasa</option>
          <option>Rabu</option>
          <option>Kamis</option>
          <option>Jumat</option>
          <option>Sabtu</option>
          <option>Minggu</option>
        </select>
      </div>

      <div>
        <label class="block mb-1 text-custom-secondary">Jam:</label>
        <input type="time" name="jam" required class="w-full bg-custom-tertiary border border-custom rounded px-3 py-2 text-custom-primary">
      </div>

      <div>
        <label class="block mb-1 text-custom-secondary">Mata Kuliah:</label>
        <input type="text" name="matakuliah" required class="w-full bg-custom-tertiary border border-custom rounded px-3 py-2 text-custom-primary">
      </div>

      <div>
        <label class="block mb-1 text-custom-secondary">Kelas:</label>
        <input type="text" name="kelas" required class="w-full bg-custom-tertiary border border-custom rounded px-3 py-2 text-custom-primary">
      </div>

      <div>
        <label class="block mb-1 text-custom-secondary">Dosen:</label>
        <input type="text" name="dosen" required class="w-full bg-custom-tertiary border border-custom rounded px-3 py-2 text-custom-primary">
      </div>

      <div>
        <label class="block mb-1 text-custom-secondary">Ruangan:</label>
        <input type="text" name="ruangan" required class="w-full bg-custom-tertiary border border-custom rounded px-3 py-2 text-custom-primary">
      </div>

      <div class="flex gap-3 pt-4">
        <button type="submit" class="flex-1 bg-accent text-white py-2 rounded-md">Simpan</button>
        <button type="button" onclick="closeAddModal()" class="flex-1 bg-custom-tertiary hover:bg-gray-700 text-custom-primary py-2 rounded-md">Batal</button>
      </div>
    </form>
  </div>
</div>



  
  <script>
    function openAddModal() {
      document.getElementById("add-modal").classList.remove("hidden");
    }
    function closeAddModal() {
      document.getElementById("add-modal").classList.add("hidden");
    }
    function openEditModal(jadwal) {
      document.getElementById("edit-id").value = jadwal.id_Jadwal;
      document.getElementById("edit-hari").value = jadwal.hari;
      document.getElementById("edit-jam").value = jadwal.jam;
      document.getElementById("edit-matkul").value = jadwal.matakuliah;
      document.getElementById("edit-kelas").value = jadwal.kelas;
      document.getElementById("edit-dosen").value = jadwal.dosen;
      document.getElementById("edit-ruangan").value = jadwal.ruangan;
      document.getElementById("edit-modal").classList.remove("hidden");
    }
    function closeEditModal() {
      document.getElementById("edit-modal").classList.add("hidden");
    }
    
    function updateThemeText() {
    const isDark = document.documentElement.classList.contains('dark');

    const darkIconSidebar = document.getElementById('theme-icon-dark-sidebar');
    const lightIconSidebar = document.getElementById('theme-icon-light-sidebar');
    const darkText = document.querySelector('.theme-text-dark');
    const lightText = document.querySelector('.theme-text-light');

    if (isDark) {
      darkIconSidebar?.classList.remove('hidden');
      lightIconSidebar?.classList.add('hidden');
      darkText && (darkText.textContent = 'Light Mode');
      lightText && (lightText.textContent = 'Light Mode');
    } else {
      darkIconSidebar?.classList.add('hidden');
      lightIconSidebar?.classList.remove('hidden');
      darkText && (darkText.textContent = 'Dark Mode');
      lightText && (lightText.textContent = 'Dark Mode');
    }
  }

  function toggleTheme() {
    const html = document.documentElement;
    html.classList.toggle('dark');
    localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
    updateThemeText();
  }

  function loadTheme() {
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (savedTheme === 'light' || (!savedTheme && !prefersDark)) {
      document.documentElement.classList.remove('dark');
    } else {
      document.documentElement.classList.add('dark');
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
</body>
</html>
