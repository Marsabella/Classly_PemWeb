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
    $username = $_SESSION['username'];

    $stmt = $db->prepare("SELECT * FROM jadwal WHERE id_pengguna = ?");
    $stmt->bind_param("i", $id_pengguna);
    $stmt->execute();
    $result = $stmt->get_result();
    $jadwals = $result->fetch_all(MYSQLI_ASSOC);

    // Ambil catatan user, urut berdasarkan tanggal terbaru
    $stmt_catatan = $db->prepare("SELECT judul, tanggal_dibuat FROM catatan WHERE id_pengguna = ? ORDER BY tanggal_dibuat DESC");
    $stmt_catatan->bind_param("i", $id_pengguna);
    $stmt_catatan->execute();
    $result_catatan = $stmt_catatan->get_result();
    $catatan_list = $result_catatan->fetch_all(MYSQLI_ASSOC);

    // Ambil file user, urut berdasarkan tanggal upload terbaru
$stmt_files = $db->prepare("SELECT nama_file, tanggal_upload FROM file_upload WHERE id_pengguna = ? ORDER BY tanggal_upload DESC");
$stmt_files->bind_param("i", $id_pengguna);
$stmt_files->execute();
$result_files = $stmt_files->get_result();
$file_list = $result_files->fetch_all(MYSQLI_ASSOC);


    ?>

    <!doctype html>
    <html class="dark">
    <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Classly Dashboard</title>
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

        <!-- Main Content -->
        <div class="main-content flex flex-col bg-custom-primary">
        <header class="flex justify-between items-center px-8 py-6 border-b border-custom bg-custom-secondary">
            <h1 class="text-xl font-medium text-custom-primary">Hello, <?= htmlspecialchars($username) ?></h1>
        </header>

        <main class="flex-1 p-8 overflow-y-auto">
            <section class="mb-12">
            <h3 class="text-lg font-medium text-custom-primary mb-6">Jadwal Kuliah Saya</h3>
            <?php if (count($jadwals) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-custom-secondary border border-custom text-sm text-left text-custom-secondary">
                <thead class="bg-custom-tertiary text-custom-primary">
                    <tr>
                    <th class="px-4 py-2">Hari</th>
                    <th class="px-4 py-2">Jam</th>
                    <th class="px-4 py-2">Mata Kuliah</th>
                    <th class="px-4 py-2">Kelas</th>
                    <th class="px-4 py-2">Dosen</th>
                    <th class="px-4 py-2">Ruangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jadwals as $jadwal): ?>
                    <tr class="border-t border-custom">
                    <td class="px-4 py-2"><?= $jadwal['hari'] ?></td>
                    <td class="px-4 py-2"><?= $jadwal['jam'] ?></td>
                    <td class="px-4 py-2"><?= $jadwal['matakuliah'] ?></td>
                    <td class="px-4 py-2"><?= $jadwal['kelas'] ?></td>
                    <td class="px-4 py-2"><?= $jadwal['dosen'] ?></td>
                    <td class="px-4 py-2"><?= $jadwal['ruangan'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-custom-tertiary">Belum ada jadwal. Silakan tambahkan melalui menu Class Schedule.</p>
            <?php endif; ?>
            </section>
            <section class="mb-12">
  <h3 class="text-lg font-medium text-custom-primary mb-6">Catatan Saya</h3>
  <?php if (count($catatan_list) > 0): ?>
    <div class="overflow-x-auto">
      <table class="min-w-full bg-custom-secondary border border-custom text-sm text-left text-custom-secondary">
        <thead class="bg-custom-tertiary text-custom-primary">
          <tr>
            <th class="px-4 py-2">Nama</th>
            <th class="px-4 py-2">Tanggal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($catatan_list as $catatan): ?>
          <tr class="border-t border-custom">
            <td class="px-4 py-2"><?= htmlspecialchars($catatan['judul']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($catatan['tanggal_dibuat']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="text-custom-tertiary">Belum ada catatan. Yuk buat sekarang!</p>
  <?php endif; ?>
</section>
<section class="mb-12">
  <h3 class="text-lg font-medium text-custom-primary mb-6">File Saya</h3>
  <?php if (count($file_list) > 0): ?>
    <div class="overflow-x-auto">
      <table class="min-w-full bg-custom-secondary border border-custom text-sm text-left text-custom-secondary">
        <thead class="bg-custom-tertiary text-custom-primary">
          <tr>
            <th class="px-4 py-2">Nama</th>
            <th class="px-4 py-2">Tanggal Upload</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($file_list as $file): ?>
          <tr class="border-t border-custom">
            <td class="px-4 py-2"><?= htmlspecialchars($file['nama_file']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($file['tanggal_upload']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="text-custom-tertiary">Belum ada file yang diupload.</p>
  <?php endif; ?>
</section>


        </main>
        </div>
    </div>

    <script>
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

    if (localStorage.getItem("reminder_dismissed_" + reminderKey)) return;

    const div = document.createElement('div');
    div.className = 'fixed bottom-6 right-6 z-50 bg-accent text-white px-6 py-4 rounded-xl shadow-lg animate-bounce';
    div.style.maxWidth = '300px';

    // Buat elemen audio
    const audio = new Audio('assets/notification.mp3');
    audio.loop = true; // agar terus diputar sampai ditutup
    audio.play();
    div.audioElement = audio; // simpan referensinya untuk nanti dihentikan

    div.innerHTML = `
        <strong class="block text-lg mb-1">Kelas Segera Dimulai!</strong>
        <p><strong>${data.matakuliah}</strong> (${data.kelas})</p>
        <p>Dosen: ${data.dosen}</p>
        <p>Jam: ${data.jam}</p>
        <p>Ruangan: ${data.ruangan}</p>
        <button onclick="dismissReminder('${reminderKey}', this)" class="mt-3 px-3 py-1 bg-white text-accent rounded shadow hover:bg-gray-200 text-sm">Tutup</button>
    `;

    document.body.appendChild(div);
    setTimeout(() => {
        if (div.audioElement) div.audioElement.pause(); // stop suara
        div.remove();
    }, 15000);
}

function dismissReminder(key, button) {
    localStorage.setItem("reminder_dismissed_" + key, "1");

    const parent = button.parentElement;
    if (parent.audioElement) parent.audioElement.pause(); // hentikan audio
    parent.remove();
}



function getShownReminders() {
  const data = localStorage.getItem('shownReminders');
  return data ? JSON.parse(data) : [];
}

function addShownReminder(key) {
  const data = getShownReminders();
  if (!data.includes(key)) {
    data.push(key);
    localStorage.setItem('shownReminders', JSON.stringify(data));
  }
}

function hasShownReminder(key) {
  const data = getShownReminders();
  return data.includes(key);
}


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
                if (!hasShownReminder(key)) {
                    showReminderPopup(reminder);
                    addShownReminder(key);
                }

            });

            // Kalau repeat reminder "yes", reset shownReminders supaya popup bisa muncul lagi nanti
            if (repeatReminder) {
    setTimeout(() => {
        localStorage.removeItem('shownReminders');
    }, repeatInterval * 60000);
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
