<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PFMO Job Order Form - {{ $jobOrder->job_order_number }}</title>
    <style>
        @page {
            size: A4;
            margin: 0.5in;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #000;
            background: #fff;
        }
        
        .form-container {
            width: 100%;
            border: 2px solid #000;
            background: #fff;
            position: relative;
        }
        
        /* Header styling */
        .header-section {
            padding: 12px;
            border-bottom: 2px solid #000;
            position: relative;
            background: #fff;
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }
        
        .logo-placeholder {
            width: 50px;
            height: 50px;
            border: 1px solid #ccc;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f0f0;
            font-size: 8px;
            color: #666;
        }
        
        .header-title {
            text-align: center;
        }
        
        .header-title h1 {
            font-size: 18px;
            font-weight: bold;
            color: #4472C4;
            letter-spacing: 2px;
        }
        
        .header-title .subtitle {
            font-size: 11px;
            margin: 2px 0;
            color: #000;
        }
        
        .header-title .iso {
            font-size: 9px;
            color: #666;
        }
        
        .form-number-box {
            position: absolute;
            top: 10px;
            right: 10px;
            border: 2px solid #000;
            padding: 6px;
            background: #fff;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            line-height: 1.2;
        }
        
        /* Blue header for form title */
        .form-title {
            background: #4472C4;
            color: #fff;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            border-bottom: 1px solid #000;
        }
        
        /* Main form content */
        .form-content {
            position: relative;
        }
        
        .main-area {
            margin-right: 140px; /* Space for sidebar */
        }
        
        /* Form rows */
        .form-row {
            display: flex;
            border-bottom: 1px solid #000;
            min-height: 25px;
        }
        
        .form-cell {
            padding: 6px 8px;
            display: flex;
            align-items: center;
            border-right: 1px solid #000;
        }
        
        .form-cell:last-child {
            border-right: none;
        }
        
        .form-label {
            background: #E7E6E6;
            font-weight: bold;
            font-size: 10px;
        }
        
        .blue-header {
            background: #4472C4;
            color: #fff;
            font-weight: bold;
            font-size: 10px;
            text-align: center;
            padding: 8px 4px;
        }
        
        /* Checkbox section */
        .checkbox-section {
            padding: 8px;
            border-bottom: 1px solid #000;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 5px;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            font-size: 9px;
            font-weight: bold;
        }
        
        .checkbox {
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            margin-right: 6px;
            display: inline-block;
            position: relative;
            background: #fff;
        }
        
        .checkbox.checked {
            background: #000;
        }
        
        .checkbox.checked::after {
            content: '✓';
            color: #fff;
            position: absolute;
            top: -2px;
            left: 1px;
            font-size: 10px;
            font-weight: bold;
        }
        
        /* Text areas */
        .text-area {
            padding: 8px;
            min-height: 60px;
            border-bottom: 1px solid #000;
            font-size: 11px;
            line-height: 1.4;
        }
        
        /* Three column layout for PFMO section */
        .three-column {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            border-bottom: 1px solid #000;
        }
        
        .three-column .column {
            padding: 8px;
            border-right: 1px solid #000;
            min-height: 80px;
        }
        
        .three-column .column:last-child {
            border-right: none;
        }
        
        /* Right sidebar */
        .right-sidebar {
            position: absolute;
            right: 0;
            top: 0;
            width: 140px;
            border-left: 2px solid #000;
            height: 100%;
        }
        
        .sidebar-item {
            border-bottom: 1px solid #000;
            padding: 12px 6px;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            min-height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
        }
        
        .sidebar-item.gray {
            background: #E7E6E6;
        }
        
        /* Signature section */
        .signature-section {
            display: grid;
            grid-template-columns: 1fr 120px;
            border-bottom: 1px solid #000;
            min-height: 60px;
        }
        
        .signature-area {
            padding: 8px;
            border-right: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .date-area {
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Received by section */
        .received-by-section {
            position: absolute;
            top: 0;
            right: 140px;
            width: 100px;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            padding: 6px;
            background: #E7E6E6;
            border-left: 1px solid #000;
            border-bottom: 1px solid #000;
        }
        
        @media print {
            body { 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact;
            }
            .no-print { display: none; }
        }
        
        .print-controls {
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 1000;
            background: #fff;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .print-btn {
            background: #4472C4;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
            font-size: 12px;
        }
        
        .print-btn:hover {
            background: #365a9b;
        }
    </style>
</head>
<body>
    <!-- Print Controls (hidden when printing) -->
    <div class="print-controls no-print">
        <button class="print-btn" onclick="window.print()">🖨️ Print</button>
        <button class="print-btn" onclick="window.close()">✕ Close</button>
    </div>

    <div class="form-container">
        <!-- Header Section -->
        <div class="header-section">
            <div class="header-content">
                <div class="logo-placeholder">LOGO</div>
                <div class="header-title">
                    <h1>LYCEUM</h1>
                    <div class="subtitle">OF ALABANG INC.</div>
                    <div class="iso">ISO 9001:2015 CERTIFIED</div>
                </div>
                <div class="logo-placeholder">ISO</div>
            </div>
            
            <!-- Form Number Box -->
            <div class="form-number-box">
                LOA-PFMO-JO-009<br>
                Rev. 01
            </div>
        </div>

        <!-- Form Title -->
        <div class="form-title">
            PFMO JOB ORDER FORM
        </div>

        <div class="form-content">
            <!-- Received By Section -->
            <div class="received-by-section">
                RECEIVED BY
            </div>

            <!-- Main Content Area -->
            <div class="main-area">
                <!-- Requestor Information -->
                <div class="form-row">
                    <div class="form-cell form-label" style="flex: 0 0 120px;">REQUESTOR NAME</div>
                    <div class="form-cell" style="flex: 1;">
                        {{ $jobOrder->formRequest->requester->employeeInfo->FirstName ?? '' }} {{ $jobOrder->formRequest->requester->employeeInfo->LastName ?? '' }}
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-cell form-label" style="flex: 0 0 120px;">DEPARTMENT</div>
                    <div class="form-cell" style="flex: 1;">
                        {{ $jobOrder->formRequest->fromDepartment->dept_name ?? $jobOrder->department ?? '' }}
                    </div>
                    <div class="form-cell form-label" style="flex: 0 0 80px;">CONTROL #</div>
                    <div class="form-cell" style="flex: 0 0 120px;">{{ $jobOrder->control_number ?? '' }}</div>
                </div>
                
                <div class="form-row">
                    <div class="form-cell" style="flex: 1;"></div>
                    <div class="form-cell form-label" style="flex: 0 0 100px;">DATE PREPARED</div>
                    <div class="form-cell" style="flex: 0 0 120px;">
                        {{ $jobOrder->date_prepared ? \Carbon\Carbon::parse($jobOrder->date_prepared)->format('m/d/Y') : '' }}
                    </div>
                </div>

                <!-- Service Type Checkboxes -->
                <div class="checkbox-section">
                    <div class="checkbox-item">
                        <span class="checkbox {{ $jobOrder->assistance ? 'checked' : '' }}"></span>
                        <span>ASSISTANCE</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox {{ $jobOrder->repair_repaint ? 'checked' : '' }}"></span>
                        <span>REPAIR/REPAINT</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox {{ $jobOrder->installation ? 'checked' : '' }}"></span>
                        <span>INSTALLATION</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox {{ $jobOrder->cleaning ? 'checked' : '' }}"></span>
                        <span>CLEANING</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox {{ $jobOrder->check_up_inspection ? 'checked' : '' }}"></span>
                        <span>CHECK UP/ INSPECTION</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox {{ $jobOrder->construction_fabrication ? 'checked' : '' }}"></span>
                        <span>CONSTRUCTION/FABRICATION</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox {{ $jobOrder->pull_out_transfer ? 'checked' : '' }}"></span>
                        <span>PULL-OUT / TRANSFER</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox {{ $jobOrder->replacement ? 'checked' : '' }}"></span>
                        <span>REPLACEMENT</span>
                    </div>
                </div>

                <!-- Request Description -->
                <div class="blue-header">
                    REQUEST DESCRIPTION
                </div>
                <div class="text-area">
                    {{ $jobOrder->request_description ?? '' }}
                </div>

                <!-- PFMO Section Header -->
                <div class="blue-header" style="font-size: 11px;">
                    TO BE FILLED OUT BY PFMO PERSONNEL
                </div>
                
                <!-- PFMO Three Column Headers -->
                <div class="three-column">
                    <div class="blue-header">FINDINGS</div>
                    <div class="blue-header">ACTIONS TAKEN</div>
                    <div class="blue-header">DATE RECEIVED<br>RECOMMENDATIONS</div>
                </div>
                
                <!-- PFMO Three Column Content -->
                <div class="three-column">
                    <div class="column">{{ $jobOrder->findings ?? '' }}</div>
                    <div class="column">{{ $jobOrder->actions_taken ?? '' }}</div>
                    <div class="column">
                        @if($jobOrder->date_received)
                            {{ \Carbon\Carbon::parse($jobOrder->date_received)->format('m/d/Y') }}
                        @endif
                        @if($jobOrder->date_received && $jobOrder->recommendations)
                            <br><br>
                        @endif
                        {{ $jobOrder->recommendations ?? '' }}
                    </div>
                </div>

                <!-- Job Completion Section -->
                <div class="form-row">
                    <div class="form-cell blue-header" style="flex: 0 0 150px;">JOB COMPLETED BY</div>
                    <div class="form-cell" style="flex: 1;">{{ $jobOrder->job_completed_by ?? '' }}</div>
                    <div class="form-cell blue-header" style="flex: 0 0 120px;">DATE COMPLETED</div>
                    <div class="form-cell" style="flex: 0 0 120px;">
                        @if($jobOrder->date_completed)
                            {{ \Carbon\Carbon::parse($jobOrder->date_completed)->format('m/d/Y') }}
                        @endif
                    </div>
                </div>

                <!-- After Job Completion Header -->
                <div class="blue-header" style="font-size: 11px;">
                    TO BE FILLED OUT AFTER JOB COMPLETION
                </div>

                <!-- Requestor's Comment Section -->
                <div class="text-area" style="min-height: 40px;">
                    <div style="margin-bottom: 10px;">
                        <strong>REQUESTOR'S COMMENT:</strong>
                        <span class="checkbox {{ $jobOrder->job_completed ? 'checked' : '' }}" style="margin-left: 20px;"></span>
                        <span style="margin-left: 5px;">JOB COMPLETED</span>
                        <span class="checkbox {{ $jobOrder->for_further_action ? 'checked' : '' }}" style="margin-left: 20px;"></span>
                        <span style="margin-left: 5px;">FOR FURTHER ACTION</span>
                    </div>
                    @if($jobOrder->requestor_comments)
                        <div style="margin-top: 10px; padding-top: 8px; border-top: 1px solid #ccc;">
                            {{ $jobOrder->requestor_comments }}
                        </div>
                    @endif
                </div>

                <!-- Signature Section -->
                <div class="form-row">
                    <div class="form-cell blue-header" style="flex: 1;">REQUESTOR'S SIGNATURE</div>
                    <div class="form-cell blue-header" style="flex: 0 0 120px;">DATE</div>
                </div>
                <div class="signature-section">
                    <div class="signature-area">
                        @if($jobOrder->requestor_signature)
                            <div style="text-align: center;">
                                <div style="border-bottom: 1px solid #000; padding: 10px 20px; margin-bottom: 5px;">
                                    {{ $jobOrder->formRequest->requester->employeeInfo->FirstName ?? '' }} {{ $jobOrder->formRequest->requester->employeeInfo->LastName ?? '' }}
                                </div>
                                <small>Digital Signature</small>
                            </div>
                        @endif
                    </div>
                    <div class="date-area">
                        @if($jobOrder->requestor_signature_date)
                            {{ \Carbon\Carbon::parse($jobOrder->requestor_signature_date)->format('m/d/Y') }}
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="right-sidebar">
                <div class="sidebar-item gray">PFMO</div>
                <div class="sidebar-item">ALBERT AYAP</div>
                <div class="sidebar-item">ROS BALTAZAR</div>
                <div class="sidebar-item gray">PRESIDENT'S OFFICE</div>
                <div class="sidebar-item gray">FINANCE</div>
                <div class="sidebar-item gray">WAREHOUSE / PROPERTY</div>
            </div>
        </div>
    </div>
</body>
</html>
