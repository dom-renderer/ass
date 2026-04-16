<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('menu_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('store_qr_code_id')->nullable();
            $table->unsignedBigInteger('ordering_user_id');
            $table->unsignedInteger('table_number');
            $table->string('order_number', 32)->unique();
            $table->string('status', 30)->default('received');
            $table->string('payment_method', 30)->default('cash');
            $table->string('coupon_code', 50)->nullable();
            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->decimal('sub_total', 10, 2)->default(0);
            $table->decimal('discount_total', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('store_qr_code_id')->references('id')->on('store_qr_codes')->nullOnDelete();
            $table->foreign('ordering_user_id')->references('id')->on('ordering_users')->onDelete('cascade');
            $table->foreign('promotion_id')->references('id')->on('promotions')->nullOnDelete();
            $table->index(['store_id', 'created_at']);
        });

        Schema::create('menu_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menu_order_id');
            $table->unsignedBigInteger('product_id');
            $table->string('product_name');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('line_total', 10, 2)->default(0);
            $table->json('addons')->nullable();
            $table->json('attributes')->nullable();
            $table->timestamps();

            $table->foreign('menu_order_id')->references('id')->on('menu_orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('menu_products');
        });
    }

    public function down()
    {
        Schema::dropIfExists('menu_order_items');
        Schema::dropIfExists('menu_orders');
    }
}
