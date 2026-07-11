<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Merchant;

class CourseMerchantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $merchants = [
            [
                'name' => 'Udemy',
                'domain' => 'udemy.com',
                'store_id' => 'latestdeal', // Placeholder affiliate tag
                'affiliate_param_key' => 'source',
                'status' => true,
            ],
            [
                'name' => 'Coursera',
                'domain' => 'coursera.org',
                'store_id' => 'latestdeal', // Placeholder affiliate tag
                'affiliate_param_key' => 'source',
                'status' => true,
            ]
        ];

        foreach ($merchants as $merchantData) {
            Merchant::firstOrCreate(
                ['domain' => $merchantData['domain']],
                $merchantData
            );
        }
    }
}
