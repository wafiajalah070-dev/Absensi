@extends('layouts.app')

@section('title', 'Kelola Karyawan')

@section('sidebar-menu')
    @include('layouts.admin-sidebar')
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-0">Kelola Karyawan</h5>
            <p class="text-muted small mb-0">Daftar semua karyawan terdaftar</p>
        </div>
        <a href="{{ route('admin.karyawan.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Tambah Karyawan
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm"
                       placeholder="Cari nama, NIP, divisi...">
                <button class="btn btn-sm btn-primary">
                    <i class="fas fa-search"></i>
                </button>
                @if($search)
                    <a href="{{ route('admin.karyawan.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Nama</th>
                            <th>NIP</th>
                            <th>Jabatan</th>
                            <th>Divisi</th>
                            <th>Email</th>
                            <th>QR Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($karyawans as $i => $k)
                            <tr>
                                <td class="ps-4">{{ $karyawans->firstItem() + $i }}</td>
                                <td class="fw-semibold">{{ $k->name }}</td>
                                <td>{{ $k->nip ?? '-' }}</td>
                                <td>{{ $k->jabatan ?? '-' }}</td>
                                <td>{{ $k->divisi ?? '-' }}</td>
                                <td class="text-muted small">{{ $k->email }}</td>
                                <td>
                                    @if($k->qr_token)
                                        <span class="badge bg-success"><i class="fas fa-qrcode me-1"></i>Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak ada</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.karyawan.qrcode', $k) }}" class="btn btn-outline-primary" title="Lihat QR">
                                            <i class="fas fa-qrcode"></i>
                                        </a>
                                        <a href="{{ route('admin.karyawan.edit', $k) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.karyawan.destroy', $k) }}"
                                              onsubmit="return confirm('Hapus karyawan {{ $k->name }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                                    Belum ada karyawan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($karyawans->hasPages())
            <div class="card-footer bg-white">
                {{ $karyawans->appends(['search' => $search])->links() }}
            </div>
        @endif
    </div>
@endsection
