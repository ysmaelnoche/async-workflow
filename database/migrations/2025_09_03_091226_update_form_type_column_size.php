<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('form_requests', function (Blueprint $table) {
            // Increase form_type column size to accommodate longer request type names
            $table->string('form_type', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_requests', function (Blueprint $table) {
            // Revert back to original size
            $table->string('form_type', 20)->change();
        });
    }
};
