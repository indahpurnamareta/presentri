<style>
    /* Style Khusus Topbar Modern */
    .topbar-modern {
        background: #ffffff !important;
        border: 1px solid rgba(0, 0, 0, 0.04) !important;
        border-radius: 20px !important;
        padding: 0.6rem 1.25rem !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.01) !important;
        display: flex;
        align-items: center;
        width: 100%;
        transition: all 0.3s ease;
    }

    /* Dukungan Otomatis Mode Gelap */
    .dark .topbar-modern {
        background: #0f172a !important;
        border-color: rgba(255, 255, 255, 0.05) !important;
        box-shadow: none !important;
    }

    /* Tombol Toggle Tema */
    .btn-theme-toggle {
        width: 38px;
        height: 38px;
        border-radius: 11px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        cursor: pointer;
        transition: all 0.25s ease;
        padding: 0;
    }
    .btn-theme-toggle:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #cbd5e1;
    }
    .dark .btn-theme-toggle {
        background: #1e293b;
        border-color: #334155;
        color: #94a3b8;
    }
    .dark .btn-theme-toggle:hover {
        background: #334155;
        color: #ffffff;
    }

    /* Avatar Minimalis */
    .user-avatar {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #059669 0%, #14b8a6 100%);
        color: #ffffff;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 0.85rem;
        box-shadow: 0 4px 10px rgba(5, 150, 105, 0.15);
    }
    
    .dark .user-detail h6 {
        color: #ffffff !important;
    }
    .dark .user-detail small {
        color: #94a3b8 !important;
    }

    /* DROPDOWN PROFIL PREMIUM */
    .profile-dropdown-toggle {
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 12px;
        transition: all 0.2s ease;
    }
    .profile-dropdown-toggle:hover {
        background: #f8fafc;
    }
    .dark .profile-dropdown-toggle:hover {
        background: #1e293b;
    }

    /* Menghilangkan panah default bootstrap jika diinginkan */
    .profile-dropdown-toggle::after {
        display: inline-block;
        margin-left: 0.55em;
        vertical-align: 0.255em;
        color: #94a3b8;
    }

    .topbar-dropdown-menu {
        border: none !important;
        border-radius: 16px !important;
        padding: 0.5rem !important;
        min-width: 210px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08) !important;
        margin-top: 15px !important;
        transform: translateY(10px);
        transition: all 0.3s ease;
    }
    .dark .topbar-dropdown-menu {
        background: #1e293b !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
    }

    .topbar-dropdown-menu .dropdown-header-user {
        padding: 0.5rem 0.8rem 0.8rem 0.8rem;
        border-bottom: 1px solid #f1f5f9;
        margin-bottom: 0.4rem;
    }
    .dark .topbar-dropdown-menu .dropdown-header-user {
        border-bottom-color: #334155;
    }

    .topbar-dropdown-menu .dropdown-item {
        border-radius: 10px !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        padding: 0.6rem 0.8rem !important;
        color: #475569 !important;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.2s ease;
    }
    .dark .topbar-dropdown-menu .dropdown-item {
        color: #cbd5e1 !important;
    }

    .topbar-dropdown-menu .dropdown-item:hover {
        background: #f1f5f9 !important;
        color: #0f172a !important;
    }
    .dark .topbar-dropdown-menu .dropdown-item:hover {
        background: #334155 !important;
        color: #ffffff !important;
    }

    /* Logout Hover Effect khusus (Merah Soft) */
    .topbar-dropdown-menu .dropdown-item.logout-item:hover {
        background: #fff1f2 !important;
        color: #e11d48 !important;
    }
    .dark .topbar-dropdown-menu .dropdown-item.logout-item:hover {
        background: #4c1d24 !important;
        color: #fda4af !important;
    }
    /* Menyelaraskan teks berlapis agar presisi */
.d-flex.flex-column {
    line-height: 1.3;
}

/* Dukungan Mode Gelap untuk teks judul baru */
.dark .text-dark {
    color: #f8fafc !important;
}
.dark .text-muted {
    color: #64748b !important;
}

