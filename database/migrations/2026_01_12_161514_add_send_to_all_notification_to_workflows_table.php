<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSendToAllNotificationToWorkflowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('new_workflow_templates', function (Blueprint $table) {
            $table->boolean('send_to_all_notification')->default(false);
        });

        Schema::table('new_workflow_assignments', function (Blueprint $table) {
            $table->boolean('send_to_all_notification')->default(false);
        });
        
        Schema::table('new_workflow_template_items', function (Blueprint $table) {
            $table->integer('maker_turn_around_time_minute')->default(0)->nullable()->after('maker_turn_around_time_hour');
            $table->integer('maker_escalation_after_minute')->default(0)->nullable()->after('maker_escalation_after_hour');

            $table->integer('checker_turn_around_time_minute')->default(0)->nullable()->after('checker_turn_around_time_hour');
            $table->integer('checker_escalation_after_minute')->default(0)->nullable()->after('checker_escalation_after_hour');
        });

        Schema::table('new_workflow_assignment_items', function (Blueprint $table) {
            $table->integer('maker_turn_around_time_minute')->default(0)->nullable()->after('maker_turn_around_time_hour');
            $table->integer('maker_escalation_after_minute')->default(0)->nullable()->after('maker_escalation_after_hour');

            $table->integer('checker_turn_around_time_minute')->default(0)->nullable()->after('checker_turn_around_time_hour');
            $table->integer('checker_escalation_after_minute')->default(0)->nullable()->after('checker_escalation_after_hour');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('new_workflow_templates', function (Blueprint $table) {
            $table->dropColumn(['send_to_all_notification']);
        });

        Schema::table('new_workflow_assignments', function (Blueprint $table) {
            $table->dropColumn('send_to_all_notification');
        });

        Schema::table('new_workflow_template_items', function (Blueprint $table) {
            $table->dropColumn(['maker_turn_around_time_minute', 'maker_escalation_after_minute', 'checker_turn_around_time_minute', 'checker_escalation_after_minute']);
        });

        Schema::table('new_workflow_assignment_items', function (Blueprint $table) {
            $table->dropColumn('maker_turn_around_time_minute', 'maker_escalation_after_minute', 'checker_turn_around_time_minute', 'checker_escalation_after_minute');
        });
    }
}