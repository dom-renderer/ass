<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuAttributeValuesTable extends Migration
{
    public function up()
    {
        Schema::create('menu_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attribute_id');
            $table->string('value', 191);
            $table->decimal('extra_price', 10, 2)->default(0);
            $table->integer('ordering')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('attribute_id')->references('id')->on('menu_attributes')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('menu_attribute_values');
    }
}
