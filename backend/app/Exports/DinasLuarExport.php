<?php

namespace App\Exports;

use App\Models\Pengajuan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DinasLuarExport implements 
    FromQuery, 
    WithHeadings, 
    WithMapping, 
    ShouldAutoSize, 
    WithStyles, 
    WithCustomStartCell,
    WithEvents
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    // 1. Tabel dimulainya di Baris 14 (Baris 1-5 Kop, 6 Spasi/Garis, 7 Judul, 8-12 Filter)
    public function startCell(): string
    {
        return 'A14';
    }

    // 2. Headings Tabel
    public function headings(): array
    {
        return [
            'NO',
            'NAMA GTK',
            'TANGGAL MULAI',
            'TANGGAL SELESAI',
            'JENIS PENGAJUAN',
            'ALASAN / TUGAS',
            'STATUS'
        ];
    }

    // 3. Mapping Data
    public function map($pengajuan): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            ($pengajuan->user->nama ?? '-') . "\n" . ($pengajuan->user->jenis_gtk ?? 'Pegawai'),
            $pengajuan->tanggal_mulai,
            $pengajuan->tanggal_selesai,
            ucwords(str_replace('_', ' ', $pengajuan->jenis_pengajuan)),
            $pengajuan->alasan ?? '-',
            strtoupper($pengajuan->status),
        ];
    }

    // 4. Query Data (Sesuai PresensiKhususController)
    public function query()
    {
        $query = Pengajuan::with('user')
            ->whereIn('jenis_pengajuan', ['wfh', 'dinas_luar']);

        if ($this->request->search || $this->request->role) {
            $query->whereHas('user', function ($q) {
                if ($this->request->search) {
                    $q->where('nama', 'like', '%' . $this->request->search . '%');
                }
                if ($this->request->role == 'guru') {
                    $q->where('role', 'guru')->where('jenis_gtk', 'Guru');
                } elseif ($this->request->role == 'tenaga') {
                    $q->where('role', 'guru')->where('jenis_gtk', 'Tenaga Kependidikan');
                } elseif ($this->request->role) {
                    $q->where('role', $this->request->role);
                }
            });
        }

        if ($this->request->status) {
            $query->where('status', $this->request->status);
        }

        if ($this->request->jenis) {
            $query->where('jenis_pengajuan', $this->request->jenis);
        }

        $tanggalAwal = $this->request->tanggal_awal;
        $tanggalAkhir = $this->request->tanggal_akhir;

        if ($this->request->semester) {
            [$tahunMulai, $tipeSemester] = explode('-', $this->request->semester);
            $tahunMulai = (int)$tahunMulai;

            if ($tipeSemester === 'ganjil') {
                $tanggalAwal = $tahunMulai . '-07-01';
                $tanggalAkhir = $tahunMulai . '-12-31';
            } else {
                $tanggalAwal = ($tahunMulai + 1) . '-01-01';
                $tanggalAkhir = ($tahunMulai + 1) . '-06-30';
            }
        }

        if ($tanggalAwal && $tanggalAkhir) {
            $query->where(function($q) use ($tanggalAwal, $tanggalAkhir) {
                $q->whereBetween('tanggal_mulai', [$tanggalAwal, $tanggalAkhir])
                  ->orWhereBetween('tanggal_selesai', [$tanggalAwal, $tanggalAkhir]);
            });
        }

        return $query->orderByDesc('updated_at');
    }

    // 5. Layout Kop Surat, Info Filter & Format Border
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // --- A. KOP SURAT (BARIS 1 - 4) ---
                $sheet->mergeCells('A1:G1');
                $sheet->mergeCells('A2:G2');
                $sheet->mergeCells('A3:G3');
                $sheet->mergeCells('A4:G4');

                $sheet->setCellValue('A1', 'PEMERINTAH PROVINSI SUMATERA BARAT');
                $sheet->setCellValue('A2', 'DINAS PENDIDIKAN');
                $sheet->setCellValue('A3', 'SMA NEGERI 3 PADANG');
                $sheet->setCellValue('A4', 'Jl. Gajah Mada No. 4, Kampung Olo, Kec. Nanggalo, Kota Padang, Sumatera Barat 25143');

                $sheet->getStyle('A1:A3')->getFont()->setBold(true);
                $sheet->getStyle('A1')->getFont()->setSize(12);
                $sheet->getStyle('A2')->getFont()->setSize(13);
                $sheet->getStyle('A3')->getFont()->setSize(14);
                $sheet->getStyle('A4')->getFont()->setSize(9)->setItalic(true);
                $sheet->getStyle('A1:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Garis ganda di bawah Kop Surat (Baris 5)
                $sheet->getStyle('A5:G5')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);

                // --- B. JUDUL LAPORAN (BARIS 7) ---
                $sheet->mergeCells('A7:G7');
                $sheet->setCellValue('A7', 'DINAS LUAR & WFH');
                $sheet->getStyle('A7')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // --- C. BLOK INFORMASI FILTER (BARIS 8 - 11) ---
                // Format parameter filter dari request
                $periodeSemester = $this->request->semester ? ucwords(str_replace('-', ' ', $this->request->semester)) : 'Semua Semester';
                $rentangTanggal = ($this->request->tanggal_awal && $this->request->tanggal_akhir) 
                    ? $this->request->tanggal_awal . ' s/d ' . $this->request->tanggal_akhir 
                    : '-';
                $pencarianNama = $this->request->search ?? '-';
                $kategoriPegawai = $this->request->role ? ucfirst($this->request->role) : 'Semua Kategori';
                $statusJenis = ($this->request->jenis ? ucfirst($this->request->jenis) : 'Dinas Luar & WFH') . 
                                ($this->request->status ? ' (' . ucfirst($this->request->status) . ')' : ' (Semua Status)');
                $waktuCetak = date('d-m-Y H:i') . ' WIB';

                // Warna background blok filter (Gray/Ice Blue tipis)
                $sheet->getStyle('A8:G12')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('F8FAFC');

                // Aksen garis hijau vertikal di sebelah kiri filter
                $sheet->getStyle('A8:A12')->getBorders()->getLeft()
                    ->setBorderStyle(Border::BORDER_MEDIUM)
                    ->getColor()->setARGB('059669'); // Warna hijau tua elegan

                // Isi data filter kolom kiri
                $sheet->setCellValue('A8', 'Periode Semester'); $sheet->setCellValue('B8', ': ' . $periodeSemester);
                $sheet->setCellValue('A9', 'Rentang Tanggal');  $sheet->setCellValue('B9', ': ' . $rentangTanggal);
                $sheet->setCellValue('A10', 'Pencarian Nama');  $sheet->setCellValue('B10', ': ' . $pencarianNama);

                // Isi data filter kolom kanan
                $sheet->setCellValue('D8', 'Kategori Pegawai'); $sheet->setCellValue('E8', ': ' . $kategoriPegawai);
                $sheet->setCellValue('D9', 'Status / Jenis');    $sheet->setCellValue('E9', ': ' . $statusJenis);
                $sheet->setCellValue('D10', 'Waktu Cetak');     $sheet->setCellValue('E10', ': ' . $waktuCetak);

                // Bold untuk label filter
                $sheet->getStyle('A8:A10')->getFont()->setBold(true)->getColor()->setARGB('334155');
                $sheet->getStyle('D8:D10')->getFont()->setBold(true)->getColor()->setARGB('334155');

                // --- D. BORDER & ALIGNMENT TABEL DATA ---
                $highestRow = $sheet->getHighestRow();
                
                // Border tipis untuk seluruh tabel data
                $sheet->getStyle("A14:G{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Ratakan Alignment
                $sheet->getStyle("A14:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // NO
                $sheet->getStyle("C14:D{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Tanggal
                $sheet->getStyle("E14:E{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Jenis
                $sheet->getStyle("G14:G{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Status

                // Aktifkan text wrap agar jika nama pegawai / alasan panjang, tampilannya tetap rapi bersusun
                $sheet->getStyle("B14:F{$highestRow}")->getAlignment()->setWrapText(true);
            },
        ];
    }

    // 6. Header Tabel Warna Hijau Lembut (Sage Green)
    public function styles(Worksheet $sheet)
    {
        return [
            14 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '1E293B'], // Teks abu gelap elegan
                    'size' => 10
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8F5E9'] // Hijau lembut / Sage green (#E8F5E9)
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}