<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChecklistCheckersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checklist_checkers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('checklist_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->tinyInteger('level')->default(1);
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
        Schema::dropIfExists('checklist_checkers');
    }
}
