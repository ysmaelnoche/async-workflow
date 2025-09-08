<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubDepartmentSeeder extends Seeder
{
    public function run(): void
    {
<<<<<<< HEAD
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear existing data
        DB::table('sub_departments')->truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('sub_departments')->insert([
            // PFMO Sub-departments based on organizational chart - 5 main sections
            [
                'subdepartment_code' => 'PFMO-WAREHOUSE',
                'name' => 'Warehouse Section',
                'description' => 'Manages maintenance materials, inventory, and storage. Head: ROY MORALES. Handles: Warehouse Staff, Electrician, HVAC Technician, Tape/Signage Installer, Fire & Appliance Equipment.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subdepartment_code' => 'PFMO-CONSTRUCTION',
                'name' => 'Construction Section',
                'description' => 'Handles construction, fabrication, and structural work. Head: ALBERT AYAP. Handles: Carpenter, Welder, Construction Workers, Pipe Installer, Painter, Artist.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subdepartment_code' => 'PFMO-GENERAL',
                'name' => 'General Services',
                'description' => 'Manages general maintenance and services. Head: ROS BALTAZAR. Handles: Plumber, Traffic, Monitoring & Inspection Team.',
=======
        DB::table('sub_departments')->insert([
            [
                'subdepartment_code' => 'PFMO-CONS',
                'name' => 'Construction',
                'description' => 'Responsible for all fabrication, building, and structural maintenance tasks.',
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subdepartment_code' => 'PFMO-HOUSEKEEPING',
                'name' => 'Housekeeping',
<<<<<<< HEAD
                'description' => 'Responsible for cleanliness and facility maintenance. Head: VIRGILIO VITERBO. Handles: Housekeepers, Mechanics, Ground Maintenance.',
=======
                'description' => 'Manages cleanliness, sanitation, and general upkeep of facilities.',
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
<<<<<<< HEAD
                'subdepartment_code' => 'PFMO-TRANSPORTATION',
                'name' => 'Transportation Section',
                'description' => 'Manages vehicle operations and transportation services. Head: MICHAEL DELA CRUZ. Handles: Head Driver, Drivers.',
=======
                'subdepartment_code' => 'PFMO-GEN',
                'name' => 'General Services',
                'description' => 'Handles repairs and maintenance of equipment, furniture, and utilities.',
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
<<<<<<< HEAD

        echo "Sub-departments seeded successfully!\n";
=======
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
    }
}
