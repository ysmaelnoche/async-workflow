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
        Schema::table('sub_departments', function (Blueprint $table) {
            $table->bigInteger('supervisor_id')->unsigned()->nullable()->after('description');
            $table->foreign('supervisor_id')->references('accnt_id')->on('tb_account')->onDelete('set null');
            $table->index('supervisor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_departments', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->dropIndex(['supervisor_id']);
            $table->dropColumn('supervisor_id');
        });
    }
};
