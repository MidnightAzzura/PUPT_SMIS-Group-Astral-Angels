@extends('layout.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/editris.css') }}">
<script src="{{ asset('js/EditRIs.js') }}" defer></script>

<style>
/* Simple radio buttons - default browser styling */
.stock-available-radio {
    width: 16px;
    height: 16px;
    margin: 0 auto;
    display: block;
}

</style>

<div class="container mt-4">    <div class="header-actions">
        <h2>Edit RIS File - Batch Request</h2>        <button type="button" class="btn btn-info print-btn highlight">
            <i class="fas fa-print"></i> Print/Save RIS
            <span class="print-required-tag">REQUIRED</span>
        </button>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        // If we have multiple requests, use the first one for batch info
        $primaryRequest = is_array($requests) ? $requests[0] : $requests;
        
        // Collect all items from all requests
        $allItems = collect();
        
        if (is_array($requests)) {
            foreach ($requests as $request) {
                // Add the main request item
                $allItems->push([
                    'stockNumber' => $request->StockNumber,
                    'unit' => $request->Unit,
                    'description' => $request->supply_name,
                    'quantity' => $request->supply_quantity,
                    'requestId' => $request->id
                ]);
                
                // Add additional items if they exist
                if ($request->items && $request->items->count() > 0) {
                    foreach ($request->items as $item) {
                        $allItems->push([
                            'stockNumber' => $item->StockNumber,
                            'unit' => $item->unit ?? 'N/A',
                            'description' => $item->supply_name,
                            'quantity' => $item->supply_quantity,
                            'requestId' => $request->id,
                            'isAdditional' => true
                        ]);
                    }
                }
            }
        } else {
            // Single request handling
            $allItems->push([
                'stockNumber' => $requests->StockNumber,
                'unit' => $requests->Unit,
                'description' => $requests->supply_name,
                'quantity' => $requests->supply_quantity,
                'requestId' => $requests->id
            ]);
            
            if ($requests->items && $requests->items->count() > 0) {
                foreach ($requests->items as $item) {
                    $allItems->push([
                        'stockNumber' => $item->StockNumber,
                        'unit' => $item->unit ?? 'N/A',
                        'description' => $item->supply_name,
                        'quantity' => $item->supply_quantity,
                        'requestId' => $requests->id,
                        'isAdditional' => true
                    ]);
                }
            }
        }
    @endphp

    <!-- Request Statistics Dashboard -->
    <div class="stats-dashboard">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-boxes"></i></div>
            <div class="stat-content">
                <h3>{{ $allItems->count() }}</h3>
                <p>Total Items</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-user-check"></i></div>
            <div class="stat-content">
                <h3>{{ is_array($requests) ? count($requests) : 1 }}</h3>
                <p>Requests</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="stat-content">
                <h3>{{ now()->format('M d, Y') }}</h3>
                <p>Processing Date</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-content">
                <h3 id="processingTime">0:00</h3>
                <p>Processing Time</p>
            </div>
        </div>
    </div>

    <form action="{{ route('requests.updateRIS', ['stockNumber' => $primaryRequest->StockNumber]) }}" method="POST" class="mt-4" id="risForm" onsubmit="return validateSubmission();">
        @csrf
        <div class="card ris-card">
            <div class="card-header">
                <div class="ris-header">
                    <p>PUP-RISL-6-PSMO-010</p>
                    <p>Rev. 2</p>
                    <p>Effectivity Date: {{ now()->addDays(5)->format('F d, Y') }}</p>
                    <p>Appendix 63</p>
                </div>
            </div>
            <div class="card-body">
                <h3 class="text-center mb-4">REQUISITION AND ISSUE SLIP</h3>

                <div class="table-responsive">
                    <table class="table table-bordered ris-table">
                        <thead>
                            <tr class="table-primary">
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
                            @foreach($allItems as $index => $item)
                            <tr class="{{ isset($item['isAdditional']) ? 'additional-item' : '' }}">
                                <td>{{ $item['stockNumber'] }}</td>
                                <td>{{ $item['unit'] }}</td>
                                <td>
                                    {{ $item['description'] }}
                                    @if(isset($item['isAdditional']))
                                        <span class="badge bg-secondary">Additional</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="quantity-container">
                                        <input type="number" 
                                            name="quantities[{{ $index }}]" 
                                            id="quantity_{{ $index }}"
                                            class="form-control quantity-input" 
                                            value="{{ $item['quantity'] }}" 
                                            min="1" 
                                            max="{{ $item['max_stock'] ?? 1000 }}"
                                            required
                                            data-max-stock="{{ $item['max_stock'] ?? 1000 }}"
                                            onchange="validateQuantity(this)">
                                        <!-- Max stock indicator -->
                                        @if(isset($item['max_stock']))
                                        <small class="form-text text-muted max-stock-indicator">
                                            Max: <span class="max-value">{{ $item['max_stock'] }}</span>
                                        </small>
                                        @endif
                                        <input type="hidden" name="stock_numbers[{{ $index }}]" value="{{ $item['stockNumber'] }}">
                                        <input type="hidden" name="request_ids[{{ $index }}]" value="{{ $item['requestId'] }}">
                                        <input type="hidden" name="max_stocks[{{ $index }}]" value="{{ $item['max_stock'] ?? 1000 }}">
                                    </div>
                                </td>
                                <td class="text-center"><input type="radio" name="stock_available[{{ $index }}]" value="yes" class="stock-available-radio" {{ old('stock_available.' . $index) == 'yes' ? 'checked' : '' }} required></td>
                                <td class="text-center"><input type="radio" name="stock_available[{{ $index }}]" value="no" class="stock-available-radio" {{ old('stock_available.' . $index) == 'no' ? 'checked' : '' }}></td>
                                <td>
                                    <div class="quantity-container">
                                        <input type="number" 
                                            name="issue_quantities[{{ $index }}]" 
                                            id="issue_quantity_{{ $index }}"
                                            class="form-control issue-input" 
                                            value="{{ old('issue_quantities.' . $index) ? old('issue_quantities.' . $index) : 0 }}" 
                                            min="0"
                                            max="{{ $item['max_stock'] ?? 1000 }}"
                                            required
                                            data-max-stock="{{ $item['max_stock'] ?? 1000 }}"
                                            data-is-issue="true"
                                            data-index="{{ $index }}"
                                            onchange="validateQuantity(this)">
                                        <small class="quantity-feedback"></small>
                                    </div>
                                </td>
                                <td><input type="text" name="remarks[{{ $index }}]" id="remarks_{{ $index }}" class="form-control remarks-input" value="{{ old('remarks.' . $index) }}"></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row mt-2">
                    <div class="col-12">
                        <div class="purpose-container">
                            <label for="purpose">Purpose:</label>
                            <input type="text" id="purpose" name="purpose" class="form-control" value="{{ old('purpose', $primaryRequest->Purpose) }}" readonly tabindex="-1" onfocus="this.blur();">
                        </div>
                    </div>
                </div>

                <!-- SIGNATURE TABLE - Using TR/TD for guaranteed rendering -->
                <table style="width: 100%; margin-top: 30px; border-collapse: collapse; border-top: 2px solid #000;">
                    <tr>
                        <td style="width: 25%; text-align: center; vertical-align: top; padding: 10px; border-right: 2px solid #000;">
                            <p style="margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #000; font-weight: normal;">Requested by:</p>
                            <input type="text" name="requested_by" readonly value="{{ strtoupper($primaryRequest->user->First_Name ?? '') }} {{ strtoupper($primaryRequest->user->Last_Name ?? '') }}" tabindex="-1" style="width: 100%; border: 2px solid #000; border-radius: 5px; text-align: center; font-weight: normal; padding: 12px; margin-bottom: 3px; margin-top: 10px; background: white;">
                            <div style="border-bottom: 1px solid #000; margin: 8px 0;"></div>
                            <input type="text" name="requested_position" readonly value="{{ strtoupper($primaryRequest->user->Office ?? 'N/A') }}" tabindex="-1" style="width: 100%; border: 2px solid #000; border-radius: 5px; text-align: center; font-weight: normal; padding: 12px; margin-top: 3px; background: white;">
                        </td>
                        <td style="width: 25%; text-align: center; vertical-align: top; padding: 10px; border-right: 2px solid #000;">
                            <p style="margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #000; font-weight: normal;">Approved by:</p>
                            <input type="text" name="approved_by" readonly value="DR. MARISSA B. FERRER" tabindex="-1" style="width: 100%; border: 2px solid #000; border-radius: 5px; text-align: center; font-weight: normal; padding: 12px; margin-bottom: 3px; margin-top: 10px; background: white;">
                            <div style="border-bottom: 1px solid #000; margin: 8px 0;"></div>
                            <input type="text" name="approved_position" readonly value="DIRECTOR" tabindex="-1" style="width: 100%; border: 2px solid #000; border-radius: 5px; text-align: center; font-weight: normal; padding: 12px; margin-top: 3px; background: white;">
                        </td>
                        <td style="width: 25%; text-align: center; vertical-align: top; padding: 10px; border-right: 2px solid #000;">
                            <p style="margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #000; font-weight: normal;">Issued by:</p>
                            <input type="text" name="issued_by" readonly value="GINA A. DELA CRUZ" tabindex="-1" style="width: 100%; border: 2px solid #000; border-radius: 5px; text-align: center; font-weight: normal; padding: 12px; margin-bottom: 3px; margin-top: 10px; background: white;">
                            <div style="border-bottom: 1px solid #000; margin: 8px 0;"></div>
                            <input type="text" name="issued_position" readonly value="PROPERTY CUSTODIAN" tabindex="-1" style="width: 100%; border: 2px solid #000; border-radius: 5px; text-align: center; font-weight: normal; padding: 12px; margin-top: 3px; background: white;">
                        </td>
                        <td style="width: 25%; text-align: center; vertical-align: top; padding: 10px;">
                            <p style="margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #000; font-weight: normal;">Received by:</p>
                            <input type="text" name="received_by" required value="{{ old('received_by') }}" style="width: 100%; border: 2px solid #000; border-radius: 5px; text-align: center; font-weight: normal; padding: 12px; margin-bottom: 3px; margin-top: 10px; background: white;">
                            <div style="border-bottom: 1px solid #000; margin: 8px 0;"></div>
                            <input type="text" name="received_position" placeholder="Position" required value="{{ old('received_position') }}" style="width: 100%; border: 2px solid #000; border-radius: 5px; text-align: center; font-weight: normal; padding: 12px; margin-top: 3px; background: white;">
                        </td>
                    </tr>
                </table>
            </div>        </div>        <div class="text-center mt-2 form-actions">            <!-- Debug info for testing -->
            <div class="alert alert-info" style="font-size: 0.8rem; margin-bottom: 5px;">
                <strong>Debug Info:</strong> Print Status: <span id="printStatus">Not Printed</span> | 
                <button type="button" class="btn btn-sm btn-warning" onclick="debugPrintStatus()">Check Status</button>
            </div>
            
            <button type="submit" class="btn btn-secondary" id="saveApproveBtn" disabled>
                <i class="fas fa-save"></i> Save & Approve Batch
            </button>
            <a href="{{ route('requests.pending') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>
@endsection
