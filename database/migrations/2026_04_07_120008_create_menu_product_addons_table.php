<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuProductAddonsTable extends Migration
{
    public function up()
    {
        Schema::create('menu_product_addons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('addon_id');
            $table->decimal('price_override', 10, 2)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('menu_products')->onDelete('cascade');
            $table->foreign('addon_id')->references('id')->on('menu_addons');

            $table->unique(['product_id', 'addon_id'], 'menu_product_addon_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('menu_product_addons');
    }
}
