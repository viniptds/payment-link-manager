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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary('id');;
            $table->string('description');
            $table->float('value', 12, 2);
            $table->enum('status', ['active', 'inactive', 'expired', 'cancelled', 'paid'])->default('active');
            $table->date('expire_at')->nullable();
            $table->date('cancelled_at')->nullable();
            $table->date('paid_at')->nullable();

            $table->text('transaction_log')->nullable();

            $table->uuid('customer_id')->nullable()->index();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
