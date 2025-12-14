<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <title>Requisition and Issue Slip</title>
    <style>
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }
        .container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header-info {
            text-align: right;
            margin-bottom: 10px;
        }
        .main-title {
            text-align: center;
            font-weight: bold;
            margin: 20px 0;
            font-size: 16px;
        }
        .entity-info {
            margin-bottom: 20px;
        }
        .entity-info div {
            margin-bottom: 5px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .info-col {
            flex: 1;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        .batch-header {
            background-color: #e3f2fd;
            font-weight: bold;
            text-align: center;
        }
        .additional-item {
            background-color: #f5f5f5;
        }
        .nothing-follows {
            text-align: center;
            font-weight: bold;
        }
        .purpose {
            margin-top: 20px;
            margin-bottom: 30px;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        .signature-box {
            flex: 1;
            text-align: center;
            margin: 0 10px;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            margin: 30px 0 5px;
        }
        /* Signature boxes - using DIVs with borders - TRIPLE BORDER DECLARATION */
        .signature-name-box,
        .signature-position-box {
            width: 100% !important;
            border: 2px solid #000 !important;
            border-top: 2px solid #000 !important;
            border-right: 2px solid #000 !important;
            border-bottom: 2px solid #000 !important;
            border-left: 2px solid #000 !important;
            outline: 1px solid #000 !important;
            padding: 8px !important;
            text-align: center !important;
            font-weight: normal !important;
            margin: 5px 0 !important;
            background-color: white !important;
            font-size: 12px !important;
            box-sizing: border-box !important;
            min-height: 35px !important;
            display: block !important;
        }
        .ao-reference {
            margin-top: 20px;
            font-size: 10px;
        }
        .checkbox {
            display: inline-block;
            width: 15px;
            height: 15px;
            border: 1px solid #000;
            border-radius: 50%;
            text-align: center;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
                font-size: 11px;
            }
            .container {
                margin: 0;
                padding: 10px;
                border: none;
                box-shadow: none;
            }
            .no-print {
                display: none;
            }
            /* GUARANTEE borders print - inline styles are strongest */
            table td {
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>
    @php
        // Helper function to determine if we're working with a collection or single item
        $isCollection = is_array($requests) || $requests instanceof \Illuminate\Support\Collection;
        
        // Get the primary request (first one if collection)
        $primaryRequest = $isCollection ? $requests[0] : $requests;
        
        // Count items
        $totalItems = $isCollection ? count($requests) : 1;
        
        // Count additional items
        $additionalItemsCount = 0;
        if ($isCollection) {
            foreach ($requests as $req) {
                $additionalItemsCount += $req->items ? $req->items->count() : 0;
            }
        } else {
            $additionalItemsCount = $requests->items ? $requests->items->count() : 0;
        }
        
        // Get purpose from primary request
        $displayPurpose = $primaryRequest->purpose ?? $primaryRequest->Purpose ?? 'OFFICE USE';
    @endphp

    <div class="container">
        <div class="header-info">
            <div>PUP-RISL-6-PSMO-010</div>
            <div>Rev. 2</div>
            <div>Effectivity Date: {{ now()->addDays(5)->format('F d, Y') }}</div>
            <div style="margin-top: 10px;">Appendix 63</div>
        </div>
        
        <div class="main-title">REQUISITION AND ISSUE SLIP</div>
        
        <div class="entity-info">
            <div class="info-row">
                <div class="info-col">
                    <span class="info-label">Office:</span> 
                    <span>{{ strtoupper($primaryRequest->user->Office ?? 'HEAD OF ACADEMIC') }}</span>
                </div>
                <div class="info-col">
                    <span>RIS No.: _____________________________________</span>
                </div>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th colspan="4">Requisition</th>
                    <th colspan="2">Stock Available?</th>
                    <th colspan="2">Issue</th>
                </tr>
                <tr>
                    <th>Stock No.</th>
                    <th>Unit</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Yes</th>
                    <th>No</th>
                    <th>Quantity</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @if($totalItems > 1 || $additionalItemsCount > 0)
                    <tr class="batch-header">
                        <td colspan="8">
                            BATCH REQUEST - {{ strtoupper($displayPurpose) }} 
                            (Total Items: {{ $totalItems + $additionalItemsCount }})
                        </td>
                    </tr>
                @endif
                
                @if($isCollection)
                    @foreach($requests as $request)
                        <tr>
                            <td>{{ $request->StockNumber }}</td>
                            <td>{{ $request->Unit ?? 'pcs' }}</td>
                            <td>{{ $request->supply_name }}</td>
                            <td>{{ $request->supply_quantity }}</td>
                            <td>{{ $request->stock_available === 'yes' ? '⊗' : '○' }}</td>
                            <td>{{ $request->stock_available === 'no' ? '⊗' : '○' }}</td>
                            <td>{{ $request->issue_quantity ?? $request->supply_quantity }}</td>
                            <td>{{ $request->remarks ?? '' }}</td>
                        </tr>
                        
                        @if($request->items && $request->items->count() > 0)
                            @foreach($request->items as $item)
                                <tr class="additional-item">
                                    <td>{{ $item->StockNumber }}</td>
                                    <td>{{ $item->unit ?? 'pcs' }}</td>
                                    <td>{{ $item->supply_name }} (Additional Item)</td>
                                    <td>{{ $item->supply_quantity }}</td>
                                    <td>○</td>
                                    <td>○</td>
                                    <td>{{ $item->supply_quantity }}</td>
                                    <td></td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                @else
                    <tr>
                        <td>{{ $requests->StockNumber }}</td>
                        <td>{{ $requests->Unit ?? 'pcs' }}</td>
                        <td>{{ $requests->supply_name }}</td>
                        <td>{{ $requests->supply_quantity }}</td>
                        <td>{{ $requests->stock_available === 'yes' ? '⊗' : '○' }}</td>
                        <td>{{ $requests->stock_available === 'no' ? '⊗' : '○' }}</td>
                        <td>{{ $requests->issue_quantity ?? $requests->supply_quantity }}</td>
                        <td>{{ $requests->remarks ?? '' }}</td>
                    </tr>
                    
                    @if($requests->items && $requests->items->count() > 0)
                        @foreach($requests->items as $item)
                            <tr class="additional-item">
                                <td>{{ $item->StockNumber }}</td>
                                <td>{{ $item->unit ?? 'pcs' }}</td>
                                <td>{{ $item->supply_name }} (Additional Item)</td>
                                <td>{{ $item->supply_quantity }}</td>
                                <td></td>
                                <td></td>
                                <td>{{ $item->supply_quantity }}</td>
                                <td></td>
                            </tr>
                        @endforeach
                    @endif
                @endif
                
                <tr>
                    <td colspan="8" class="nothing-follows">***NOTHING FOLLOWS***</td>
                </tr>
            </tbody>
        </table>
        
        <div class="purpose">
            <span class="info-label">Purpose:</span>
            <span>{{ strtoupper($displayPurpose) }}</span>
        </div>
        
        @php
            $requesterName = $primaryRequest->user 
                ? strtoupper($primaryRequest->user->First_Name . ' ' . $primaryRequest->user->Last_Name) 
                : 'DR. RHYAN V. MOLINAR';
            $requesterDesignation = $primaryRequest->user 
                ? strtoupper($primaryRequest->user->Office ?? 'N/A') 
                : 'HAP';
        @endphp
        
        <!-- SIGNATURE TABLE - Using TR/TD for guaranteed PDF rendering -->
        <table style="width: 100%; margin-top: 30px; border-collapse: collapse; border-top: 2px solid #000;">
            <tr>
                <td style="width: 25%; text-align: center; vertical-align: top; padding: 10px; border-right: 2px solid #000;">
                    <div style="margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #000; font-weight: normal;">Requested by:</div>
                    <div style="border: 2px solid #000; border-radius: 5px; padding: 12px; margin-bottom: 3px; margin-top: 10px; background: white; min-height: 40px;">{{ $requesterName }}</div>
                    <div style="border-bottom: 1px solid #000; margin: 8px 0;"></div>
                    <div style="border: 2px solid #000; border-radius: 5px; padding: 12px; margin-top: 3px; background: white; min-height: 40px;">{{ $requesterDesignation }}</div>
                </td>
                <td style="width: 25%; text-align: center; vertical-align: top; padding: 10px; border-right: 2px solid #000;">
                    <div style="margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #000; font-weight: normal;">Approved by:</div>
                    <div style="border: 2px solid #000; border-radius: 5px; padding: 12px; margin-bottom: 3px; margin-top: 10px; background: white; min-height: 40px;">DR. MARISSA B. FERRER</div>
                    <div style="border-bottom: 1px solid #000; margin: 8px 0;"></div>
                    <div style="border: 2px solid #000; border-radius: 5px; padding: 12px; margin-top: 3px; background: white; min-height: 40px;">DIRECTOR</div>
                </td>
                <td style="width: 25%; text-align: center; vertical-align: top; padding: 10px; border-right: 2px solid #000;">
                    <div style="margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #000; font-weight: normal;">Issued by:</div>
                    <div style="border: 2px solid #000; border-radius: 5px; padding: 12px; margin-bottom: 3px; margin-top: 10px; background: white; min-height: 40px;">GINA A. DELA CRUZ</div>
                    <div style="border-bottom: 1px solid #000; margin: 8px 0;"></div>
                    <div style="border: 2px solid #000; border-radius: 5px; padding: 12px; margin-top: 3px; background: white; min-height: 40px;">PROPERTY CUSTODIAN</div>
                </td>
                <td style="width: 25%; text-align: center; vertical-align: top; padding: 10px;">
                    <div style="margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #000; font-weight: normal;">Received by:</div>
                    <div style="border: 2px solid #000; border-radius: 5px; padding: 12px; margin-bottom: 3px; margin-top: 10px; background: white; min-height: 40px;">{{ $primaryRequest->received_by ?? '' }}</div>
                    <div style="border-bottom: 1px solid #000; margin: 8px 0;"></div>
                    <div style="border: 2px solid #000; border-radius: 5px; padding: 12px; margin-top: 3px; background: white; min-height: 40px;">{{ $primaryRequest->received_position ?? '' }}</div>
                </td>
            </tr>
        </table>
        
        <div class="ao-reference">AO 6/15/02</div>
    </div>

    <div class="no-print">
        <div style="text-align: center; margin-top: 20px;">
            <button onclick="window.print()" style="padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Print Document
            </button>
            <button onclick="window.close()" style="padding: 10px 20px; margin-left: 10px; background: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Close
            </button>
        </div>
    </div>

    @if(isset($autoPrint) && $autoPrint)
    <script>
        // Auto-print when page loads if autoprint parameter is set
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500); // Short delay to ensure the page is fully rendered
        }
    </script>
    @endif
</body>
</html>
