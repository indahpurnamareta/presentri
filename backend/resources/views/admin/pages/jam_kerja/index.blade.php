@extends('admin.layouts.app')

@section('title', 'Jam Kerja')

@section('content')

<!-- Memasukkan Google Font "Plus Jakarta Sans" & Icon Tambahan jika belum ada -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

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
       2. JAM KERJA BASE STYLES
       ========================================================================== */
    .saas-jk {
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
        letter-spacing: -0.015em;
        background-color: #f8fafc;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .dark .saas-jk {
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
    .btn-emerald {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
        border: none !important;
        color: #ffffff !important;
        height: 44px !important;
        border-radius: 12px !important;
        font-weight: 700 !important;
        font-size: 0.85rem !important;
        padding: 0 1.25rem !important;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2) !important;
        transition: all 0.25s ease !important;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }

    .btn-emerald:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(5, 150, 105, 0.3) !important;
    }

    /* Tombol Aksi Ikon Tunggal */
    .btn-action-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s ease;
        margin: 0 2px;
    }
    
    .dark .btn-action-icon {
        background: #111c2a;
        border-color: #334155;
    }

    .btn-edit-icon { color: #f59e0b; }
    .btn-edit-icon:hover { background: rgba(245, 158, 11, 0.08); border-color: #fbbf24; }

    .btn-delete-icon { color: #f43f5e; }
    .btn-delete-icon:hover { background: rgba(244, 63, 94, 0.08); border-color: #fb7185; }

    /* Alert Kustom Premium */
    .saas-alert {
        border: none !important;
        border-radius: 14px !important;
        padding: 1rem 1.25rem !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .saas-alert-success { background-color: rgba(16, 185, 129, 0.08) !important; color: #10b981 !important; }


    /* Badges & Tags */
    .badge-custom {
        padding: 0.35rem 0.65rem !important;
        border-radius: 8px !important;
        font-size: 0.725rem !important;
        font-weight: 700 !important;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .badge-code { background-color: rgba(99, 102, 241, 0.08) !important; color: #6366f1 !important; }
    .dark .badge-code { background-color: rgba(99, 102, 241, 0.15) !important; color: #818cf8 !important; }

    /* Modal Styling Kustom */
    .saas-modal .modal-content {
        border: none !important;
        border-radius: 24px !important;
        background: #ffffff !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1) !important;
    }

    .dark .saas-modal .modal-content {
        background: #111c2a !important;
        border: 1px solid rgba(255, 255, 255, 0.05) !important;
    }

    .saas-modal .modal-header {
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 1.5rem 1.75rem !important;
    }

    .dark .saas-modal .modal-header {
        border-bottom-color: rgba(255, 255, 255, 0.05) !important;
    }

    .saas-modal .modal-header h5 {
        font-weight: 800 !important;
        letter-spacing: -0.02em !important;
        color: #0f172a !important;
    }

    .dark .saas-modal .modal-header h5 {
        color: #ffffff !important;
    }

    .saas-modal .modal-header .close {
        color: #64748b !important;
        opacity: 0.8 !important;
        font-size: 1.5rem !important;
    }

    .saas-modal .modal-body {
        padding: 1.75rem !important;
    }

    .saas-modal .modal-body label {
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        color: #64748b !important;
        letter-spacing: 0.05em !important;
        margin-bottom: 0.5rem !important;
    }
    
    .dark .saas-modal .modal-body label {
        color: #94a3b8 !important;
    }

    .saas-modal .modal-footer {
        border-top: 1px solid #f1f5f9 !important;
        padding: 1.25rem 1.75rem !important;
    }

    .dark .saas-modal .modal-footer {
        border-top-color: rgba(255, 255, 255, 0.05) !important;
    }

    /* Styling Kompak untuk Pagination Ramping */
    .saas-pagination .pagination { gap: 4px; }
    .saas-pagination .page-item .page-link {
        padding: 6px 12px !important;
        font-size: 0.8rem !important;
        line-height: 1.4 !important;
        color: #475569 !important;
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        transition: all 0.2s ease !important;
    }
    .dark .saas-pagination .page-item .page-link {
        color: #94a3b8 !important;
        background-color: #1e293b !important;
        border-color: #334155 !important;
    }
    .saas-pagination .page-item.active .page-link {
        background-color: #059669 !important;
        border-color: #059669 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 4px rgba(5, 150, 105, 0.2) !important;
    }
    .saas-pagination .page-item.disabled .page-link { opacity: 0.5 !important; cursor: not-allowed !important; }
    .saas-pagination .page-item:hover:not(.active):not(.disabled) .page-link {
        background-color: #f1f5f9 !important;
        border-color: #cbd5e1 !important;
    }
    .dark .saas-pagination .page-item:hover:not(.active):not(.disabled) .page-link {
        background-color: #2d3748 !important;
        border-color: #4a5568 !important;
    }

    /* Global Text Dark & Muted Overrides */
    .dark .text-dark, .dark h1, .dark h2, .dark h3, .dark h4, .dark h5, .dark h6 {
        color: #ffffff !important;
    }
    .dark .text-muted {
        color: #94a3b8 !important;
    }
</style>

<div class="container-fluid py-4 saas-jk">


    <!-- Judul Halaman -->
    <div class="mb-4">
        <h1 class="h3 font-weight-bold text-dark mb-1" style="font-size: 1.65rem; letter-spacing: -0.025em;">
            Kelola Jam Kerja
        </h1>
        <p class="text-muted mb-0" style="font-size: 0.9rem;">
            Atur parameter jadwal masuk sekolah, batas waktu dispensasi keterlambatan, serta aturan jam kepulangan guru.
        </p>
    </div>

    {{-- Alert Sukses --}}
    @if(session('success'))
        <div class="alert saas-alert saas-alert-success mb-4 shadow-sm">
            <i class="fas fa-check-circle text-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif



@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Gagal!</strong> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif



<div class="card shadow-sm border-0">
    </div>
    
    

    <!-- Header Action Filter Card -->
    <div class="card saas-card mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center">

                <!-- Search -->
                <div class="col-lg-5 col-md-6 mb-3 mb-lg-0">
                    <form method="GET">
                        <div class="input-group">
                            <input
                                type="text"
                                class="form-control saas-input"
                                name="search"
                                placeholder="Cari..."
                                value="{{ request('search') }}"
                                style="border-top-right-radius: 0px !important; border-bottom-right-radius: 0px !important;"
                            >
                            <div class="input-group-append">
                                <button class="btn btn-emerald" style="border-top-left-radius: 0px !important; border-bottom-left-radius: 0px !important; height: 44px !important; box-shadow: none !important;">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Button Tambah Jam Kerja -->
                <div class="col-lg-7 col-md-6 text-right">
                    <button
                        class="btn btn-emerald"
                        data-toggle="modal"
                        data-target="#tambahModal"
                    >
                        <i class="fas fa-plus"></i>
                        <span>Tambah Jam Kerja</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card saas-card">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table saas-table table-hover">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 60px;">No</th>
                            <th>Nama Jam Kerja</th>
                            <th class="text-center">Mode</th>
                            <th class="text-center">Jam Masuk</th>
                            <th class="text-center">Batas Jam Masuk</th>
                            <th class="text-center">Jam Pulang</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jamKerjas as $index => $jk)
                        <tr>
                            <td class="text-center font-weight-bold text-muted">
                                {{ $jamKerjas->firstItem() + $index }}
                            </td>
  
                            
                            <td>
                                <div class="font-weight-bold text-dark">{{ $jk->nama_jk }}</div>
                            </td>
                            <td class="text-center font-weight-bold text-muted"> 
                                <div class="font-weight-bold text-dark">
                                    @if($jk->mode == 'wfh_massal')
                                        WFH Sekolah
                                    @elseif($jk->mode == 'normal')
                                        Normal
                                    @else
                                        {{ ucfirst($jk->mode) }}
                                    @endif
                                </div>
                            </td>
                            <td class="text-center font-weight-bold text-success" style="font-size: 0.9rem;">
                                <i class="far fa-clock text-xs mr-1 opacity-70"></i>
                                {{ \Carbon\Carbon::parse($jk->jam_masuk)->format('H:i') }}
                            </td>
                            <td class="text-center font-weight-bold text-warning" style="font-size: 0.9rem;">
                                <i class="far fa-bell text-xs mr-1 opacity-70"></i>
                                {{ \Carbon\Carbon::parse($jk->batas_jam_masuk)->format('H:i') }}
                            </td>
                            <td class="text-center font-weight-bold text-danger" style="font-size: 0.9rem;">
                                <i class="far fa-circle-user text-xs mr-1 opacity-70"></i>
                                {{ \Carbon\Carbon::parse($jk->jam_pulang)->format('H:i') }}
                            </td>
                            <td class="text-center">
                                @if($jk->status == 'aktif')
                                    <span class="badge-custom badge-active">
                                        <i class="fas fa-check-circle"></i> Aktif
                                    </span>
                                @else
                                    <span class="badge-custom badge-inactive">
                                        <i class="fas fa-times-circle"></i> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <!-- Tombol Edit -->
                                <button
                                    class="btn-action-icon btn-edit-icon"
                                    data-toggle="modal"
                                    data-target="#edit{{ $jk->id_jam_kerja }}"
                                    title="Edit Jam Kerja"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>

                                <!-- Tombol Hapus -->
                                <button
                                    class="btn-action-icon btn-delete-icon"
                                    data-toggle="modal"
                                    data-target="#hapus{{ $jk->id_jam_kerja }}"
                                    title="Hapus Jam Kerja"
                                >
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>

                        {{-- MODAL EDIT JAM KERJA --}}
                        <div class="modal fade saas-modal" id="edit{{ $jk->id_jam_kerja }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.jam.kerja.update', $jk->id_jam_kerja) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Jam Kerja</h5>
                                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>Mode Jam Kerja</label>
                                                <select name="mode" class="form-control saas-select">
                                                    <option value="normal" {{ $jk->mode == 'normal' ? 'selected' : '' }}>Normal</option>
                                                    <option value="wfh_massal" {{ $jk->mode == 'wfh_massal' ? 'selected' : '' }}>WFH Massal</option>
                                                </select>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Nama Jam Kerja</label>
                                                <input type="text" name="nama_jk" class="form-control saas-input" value="{{ $jk->nama_jk }}" required placeholder="Contoh: Senin - Kamis Regular">
                                            </div>
                                            <div class="form-group">
                                                <label>Jam Masuk</label>
                                                <input type="time" name="jam_masuk" class="form-control saas-input" value="{{ $jk->jam_masuk }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Batas Jam Masuk (Dispensasi)</label>
                                                <input type="time" name="batas_jam_masuk" class="form-control saas-input" value="{{ $jk->batas_jam_masuk }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Jam Pulang</label>
                                                <input type="time" name="jam_pulang" class="form-control saas-input" value="{{ $jk->jam_pulang }}" required>
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label>Status Keaktifan</label>
                                                <select name="status" class="form-control saas-select">
                                                    <option value="aktif" {{ $jk->status == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                                    <option value="nonaktif" {{ $jk->status == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="border-radius:12px; font-weight:700; height:44px; font-size:0.85rem;">Batal</button>
                                            <button type="submit" class="btn btn-emerald">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- MODAL HAPUS JAM KERJA --}}
                        <div class="modal fade saas-modal" id="hapus{{ $jk->id_jam_kerja }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.jam.kerja.delete', $jk->id_jam_kerja) }}" method="POST">
                                        @csrf
                                        <div class="modal-body text-center py-5">
                                            <div class="text-danger mb-4">
                                                <i class="fas fa-exclamation-triangle fa-3x opacity-80"></i>
                                            </div>
                                            <h5 class="font-weight-extrabold text-dark mb-2">Hapus Jam Kerja?</h5>
                                            <p class="text-muted small mb-4">
                                                Apakah Anda yakin ingin menghapus pengaturan jam kerja <strong>{{ $jk->nama_jk }}</strong>?<br>Tindakan ini mungkin mempengaruhi rekapitulasi data absensi terkait.
                                            </p>
                                            <div class="d-flex justify-content-center gap-2" style="gap: 10px;">
                                                <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="border-radius:12px; font-weight:700; height:44px; font-size:0.85rem;">Batal</button>
                                                <button type="submit" class="btn btn-danger px-4" style="border-radius:12px; font-weight:700; height:44px; font-size:0.85rem; background:#f43f5e; border:none; box-shadow: 0 4px 12px rgba(244,63,94,0.2);">Hapus Permanen</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted mb-2">
                                    <i class="fas fa-clock fa-2x opacity-50"></i>
                                </div>
                                <h6 class="font-weight-bold text-muted">Data jam kerja kosong</h6>
                                <p class="text-muted small mb-0">Silakan tentukan shift jam kerja guru baru.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Ramping Kustom (Sesuai Desain Ramping Bebas Berantakan) -->
            <div class="mt-4 d-flex justify-content-center saas-pagination">
                {{ $jamKerjas->onEachSide(1)->links('pagination::bootstrap-4') }}
            </div>

        </div>
    </div>

</div>

{{-- MODAL TAMBAH JAM KERJA --}}
<div class="modal fade saas-modal" id="tambahModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.jam.kerja.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Jam Kerja</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Mode Jam Kerja</label>
                        <select name="mode" class="form-control saas-select">
                            <option value="normal" selected>Normal</option>
                            <option value="wfh_massal">WFH Massal</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nama Jam Kerja</label>
                        <input type="text" name="nama_jk" class="form-control saas-input" placeholder="Contoh: Full Day (Senin-Jumat)" required>
                    </div>
                    <div class="form-group">
                        <label>Jam Masuk</label>
                        <input type="time" name="jam_masuk" class="form-control saas-input" required>
                    </div>
                    <div class="form-group">
                        <label>Batas Jam Masuk (Dispensasi Terlambat)</label>
                        <input type="time" name="batas_jam_masuk" class="form-control saas-input" required>
                    </div>
                    <div class="form-group">
                        <label>Jam Pulang</label>
                        <input type="time" name="jam_pulang" class="form-control saas-input" required>
                    </div>
                    <div class="form-group">
                        <label>Status Keaktifan</label>
                        <select name="status" class="form-control saas-select">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif" selected>Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="border-radius:12px; font-weight:700; height:44px; font-size:0.85rem;">Batal</button>
                    <button type="submit" class="btn btn-emerald">
                        <i class="fas fa-save"></i>
                        <span>Simpan Jam Kerja</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection