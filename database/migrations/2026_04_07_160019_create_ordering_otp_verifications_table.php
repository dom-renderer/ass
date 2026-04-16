<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderingOtpVerificationsTable extends Migration
{
    public function up()
    {
        Schema::create('ordering_otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20);
            $table->string('otp', 10);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->index(['phone', 'expires_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ordering_otp_verifications');
    }
}
