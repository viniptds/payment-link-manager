<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $companySeeder = new CompanySeeder;
        $companySeeder->run();

        $adminUser = \App\Models\User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'is_admin' => true,
            'password' => Hash::make('123123123'),
        ]);

        $basicUser = \App\Models\User::factory()->create([
            'name' => 'Basic User',
            'email' => 'user@example.com',
            'is_admin' => false,
            'password' => Hash::make('123123123'),
        ]);

        \App\Models\User::factory(10)->create();

        \App\Models\Payment::factory(10)->create();
        \App\Models\Payment::factory()->count(3)->for(\App\Models\Customer::factory()->state([
            'name' => 'Vinicius'
        ]))->create();

        \App\Models\Payment::factory()->count(3)->create([
            'created_by' => $basicUser->id
        ]);

        \App\Models\Settings::factory()->create([
            'id' => 'logo_main',
            'value' => 'logo.png',
            'updated_by' => $adminUser->id
        ]);

        $gatewaySeeder = new GatewaySeeder;
        $gatewaySeeder->run();
    }
}
