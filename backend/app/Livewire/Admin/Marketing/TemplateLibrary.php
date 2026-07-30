<?php

namespace App\Livewire\Admin\Marketing;

use Livewire\Component;

class TemplateLibrary extends Component
{
    public string $search = '';
    public string $category = 'all';

    public function getCategories()
    {
        return [
            'all' => 'All Templates',
            'ecommerce' => 'E-Commerce & Deals',
            'newsletters' => 'Newsletters',
            'transactional' => 'Transactional',
            'seasonal' => 'Seasonal & Holidays'
        ];
    }

    public function getTemplates()
    {
        // 20+ predesigned templates mock data
        $templates = [
            // E-Commerce & Deals
            ['id' => 1, 'name' => 'Flash Sale Alert', 'category' => 'ecommerce', 'thumbnail' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?q=80&w=300&auto=format&fit=crop', 'type' => 'Email', 'tags' => ['Urgency', 'Sale']],
            ['id' => 2, 'name' => 'Deal of the Day', 'category' => 'ecommerce', 'thumbnail' => 'https://images.unsplash.com/photo-1528825871115-3581a5387919?q=80&w=300&auto=format&fit=crop', 'type' => 'Email', 'tags' => ['Daily', 'Conversion']],
            ['id' => 3, 'name' => 'Abandoned Cart Recovery', 'category' => 'ecommerce', 'thumbnail' => 'https://images.unsplash.com/photo-1555529733-0e670560f7e1?q=80&w=300&auto=format&fit=crop', 'type' => 'Email', 'tags' => ['Automated', 'Recovery']],
            ['id' => 4, 'name' => 'New Product Arrival', 'category' => 'ecommerce', 'thumbnail' => 'https://images.unsplash.com/photo-1472851294608-062f824d29cc?q=80&w=300&auto=format&fit=crop', 'type' => 'Email', 'tags' => ['Launch', 'Retail']],
            ['id' => 5, 'name' => 'Exclusive VIP Offer', 'category' => 'ecommerce', 'thumbnail' => 'https://images.unsplash.com/photo-1601924994987-69e26d50dc26?q=80&w=300&auto=format&fit=crop', 'type' => 'Email', 'tags' => ['VIP', 'Premium']],
            ['id' => 6, 'name' => 'Clearance Blowout', 'category' => 'ecommerce', 'thumbnail' => 'https://images.unsplash.com/photo-1534452203293-494d7ddbf7e0?q=80&w=300&auto=format&fit=crop', 'type' => 'Email', 'tags' => ['Clearance', 'Discount']],

            // Newsletters
            ['id' => 7, 'name' => 'Weekly Digest (Modern)', 'category' => 'newsletters', 'thumbnail' => 'https://images.unsplash.com/photo-1503694978374-8a2fa686963a?q=80&w=300&auto=format&fit=crop', 'type' => 'Email', 'tags' => ['Weekly', 'News']],
            ['id' => 8, 'name' => 'Monthly Highlights', 'category' => 'newsletters', 'thumbnail' => 'https://images.unsplash.com/photo-1512486130939-2c4f79935e4f?q=80&w=300&auto=format&fit=crop', 'type' => 'Email', 'tags' => ['Monthly', 'Roundup']],
            ['id' => 9, 'name' => 'Tech Gadgets Roundup', 'category' => 'newsletters', 'thumbnail' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?q=80&w=300&auto=format&fit=crop', 'type' => 'Email', 'tags' => ['Tech', 'Curated']],
            ['id' => 10, 'name' => 'Fashion & Lifestyle', 'category' => 'newsletters', 'thumbnail' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=300&auto=format&fit=crop', 'type' => 'Email', 'tags' => ['Fashion', 'Editorial']],

            // Transactional
            ['id' => 11, 'name' => 'Order Confirmation', 'category' => 'transactional', 'thumbnail' => 'https://images.unsplash.com/photo-1586769852044-692d6e3703f0?q=80&w=300&auto=format&fit=crop', 'type' => 'Email', 'tags' => ['Receipt', 'Order']],
            ['id' => 12, 'name' => 'Shipping Update', 'category' => 'transactional', 'thumbnail' => 'https://images.unsplash.com/photo-1580674285054-bed31e145f59?q=80&w=300&auto=format&fit=crop', 'type' => 'Email', 'tags' => ['Logistics', 'Status']],
            ['id' => 13, 'name' => 'Password Reset', 'category' => 'transactional', 'thumbnail' => 'https://images.unsplash.com/photo-1614064641913-6b714155b1f5?q=80&w=300&auto=format&fit=crop', 'type' => 'Email', 'tags' => ['Security', 'Account']],
            ['id' => 14, 'name' => 'Welcome to Platform', 'category' => 'transactional', 'thumbnail' => 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?q=80&w=300&auto=format&fit=crop', 'type' => 'Email', 'tags' => ['Onboarding', 'Welcome']],

            // Seasonal
            ['id' => 15, 'name' => 'Black Friday Mega', 'category' => 'seasonal', 'thumbnail' => 'https://images.unsplash.com/photo-1604323214371-2ed1eb24bb9a?q=80&w=300&auto=format&fit=crop', 'type' => 'Email', 'tags' => ['Black Friday', 'Q4']],
            ['id' => 16, 'name' => 'Cyber Monday Tech', 'category' => 'seasonal', 'thumbnail' => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?q=80&w=300&auto=format&fit=crop', 'type' => 'Email', 'tags' => ['Cyber Monday', 'Tech']],
            ['id' => 17, 'name' => 'Christmas Gifting', 'category' => 'seasonal', 'thumbnail' => 'https://images.unsplash.com/photo-1513201099705-a9746e1e201f?q=80&w=300&auto=format&fit=crop', 'type' => 'Email', 'tags' => ['Holiday', 'Christmas']],
            ['id' => 18, 'name' => 'New Year Refresh', 'category' => 'seasonal', 'thumbnail' => 'https://images.unsplash.com/photo-1545642412-f174092b7245?q=80&w=300&auto=format&fit=crop', 'type' => 'Email', 'tags' => ['New Year', 'Resolutions']],
            ['id' => 19, 'name' => 'Valentine\'s Special', 'category' => 'seasonal', 'thumbnail' => 'https://images.unsplash.com/photo-1518199266791-5375a83190b7?q=80&w=300&auto=format&fit=crop', 'type' => 'Email', 'tags' => ['Valentines', 'Gifts']],
            ['id' => 20, 'name' => 'Summer Sale Starts', 'category' => 'seasonal', 'thumbnail' => 'https://images.unsplash.com/photo-1536785863268-b39faeeb0008?q=80&w=300&auto=format&fit=crop', 'type' => 'Email', 'tags' => ['Summer', 'Apparel']],
        ];

        return collect($templates)
            ->filter(function ($template) {
                $categoryMatch = $this->category === 'all' || $template['category'] === $this->category;
                $searchMatch = empty($this->search) || stripos($template['name'], $this->search) !== false;
                return $categoryMatch && $searchMatch;
            })
            ->values()
            ->toArray();
    }

    public function selectTemplate($id)
    {
        // Mock action for template selection
        session()->flash('message', "Template #{$id} selected and ready for editing.");
    }

    public function render()
    {
        return view('livewire.admin.marketing.template-library', [
            'categories' => $this->getCategories(),
            'templates' => $this->getTemplates()
        ]);
    }
}
