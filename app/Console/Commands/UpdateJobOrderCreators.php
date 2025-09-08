<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JobOrder;
use App\Models\User;

class UpdateJobOrderCreators extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job-orders:update-creators';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing job orders with creator information';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Find PFMO Head user
        $pfmoHead = User::join('tb_department', 'tb_account.department_id', '=', 'tb_department.department_id')
            ->where('tb_department.dept_code', 'PFMO')
            ->where('tb_account.position', 'Head')
            ->select('tb_account.*')
            ->first();

        if ($pfmoHead) {
            // Update existing job orders
            $updated = JobOrder::whereNull('created_by')->update(['created_by' => $pfmoHead->accnt_id]);
            $this->info("Updated {$updated} job orders with PFMO Head as creator: {$pfmoHead->username}");
            
            // Show job order count
            $totalJobOrders = JobOrder::count();
            $this->info("Total job orders: {$totalJobOrders}");
        } else {
            $this->error("No PFMO Head found");
            
            // Show PFMO users instead
            $pfmoUsers = User::join('tb_department', 'tb_account.department_id', '=', 'tb_department.department_id')
                ->where('tb_department.dept_code', 'PFMO')
                ->select('tb_account.*', 'tb_department.dept_name')
                ->get();
            
            $this->info("PFMO Users:");
            foreach ($pfmoUsers as $user) {
                $this->line("- {$user->username} ({$user->position})");
            }
        }
    }
}
