<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; // Added
use App\Models\FormRequest; // Import the FormRequest model
use App\Models\Department;
use App\Models\EmployeeInfo;
use App\Models\User;
use App\Models\ApproverPermission; // Added for approver permissions
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth; // Import Auth for potential direct checks if needed
use Illuminate\Support\Facades\DB; // Added for transactions
use Carbon\Carbon; // Import Carbon for date manipulation

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function dashboard(Request $request)
    {
        $tabs = [
            'all_requests' => 'All Requests',
            'pending' => 'Pending',
            'approved' => 'Approved', // Changed from 'completed' to 'approved' for consistency
            'rejected' => 'Rejected',
            // Add other admin-specific tabs if needed
        ];

        $activeTab = $request->query('tab', array_key_first($tabs));
        if (!array_key_exists($activeTab, $tabs)) {
            $activeTab = array_key_first($tabs);
        }

        $allRequestsQuery = FormRequest::with([
            'requester.employeeInfo',
            'requester.department',
            'iomDetails',     // Ensure iomDetails is eager loaded
            'leaveDetails',   // Ensure leaveDetails is eager loaded
            'approvals' // Eager load approvals for processing time and status
        ]);

        // --- Statistics Calculations ---
        $now = Carbon::now();

        // Monthly Count (for the current month)
        $monthlyCount = (clone $allRequestsQuery)->whereYear('date_submitted', $now->year)
            ->whereMonth('date_submitted', $now->month)
            ->count();
        // Yearly Count (for the current year)
        $yearlyCount = (clone $allRequestsQuery)->whereYear('date_submitted', $now->year)
            ->count();

        // All requests for the current year for further stats
        $requestsThisYear = (clone $allRequestsQuery)->whereYear('date_submitted', $now->year)->get();

        // Average Processing Time (for completed/rejected requests this year)
        $completedOrRejectedRequests = $requestsThisYear->filter(function ($req) {
            return in_array(strtolower($req->status), ['approved', 'rejected']) && $req->date_submitted && $req->approvals->isNotEmpty();
        });

        $totalProcessingDays = 0;
        $processedCount = 0;
        foreach ($completedOrRejectedRequests as $req) {
            $submittedDate = Carbon::parse($req->date_submitted);
            // Find the latest approval/rejection date
            $finalActionDate = null;
            foreach ($req->approvals as $approval) {
                if (in_array(strtolower($approval->action), ['approved', 'rejected'])) {
                    $actionDate = Carbon::parse($approval->action_date);
                    if ($finalActionDate === null || $actionDate->gt($finalActionDate)) {
                        $finalActionDate = $actionDate;
                    }
                }
            }
            if ($finalActionDate) {
                $totalProcessingDays += $submittedDate->diffInDays($finalActionDate);
                $processedCount++;
            }
        }
        $avgProcessingTime = $processedCount > 0 ? round($totalProcessingDays / $processedCount, 1) . ' days' : 'N/A';


        // Approval Rate (for completed requests this year)
        $totalDecidedRequests = $requestsThisYear->whereIn('status', ['Approved', 'Rejected'])->count();
        $approvedRequestsCount = $requestsThisYear->where('status', 'Approved')->count();
        $approvalRate = $totalDecidedRequests > 0 ? round(($approvedRequestsCount / $totalDecidedRequests) * 100, 1) : 0;        // --- Tab Counts ---
        $counts = [];
        $allDbRequestsForCounts = (clone $allRequestsQuery)->get(); // Get all for counts only

        $counts['all_requests'] = $allDbRequestsForCounts->count();
        $counts['pending'] = $allDbRequestsForCounts->whereIn('status', ['Pending', 'In Progress', 'Pending Department Head Approval', 'Pending Target Department Approval', 'Under Sub-Department Evaluation', 'Awaiting PFMO Decision'])->count(); // Sum of all pending-like statuses
        $counts['approved'] = $allDbRequestsForCounts->where('status', 'Approved')->count();
        $counts['rejected'] = $allDbRequestsForCounts->where('status', 'Rejected')->count();        // Apply filters based on active tab (similar to staff dashboard)
        $filteredQuery = match ($activeTab) {
            'pending' => (clone $allRequestsQuery)->whereIn('status', [
                'Pending',
                'In Progress',
                'Pending Department Head Approval',
                'Pending Target Department Approval',
                'Under Sub-Department Evaluation',
                'Awaiting PFMO Decision'
            ]),
            'approved' => (clone $allRequestsQuery)->where('status', 'Approved'),
            'rejected' => (clone $allRequestsQuery)->where('status', 'Rejected'),
            default => clone $allRequestsQuery,
        };

        // Get paginated requests for the table
        $requestsForTable = $filteredQuery->orderBy('date_submitted', 'desc')->paginate(10)->withQueryString();        //         break;
        // }


        return view('admin.dashboard', [
            'requests' => $requestsForTable, // Use this for the table
            'tabs' => $tabs,
            'activeTab' => $activeTab,
            'monthlyCount' => $monthlyCount,
            'yearlyCount' => $yearlyCount,
            'avgProcessingTime' => $avgProcessingTime,
            'approvalRate' => $approvalRate,
            'counts' => $counts, // Pass tab counts
        ]);
    }

    /**
     * Display the specified resource for tracking by admin.
     *
     * @param  string  $formId
     * @return \Illuminate\View\View
     */
    public function showRequestTrack($formId)
    {
        $formRequest = FormRequest::with([
            'requester.employeeInfo', // For requester's name
            'requester.department',   // For requester's department
            'fromDepartment',         // Department submitting the request (if different from requester's, or for context)
            'toDepartment',           // Target department for IOMs
            'iomDetails',
            'leaveDetails',
            'approvals.approver.employeeInfo', // For approver names in timeline
            'approvals.approver.signatureStyle',      // Corrected: For displaying signatures if available, via approver
            'currentApprover.employeeInfo'   // For displaying current approver's name
        ])->findOrFail($formId);

        // Admin should be able to view any request, so no specific authorization check here beyond the 'admin' middleware on the route.

        return view('admin.requests.track', compact('formRequest'));
    }

    /**
     * Show the employee import form (admin only).
     */
    public function showEmployeeImportForm()
    {
        return view('admin.employee-import');
    }

    /**
     * Handle the Excel import for employees and accounts (admin only).
     */
    public function importEmployeesFromExcel(Request $request)
    {
        \Log::info('ETL Import - Method called'); // Debug: method entry
        $request->validate([
            'employee_excel' => 'required|file|mimes:xlsx,xls',
        ]);
        \Log::info('ETL Import - File validated', ['file' => $request->file('employee_excel') ? $request->file('employee_excel')->getClientOriginalName() : null]);
        $path = $request->file('employee_excel')->getRealPath();
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            \Log::info('ETL Import - Spreadsheet loaded');
        } catch (\Exception $e) {
            \Log::error('ETL Import - Spreadsheet load failed', ['error' => $e->getMessage()]);
            throw $e;
        }
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        $header = array_map('trim', $rows[0]);
<<<<<<< HEAD

