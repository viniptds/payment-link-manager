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
        \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('123123123'),
        ]);

        \App\Models\Payment::factory(10)->create();
        \App\Models\Payment::factory()->count(3)->for(\App\Models\Customer::factory()->state([
            'name' => 'Vinicius'
        ]))->create();
    }
}
