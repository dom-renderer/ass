<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('new_workflow_template_items', function (Blueprint $table) {
            $table->json('maker_escalation_user_ids')->nullable()->after('maker_escalation_user_id');
            $table->json('checker_escalation_user_ids')->nullable()->after('checker_escalation_user_id');
        });

        Schema::table('new_workflow_assignment_items', function (Blueprint $table) {
            $table->json('maker_escalation_user_ids')->nullable()->after('maker_escalation_user_id');
            $table->json('checker_escalation_user_ids')->nullable()->after('checker_escalation_user_id');
        });

        DB::table('new_workflow_template_items')
            ->whereNotNull('maker_escalation_user_id')
            ->update([
                'maker_escalation_user_ids' => DB::raw("JSON_ARRAY(maker_escalation_user_id)")
            ]);

        DB::table('new_workflow_template_items')
            ->whereNotNull('checker_escalation_user_id')
            ->update([
                'checker_escalation_user_ids' => DB::raw("JSON_ARRAY(checker_escalation_user_id)")
            ]);

        DB::table('new_workflow_assignment_items')
            ->whereNotNull('maker_escalation_user_id')
            ->update([
                'maker_escalation_user_ids' => DB::raw("JSON_ARRAY(maker_escalation_user_id)")
            ]);

        DB::table('new_workflow_assignment_items')
            ->whereNotNull('checker_escalation_user_id')
            ->update([
                'checker_escalation_user_ids' => DB::raw("JSON_ARRAY(checker_escalation_user_id)")
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('new_workflow_template_items', function (Blueprint $table) {
            $table->dropColumn(['maker_escalation_user_ids', 'checker_escalation_user_ids']);
        });

        Schema::table('new_workflow_assignment_items', function (Blueprint $table) {
            $table->dropColumn(['maker_escalation_user_ids', 'checker_escalation_user_ids']);
        });
    }
};
