<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionsTable extends Migration
{
    public function up()
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('type', 50);
            $table->text('description')->nullable();
            $table->boolean('is_auto_apply')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_stackable')->default(false);
            $table->boolean('is_global')->default(true);
            $table->unsignedInteger('priority')->default(0);
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->decimal('max_discount_amount', 10, 2)->nullable();
            $table->decimal('min_cart_amount', 10, 2)->nullable();
            $table->unsignedInteger('total_usage_limit')->nullable();
            $table->unsignedInteger('per_user_limit')->nullable();
            $table->unsignedBigInteger('buy_product_id')->nullable();
            $table->unsignedInteger('buy_quantity')->nullable();
            $table->unsignedBigInteger('get_product_id')->nullable();
            $table->unsignedInteger('get_quantity')->nullable();
            $table->decimal('get_discount_percent', 5, 2)->nullable();
            $table->json('applicable_category_ids')->nullable();
            $table->json('applicable_product_ids')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->json('meta')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('buy_product_id')->references('id')->on('menu_products')->nullOnDelete();
            $table->foreign('get_product_id')->references('id')->on('menu_products')->nullOnDelete();
            $table->index(['is_active', 'type']);
            $table->index(['starts_at', 'ends_at']);
        });

        Schema::create('promotion_stores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('promotion_id');
            $table->unsignedBigInteger('store_id');
            $table->timestamps();

            $table->foreign('promotion_id')->references('id')->on('promotions')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->unique(['promotion_id', 'store_id']);
        });

        Schema::create('promotion_redemptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('promotion_id');
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('order_reference')->nullable();
            $table->decimal('cart_amount', 10, 2)->nullable();
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamps();

            $table->foreign('promotion_id')->references('id')->on('promotions')->onDelete('cascade');
            $table->index(['promotion_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('promotion_redemptions');
        Schema::dropIfExists('promotion_stores');
        Schema::dropIfExists('promotions');
    }
}
