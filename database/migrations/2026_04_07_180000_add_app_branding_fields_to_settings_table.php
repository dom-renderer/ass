<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAppBrandingFieldsToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('app_name')->nullable()->after('maintenance_mode');
            $table->text('app_description')->nullable()->after('app_name');
            $table->string('logo')->nullable()->after('app_description');
            $table->string('favicon')->nullable()->after('logo');
            $table->string('primary_theme_colour', 20)->nullable()->after('favicon');
            $table->string('primary_font_colour', 20)->nullable()->after('primary_theme_colour');
            $table->unsignedBigInteger('default_font_id')->nullable()->after('primary_font_colour');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'app_name',
                'app_description',
                'logo',
                'favicon',
                'primary_theme_colour',
                'primary_font_colour',
                'default_font_id',
            ]);
        });
    }
}

