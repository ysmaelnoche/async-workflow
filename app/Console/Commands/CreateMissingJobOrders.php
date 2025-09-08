<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FormRequest;
use App\Models\JobOrder;
use App\Services\JobOrderService;

class CreateMissingJobOrders extends Command
{
    protected $signature = 'job-orders:create-missing';
    protected $description = 'Create job orders for approved PFMO requests that are missing them';

    public function handle()
    {
        $this->info('=== Creating Missing Job Orders ===');

        // Get approved IOM requests to PFMO without job orders
        $requestsWithoutJobOrders = FormRequest::with(['toDepartment', 'jobOrder'])
            ->where('status', 'Approved')
            ->where('form_type', 'IOM')
            ->whereHas('toDepartment', function($query) {
                $query->where('dept_code', 'PFMO');
            })
            ->whereDoesntHave('jobOrder')
            ->get();

        $this->info("Found {$requestsWithoutJobOrders->count()} approved PFMO requests without job orders");

        $created = 0;
        foreach ($requestsWithoutJobOrders as $request) {
            try {
                // Check if it needs a job order
                if (JobOrderService::needsJobOrder($request)) {
                    $jobOrder = JobOrderService::createJobOrder($request);
                    $this->info("Created job order {$jobOrder->job_order_number} for form {$request->form_id}");
                    $created++;
                } else {
                    $this->warn("Form {$request->form_id} does not need a job order");
                }
            } catch (\Exception $e) {
                $this->error("Failed to create job order for form {$request->form_id}: " . $e->getMessage());
            }
        }

        $this->info("Successfully created {$created} job orders");
        
        // Show updated stats
        $totalApproved = FormRequest::where('status', 'Approved')
            ->where('form_type', 'IOM')
            ->whereHas('toDepartment', function($query) {
                $query->where('dept_code', 'PFMO');
            })
            ->count();
            
        $totalWithJobOrders = FormRequest::where('status', 'Approved')
            ->where('form_type', 'IOM')
            ->whereHas('toDepartment', function($query) {
                $query->where('dept_code', 'PFMO');
            })
            ->whereHas('jobOrder')
            ->count();

        $percentage = $totalApproved > 0 ? round(($totalWithJobOrders / $totalApproved) * 100, 1) : 0;
        $this->info("Updated job order creation rate: {$totalWithJobOrders}/{$totalApproved} ({$percentage}%)");
    }
}
