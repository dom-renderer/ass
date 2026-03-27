<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStoreTypeToStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->boolean('type')->default(0)->comment('0 = Location | 1 = Asset')->after('id');
            $table->string('ucode')->nullable()->after('code');
            $table->dateTime('po_date')->nullable()->after('ucode');
            $table->integer('warranty')->default(0)->comment('in months');
            $table->integer('lifespan')->default(0)->comment('in months');
            $table->string('primary_image')->nullable();
            $table->json('secondary_images')->nullable();
        });

        Schema::table('store_types', function (Blueprint $table) {
            $table->boolean('type')->default(0)->comment('0 = Location | 1 = Asset')->after('id');
        });

        Schema::table('store_categories', function (Blueprint $table) {
            $table->boolean('type')->default(0)->comment('0 = Location | 1 = Asset')->after('id');
        });

        Schema::table('model_types', function (Blueprint $table) {
            $table->boolean('type')->default(0)->comment('0 = Location | 1 = Asset')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('store_types', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('store_categories', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('model_types', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}