<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FormRequest;
use App\Models\JobOrder;

class AnalyzeJobOrderCreation extends Command
{
    protected $signature = 'analyze:job-order-creation';
    protected $description = 'Analyze why job orders are not being created for approved requests';

    public function handle()
    {
        $this->info('=== Job Order Creation Analysis ===');

        // Get all approved IOM requests to PFMO
        $approvedPfmoRequests = FormRequest::with(['toDepartment', 'jobOrder'])
            ->where('status', 'Approved')
            ->where('form_type', 'IOM')
            ->whereHas('toDepartment', function($query) {
                $query->where('dept_code', 'PFMO');
            })
            ->get();

        $this->info("Total approved IOM requests to PFMO: " . $approvedPfmoRequests->count());

        $withJobOrders = $approvedPfmoRequests->filter(function($request) {
            return $request->jobOrder;
        });

        $withoutJobOrders = $approvedPfmoRequests->filter(function($request) {
            return !$request->jobOrder;
        });

        $this->info("With job orders: " . $withJobOrders->count());
        $this->info("Without job orders: " . $withoutJobOrders->count());

        if ($withoutJobOrders->count() > 0) {
            $this->info("\n=== Requests WITHOUT Job Orders ===");
            foreach ($withoutJobOrders as $request) {
                $this->line("Form ID: {$request->form_id}, Title: {$request->title}, Date: {$request->date_submitted}");
            }
        }

        if ($withJobOrders->count() > 0) {
            $this->info("\n=== Requests WITH Job Orders ===");
            foreach ($withJobOrders as $request) {
                $this->line("Form ID: {$request->form_id}, Job Order: {$request->jobOrder->job_order_number}");
            }
        }

        // Check all job orders
        $allJobOrders = JobOrder::with('formRequest')->get();
        $this->info("\n=== All Job Orders ===");
        $this->info("Total job orders: " . $allJobOrders->count());
        
        foreach ($allJobOrders as $jobOrder) {
            $request = $jobOrder->formRequest;
            $this->line("Job Order: {$jobOrder->job_order_number}, Form: {$request->form_id}, Status: {$request->status}");
        }
    }
}
