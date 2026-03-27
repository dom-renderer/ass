<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStartEndDatetimeToNewWorkflowAssignmentItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('new_workflow_assignment_items', function (Blueprint $table) {
            $table->datetime('start_datetime')->nullable()->after('trigger');
            $table->datetime('end_datetime')->nullable()->after('start_datetime');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('new_workflow_assignment_items', function (Blueprint $table) {
            $table->dropColumn(['start_datetime', 'end_datetime']);
        });
    }
}
