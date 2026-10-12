<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cetak QR Code Absensi - SMAN 3 Padang</title>

    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            -webkit-print-color-adjust: exact; /* Memaksa printer mencetak background warna */
            print-color-adjust: exact;
        }

        /* Container Plakat / Card */
        .card {
            background-color: #ffffff;
            border: 2px solid #e2e8f0;
            padding: 40px 35px;
            width: 420px;
            margin: 40px auto;
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        /* Aksen Dekorasi Top Bar Premium khas Aplikasi Presentri */
        .card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 10px;
            background: linear-gradient(90deg, #059669, #10b981); /* Warna Hijau Emerald Khas Aplikasi */
        }

        /* Nama QR / Judul Utama */
        h2 {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 10px;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Subtitle Instansi Sekolah */
        .subtitle {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Pembungkus QR Code agar memiliki border halus & padding */
        .qr-container {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            padding: 20px;
            display: inline-block;
            border-radius: 16px;
            box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.02);
            margin-bottom: 25px;
        }

        /* Kode Token di Bawah QR */
        .kode {
            font-family: 'Courier New', Courier, monospace;
            font-size: 15px;
            font-weight: bold;
            color: #334155;
            background-color: #f1f5f9;
            padding: 8px 16px;
            border-radius: 8px;
            display: inline-block;
            letter-spacing: 2px;
            margin-bottom: 25px;
            border: 1px dashed #cbd5e1;
        }

        /* Petunjuk Tambahan untuk Guru/Pegawai */
        .petunjuk {
            border-top: 1px solid #f1f5f9;
            padding-top: 20px;
            font-size: 12px;
            color: #64748b;
            line-height: 1.6;
        }

        .petunjuk strong {
            color: #059669;
        }

        /* ==========================================================================
           PENGATURAN KHUSUS SAAT DICETAK KERTAS (PRINT STYLES)
           ========================================================================== */
        @media print {
            body {
                background-color: #ffffff;
                padding-top: 0;
            }
            .card {
                border: 2px solid #059669; /* Memberi border hijau tegas saat dicetak */
                box-shadow: none;
                margin: 0 auto;
                page-break-inside: avoid;
            }
            .kode {
                background-color: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <div class="card">
        
        <h2>{{ $qr->nama_qr }}</h2>
        <div class="subtitle">SMAN 3 Padang</div>

        <div class="qr-container">
            {!! QrCode::size(280)
                ->eye('square') /* Bentuk sudut mata QR kotak modern */
                ->style('square')
                ->color(15, 23, 42) /* Warna QR Slate gelap premium, bukan hitam pekat kaku */
                ->generate($qr->kode_qr) !!}
        </div>

        <div>
            <div class="kode">
                {{ $qr->kode_qr }}
            </div>
        </div>

        <div class="petunjuk">
            Gunakan menu <strong>Scan QR</strong> pada aplikasi <strong>Presentri Mobile</strong><br>
            untuk melakukan validasi presensi kehadiran / kepulangan.
        </div>

    </div>

    <script>
        // Otomatis trigger print dialog saat halaman selesai dimuat
        window.onload = function() {
            window.print();
        }
    </script>

</body>
</html>