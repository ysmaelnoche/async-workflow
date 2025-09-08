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
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.2;
            color: black;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .logo {
            font-weight: bold;
            font-size: 14px;
        }
        
        .form-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin: 10px 0;
        }
        
        .form-row {
            display: flex;
            margin-bottom: 8px;
            align-items: center;
        }
        
        .form-group {
            margin-right: 20px;
        }
        
        .form-group label {
            font-weight: bold;
            margin-right: 5px;
        }
        
        .underline {
            border-bottom: 1px solid #333;
            min-width: 150px;
            display: inline-block;
            padding: 2px 5px;
        }
        
        .checkbox-section {
            margin: 15px 0;
        }
        
        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin: 10px 0;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
        }
        
        .checkbox-item input {
            margin-right: 5px;
        }
        
        .section {
            margin: 20px 0;
            border: 1px solid #333;
            padding: 10px;
        }
        
        .section-title {
            font-weight: bold;
            background-color: #4472C4;
            color: white;
            padding: 5px;
            margin: -10px -10px 10px -10px;
            text-align: center;
        }
        
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .signature-box {
            text-align: center;
            width: 200px;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            width: 100%;
            height: 40px;
            margin-bottom: 5px;
        }
        
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="background: #4472C4; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
            Print Job Order
        </button>
        <button onclick="window.close()" style="background: #666; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            Close
        </button>
    </div>

    <div class="header">
        <div class="logo">LYCEUM OF ALABANG INC.</div>
        <div style="font-size: 8px;">ISO 9001:2015 CERTIFIED</div>
        <div class="form-title">PFMO JOB ORDER FORM</div>
        <div style="position: absolute; right: 0; top: 0; border: 1px solid #333; padding: 5px;">
            <div>{{ $jobOrder->control_number ?? 'LOA-PFMO-JO-009' }}</div>
            <div>Rev. 01</div>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>REQUESTOR NAME:</label>
            <span class="underline">{{ $jobOrder->requestor_name }}</span>
        </div>
        <div class="form-group">
            <label>CONTROL #:</label>
            <span class="underline">{{ $jobOrder->control_number ?? '' }}</span>
        </div>
        <div class="form-group">
            <label>RECEIVED BY:</label>
            <span class="underline">{{ $jobOrder->received_by ?? '' }}</span>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>DEPARTMENT:</label>
            <span class="underline">{{ $jobOrder->department }}</span>
        </div>
        <div class="form-group">
            <label>DATE PREPARED:</label>
            <span class="underline">{{ $jobOrder->date_prepared ? $jobOrder->date_prepared->format('m/d/Y') : '' }}</span>
        </div>
        <div class="form-group">
            <label>PFMO:</label>
            <span class="underline"></span>
        </div>
    </div>

    <div class="checkbox-section">
        <div class="checkbox-grid">
            <div class="checkbox-item">
                <input type="checkbox" {{ $jobOrder->assistance ? 'checked' : '' }}> ASSISTANCE
            </div>
            <div class="checkbox-item">
                <input type="checkbox" {{ $jobOrder->repair_repaint ? 'checked' : '' }}> REPAIR/REPAINT
            </div>
            <div class="checkbox-item">
                <input type="checkbox" {{ $jobOrder->installation ? 'checked' : '' }}> INSTALLATION
            </div>
            <div class="checkbox-item">
                <input type="checkbox" {{ $jobOrder->cleaning ? 'checked' : '' }}> CLEANING
            </div>
            <div class="checkbox-item">
                <input type="checkbox" {{ $jobOrder->check_up_inspection ? 'checked' : '' }}> CHECK UP / INSPECTION
            </div>
            <div class="checkbox-item">
                <input type="checkbox" {{ $jobOrder->construction_fabrication ? 'checked' : '' }}> CONSTRUCTION/FABRICATION
            </div>
            <div class="checkbox-item">
                <input type="checkbox" {{ $jobOrder->pull_out_transfer ? 'checked' : '' }}> PULL-OUT / TRANSFER
            </div>
            <div class="checkbox-item">
                <input type="checkbox" {{ $jobOrder->replacement ? 'checked' : '' }}> REPLACEMENT
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">REQUEST DESCRIPTION</div>
        <div style="min-height: 60px; padding: 10px;">
            {{ $jobOrder->request_description }}
        </div>
    </div>

    <div class="section">
        <div class="section-title">TO BE FILLED OUT BY PFMO PERSONNEL</div>
        <div style="margin: 10px 0;">
            <strong>FINDINGS:</strong>
            <div style="min-height: 40px; border-bottom: 1px solid #333; margin: 5px 0; padding: 5px;">
                {{ $jobOrder->findings ?? '' }}
            </div>
        </div>
        <div style="margin: 10px 0;">
            <strong>ACTIONS TAKEN:</strong>
            <div style="min-height: 40px; border-bottom: 1px solid #333; margin: 5px 0; padding: 5px;">
                {{ $jobOrder->actions_taken ?? '' }}
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>DATE RECEIVED:</label>
                <span class="underline">{{ $jobOrder->date_received ? $jobOrder->date_received->format('m/d/Y') : '' }}</span>
            </div>
            <div class="form-group">
                <label>RECOMMENDATIONS:</label>
                <span class="underline">{{ $jobOrder->recommendations ?? '' }}</span>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">JOB COMPLETED BY</div>
        <div class="form-row">
            <div class="form-group">
                <span class="underline" style="width: 300px;">{{ $jobOrder->job_completed_by ?? '' }}</span>
            </div>
            <div class="form-group">
                <label>DATE COMPLETED:</label>
                <span class="underline">{{ $jobOrder->date_completed ? $jobOrder->date_completed->format('m/d/Y') : '' }}</span>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">TO BE FILLED OUT AFTER JOB COMPLETION</div>
        <div style="margin: 15px 0;">
            <strong>REQUESTOR'S COMMENT:</strong>
            <div class="checkbox-grid" style="grid-template-columns: 1fr 1fr; margin: 10px 0;">
                <div class="checkbox-item">
                    <input type="checkbox" {{ $jobOrder->job_completed ? 'checked' : '' }}> JOB COMPLETED
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" {{ $jobOrder->for_further_action ? 'checked' : '' }}> FOR FURTHER ACTION
                </div>
            </div>
            <div style="min-height: 40px; border: 1px solid #333; margin: 10px 0; padding: 5px;">
                {{ $jobOrder->requestor_comments ?? '' }}
            </div>
        </div>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div><strong>REQUESTOR'S SIGNATURE</strong></div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div><strong>DATE</strong></div>
        </div>
    </div>

    <div style="text-align: right; margin-top: 20px; font-size: 8px;">
        Job Order Number: {{ $jobOrder->job_order_number }}<br>
        Generated: {{ now()->format('M j, Y g:i A') }}
    </div>
</body>
</html>
