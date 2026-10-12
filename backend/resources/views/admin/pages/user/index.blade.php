@extends('admin.layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* ================================
       FORCE DARK LAYOUT
    ================================ */
    html.dark body,
    html.dark #wrapper,
    html.dark #content-wrapper,
    html.dark .content-wrapper,
    html.dark #content,
    html.dark .container-fluid {
        background: #0b111e !important;
        color: #fff !important;
    }

    /* ================================
       BASE
    ================================ */
    .saas-user {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        background: #f8fafc;
    }
    .dark .saas-user {
        background: #0b111e !important;
    }

    /* ================================
       CARD
    ================================ */
    .saas-card {
        background: #fff;
        border-radius: 20px !important;
        border: 1px solid rgba(0,0,0,.04) !important;
        box-shadow: 0 4px 20px rgba(0,0,0,.02) !important;
    }
    .dark .saas-card {
        background: #111c2a !important;
        border-color: rgba(255,255,255,.05) !important;
        box-shadow: none !important;
    }

    /* ================================
       INPUT
    ================================ */
    .saas-input,
    .saas-select {
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        height: 44px !important;
        font-size: .85rem !important;
        font-weight: 600 !important;
        color: #1e293b !important;
    }
    .saas-input:focus,
    .saas-select:focus {
        border-color: #059669 !important;
        box-shadow: 0 0 0 3px rgba(5,150,105,.12) !important;
    }
    .dark .saas-input,
    .dark .saas-select {
        background: #1e293b !important;
        border-color: #334155 !important;
        color: #fff !important;
    }

    /* ================================
       BUTTON
    ================================ */
    .btn-emerald {
        background: linear-gradient(135deg, #059669, #10b981) !important;
        border: none !important;
        color: #fff !important;
        height: 44px !important;
        padding: 0 20px !important;
        border-radius: 12px !important;
        font-size: .85rem !important;
        font-weight: 700 !important;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(5,150,105,.25);
    }
    .btn-emerald:hover {
        transform: translateY(-2px);
        color: #fff;
    }
    

 

    /* ================================
       BADGE
    ================================ */
    .badge-custom {
        padding: 7px 12px;
        border-radius: 10px;
        font-size: .75rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    /* ROLE */
    .badge-role {
        background: rgba(99,102,241,.08);
        color: #6366f1;
    }
    /* STATUS */
    .badge-active {
        background: rgba(16,185,129,.08);
        color: #10b981;
    }
    .badge-inactive {
        background: rgba(244,63,94,.08);
        color: #f43f5e;
    }
    /* DEVICE */
    .badge-device {
        background: rgba(14,165,233,.08);
        color: #0284c7;
    }
    .badge-nodevice {
        background: #f1f5f9;
        color: #64748b;
    }

    /* ================================
       ACTION BUTTON
    ================================ */
    .btn-action-trigger {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .btn-action-trigger:hover {
        background: #f8fafc;
    }
    .dark .btn-action-trigger {
        background: #111c2a;
        border-color: #334155;
        color: #94a3b8;
    }

    /* DROPDOWN */
    .saas-dropdown-menu {
        border: none !important;
        border-radius: 14px !important;
        padding: .4rem !important;
        min-width: 180px;
        box-shadow: 0 10px 25px rgba(0,0,0,.08) !important;
    }
    .saas-dropdown-menu .dropdown-item {
        border-radius: 10px !important;
        font-size: .83rem !important;
        font-weight: 600 !important;
        padding: 10px 14px !important;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .saas-dropdown-menu .dropdown-item:hover {
        background: #f8fafc !important;
    }
    /* DARK DROPDOWN */
    .dark .saas-dropdown-menu {
        background: #111c2a !important;
    }
    .dark .saas-dropdown-menu .dropdown-item {
        color: #cbd5e1 !important;
    }
    .dark .saas-dropdown-menu .dropdown-item:hover {
        background: #1e293b !important;
    }

    /* ALERT */
    .saas-alert {
        border: none !important;
        border-radius: 14px !important;
        font-size: .85rem;
        font-weight: 600;
        padding: 1rem 1.25rem;
    }
    .saas-alert-success {
        background: rgba(16,185,129,.08) !important;
        color: #10b981 !important;
    }
    .saas-alert-danger {
        background: rgba(244,63,94,.08) !important;
        color: #f43f5e !important;
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

</style>

<div class="container-fluid py-4 saas-user">

    <div class="mb-4">
        <h1 class="h3 font-weight-bold text-dark mb-1" style="font-size:1.65rem; letter-spacing:-.02em;">
            Manajemen Pengguna
        </h1>
        <p class="text-muted mb-0">
            Kelola akun user disini.
        </p>
    </div>

    {{-- ALERT --}}
    @if(session('success'))
        <div class="alert saas-alert saas-alert-success mb-4">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert saas-alert saas-alert-danger mb-4">
            <i class="fas fa-times-circle mr-2"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="card saas-card mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-4 mb-3 mb-lg-0">
                    <form method="GET">
                        <div class="input-group">
                            <input type="text" class="form-control saas-input" name="search" placeholder="Cari nama / username..." value="{{ request('search') }}" style="border-top-right-radius:0 !important; border-bottom-right-radius:0 !important;">
                            <div class="input-group-append">
                                <button class="btn btn-emerald" style="border-top-left-radius:0 !important; border-bottom-left-radius:0 !important; box-shadow:none !important;">
                                    <i class="fas fa-search"></i>
                                </button>
                                 @if(request()->anyFilled(['search']))
                                    <a href="{{ route('admin.user') }}" 
                                       class="btn btn-light border d-flex align-items-center justify-content-center" 
                                       style="border-radius: 6px; height: 38px; width: 38px; padding: 0; background-color: #f8fafc; flex-shrink: 0;" 
                                       title="Reset Filter">
                                        <i class="fas fa-undo" style="font-size: 0.85rem; color: #64748b;"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

                <div class="col-lg-3 mb-3 mb-lg-0">

    <form method="GET">

        <select
            class="form-control saas-select"
            name="role"
            onchange="this.form.submit()"
        >

            <option
                value=""
                {{ request('role') == '' ? 'selected' : '' }}
            >
                Semua Pengguna
            </option>

            <option
                value="guru"
                {{ request('role') == 'guru' ? 'selected' : '' }}
            >
                Guru
            </option>

            <option
                value="tenaga"
                {{ request('role') == 'tenaga' ? 'selected' : '' }}
            >
                Tenaga Kependidikan
            </option>

            <option
                value="tim_kurikulum"
                {{ request('role') == 'tim_kurikulum' ? 'selected' : '' }}
            >
                Tim Kurikulum
            </option>

            <option
                value="kepala_sekolah"
                {{ request('role') == 'kepala_sekolah' ? 'selected' : '' }}
            >
                Kepala Sekolah
            </option>

        </select>

    </form>

</div>


                
        <div class="d-flex align-items-center flex-wrap ml-auto" style="gap: 8px;">
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
                data-target="#tambahModal">
                <i class="fas fa-plus mr-2"></i>
                Tambah Data
            </button>
        
            {{-- Export PDF --}}
            <a href="{{ route('admin.user.download-pdf', request()->query()) }}"
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
            <a href="{{ route('user.export-excel', request()->query()) }}"
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
        </div>
    </div>

    <div class="card saas-card">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table saas-table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Role</th>
                            <th>NIP</th>
                            <th>Mata Pelajaran</th>
                            <th>No HP</th>
                            <th>Status</th>
                            <th>Perangkat</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                            <tr>
                                <td class="font-weight-bold text-muted">
                                    @if($users instanceof \Illuminate\Pagination\AbstractPaginator)
                                        {{ $users->firstItem() + $index }}
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </td>
                                <td>
                                    <div class="font-weight-bold text-dark">
                                        {{ $user->nama }}
                                    </div>
                                </td>

<td>

<span class="text-dark">

@if($user->role == 'guru')

{{

$user->jenis_gtk

??

'GTK'

}}

@else

{{

ucwords(

str_replace(

'_',

' ',

$user->role

)

)

}}

@endif

</span>

</td>
                                <td>{{ $user->nip ?? '-' }}</td>
                                <td>{{ $user->mata_pelajaran ?? '-' }}</td>
                                <td>{{ $user->no_hp ?? '-' }}</td>
                                <td>
                                    @if($user->status_akun == 'aktif')
                                        <span class="badge-custom badge-active">
                                            <i class="fas fa-check-circle"></i> Aktif
                                        </span>
                                    @else
                                        <span class="badge-custom badge-inactive">
                                            <i class="fas fa-times-circle"></i> Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->device_id)
                                        <span class="badge-custom badge-device">
                                            <i class="fas fa-mobile-alt"></i> Terdaftar
                                        </span>
                                    @else
                                        <span class="badge-custom badge-nodevice">
                                            <i class="fas fa-mobile-alt"></i> Belum
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="dropdown d-inline-block">
                                        <button class="btn-action-trigger" data-toggle="dropdown" data-boundary="viewport">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right saas-dropdown-menu">
                                            <button class="dropdown-item" data-toggle="modal" data-target="#editModal{{ $user->id_user }}">
                                                <i class="fas fa-edit text-warning"></i> Edit Data
                                            </button>
                                            @if($user->device_id)
                                                <button class="dropdown-item" data-toggle="modal" data-target="#resetDevice{{ $user->id_user }}">
                                                    <i class="fas fa-mobile-alt text-info"></i> Reset Device
                                                </button>
                                            @endif
                                            <div class="dropdown-divider"></div>
                                            <button class="dropdown-item text-danger" data-toggle="modal" data-target="#hapusModal{{ $user->id_user }}">
                                                <i class="fas fa-trash-alt text-danger"></i> Hapus User
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                           {{-- MODAL EDIT --}}
                            <div class="modal fade saas-modal" id="editModal{{ $user->id_user }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('admin.user.update', $user->id_user) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Pengguna</h5>
                                                <button type="button" class="close" data-dismiss="modal">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Nama</label>
                                                            <input type="text" name="nama" class="form-control saas-input" value="{{ $user->nama }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Username</label>
                                                            <input type="text" name="username" class="form-control saas-input" value="{{ $user->username }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Role / Kategori</label>
                                                            <select name="role" id="roleEdit{{ $user->id_user }}" class="form-control saas-select" required>
                                                                <option value="guru" {{ $user->role == 'guru' ? 'selected' : '' }}>GTK (Guru / Tendik)</option>
                                                                <option value="tim_kurikulum" {{ $user->role == 'tim_kurikulum' ? 'selected' : '' }}>Tim Kurikulum</option>
                                                                <option value="kepala_sekolah" {{ $user->role == 'kepala_sekolah' ? 'selected' : '' }}>Kepala Sekolah</option>
                                                            </select>
                                                        </div>
                                                    </div>
                            
                                                    {{-- Input Jenis GTK (Dukungan Guru / Tenaga Kependidikan) --}}
                                                    <div class="col-md-6" id="jenisGtkGroupEdit{{ $user->id_user }}" style="{{ $user->role == 'guru' ? '' : 'display: none;' }}">
                                                        <div class="form-group">
                                                            <label>Jenis GTK</label>
                                                            <select name="jenis_gtk" class="form-control saas-select">
                                                                <option value="Guru" {{ $user->jenis_gtk == 'Guru' ? 'selected' : '' }}>Guru</option>
                                                                <option value="Tenaga Kependidikan" {{ $user->jenis_gtk == 'Tenaga Kependidikan' ? 'selected' : '' }}>Tenaga Kependidikan</option>
                                                            </select>
                                                        </div>
                                                    </div>
                            
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>NIP</label>
                                                            <input type="text" name="nip" class="form-control saas-input" value="{{ $user->nip }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Mata Pelajaran</label>
                                                            <input type="text" name="mata_pelajaran" class="form-control saas-input" value="{{ $user->mata_pelajaran }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>No HP</label>
                                                            <input type="text" name="no_hp" class="form-control saas-input" value="{{ $user->no_hp }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Status Akun</label>
                                                            <select name="status_akun" class="form-control saas-select">
                                                                <option value="aktif" {{ $user->status_akun == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                                                <option value="nonaktif" {{ $user->status_akun == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Password Baru</label>
                                                            <input type="password" name="password" class="form-control saas-input" placeholder="Kosongkan jika tidak diubah">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" style="border-radius:12px" data-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-emerald">
                                                    <i class="fas fa-save"></i> Simpan Perubahan
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- MODAL HAPUS --}}
                            <div class="modal fade saas-modal" id="hapusModal{{ $user->id_user }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('admin.user.delete', $user->id_user) }}">
                                            @csrf
                                            <div class="modal-body text-center py-5">
                                                <div class="mb-4 text-danger">
                                                    <i class="fas fa-trash-alt fa-3x"></i>
                                                </div>
                                                <h5 class="font-weight-bold mb-3">Hapus Pengguna?</h5>
                                                <p class="text-muted">
                                                    Apakah Anda yakin ingin menghapus <strong>{{ $user->nama }}</strong>?<br>
                                                    Tindakan ini tidak dapat dibatalkan.
                                                </p>
                                            </div>
                                            <div class="modal-footer justify-content-center">
                                                <button type="button" class="btn btn-secondary" style="border-radius:12px" data-dismiss="modal">Batal</button>
                                                <button class="btn btn-danger" style="border-radius:12px; height:44px; padding:0 20px; font-weight:700;">
                                                    Hapus
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- MODAL RESET DEVICE --}}
                            <div class="modal fade saas-modal" id="resetDevice{{ $user->id_user }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('admin.user.resetDevice', $user->id_user) }}">
                                            @csrf
                                            <div class="modal-body text-center py-5">
                                                <div class="mb-4 text-info">
                                                    <i class="fas fa-mobile-alt fa-3x"></i>
                                                </div>
                                                <h5 class="font-weight-bold mb-3">Reset Device?</h5>
                                                <p class="text-muted">
                                                    Reset perangkat milik <br><strong>{{ $user->nama }}</strong>?<br><br>
                                                    Pengguna harus login ulang pada perangkat baru.
                                                </p>
                                            </div>
                                            <div class="modal-footer justify-content-center">
                                                <button type="button" class="btn btn-secondary" style="border-radius:12px" data-dismiss="modal">Batal</button>
                                                <button class="btn btn-info" style="border-radius:12px; height:44px; padding:0 20px; font-weight:700;">
                                                    Reset Device
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <div class="mb-3 text-muted">
                                        <i class="fas fa-users fa-3x opacity-50"></i>
                                    </div>
                                    <h6 class="font-weight-bold">Belum Ada Data Pengguna</h6>
                                    <p class="text-muted mb-0">Silakan tambahkan pengguna baru.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

<div class="mt-4 d-flex justify-content-center">
    {{ $users->withQueryString()->links() }}
</div>
        </div>
    </div>
</div>

{{-- ==========================
    MODAL TAMBAH USER (Di luar Loop @forelse)
========================== --}}
<div class="modal fade saas-modal" id="tambahModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.user.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengguna</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama</label>
                                <input type="text" name="nama" class="form-control saas-input" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control saas-input" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control saas-input" required>
                            </div>
                        </div>
                        <div class="col-md-6">

    <div class="form-group">

        <label>Role</label>

        <select

            name="role"

            id="roleTambah"

            class="form-control saas-select"

            required

        >

            <option value="">

                -- Pilih Role --

            </option>

            <option value="guru">

                GTK

            </option>

            <option value="tim_kurikulum">

                Tim Kurikulum

            </option>

            <option value="kepala_sekolah">

                Kepala Sekolah

            </option>

        </select>

    </div>

</div>


<div

class="col-md-6"

id="jenisGtkGroup"

>

    <div class="form-group">

        <label>

            Jenis GTK

        </label>

        <select

            name="jenis_gtk"

            class="form-control saas-select"

        >

            <option value="Guru">

                Guru

            </option>

            <option

            value=

            "Tenaga Kependidikan"

            >

                Tenaga Kependidikan

            </option>

        </select>

    </div>

</div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NIP</label>
                                <input type="text" name="nip" class="form-control saas-input">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Mata Pelajaran</label>
                                <input type="text" name="mata_pelajaran" class="form-control saas-input">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>No HP</label>
                                <input type="text" name="no_hp" class="form-control saas-input">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status Akun</label>
                                <select name="status_akun" class="form-control saas-select">
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" style="border-radius:12px" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-emerald">
                        <i class="fas fa-save"></i> Simpan User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Fungsi Toggle untuk Modal Edit
    document.querySelectorAll('[id^="roleEdit"]').forEach(function (select) {
        function toggleEditGtk() {
            const id = select.id.replace('roleEdit', '');
            const jenisGtkGroup = document.getElementById('jenisGtkGroupEdit' + id);
            
            if (jenisGtkGroup) {
                if (select.value === 'guru') {
                    jenisGtkGroup.style.display = 'block';
                } else {
                    jenisGtkGroup.style.display = 'none';
                }
            }
        }

        // Jalankan saat load & saat event change
        toggleEditGtk();
        select.addEventListener('change', toggleEditGtk);
    });
});
</script>

<script>

const roleTambah =

document.getElementById(

'roleTambah'

);

const jenisGtkGroup =

document.getElementById(

'jenisGtkGroup'

);

function toggleJenisGtk(){

if(

roleTambah.value

==

'guru'

){

jenisGtkGroup.style.display='block';

}

else{

jenisGtkGroup.style.display='none';

}

}

toggleJenisGtk();

roleTambah.addEventListener(

'change',

toggleJenisGtk

);

</script>
@endsection