/* CSS Opsional jika memakai Opsi 1 (Efek Text Gradient Berwarna Jumper) */
.text-gradient-saas {
    background: linear-gradient(135deg, #0f172a 0%, #059669 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.dark .text-gradient-saas {
    background: linear-gradient(135deg, #ffffff 0%, #10b981 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
</style>

<nav class="navbar navbar-expand topbar-modern mb-4">
    
    <button id="sidebarToggleTop" class="btn d-md-none mr-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; color: #64748b;">
        <i class="fas fa-bars"></i>
    </button>

<div class="d-none d-sm-flex align-items-center">
    <span class="badge px-3 py-1.5 rounded-pill font-weight-bold uppercase" style="background: rgba(5, 150, 105, 0.08); color: #059669; font-size: 0.725rem; letter-spacing: 0.05em;">
        Live Control
    </span>
    <div class="mx-3" style="width: 1px; height: 24px; background-color: #e2e8f0;"></div>
    <div class="d-flex flex-column text-left" style="line-height: 1.2;">
        <h5 class="mb-0 font-weight-bold text-dark" style="font-size: 0.875rem; letter-spacing: -0.01em;">
            @php
                $hour = date('H');
                $sapaan = 'Selamat Malam';
                if ($hour >= 5 && $hour < 11) $sapaan = 'Selamat Pagi';
                elseif ($hour >= 11 && $hour < 15) $sapaan = 'Selamat Siang';
                elseif ($hour >= 15 && $hour < 18) $sapaan = 'Selamat Sore';
            @endphp
            {{ $sapaan }}, {{ strtok(Auth::user()->nama ?? 'Admin', ' ') }}! 
        </h5>
        <small class="text-muted font-weight-medium mt-0.5" style="font-size: 0.725rem; color: #94a3b8 !important;">
            Presentri Admin Page
        </small>
    </div>
</div>

    <ul class="navbar-nav ml-auto align-items-center flex-row">

        <li class="nav-item mr-2">
            <button id="themeToggleBtn" class="btn btn-theme-toggle" title="Ganti Tema">
                <i class="fas fa-sun text-warning sun-icon"></i>
                <i class="fas fa-moon text-emerald-400 moon-icon d-none"></i>
            </button>
        </li>
        <li class="nav-item mr-2">

    <button
        class="btn btn-theme-toggle"
        data-toggle="modal"
        data-target="#aboutModal"
        title="Tentang Aplikasi"
    >

        <i class="fas fa-info-circle"></i>

    </button>

</li>

        <div class="mr-3 d-none d-sm-block" style="width: 1px; height: 24px; background-color: #e2e8f0;"></div>

        <li class="nav-item dropdown">
            <div class="d-flex align-items-center profile-dropdown-toggle dropdown-toggle" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <div class="user-avatar mr-2">
                    {{ strtoupper(substr(Auth::user()->nama ?? Auth::user()->name ?? 'A', 0, 1)) }}
                </div>
                    <div class="user-detail d-none d-md-block text-left mr-1">
                        <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 0.85rem; line-height: 1.2;">
                            {{ Auth::user()->nama ?? 'Administrator' }}
                        </h6>
                        <small class="text-muted" style="font-size: 0.725rem; font-weight: 500;">
                            {{ ucwords(str_replace('_', ' ', Auth::user()->role ?? 'User')) }}
                        </small>
                    </div>
            </div>

            <div class="dropdown-menu dropdown-menu-right topbar-dropdown-menu" aria-labelledby="userDropdown">
                    <div class="dropdown-header-user d-block d-md-none">
                        <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 0.85rem;">
                            {{ Auth::user()->nama ?? 'Administrator' }}
                        </h6>
                        <small class="text-muted" style="font-size: 0.725rem;">
                            {{ ucwords(str_replace('_', ' ', Auth::user()->role ?? 'User')) }}
                        </small>
                    </div>

                <a class="dropdown-item" href="{{ route('profile.edit') ?? '#' }}">
                    <i class="fas fa-user-circle text-muted" style="font-size: 1rem;"></i>
                    <span>Lihat Profil</span>
                </a>


                <div class="dropdown-divider" style="border-top-color: #f1f5f9; margin: 0.5rem 0;"></div>

                <a class="dropdown-item logout-item text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Keluar Aplikasi</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" id="logoutForm" style="display: none;">
                    @csrf
                </form>
            </div>
        </li>

    </ul>

</nav>

<!-- Modal Tentang Aplikasi -->

<div class="modal fade" id="aboutModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow-lg" style="border-radius:20px;">

            <div class="modal-header border-0 pb-0">

                <h5 class="modal-title font-weight-bold">

                    <i class="fas fa-qrcode text-success mr-2"></i>

                    Presentri

                </h5>

                <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                >

                    <span>&times;</span>

                </button>

            </div>

            <div class="modal-body text-center">

                <div
                    style="
                    width:80px;
                    height:80px;
                    background:linear-gradient(135deg,#059669,#14b8a6);
                    border-radius:24px;
                    margin:auto;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    "
                >

                    <i
                        class="fas fa-qrcode"
                        style="
                        color:white;
                        font-size:35px;
                        "
                    ></i>

                </div>

                <h4 class="mt-4 font-weight-bold">

                    Presentri

                </h4>

                <p class="text-muted">

                    Sistem Presensi dan Perizinan

                    Guru dan Tenaga Kependidikan (GTK)

                    berbasis mobile dan web.

                </p>

                <hr>

                <p class="mb-1">

                    <strong>Versi</strong>

                    1.0

                </p>

                <p class="mb-1">

                    Dikembangkan oleh

                </p>

                <h6 class="font-weight-bold">

                    Indah Purnama Reta

                </h6>

                <small class="text-muted">

                    Pendidikan Teknik Informatika

                    <br>

                    Universitas Negeri Padang

                </small>

            </div>

        </div>

    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const sunIcon = themeToggleBtn.querySelector('.sun-icon');
        const moonIcon = themeToggleBtn.querySelector('.moon-icon');
        const appHtml = document.documentElement;

        // 1. Cek preference tema dari localStorage saat halaman dimuat
        const savedTheme = localStorage.getItem('theme');
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
            appHtml.classList.add('dark');
            sunIcon.classList.add('d-none');
            moonIcon.classList.remove('d-none');
        } else {
            appHtml.classList.remove('dark');
            sunIcon.classList.remove('d-none');
            moonIcon.classList.add('d-none');
        }

        // 2. Event Listener klik tombol tema
        themeToggleBtn.addEventListener('click', function() {
            if (appHtml.classList.contains('dark')) {
                appHtml.classList.remove('dark');
                localStorage.setItem('theme', 'light');
                sunIcon.classList.remove('d-none');
                moonIcon.classList.add('d-none');
            } else {
                appHtml.classList.add('dark');
                localStorage.setItem('theme', 'dark');
                sunIcon.classList.add('d-none');
                moonIcon.classList.remove('d-none');
            }
        });
    });
</script>