@extends('layouts.app')

@section('title', 'Ajukan Izin')

@section('sidebar-menu')
    @include('layouts.karyawan-sidebar')
@endsection

@section('content')
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('karyawan.izin.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h5 class="fw-bold mb-0">Ajukan Izin / Sakit</h5>
            <p class="text-muted small mb-0">Pengajuan akan diteruskan ke admin untuk disetujui</p>
        </div>
    </div>

    <div class="card" style="max-width:580px">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('karyawan.izin.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Jenis <span class="text-danger">*</span></label>
                    <div class="d-flex gap-2">
                        @foreach(['izin' => ['label'=>'Izin','icon'=>'fa-calendar-minus','color'=>'secondary'],
                                   'sakit'=> ['label'=>'Sakit','icon'=>'fa-hospital','color'=>'info'],
                                   'cuti' => ['label'=>'Cuti','icon'=>'fa-umbrella-beach','color'=>'primary']] as $val => $opt)
                            <div class="flex-fill">
                                <input type="radio" class="btn-check" name="jenis" id="jenis_{{ $val }}"
                                       value="{{ $val }}" {{ old('jenis','izin') === $val ? 'checked' : '' }}>
                                <label class="btn btn-outline-{{ $opt['color'] }} w-100" for="jenis_{{ $val }}">
                                    <i class="fas {{ $opt['icon'] }} me-1"></i>{{ $opt['label'] }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('jenis')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_mulai"
                               value="{{ old('tanggal_mulai', today()->format('Y-m-d')) }}"
                               min="{{ today()->format('Y-m-d') }}"
                               class="form-control @error('tanggal_mulai') is-invalid @enderror">
                        @error('tanggal_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_selesai"
                               value="{{ old('tanggal_selesai', today()->format('Y-m-d')) }}"
                               min="{{ today()->format('Y-m-d') }}"
                               class="form-control @error('tanggal_selesai') is-invalid @enderror">
                        @error('tanggal_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-1">
                    <span class="badge bg-light text-dark border" id="infoDurasi">
                        <i class="fas fa-calendar me-1"></i> <span id="labelDurasi">1 hari</span>
                    </span>
                </div>

                <div class="mb-3 mt-3">
                    <label class="form-label fw-semibold">Alasan <span class="text-danger">*</span></label>
                    <textarea name="alasan" rows="3"
                              class="form-control @error('alasan') is-invalid @enderror"
                              placeholder="Jelaskan alasan izin Anda (min. 10 karakter)...">{{ old('alasan') }}</textarea>
                    @error('alasan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted" id="hitungKarakter">0 / 500 karakter</small>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        Lampiran <small class="text-muted fw-normal">(opsional – surat dokter, dll)</small>
                    </label>
                    <input type="file" name="lampiran" accept=".jpg,.jpeg,.png,.pdf"
                           class="form-control @error('lampiran') is-invalid @enderror">
                    @error('lampiran')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Format: JPG, PNG, PDF. Maks 2MB.</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="fas fa-paper-plane me-2"></i>Kirim Pengajuan
                    </button>
                    <a href="{{ route('karyawan.izin.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Hitung durasi
    function hitungDurasi() {
        const mulai   = new Date(document.querySelector('[name=tanggal_mulai]').value);
        const selesai = new Date(document.querySelector('[name=tanggal_selesai]').value);
        if (isNaN(mulai) || isNaN(selesai)) return;
        const hari = Math.round((selesai - mulai) / 86400000) + 1;
        document.getElementById('labelDurasi').textContent = hari + (hari > 1 ? ' hari' : ' hari');
        // Pastikan selesai >= mulai
        document.querySelector('[name=tanggal_selesai]').min = document.querySelector('[name=tanggal_mulai]').value;
    }

    document.querySelector('[name=tanggal_mulai]').addEventListener('change', hitungDurasi);
    document.querySelector('[name=tanggal_selesai]').addEventListener('change', hitungDurasi);
    hitungDurasi();

    // Hitung karakter alasan
    const alasan = document.querySelector('[name=alasan]');
    alasan.addEventListener('input', () => {
        document.getElementById('hitungKarakter').textContent = alasan.value.length + ' / 500 karakter';
    });
</script>
@endpush
