<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEscalationSentAtToChecklistTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('checklist_tasks', function (Blueprint $table) {
            $table->timestamp('maker_escalation_sent_at')->nullable()->after('completion_date');
            $table->timestamp('checker_escalation_sent_at')->nullable()->after('maker_escalation_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('checklist_tasks', function (Blueprint $table) {
            $table->dropColumn(['maker_escalation_sent_at', 'checker_escalation_sent_at']);
        });
    }
}
