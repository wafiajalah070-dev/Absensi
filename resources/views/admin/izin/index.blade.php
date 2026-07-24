@extends('layouts.app')

@section('title', 'Kelola Izin')

@section('sidebar-menu')
    <a href="{{ route('admin.dashboard') }}" class="nav-link">
        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
    </a>
    <a href="{{ route('admin.scanner') }}" class="nav-link">
        <i class="fas fa-camera me-2"></i> Scanner Absensi
    </a>
    <a href="{{ route('admin.izin.index') }}" class="nav-link active">
        <i class="fas fa-file-alt me-2"></i> Kelola Izin
        @if($jumlahPending > 0)
            <span class="badge bg-danger ms-1">{{ $jumlahPending }}</span>
        @endif
    </a>
    <a href="{{ route('admin.karyawan.index') }}" class="nav-link">
        <i class="fas fa-users me-2"></i> Karyawan
    </a>
    <a href="{{ route('admin.rekap.index') }}" class="nav-link">
        <i class="fas fa-chart-bar me-2"></i> Rekap Absensi
    </a>
    <a href="{{ route('admin.pengaturan-kantor') }}" class="nav-link">
        <i class="fas fa-map-marker-alt me-2"></i> Pengaturan Kantor
    </a>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-0">Kelola Izin Karyawan</h5>
            @if($jumlahPending > 0)
                <p class="text-warning small mb-0">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    {{ $jumlahPending }} pengajuan menunggu persetujuan
                </p>
            @else
                <p class="text-muted small mb-0">Semua pengajuan sudah diproses</p>
            @endif
        </div>
    </div>

    {{-- Filter Status --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="d-flex gap-2 flex-wrap">
                @foreach(['pending'=>'Menunggu','disetujui'=>'Disetujui','ditolak'=>'Ditolak','semua'=>'Semua'] as $val => $label)
                    <a href="{{ route('admin.izin.index', ['status'=>$val]) }}"
                       class="btn btn-sm {{ $status === $val ? 'btn-primary' : 'btn-outline-secondary' }}">
                        {{ $label }}
                        @if($val === 'pending' && $jumlahPending > 0)
                            <span class="badge bg-danger ms-1">{{ $jumlahPending }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Karyawan</th>
                            <th>Jenis</th>
                            <th>Tanggal</th>
                            <th>Durasi</th>
                            <th>Alasan</th>
                            <th>Lampiran</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($izins as $izin)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold">{{ $izin->user->name }}</div>
                                    <div class="text-muted small">{{ $izin->user->divisi ?? '-' }}</div>
                                </td>
                                <td>{!! $izin->badge_jenis !!}</td>
                                <td>
                                    <div>{{ $izin->tanggal_mulai->format('d/m/Y') }}</div>
                                    @if($izin->tanggal_mulai != $izin->tanggal_selesai)
                                        <small class="text-muted">s/d {{ $izin->tanggal_selesai->format('d/m/Y') }}</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">{{ $izin->jumlah_hari }}h</span>
                                </td>
                                <td class="small text-muted" style="max-width:180px">
                                    {{ Str::limit($izin->alasan, 60) }}
                                </td>
                                <td>
                                    @if($izin->lampiran)
                                        <a href="{{ Storage::url($izin->lampiran) }}" target="_blank"
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-paperclip"></i>
                                        </a>
                                    @else
                                        <span class="text-muted small">–</span>
                                    @endif
                                </td>
                                <td>{!! $izin->badge_status !!}</td>
                                <td class="text-center" style="min-width:180px">
                                    @if($izin->status === 'pending')
                                        <div class="d-flex gap-1 justify-content-center">
                                            {{-- Setujui --}}
                                            <form method="POST" action="{{ route('admin.izin.setujui', $izin) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success"
                                                        onclick="return confirm('Setujui izin {{ $izin->user->name }}?')">
                                                    <i class="fas fa-check me-1"></i>Setujui
                                                </button>
                                            </form>
                                            {{-- Tolak --}}
                                            <button type="button" class="btn btn-sm btn-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalTolak{{ $izin->id }}">
                                                <i class="fas fa-times me-1"></i>Tolak
                                            </button>
                                        </div>

                                        {{-- Modal Tolak --}}
                                        <div class="modal fade" id="modalTolak{{ $izin->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-sm">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('admin.izin.tolak', $izin) }}">
                                                        @csrf
                                                        <div class="modal-header border-0 pb-0">
                                                            <h6 class="modal-title fw-bold">Tolak Izin</h6>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body pt-2">
                                                            <p class="text-muted small mb-2">{{ $izin->user->name }} – {{ $izin->jenis }}</p>
                                                            <label class="form-label fw-semibold small">Alasan Penolakan <span class="text-danger">*</span></label>
                                                            <textarea name="catatan_admin" class="form-control form-control-sm"
                                                                      rows="3" placeholder="Tulis alasan penolakan..." required></textarea>
                                                        </div>
                                                        <div class="modal-footer border-0 pt-0">
                                                            <button type="submit" class="btn btn-sm btn-danger w-100">Tolak Izin</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-muted small">{{ $izin->catatan_admin ?? '–' }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="fas fa-file-alt fa-2x mb-2 d-block opacity-25"></i>
                                    Tidak ada pengajuan izin
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($izins->hasPages())
            <div class="card-footer bg-white">{{ $izins->appends(['status' => $status])->links() }}</div>
        @endif
    </div>
@endsection
