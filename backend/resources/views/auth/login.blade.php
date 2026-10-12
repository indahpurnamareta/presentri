<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Masuk | Presentri</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- Google Fonts - Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

  <script>
    // Konfigurasi Kustom Tailwind untuk Aksen Emerald & Teal
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          fontFamily: {
            sans: ['Plus Jakarta Sans', 'sans-serif'],
          },
          colors: {
            brand: {
              50: '#ecfdf5',
              100: '#d1fae5',
              500: '#059669',
              600: '#059669',
              700: '#047857',
              teal: '#14b8a6',
              deep: '#011c15',
            }
          }
        }
      }
    }
  </script>

  <style>
    /* Animasi Lembut Saat Membuka Halaman */
    @keyframes fadeSlide {
      from {
        opacity: 0;
        transform: translateY(24px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    .fade-slide-in {
      animation: fadeSlide 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
  </style>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-[#0b111e] flex items-center justify-center px-4 relative overflow-hidden transition-colors duration-300">

  <!-- Hiasan Dekoratif Latar Belakang (Aksen Lensa Cahaya Hijau Lembut) -->
  <div class="absolute -top-40 -right-40 w-96 height-96 bg-brand-500/10 dark:bg-brand-500/5 rounded-full blur-3xl pointer-events-none"></div>
  <div class="absolute -bottom-40 -left-40 w-96 height-96 bg-brand-teal/10 dark:bg-brand-teal/5 rounded-full blur-3xl pointer-events-none"></div>

  <!-- Container Login Card -->
  <div class="w-full max-w-md bg-white dark:bg-[#111c2a] border border-slate-100 dark:border-slate-800/60 rounded-3xl shadow-xl dark:shadow-2xl/40 p-8 md:p-10 fade-slide-in relative z-10 transition-colors duration-300">
    
    <!-- Logo & Tajuk Utama -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-500 to-brand-teal text-white shadow-lg shadow-brand-500/25 mb-4 transform hover:scale-105 transition-transform duration-300 relative">
        <!-- Logo Presentri: Presisi QR Code & Toga Icon (Sesuai Desain Utama) -->
        <i class="fas fa-qrcode text-3xl"></i>
      </div>
      <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Selamat Datang di <span class="bg-gradient-to-r from-brand-500 to-brand-teal bg-clip-text text-transparent">Presentri</span></h2>
      <p class="text-sm text-slate-500 dark:text-slate-400 font-semibold mt-1">Masuk untuk melanjutkan ke dashboard</p>
    </div>

    <!-- Alert Kesalahan/Error Kustom Premium -->
    @if (session('error'))
      <div class="flex items-center gap-3 bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/30 text-rose-700 dark:text-rose-400 p-4 rounded-2xl mb-6 text-sm font-semibold transition-all">
        <i class="fas fa-exclamation-circle text-base"></i>
        <span>{{ session('error') }}</span>
      </div>
    @endif

    <!-- Form Login Utama -->
    <form method="POST" action="{{ route('login.proses') }}" class="space-y-6">
      @csrf

      <!-- Username Input -->
      <div>
        <label for="username" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">Username</label>
        <div class="relative flex items-center bg-slate-50 dark:bg-[#1e293b] border border-slate-200 dark:border-slate-800 rounded-2xl px-4 py-3.5 focus-within:bg-white dark:focus-within:bg-[#111c2a] focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/10 dark:focus-within:ring-brand-500/5 transition-all duration-200">
          <i class="fas fa-user-circle text-slate-400 dark:text-slate-500 mr-3 text-lg transition-colors"></i>
          <input 
            type="text" 
            name="username" 
            id="username" 
            placeholder="Masukkan username Anda" 
            required
            class="bg-transparent w-full outline-none text-sm font-semibold text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500"
          >
        </div>
      </div>

      <!-- Password Input dengan Integrasi Reveal Sederhana -->
      <div>
        <div class="flex justify-between items-center mb-2">
          <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">Kata Sandi</label>
       <!--     <a href="#" class="text-xs font-bold text-brand-500 hover:text-brand-600 dark:hover:text-brand-400 hover:underline">Lupa Sandi?</a> -->
        </div>
        <div class="relative flex items-center bg-slate-50 dark:bg-[#1e293b] border border-slate-200 dark:border-slate-800 rounded-2xl px-4 py-3.5 focus-within:bg-white dark:focus-within:bg-[#111c2a] focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/10 dark:focus-within:ring-brand-500/5 transition-all duration-200">
          <i class="fas fa-lock text-slate-400 dark:text-slate-500 mr-3 text-lg transition-colors"></i>
          <input 
            type="password" 
            name="password" 
            id="password" 
            placeholder="Masukkan password Anda" 
            required
            class="bg-transparent w-full outline-none text-sm font-semibold text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500"
          >
          <button 
            type="button" 
            onclick="togglePasswordVisibility()" 
            class="focus:outline-none text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 ml-2"
          >
            <i id="eye-icon" class="fas fa-eye text-sm"></i>
          </button>
        </div>
      </div>

      <!-- Tombol Masuk Bertema Gradasi Emerald-Teal -->
      <button 
        type="submit" 
        class="w-full h-12 bg-gradient-to-r from-brand-500 to-brand-teal hover:from-brand-600 hover:to-brand-700 text-white rounded-2xl font-bold text-sm shadow-md hover:shadow-lg hover:shadow-brand-500/20 hover:-translate-y-0.5 active:translate-y-0 active:shadow-md transition-all duration-200 flex items-center justify-center gap-2"
      >
        <i class="fas fa-sign-in-alt"></i>
        <span>Masuk ke Akun</span>
      </button>
    </form>

   <!-- Opsi Registrasi Bawah Kustom -->
   <!--  <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800/80 text-center text-sm text-slate-500 dark:text-slate-400 font-semibold">-->
    <!--   Belum memiliki akun terdaftar? -->
     <!--  <a href="{{ url('/register') }}" class="text-brand-500 hover:text-brand-600 dark:hover:text-brand-400 hover:underline font-bold ml-1">Hubungi Administrator</a>-->
     <!--</div>-->
  </div>

  <!-- JavaScript Sederhana untuk Sembunyikan/Tampilkan Sandi -->
  <script>
    function togglePasswordVisibility() {
      const passwordInput = document.getElementById('password');
      const eyeIcon = document.getElementById('eye-icon');
      
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
      } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
      }
    }
  </script>

</body>
</html>