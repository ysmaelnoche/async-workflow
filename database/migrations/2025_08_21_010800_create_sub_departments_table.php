<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sub_departments', function (Blueprint $table) {
            $table->id();
            $table->string('subdepartment_code')->unique();
            $table->string('name')->unique();
            $table->string('description');
            $table->timestamps();
        });
        Schema::table('tb_account', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_department_id')->nullable()->after('department_id');
            $table->foreign('sub_department_id')->references('id')->on('sub_departments')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('tb_account', function (Blueprint $table) {
            $table->dropForeign(['sub_department_id']);
            $table->dropColumn('sub_department_id');
        });
        Schema::dropIfExists('sub_departments');
    }
};
