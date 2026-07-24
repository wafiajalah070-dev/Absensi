@extends('layouts.app')

@section('title', 'Pengajuan Izin')

@section('sidebar-menu')
    @include('layouts.karyawan-sidebar')
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-0">Pengajuan Izin / Sakit</h5>
            <p class="text-muted small mb-0">Riwayat pengajuan izin Anda</p>
        </div>
        <a href="{{ route('karyawan.izin.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Ajukan Izin
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Jenis</th>
                            <th>Tanggal</th>
                            <th>Durasi</th>
                            <th>Alasan</th>
                            <th>Status</th>
                            <th>Catatan Admin</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($izins as $izin)
                            <tr>
                                <td class="ps-4">{!! $izin->badge_jenis !!}</td>
                                <td>
                                    <div>{{ $izin->tanggal_mulai->format('d/m/Y') }}</div>
                                    @if($izin->tanggal_mulai != $izin->tanggal_selesai)
                                        <small class="text-muted">s/d {{ $izin->tanggal_selesai->format('d/m/Y') }}</small>
                                    @endif
                                </td>
                                <td>{{ $izin->jumlah_hari }} hari</td>
                                <td class="text-muted small" style="max-width:200px">
                                    {{ Str::limit($izin->alasan, 60) }}
                                </td>
                                <td>{!! $izin->badge_status !!}</td>
                                <td class="text-muted small">{{ $izin->catatan_admin ?? '-' }}</td>
                                <td class="text-center">
                                    @if($izin->status === 'pending')
                                        <form method="POST" action="{{ route('karyawan.izin.destroy', $izin) }}"
                                              onsubmit="return confirm('Batalkan pengajuan ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Batalkan">
                                                <i class="fas fa-times"></i> Batalkan
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">–</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fas fa-file-alt fa-2x mb-2 d-block opacity-25"></i>
                                    Belum ada pengajuan izin
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($izins->hasPages())
            <div class="card-footer bg-white">{{ $izins->links() }}</div>
        @endif
    </div>
@endsection
