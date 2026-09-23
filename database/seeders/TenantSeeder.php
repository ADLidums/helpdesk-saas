<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            ['name' => 'Potato', 'slug' => 'potato'],
            ['name' => 'Tomato', 'slug' => 'tomato'],
        ];

        foreach ($companies as $company) {
            $tenant = Tenant::firstOrCreate(
                ['slug' => $company['slug']],
                ['name' => $company['name']],
            );

            $tenant->users()->firstOrCreate(
                ['email' => 'owner@'.$company['slug'].'.example'],
                [
                    'name' => $company['name'].' Owner',
                    'password' => 'password',
                ],
            );
        }
    }
}
