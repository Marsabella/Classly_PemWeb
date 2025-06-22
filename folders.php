<?php
include 'service/database.php';
session_start();

if (!isset($_SESSION['is_login'])) {
    echo "<meta http-equiv='refresh' content='0;url=login.php'>";
    exit;
}

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

$id_pengguna = $_SESSION['is_login'];

// === Proses Upload File ===
if (isset($_POST['upload']) && isset($_FILES['file'])) {
    $fileName = $_FILES['file']['name'];
    $fileTmp = $_FILES['file']['tmp_name'];
    $fileSize = $_FILES['file']['size'];
    $fileType = $_FILES['file']['type'];

    $targetDir = "uploads/";
    $uniqueName = time() . "_" . basename($fileName);
    $targetPath = $targetDir . $uniqueName;

    if (move_uploaded_file($fileTmp, $targetPath)) {
        $stmt = $db->prepare("INSERT INTO file_upload (id_pengguna, nama_file, path_file, tipe_file, ukuran_file) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $id_pengguna, $fileName, $targetPath, $fileType, $fileSize);
        $stmt->execute();
        $uploadMsg = "✅ Upload berhasil!";
    } else {
        $uploadMsg = "❌ Upload gagal.";
    }
}

// === Proses Hapus File ===
if (isset($_GET['hapus'])) {
    $id_file = $_GET['hapus'];

    $stmt = $db->prepare("SELECT path_file FROM file_upload WHERE id_file = ? AND id_pengguna = ?");
    $stmt->bind_param("ii", $id_file, $id_pengguna);
    $stmt->execute();
    $result = $stmt->get_result();
    $file = $result->fetch_assoc();

    if ($file && file_exists($file['path_file'])) {
        unlink($file['path_file']); // Hapus fisik file
    }

    $stmt = $db->prepare("DELETE FROM file_upload WHERE id_file = ? AND id_pengguna = ?");
    $stmt->bind_param("ii", $id_file, $id_pengguna);
    $stmt->execute();

    header("Location: folders.php");
    exit;
}
?>

<!doctype html>
<html class="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Classly Dashboard - File Manager</title>
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
        <ul class="space-y-1">
          <li><a href="dashboard.php" class="flex items-center px-4 py-2 text-custom-secondary hover:bg-custom-tertiary hover:text-custom-primary rounded-md text-sm font-medium transition-colors">Dashboard</a></li>
          <li><a href="class_schedule.php" class="flex items-center px-4 py-2 text-custom-secondary hover:bg-custom-tertiary hover:text-custom-primary rounded-md text-sm font-medium transition-colors">Class Schedule</a></li>
          <li><a href="notes.php" class="flex items-center px-4 py-2 text-custom-secondary hover:bg-custom-tertiary hover:text-custom-primary rounded-md text-sm font-medium transition-colors">Notes</a></li>
          <li><a href="folders.php" class="flex items-center px-4 py-2 text-custom-primary bg-accent rounded-md text-sm font-medium">Folders</a></li>
          <li><a href="reminders.php" class="flex items-center px-4 py-2 text-custom-secondary hover:bg-custom-tertiary hover:text-custom-primary rounded-md text-sm font-medium transition-colors">Reminders</a></li>
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
    <div class="main-content flex flex-col">
      <!-- Header -->
      <div class="flex items-center justify-between px-6 lg:px-8 py-6 border-b border-custom bg-custom-secondary">
        <h1 class="text-2xl font-semibold text-custom-primary">Your Folder</h1>
      </div>

      <!-- Upload Section -->
      <div class="p-6 lg:p-8 overflow-y-auto flex flex-col gap-6">
        <?php if (!empty($uploadMsg)) : ?>
          <p class="text-green-600 dark:text-green-400 font-semibold"><?= htmlspecialchars($uploadMsg) ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="flex gap-4 items-center">
          <input type="file" name="file" required class="block w-full text-sm text-gray-500
            file:mr-4 file:py-2 file:px-4
            file:rounded-md file:border-0
            file:text-sm file:font-semibold
            file:bg-accent file:text-white
            hover:file:bg-accent-hover
            cursor-pointer"/>
          <button type="submit" name="upload" class="bg-accent hover:bg-accent-hover text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">Upload</button>
        </form>

        <!-- Files Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full table-auto border border-custom rounded-md">
            <thead class="bg-custom-tertiary text-custom-secondary text-left text-sm">
              <tr>
                <th class="px-4 py-2 border border-custom">Nama File</th>
                <th class="px-4 py-2 border border-custom">Tipe</th>
                <th class="px-4 py-2 border border-custom">Ukuran (KB)</th>
                <th class="px-4 py-2 border border-custom">Download</th>
                <th class="px-4 py-2 border border-custom">Baca</th>
                <th class="px-4 py-2 border border-custom">Hapus</th>
              </tr>
            </thead>
            <tbody>
            <?php
              $stmt = $db->prepare("SELECT * FROM file_upload WHERE id_pengguna = ? ORDER BY tanggal_upload DESC");
              $stmt->bind_param("i", $id_pengguna);
              $stmt->execute();
              $result = $stmt->get_result();

              if ($result->num_rows === 0) {
                echo '<tr><td colspan="6" class="text-center py-6 text-custom-tertiary">Folder Anda kosong. Silakan upload file.</td></tr>';
              } else {
                while ($file = $result->fetch_assoc()) {
                  $fileNameEsc = htmlspecialchars($file['nama_file']);
                  $fileTypeEsc = htmlspecialchars($file['tipe_file']);
                  $fileSizeKb = round($file['ukuran_file'] / 1024, 2);
                  $filePathEsc = htmlspecialchars($file['path_file']);
                  $fileId = (int)$file['id_file'];

                  echo "<tr class='border-t border-custom'>
                    <td class='px-4 py-2'>{$fileNameEsc}</td>
                    <td class='px-4 py-2'>{$fileTypeEsc}</td>
                    <td class='px-4 py-2'>{$fileSizeKb}</td>
                    <td class='px-4 py-2'>
                      <a href='{$filePathEsc}' download class='text-accent hover:text-accent-hover'>Download</a>
                    </td>
                    <td class='px-4 py-2'>
                      <a href='{$filePathEsc}' target='_blank' class='text-accent hover:text-accent-hover'>Baca</a>
                    </td>
                    <td class='px-4 py-2'>
                      <a href='folders.php?hapus={$fileId}' onclick='return confirm(\"Hapus file ini?\")' class='text-red-600 hover:text-red-800'>Hapus</a>
                    </td>
                  </tr>";
                }
              }
            ?>
            </tbody>
          </table>
        </div>
      </div>
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
