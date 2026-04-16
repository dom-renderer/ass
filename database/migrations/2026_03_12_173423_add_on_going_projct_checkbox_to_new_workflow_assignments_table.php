<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOnGoingProjctCheckboxToNewWorkflowAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('new_workflow_assignments', function (Blueprint $table) {
            $table->boolean('on_going_project')->default(0);
        });

        Schema::table('checklist_tasks', function (Blueprint $table) {
            $table->dateTime('on_going_completion_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('new_workflow_assignments', function (Blueprint $table) {
            $table->dropColumn('on_going_project');
        });

        Schema::table('checklist_tasks', function (Blueprint $table) {
            $table->dropColumn('on_going_completion_date');
        });
    }
}
