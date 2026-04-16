<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuAttributesTable extends Migration
{
    public function up()
    {
        Schema::create('menu_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->boolean('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('menu_attributes');
    }
}
