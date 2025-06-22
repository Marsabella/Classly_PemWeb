<?php
include "service/database.php";
session_start();

$register_message = "";
$register_error = "";

if (isset($_SESSION["is_login"])) {
    header("Location: dashboard.php");
    exit;
}

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $db->prepare("INSERT INTO pengguna (nama_pengguna, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $password);
        if ($stmt->execute()) {
            $register_message = "✅ Pendaftaran berhasil! Silakan login.";
            header("Refresh:1; url=login.php");
        } else {
            $register_error = "❌ Gagal mendaftar. Silakan coba lagi.";
        }
    } catch (mysqli_sql_exception) {
        $register_error = "❌ Username sudah digunakan.";
    }
}
?>

<!DOCTYPE html>
<html class="dark" lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Classly</title>
  <link href="./output.css" rel="stylesheet">
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
  </style>
</head>
<body class="bg-custom-primary text-custom-primary min-h-screen flex items-center justify-center p-4">

  <button onclick="toggleTheme()" class="fixed top-4 right-4 p-2 bg-custom-secondary border border-custom rounded-md hover:bg-custom-tertiary transition-colors">
    <svg id="theme-icon-dark" class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
    </svg>
    <svg id="theme-icon-light" class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
    </svg>
  </button>

  <div class="bg-custom-secondary border border-custom rounded-lg w-full max-w-md p-8">
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold mb-2 text-custom-primary">Create Account</h1>
      <p class="text-custom-tertiary">Join Classly to organize your campus life</p>
    </div>

    <?php if ($register_message): ?>
    <div class="text-green-500 text-sm text-center mb-4"><?= $register_message ?></div>
    <?php endif; ?>
    <?php if ($register_error): ?>
    <div class="text-red-400 text-sm text-center mb-4"><?= $register_error ?></div>
    <?php endif; ?>

    <form method="POST" onsubmit="return validateForm()" class="space-y-6">
    <div>
        <label for="username" class="block text-sm font-medium mb-1 text-custom-secondary">Username</label>
        <input 
        type="text" 
        id="username" 
        name="username"
        required 
        class="w-full px-4 py-2 bg-custom-tertiary border border-custom rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-custom-primary"
        placeholder="Enter a unique username"
        >
    </div>

    <div>
        <label for="password" class="block text-sm font-medium mb-1 text-custom-secondary">Password</label>
        <input 
        type="password" 
        id="password" 
        name="password"
        required 
        class="w-full px-4 py-2 bg-custom-tertiary border border-custom rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-custom-primary"
        placeholder="Create a password"
        >
    </div>

    <div>
        <label for="confirm" class="block text-sm font-medium mb-1 text-custom-secondary">Confirm Password</label>
        <input 
        type="password" 
        id="confirm" 
        required 
        class="w-full px-4 py-2 bg-custom-tertiary border border-custom rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-custom-primary"
        placeholder="Repeat password"
        >
    </div>

    <div id="form-error" class="text-red-400 text-sm text-center hidden"></div>

    <button 
        type="submit" 
        name="register"
        id="register-btn" 
        class="w-full py-2 bg-accent bg-accent-hover text-white rounded-md font-medium transition-colors"
    >
        Create Account
    </button>
    </form>

    <div class="text-center mt-6">
      <p class="text-custom-tertiary text-sm">
        Already have an account? 
        <a href="login.php" class="text-accent hover:underline">Sign in</a>
      </p>
    </div>
  </div>

  <script>
    function updateThemeText() {
      const isDark = document.documentElement.classList.contains('dark');
      document.getElementById('theme-icon-dark').classList.toggle('hidden', !isDark);
      document.getElementById('theme-icon-light').classList.toggle('hidden', isDark);
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

    function validateForm() {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm').value;
    const errorDiv = document.getElementById('form-error');

    if (password.length < 6) {
      errorDiv.textContent = "Password must be at least 6 characters";
      errorDiv.classList.remove("hidden");
      return false;
    }

    if (password !== confirm) {
      errorDiv.textContent = "Passwords do not match";
      errorDiv.classList.remove("hidden");
      return false;
    }

    errorDiv.classList.add("hidden");
    return true;
  }
  </script>
</body>
</html>
