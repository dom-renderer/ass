<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskCheckersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_checkers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->tinyInteger('status')->default(0)->comment('0 = Pending Verification | 1 = Reassigned | 2 = Verified');
            $table->tinyInteger('level')->default(1);
            $table->string('signature')->nullable();
            $table->json('data')->nullable();
            $table->text('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_checkers');
    }
}
