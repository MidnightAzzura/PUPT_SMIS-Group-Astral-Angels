<?php

namespace App\Exports;

use App\Models\Requests;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LogsExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths, WithMapping
{
    protected $startDate;
    protected $endDate;
    protected $status;
    protected $office;

    public function __construct($startDate = null, $endDate = null, $status = null, $office = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->status = $status;
        $this->office = $office;
    }    public function collection()
    {
        $query = Requests::with('user');
        
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
        }
        
        if ($this->status && $this->status !== 'all') {
            $query->where('status', $this->status);
        }

        // Add office filter
        if ($this->office && $this->office !== 'all') {
            $query->whereHas('user', function($q) {
                $q->where('office', $this->office);
            });
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }    public function map($request): array
    {
        return [
            $request->StockNumber ?? '',
            $request->user->Office ?? 'N/A',
            $request->supply_name ?? '',
            $request->supply_quantity ?? 0,
            ucfirst($request->status ?? ''),
            $request->created_at ? $request->created_at->format('Y-m-d H:i:s') : '',
            $request->updated_at ? $request->updated_at->format('Y-m-d H:i:s') : '',
        ];
    }public function headings(): array
    {
        return [
            'STOCK #',
            'OFFICE',
            'SUPPLY NAME',
            'REQUESTED QTY',
            'STATUS',
            'CREATED AT',
            'UPDATED AT'
        ];
    }

    public function title(): string
    {
        $title = 'Supply Requests Report';
        
        if ($this->status && $this->status !== 'all') {
            $title .= ' - ' . ucfirst($this->status);
        }
        
        if ($this->startDate && $this->endDate) {
            $title .= ' (' . $this->startDate . ' to ' . $this->endDate . ')';
        }
        
        return $title;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // Stock #
            'B' => 20, // Office
            'C' => 30, // Supply Name
            'D' => 15, // Requested Qty
            'E' => 12, // Status
            'F' => 20, // Created At
            'G' => 20, // Updated At
        ];
    }    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'A52A2A'],  // Dark red color to match the image
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
        
        // Add borders to all cells
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:G' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        // Center align the Status and Quantity columns
        if ($lastRow > 1) {
            $sheet->getStyle('D2:E' . $lastRow)->getAlignment()
                  ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // Add alternating row colors for better readability
        for ($row = 2; $row <= $lastRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':G' . $row)->getFill()
                      ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                      ->getStartColor()->setRGB('F8F9FA'); // Light gray
            }
        }

        return $sheet;
    }
}