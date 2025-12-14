<?php

namespace App\Exports;

use App\Models\AdminAuditLog;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Carbon\Carbon;

class AuditLogsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    use Exportable;

    protected ?string $startDate;
    protected ?string $endDate;
    protected ?string $actionType;
    protected ?string $adminId;

    public function __construct($startDate = null, $endDate = null, $actionType = null, $adminId = null)
    {
        $this->startDate  = $startDate;
        $this->endDate    = $endDate;
        $this->actionType = $actionType;
        $this->adminId    = $adminId;
    }

    public function collection()
    {
        $q = AdminAuditLog::with('admin');

        if ($this->startDate && $this->endDate) {
            $q->whereBetween('performed_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
        }

        if ($this->actionType && $this->actionType !== 'all') {
            $q->where('action_type', $this->actionType);
        }

        if ($this->adminId && $this->adminId !== 'all') {
            $q->where('admin_id', $this->adminId);
        }

        return $q->orderBy('performed_at', 'desc')->get();
    }

    public function headings(): array
    {
        return ['Date/Time', 'Admin', 'Action', 'Details'];
    }

    public function map($log): array
    {
        $date = $log->performed_at
            ? Carbon::parse($log->performed_at)->format('M d, Y g:i A')
            : '';

        $adminName = $log->admin
            ? trim(($log->admin->First_Name ?? '') . ' ' . ($log->admin->Last_Name ?? ''))
            : ($log->admin_name ?? 'Unknown Admin');

        $action = $log->action_type
            ? ucfirst(strtolower(str_replace('_', ' ', $log->action_type)))
            : 'Unknown';

        // Mirror details logic from the blade
        $details = '';
        if (!empty($log->description)) {
            $details = $log->description;
        } else {
            $actionType = strtolower($log->action_type ?? '');
            $targetType = $log->target_type ?? 'item';
            $targetName = $log->target_name ?? 'Unknown';

            if (in_array($actionType, ['create', 'created'])) {
                $details = "Added new {$targetType}: {$targetName}";
            } elseif (in_array($actionType, ['update', 'updated'])) {
                $details = "Edited {$targetType}: {$targetName}";
                // Append changes if available
                $oldVals = is_string($log->old_values) ? json_decode($log->old_values, true) : $log->old_values;
                $newVals = is_string($log->new_values) ? json_decode($log->new_values, true) : $log->new_values;
                if (is_array($oldVals) && is_array($newVals)) {
                    $changes = [];
                    foreach ($newVals as $key => $newVal) {
                        if (array_key_exists($key, $oldVals) && $oldVals[$key] != $newVal) {
                            $changes[] = "Changed {$key} from \"{$oldVals[$key]}\" to \"{$newVal}\"";
                        }
                    }
                    if (!empty($changes)) {
                        $details .= ' | ' . implode('; ', $changes);
                    }
                }
            } elseif (in_array($actionType, ['delete', 'deleted'])) {
                $details = "Deleted {$targetType}: {$targetName}";
            } elseif (in_array($actionType, ['approve', 'approved'])) {
                $details = "Approved {$targetType}: {$targetName}";
            } elseif (in_array($actionType, ['deny', 'denied'])) {
                $details = "Denied {$targetType}: {$targetName}";
            } else {
                $details = trim(ucfirst($actionType) . " {$targetType}: {$targetName}");
            }
        }

        return [$date, $adminName, $action, $details];
    }

    // NEW: Style the sheet like the second image
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Determine used range
                $lastRow = $sheet->getHighestRow();       // e.g. 1 if only headers
                $lastCol = 'D';                            // Fixed (A..D)
                $rangeAll = "A1:{$lastCol}{$lastRow}";
                $headerRange = "A1:{$lastCol}1";

                // Header style (maroon background, white bold text, centered)
                $sheet->getStyle($headerRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF800020'); // maroon
                $sheet->getStyle($headerRange)->getFont()
                    ->setBold(true)
                    ->getColor()->setARGB(Color::COLOR_WHITE);
                $sheet->getStyle($headerRange)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getRowDimension(1)->setRowHeight(22);

                // Borders for entire used range
                $sheet->getStyle($rangeAll)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setARGB('FF666666');

                // Column widths similar to the sample
                $sheet->getColumnDimension('A')->setWidth(22); // Date/Time
                $sheet->getColumnDimension('B')->setWidth(28); // Admin
                $sheet->getColumnDimension('C')->setWidth(14); // Action
                $sheet->getColumnDimension('D')->setWidth(60); // Details

                // Alignments for body cells
                if ($lastRow > 1) {
                    $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle("B2:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle("C2:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D2:D{$lastRow}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setWrapText(true);
                }

                // Freeze header and add autofilter
                $event->sheet->freezePane('A2');
                $sheet->setAutoFilter($headerRange);

                // Optional: name the worksheet
                $sheet->setTitle('Audit Logs');
            },
        ];
    }
}
