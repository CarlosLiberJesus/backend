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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('app_id');
            $table->string('uuid')->unique()->index();
            $table->string('name', 100)->unique();
            $table->string('code', 5)->unique();
            $table->string('color', 10);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('app_id')->references('id')->on('applications');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};