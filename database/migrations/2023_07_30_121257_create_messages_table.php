<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('phoneNumber')->nullable();
            $table->foreignId('userId')->nullable();
            $table->string('content')->nullable();
            $table->string('to')->nullable();
            $table->string('number')->nullable();
            $table->string('type')->nullable();
            $table->string('from')->nullable();
            $table->string('status')->nullable();
            $table->string('workspace')->nullable();
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
        Schema::dropIfExists('messages');
    }
};
