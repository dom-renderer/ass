<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFlagsToMenuProductAttributesTable extends Migration
{
    public function up()
    {
        Schema::table('menu_product_attributes', function (Blueprint $table) {
            $table->boolean('is_available')->default(1)->after('price_override');
            $table->boolean('is_default')->default(0)->after('is_available');
        });
    }

    public function down()
    {
        Schema::table('menu_product_attributes', function (Blueprint $table) {
            $table->dropColumn(['is_available', 'is_default']);
        });
    }
}
