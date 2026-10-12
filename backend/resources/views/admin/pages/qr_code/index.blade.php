@extends('admin.layouts.app')

@section('title', 'Manajemen QR Code')

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* ==========================================================================
       1. FORCE OVERRIDE PARENT LAYOUT
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

    .saas-qr {
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
        letter-spacing: -0.015em;
        background-color: #f8fafc;
    }

    .dark .saas-qr {
        background-color: #0b111e !important;
    }

    .saas-card {
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.04) !important;
        border-radius: 20px !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.01) !important;
    }

    .dark .saas-card {
        background: #111c2a !important;
        border-color: rgba(255, 255, 255, 0.05) !important;
        box-shadow: none !important;
    }

    .saas-input {
        background-color: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        font-size: 0.85rem !important;
        font-weight: 500 !important;
        color: #1e293b !important;
        height: 44px !important;
        padding: 0.5rem 1rem !important;
    }

    .dark .saas-input {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #ffffff !important;
    }

    .btn-emerald {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
        border: none !important;
        color: #ffffff !important;
        height: 44px !important;
        border-radius: 12px !important;
        font-weight: 700 !important;
        font-size: 0.85rem !important;
        padding: 0 1.5rem !important;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2) !important;
        transition: all 0.25s ease !important;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-emerald:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(5, 150, 105, 0.3) !important;
    }

    .btn-secondary-custom {
        background-color: #f1f5f9 !important;
        color: #475569 !important;
        border: 1px solid #e2e8f0 !important;
        height: 44px !important;
        border-radius: 12px !important;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .dark .btn-secondary-custom {
        background-color: #1e293b !important;
        color: #cbd5e1 !important;
        border-color: #334155 !important;
    }

    .saas-alert {
        border: none !important;
        border-radius: 14px !important;
        padding: 1rem 1.25rem !important;
        font-size: 0.85rem !important;
        font-weight: 600;
    }
    .saas-alert-success { background-color: rgba(16, 185, 129, 0.08) !important; color: #10b981 !important; }

    .badge-custom {
        padding: 0.4rem 0.75rem !important;
        border-radius: 8px !important;
        font-size: 0.75rem !important;
        font-weight: 700 !important;
    }
    .badge-active { background-color: rgba(16, 185, 129, 0.08) !important; color: #10b981 !important; }
    .dark .badge-active { background-color: rgba(16, 185, 129, 0.15) !important; color: #34d399 !important; }
    .badge-code { background-color: rgba(99, 102, 241, 0.08) !important; color: #6366f1 !important; }
    .dark .badge-code { background-color: rgba(99, 102, 241, 0.15) !important; color: #818cf8 !important; }

    .qr-display-container {
        background: #ffffff;
        padding: 24px;
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        display: inline-block;
    }

    /* Modal Styling */
    .saas-modal .modal-content {
        border: none !important;
        border-radius: 24px !important;
        background: #ffffff !important;
    }
    .dark .saas-modal .modal-content { background: #111c2a !important; border: 1px solid rgba(255, 255, 255, 0.05) !important; }
    .saas-modal .modal-header { border-bottom: 1px solid #f1f5f9 !important; padding: 1.5rem 1.75rem !important; }
    .dark .saas-modal .modal-header { border-bottom-color: rgba(255, 255, 255, 0.05) !important; }
    .saas-modal .modal-body { padding: 1.75rem !important; }

    .dark .text-dark, .dark h1, .dark h2, .dark h3, .dark h4, .dark h5, .dark h6 { color: #ffffff !important; }
    .dark .text-muted { color: #94a3b8 !important; }
</style>

<div class="container-fluid py-4 saas-qr">

    <div class="mb-4">
        <h1 class="h3 font-weight-bold text-dark mb-1" style="font-size: 1.65rem; letter-spacing: -0.025em;">
            Manajemen QR Code 
        </h1>
        <p class="text-muted mb-0" style="font-size: 0.9rem;">
            Lakukan regenerasi secara berkala demi keamanan agar data presensi tidak dapat dimanipulasi.
        </p>
    </div>

    {{-- Alert Sukses --}}
    @if(session('success'))
        <div class="alert saas-alert saas-alert-success mb-4 shadow-sm d-flex align-items-center gap-2">
            <i class="fas fa-check-circle text-lg mr-2"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card saas-card text-center p-4 p-md-5">
                <div class="card-body">
                    
                    <div class="mb-3">
                        <span class="badge-custom badge-active mb-2 d-inline-block">
                            <i class="fas fa-dot-circle shadow-sm"></i> Sesi QR Aktif
                        </span>
                        <h2 class="font-weight-extrabold text-dark mt-2 mb-1" style="font-size: 1.5rem;">
                            {{ $qr->nama_qr }}
                        </h2>
                        <p class="text-muted small">Token enkripsi saat ini: 
                            <span class="badge-custom badge-code ml-1">{{ $qr->kode_qr }}</span>
                        </p>
                    </div>

                    <div class="my-4">
                        <div class="qr-display-container">
                            {!! QrCode::size(240)->margin(1)->generate($qr->kode_qr) !!}
                        </div>
                    </div>

                    <p class="text-muted mx-auto mb-4 small" style="max-width: 450px;">
                        *Saat GTK memindai QR Code di atas, sistem mobile akan mencocokkan token ini beserta verifikasi koordinat dari <strong>Lokasi yang saat ini Anda set Aktif</strong>.
                    </p>

                    <div class="d-flex flex-wrap justify-content-center align-items-center" style="gap: 12px;">
                        
                        <a href="{{ route('admin.qrcode.print', $qr->id_qr) }}" target="_blank" class="btn btn-secondary-custom px-4">
                            <i class="fas fa-print text-success"></i> Cetak QR
                        </a>

                        <button class="btn btn-emerald px-4" data-toggle="modal" data-target="#regenerateModal">
                            <i class="fas fa-sync-alt animate-spin"></i> Regenerate QR
                        </button>

                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

{{-- MODAL REGENERATE & EDIT DATA TUNGGAL --}}
<div class="modal fade saas-modal" id="regenerateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.qrcode.update', $qr->id_qr) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold">Regenerate Token & Edit Informasi</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body text-left">
                    
                    <div class="alert alert-warning border-0 p-3 mb-3 small" style="border-radius:12px; background-color: rgba(245,158,11,0.08); color: #b45309; font-weight: 600;">
                        <i class="fas fa-exclamation-circle mr-1"></i> 
                        Tindakan ini akan langsung memperbarui string teks QR Code menjadi token baru secara acak. Kode QR yang lama/yang dicetak sebelumnya akan <strong>otomatis hangus</strong>.
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small text-muted text-uppercase" style="letter-spacing: 0.05em;">Nama Identitas QR Code</label>
                        <input type="text" name="nama_qr" class="form-control saas-input" value="{{ $qr->nama_qr }}" required placeholder="Contoh: QR Utama SMAN 3 Padang">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light px-4" data-dismiss="modal" style="border-radius:12px; height:44px; font-weight: 700;">Batal</button>
                    <button type="submit" class="btn btn-emerald">
                        <i class="fas fa-bolt"></i> Generate Token Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection