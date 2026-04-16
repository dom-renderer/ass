<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuProductAttributesTable extends Migration
{
    public function up()
    {
        Schema::create('menu_product_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('attribute_id');
            $table->unsignedBigInteger('attribute_value_id');
            $table->decimal('price_override', 10, 2)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('menu_products')->onDelete('cascade');
            $table->foreign('attribute_id')->references('id')->on('menu_attributes');
            $table->foreign('attribute_value_id')->references('id')->on('menu_attribute_values');

            $table->unique(['product_id', 'attribute_value_id'], 'menu_product_attr_value_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('menu_product_attributes');
    }
}
