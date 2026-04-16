<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderingUsersTable extends Migration
{
    public function up()
    {
        Schema::create('ordering_users', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->unique();
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ordering_users');
    }
}
