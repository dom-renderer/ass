<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompletionTemplatesToNewWorkflowTemplateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('new_workflow_assignment_items', function (Blueprint $table) {
            $table->unsignedBigInteger('maker_completion_push_notification')->nullable();
            $table->unsignedBigInteger('maker_completion_email_notification')->nullable();
        });

        Schema::table('new_workflow_template_items', function (Blueprint $table) {
            $table->unsignedBigInteger('maker_completion_push_notification')->nullable();
            $table->unsignedBigInteger('maker_completion_email_notification')->nullable();
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
            $table->dropColumn(['maker_completion_push_notification', 'maker_completion_email_notification']);
        });

        Schema::table('new_workflow_template_items', function (Blueprint $table) {
            $table->dropColumn(['maker_completion_push_notification', 'maker_completion_email_notification']);
        });
    }
}
