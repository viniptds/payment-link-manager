<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        // DB::statement('alter table gateway_operations drop constraint `gateway_operations_payment_id_foreign`');
        // DB::statement('alter table gateway_operations drop key  `gateway_operations_payment_id_foreign`');
        // DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // DB::statement("ALTER TABLE gateway_operations ADD CONSTRAINT `gateway_operations_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE");
        
        // DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
