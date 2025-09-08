<?php

namespace Database\Seeders;

// use App\Models\User; // Commented out or remove if not needed elsewhere
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
<<<<<<< HEAD
            SubDepartmentSeeder::class,
            SignatureStyleSeeder::class,
            AdminAccountSeeder::class,
            // EmployeeAndAccountSeeder::class,
=======

            SignatureStyleSeeder::class,
            AdminAccountSeeder::class,
            // EmployeeAndAccountSeeder::class,


>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
        ]);
    }
}
