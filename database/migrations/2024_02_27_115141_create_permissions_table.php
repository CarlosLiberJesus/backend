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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('app_id');
            $table->unsignedBigInteger('role_id');
            $table->string('uuid')->unique()->index();
            $table->string('code', 100);
            $table->string('name', 100);
            $table->string('color', 10)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('app_id')->references('id')->on('applications');
            $table->foreign('role_id')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
