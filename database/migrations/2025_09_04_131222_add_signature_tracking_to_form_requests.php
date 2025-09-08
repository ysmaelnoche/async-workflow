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
            $table->string('approved_by')->nullable()->after('encoding_timestamp');
            $table->boolean('signature_applied')->default(false)->after('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_requests', function (Blueprint $table) {
            $table->dropColumn(['approved_by', 'signature_applied']);
        });
    }
};
