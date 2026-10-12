<?php

namespace App\Exports;

use App\Models\User;
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

class GuruExport implements 
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

    public function startCell(): string
    {
        return 'A14';
    }

    public function headings(): array
    {
        return [
            'NO',
            'NIP',
            'NAMA GTK / PEGAWAI',
            'USERNAME',
            'ROLE',
            'JENIS GTK',
            'MATA PELAJARAN',
            'NO. HP',
            'STATUS AKUN'
        ];
    }

    public function map($user): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $user->nip ?? '-',
            $user->nama,
            $user->username,
            strtoupper($user->role),
            $user->jenis_gtk ?? '-',
            $user->mata_pelajaran ?? '-',
            $user->no_hp ?? '-',
            ucfirst($user->status_akun ?? 'Aktif'),
        ];
    }

    public function query()
    {
        $query = User::query();

        if ($this->request->search) {
            $query->where(function ($q) {
                $q->where('nama', 'like', '%' . $this->request->search . '%')
                    ->orWhere('username', 'like', '%' . $this->request->search . '%');
            });
        }

        if ($this->request->role == 'guru') {
            $query->where('role', 'guru')->where('jenis_gtk', 'Guru');
        } elseif ($this->request->role == 'tenaga') {
            $query->where('role', 'guru')->where('jenis_gtk', 'Tenaga Kependidikan');
        } elseif ($this->request->role) {
            $query->where('role', $this->request->role);
        }

        return $query->latest();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // KOP SURAT
                $sheet->mergeCells('A1:I1');
                $sheet->mergeCells('A2:I2');
                $sheet->mergeCells('A3:I3');
                $sheet->mergeCells('A4:I4');

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

                $sheet->getStyle('A5:I5')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);

                // JUDUL
                $sheet->mergeCells('A7:I7');
                $sheet->setCellValue('A7', 'DATA PENGGUNA & GURU TENAGA KEPENDIDIKAN (GTK)');
                $sheet->getStyle('A7')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // FILTER
                $pencarianNama = $this->request->search ?? '-';
                $kategoriPegawai = $this->request->role ? ucfirst($this->request->role) : 'Semua Kategori';
                $waktuCetak = date('d-m-Y H:i') . ' WIB';

                $sheet->getStyle('A8:I12')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('F8FAFC');

                $sheet->getStyle('A8:A12')->getBorders()->getLeft()
                    ->setBorderStyle(Border::BORDER_MEDIUM)
                    ->getColor()->setARGB('059669');

                $sheet->setCellValue('A8', 'Pencarian');       $sheet->setCellValue('B8', ': ' . $pencarianNama);
                $sheet->setCellValue('A9', 'Kategori User');   $sheet->setCellValue('B9', ': ' . $kategoriPegawai);

                $sheet->setCellValue('E8', 'Waktu Cetak');     $sheet->setCellValue('F8', ': ' . $waktuCetak);

                $sheet->getStyle('A8:A9')->getFont()->setBold(true)->getColor()->setARGB('334155');
                $sheet->getStyle('E8')->getFont()->setBold(true)->getColor()->setARGB('334155');

                // BORDER & ALIGNMENT
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("A14:I{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $sheet->getStyle("A14:B{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D14:I{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C14:G{$highestRow}")->getAlignment()->setWrapText(true);
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