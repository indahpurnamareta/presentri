<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
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

class PresensiExport implements 
    FromCollection, 
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

    public function startCell(): string
    {
        return 'A14';
    }

    public function headings(): array
    {
        return [
            'NO',
            'TANGGAL',
            'NAMA GTK',
            'JAM MASUK',
            'JAM PULANG',
            'STATUS KEHADIRAN',
            'KETERLAMBATAN',
            'KETERANGAN'
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->tanggal,
            ($row->user->nama ?? '-') . "\n" . ($row->user->jenis_gtk ?? 'Pegawai'),
            $row->jam_masuk ?? '--:--',
            $row->jam_keluar ?? '--:--',
            strtoupper($row->status),
            $row->status_keterlambatan ?? '-',
            $row->keterangan ?? '-',
        ];
    }

    public function collection()
    {
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

        $queryPresensi = DB::table('presensi')
            ->select('id_user', 'tanggal', 'jam_masuk', 'jam_keluar', 'status', 'status_keterlambatan', 'keterangan', 'updated_at');

        $queryPengajuan = DB::table('pengajuan')
            ->whereNotIn('jenis_pengajuan', ['dinas_luar', 'wfh'])
            ->where('status', 'disetujui')
            ->select('id_user', 'tanggal_mulai as tanggal', DB::raw("'--:--' as jam_masuk"), DB::raw("'--:--' as jam_keluar"), 'jenis_pengajuan as status', DB::raw("'-' as status_keterlambatan"), 'alasan as keterangan', 'updated_at');

        if ($tanggalAwal && $tanggalAkhir) {
            $queryPresensi->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
            $queryPengajuan->where(function($q) use ($tanggalAwal, $tanggalAkhir) {
                $q->whereBetween('tanggal_mulai', [$tanggalAwal, $tanggalAkhir])
                  ->orWhereBetween('tanggal_selesai', [$tanggalAwal, $tanggalAkhir]);
            });
        }

        $userQuery = DB::table('users');
        if ($this->request->search) {
            $userQuery->where('nama', 'like', '%' . $this->request->search . '%');
        }

        if ($this->request->role == 'guru') {
            $userQuery->where('role', 'guru')->where('jenis_gtk', 'Guru');
        } elseif ($this->request->role == 'tenaga') {
            $userQuery->where('role', 'guru')->where('jenis_gtk', 'Tenaga Kependidikan');
        } elseif ($this->request->role) {
            $userQuery->where('role', $this->request->role);
        }

        if ($this->request->search || $this->request->role) {
            $userIds = $userQuery->pluck('id_user');
            $queryPresensi->whereIn('id_user', $userIds);
            $queryPengajuan->whereIn('id_user', $userIds);
        }

        if ($this->request->status) {
            $st = strtolower($this->request->status);
            if ($st === 'alpha') {
                $queryPresensi->where('status', 'alpha');
                $queryPengajuan->whereRaw('0 = 1');
            } else {
                $queryPresensi->where('status', $this->request->status);
                $queryPengajuan->where('jenis_pengajuan', $this->request->status);
            }
        }

        if ($this->request->keterlambatan) {
            $queryPresensi->where('status_keterlambatan', $this->request->keterlambatan);
            $queryPengajuan->whereRaw('0 = 1');
        }

        $combinedQuery = $queryPresensi->unionAll($queryPengajuan);
        $rawPresensis = DB::table(DB::raw("({$combinedQuery->toSql()}) as rekap"))
            ->mergeBindings($combinedQuery)
            ->orderBy('tanggal', 'desc')
            ->get();

        return $rawPresensis->map(function ($item) {
            $item->user = User::find($item->id_user);
            return $item;
        });
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // KOP SURAT
                $sheet->mergeCells('A1:H1');
                $sheet->mergeCells('A2:H2');
                $sheet->mergeCells('A3:H3');
                $sheet->mergeCells('A4:H4');

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

                $sheet->getStyle('A5:H5')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);

                // JUDUL
                $sheet->mergeCells('A7:H7');
                $sheet->setCellValue('A7', 'REKAPITULASI PRESENSI HARI AN');
                $sheet->getStyle('A7')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // INFORMASI FILTER
                $periodeSemester = $this->request->semester ? ucwords(str_replace('-', ' ', $this->request->semester)) : 'Semua Semester';
                $rentangTanggal = ($this->request->tanggal_awal && $this->request->tanggal_akhir) 
                    ? $this->request->tanggal_awal . ' s/d ' . $this->request->tanggal_akhir 
                    : '-';
                $pencarianNama = $this->request->search ?? '-';
                $kategoriPegawai = $this->request->role ? ucfirst($this->request->role) : 'Semua Kategori';
                $statusJenis = $this->request->status ? ucfirst($this->request->status) : 'Semua Status';
                $waktuCetak = date('d-m-Y H:i') . ' WIB';

                $sheet->getStyle('A8:H12')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('F8FAFC');

                $sheet->getStyle('A8:A12')->getBorders()->getLeft()
                    ->setBorderStyle(Border::BORDER_MEDIUM)
                    ->getColor()->setARGB('059669');

                $sheet->setCellValue('A8', 'Periode Semester'); $sheet->setCellValue('B8', ': ' . $periodeSemester);
                $sheet->setCellValue('A9', 'Rentang Tanggal');  $sheet->setCellValue('B9', ': ' . $rentangTanggal);
                $sheet->setCellValue('A10', 'Pencarian Nama');  $sheet->setCellValue('B10', ': ' . $pencarianNama);

                $sheet->setCellValue('E8', 'Kategori Pegawai'); $sheet->setCellValue('F8', ': ' . $kategoriPegawai);
                $sheet->setCellValue('E9', 'Status / Jenis');    $sheet->setCellValue('F9', ': ' . $statusJenis);
                $sheet->setCellValue('E10', 'Waktu Cetak');     $sheet->setCellValue('F10', ': ' . $waktuCetak);

                $sheet->getStyle('A8:A10')->getFont()->setBold(true)->getColor()->setARGB('334155');
                $sheet->getStyle('E8:E10')->getFont()->setBold(true)->getColor()->setARGB('334155');

                // BORDER & ALIGNMENT
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("A14:H{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $sheet->getStyle("A14:B{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D14:G{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C14:H{$highestRow}")->getAlignment()->setWrapText(true);
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            14 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '1E293B'],
                    'size' => 10
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8F5E9']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}