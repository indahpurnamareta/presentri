<!DOCTYPE html>
<html lang="id" class="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Presentri - Aplikasi Presensi GTK</title>
  
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          fontFamily: {
            sans: ['Plus Jakarta Sans', 'sans-serif'],
          },
          colors: {
            emerald: {
              50: '#ecfdf5',
              100: '#d1fae5',
              200: '#a7f3d0',
              300: '#6ee7b7',
              400: '#34d399',
              500: '#10b981',
              600: '#059669',
              700: '#047857',
              800: '#065f46',
              950: '#022c22',
            }
          }
        }
      }
    }
  </script>

  <style>
    body, header, div, h1, p, a, h2, h3 {
      transition: background-color 0.4s cubic-bezier(0.16, 1, 0.3, 1), 
                  border-color 0.4s cubic-bezier(0.16, 1, 0.3, 1), 
                  color 0.4s cubic-bezier(0.16, 1, 0.3, 1), 
                  box-shadow 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* Pattern Background - Clean & Professional Grid */
    .bg-pattern {
        background-color: #faf9fc;
        background-image: linear-gradient(#e2e8f0 1px, transparent 1px), linear-gradient(90deg, #e2e8f0 1px, transparent 1px);
        background-size: 40px 40px;
        background-position: center;
    }
    .dark .bg-pattern {
        background-color: #020d09;
        background-image: linear-gradient(#0f1f18 1px, transparent 1px), linear-gradient(90deg, #0f1f18 1px, transparent 1px);
    }
  </style>
</head>
<body class="bg-pattern text-slate-800 dark:text-slate-100 h-screen flex flex-col justify-between overflow-hidden relative select-none">

  <!-- Subtle Ambient Glow -->
  <div class="absolute top-0 right-0 w-[25vw] h-[25vw] rounded-full bg-emerald-500/5 blur-[100px] pointer-events-none z-0"></div>
  <div class="absolute bottom-0 left-0 w-[20vw] h-[20vw] rounded-full bg-emerald-500/5 blur-[80px] pointer-events-none z-0"></div>

  <!-- Header -->
  <header class="w-full max-w-7xl mx-auto px-6 py-4 flex items-center justify-between relative z-10 shrink-0">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-700 to-emerald-500 flex items-center justify-center text-white shadow-md">
        <i class="fas fa-qrcode text-lg"></i>
      </div>
      <div>
        <h1 class="text-lg font-extrabold tracking-tight leading-none text-slate-950 dark:text-white">Presentri</h1>
        <small class="text-[9px] font-bold text-emerald-700 dark:text-emerald-400 tracking-wider uppercase">Attandance App</small>
      </div>
    </div>

    <button id="themeToggle" class="w-9 h-9 rounded-full bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:border-emerald-300 dark:hover:border-emerald-700 flex items-center justify-center relative overflow-hidden shadow-sm" title="Ganti Tema">
      <i class="fas fa-sun text-amber-500 text-xs transition-all duration-300 dark:rotate-90 dark:scale-0 absolute"></i>
      <i class="fas fa-moon text-teal-400 text-xs transition-all duration-300 rotate-90 scale-0 dark:rotate-0 dark:scale-100 absolute"></i>
    </button>
  </header>

  <!-- Hero Section (One Screen Fit) -->
  <main class="w-full max-w-5xl mx-auto px-6 flex flex-col items-center justify-center text-center flex-1 relative z-10 py-4 gap-6 md:gap-8">
    
    <!-- Badge Status -->
    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-950/70 border border-emerald-200 dark:border-emerald-900 shrink-0">
      <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
      <span class="text-[11px] font-bold text-emerald-800 dark:text-emerald-300 tracking-wide uppercase">Solusi Presensi Modern GTK</span>
    </div>

    <!-- Main Headline -->
    <div class="space-y-3 max-w-4xl">
      <h2 class="text-3xl sm:text-5xl md:text-6xl font-black text-slate-950 dark:text-white tracking-tighter leading-[1.1]">
        Kelola Kehadiran Guru & Tendik <br> secara 
        <span class="text-emerald-600 dark:text-emerald-400 relative inline-block mx-1">
          Presisi
          <span class="absolute bottom-1 left-0 w-full h-1.5 bg-emerald-400/30 -z-10 rounded-full"></span>
        </span>
      </h2>
      <p class="text-sm sm:text-base md:text-lg text-slate-600 dark:text-slate-400 font-normal leading-relaxed max-w-2xl mx-auto">
        Aplikasi manajemen presensi dan perizinan guru serta tenaga kependidikan yang terintegrasi, cepat, dan akurat.
      </p>
    </div>

    <!-- Horizontal Simple Features -->
    <div class="flex flex-wrap justify-center items-center gap-3 max-w-4xl w-full">
      <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-150 dark:border-slate-800/80 shadow-sm text-xs font-semibold text-slate-700 dark:text-slate-300">
        <i class="fas fa-map-marked-alt text-emerald-600 dark:text-emerald-400"></i>
        <span>Geofencing</span>
      </div>
      <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-150 dark:border-slate-800/80 shadow-sm text-xs font-semibold text-slate-700 dark:text-slate-300">
        <i class="fas fa-qrcode text-emerald-600 dark:text-emerald-400"></i>
        <span>QR Code</span>
      </div>
      <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-150 dark:border-slate-800/80 shadow-sm text-xs font-semibold text-slate-700 dark:text-slate-300">
        <i class="fas fa-chart-line text-emerald-600 dark:text-emerald-400"></i>
        <span>Laporan</span>
      </div>
    </div>

    <!-- Main Call to Action -->
    <div class="pt-2 shrink-0">
      <a href="/login" class="inline-flex items-center justify-center gap-3 bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-800 text-white font-bold px-12 py-3.5 rounded-xl shadow-lg shadow-emerald-900/10 transition duration-200 text-sm tracking-wide">
        <span>Login Portal Admin</span>
        <i class="fas fa-arrow-right text-xs"></i>
      </a>
    </div>

  </main>

  <!-- Footer -->
  <footer class="w-full border-t border-slate-200/50 dark:border-slate-900/60 py-4 text-center relative z-10 shrink-0">
    <div class="max-w-7xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
      <p class="text-slate-400 dark:text-slate-500 font-medium">
        &copy; 2026 Presentri. All Rights Reserved.
      </p>
    </div>
  </footer>

  <!-- JavaScript -->
  <script>
    const themeToggleBtn = document.getElementById('themeToggle');
    const appHtml = document.documentElement;

    const isDark = localStorage.getItem('theme') === 'dark' || 
        (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);

    if (isDark) {
      appHtml.classList.add('dark');
    } else {
      appHtml.classList.remove('dark');
    }

    themeToggleBtn.addEventListener('click', () => {
      if (appHtml.classList.contains('dark')) {
        appHtml.classList.remove('dark');
        localStorage.setItem('theme', 'light');
      } else {
        appHtml.classList.add('dark');
        localStorage.setItem('theme', 'dark');
      }
    });
  </script>
</body>
</html>