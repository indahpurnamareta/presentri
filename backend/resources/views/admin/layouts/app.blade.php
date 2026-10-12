<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="stylesheet" href="{{ asset('dist/css/custom.css') }}">

    <title>@yield('title')</title>

    <!-- Custom fonts for this template-->
   @include('admin.layouts.css')

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        @include('admin.layouts.sidebar')
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                @include('admin.layouts.topbar')
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    @yield('content')

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            @include('admin.layouts.footer')
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- jQuery (wajib untuk Bootstrap 4) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<style>
    /* ==========================================================================
       1. FORCE OVERRIDE PARENT LAYOUT (PENTING: Memaksa Layout Bawaan Menjadi Gelap)
       ========================================================================== */
    html.dark body,
    html.dark #wrapper,
    html.dark #content-wrapper,
    html.dark .content-wrapper,
    html.dark #content,
    html.dark .main-panel,
    html.dark .content,
    html.dark main,
    html.dark .container-fluid {
        background-color: #0b111e !important;
        color: #f8fafc !important;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    /* ==========================================================================
       2. REKAP IZIN BASE STYLES
       ========================================================================== */
    .saas-rekap {
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
        letter-spacing: -0.015em;
        background-color: #f8fafc;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .dark .saas-rekap {
        background-color: #0b111e !important;
    }

    /* Modifikasi Card */
    .saas-card {
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.04) !important;
        border-radius: 20px !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.01) !important;
        transition: all 0.3s ease;
    }

    .dark .saas-card {
        background: #111c2a !important;
        border-color: rgba(255, 255, 255, 0.05) !important;
        box-shadow: none !important;
    }

    /* Form Control Minimalis (Input, Select, dll) */
    .saas-input, .saas-select {
        background-color: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        font-size: 0.85rem !important;
        font-weight: 500 !important;
        color: #1e293b !important;
        height: 44px !important;
        padding: 0.5rem 1rem !important;
        transition: all 0.2s ease !important;
    }

    .saas-input:focus, .saas-select:focus {
        background-color: #ffffff !important;
        border-color: #059669 !important;
        box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.12) !important;
    }

    .dark .saas-input, .dark .saas-select {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #ffffff !important;
    }

    .dark .saas-input:focus, .dark .saas-select:focus {
        background-color: #111c2a !important;
        border-color: #34d399 !important;
        box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.15) !important;
    }

    /* Tombol Aksi */
    .btn-search-emerald {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
        border: none !important;
        color: #ffffff !important;
        height: 44px !important;
        border-radius: 12px !important;
        font-weight: 700 !important;
        font-size: 0.875rem !important;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2) !important;
        transition: all 0.25s ease !important;
        cursor: pointer;
    }

    .btn-search-emerald:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(5, 150, 105, 0.3) !important;
    }

    .btn-pdf-rose {
        background-color: #fff1f2 !important;
        border: 1px solid #fecdd3 !important;
        color: #e11d48 !important;
        padding: 0.6rem 1.25rem !important;
        border-radius: 12px !important;
        font-weight: 700 !important;
        font-size: 0.85rem !important;
        transition: all 0.25s ease !important;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }

    .btn-pdf-rose:hover {
        background-color: #ffe4e6 !important;
        border-color: #fda4af !important;
        transform: translateY(-2px);
    }

    .dark .btn-pdf-rose {
        background-color: rgba(244, 63, 94, 0.1) !important;
        border-color: rgba(244, 63, 94, 0.2) !important;
        color: #fb7185 !important;
    }

    /* Tabel Modern */
    .table-responsive {
        border-radius: 16px;
        overflow: hidden;
    }

 /* ==========================================================================
       3. OPTIMALISASI TABEL MINIMALIS, TIPIS & RAPI (FONT KECIL & TIDAK TEBAL)
       ========================================================================== */
    .saas-table {
        table-layout: auto;
        width: 100%;
        margin-bottom: 0 !important;
    }

    .saas-table th {
        background-color: #f8fafc !important;
        color: #64748b !important; /* Warna text lebih soft */
        font-size: 0.75rem !important; /* Ukuran kecil profesional */
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        font-weight: 600 !important; /* Sedikit semi-bold hanya untuk header agar terbaca */
        padding: 0.6rem 0.75rem !important; /* Jarak padding dirapatkan */
        border-bottom: 1px solid #e2e8f0 !important;
        white-space: nowrap;
    }

    .dark .saas-table th {
        background-color: #1f2937 !important;
        color: #94a3b8 !important;
        border-bottom: 1px solid #374151 !important;
    }

    .saas-table td {
        padding: 0.6rem 0.75rem !important; /* Jarak padding dirapatkan */
        font-size: 0.775rem !important; /* Ukuran teks diperkecil */
        font-weight: 400 !important; /* DIPAKSA TIPIS (Normal/Tidak Bold) */
        color: #334155 !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }

    .dark .saas-table td {
        color: #cbd5e1 !important;
        border-bottom: 1px solid #1f2937 !important;
    }

    /* Kolom dengan teks pendek tetap satu baris */
    .kolom-pas {
        white-space: nowrap;
        width: 1%;
    }

    /* Badges Status dibuat lebih tipis dan kecil */
    .badge-status {
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 600;
        display: inline-block;
        white-space: nowrap;
    }

    /* Badges Status Pengajuan (Pending, Disetujui, Ditolak) */
    .badge-status {
        padding: 0.4rem 0.75rem !important;
        border-radius: 8px !important;
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        display: inline-block;
        text-align: center;
    }

    .badge-approved { background-color: rgba(16, 185, 129, 0.08) !important; color: #10b981 !important; }
    .badge-rejected { background-color: rgba(244, 63, 94, 0.08) !important; color: #f43f5e !important; }
    .badge-pending { background-color: rgba(245, 158, 11, 0.08) !important; color: #f59e0b !important; }

    .dark .badge-approved { background-color: rgba(16, 185, 129, 0.15) !important; color: #34d399 !important; }
    .dark .badge-rejected { background-color: rgba(244, 63, 94, 0.15) !important; color: #fb7185 !important; }
    .dark .badge-pending { background-color: rgba(245, 158, 11, 0.15) !important; color: #fbbf24 !important; }

    /* Badges Jenis Pengajuan (Izin, Sakit, Cuti, Dinas Luar) */
    .badge-type {
        padding: 0.35rem 0.65rem !important;
        border-radius: 6px !important;
        font-size: 0.725rem !important;
        font-weight: 600 !important;
        display: inline-block;
    }
    
        .badge-active { background-color: rgba(16, 185, 129, 0.08) !important; color: #10b981 !important; }
    .badge-inactive { background-color: rgba(244, 63, 94, 0.08) !important; color: #f43f5e !important; }

    .dark .badge-active { background-color: rgba(16, 185, 129, 0.15) !important; color: #34d399 !important; }
    .dark .badge-inactive { background-color: rgba(244, 63, 94, 0.15) !important; color: #fb7185 !important; }
    
    .type-izin { background-color: rgba(14, 165, 233, 0.06) !important; color: #0ea5e9 !important; }
    .type-sakit { background-color: rgba(245, 158, 11, 0.06) !important; color: #d97706 !important; }
    .type-cuti { background-color: rgba(139, 92, 246, 0.06) !important; color: #8b5cf6 !important; }
    .type-dinas { background-color: rgba(20, 184, 166, 0.06) !important; color: #0d9488 !important; }

    .dark .type-izin { background-color: rgba(14, 165, 233, 0.15) !important; color: #38bdf8 !important; }
    .dark .type-sakit { background-color: rgba(245, 158, 11, 0.15) !important; color: #fbbf24 !important; }
    .dark .type-cuti { background-color: rgba(139, 92, 246, 0.15) !important; color: #a78bfa !important; }
    .dark .type-dinas { background-color: rgba(20, 184, 166, 0.15) !important; color: #2dd4bf !important; }

    /* Override Desain Pagination Laravel / Bootstrap */
    .pagination .page-link {
        color: #059669 !important;
        border-radius: 8px !important;
        margin: 0 2px !important;
        border-color: #e2e8f0 !important;
        font-weight: 600 !important;
        font-size: 0.85rem !important;
        transition: all 0.2s ease;
    }

    .pagination .page-item.active .page-link {
        background-color: #059669 !important;
        border-color: #059669 !important;
        color: #ffffff !important;
    }

    .dark .pagination .page-link {
        background-color: #111c2a !important;
        border-color: #334155 !important;
        color: #cbd5e1 !important;
    }

    .dark .pagination .page-item.active .page-link {
        background-color: #34d399 !important;
        border-color: #34d399 !important;
        color: #0f172a !important;
    }

    /* Global Text Dark & Muted Overrides untuk Dark Mode */
    .dark .text-dark, .dark h1, .dark h2, .dark h3, .dark h4, .dark h5, .dark h6 {
        color: #ffffff !important;
    }
    .dark .text-muted {
        color: #94a3b8 !important;
    }

    /* --- PERBAIKAN DARK MODE UNTUK TABEL SAAS --- */

/* Menyesuaikan teks judul nama guru agar adaptif */
.saas-table-text-primary {
    color: #1e293b; /* Warna gelap saat light-mode */
}

/* Ketika beralih ke Mode Gelap (.dark) */
.dark .card.saas-card {
    background: #1e293b !important;
    border-color: #334155 !important;
}

.dark .saas-table {
    color: #cbd5e1 !important; /* Warna teks isi tabel jadi putih abu-abu */
}

.dark .saas-table-text-primary {
    color: #f8fafc !important; /* Nama guru otomatis jadi putih terang */
}

.dark .saas-table thead th {
    background-color: #0f172a !important; /* Header tabel jadi deep dark */
    color: #94a3b8 !important;
    border-bottom-color: #334155 !important;
}

.dark .saas-table tbody tr {
    background-color: #1e293b !important;
}

.dark .saas-table tbody tr:hover {
    background-color: #24334d !important; /* Efek hover baris tabel saat dark mode */
}

.dark .saas-table td {
    border-top-color: #334155 !important; /* Garis pembatas td yang halus */
}

.dark hr {
    border-top-color: rgba(255, 255, 255, 0.08) !important; /* Garis pembatas form-tabel beradaptasi */    
}

/* --- DARK MODE OVERRIDES --- */

/* 1. Mengubah background dan border log-item */
.dark .log-item {
    background-color: #1e293b !important; 
    border-color: #334155 !important;
}

/* 2. Mengubah teks agar terbaca di background gelap */
.dark .text-dark {
    color: #f1f5f9 !important;
}

.dark .text-muted {
    color: #94a3b8 !important;
}

/* 3. Menyesuaikan background ikon agar tidak terlalu terang */
.dark .log-item [class*="bg-"] {
    /* Jika Anda menggunakan class bg-*, sesuaikan di sini */
    filter: brightness(0.8);
}

/* Khusus untuk background ikon yang inline (seperti rgba(13, 202, 240, 0.1)) */
.dark .log-item div[style*="background"] {
    background: rgba(255, 255, 255, 0.05) !important;
}

/* --- Footer Standar (Mode Terang) --- */
.saas-footer {
    background-color: #f8fafc !important; /* Warna terang yang matching dengan dashboard saas Anda */
    border-top: 1px solid rgba(0, 0, 0, 0.04);
}

.saas-footer-text {
    color: #94a3b8; 
    font-size: 0.8rem; 
    font-weight: 500; 
    letter-spacing: 0.3px;
}

/* --- Footer Saat Mode Gelap --- */
.dark .saas-footer {
    background-color: #0f172a !important; /* Mengikuti warna dasar background dark mode (slate-900) */
    border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
}

.dark .saas-footer-text {
    color: #64748b !important; /* Warna teks melunak agar tidak terlalu mencolok di mode gelap */
}
</style>
    