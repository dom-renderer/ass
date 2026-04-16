<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreQrCodesTable extends Migration
{
    public function up()
    {
        Schema::create('store_qr_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->integer('table_number');
            $table->string('qr_label', 50);
            $table->text('qr_url');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->unique(['store_id', 'table_number'], 'store_qr_store_table_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('store_qr_codes');
    }
}
