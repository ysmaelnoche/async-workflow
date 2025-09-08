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
            $table->string('prepared_by')->nullable()->after('is_proxy_submission');
            $table->enum('encoded_by_role', ['dean', 'secretary'])->nullable()->after('prepared_by');
            $table->string('encoded_by_name')->nullable()->after('encoded_by_role');
            $table->timestamp('encoding_timestamp')->nullable()->after('encoded_by_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_requests', function (Blueprint $table) {
            $table->dropColumn(['prepared_by', 'encoded_by_role', 'encoded_by_name', 'encoding_timestamp']);
        });
    }
};
