@extends('layouts.app')

@section('title', 'Tambah Karyawan')

@section('sidebar-menu')
    @include('layouts.admin-sidebar')
@endsection

@section('content')
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('admin.karyawan.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h5 class="fw-bold mb-0">Tambah Karyawan</h5>
            <p class="text-muted small mb-0">QR code akan otomatis dibuat</p>
        </div>
    </div>

    <div class="card" style="max-width:600px">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.karyawan.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="form-control @error('name') is-invalid @enderror" placeholder="Nama karyawan">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror" placeholder="email@contoh.com">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">NIP</label>
                        <input type="text" name="nip" value="{{ old('nip') }}"
                               class="form-control @error('nip') is-invalid @enderror" placeholder="Nomor Induk Pegawai">
                        @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Jabatan</label>
                        <input type="text" name="jabatan" value="{{ old('jabatan') }}"
                               class="form-control" placeholder="Contoh: Staff IT">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Divisi</label>
                        <input type="text" name="divisi" value="{{ old('divisi') }}"
                               class="form-control" placeholder="Contoh: Teknologi">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror" placeholder="Min. 6 karakter">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation"
                               class="form-control" placeholder="Ulangi password">
                    </div>
                    <div class="col-12 pt-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i>Simpan & Buat QR
                        </button>
                        <a href="{{ route('admin.karyawan.index') }}" class="btn btn-outline-secondary ms-2">Batal</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
