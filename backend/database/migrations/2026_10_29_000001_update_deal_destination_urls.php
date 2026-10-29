<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations to update dead/outdated retailer URLs to active, verified destination URLs.
     */
    public function up(): void
    {
        $urlUpdates = [
            'https://www.amazon.in/dp/B0BP9RR1E6' => 'https://www.amazon.in/dp/B0F38D5BH6',
            'https://www.amazon.in/dp/B0CGXB345Y' => 'https://www.amazon.in/dp/B0GYSTL6QT',
            'https://www.amazon.in/dp/B0C39RKQW7' => 'https://www.amazon.in/dp/B0DGPZTGB4',
            'https://www.amazon.in/dp/B0BHQ49X3K' => 'https://www.amazon.in/dp/B0DWK616GS',
            'https://www.amazon.in/dp/B0CX24J1G1' => 'https://www.amazon.in/dp/B0CX58MTNN',
            'https://www.amazon.in/dp/B0CY5JDF3V' => 'https://www.amazon.in/dp/B0CX1Y49P2',
            'https://www.amazon.in/dp/B0D7D76B1Y' => 'https://www.amazon.in/dp/B0D7Z8FJZ9',
            'https://www.amazon.in/dp/B0B5L2B56Y' => 'https://www.amazon.in/dp/B0B6BLTGTT',
            'https://www.amazon.in/dp/B09CGCB8LL' => 'https://www.amazon.in/dp/B0DS2XNXZB',
            'https://www.amazon.in/dp/B09CCW5XW8' => 'https://www.amazon.in/dp/B0C27SD9YR',
            'https://www.flipkart.com/motorola-edge-50-fusion-marshmallow-blue-128-gb/p/itme74431e679a95' => 'https://www.flipkart.com/motorola-edge-50-fusion/p/itm7d39b15599c7e',
            'https://www.flipkart.com/nothing-phone-2a-5g-black-128-gb/p/itm5a4847e11f1ae' => 'https://www.flipkart.com/nothing-phone-2a-5g-black-128-gb/p/itm85c6bca5edadc',
        ];

        foreach ($urlUpdates as $oldUrl => $newUrl) {
            DB::table('deals')->where('url', $oldUrl)->update([
                'url' => $newUrl,
                'updated_at' => now(),
            ]);
        }

        // Also update deals by title keywords if URL wasn't exact match
        DB::table('deals')->where('title', 'like', '%ECOFLOW DELTA 2%')->update(['url' => 'https://www.amazon.in/dp/B0F38D5BH6']);
        DB::table('deals')->where('title', 'like', '%Samsung Galaxy M35%')->update(['url' => 'https://www.amazon.in/dp/B0D7Z8FJZ9']);
        DB::table('deals')->where('title', 'like', '%Realme Narzo 70 Pro%')->update(['url' => 'https://www.amazon.in/dp/B0CX1Y49P2']);
        DB::table('deals')->where('title', 'like', '%OnePlus Nord CE4%')->update(['url' => 'https://www.amazon.in/dp/B0CX58MTNN']);
        DB::table('deals')->where('title', 'like', '%Fitkit%')->update(['url' => 'https://www.amazon.in/dp/B0GYSTL6QT']);
        DB::table('deals')->where('title', 'like', '%ZEBRONICS Juke BAR%')->update(['url' => 'https://www.amazon.in/dp/B0DGPZTGB4']);
        DB::table('deals')->where('title', 'like', '%Motorola Edge 50%')->update(['url' => 'https://www.flipkart.com/motorola-edge-50-fusion/p/itm7d39b15599c7e']);
        DB::table('deals')->where('title', 'like', '%Nothing Phone (2a)%')->update(['url' => 'https://www.flipkart.com/nothing-phone-2a-5g-black-128-gb/p/itm85c6bca5edadc']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversible no-op
    }
};
