<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddHtmlcontentToTicketitAndComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ticketit', function (Blueprint $table) {
            $table->longText('html')->nullable()->after('content');
        });

        Schema::table('ticketit_comments', function (Blueprint $table) {
            $table->longText('html')->nullable()->after('content');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ticketit', function (Blueprint $table) {
            $table->dropColumn('html');
        });

        Schema::table('ticketit_comments', function (Blueprint $table) {
            $table->dropColumn('html');
        });
    }
}