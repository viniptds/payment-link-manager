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
        Schema::create('gateway_operations', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->foreignUuid('payment_id')->references('id')->on('payments');
            $table->text('log');
            $table->string('type');
            $table->boolean('status');
            $table->string('gateway');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gateway_operations');
    }
};
