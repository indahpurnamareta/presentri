@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')

<!-- Memasukkan Google Font "Plus Jakarta Sans" & Icon Tambahan -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* Scoped Container untuk menghindari tabrakan dengan CSS Admin bawaan */
    .saas-dashboard {
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
        letter-spacing: -0.015em;
        background-color: #f8fafc;
    }

    /* Modifikasi Card */
    .saas-card {
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.04) !important;
        border-radius: 20px !important;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.01) !important;
    }

    .saas-card:hover {
        transform: translateY(-5px);
        border-color: rgba(5, 150, 105, 0.15) !important;
        box-shadow: 0 20px 25px -5px rgba(5, 150, 105, 0.02), 0 10px 10px -5px rgba(0, 0, 0, 0.01) !important;
    }

    /* Efek Glow & Warna Lembut Berbasis Emerald */
    .glow-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .glow-emerald { background: rgba(5, 150, 105, 0.08); color: #059669; }
    .glow-teal { background: rgba(20, 184, 166, 0.08); color: #14b8a6; }
    .glow-amber { background: rgba(245, 158, 11, 0.08); color: #f59e0b; }
    .glow-rose { background: rgba(244, 63, 94, 0.08); color: #f43f5e; }

    /* Custom Progress Bar Presisi */
    .progress-micro {
        height: 6px !important;
        border-radius: 10px;
        background-color: #f1f5f9;
        overflow: hidden;
    }

    /* SVG Donut Chart */
    .chart-donut-svg {
        transform: rotate(-90deg);
    }
    .chart-donut-bg {
        fill: none;
        stroke: #f1f5f9;
        stroke-width: 3.8;
    }
    .chart-donut-fill {
        fill: none;
        stroke: url(#emeraldTealGrad);
        stroke-width: 3.8;
        stroke-dasharray: 100;
        stroke-linecap: round;
        transition: stroke-dashoffset 1s ease-in-out;
    }

    /* Log Item Hover Effect */
    .log-item {
        background: #f8fafc; 
        border-radius: 12px; 
        border: 1px solid #f1f5f9;
        transition: all 0.3s ease;
    }
    .log-item:hover {
        background: #ffffff;
        border-color: rgba(5, 150, 105, 0.15);
        transform: scale(1.01);
    }
</style>

@php
    // Menghitung persentase kehadiran untuk visualisasi chart
    $totalMasuk = $totalHadir + $totalTerlambat;
    $persenKehadiran = $totalGuru > 0 ? round(($totalMasuk / $totalGuru) * 100) : 0;
    
    // Perhitungan stroke-dashoffset untuk SVG Donut (100 - % kehadiran)
    $strokeOffset = 100 - $persenKehadiran;
@endphp

<div class="container-fluid py-4 saas-dashboard">

    <!-- Row Utama -->
    <div class="row">
        
        <!-- KOLOM KIRI (70%): Greeting, Utama Metrics, Status List -->
        <div class="col-xl-8 col-lg-12">
            
            <!-- Hero Greeting Banner (Deep Forest Emerald & Teal Gradient) -->
            <div class="card border-0 overflow-hidden mb-4 shadow-sm" style="background: linear-gradient(135deg, #022c22 0%, #064e3b 100%); border-radius: 24px; position: relative;">
                <!-- Hiasan Background Abstrak -->
                <div style="position: absolute; width: 300px; height: 300px; border-radius: 50%; background: radial-gradient(circle, rgba(20,184,166,0.15) 0%, transparent 70%); right: -50px; top: -50px; pointer-events: none;"></div>
                <div style="position: absolute; width: 200px; height: 200px; border-radius: 50%; background: radial-gradient(circle, rgba(52,211,153,0.1) 0%, transparent 70%); left: -50px; bottom: -50px; pointer-events: none;"></div>

                <div class="card-body p-4 p-md-5 text-white" style="position: relative; z-index: 2;">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4">
                        <span class="badge px-3 py-2 rounded-pill mb-3 mb-sm-0" style="background: rgba(255, 255, 255, 0.08); backdrop-filter: blur(8px); font-size: 0.8rem; font-weight: 600; letter-spacing: 0.05em; color: #a7f3d0;">
                            <i class="far fa-calendar-alt mr-2 text-emerald-400"></i> {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                        </span>
                        <div class="text-muted text-sm-right" style="font-size: 0.85rem; color: #a7f3d0 !important; opacity: 0.85;">
                            Presentri 1.0
                        </div>
                    </div>
                    
                    <h1 class="font-weight-bold mb-2" style="font-size: 2.1rem; letter-spacing: -0.03em;">
                        Halo, {{ Auth::user()->nama }}
                    </h1>
                    <p class="mb-0 text-light" style="max-width: 550px; opacity: 0.9; line-height: 1.6; font-size: 1rem; color: #d1fae5 !important;">
                        Silakan pantau detail kehadiran berkala di bawah ini.
                    </p>
                </div>
            </div>

            <!-- Header Section untuk Metrik Utama -->
            <div class="d-flex align-items-center justify-content-between mb-3 mt-4">
                <h5 class="font-weight-bold text-dark mb-0" style="font-size: 1.15rem; letter-spacing: -0.02em;">Aktivitas Kehadiran</h5>
                <span class="text-muted small">Real-time updates</span>
            </div>

            <!-- Grid Metrik Utama -->
            <div class="row">
                <!-- Total Guru -->
                <div class="col-sm-6 mb-4">
                    <div class="card saas-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="glow-icon glow-emerald">
                                    <i class="fas fa-users"></i>
                                </div>
                                <span class="badge badge-light text-muted px-2 py-1 small rounded-pill">Total</span>
                            </div>
                            <span class="text-muted small d-block font-weight-bold text-uppercase mb-1" style="letter-spacing: 0.05em;">GTK Terdaftar</span>
                            <h2 class="font-weight-bold text-dark mb-2" style="font-size: 1.8rem;">{{ $totalGuru }}</h2>
                            <div class="progress progress-micro">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 100%; background-color: #059669 !important;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hadir -->
                <div class="col-sm-6 mb-4">
                    <div class="card saas-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="glow-icon glow-teal">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                @php $pctHadir = $totalGuru > 0 ? round(($totalHadir / $totalGuru) * 100) : 0; @endphp
                                <span class="badge text-teal px-2 py-1 small rounded-pill font-weight-bold" style="background: rgba(20, 184, 166, 0.1); color: #0d9488;">
                                    {{ $pctHadir }}%
                                </span>
                            </div>
                            <span class="text-muted small d-block font-weight-bold text-uppercase mb-1" style="letter-spacing: 0.05em;">Hadir Tepat Waktu</span>
                            <h2 class="font-weight-bold text-dark mb-2" style="font-size: 1.8rem;">{{ $totalHadir }}</h2>
                            <div class="progress progress-micro">
                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $pctHadir }}%; background-color: #14b8a6 !important;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terlambat -->
                <div class="col-sm-6 mb-4">
                    <div class="card saas-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="glow-icon glow-amber">
                                    <i class="fas fa-clock"></i>
                                </div>
                                @php $pctTerlambat = $totalGuru > 0 ? round(($totalTerlambat / $totalGuru) * 100) : 0; @endphp
                                <span class="badge badge-warning-soft text-warning px-2 py-1 small rounded-pill font-weight-bold" style="background: rgba(245, 158, 11, 0.1)">
                                    {{ $pctTerlambat }}%
                                </span>
                            </div>
                            <span class="text-muted small d-block font-weight-bold text-uppercase mb-1" style="letter-spacing: 0.05em;">Hadir Terlambat</span>
                            <h2 class="font-weight-bold text-dark mb-2" style="font-size: 1.8rem;">{{ $totalTerlambat }}</h2>
                            <div class="progress progress-micro">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pctTerlambat }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tidak Hadir / Alpha -->
                <div class="col-sm-6 mb-4">
                    <div class="card saas-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="glow-icon glow-rose">
                                    <i class="fas fa-user-times"></i>
                                </div>
                                @php $pctTidakHadir = $totalGuru > 0 ? round(($totalTidakHadir / $totalGuru) * 100) : 0; @endphp
                                <span class="badge badge-danger-soft text-danger px-2 py-1 small rounded-pill font-weight-bold" style="background: rgba(244, 63, 94, 0.1)">
                                    {{ $pctTidakHadir }}%
                                </span>
                            </div>
                            <span class="text-muted small d-block font-weight-bold text-uppercase mb-1" style="letter-spacing: 0.05em;">Tanpa Keterangan (Alpha)</span>
                            <h2 class="font-weight-bold text-dark mb-2" style="font-size: 1.8rem;">{{ $totalTidakHadir }}</h2>
                            <div class="progress progress-micro">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $pctTidakHadir }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- KOLOM KANAN (30%): Ringkasan Visual, Izin, Sakit, Cuti -->
        <div class="col-xl-4 col-lg-12">
            
            <!-- Ringkasan Kehadiran Visual Card -->
            <div class="card saas-card mb-4">
                <div class="card-body p-4 text-center">
                    <h6 class="font-weight-bold text-dark text-left mb-4" style="font-size: 1rem; letter-spacing: -0.02em;">Rasio Kehadiran Hari Ini</h6>
                    
                    <!-- SVG Circular Progress dengan Gradient Emerald-Teal -->
                    <div class="position-relative d-inline-flex mb-4">
                        <svg class="chart-donut-svg" width="160" height="160" viewBox="0 0 36 36">
                            <path class="chart-donut-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="chart-donut-fill" stroke-dashoffset="{{ $strokeOffset }}" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            
                            <defs>
                                <linearGradient id="emeraldTealGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#059669" />
                                    <stop offset="100%" stop-color="#14b8a6" />
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="position-absolute d-flex flex-column align-items-center justify-content-center" style="top: 0; left: 0; right: 0; bottom: 0;">
                            <h2 class="font-weight-bold text-dark mb-0" style="font-size: 1.75rem;">{{ $persenKehadiran }}%</h2>
                            <span class="text-muted small" style="font-size: 0.75rem; font-weight: 500;">Guru Hadir</span>
                        </div>
                    </div>

<p class="text-muted small px-2 mb-0" style="line-height: 1.5;">
    Sebanyak <strong>{{ $totalMasuk }}</strong> masuk, 
    @php $totalAbsen = $totalIzin + $totalSakit + $totalCuti; @endphp
    @if($totalAbsen > 0)
        <strong>{{ $totalAbsen }}</strong> berhalangan (izin/sakit/cuti),
    @endif
    dari total <strong>{{ $totalGuru }}</strong> guru dan tenaga kependidikan terdaftar.
</p>
                </div>
            </div>

            <!-- List Keterangan Berhalangan (Izin, Sakit, Cuti) -->
            <div class="card saas-card">
                <div class="card-body p-4">
                    <h6 class="font-weight-bold text-dark mb-3" style="font-size: 1rem; letter-spacing: -0.02em;">Log Dispensasi & Ketidakhadiran</h6>
                    
                    <div class="d-flex flex-column gap-3">
                        
                        <!-- Izin Item -->
                        <div class="d-flex align-items-center justify-content-between p-2 mb-2 log-item">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: rgba(13, 202, 240, 0.1); font-size: 0.95rem;">
                                    <i class="fas fa-file-signature"></i>
                                </div>
                                <div>
                                    <span class="d-block font-weight-bold text-dark" style="font-size: 0.85rem;">Izin</span>
                                    <small class="text-muted" style="font-size: 0.75rem;">Pengajuan Aktif</small>
                                </div>
                            </div>
                            <span class="font-weight-bold text-dark px-3" style="font-size: 1.1rem;">{{ $totalIzin }}</span>
                        </div>

                        <!-- Sakit Item -->
                        <div class="d-flex align-items-center justify-content-between p-2 mb-2 log-item">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 text-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: rgba(108, 117, 125, 0.1); font-size: 0.95rem;">
                                    <i class="fas fa-notes-medical"></i>
                                </div>
                                <div>
                                    <span class="d-block font-weight-bold text-dark" style="font-size: 0.85rem;">Sakit</span>
                                    <small class="text-muted" style="font-size: 0.75rem;">Dengan Surat Dokter</small>
                                </div>
                            </div>
                            <span class="font-weight-bold text-dark px-3" style="font-size: 1.1rem;">{{ $totalSakit }}</span>
                        </div>

                        <!-- Cuti Item -->
                        <div class="d-flex align-items-center justify-content-between p-2 log-item">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: rgba(5, 150, 105, 0.1); color: #059669; font-size: 0.95rem;">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <span class="d-block font-weight-bold text-dark" style="font-size: 0.85rem;">Cuti</span>
                                    <small class="text-muted" style="font-size: 0.75rem;">Disetujui</small>
                                </div>
                            </div>
                            <span class="font-weight-bold text-dark px-3" style="font-size: 1.1rem;">{{ $totalCuti }}</span>
                        </div>

                     <!-- Dinas Luar Item -->
                        <div class="d-flex align-items-center justify-content-between p-2 log-item">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: rgba(5, 150, 105, 0.1); color: #8a8a8a; font-size: 0.95rem;">
                                    <i class="fas fa-plane-departure"></i>
                                </div>
                                <div>
                                    <span class="d-block font-weight-bold text-dark" style="font-size: 0.85rem;">Dinas Luar</span>
                                    <small class="text-muted" style="font-size: 0.75rem;">Dengan Surat Tugas</small>
                                </div>
                            </div>
                            <span class="font-weight-bold text-dark px-3" style="font-size: 1.1rem;">{{ $totalDinasLuar }}</span>
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </div>

</div>


@endsection