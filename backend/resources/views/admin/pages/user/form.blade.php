@php
    $k = $karyawan ?? null;
@endphp

<div class="form-group">
    <label>ID Karyawan</label>
    <input type="number" name="id_karyawan" class="form-control" value="{{ $k->id_karyawan ?? '' }}" required>
</div>

<div class="form-group">
    <label>Nama Lengkap</label>
    <input type="text" name="nama_lengkap" class="form-control" value="{{ $k->nama_lengkap ?? '' }}" required>
</div>

<div class="form-group">
    <label>Username</label>
    <input type="text" name="username" class="form-control" value="{{ $k->username ?? '' }}" required>
</div>

<div class="form-group">
    <label>Email</label>
    <input type="email" name="email" class="form-control" value="{{ $k->email ?? '' }}" required>
</div>

<div class="form-group">
    <label>Alamat</label>
    <textarea name="alamat" class="form-control" rows="2" required>{{ $k->alamat ?? '' }}</textarea>
</div>

<div class="form-group">
    <label>No HP</label>
    <input type="text" name="no_hp" class="form-control" value="{{ $k->no_hp ?? '' }}" required>
</div>
