<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreMenuProductAddonsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('store_menu_product_addons')) {
            return;
        }

        Schema::create('store_menu_product_addons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_addon_id');
            $table->boolean('is_available')->default(1);
            $table->boolean('is_default')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('menu_products')->onDelete('cascade');
            $table->foreign('product_addon_id')->references('id')->on('menu_product_addons')->onDelete('cascade');
            $table->unique(['store_id', 'product_addon_id'], 'store_menu_prod_addon_unique');
        });
    }

    public function down()
    {
        if (!Schema::hasTable('store_menu_product_addons')) {
            return;
        }

        Schema::dropIfExists('store_menu_product_addons');
    }
}
