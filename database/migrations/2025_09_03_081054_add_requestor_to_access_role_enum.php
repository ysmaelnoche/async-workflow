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
        DB::statement("ALTER TABLE tb_account MODIFY COLUMN accessRole ENUM('Approver','Viewer','Admin','Requestor') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE tb_account MODIFY COLUMN accessRole ENUM('Approver','Viewer','Admin') NOT NULL");
    }
};
