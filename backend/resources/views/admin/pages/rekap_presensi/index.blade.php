@extends('admin.layouts.app')

@section('title', 'Rekapitulasi Presensi')

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
    }

    /* ==========================================================================
       2. REKAP PRESENSI BASE STYLES
       ========================================================================== */
    .saas-rekap {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .saas-card {
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
        border-radius: 16px;
        background-color: #ffffff;
        transition: all 0.3s ease;
    }

    .dark .saas-card {
        background-color: #111827 !important;
        border-color: #1f2937 !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    .saas-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.5px;
    }

    .dark .saas-title {
        color: #f8fafc;
    }

    .saas-subtitle {
        font-size: 0.875rem;
        color: #64748b;
        font-weight: 500;
    }

    .dark .saas-subtitle {
        color: #94a3b8;
    }

    /* Form Controls Custom */
    .saas-input, .saas-select {
        border-radius: 10px !important;
        border: 1px solid #cbd5e1 !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        color: #334155 !important;
        padding: 0.6rem 1rem !important;
        height: auto !important;
        transition: all 0.2s ease;
    }

    .dark .saas-input, .dark .saas-select {
        background-color: #1f2937 !important;
        border-color: #374151 !important;
        color: #e2e8f0 !important;
    }

    /* Buttons Style */
    .btn-saas-primary {
        background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
        color: #ffffff !important;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        padding: 0.6rem 1.2rem;
        font-size: 0.875rem;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
        transition: all 0.2s ease;
    }

    .btn-saas-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.25);
    }

    .btn-saas-secondary {
        background-color: #f1f5f9;
        color: #334155 !important;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        font-weight: 600;
        padding: 0.6rem 1.2rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }

    .dark .btn-saas-secondary {
        background-color: #1f2937;
        color: #e2e8f0 !important;
        border-color: #374151;
    }

    .btn-saas-success {
        background: linear-gradient(135deg, #10b981 0%, #065f46 100%);
        color: #ffffff !important;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        padding: 0.6rem 1.2rem;
        font-size: 0.875rem;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
        transition: all 0.2s ease;
    }


    .badge-hadir { background-color: rgba(16, 185, 129, 0.08) !important; color: #10b981 !important; }
    .badge-izin { background-color: rgba(245, 158, 11, 0.08) !important; color: #f59e0b !important; }
    .badge-sakit { background-color: rgba(236, 72, 153, 0.08) !important; color: #ec4899 !important; }
    .badge-wfh { background-color: rgba(14, 165, 233, 0.08) !important; color: #0ea5e9 !important; }
    .badge-dinas { background-color: rgba(139, 92, 246, 0.08) !important; color: #8b5cf6 !important; }
    .badge-cuti { background-color: rgba(100, 116, 139, 0.08) !important; color: #64748b !important; }
    .badge-alpha { background-color: rgba(239, 68, 68, 0.08) !important; color: #ef4444 !important; }

    .dark .badge-hadir { background-color: rgba(16, 185, 129, 0.15) !important; color: #34d399 !important; }
    .dark .badge-izin { background-color: rgba(245, 158, 11, 0.15) !important; color: #fbbf24 !important; }
    .dark .badge-sakit { background-color: rgba(236, 72, 153, 0.15) !important; color: #f472b6 !important; }
    .dark .badge-wfh { background-color: rgba(14, 165, 233, 0.15) !important; color: #38bdf8 !important; }
    .dark .badge-dinas { background-color: rgba(139, 92, 246, 0.15) !important; color: #a78bfa !important; }
    .dark .badge-cuti { background-color: rgba(100, 116, 139, 0.15) !important; color: #94a3b8 !important; }
    .dark .badge-alpha { background-color: rgba(239, 68, 68, 0.15) !important; color: #f87171 !important; }
</style>

<div class="container-fluid saas-rekap py-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="saas-title m-0">Rekapitulasi Presensi</h1>
            <p class="saas-subtitle m-0 mt-1">Pantau kehadiran menyeluruh guru dan tenaga kependidikan SMAN 3 Padang</p>
        </div>
        <div>
           
              {{-- Export PDF --}}
            <a href="{{ route('admin.rekap.presensi.pdf', request()->query()) }}"
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
            <a href="{{ route('rekap-presensi.export-excel', request()->query()) }}"
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
            <form action="{{ route('admin.rekap.presensi') }}" method="GET" id="filterForm">
<div class="row g-1 align-items-end" style="margin-right: -4px; margin-left: -4px;">
    <div class="col-12 col-md-2" style="flex: 0 0 14%; max-width: 14%; padding-right: 4px; padding-left: 4px;">
        <label class="form-label small font-weight-normal text-muted mb-1" style="font-size: 0.75rem;">Nama Pegawai</label>
        <input type="text" name="search" value="{{ request('search') }}" class="form-control saas-input" placeholder="Cari.." style="font-size: 0.85rem; height: 38px;">
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
        <label class="form-label small font-weight-normal text-muted mb-1" style="font-size: 0.75rem;">Status Kehadiran</label>
        <select name="status" class="form-control saas-select" style="font-size: 0.85rem; height: 38px;">
            <option value="">Semua </option>
            <option value="hadir" {{ request('status') == 'hadir' ? 'selected' : '' }}>Hadir</option>
            <option value="wfh" {{ request('status') == 'wfh' ? 'selected' : '' }}>WFH</option>
            <option value="dinas_luar" {{ request('status') == 'dinas_luar' ? 'selected' : '' }}>Dinas Luar</option>
            <option value="izin" {{ request('status') == 'izin' ? 'selected' : '' }}>Izin</option>
            <option value="sakit" {{ request('status') == 'sakit' ? 'selected' : '' }}>Sakit</option>
            <option value="cuti" {{ request('status') == 'cuti' ? 'selected' : '' }}>Cuti</option>
            <option value="alpha" {{ request('status') == 'alpha' ? 'selected' : '' }}>Alpha</option>
        </select>
    </div>

    <div class="col-12 col-md-2" style="flex: 0 0 13%; max-width: 13%; padding-right: 4px; padding-left: 4px;">
        <label class="form-label small font-weight-normal text-muted mb-1" style="font-size: 0.75rem;">Keterlambatan</label>
        <select name="keterlambatan" class="form-control saas-select" style="font-size: 0.85rem; height: 38px;">
            <option value="">Semua</option>
            <option value="tepat_waktu" {{ request('keterlambatan') == 'tepat_waktu' ? 'selected' : '' }}>Tepat Waktu</option>
            <option value="terlambat" {{ request('keterlambatan') == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
        </select>
    </div>
    
    <div class="col-12 col-md-2" style="flex: 0 0 12%; max-width: 12%; padding-right: 4px; padding-left: 4px;">
        <label class="form-label small font-weight-normal text-muted mb-1" style="font-size: 0.75rem;">Tanggal Mulai</label>
        <input type="date" 
               name="tanggal_awal" 
               value="{{ request('tanggal_awal') }}" 
               class="form-control saas-input" 
               style="font-size: 0.85rem; height: 38px; border-radius: 6px;">
    </div>
    
    <div class="col-12 col-md-2" style="flex: 0 0 12%; max-width: 12%; padding-right: 4px; padding-left: 4px;">
        <label class="form-label small font-weight-normal text-muted mb-1" style="font-size: 0.75rem;">Tanggal Selesai</label>
        <input type="date" 
               name="tanggal_akhir" 
               value="{{ request('tanggal_akhir') }}" 
               class="form-control saas-input" 
               style="font-size: 0.85rem; height: 38px; border-radius: 6px;">
    </div>
               
    <div class="col-12 col-md-2" style="flex: 0 0 14%; max-width: 14%; padding-right: 4px; padding-left: 4px;">
        <label class="form-label small font-weight-normal text-muted mb-1" style="font-size: 0.75rem;">Periode Semester</label>
        <select name="semester" class="form-control saas-select" style="font-size: 0.85rem; height: 38px;">
            <option value="">Semua</option>
            <option value="2024-genap" {{ request('semester') == '2024-genap' ? 'selected' : '' }}>Genap 2024/2025</option>
            <option value="2025-ganjil" {{ request('semester') == '2025-ganjil' ? 'selected' : '' }}>Ganjil 2025/2026</option>
            <option value="2025-genap" {{ request('semester') == '2025-genap' ? 'selected' : '' }}>Genap 2025/2026</option>
            <option value="2026-ganjil" {{ request('semester') == '2026-ganjil' ? 'selected' : '' }}>Ganjil 2026/2027</option>
            <option value="2026-genap" {{ request('semester') == '2026-genap' ? 'selected' : '' }}>Genap 2026/2027</option>
        </select>
    </div>

    <div class="col-12 col-md-1" style="flex: 0 0 9%; max-width: 9%; padding-right: 4px; padding-left: 4px;">
        <div class="d-flex gap-1 align-items-center">
            <button type="submit" class="btn text-white d-flex align-items-center justify-content-center" 
                    style="background-color: #059669; border-radius: 6px; height: 38px; width: 38px; padding: 0;" 
                    title="Terapkan Filter">
                <i class="fas fa-filter" style="font-size: 0.85rem;"></i>
            </button>
            
            @if(request()->anyFilled(['search', 'role', 'status', 'keterlambatan', 'tanggal_awal', 'tanggal_akhir', 'semester']))
                <a href="{{ route('admin.rekap.presensi') }}" 
                   class="btn btn-light border d-flex align-items-center justify-content-center" 
                   style="border-radius: 6px; height: 38px; width: 38px; padding: 0; background-color: #f8fafc;" 
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
        <div class="card-body p-0">
            <div class="table-responsive">
<table class="table saas-table m-0">
    <thead>
        <tr>
            <th class="text-center" style="width: 50px; text-align: center;">No</th>
            <th style="text-align: left;">Nama Guru</th>
            <th style="text-align: left; width: 140px;">NIP</th>
            <th class="text-center" style="width: 100px; text-align: center;">Tanggal</th>
            <th class="text-center" style="width: 80px; text-align: center;">Masuk</th>
            <th class="text-center" style="width: 80px; text-align: center;">Keluar</th>
            <th class="text-center" style="width: 110px; text-align: center;">Status</th>
            <th class="text-center" style="width: 110px; text-align: center;">Kategori</th>
            <th style="text-align: left;">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($presensis as $index => $presensi)
        <tr>
            <td class="text-center text-muted" style="text-align: center; vertical-align: middle;">
                {{ $presensis->firstItem() + $index }}
            </td>

            <td style="text-align: left; vertical-align: middle;">
                <div class="text-dark dark:text-white font-weight-medium">
                    {{ $presensi->user->nama ?? 'User Terhapus' }}
                </div>
            </td>

            <td style="text-align: left; vertical-align: middle;">
                <span class="text-muted">
                    {{ $presensi->user->nip ?? '-' }}
                </span>
            </td>

            <td class="text-center" style="text-align: center; vertical-align: middle;">
                {{ \Carbon\Carbon::parse($presensi->tanggal)->format('d/m/Y') }}
            </td>

            <td class="text-center text-success font-weight-bold" style="text-align: center; vertical-align: middle;">
                {{ $presensi->jam_masuk ?? '--:--' }}
            </td>

            <td class="text-center text-danger font-weight-bold" style="text-align: center; vertical-align: middle;">
                {{ $presensi->jam_keluar ?? '--:--' }}
            </td>

            <td class="text-center" style="text-align: center; vertical-align: middle;">
                @php
                    $statusClass = 'badge-hadir';
                    $statusText = ucfirst(str_replace('_', ' ', $presensi->status));

                    if($presensi->status == 'izin') {
                        $statusClass = 'badge-izin';
                    } elseif($presensi->status == 'sakit') {
                        $statusClass = 'badge-sakit';
                    } elseif($presensi->status == 'wfh') {
                        $statusClass = 'badge-wfh';
                        $statusText = 'WFH';
                    } elseif($presensi->status == 'dinas_luar') {
                        $statusClass = 'badge-dinas';
                        $statusText = 'Dinas Luar';
                    } elseif($presensi->status == 'cuti') {
                        $statusClass = 'badge-cuti';
                    } elseif($presensi->status == 'alpha') {
                        $statusClass = 'badge-alpha';
                        $statusText = 'Alpha';
                    }
                @endphp
                <span class="badge-status {{ $statusClass }}">
                    {{ $statusText }}
                </span>
            </td>

            <td class="text-center" style="text-align: center; vertical-align: middle;">
                @if(($presensi->status_keterlambatan ?? '') == 'tepat_waktu')
                    <span class="badge badge-success px-2 py-1" style="font-size: 0.7rem; border-radius: 4px; font-weight: 500;">
                        Tepat Waktu
                    </span>
                @elseif(($presensi->status_keterlambatan ?? '') == 'terlambat')
                    <span class="badge badge-warning px-2 py-1" style="font-size: 0.7rem; border-radius: 4px; font-weight: 500;">
                        Terlambat
                    </span>
                @else
                    <span class="text-muted">-</span>
                @endif
            </td>

            <td style="text-align: left; vertical-align: middle;">
                <div class="text-muted" style="font-size: 0.75rem; line-height: 1.4;">
                    {{ $presensi->keterangan ?? '-' }}
                </div>

                {{-- File Pengajuan --}}
                @if($presensi->pengajuan && $presensi->pengajuan->bukti_file)
                    <div class="mt-1" style="font-size: 0.7rem;">
                        <a href="{{ asset('uploads/pengajuan/'.$presensi->pengajuan->bukti_file) }}"
                           target="_blank"
                           class="text-primary font-weight-medium">
                            <i class="fas fa-paperclip mr-1"></i>
                            @if(in_array($presensi->status, ['dinas_luar']))
                                Surat Tugas
                            @else
                                Dokumen Pendukung
                            @endif
                        </a>
                    </div>
                @endif

                {{-- Dokumentasi Kegiatan --}}
                @if(($presensi->status == 'dinas_luar' || $presensi->status == 'wfh') && $presensi->pengajuan && $presensi->pengajuan->foto_dokumentasi)
                    <div class="mt-1" style="font-size: 0.7rem;">
                        <a href="{{ asset('uploads/dokumentasi/'.$presensi->pengajuan->foto_dokumentasi) }}"
                           target="_blank"
                           class="text-success font-weight-medium">
                            <i class="fas fa-camera mr-1"></i> Dokumentasi
                        </a>
                    </div>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="text-center py-5" style="text-align: center;">
                <div class="text-muted mb-2">
                    <i class="fas fa-folder-open fa-2x opacity-50"></i>
                </div>
                <h6 class="font-weight-bold text-muted">Data tidak ditemukan</h6>
                <p class="text-muted small mb-0">Silakan sesuaikan parameter pencarian Anda.</p>
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
            </div>

            <div class="mt-4 d-flex justify-content-center">
                {{ $presensis->appends(request()->query())->links() }}
            </div>

        </div>
    </div>

</div>

@endsection