<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* Styling Premium & Modern untuk Sidebar dengan Background Gradasi Hero Banner */
    .app-sidebar {
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
        /* Diubah menjadi gradasi 135deg Deep Forest Emerald & Teal sesuai permintaan */
        background: linear-gradient(135deg, #022c22 0%, #064e3b 100%) !important; 
        padding: 1.75rem 1.15rem !important;
        width: 260px;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        border-right: none !important;
        list-style: none;
        margin: 0;
        box-shadow: 6px 0 30px rgba(2, 44, 34, 0.3);
        transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1) !important;
    }

    /* Sinkronisasi otomatis saat Dark Mode aktif dengan Gradasi Slate/Gelap Mendalam */
    .dark .app-sidebar {
        background: linear-gradient(180deg, #0f172a 0%, #020617 100%) !important;
        border-right: 1px solid rgba(255, 255, 255, 0.04) !important;
        box-shadow: none;
    }

    /* Logo Brand Area */
    .sidebar-brand {
        display: flex;
        align-items: center;
        text-decoration: none !important;
        padding: 0.5rem 0.5rem;
        margin-bottom: 2.25rem;
        transition: all 0.3s ease;
    }

    .brand-icon {
        width: 42px;
        height: 42px;
        /* Gradasi ikon putih mewah */
        background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #022c22;
        font-size: 1.25rem;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        margin-right: 0.95rem;
        transition: all 0.3s ease;
    }

    .dark .brand-icon {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: #ffffff;
        box-shadow: 0 10px 20px rgba(16, 185, 129, 0.15);
    }

    .brand-content h5 {
        color: #ffffff;
        font-weight: 800;
        font-size: 1.25rem;
        margin: 0;
        letter-spacing: -0.03em;
    }

    .brand-content small {
        color: #6ee7b7; /* Mint cerah agar kontras elegan */
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        display: block;
        margin-top: 2px;
        opacity: 0.9;
    }

    .dark .brand-content small {
        color: #94a3b8;
    }

    /* Section Header Title */
    .sidebar-title {
        color: #34d399; /* Warna teks penanda section lebih terang dan estetik */
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        padding: 1.75rem 0.85rem 0.6rem 0.85rem;
        opacity: 0.75;
    }

    .dark .sidebar-title {
        color: #475569;
    }

    /* Nav Item Base Style */
    .app-sidebar .nav-item {
        margin-bottom: 6px;
        width: 100%;
    }

    .app-sidebar .nav-link {
        display: flex;
        align-items: center;
        padding: 0.8rem 0.95rem;
        color: #a7f3d0 !important; /* Warna teks bawaan lebih lembut */
        text-decoration: none !important;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .dark .app-sidebar .nav-link {
        color: #94a3b8 !important;
    }

    /* Nav Icon Box */
    .nav-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        margin-right: 0.95rem;
        font-size: 0.95rem;
        background: rgba(255, 255, 255, 0.06); /* Glassmorphism tipis */
        color: #a7f3d0;
        transition: all 0.25s ease;
    }

    .dark .nav-icon {
        background: rgba(255, 255, 255, 0.03);
        color: #64748b;
    }

    /* Hover States (Slightly brighter background tint) */
    .app-sidebar .nav-item:not(.active) .nav-link:hover {
        color: #ffffff !important;
        background: rgba(255, 255, 255, 0.08);
    }

    .dark .app-sidebar .nav-item:not(.active) .nav-link:hover {
        color: #ffffff !important;
        background: rgba(255, 255, 255, 0.05);
    }

    .app-sidebar .nav-item:not(.active) .nav-link:hover .nav-icon {
        background: #10b981;
        color: #ffffff;
        transform: translateY(-1px);
    }

    .dark .app-sidebar .nav-item:not(.active) .nav-link:hover .nav-icon {
        background: #334155;
        color: #e2e8f0;
    }

    /* Active State (Premium Soft Solid Background) */
    .app-sidebar .nav-item.active .nav-link {
        color: #ffffff !important; 
        background: rgba(255, 255, 255, 0.15); /* Menggunakan translusen padat di atas gradasi */
        font-weight: 600;
        box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.1), 0 8px 20px rgba(0, 0, 0, 0.1);
        border-left: 4px solid #34d399; /* Indikator aktif vertikal yang modern */
        border-radius: 4px 12px 12px 4px;
    }

    .dark .app-sidebar .nav-item.active .nav-link {
        color: #34d399 !important;
        background: rgba(52, 211, 153, 0.06);
        border-left: 4px solid #34d399;
        border-radius: 4px 12px 12px 4px;
        box-shadow: none;
    }

    .app-sidebar .nav-item.active .nav-icon {
        background: #34d399;
        color: #022c22;
    }

    .dark .app-sidebar .nav-item.active .nav-icon {
        background: rgba(52, 211, 153, 0.15);
        color: #34d399;
    }

    /* Wrapper untuk tombol toggle */
    .sidebar-toggle-wrapper {
        padding: 0 0.85rem;
        width: 100%;
    }

    .sidebar-toggle-wrapper button {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: #a7f3d0;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.25s ease;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .dark .sidebar-toggle-wrapper button {
        background: rgba(255, 255, 255, 0.03);
        border-color: rgba(255, 255, 255, 0.05);
        color: #64748b;
    }

    .sidebar-toggle-wrapper button:hover {
        background: #ffffff;
        color: #022c22;
        border-color: #ffffff;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    }

    .dark .sidebar-toggle-wrapper button:hover {
        background: #1e293b;
        color: #f8fafc;
        border-color: #334155;
    }

    /* --- KETIKA SIDEBAR MENJADI KECIL & TIPIS (TOGGLED) --- */
    .app-sidebar.toggled {
        width: 78px !important;
        padding: 1.75rem 0.5rem !important;
    }

    .app-sidebar.toggled .brand-content,
    .app-sidebar.toggled .sidebar-title,
    .app-sidebar.toggled .nav-link span,
    .app-sidebar.toggled .toggle-text {
        display: none !important;
        opacity: 0;
    }

    .app-sidebar.toggled .sidebar-title {
        height: 0;
        padding: 0;
        margin: 0;
        overflow: hidden;
    }

    .app-sidebar.toggled .sidebar-brand,
    .app-sidebar.toggled .nav-link,
    .app-sidebar.toggled .sidebar-toggle-wrapper {
        justify-content: center !important;
        padding: 0.8rem 0 !important;
    }

    .app-sidebar.toggled .brand-icon,
    .app-sidebar.toggled .nav-icon,
    .app-sidebar.toggled .toggle-icon {
        margin-right: 0 !important;
    }

    .app-sidebar.toggled .sidebar-toggle-wrapper .toggle-text {
        display: none !important;
    }

    .app-sidebar.toggled .sidebar-toggle-wrapper .toggle-icon {
        margin-right: 0 !important;
        transform: rotate(180deg);
    }

    .app-sidebar.toggled .sidebar-toggle-wrapper button {
        width: 40px !important;
        margin: 0 auto;
    }
</style>

<ul class="navbar-nav app-sidebar" id="accordionSidebar">

    <a class="sidebar-brand" href="{{ route('admin.dashboard') }}">
        <div class="brand-icon">
            <i class="fas fa-qrcode text-lg"></i>
        </div>
        <div class="brand-content">
            <h5>Presentri</h5>
            <small>Attendance App</small>
        </div>
    </a>

    <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.dashboard') }}">
            <div class="nav-icon"><i class="fas fa-home"></i></div>
            <span>Dashboard</span>
        </a>
    </li>

    <div class="sidebar-title">Rekapitulasi</div>

    <li class="nav-item {{ request()->routeIs('admin.rekap.presensi') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.rekap.presensi') }}">
            <div class="nav-icon"><i class="fas fa-calendar-check"></i></div>
            <span>Presensi</span>
        </a>
    </li>

    <li class="nav-item {{ request()->routeIs('admin.rekap.izin') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.rekap.izin') }}">
            <div class="nav-icon"><i class="fas fa-file-alt"></i></div>
            <span>Perizinan</span>
        </a>
    </li>
    <li class="nav-item {{ request()->routeIs('admin.presensi.khusus') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.presensi.khusus') }}">
            <div class="nav-icon"><i class="fas fa-file-alt"></i></div>
            <span>Dinas Luar & WFH</span>
        </a>
    </li>

    <div class="sidebar-title">Manajemen Data</div>

    <li class="nav-item {{ request()->routeIs('admin.user') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.user') }}">
            <div class="nav-icon"><i class="fas fa-users"></i></div>
            <span>Manajemen Pengguna</span>
        </a>
    </li>

    <li class="nav-item {{ request()->routeIs('admin.lokasi') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.lokasi') }}">
            <div class="nav-icon"><i class="fas fa-map-marker-alt"></i></div>
            <span>Data Lokasi</span>
        </a>
    </li>

    <li class="nav-item {{ request()->routeIs('admin.jam.kerja') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.jam.kerja') }}">
            <div class="nav-icon"><i class="fas fa-clock"></i></div>
            <span>Jam Kerja</span>
        </a>
    </li>

    <li class="nav-item {{ request()->routeIs('admin.hari.libur') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.hari.libur') }}">
            <div class="nav-icon"><i class="fas fa-calendar-times"></i></div>
            <span>Hari Libur</span>
        </a>
    </li>

    <li class="nav-item {{ request()->routeIs('admin.qrcode') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.qrcode') }}">
            <div class="nav-icon"><i class="fas fa-qrcode"></i></div>
            <span>QR Code</span>
        </a>
    </li>

    <div class="sidebar-toggle-wrapper mt-auto mb-2">
        <button id="sidebarToggle" class="w-100">
            <i class="fas fa-angle-left mr-2 toggle-icon"></i>
            <span class="toggle-text">Minimize</span>
        </button>
    </div>

</ul>

<script>
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        const sidebar = document.querySelector('.app-sidebar');
        sidebar.classList.toggle('toggled');
    });
</script>