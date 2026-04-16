<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGatePassLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gate_pass_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id')->nullable();
            $table->string('entered_pass_number')->nullable();
            $table->boolean('validation_type')->default(0)->comment('0 = Scanned | 1 = Manually');
            $table->boolean('is_valid')->default(0);
            $table->boolean('entry_type')->default(0)->comment('1 = Entry | 2 = Exit');
            $table->unsignedBigInteger('verified_by')->nullable();
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
        Schema::dropIfExists('gate_pass_logs');
    }
}
