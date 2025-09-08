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
            $table->text('signature_data')->nullable()->after('signature_applied');
            $table->enum('signature_style', ['text', 'draw'])->nullable()->after('signature_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_requests', function (Blueprint $table) {
            $table->dropColumn(['signature_data', 'signature_style']);
        });
    }
};