=======
        
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
        // Handle potential missing headers
        for ($i = 0; $i < count($header); $i++) {
            if (empty($header[$i])) {
                $header[$i] = "col_" . $i; // Name empty columns as col_0, col_1, etc.
            }
        }
<<<<<<< HEAD

=======
        
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
        $dataRows = array_slice($rows, 1);

        // Debug: Log the first 3 rows to storage/logs/laravel.log
        \Log::info('ETL Import - Header', $header);
        \Log::info('ETL Import - Total data rows: ' . count($dataRows));
        foreach (array_slice($dataRows, 0, 5) as $index => $row) {
            \Log::info("ETL Import - Sample Row {$index}", $row);
        }

        $newEmployeesCount = 0;
        $skippedEmployeesCount = 0;
        $totalRowsProcessed = 0;

        foreach ($dataRows as $rowIndex => $row) {
            $totalRowsProcessed++;
<<<<<<< HEAD

=======
            
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
            // Skip empty rows completely
            if (empty(array_filter($row))) {
                \Log::info("ETL Import - Skipping completely empty row {$rowIndex}");
                $totalRowsProcessed--; // Don't count empty rows
                continue;
            }
<<<<<<< HEAD

            $row = array_combine($header, $row);

=======
            
            $row = array_combine($header, $row);
            
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
            // Log each row for debugging
            \Log::info("ETL Import - Processing Row {$rowIndex}", [
                'Emp_No' => $row['Emp_No'] ?? 'MISSING',
                'dept_name' => $row['dept_name'] ?? 'MISSING',
                'FirstName' => $row['FirstName'] ?? 'MISSING',
                'LastName' => $row['LastName'] ?? 'MISSING'
            ]);
<<<<<<< HEAD

=======
            
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
            if (empty($row['Emp_No']) || empty($row['dept_name'])) {
                \Log::warning("ETL Import - Skipping row {$rowIndex} - Missing Emp_No or dept_name", [
                    'Emp_No' => $row['Emp_No'] ?? 'NULL',
                    'dept_name' => $row['dept_name'] ?? 'NULL'
                ]);
                continue;
            }

            // Find department by dept_name (case-insensitive and trimmed)
            $deptName = trim($row['dept_name']);
            $department = Department::whereRaw('LOWER(TRIM(dept_name)) = ?', [strtolower($deptName)])->first();
<<<<<<< HEAD

=======
            
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
            if (!$department) {
                // Try alternative matching - by dept_code if provided
                $department = Department::whereRaw('LOWER(TRIM(dept_code)) = ?', [strtolower($deptName)])->first();
            }
<<<<<<< HEAD

=======
            
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
            if (!$department) {
                \Log::warning('ETL Import - Department not found', [
                    'dept_name_provided' => $deptName,
                    'available_departments' => Department::pluck('dept_name', 'dept_code')->toArray()
                ]);
                continue; // skip if department not found
            }
<<<<<<< HEAD

=======
            
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
            \Log::info("ETL Import - Department found", [
                'provided' => $deptName,
                'matched' => $department->dept_name,
                'dept_id' => $department->department_id
            ]);

            // Determine position and accessRole
            // Check multiple possible column names for position/title information (case-insensitive)
            // PRIORITIZE 'position' column first since it contains the actual position data
            $title = '';
            $priorityColumns = ['position', 'Position']; // Check position column first
            $fallbackColumns = ['Title', 'Titles', 'Role', 'Designation', 'title', 'titles'];
<<<<<<< HEAD

=======
            
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
            // SPECIFIC DEBUG: Check if position column exists
            \Log::info("ETL Import - Position Column Debug", [
                'position_lowercase' => $row['position'] ?? 'NOT_FOUND',
                'Position_uppercase' => $row['Position'] ?? 'NOT_FOUND',
                'all_keys' => array_keys($row)
            ]);
<<<<<<< HEAD

=======
            
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
            // First try priority columns (position)
            foreach ($priorityColumns as $colName) {
                if (isset($row[$colName]) && !empty(trim($row[$colName]))) {
                    $title = strtolower(trim($row[$colName]));
                    \Log::info("ETL Import - Found title in PRIORITY column: {$colName}", ['value' => $title]);
                    break;
                }
            }
<<<<<<< HEAD

=======
            
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
            // If not found in priority columns, try fallback columns
            if (empty($title)) {
                foreach ($fallbackColumns as $colName) {
                    // Check exact match first
                    if (isset($row[$colName]) && !empty(trim($row[$colName]))) {
                        $title = strtolower(trim($row[$colName]));
                        \Log::info("ETL Import - Found title in fallback column: {$colName}", ['value' => $title]);
                        break;
                    }
<<<<<<< HEAD

=======
                    
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
                    // Check case-insensitive match
                    foreach ($row as $actualKey => $value) {
                        if (strtolower($actualKey) === strtolower($colName) && !empty(trim($value))) {
                            $title = strtolower(trim($value));
                            \Log::info("ETL Import - Found title in column (case-insensitive): {$actualKey}", ['value' => $title]);
                            break 2; // Break out of both loops
                        }
                    }
                }
            }
<<<<<<< HEAD

=======
            
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
            // If no standard column found, check for any column containing position-like values
            if (empty($title)) {
                foreach ($row as $key => $value) {
                    if (!empty(trim($value))) {
                        $cleanValue = strtolower(trim($value));
                        // Check if any position-related keywords are found
<<<<<<< HEAD
                        if (
                            str_contains($cleanValue, 'head') ||
                            str_contains($cleanValue, 'staff') ||
                            str_contains($cleanValue, 'director') ||
                            str_contains($cleanValue, 'chief') ||
                            str_contains($cleanValue, 'vpaa') ||
                            str_contains($cleanValue, 'vice president')
                        ) {
=======
                        if (str_contains($cleanValue, 'head') || 
                            str_contains($cleanValue, 'staff') || 
                            str_contains($cleanValue, 'director') || 
                            str_contains($cleanValue, 'chief') || 
                            str_contains($cleanValue, 'vpaa') || 
                            str_contains($cleanValue, 'vice president')) {
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
                            $title = $cleanValue;
                            \Log::info("ETL Import - Found position value in column: {$key}", ['value' => $title]);
                            break;
                        }
                    }
                }
            }
<<<<<<< HEAD

=======
            
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
            // Enhanced debugging for title detection
            \Log::info("ETL Import - Title Analysis", [
                'All_Row_Data' => $row,
                'Cleaned_Title' => $title,
                'Title_Length' => strlen($title),
                'Available_Keys' => array_keys($row)
            ]);
<<<<<<< HEAD

=======
            
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
            // Check if title indicates Head position
            if (str_contains($title, 'head') || str_contains($title, 'director') || str_contains($title, 'chief')) {
                $position = 'Head';
                \Log::info("ETL Import - Detected as Head", ['title' => $title, 'contains_head' => str_contains($title, 'head')]);
            } elseif (str_contains($title, 'vpaa') || str_contains($title, 'vice president')) {
                $position = 'VPAA';
                \Log::info("ETL Import - Detected as VPAA", ['title' => $title]);
<<<<<<< HEAD
            } elseif (str_contains($title, 'secretary')) {  // <-- ADD THIS
                $position = 'Secretary';
                \Log::info("ETL Import - Detected as Secretary", ['title' => $title]);
            } elseif (str_contains($title, 'staff')) {
                $position = 'Staff';
=======
            } elseif (str_contains($title, 'staff')) {
                $position = 'Staff';  // Explicitly handle Staff
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
                \Log::info("ETL Import - Detected as Staff", ['title' => $title]);
            } else {
                $position = 'Staff';  // Default to Staff for unrecognized titles
                \Log::info("ETL Import - Defaulted to Staff", ['title' => $title, 'reason' => 'No matching keywords found']);
            }

<<<<<<< HEAD
            // Set access role - Head and VPAA are approvers, Secretary is requestor, Staff are viewers
            if ($position === 'Head' || $position === 'VPAA') {
                $accessRole = 'Approver';
            } elseif ($position === 'Secretary') {
                $accessRole = 'Requestor';
            } else {
                $accessRole = 'Viewer';
            }

=======
            // Set access role - Head and VPAA are approvers, Staff are viewers
            $accessRole = ($position === 'Head' || $position === 'VPAA') ? 'Approver' : 'Viewer';
            
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
            \Log::info("ETL Import - Position determined", [
                'Title_Used' => $title,
                'Determined_Position' => $position,
                'Access_Role' => $accessRole
            ]);

            // Check if employee already exists in tb_employeeinfo
            $existingEmployee = EmployeeInfo::where('Emp_No', $row['Emp_No'])->first();
<<<<<<< HEAD

=======
            
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
            if ($existingEmployee) {
                \Log::info('ETL Import - Employee already exists, skipping', [
                    'Emp_No' => $existingEmployee->Emp_No,
                    'Name' => $existingEmployee->FirstName . ' ' . $existingEmployee->LastName
                ]);
                $skippedEmployeesCount++;
                continue; // Skip existing employees - this is the key fix!
            }

            // Check if user account already exists in tb_account 
            $existingUser = User::where('Emp_No', $row['Emp_No'])->first();
<<<<<<< HEAD

=======
            
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
            if ($existingUser) {
                \Log::info('ETL Import - User account already exists, skipping', [
                    'Emp_No' => $employee->Emp_No,
                    'Username' => $existingUser->username
                ]);
                $skippedEmployeesCount++;
                continue; // Skip existing user accounts
            }

            try {
                DB::beginTransaction();

                // Create new employee (only if doesn't exist)
                $employee = EmployeeInfo::create([
                    'Emp_No' => trim($row['Emp_No']),
                    'Titles' => isset($row['Titles']) ? trim($row['Titles']) : '', // Always use Titles column from Excel
                    'LastName' => trim($row['LastName'] ?? ''),
                    'FirstName' => trim($row['FirstName'] ?? ''),
                    'MiddleName' => trim($row['MiddleName'] ?? ''),
                    'Suffix' => trim($row['Suffix'] ?? ''),
                    'Email' => trim($row['Email'] ?? ''),
                ]);

                \Log::info('ETL Import - Employee created', [
                    'Emp_No' => $employee->Emp_No,
                    'Titles' => $employee->Titles,
                    'Name' => $employee->FirstName . ' ' . $employee->LastName
                ]);

                // Generate username and password
                $username = $department->dept_code . '-' . $row['Emp_No'];
                $password = $row['Emp_No'] . $row['LastName']; // password is Emp_No + LastName

                // Create new user account (only if doesn't exist)
                $user = User::create([
                    'Emp_No' => $employee->Emp_No,
                    'username' => $username,
                    'password' => Hash::make($password),
                    'department_id' => $department->department_id,
                    'position' => $position,
                    'accessRole' => $accessRole,
                    'status' => 'active',
                ]);

                // Create approver permissions for Head and VPAA
                if ($accessRole === 'Approver') {
                    ApproverPermission::create([
                        'accnt_id' => $user->accnt_id,
                        'can_approve_pending' => true,
                        'can_approve_in_progress' => true,
                    ]);
                }

                DB::commit();
                $newEmployeesCount++;
                \Log::info('ETL Import - New employee created successfully', [
                    'Emp_No' => $employee->Emp_No,
                    'Name' => $employee->FirstName . ' ' . $employee->LastName,
                    'Department' => $department->dept_name,
                    'Position' => $position
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                \Log::error('ETL Import - Failed to create employee', [
                    'Emp_No' => $row['Emp_No'],
                    'error' => $e->getMessage()
                ]);
                continue; // Continue with next employee if one fails
            }
        }

        // Final verification of counts
        $expectedTotal = $newEmployeesCount + $skippedEmployeesCount;
        if ($totalRowsProcessed !== $expectedTotal) {
            \Log::warning('ETL Import - Count mismatch detected', [
                'total_rows_processed' => $totalRowsProcessed,
                'new_employees' => $newEmployeesCount,
                'skipped_employees' => $skippedEmployeesCount,
                'expected_total' => $expectedTotal
            ]);
        }

        // Return with focused import summary - only show imported count
        if ($newEmployeesCount > 0) {
            $message = "ETL Import completed successfully. {$newEmployeesCount} new employees imported.";
        } else {
            $message = "ETL Import completed successfully. No new employees found to import.";
        }

        \Log::info('ETL Import - Final Summary', [
            'total_rows_processed' => $totalRowsProcessed,
            'new_employees' => $newEmployeesCount,
            'skipped_employees' => $skippedEmployeesCount,
            'expected_match' => ($totalRowsProcessed === $newEmployeesCount + $skippedEmployeesCount)
        ]);

        return redirect()->route('admin.employee_list')->with('success', $message);
    }

    /**
     * Show all employees after ETL import (admin only).
     */
    public function showAllEmployees()
    {
        $employees = EmployeeInfo::with(['user.department'])
            ->orderBy('created_at', 'desc')
            ->paginate(10); // 10 employees per page, consistent with other admin tables

        return view('admin.employee-list', compact('employees'));
    }
}
