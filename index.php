<!doctype html>
<html class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Classly: Your Campus Life, Organized</title>
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

    /* Sidebar always full height */
    .sidebar-fixed {
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      width: 16rem;
      z-index: 40;
    }

    /* Main content with proper margin */
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

  <!-- Landing Page (untuk user yang belum login) -->
  <div id="landing-page" class="min-h-screen bg-custom-primary">
    <!-- Header -->
    <header class="flex justify-between items-center px-6 lg:px-8 py-6 border-b border-custom bg-custom-secondary">
      <div class="flex items-center">
        <h1 class="text-2xl font-bold text-custom-primary">Classly</h1>
      </div>
      <div class="flex items-center gap-4">
        <!-- Theme Toggle -->
        <button onclick="toggleTheme()" class="p-2 bg-custom-tertiary border border-custom rounded-md hover:bg-custom-secondary transition-colors">
          <svg id="theme-icon-dark" class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707-.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
          </svg>
          <svg id="theme-icon-light" class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
          </svg>
        </button>
        <div class="flex gap-3">
          <a href="login.php" class="bg-accent bg-accent-hover text-white px-4 py-2 rounded-md font-medium transition-colors">
            Login
          </a>
          <a href="register.php" class="bg-custom-tertiary hover:bg-custom-secondary text-custom-primary px-4 py-2 rounded-md font-medium transition-colors border border-custom">
            Sign Up
          </a>
        </div>
      </div>
    </header>

    <!-- Hero Section -->
    <main class="flex-1 flex items-center justify-center px-6 lg:px-8 py-12">
      <div class="text-center max-w-3xl mx-auto">
        <h1 class="text-4xl lg:text-6xl font-bold mb-6 text-custom-primary">
          Welcome to <span class="text-accent">Classly</span>
        </h1>
        <p class="text-xl lg:text-2xl text-custom-tertiary mb-12 max-w-2xl mx-auto">
          Your Campus Life, Organized.
        </p>

        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <a href="register.php" class="bg-accent bg-accent-hover text-white px-8 py-3 rounded-md font-medium text-lg transition-colors">
            Get Started Free
          </a>
          <a href="login.php" class="bg-custom-secondary hover:bg-custom-tertiary text-custom-primary px-8 py-3 rounded-md font-medium text-lg transition-colors border border-custom">
            Sign In
          </a>
        </div>
      </div>
    </main>
  </div>

  <script>
    // Check if user is logged in
    function checkAuth() {
      const user = JSON.parse(localStorage.getItem('classly-user') || 'null');
      const landingPage = document.getElementById('landing-page');
      const dashboard = document.getElementById('dashboard');
      const usernameElement = document.getElementById('username');
      
      if (user) {
        // User is logged in, show dashboard
        landingPage.classList.add('hidden');
        dashboard.classList.remove('hidden');
        if (usernameElement) {
          usernameElement.textContent = user.name || 'User';
        }
      } else {
        // User is not logged in, show landing page
        landingPage.classList.remove('hidden');
        dashboard.classList.add('hidden');
      }
    }

    // Update theme text based on current mode
    function updateThemeText() {
      const isDark = document.documentElement.classList.contains('dark');
      
      // Landing page theme icons
      const darkIcon = document.getElementById('theme-icon-dark');
      const lightIcon = document.getElementById('theme-icon-light');
      
      // Sidebar theme icons
      const darkIconSidebar = document.getElementById('theme-icon-dark-sidebar');
      const lightIconSidebar = document.getElementById('theme-icon-light-sidebar');
      const darkText = document.querySelector('.theme-text-dark');
      const lightText = document.querySelector('.theme-text-light');
      
      if (isDark) {
        if (darkIcon) {
          darkIcon.classList.remove('hidden');
          lightIcon.classList.add('hidden');
        }
        if (darkIconSidebar) {
          darkIconSidebar.classList.remove('hidden');
          lightIconSidebar.classList.add('hidden');
        }
        if (darkText) darkText.textContent = 'Light Mode';
        if (lightText) lightText.textContent = 'Light Mode';
      } else {
        if (darkIcon) {
          darkIcon.classList.add('hidden');
          lightIcon.classList.remove('hidden');
        }
        if (darkIconSidebar) {
          darkIconSidebar.classList.add('hidden');
          lightIconSidebar.classList.remove('hidden');
        }
        if (darkText) darkText.textContent = 'Dark Mode';
        if (lightText) lightText.textContent = 'Dark Mode';
      }
    }

    // Theme toggle functionality
    function toggleTheme() {
      const html = document.documentElement;
      const isDark = html.classList.contains('dark');
      
      if (isDark) {
        html.classList.remove('dark');
        localStorage.setItem('theme', 'light');
      } else {
        html.classList.add('dark');
        localStorage.setItem('theme', 'dark');
      }
      updateThemeText();
    }

    // Load saved theme
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

    function logout() {
      localStorage.removeItem('classly-user');
      checkAuth();
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      loadTheme();
      checkAuth();
    });
  </script>
</body>
</html>