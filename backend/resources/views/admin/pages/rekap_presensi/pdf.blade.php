<!DOCTYPE html>
<html>
<head>
    <title>Rekap Presensi Guru SMAN 3 Padang</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        
        /* ==========================================================================
           STYLE KOP SURAT RESMI
           ========================================================================== */
        .kop-surat {
            position: relative;
            width: 100%;
            border-bottom: 3px solid #000;
            padding-bottom: 2px;
            margin-bottom: 2px;
        }
        .kop-surat-inner {
            border-bottom: 1px solid #000; /* Garis tipis ganda bawah kop */
            padding-bottom: 8px;
            text-align: center;
        }
        .logo-kop {
            position: absolute;
            top: 5px;
            left: 10px;
            width: 70px;
            height: auto;
        }
        .kop-text h1 {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
            padding: 0;
            letter-spacing: 0.5px;
        }
        .kop-text h2 {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 2px 0;
            padding: 0;
            letter-spacing: 1px;
        }
        .kop-text p {
            font-size: 10px;
            font-style: italic;
            margin: 4px 0 0 0;
            padding: 0;
            color: #222;
        }

        /* ==========================================================================
           STYLE KONTEN UTAMA
           ========================================================================== */
        .judul-rekap {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 15px 0 10px 0;
            color: #111;
        }

        /* Gaya Blok Informasi Filter Minimalis */
        .filter-info { 
            background-color: #f8fafc; 
            border-left: 3px solid #059669; 
            padding: 8px 12px; 
            margin-bottom: 15px; 
            font-size: 10.5px;
        }
        .filter-info table { width: auto; margin-top: 0; border: none; }
        .filter-info td { border: none; padding: 2px 8px 2px 0; }
        .filter-title { font-weight: bold; color: #475569; width: 100px; }
        
        /* Tabel Utama Data Presensi */
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        table.data-table th, table.data-table td { border: 1px solid #666; padding: 7px 6px; text-align: left; }
        table.data-table th { background-color: #f1f5f9; font-weight: bold; text-transform: uppercase; font-size: 9.5px; }
        .text-center { text-align: center; }
        
        /* Badge Status Kehadiran & Perizinan */
        .badge { padding: 2px 5px; border-radius: 3px; font-weight: bold; font-size: 9.5px; text-transform: uppercase; display: inline-block; }
        .hadir { background-color: #e6f4ea; color: #137333; }
        .izin { background-color: #e8f0fe; color: #1a73e8; }
        .sakit { background-color: #fef7e0; color: #b06000; }
        .wfh { background-color: #e0f7fa; color: #006064; }
        .dinas_luar { background-color: #f3e5f5; color: #4a148c; }
        .cuti { background-color: #eceff1; color: #37474f; }
        .alpha { background-color: #fce8e6; color: #c5221f; }
    </style>
</head>
<body>

    <div class="kop-surat">
        <div class="kop-surat-inner">
            <img class="logo-kop" src="data:image/png;base64,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">
            
            <div class="kop-text">
                <h1>Pemerintah Provinsi Sumatera Barat</h1>
                <h2>Dinas Pendidikan</h2>
                <h2 style="font-size: 18px; margin-top: 1px;">SMA Negeri 3 Padang</h2>
                <p>Jl. Gajah Mada No. 4, Kampung Olo, Kec. Nanggalo, Kota Padang, Sumatera Barat 25143</p>
            </div>
        </div>
    </div>

    <div class="judul-rekap">
        Laporan Rekapitulasi Presensi Harian Guru & Tenaga Kependidikan
    </div>

    <div class="filter-info">
        <table style="width: 100%;">
            <tr>
                <td class="filter-title">Periode Semester</td>
                <td>: 
                    @if(request('semester') == '2024-genap') Genap 2024/2025 (Jan - Jun 2025)
                    @elseif(request('semester') == '2025-ganjil') Ganjil 2025/2026 (Jul - Des 2025)
                    @elseif(request('semester') == '2025-genap') Genap 2025/2026 (Jan - Jun 2026)
                    @elseif(request('semester') == '2026-ganjil') Ganjil 2026/2027 (Jul - Des 2026)
                    @elseif(request('semester') == '2026-genap') Genap 2026/2027 (Jan - Jun 2027)
                    @else Semua Waktu / Semua Semester
                    @endif
                </td>
                <td class="filter-title" style="padding-left: 30px;">Status Kehadiran</td>
                <td>: 
                    {{ request('status') ? ucfirst(str_replace('_', ' ', request('status'))) : 'Semua Status' }}
                </td>
            </tr>
            <tr>
                <td class="filter-title">Rentang Tanggal</td>
                <td>: 
                    @if(request('tanggal_awal') && request('tanggal_akhir'))
                        {{ date('d-m-Y', strtotime(request('tanggal_awal'))) }} s/d {{ date('d-m-Y', strtotime(request('tanggal_akhir'))) }}
                    @else
                        -
                    @endif
                </td>
                <td class="filter-title" style="padding-left: 30px;">Keterlambatan</td>
                <td>: 
                    @if(request('keterlambatan') == 'tepat_waktu') Tepat Waktu
                    @elseif(request('keterlambatan') == 'terlambat') Terlambat
                    @else Semua Kondisi
                    @endif
                </td>
            </tr>
            <tr>
                <td class="filter-title">Pencarian Nama</td>
                <td>: {{ request('search') ? request('search') : '-' }}</td>
                <td class="filter-title" style="padding-left: 30px;">Waktu Cetak</td>
                <td>: {{ date('d-m-Y H:i') }} WIB</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No</th>
                <th>Nama Guru / Pegawai</th>
                <th>Mata Pelajaran / Tugas</th>
                <th class="text-center" style="width: 75px;">Tanggal</th>
                <th class="text-center" style="width: 65px;">Jam Masuk</th>
                <th class="text-center" style="width: 65px;">Jam Keluar</th>
                <th class="text-center" style="width: 75px;">Status</th>
                <th class="text-center" style="width: 80px;">Ket. Waktu</th>
            </tr>
        </thead>
        <tbody>
            @forelse($presensis as $index => $presensi)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $presensi->user->nama ?? 'User Terhapus' }}</strong>
                </td>
                <td>{{ $presensi->user->mata_pelajaran ?? '-' }}</td>
                <td class="text-center">{{ date('d-m-Y', strtotime($presensi->tanggal)) }}</td>
                <td class="text-center" style="color: #15803d; font-weight: bold;">{{ $presensi->jam_masuk ?? '--:--' }}</td>
                <td class="text-center" style="color: #b91c1c; font-weight: bold;">{{ $presensi->jam_keluar ?? '--:--' }}</td>
                <td class="text-center">
                    @php
                        $cleanStatus = strtolower($presensi->status);
                        $statusLabel = ucfirst(str_replace('_', ' ', $cleanStatus));
                        if($cleanStatus == 'wfh') $statusLabel = 'WFH';
                    @endphp
                    <span class="badge {{ $cleanStatus }}">
                        {{ $statusLabel }}
                    </span>
                </td>
                <td class="text-center">
                    @php
                        $keterlambatan = strtolower(str_replace(' ', '_', $presensi->status_keterlambatan ?? ''));
                    @endphp
                    @if($keterlambatan == 'tepat_waktu' || $presensi->status_keterlambatan == 'Tepat Waktu')
                        <span style="color: #15803d; font-weight: bold;">Tepat Waktu</span>
                    @elseif($keterlambatan == 'terlambat' || $presensi->status_keterlambatan == 'Terlambat')
                        <span style="color: #b45309; font-weight: bold;">Terlambat</span>
                    @else
                        <span style="color: #64748b;">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: 20px; color: #666; font-style: italic;">
                    Tidak ada data log presensi yang ditemukan berdasarkan parameter filter.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>