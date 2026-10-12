@extends('admin.layouts.app')

@section('title', 'Data Lokasi')

@section('content')

<!-- Memasukkan Google Font "Plus Jakarta Sans" & Icon Tambahan jika belum ada -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>

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
       2. DATA LOKASI BASE STYLES
       ========================================================================== */
    .saas-lokasi {
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
        letter-spacing: -0.015em;
        background-color: #f8fafc;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .dark .saas-lokasi {
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


    /* Badges */
    .badge-custom {
        padding: 0.35rem 0.65rem !important;
        border-radius: 8px !important;
        font-size: 0.725rem !important;
        font-weight: 700 !important;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .badge-active { background-color: rgba(16, 185, 129, 0.08) !important; color: #10b981 !important; }
    .badge-inactive { background-color: rgba(244, 63, 94, 0.08) !important; color: #f43f5e !important; }

    .dark .badge-active { background-color: rgba(16, 185, 129, 0.15) !important; color: #34d399 !important; }
    .dark .badge-inactive { background-color: rgba(244, 63, 94, 0.15) !important; color: #fb7185 !important; }

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

    /* Leaflet Dark Mode Adjustment */
    .dark #map {
        filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
    }

    /* Global Text Dark & Muted Overrides */
    .dark .text-dark, .dark h1, .dark h2, .dark h3, .dark h4, .dark h5, .dark h6 {
        color: #ffffff !important;
    }
    .dark .text-muted {
        color: #94a3b8 !important;
    }
</style>

<div class="container-fluid py-4 saas-lokasi">

    <!-- Judul Halaman -->
    <div class="mb-4">
        <h1 class="h3 font-weight-bold text-dark mb-1" style="font-size: 1.65rem; letter-spacing: -0.025em;">
            Data Lokasi Presensi
        </h1>
        <p class="text-muted mb-0" style="font-size: 0.9rem;">
            Konfigurasi koordinat geofencing sekolah, batasan radius presensi, serta status keaktifan lokasi absensi.
        </p>
    </div>

    {{-- Kustomisasi Alert Sukses --}}
    @if(session('success'))
        <div class="alert saas-alert saas-alert-success mb-4 shadow-sm">
            <i class="fas fa-check-circle text-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Header Action Filter Card -->
    <div class="card saas-card mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center">

                <!-- Search -->
                <div class="col-lg-5 col-md-6 mb-3 mb-lg-0">
                    <form action="{{ route('admin.lokasi') }}" method="GET">
                        <div class="input-group">
                            <input
                                type="text"
                                class="form-control saas-input"
                                name="search"
                                placeholder="Cari nama lokasi..."
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

                <!-- Button Tambah Lokasi -->
                <div class="col-lg-7 col-md-6 text-right">
                    <button
                        class="btn btn-emerald"
                        data-toggle="modal"
                        data-target="#tambahModal"
                    >
                        <i class="fas fa-plus"></i>
                        <span>Tambah Lokasi</span>
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
                            <th>Nama Lokasi</th>
                            <th class="text-center">Latitude</th>
                            <th class="text-center">Longitude</th>
                            <th class="text-center">Radius</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lokasis as $index => $lokasi)
                        <tr>
                            <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                            <td>
                                <div class="font-weight-bold text-dark">{{ $lokasi->nama_lokasi }}</div>
                            </td>
                            <td class="text-center font-weight-semibold text-muted">{{ $lokasi->latitude }}</td>
                            <td class="text-center font-weight-semibold text-muted">{{ $lokasi->longitude }}</td>
                            <td class="text-center">
                                <span class="badge-custom badge-role" style="color:#0ea5e9; background:rgba(14,165,233,0.08);">
                                    <i class="fas fa-street-view"></i> {{ $lokasi->radius }} meter
                                </span>
                            </td>
                            <td class="text-center">
                                @if($lokasi->status == 'aktif')
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
                                    data-target="#editModal{{ $lokasi->id_lokasi }}"
                                    title="Edit Lokasi"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>

                                <!-- Tombol Hapus -->
                                <button
                                    class="btn-action-icon btn-delete-icon"
                                    data-toggle="modal"
                                    data-target="#hapusModal{{ $lokasi->id_lokasi }}"
                                    title="Hapus Lokasi"
                                >
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>

                       {{-- MODAL EDIT LOKASI DENGAN PETA LEAFLET --}}
                        <div class="modal fade saas-modal" id="editModal{{ $lokasi->id_lokasi }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form action="{{ route('admin.lokasi.update', $lokasi->id_lokasi) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Lokasi Presensi</h5>
                                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>Nama Lokasi Sekolah</label>
                                                <input type="text" name="nama_lokasi" class="form-control saas-input" value="{{ $lokasi->nama_lokasi }}" required>
                                            </div>
                        
                                            <div class="d-flex justify-content-between align-items-center mb-2 mt-3">
                                                <small class="text-muted font-weight-semibold">Klik peta atau gunakan pencarian untuk mengubah titik lokasi</small>
                                                <button type="button" class="btn btn-sm btn-emerald btn-lokasi-saya-edit" data-id="{{ $lokasi->id_lokasi }}" style="height:34px !important; border-radius:8px !important; font-size:0.75rem !important; box-shadow:none !important;">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <span>Gunakan Lokasi Saya</span>
                                                </button>
                                            </div>
                        
                                            <div class="mb-3">
                                                <div class="input-group">
                                                    <input type="text" id="searchLocationEdit{{ $lokasi->id_lokasi }}" class="form-control saas-input" placeholder="Cari lokasi / jalan..." style="border-top-right-radius: 0px !important; border-bottom-right-radius: 0px !important;">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-emerald btn-search-edit" data-id="{{ $lokasi->id_lokasi }}" style="border-top-left-radius: 0px !important; border-bottom-left-radius: 0px !important; height:44px !important; box-shadow:none !important;">
                                                            <i class="fas fa-search"></i> Cari
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                        
                                            <!-- CONTAINER PETA UNTUK MODAL EDIT -->
                                            <div id="mapEdit{{ $lokasi->id_lokasi }}" style="height: 300px; border-radius:16px; border:1px solid rgba(0,0,0,0.06); margin-bottom:1.5rem;"></div>
                        
                                            <div class="row">
                                                <div class="col-md-6 form-group">
                                                    <label>Latitude</label>
                                                    <input type="text" id="latitudeEdit{{ $lokasi->id_lokasi }}" name="latitude" class="form-control saas-input" value="{{ $lokasi->latitude }}" readonly required>
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label>Longitude</label>
                                                    <input type="text" id="longitudeEdit{{ $lokasi->id_lokasi }}" name="longitude" class="form-control saas-input" value="{{ $lokasi->longitude }}" readonly required>
                                                </div>
                                            </div>
                        
                                            <div class="row mt-2">
                                                <div class="col-md-6 form-group">
                                                    <label>Radius (meter)</label>
                                                    <input type="number" name="radius" class="form-control saas-input" value="{{ $lokasi->radius }}" required>
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label>Status Keaktifan</label>
                                                    <select name="status" class="form-control saas-select">
                                                        <option value="aktif" {{ $lokasi->status == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                                        <option value="nonaktif" {{ $lokasi->status == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="border-radius:12px; font-weight:700; height:44px; font-size:0.85rem;">Batal</button>
                                            <button type="submit" class="btn btn-emerald">
                                                <i class="fas fa-save"></i>
                                                <span>Simpan Perubahan</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- MODAL HAPUS LOKASI --}}
                        <div class="modal fade saas-modal" id="hapusModal{{ $lokasi->id_lokasi }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.lokasi.delete', $lokasi->id_lokasi) }}" method="POST">
                                        @csrf
                                        <div class="modal-body text-center py-5">
                                            <div class="text-danger mb-4">
                                                <i class="fas fa-exclamation-triangle fa-3x opacity-80"></i>
                                            </div>
                                            <h5 class="font-weight-extrabold text-dark mb-2">Hapus Lokasi?</h5>
                                            <p class="text-muted small mb-4">
                                                Apakah Anda yakin ingin menghapus lokasi <strong>{{ $lokasi->nama_lokasi }}</strong>?<br>Tindakan ini tidak dapat dibatalkan.
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
                                    <i class="fas fa-map-marked-alt fa-2x opacity-50"></i>
                                </div>
                                <h6 class="font-weight-bold text-muted">Data lokasi kosong</h6>
                                <p class="text-muted small mb-0">Silakan daftarkan area geofencing sekolah baru.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- MODAL TAMBAH LOKASI (DENGAN INTEGRASI LEAFLET MAP) --}}
<div class="modal fade saas-modal" id="tambahModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.lokasi.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Lokasi Presensi</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Lokasi Sekolah</label>
                        <input type="text" name="nama_lokasi" class="form-control saas-input" placeholder="Contoh: SMA Negeri 1 Utama" required>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2 mt-3">
                        <small class="text-muted font-weight-semibold">Klik peta atau gunakan pencarian untuk mengambil koordinat</small>
                        <button type="button" class="btn btn-sm btn-emerald" id="btnLokasiSaya" style="height:34px !important; border-radius:8px !important; font-size:0.75rem !important; box-shadow:none !important;">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Gunakan Lokasi Saya</span>
                        </button>
                    </div>

                    <div class="mb-3">
                        <div class="input-group">
                            <input type="text" id="searchLocation" class="form-control saas-input" placeholder="Cari nama jalan / kelurahan / area sekolah..." style="border-top-right-radius: 0px !important; border-bottom-right-radius: 0px !important;">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-emerald" id="btnSearch" style="border-top-left-radius: 0px !important; border-bottom-left-radius: 0px !important; height:44px !important; box-shadow:none !important;">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- LEAFLET MAP WRAPPER -->
                    <div id="map" style="height: 320px; border-radius:16px; border:1px solid rgba(0,0,0,0.06); margin-bottom:1.5rem;"></div>

                    <div class="row mt-3">
                        <div class="col-md-6 form-group">
                            <label>Latitude</label>
                            <input type="text" id="latitude" name="latitude" class="form-control saas-input" readonly required placeholder="Otomatis terisi dari peta">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Longitude</label>
                            <input type="text" id="longitude" name="longitude" class="form-control saas-input" readonly required placeholder="Otomatis terisi dari peta">
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-6 form-group">
                            <label>Radius Batasan Presensi (meter)</label>
                            <input type="number" name="radius" class="form-control saas-input" placeholder="Contoh: 100" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Status Aktif</label>
                            <select name="status" class="form-control saas-select">
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="border-radius:12px; font-weight:700; height:44px; font-size:0.85rem;">Batal</button>
                    <button type="submit" class="btn btn-emerald">
                        <i class="fas fa-save"></i>
                        <span>Simpan Lokasi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- LEAFLET MAPS JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    
    // ==========================================
    // 1. INISIALISASI PETA UNTUK MODAL TAMBAH
    // ==========================================
    var mapTambah = L.map('map').setView([-0.947083, 100.417181], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(mapTambah);

    var markerTambah;

    function setMarkerTambah(lat, lng) {
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
        if (markerTambah) mapTambah.removeLayer(markerTambah);
        markerTambah = L.marker([lat, lng]).addTo(mapTambah);
        mapTambah.setView([lat, lng], 18);
    }

    $('#tambahModal').on('shown.bs.modal', function () {
        setTimeout(function () { mapTambah.invalidateSize(); }, 250);
    });

    mapTambah.on('click', function (e) {
        setMarkerTambah(e.latlng.lat, e.latlng.lng);
    });

    document.getElementById('btnLokasiSaya')?.addEventListener('click', function () {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(pos) {
                setMarkerTambah(pos.coords.latitude, pos.coords.longitude);
            });
        }
    });

    document.getElementById('btnSearch')?.addEventListener('click', function () {
        let keyword = document.getElementById('searchLocation').value;
        if (!keyword) return;
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${keyword}`)
            .then(res => res.json())
            .then(data => {
                if (data.length > 0) {
                    setMarkerTambah(parseFloat(data[0].lat), parseFloat(data[0].lon));
                }
            });
    });


    // ==========================================
    // 2. INISIALISASI PETA UNTUK MODAL EDIT (MULTI-MAP)
    // ==========================================
    var editMaps = {};
    var editMarkers = {};

    $('[id^="editModal"]').on('shown.bs.modal', function () {
        var modalId = $(this).attr('id');
        var idLokasi = modalId.replace('editModal', '');

        var latInput = document.getElementById('latitudeEdit' + idLokasi);
        var lngInput = document.getElementById('longitudeEdit' + idLokasi);

        var currentLat = parseFloat(latInput.value) || -0.947083;
        var currentLng = parseFloat(lngInput.value) || 100.417181;

        // Inisialisasi map jika belum dibuat
        if (!editMaps[idLokasi]) {
            var mapContainer = document.getElementById('mapEdit' + idLokasi);

            editMaps[idLokasi] = L.map(mapContainer).setView([currentLat, currentLng], 17);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(editMaps[idLokasi]);

            // Buat marker awal berdasarkan data database
            editMarkers[idLokasi] = L.marker([currentLat, currentLng]).addTo(editMaps[idLokasi]);

            // Handler klik peta pada modal edit
            editMaps[idLokasi].on('click', function (e) {
                var lat = e.latlng.lat;
                var lng = e.latlng.lng;

                latInput.value = lat;
                lngInput.value = lng;

                if (editMarkers[idLokasi]) {
                    editMaps[idLokasi].removeLayer(editMarkers[idLokasi]);
                }
                editMarkers[idLokasi] = L.marker([lat, lng]).addTo(editMaps[idLokasi]);
                editMaps[idLokasi].setView([lat, lng], 18);
            });
        }

        // Perbarui tampilan ukuran peta agar tidak patah/abu-abu
        setTimeout(function () {
            editMaps[idLokasi].invalidateSize();
            editMaps[idLokasi].setView([currentLat, currentLng], 17);
        }, 250);
    });

    // Handler Tombol "Gunakan Lokasi Saya" pada Modal Edit
    $(document).on('click', '.btn-lokasi-saya-edit', function () {
        var idLokasi = $(this).data('id');
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (pos) {
                var lat = pos.coords.latitude;
                var lng = pos.coords.longitude;

                document.getElementById('latitudeEdit' + idLokasi).value = lat;
                document.getElementById('longitudeEdit' + idLokasi).value = lng;

                if (editMarkers[idLokasi]) {
                    editMaps[idLokasi].removeLayer(editMarkers[idLokasi]);
                }
                editMarkers[idLokasi] = L.marker([lat, lng]).addTo(editMaps[idLokasi]);
                editMaps[idLokasi].setView([lat, lng], 18);
            });
        }
    });

    // Handler Tombol Pencarian Lokasi pada Modal Edit
    $(document).on('click', '.btn-search-edit', function () {
        var idLokasi = $(this).data('id');
        var keyword = document.getElementById('searchLocationEdit' + idLokasi).value;

        if (!keyword) return;

        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${keyword}`)
            .then(res => res.json())
            .then(data => {
                if (data.length > 0) {
                    var lat = parseFloat(data[0].lat);
                    var lng = parseFloat(data[0].lon);

                    document.getElementById('latitudeEdit' + idLokasi).value = lat;
                    document.getElementById('longitudeEdit' + idLokasi).value = lng;

                    if (editMarkers[idLokasi]) {
                        editMaps[idLokasi].removeLayer(editMarkers[idLokasi]);
                    }
                    editMarkers[idLokasi] = L.marker([lat, lng]).addTo(editMaps[idLokasi]);
                    editMaps[idLokasi].setView([lat, lng], 18);
                }
            });
    });

});
</script>

@endsection