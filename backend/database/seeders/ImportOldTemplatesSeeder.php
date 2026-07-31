<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Communications\Template;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImportOldTemplatesSeeder extends Seeder
{
    public function run()
    {
        $filesToImport = [
            'newsletter-welcome.blade.php' => [
                'name' => 'Subscription Confirmed',
                'category' => 'Welcome',
            ],
            'verify-email.blade.php' => [
                'name' => 'Verify Your Email Address',
                'category' => 'System',
            ],
            'shopper-welcome.blade.php' => [
                'name' => 'Welcome to LatestDeal',
                'category' => 'Welcome',
            ],
            'promo-deal-digest.blade.php' => [
                'name' => 'Mega Deals / Hot Deals',
                'category' => 'Promotional',
            ],
        ];

        foreach ($filesToImport as $filename => $meta) {
            $path = resource_path("views/emails/{$filename}");
            if (File::exists($path)) {
                $content = File::get($path);
                
                // Extract everything between @section('content') and @endsection
                if (preg_match("/@section\('content'\)(.*?)@endsection/s", $content, $matches)) {
                    $html = trim($matches[1]);
                } else {
                    $html = trim($content);
                }
                
                if (!empty($html)) {
                    Template::updateOrCreate(
                        ['name' => $meta['name']],
                        [
                            'slug' => Str::slug($meta['name']),
                            'category' => $meta['category'],
                            'is_system' => false,
                            'tags' => ['Imported', 'Legacy'],
                            'blocks' => [
                                [
                                    'id' => uniqid(),
                                    'type' => 'RawHtmlBlock',
                                    'settings' => [
                                        'html' => $html
                                    ]
                                ]
                            ]
                        ]
                    );
                    $this->command->info("Imported: {$meta['name']}");
                }
            } else {
                $this->command->error("File not found: {$filename}");
            }
        }
    }
}
