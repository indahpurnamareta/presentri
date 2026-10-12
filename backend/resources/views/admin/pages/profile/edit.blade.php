@extends('admin.layouts.app')

@section('title', 'Edit Profil')

@section('content')
<div class="container-fluid py-4 saas-user">
    
    <div class="mb-4">
        <h1 class="h3 font-weight-bold text-dark mb-1" style="font-size:1.65rem; letter-spacing:-.02em;">
            Pengaturan Profil
        </h1>
        <p class="text-muted mb-0">
            Perbarui informasi personal akun Anda di sini.
        </p>
    </div>

    {{-- ALERT SUCCESS/ERROR --}}
    @if(session('success'))
        <div class="alert saas-alert saas-alert-success mb-4" style="background: rgba(16, 185, 129, 0.08); color: #10b981; border: none; border-radius: 14px; padding: 1rem 1.25rem; font-weight: 600;">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert saas-alert saas-alert-danger mb-4" style="background: rgba(244, 63, 94, 0.08); color: #f43f5e; border: none; border-radius: 14px; padding: 1rem 1.25rem; font-weight: 600;">
            <i class="fas fa-times-circle mr-2"></i> Ada kesalahan pada input Anda. Silakan cek kembali.
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card saas-card" style="background: #fff; border-radius: 20px; border: 1px solid rgba(0,0,0,.04); box-shadow: 0 4px 20px rgba(0,0,0,.02);">
                <div class="card-body p-4">
                    
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        
                        <h5 class="font-weight-bold text-dark mb-4">Informasi Profil</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted small">Nama Lengkap</label>
                                    <input type="text" name="nama" class="form-control saas-input @error('nama') is-invalid @enderror" value="{{ old('nama', $user->nama) }}" required style="border-radius: 12px; height: 44px; background: #f8fafc;">
                                    @error('nama') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted small">Username</label>
                                    <input type="text" name="username" class="form-control saas-input @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}" required style="border-radius: 12px; height: 44px; background: #f8fafc;">
                                    @error('username') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted small">No. HP / WhatsApp</label>
                                    <input type="text" name="no_hp" class="form-control saas-input @error('no_hp') is-invalid @enderror" value="{{ old('no_hp', $user->no_hp) }}" style="border-radius: 12px; height: 44px; background: #f8fafc;">
                                    @error('no_hp') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted small">Role Akun</label>
                                    <input type="text" class="form-control" value="{{ strtoupper(str_replace('_', ' ', $user->role)) }}" disabled style="border-radius: 12px; height: 44px; background: #e2e8f0; font-weight: 600; color: #64748b;">
                                    <small class="text-muted">Role tidak dapat diubah secara mandiri.</small>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4" style="border-top-color: #f1f5f9;">
                        
                        <h5 class="font-weight-bold text-dark mb-4">Keamanan Akun</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted small">Password Baru</label>
                                    <input type="password" name="password_baru" class="form-control saas-input @error('password_baru') is-invalid @enderror" placeholder="Kosongkan jika tidak ingin diubah" style="border-radius: 12px; height: 44px; background: #f8fafc;">
                                    @error('password_baru') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted small">Konfirmasi Password Baru</label>
                                    <input type="password" name="password_baru_confirmation" class="form-control saas-input" placeholder="Masukkan ulang password baru" style="border-radius: 12px; height: 44px; background: #f8fafc;">
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-right">
                            <button type="submit" class="btn btn-emerald" style="background: linear-gradient(135deg, #059669, #10b981); border: none; color: #fff; height: 44px; padding: 0 24px; border-radius: 12px; font-weight: 700; box-shadow: 0 4px 12px rgba(5,150,105,.25);">
                                <i class="fas fa-save mr-2"></i> Simpan Perubahan
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection