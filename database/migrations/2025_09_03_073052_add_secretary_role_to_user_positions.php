<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add Secretary position to existing enum
        DB::statement("ALTER TABLE `tb_account` MODIFY COLUMN `position` ENUM('Head','Staff','Admin','VPAA','Secretary') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove Secretary position from enum (revert to previous values)
        DB::statement("ALTER TABLE `tb_account` MODIFY COLUMN `position` ENUM('Head','Staff','Admin','VPAA') NOT NULL");
    }
};
