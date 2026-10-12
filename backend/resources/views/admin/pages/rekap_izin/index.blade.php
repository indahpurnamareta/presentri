@extends('admin.layouts.app')

@section('title', 'Rekapitulasi Izin')

@section('content')

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


    .btn-add-primary {
        background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%) !important;
        border: none !important;
        color: #ffffff !important;
        padding: 0.6rem 1.25rem !important;
        border-radius: 12px !important;
        font-weight: 700 !important;
        font-size: 0.85rem !important;
        transition: all 0.25s ease !important;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(37, 99, 243, 0.2) !important;
    }

    .btn-add-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(37, 99, 243, 0.3) !important;
    }


    .badge-approved { background-color: rgba(16, 185, 129, 0.08) !important; color: #10b981 !important; }
    .badge-rejected { background-color: rgba(244, 63, 94, 0.08) !important; color: #f43f5e !important; }
    .badge-pending { background-color: rgba(245, 158, 11, 0.08) !important; color: #f59e0b !important; }

    .dark .badge-approved { background-color: rgba(16, 185, 129, 0.15) !important; color: #34d399 !important; }
    .dark .badge-rejected { background-color: rgba(244, 63, 94, 0.15) !important; color: #fb7185 !important; }
    .dark .badge-pending { background-color: rgba(245, 158, 11, 0.15) !important; color: #fbbf24 !important; }

    /* Badges Jenis Pengajuan (Izin, Sakit, Cuti, Dinas Luar, WFH) */
    .badge-type {
        padding: 0.35rem 0.65rem !important;
        border-radius: 6px !important;
        font-size: 0.725rem !important;
        font-weight: 600 !important;
        display: inline-block;
    }
    
    .type-izin { background-color: rgba(14, 165, 233, 0.06) !important; color: #0ea5e9 !important; }
    .type-sakit { background-color: rgba(245, 158, 11, 0.06) !important; color: #d97706 !important; }
    .type-cuti { background-color: rgba(139, 92, 246, 0.06) !important; color: #8b5cf6 !important; }
    .type-dinas { background-color: rgba(20, 184, 166, 0.06) !important; color: #0d9488 !important; }
    .type-wfh { background-color: rgba(99, 102, 241, 0.06) !important; color: #6366f1 !important; }

    .dark .type-izin { background-color: rgba(14, 165, 233, 0.15) !important; color: #38bdf8 !important; }
    .dark .type-sakit { background-color: rgba(245, 158, 11, 0.15) !important; color: #fbbf24 !important; }
    .dark .type-cuti { background-color: rgba(139, 92, 246, 0.15) !important; color: #a78bfa !important; }
    .dark .type-dinas { background-color: rgba(20, 184, 166, 0.15) !important; color: #2dd4bf !important; }
    .dark .type-wfh { background-color: rgba(99, 102, 241, 0.15) !important; color: #818cf8 !important; }

    /* Modal Custom Styling */
    .modal-content.saas-modal {
        border-radius: 24px !important;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 20px 50px rgba(0,0,0,0.15) !important;
    }
    .dark .modal-content.saas-modal {
        background: #111c2a !important;
        border-color: #334155 !important;
    }
    .modal-header { border-bottom: 1px solid rgba(0,0,0,0.04); }
    .dark .modal-header { border-bottom-color: #334155; }
    .modal-footer { border-top: 1px solid rgba(0,0,0,0.04); }
    .dark .modal-footer { border-top-color: #334155; }
    
    .btn-add-emerald {
    background-color: #059669;
    color: #ffffff;
    border: 1px solid #047857;
    font-weight: 600;
    transition: all 0.2s ease;
}

.btn-add-emerald:hover {
    background-color: #047857;
    color: #ffffff;
    box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.2);
}

/* Refinement Tombol Titik 3 (Action Trigger) */
.btn-action-trigger {
    background: transparent !important;
    border: none !important;
    color: #64748b !important;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    cursor: pointer;
}

.btn-action-trigger:hover,
.btn-action-trigger:focus {
    background-color: #f1f5f9 !important;
    color: #0f172a !important;
    outline: none !important;
}

.dark .btn-action-trigger {
    color: #94a3b8 !important;
}

.dark .btn-action-trigger:hover,
.dark .btn-action-trigger:focus {
    background-color: #1e293b !important;
    color: #f8fafc !important;
}

/* Styling Dropdown Menu Aksi agar Mengambang Sempurna */
.saas-dropdown-menu {
    border-radius: 12px !important;
    border: 1px solid rgba(0, 0, 0, 0.08) !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
    padding: 0.5rem !important;
    min-width: 160px !important;
    z-index: 1050 !important;
}

.dark .saas-dropdown-menu {
    background-color: #111c2a !important;
    border-color: #334155 !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5) !important;
}

.saas-dropdown-menu .dropdown-item {
    border-radius: 8px !important;
    font-size: 0.825rem !important;
    font-weight: 600 !important;
    padding: 0.5rem 0.75rem !important;
    display: flex;
    align-items: center;
    gap: 8px;
}
</style>

<div class="container-fluid py-4 saas-rekap">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius:12px; font-weight: 600;">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h1 class="h3 font-weight-bold text-dark mb-1" style="font-size: 1.65rem; letter-spacing: -0.025em;">
                Rekapitulasi Izin
            </h1>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">
                Lihat data perizinan guru disini.
            </p>
        </div>

<div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
          {{-- Tambah Data --}}
            <button type="button"
                class="btn d-inline-flex align-items-center"
                style="
                    background-color: #059669;
                    color: #fff;
                    border: none;
                    border-radius: 8px;
                    padding: 9px 16px;
                    font-size: 13px;
                    font-weight: 500;
                    box-shadow: 0 2px 5px rgba(5, 150, 105, 0.18);
                "
                data-toggle="modal"
                data-target="#modalTambahIzin">
                <i class="fas fa-plus mr-2"></i>
                Tambah Data
            </button>    
            
            {{-- Export PDF --}}
            <a href="{{ route('admin.rekap.izin.pdf', request()->query()) }}"
                class="btn d-inline-flex align-items-center"
                style="
                    background-color: #fff;
                    color: #e11d48;
                    border: 1px solid #fecdd3;
                    border-radius: 8px;
                    padding: 9px 14px;
                    font-size: 13px;
                    font-weight: 500;
                ">
                <i class="fas fa-file-pdf mr-2"></i>
                PDF
            </a>
        
            {{-- Export Excel --}}
            <a href="{{ route('rekap-izin.export-excel', request()->query()) }}"
                class="btn d-inline-flex align-items-center"
                style="
                    background-color: #fff;
                    color: #059669;
                    border: 1px solid #a7f3d0;
                    border-radius: 8px;
                    padding: 9px 14px;
                    font-size: 13px;
                    font-weight: 500;
                ">
                <i class="fas fa-file-excel mr-2"></i>
                Excel
            </a>
</div>
    </div>

    <div class="card saas-card mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('admin.rekap.izin') }}">
<div class="row g-1 align-items-end" style="margin-right: -4px; margin-left: -4px;">

    <div class="col-12 col-md-2" style="flex: 0 0 14%; max-width: 14%; padding-right: 4px; padding-left: 4px;">
        <label class="form-label small font-weight-normal text-muted mb-1" style="font-size: 0.75rem;">Cari Nama</label>
        <input type="text" name="search" class="form-control saas-input" placeholder="Cari.." value="{{ request('search') }}" style="font-size: 0.85rem; height: 38px;">
    </div>
        <div class="col-12 col-md-2" style="flex: 0 0 13%; max-width: 13%; padding-right: 4px; padding-left: 4px;">
        <label class="form-label small font-weight-normal text-muted mb-1" style="font-size: 0.75rem;">Kategori Pegawai</label>
        <select name="role" class="form-control saas-select" style="font-size: 0.85rem; height: 38px;">
            <option value="">Semua</option>
            <option value="kepala_sekolah" {{ request('role') == 'kepala_sekolah' ? 'selected' : '' }}>Kepala Sekolah</option>
            <option value="guru" {{ request('role') == 'guru' ? 'selected' : '' }}>Guru</option>
            <option value="tenaga" {{ request('role') == 'tenaga' ? 'selected' : '' }}>Tenaga Kependidikan</option>
        </select>
    </div>

    <div class="col-12 col-md-2" style="flex: 0 0 13%; max-width: 13%; padding-right: 4px; padding-left: 4px;">
        <label class="form-label small font-weight-normal text-muted mb-1" style="font-size: 0.75rem;">Status Verifikasi</label>
        <select name="status" class="form-control saas-select" style="font-size: 0.85rem; height: 38px;">
            <option value="">Semua</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
            <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
        </select>
    </div>

    <div class="col-12 col-md-2" style="flex: 0 0 13%; max-width: 13%; padding-right: 4px; padding-left: 4px;">
        <label class="form-label small font-weight-normal text-muted mb-1" style="font-size: 0.75rem;">Jenis Pengajuan</label>
        <select name="jenis" class="form-control saas-select" style="font-size: 0.85rem; height: 38px;">
            <option value="">Semua</option>
            <option value="izin" {{ request('jenis') == 'izin' ? 'selected' : '' }}>Izin</option>
            <option value="sakit" {{ request('jenis') == 'sakit' ? 'selected' : '' }}>Sakit</option>
            <option value="cuti" {{ request('jenis') == 'cuti' ? 'selected' : '' }}>Cuti</option>
        </select>
    </div>

    <div class="col-12 col-md-1" style="flex: 0 0 12%; max-width: 12%; padding-right: 4px; padding-left: 4px;">
        <label class="form-label small font-weight-normal text-muted mb-1" style="font-size: 0.75rem;">Tanggal Awal</label>
        <input type="date" name="tanggal_awal" class="form-control saas-input px-1" value="{{ request('tanggal_awal') }}" style="font-size: 0.8rem; height: 38px;">
    </div>

    <div class="col-12 col-md-1" style="flex: 0 0 12%; max-width: 12%; padding-right: 4px; padding-left: 4px;">
        <label class="form-label small font-weight-normal text-muted mb-1" style="font-size: 0.75rem;">Tanggal Akhir</label>
        <input type="date" name="tanggal_akhir" class="form-control saas-input px-1" value="{{ request('tanggal_akhir') }}" style="font-size: 0.8rem; height: 38px;">
    </div>
    
    <div class="col-12 col-md-3" style="flex: 0 0 14%; max-width: 14%; padding-right: 4px; padding-left: 4px;">
        <label class="form-label small font-weight-normal text-muted mb-1" style="font-size: 0.75rem;">Periode Semester</label>
        <select name="semester" class="form-control saas-select" style="font-size: 0.85rem; height: 38px;">
            <option value="">Semua</option>
            <option value="2024-genap" {{ request('semester') == '2024-genap' ? 'selected' : '' }}>Genap 2024/2025 (Jan - Jun 2025)</option>
            <option value="2025-ganjil" {{ request('semester') == '2025-ganjil' ? 'selected' : '' }}>Ganjil 2025/2026 (Jul - Des 2025)</option>
            <option value="2025-genap" {{ request('semester') == '2025-genap' ? 'selected' : '' }}>Genap 2025/2026 (Jan - Jun 2026)</option>
            <option value="2026-ganjil" {{ request('semester') == '2026-ganjil' ? 'selected' : '' }}>Ganjil 2026/2027 (Jul - Des 2026)</option>
            <option value="2026-genap" {{ request('semester') == '2026-genap' ? 'selected' : '' }}>Genap 2026/2027 (Jan - Jun 2027)</option>
        </select>
    </div>

    <div class="col-12 col-md-1" style="flex: 0 0 9%; max-width: 9%; padding-right: 4px; padding-left: 4px;">
        <div class="d-flex gap-1 align-items-center">
            <button type="submit" class="btn text-white d-flex align-items-center justify-content-center" 
                    style="background-color: #059669; border-radius: 6px; height: 38px; width: 38px; padding: 0; flex-shrink: 0;" 
                    title="Terapkan Filter">
                <i class="fas fa-filter" style="font-size: 0.85rem;"></i>
            </button>
            
            @if(request()->anyFilled(['search','role', 'status', 'jenis', 'tanggal_awal', 'tanggal_akhir','semester']))
                <a href="{{ route('admin.rekap.izin') }}" 
                   class="btn btn-light border d-flex align-items-center justify-content-center" 
                   style="border-radius: 6px; height: 38px; width: 38px; padding: 0; background-color: #f8fafc; flex-shrink: 0;" 
                   title="Reset Filter">
                    <i class="fas fa-undo" style="font-size: 0.85rem; color: #64748b;"></i>
                </a>
            @endif
        </div>
    </div>

</div>
            </form>
        </div>
    </div>

    <div class="card saas-card">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table saas-table table-hover">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 60px;">No</th>
                            <th>Nama </th>
                            <th class="text-center">Jenis</th>
                            <th>Keterangan</th>
                            <th class="text-center">Tanggal Mulai</th>
                            <th class="text-center">Tanggal Selesai</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Waktu Pengajuan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengajuans as $index => $pengajuan)
                        <tr>
                            <td class="text-center font-weight-bold text-muted">
                                {{ $pengajuans->firstItem() + $index }}
                            </td>
                            <td>
                                <div class="font-weight-bold text-dark saas-table-text-primary">{{ $pengajuan->user->nama ?? '-' }}</div>
                            </td>
                            <td class="text-center">
                            @php
                                $jenisClass = 'type-izin';
                                $jenisText = 'Izin';
                                if ($pengajuan->jenis_pengajuan == 'sakit') {
                                    $jenisClass = 'type-sakit';
                                    $jenisText = 'Sakit';
                                } elseif ($pengajuan->jenis_pengajuan == 'cuti') {
                                    $jenisClass = 'type-cuti';
                                    $jenisText = 'Cuti';
                                }
                            @endphp <span class="badge-type {{ $jenisClass }}">
                                {{ $jenisText }}
                            </span>
                            </td>
                            <td>
                                <span class="text-muted font-weight-medium" style="font-size: 0.825rem;">
                                    {{ $pengajuan->alasan ?? '-' }}
                                </span>
                                @if($pengajuan->bukti_file)
                                    <div class="mt-1">
                                        <a href="{{ asset('uploads/pengajuan/'.$pengajuan->bukti_file) }}" target="_blank" class="small text-primary font-weight-bold">
                                            <i class="fas fa-paperclip mr-1"></i> Lihat Bukti File
                                        </a>
                                    </div>
                                @endif
                            </td>
                            <td class="text-center font-weight-semibold">
                                {{ date('d-m-Y', strtotime($pengajuan->tanggal_mulai)) }}
                            </td>
                            <td class="text-center font-weight-semibold">
                                {{ date('d-m-Y', strtotime($pengajuan->tanggal_selesai)) }}
                            </td>
                            <td class="text-center">
                                @if($pengajuan->status == 'disetujui')
                                    <span class="badge-status badge-approved">Disetujui</span>
                                @elseif($pengajuan->status == 'ditolak')
                                    <span class="badge-status badge-rejected">Ditolak</span>
                                @else
                                    <span class="badge-status badge-pending">Pending</span>
                                @endif
                            </td>
                            <td class="text-center text-muted font-weight-medium" style="font-size: 0.8rem;">
                                {{ $pengajuan->created_at ? $pengajuan->created_at->format('d-m-Y H:i') : '-' }}
                            </td>
                           <td class="text-center">
                                    <div class="dropdown d-inline-block">
                                        <button class="btn-action-trigger" data-toggle="dropdown" data-boundary="viewport">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right saas-dropdown-menu">
                                            <button class="dropdown-item" data-toggle="modal" data-target="#modalEditIzin{{ $pengajuan->id_pengajuan }}">
                                                <i class="fas fa-edit text-warning"></i> Edit Data
                                            </button>
                                           
                                            <div class="dropdown-divider"></div>
                                            <form action="{{ route('admin.rekap.izin.destroy', $pengajuan->id_pengajuan) }}" 
                                                method="POST" class="d-inline" 
                                                onsubmit="return confirm('Yakin ingin menghapus data perizinan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="dropdown-item text-danger" title="Hapus Data">
                                                    <i class="fas fa-trash-alt text-danger"></i> Hapus User
                                                </button>
                                                </form>
                                        </div>
                                    </div>
                                </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted mb-2">
                                    <i class="fas fa-folder-open fa-2x opacity-50"></i>
                                </div>
                                <h6 class="font-weight-bold text-muted">Data pengajuan tidak ditemukan</h6>
                                <p class="text-muted small mb-0">Silakan sesuaikan parameter pencarian Anda.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 d-flex justify-content-center">
              {{ $pengajuans->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL TAMBAH IZIN MANUAL                   -->
<!-- ========================================== -->
<div class="modal fade" id="modalTambahIzin" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content saas-modal">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">Tambah Perizinan Manual</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.rekap.izin.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="small font-weight-bold">Pilih Pegawai (Kepsek / Guru)</label>
                        <select name="id_user" class="form-control saas-select" required>
                            <option value="">-- Pilih Pegawai --</option>
                            @foreach($gurus as $p)
                                <option value="{{ $p->id_user }}">
                                    {{ $p->nama }} ({{ ucfirst(str_replace('_', ' ', $p->role)) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">Jenis Pengajuan</label>
                        <select name="jenis_pengajuan" class="form-control saas-select" required>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                            <option value="cuti">Cuti</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" class="form-control saas-input" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" class="form-control saas-input" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">Keterangan / Alasan</label>
                        <textarea name="alasan" class="form-control saas-input" rows="3" style="height:auto !important;" required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">Upload Bukti File (Opsional)</label>
                        <input type="file" name="bukti_file" class="form-control-file">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-add-emerald">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL EDIT IZIN (LOOPING FOR EACH DATA)     -->
<!-- ========================================== -->
@foreach($pengajuans as $pengajuan)
<div class="modal fade" id="modalEditIzin{{ $pengajuan->id_pengajuan }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content saas-modal">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">Edit Perizinan - {{ $pengajuan->user->nama ?? '' }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.rekap.izin.update', $pengajuan->id_pengajuan) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label class="small font-weight-bold">Jenis Pengajuan</label>
                        <select name="jenis_pengajuan" class="form-control saas-select" required>
                            <option value="izin" {{ $pengajuan->jenis_pengajuan == 'izin' ? 'selected' : '' }}>Izin</option>
                            <option value="sakit" {{ $pengajuan->jenis_pengajuan == 'sakit' ? 'selected' : '' }}>Sakit</option>
                            <option value="cuti" {{ $pengajuan->jenis_pengajuan == 'cuti' ? 'selected' : '' }}>Cuti</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" value="{{ $pengajuan->tanggal_mulai }}" class="form-control saas-input" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" value="{{ $pengajuan->tanggal_selesai }}" class="form-control saas-input" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">Status Verification</label>
                        <select name="status" class="form-control saas-select" required>
                            <option value="pending" {{ $pengajuan->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="disetujui" {{ $pengajuan->status == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                            <option value="ditolak" {{ $pengajuan->status == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">Keterangan / Alasan</label>
                        <textarea name="alasan" class="form-control saas-input" rows="3" style="height:auto !important;" required>{{ $pengajuan->alasan }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">Ganti Bukti File (Opsional)</label>
                        <input type="file" name="bukti_file" class="form-control-file">
                        @if($pengajuan->bukti_file)
                            <small class="text-muted d-block mt-1">File saat ini: {{ $pengajuan->bukti_file }}</small>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-add-emerald">Update Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection