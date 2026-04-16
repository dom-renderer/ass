<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreMenuItemsTable extends Migration
{
    public function up()
    {
        Schema::create('store_menu_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('product_id')->nullable()->default(null);
            $table->boolean('is_active')->default(1);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('menu_categories');
            $table->foreign('product_id')->references('id')->on('menu_products')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('store_menu_items');
    }
}
