<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string("ip");
            $table->string("key")->nullable();
            $table->text("value")->nullable();
            $table->text("referrer")->nullable();
            $table->string("user_agent")->nullable();
            $table->string("cssguid", 32)->nullable();
            $table->string("cssgsuid", 32)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
