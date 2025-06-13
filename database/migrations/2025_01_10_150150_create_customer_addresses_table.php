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
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            
            $table->string('address_line_1');
            $table->string('address_line_2');
            $table->string('complement')->nullable();
            $table->string('number');
            $table->string('zip');
            $table->string('neighborhood');
            $table->string('city');
            $table->string('state');
            $table->string('country');

            $table->string('description');
            $table->boolean('is_active');
            
            $table->uuid('customer_id')->nullable();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('set null'); // Or use cascade if needed

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
