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
            $table->uuid('id')->primary('id');
            $table->string('description');
            // $table->string('gateway');
            $table->float('value', 12, 2);
            $table->enum('status', ['active', 'inactive', 'expired', 'cancelled', 'paid', 'pending'])->default('active');
            $table->date('due_date')->nullable();
            $table->dateTime('expire_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->dateTime('paid_at')->nullable();

            $table->text('transaction_log')->nullable();

            $table->uuid('customer_id')->nullable()->index();
            $table->timestamps();
            $table->foreignUuid('created_by')->nullable()->references('id')->on('users');

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
