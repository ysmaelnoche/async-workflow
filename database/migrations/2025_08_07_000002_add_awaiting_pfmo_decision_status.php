<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAwaitingPfmoDecisionStatus extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // TODO: Add your migration logic here
        // Example: Schema::table('form_requests', function (Blueprint $table) {
        //     $table->string('awaiting_pfmo_decision_status')->nullable();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // TODO: Add your rollback logic here
        // Example: Schema::table('form_requests', function (Blueprint $table) {
        //     $table->dropColumn('awaiting_pfmo_decision_status');
        // });
    }
}
