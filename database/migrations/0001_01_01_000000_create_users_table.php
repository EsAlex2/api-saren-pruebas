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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('identity')->unique();
            $table->string('first_name', 50);
            $table->string('second_name', 50)->nullable();
            $table->string('first_lastname', 50);
            $table->string('second_lastname', 50)->nullable();
            $table->string('nacionality');
            $table->string('email', 50)->unique();
            $table->string('birthdate')->nullable();
            $table->string('company_name')->nullable();
            $table->string('rif_digit')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
