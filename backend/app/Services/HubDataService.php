<?php

namespace App\Services;

class HubDataService
{
    /**
     * Returns rich editorial content (intro, faqs, schema) for specific slugs.
     * This acts as the "Topic Clusters" and "Search Hubs" layer (Phase 2).
     */
    public function getHubData($type, $slug)
    {
        $hubs = [
            'merchant' => [
                'amazon' => [
                    'title' => 'Amazon Deals, Coupons & Price Drops',
                    'intro' => 'Discover the best verified Amazon deals today. Our AI continuously tracks Amazon price histories to ensure you never pay full price. We filter out fake discounts and highlight true price drops on electronics, home appliances, and daily essentials.',
                    'faqs' => [
                        ['question' => 'How do you find the best Amazon deals?', 'answer' => 'Our proprietary bots scan Amazon India 24/7. When a price drops significantly below its 90-day average, our AI flags it, and our human editors verify the discount before publishing.'],
                        ['question' => 'Are these Amazon coupons verified?', 'answer' => 'Yes, every coupon code listed is manually tested by our team to ensure it works at checkout.'],
                        ['question' => 'What is the best time to shop on Amazon?', 'answer' => 'While Great Indian Festival sales offer huge discounts, our tracker finds hidden clearance deals and lightning deals every single day at midnight.'],
                    ]
                ],
                'flipkart' => [
                    'title' => 'Flipkart Offers & Big Billion Day Deals',
                    'intro' => 'Get real-time alerts on the best Flipkart deals. We monitor Flipkart\'s vast catalog to bring you genuine discounts on smartphones, laptops, and fashion. Never miss a Big Saving Days sale again.',
                    'faqs' => [
                        ['question' => 'Does Flipkart offer price tracking?', 'answer' => 'Flipkart does not natively show price history. LatestDeal provides 6-month historical price charts so you can verify if a discount is genuine.'],
                        ['question' => 'Are Bank Offers included in the deal price?', 'answer' => 'When applicable, our deal descriptions highlight extra bank discounts (like SBI or HDFC credit card offers) to maximize your savings.'],
                    ]
                ]
            ],
            'category' => [
                'electronics' => [
                    'title' => 'Top Electronics Deals & Gadget Discounts',
                    'intro' => 'Upgrade your tech without breaking the bank. From the latest smartphones to 4K smart TVs, our Electronics Hub aggregates the most aggressive price drops across the web. Every gadget deal is vetted for seller authenticity and warranty validity.',
                    'faqs' => [
                        ['question' => 'How do I know the electronics seller is genuine?', 'answer' => 'Our AI Trust Score penalizes third-party sellers with low ratings. We prioritize deals fulfilled directly by Amazon, Flipkart, or authorized brand stores.'],
                        ['question' => 'Do these deals include warranty?', 'answer' => 'Yes, we only list brand-new products with standard manufacturer warranties unless explicitly labeled as "Refurbished" in the title.'],
                    ]
                ],
                'smartphones' => [
                    'title' => 'Smartphone Deals: Apple, Samsung & More',
                    'intro' => 'Never overpay for a mobile phone. Track massive price cuts on iPhones, Samsung Galaxy devices, and budget champions from Xiaomi and realme. We cross-reference launch prices with current offers to find true steals.',
                    'faqs' => [
                        ['question' => 'When do iPhones go on sale?', 'answer' => 'iPhones see their biggest price drops in September (when new models launch) and during Diwali sales. However, we often catch random $100-$150 drops throughout the year.'],
                        ['question' => 'Are exchange offers included?', 'answer' => 'We display the flat cash discount. Exchange offers and bank discounts are mentioned as additional savings in the deal details.'],
                    ]
                ],
                'home-kitchen' => [
                    'title' => 'Home & Kitchen Appliances Deals',
                    'intro' => 'Transform your home with our curated list of home and kitchen deals. From air fryers and robotic vacuums to mattresses and furniture, find verified discounts from trusted brands like Philips, Dyson, and LG.',
                    'faqs' => [
                        ['question' => 'What are the best kitchen appliance brands?', 'answer' => 'We frequently feature verified deals from top-rated brands including Philips, Morphy Richards, Panasonic, and Bosch.'],
                    ]
                ]
            ]
        ];

        return $hubs[$type][$slug] ?? null;
    }
}
