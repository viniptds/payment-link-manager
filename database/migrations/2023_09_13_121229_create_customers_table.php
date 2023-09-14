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
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('email');
            $table->string('cpf');
            $table->string('document')->nullable();

            // $table->string('street')->nullable();
            // $table->string('number')->nullable();
            // $table->string('complement')->nullable();
            // $table->string('zipcode')->nullable();
            // $table->string('city')->nullable();
            // $table->string('state')->nullable();
            
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
