@extends('admin.layouts.app')

@section('title', 'Profile Admin')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4 mb-3">Profil Saya</h1>

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Profil</li>
        </ol>
    </nav>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="card shadow-sm">
        <div class="card-body">
            <form>
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">ID</label>
                    <div class="col-sm-9">
                        <input type="text" readonly class="form-control-plaintext" value="{{ $admin->id_admin ?? '-' }}">
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Nama Lengkap</label>
                    <div class="col-sm-9">
                        <input type="text" readonly class="form-control-plaintext" value="{{ $admin->nama ?? '-' }}">
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Email</label>
                    <div class="col-sm-9">
                        <input type="text" readonly class="form-control-plaintext" value="{{ $admin->email ?? '-' }}">
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">No HP</label>
                    <div class="col-sm-9">
                        <input type="text" readonly class="form-control-plaintext" value="{{ $admin->no_hp ?? '-' }}">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
