<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGatePassToChecklistTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('checklist_tasks', function (Blueprint $table) {
            $table->string('pass_number')->nullable();

            $table->index('pass_number', 'pass_number_to_verify');
        });

        Schema::table('dynamic_forms', function (Blueprint $table) {
            $table->boolean('needs_pass')->default(0)->comment('0 = No | 1 = Yes');
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
            $table->dropColumn(['pass_number']);
        });

        Schema::table('dynamic_forms', function (Blueprint $table) {
            $table->dropColumn(['needs_pass']);
        });
    }
}
