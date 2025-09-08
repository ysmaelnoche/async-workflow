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
            font-size: 10px;
            line-height: 1.2;
            color: black;
        }
        
        .form-container {
            width: 100%;
            border: 2px solid black;
            background: white;
        }
        
        .header {
            background: #4472C4;
            color: white;
            padding: 8px;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            border-bottom: 2px solid black;
        }
        
        .form-number {
            position: absolute;
            top: 10px;
            right: 10px;
            border: 1px solid black;
            padding: 4px;
            background: white;
            color: black;
            font-size: 8px;
            font-weight: bold;
        }
        
        .section {
            border-bottom: 1px solid black;
        }
        
        .row {
            display: flex;
            border-bottom: 1px solid #ccc;
        }
        
        .cell {
            padding: 4px;
            border-right: 1px solid #ccc;
            flex: 1;
        }
        
        .cell:last-child {
            border-right: none;
        }
        
        .label {
            font-weight: bold;
            background: #E7E6E6;
        }
        
        .blue-header {
            background: #4472C4;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 6px;
        }
        
        .checkbox-section {
            display: flex;
            flex-wrap: wrap;
            padding: 8px;
            gap: 15px;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            margin-right: 20px;
        }
        
        .checkbox {
            width: 12px;
            height: 12px;
            border: 1px solid black;
            margin-right: 5px;
            display: inline-block;
            position: relative;
        }
        
        .checked {
            background: black;
        }
        
        .text-area {
            min-height: 40px;
            padding: 4px;
            border: none;
            width: 100%;
        }
        
        .signature-section {
            padding: 8px;
            min-height: 60px;
        }
        
        .three-column {
            display: flex;
        }
        
        .three-column .cell {
            flex: 1;
            text-align: center;
            min-height: 40px;
        }
        
        .right-sidebar {
            position: absolute;
            right: 0;
            top: 60px;
            width: 120px;
            border-left: 2px solid black;
        }
        
        .sidebar-item {
            border-bottom: 1px solid black;
            padding: 8px 4px;
            text-align: center;
            font-size: 8px;
            font-weight: bold;
            min-height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .main-content {
            margin-right: 120px;
        }
        
        @media print {
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <!-- Header with Logo -->
        <div style="position: relative; padding: 10px; border-bottom: 2px solid black;">
            <div style="display: flex; align-items: center; justify-content: center;">
                <div style="margin-right: 20px;">
                    <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiM0NDcyQzQiLz4KPHR1dCB4PSIyMCIgeT0iMjUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IndoaXRlIiBmb250LXNpemU9IjEyIiBmb250LXdlaWdodD0iYm9sZCI+TEE8L3RleHQ+Cjwvc3ZnPgo=" alt="Logo" style="width: 40px; height: 40px;">
                </div>
                <div style="text-align: center;">
                    <h1 style="font-size: 16px; font-weight: bold; color: #4472C4;">LYCEUM</h1>
                    <p style="font-size: 10px; margin: 2px 0;">OF ALABANG INC.</p>
                    <p style="font-size: 8px;">ISO 9001:2015 CERTIFIED</p>
                </div>
                <div style="margin-left: 20px;">
                    <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAiIGhlaWdodD0iMzAiIHZpZXdCb3g9IjAgMCAzMCAzMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjMwIiBoZWlnaHQ9IjMwIiBmaWxsPSIjNDQ3MkM0Ii8+Cjx0ZXh0IHg9IjE1IiB5PSIyMCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0id2hpdGUiIGZvbnQtc2l6ZT0iOCIgZm9udC13ZWlnaHQ9ImJvbGQiPklTTzwvdGV4dD4KPC9zdmc+Cg==" alt="ISO" style="width: 30px; height: 30px;">
                </div>
            </div>
            
            <!-- Form Number Box -->
            <div class="form-number">
                LOA-PFMO-JO-009<br>
                Rev. 01
            </div>
        </div>

        <!-- Title Header -->
        <div class="header">
            PFMO JOB ORDER FORM
        </div>

        <div style="position: relative;">
            <!-- Main Content -->
            <div class="main-content">
                <!-- Requestor Information -->
                <div class="row">
                    <div class="cell label" style="flex: 0 0 120px;">REQUESTOR NAME</div>
                    <div class="cell" style="flex: 1;">{{ $jobOrder->request->requester->employeeInfo->FirstName ?? '' }} {{ $jobOrder->request->requester->employeeInfo->LastName ?? '' }}</div>
                    <div class="cell label" style="flex: 0 0 100px;">RECEIVED BY</div>
                </div>
                
                <div class="row">
                    <div class="cell label" style="flex: 0 0 120px;">DEPARTMENT</div>
                    <div class="cell" style="flex: 1;">{{ $jobOrder->request->requester->department->dept_name ?? '' }}</div>
                    <div class="cell label" style="flex: 0 0 60px;">CONTROL #</div>
                    <div class="cell" style="flex: 0 0 80px;">{{ $jobOrder->job_order_number }}</div>
                </div>
                
                <div class="row">
                    <div class="cell" style="flex: 1;"></div>
                    <div class="cell label" style="flex: 0 0 100px;">DATE PREPARED</div>
                    <div class="cell" style="flex: 0 0 80px;">{{ $jobOrder->created_at->format('m/d/Y') }}</div>
                </div>

                <!-- Service Type Checkboxes -->
                <div class="checkbox-section" style="border-bottom: 1px solid black;">
                    <div class="checkbox-item">
                        <span class="checkbox {{ $jobOrder->requestor_service_types && in_array('Assistance', $jobOrder->requestor_service_types) ? 'checked' : '' }}"></span>
                        <span>ASSISTANCE</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox {{ $jobOrder->requestor_service_types && in_array('Repair/Repaint', $jobOrder->requestor_service_types) ? 'checked' : '' }}"></span>
                        <span>REPAIR/REPAINT</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox {{ $jobOrder->requestor_service_types && in_array('Installation', $jobOrder->requestor_service_types) ? 'checked' : '' }}"></span>
                        <span>INSTALLATION</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox {{ $jobOrder->requestor_service_types && in_array('Cleaning', $jobOrder->requestor_service_types) ? 'checked' : '' }}"></span>
                        <span>CLEANING</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox {{ $jobOrder->requestor_service_types && in_array('Check Up/Inspection', $jobOrder->requestor_service_types) ? 'checked' : '' }}"></span>
                        <span>CHECK UP/ INSPECTION</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox {{ $jobOrder->requestor_service_types && in_array('Construction/Fabrication', $jobOrder->requestor_service_types) ? 'checked' : '' }}"></span>
                        <span>CONSTRUCTION/FABRICATION</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox {{ $jobOrder->requestor_service_types && in_array('Pull Out/Transfer', $jobOrder->requestor_service_types) ? 'checked' : '' }}"></span>
                        <span>PULL-OUT / TRANSFER</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox {{ $jobOrder->requestor_service_types && in_array('Replacement', $jobOrder->requestor_service_types) ? 'checked' : '' }}"></span>
                        <span>REPLACEMENT</span>
                    </div>
                </div>

                <!-- Request Description -->
                <div class="blue-header">
                    REQUEST DESCRIPTION
                </div>
                <div style="padding: 8px; min-height: 60px; border-bottom: 1px solid black;">
                    {{ $jobOrder->request->iomDetails->body ?? $jobOrder->description ?? '' }}
                </div>

                <!-- PFMO Section -->
                <div class="blue-header" style="font-size: 10px;">
                    TO BE FILLED OUT BY PFMO PERSONNEL
                </div>
                
                <div class="three-column" style="border-bottom: 1px solid black;">
                    <div class="cell blue-header" style="font-size: 9px;">FINDINGS</div>
                    <div class="cell blue-header" style="font-size: 9px;">ACTIONS TAKEN</div>
                    <div class="cell blue-header" style="font-size: 9px;">DATE RECEIVED<br>RECOMMENDATIONS</div>
                </div>
                
                <div class="three-column" style="min-height: 80px; border-bottom: 1px solid black;">
                    <div class="cell">{{ $jobOrder->findings ?? '' }}</div>
                    <div class="cell">{{ $jobOrder->actions_taken ?? '' }}</div>
                    <div class="cell">
                        @if($jobOrder->date_received)
                            {{ \Carbon\Carbon::parse($jobOrder->date_received)->format('m/d/Y') }}
                        @endif
                        <br><br>
                        {{ $jobOrder->recommendations ?? '' }}
                    </div>
                </div>

                <!-- Job Completion -->
                <div class="row">
                    <div class="cell blue-header" style="flex: 0 0 150px; font-size: 9px;">JOB COMPLETED BY</div>
                    <div class="cell" style="flex: 1;">{{ $jobOrder->completed_by_name ?? '' }}</div>
                    <div class="cell blue-header" style="flex: 0 0 120px; font-size: 9px;">DATE COMPLETED</div>
                    <div class="cell" style="flex: 0 0 100px;">
                        @if($jobOrder->status === 'Completed' && $jobOrder->updated_at)
                            {{ $jobOrder->updated_at->format('m/d/Y') }}
                        @endif
                    </div>
                </div>

                <!-- After Job Completion -->
                <div class="blue-header" style="font-size: 10px;">
                    TO BE FILLED OUT AFTER JOB COMPLETION
                </div>

                <!-- Requestor's Comment -->
                <div style="padding: 8px; border-bottom: 1px solid black;">
                    <div style="margin-bottom: 10px;">
                        <strong>REQUESTOR'S COMMENT:</strong>
                        <span class="checkbox {{ $jobOrder->requestor_feedback_submitted && $jobOrder->requestor_satisfaction_rating >= 4 ? 'checked' : '' }}" style="margin-left: 20px;"></span>
                        <span style="margin-left: 5px;">JOB COMPLETED</span>
                        <span class="checkbox {{ $jobOrder->requestor_feedback_submitted && $jobOrder->requestor_satisfaction_rating < 4 ? 'checked' : '' }}" style="margin-left: 20px;"></span>
                        <span style="margin-left: 5px;">FOR FURTHER ACTION</span>
                    </div>
                    @if($jobOrder->requestor_feedback_submitted)
                        <div style="min-height: 30px; border-top: 1px solid #ccc; padding-top: 5px;">
                            {{ $jobOrder->requestor_comments ?? '' }}
                        </div>
                    @endif
                </div>

                <!-- Signature Section -->
                <div class="row">
                    <div class="cell blue-header" style="flex: 1; font-size: 9px;">REQUESTOR'S SIGNATURE</div>
                    <div class="cell blue-header" style="flex: 0 0 100px; font-size: 9px;">DATE</div>
                </div>
                <div class="signature-section" style="border-bottom: 1px solid black; display: flex;">
                    <div style="flex: 1; min-height: 50px; display: flex; align-items: center; justify-content: center;">
                        @if($jobOrder->requestor_feedback_submitted)
                            <div style="text-align: center;">
                                <div style="border-bottom: 1px solid black; padding: 10px 20px; margin-bottom: 5px;">
                                    {{ $jobOrder->request->requester->employeeInfo->FirstName ?? '' }} {{ $jobOrder->request->requester->employeeInfo->LastName ?? '' }}
                                </div>
                                <small>Digital Signature</small>
                            </div>
                        @endif
                    </div>
                    <div style="flex: 0 0 100px; border-left: 1px solid black; display: flex; align-items: center; justify-content: center;">
                        @if($jobOrder->requestor_feedback_submitted && $jobOrder->requestor_feedback_date)
                            {{ $jobOrder->requestor_feedback_date->format('m/d/Y') }}
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="right-sidebar">
                <div class="sidebar-item" style="background: #E7E6E6;">PFMO</div>
                <div class="sidebar-item">{{ $jobOrder->assigned_to_name ?? 'ALBERT AYAP' }}</div>
                <div class="sidebar-item">{{ $jobOrder->checked_by_name ?? 'ROS BALTAZAR' }}</div>
                <div class="sidebar-item" style="background: #E7E6E6;">PRESIDENT'S OFFICE</div>
                <div class="sidebar-item" style="background: #E7E6E6;">FINANCE</div>
                <div class="sidebar-item" style="background: #E7E6E6;">WAREHOUSE / PROPERTY</div>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
