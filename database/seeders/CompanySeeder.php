<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // The company answering for every request that no subdomain reaches.
        Company::updateOrCreate(
            ['id' => Company::FALLBACK_ID],
            [
                'name' => 'OAB - CE',
                'slug' => 'oabce',
                'site_title' => 'Link de Pagamento',
                'status' => true,
            ]
        );
    }
}
