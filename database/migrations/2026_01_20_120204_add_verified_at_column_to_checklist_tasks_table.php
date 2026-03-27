<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVerifiedAtColumnToChecklistTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('checklist_tasks', function (Blueprint $table) {
            $table->dateTime('verified_at')->nullable()->after('date');
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
            $table->dropColumn('verified_at');
        });
    }
}
