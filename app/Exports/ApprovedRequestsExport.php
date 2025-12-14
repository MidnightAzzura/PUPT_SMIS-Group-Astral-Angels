<?php
namespace App\Exports;

use App\Models\Requests;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Log;

class ApprovedRequestsExport
{
    protected $filePath;

    public function __construct($filePath = 'approved_requests.xlsx')
    {
        $this->filePath = $filePath;
    }

    public function export()
    {
        // Get approved requests with user relationship
        $approvedRequests = Requests::with(['user', 'items'])
            ->where('status', 'approved')
            ->get();

        // Apply the same grouping logic as in the blade file
        $normalizedRequests = $approvedRequests->map(function($request) {
            // Create a copy to avoid modifying the original
            $request->normalizedPurpose = strtolower(trim($request->purpose ?? $request->Purpose ?? 'office use'));
            
            // Create a timestamp for grouping - rounded to the nearest 10 minutes
            $timestamp = $request->created_at ?? now();
            $minutes = floor($timestamp->minute / 10) * 10;
            $batchTimestamp = $timestamp->format('Y-m-d_H_') . $minutes;
            
            // Create a compound key: purpose + timestamp
            $request->batchKey = $request->normalizedPurpose . '_' . $batchTimestamp;
            
            return $request;
        });
        
        // Group by the compound key (same as blade file)
        $groupedRequests = $normalizedRequests->groupBy('batchKey');

        Log::info('Export Grouped Data:', ['groups' => $groupedRequests->count()]);

        if ($groupedRequests->isEmpty()) {
            Log::warning('No approved requests available to export.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();        // Set document header information and align right
        $sheet->setCellValue('H1', 'PUP-RISL-6-PSMO-010');
        $sheet->setCellValue('H2', 'Rev. 2');
        $sheet->setCellValue('H3', 'Effectivity Date: June 3, 2022');
        $sheet->setCellValue('H5', 'Appendix 63');
        
        // Right align the header information
        $sheet->getStyle('H1:H5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);        // Set main title and center it
        $sheet->setCellValue('A7', 'REQUISITION AND ISSUE SLIP');
        $sheet->mergeCells('A7:H7');
        $sheet->getStyle('A7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A7')->getFont()->setBold(true);        // Set entity information - use dynamic office from first request
        $firstBatch = $groupedRequests->first();
        $firstUser = $firstBatch->first()->user ?? null;
        $userOffice = $firstUser ? strtoupper($firstUser->Office ?? 'HEAD OF ACADEMIC') : 'HEAD OF ACADEMIC';
        
        $sheet->setCellValue('A9', 'Entity Name :');
        $sheet->setCellValue('C9', 'PUP TAGUIG CAMPUS');
        $sheet->setCellValue('G9', 'Fund Cluster : ______________________');

        $sheet->setCellValue('A11', 'Division :');
        $sheet->setCellValue('F11', 'Responsibility Center Code : ______________________');

        $sheet->setCellValue('A12', 'Office :');
        $sheet->setCellValue('C12', $userOffice);
        $sheet->setCellValue('F12', 'RIS No. : _____________________________________');

        // Create table headers
        $sheet->mergeCells('A14:D14');
        $sheet->mergeCells('E14:F14');
        $sheet->mergeCells('G14:H14');
        $sheet->setCellValue('A14', 'Requisition');
        $sheet->setCellValue('E14', 'Stock Available?');
        $sheet->setCellValue('G14', 'Issue');
        
        $sheet->setCellValue('A15', 'Stock No.');
        $sheet->setCellValue('B15', 'Unit');
        $sheet->setCellValue('C15', 'Description');
        $sheet->setCellValue('D15', 'Quantity');
        $sheet->setCellValue('E15', 'Yes');
        $sheet->setCellValue('F15', 'No');
        $sheet->setCellValue('G15', 'Quantity');
        $sheet->setCellValue('H15', 'Remarks');
        
        // Center align the headers
        $sheet->getStyle('A14:H15')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Apply borders and styles to header cells
        $headerStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ]
            ],
            'font' => [
                'bold' => true
            ]
        ];
        
        // Apply borders to the header area
        $sheet->getStyle('A14:H15')->applyFromArray($headerStyle);        // Populate the table with grouped batch data
        $rowIndex = 16; // Data starts from row 16
        
        foreach ($groupedRequests as $batchKey => $requests) {
            $firstRequest = $requests->first();
            $totalItems = $requests->count();
            
            // Get original purpose format from the first request
            $displayPurpose = $firstRequest->purpose ?: $firstRequest->Purpose ?: 'OFFICE USE';
            
            // Count all additional items across all requests in this group
            $additionalItemsCount = $requests->sum(function($req) {
                return $req->items ? $req->items->count() : 0;
            });
            
            // Add batch header if there are multiple items
            if ($totalItems > 1 || $additionalItemsCount > 0) {
                // Add batch header row                $sheet->mergeCells('A' . $rowIndex . ':H' . $rowIndex);
                // Use setCellValueExplicit with TYPE_STRING to prevent formula interpretation
                $sheet->setCellValueExplicit(
                    'A' . $rowIndex, 
                    'BATCH REQUEST - ' . strtoupper($displayPurpose) . ' (Total Items: ' . ($totalItems + $additionalItemsCount) . ')',
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
                $sheet->getStyle('A' . $rowIndex)->getAlignment()
                      ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A' . $rowIndex)->getFont()->setBold(true);
                $sheet->getStyle('A' . $rowIndex)->getFill()
                      ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                      ->getStartColor()->setRGB('E3F2FD');
                $sheet->getStyle('A' . $rowIndex . ':H' . $rowIndex)->applyFromArray($headerStyle);
                $rowIndex++;
            }
            
            // Add all requests in this batch
            foreach ($requests as $requestIndex => $request) {
                // Sanitize data to prevent formula errors
                $stockNumber = $this->sanitizeValue($request->StockNumber);
                $unit = $this->sanitizeValue($request->Unit ?? 'pcs');
                $supplyName = $this->sanitizeValue($request->supply_name);
                $quantity = $this->sanitizeNumericValue($request->supply_quantity);
                $issueQuantity = $this->sanitizeNumericValue($request->issue_quantity);
                
                // Add main request item
                $sheet->setCellValueExplicit('A' . $rowIndex, $stockNumber, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('B' . $rowIndex, $unit, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('C' . $rowIndex, $supplyName, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('D' . $rowIndex, $quantity);
                $sheet->setCellValue('E' . $rowIndex, $request->stock_available === 'yes' ? '●' : '○');
                $sheet->setCellValue('F' . $rowIndex, $request->stock_available === 'no' ? '●' : '○');
                $sheet->setCellValue('G' . $rowIndex, $issueQuantity);
                $sheet->setCellValueExplicit('H' . $rowIndex, '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                
                // Center align the checkboxes and quantity
                $sheet->getStyle('D' . $rowIndex . ':F' . $rowIndex)
                      ->getAlignment()
                      ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                
                // Apply borders to each data row
                $sheet->getStyle('A' . $rowIndex . ':H' . $rowIndex)->applyFromArray($headerStyle);
                $rowIndex++;
                
                // Add additional items if they exist
                if ($request->items && $request->items->count() > 0) {
                    foreach ($request->items as $item) {
                        $itemStockNumber = $this->sanitizeValue($item->StockNumber);
                        $itemUnit = $this->sanitizeValue($item->unit ?? 'pcs');
                        $itemSupplyName = $this->sanitizeValue($item->supply_name . ' (Additional Item)');
                        $itemQuantity = $this->sanitizeNumericValue($item->supply_quantity);
                        
                        $sheet->setCellValueExplicit('A' . $rowIndex, $itemStockNumber, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        $sheet->setCellValueExplicit('B' . $rowIndex, $itemUnit, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        $sheet->setCellValueExplicit('C' . $rowIndex, $itemSupplyName, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        $sheet->setCellValue('D' . $rowIndex, $itemQuantity);
                        $sheet->setCellValue('E' . $rowIndex, '○');
                        $sheet->setCellValue('F' . $rowIndex, '○');
                        $sheet->setCellValueExplicit('G' . $rowIndex, '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        $sheet->setCellValueExplicit('H' . $rowIndex, '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        
                        // Center align the checkboxes and quantity
                        $sheet->getStyle('D' . $rowIndex . ':F' . $rowIndex)
                              ->getAlignment()
                              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        
                        // Apply borders and light background for additional items
                        $sheet->getStyle('A' . $rowIndex . ':H' . $rowIndex)->applyFromArray($headerStyle);
                        $sheet->getStyle('A' . $rowIndex . ':H' . $rowIndex)->getFill()
                              ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                              ->getStartColor()->setRGB('F5F5F5');
                        $rowIndex++;
                    }
                }
            }
            
            // Add requester information for batch
            $user = $firstRequest->user;
            if ($user) {
                $requesterName = $this->sanitizeValue($user->First_Name . ' ' . $user->Last_Name);
                $office = $this->sanitizeValue($user->Office ?? 'N/A');
                
                $sheet->setCellValueExplicit('A' . $rowIndex, 'Requester:', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('B' . $rowIndex, $requesterName, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('D' . $rowIndex, 'Office:', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('E' . $rowIndex, $office, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->getStyle('A' . $rowIndex . ':H' . $rowIndex)->getFont()->setItalic(true);
                $rowIndex++;
            }
            
            // Add spacing between batches
            $rowIndex++;
        }// Add "NOTHING FOLLOWS" marker
        $lastRow = $rowIndex;
        $sheet->mergeCells('A' . $lastRow . ':C' . $lastRow);
        $sheet->setCellValueExplicit('A' . $lastRow, '***NOTHING FOLLOWS***', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->getStyle('A' . $lastRow)->getAlignment()
              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $lastRow . ':H' . $lastRow)->applyFromArray($headerStyle);// Add purpose - use the most common purpose from all batches
        $allPurposes = $groupedRequests->map(function($requests) {
            $firstRequest = $requests->first();
            return $firstRequest->purpose ?: $firstRequest->Purpose ?: 'OFFICE USE';
        });
          $mostCommonPurpose = $allPurposes->mode()[0] ?? 'OFFICE USE';
        
        $sheet->setCellValue('A' . ($lastRow + 3), 'Purpose:');
        $sheet->setCellValueExplicit('B' . ($lastRow + 3), strtoupper($mostCommonPurpose), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->getStyle('B' . ($lastRow + 3))->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);// Add signature blocks with proper spacing and alignment
        $signatureRow = $lastRow + 5;
        
        // Add signature labels with proper spacing
        $sheet->setCellValue('B' . $signatureRow, 'Requested by:');
        $sheet->setCellValue('D' . $signatureRow, 'Approved by:');
        $sheet->setCellValue('F' . $signatureRow, 'Issued by:');
        $sheet->setCellValue('H' . $signatureRow, 'Received by:');

        // Center align the signature headers
        $sheet->getStyle('B' . $signatureRow . ':H' . $signatureRow)
              ->getAlignment()
              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Style for signature lines and text
        $signatureStyle = [
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_THIN
                ]
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ];
        
        $sheet->getStyle('C' . ($signatureRow + 1))->applyFromArray($signatureStyle);
        $sheet->getStyle('D' . ($signatureRow + 1))->applyFromArray($signatureStyle);
        $sheet->getStyle('F' . ($signatureRow + 1))->applyFromArray($signatureStyle);
        $sheet->getStyle('H' . ($signatureRow + 1))->applyFromArray($signatureStyle);
        
        $sheet->setCellValue('A' . ($signatureRow + 1), 'Signature :');
        
        $sheet->setCellValue('A' . ($signatureRow + 2), 'Printed Name :');
        
        // Use dynamic requester name
        $requesterName = $firstUser ? strtoupper($firstUser->First_Name . ' ' . $firstUser->Last_Name) : 'YAN V. MOLINAR';
        
        $sheet->setCellValueExplicit('B' . ($signatureRow + 2), $requesterName, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('D' . ($signatureRow + 2), 'DR. MARISSA B. FERRER', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('F' . ($signatureRow + 2), 'GINA A. DELA CRUZ', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('H' . ($signatureRow + 2), '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING); // Leave blank for manual filling
        
        // Center align and style the printed names
        $sheet->getStyle('B' . ($signatureRow + 2) . ':H' . ($signatureRow + 2))
              ->getAlignment()
              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
              
        $sheet->setCellValue('A' . ($signatureRow + 3), 'Designation :');
        
        // Use dynamic designation based on office - ensure we get the correct requester's designation
        $requesterDesignation = '';
        if ($firstUser) {
            $requesterDesignation = strtoupper($firstUser->Office ?? 'HAP');
            // You can add additional logic here if needed to format the designation
        } else {
            $requesterDesignation = 'HAP';
        }
        
        $sheet->setCellValueExplicit('B' . ($signatureRow + 3), $requesterDesignation, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('D' . ($signatureRow + 3), 'DIRECTOR', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('F' . ($signatureRow + 3), 'PROPERTY CUSTODIAN', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('H' . ($signatureRow + 3), '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING); // Leave blank for manual filling

        // Center align and style the designations
        $sheet->getStyle('B' . ($signatureRow + 3) . ':H' . ($signatureRow + 3))
              ->getAlignment()
              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A' . ($signatureRow + 4), 'Date :');
        
        // Add the exact date of the request
        $requestDate = $firstBatch->first()->created_at ?? now();
        $formattedDate = $requestDate->format('m/d/Y');
        
        $sheet->setCellValueExplicit('B' . ($signatureRow + 4), $formattedDate, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->getStyle('B' . ($signatureRow + 4))
              ->getAlignment()
              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Add borders to date fields
        $sheet->getStyle('B' . ($signatureRow + 4))->applyFromArray($signatureStyle);
        $sheet->getStyle('D' . ($signatureRow + 4))->applyFromArray($signatureStyle);
        $sheet->getStyle('F' . ($signatureRow + 4))->applyFromArray($signatureStyle);
        $sheet->getStyle('H' . ($signatureRow + 4))->applyFromArray($signatureStyle);        // Add AO reference at the bottom
        $sheet->setCellValueExplicit('A' . ($signatureRow + 6), 'AO 6/15/02', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        // Set column widths to match the template
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(8);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(8);
        $sheet->getColumnDimension('F')->setWidth(8);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(20);

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($this->filePath);

        return $this->filePath;
    }    /**
     * Sanitize string values to prevent formula injection
     */
    private function sanitizeValue($value)
    {
        if ($value === null) {
            return '';
        }
        
        $value = (string) $value;
        
        // Remove or escape characters that could be interpreted as formulas
        if (strlen($value) > 0) {
            // Check for formula-like characters at the beginning
            if (in_array($value[0], ['=', '+', '-', '@', '\'', '=', '+', '-'])) {
                $value = "'" . $value; // Add a single quote to prevent Excel treating it as a formula
            }
            
            // Check for strings that might cause CSV injection or special interpretation
            if (substr($value, 0, 1) === '=' || substr($value, 0, 1) === '@') {
                $value = "'" . $value;
            }
            
            // Handle cases where the value starts with special characters like = or +
            if (preg_match('/^[=\+\-@]/', $value)) {
                $value = "'" . $value;
            }
        }
        
        return $value;
    }

    /**
     * Sanitize numeric values
     */
    private function sanitizeNumericValue($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }
        
        // Ensure it's a valid number
        $numericValue = is_numeric($value) ? (float) $value : 0;
        
        return $numericValue;
    }
}