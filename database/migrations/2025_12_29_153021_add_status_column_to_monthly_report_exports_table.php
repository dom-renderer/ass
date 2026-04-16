<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusColumnToMonthlyReportExportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('monthly_report_exports', function (Blueprint $table) {
            $table->tinyInteger('status')->default(false)->comment('0 = Pending | 1 = In Progress | 2 = Completed | 3 = Failed')->after('city');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('monthly_report_exports', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
