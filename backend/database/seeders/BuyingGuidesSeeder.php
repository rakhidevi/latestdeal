<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;

class BuyingGuidesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure editorial author exists
        $author = User::first();
        if (!$author) {
            $author = User::create([
                'name' => 'LatestDeal Editorial Team',
                'email' => 'editorial@latestdeal.in',
                'password' => bcrypt(\Illuminate\Support\Str::random(16)),
                'role' => 'admin',
            ]);
        }

        // 2. Ensure relevant categories exist
        $categories = [
            'electronics' => Category::firstOrCreate(['slug' => 'electronics'], ['name' => 'Electronics']),
            'home-kitchen' => Category::firstOrCreate(['slug' => 'home-kitchen'], ['name' => 'Home & Kitchen']),
            'shopping-tips' => Category::firstOrCreate(['slug' => 'shopping-tips'], ['name' => 'Shopping Tips']),
        ];

        $guides = [
            [
                'title' => 'How to Spot Fake Discounts & Inflated MRPs on Amazon (2026 Edition)',
                'slug' => 'how-to-spot-fake-discounts-inflated-mrp-guide',
                'category_id' => $categories['shopping-tips']->id,
                'featured_image' => 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=1200&auto=format&fit=crop&q=80',
                'summary' => 'Don\'t fall for the 80% off illusion. Learn how merchants manipulate strike-through prices, how to verify real all-time lows, and how to spot legitimate price drops.',
                'content' => '
<div class="space-y-6">
    <p class="lead text-lg font-medium text-slate-700 dark:text-slate-300">
        Every major online sale advertises "jaw-dropping discounts of up to 80% off." Yet seasoned deal hunters know that a high discount percentage often hides an artificial list price (MRP) rather than genuine savings. Here is our data-backed blueprint on separating authentic flash deals from price illusions.
    </p>

    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">1. The Strike-Through Price Illusion</h2>
    <p>
        The most common marketing tactic on Indian e-commerce platforms is <strong>artificial reference pricing</strong>. A retailer lists a pair of wireless earbuds with an arbitrary Maximum Retail Price (MRP) of ₹4,999 and sells them permanently at ₹999, claiming an "80% Discount." In reality, the product was never sold at ₹4,999 anywhere on earth.
    </p>
    <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200 text-sm">
        <strong>Rule of Thumb:</strong> Never assess a deal by its discount percentage. Always judge the deal by its <em>absolute selling price</em> relative to historical market benchmarks.
    </div>

    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">2. The 30-Day Pre-Sale Price Hike</h2>
    <p>
        During major events like festive sales, algorithmic sellers often gradually bump the selling price 2 to 3 weeks prior to the event. When sale week launches, the price drops back to the standard regular price, labeled as a "Great Festive Deal."
    </p>
    <ul class="list-disc pl-6 space-y-2">
        <li><strong>Step 1:</strong> Always examine the 90-day price history curve before pulling the trigger.</li>
        <li><strong>Step 2:</strong> Check price trends on competing platforms like Flipkart, Croma, and Reliance Digital.</li>
        <li><strong>Step 3:</strong> Watch out for bundled "filler" items that artificially inflate bundle MRPs.</li>
    </ul>

    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">3. The 3-Step Verification Formula</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm border-collapse border border-slate-200 dark:border-slate-700 my-4">
            <thead>
                <tr class="bg-slate-100 dark:bg-slate-800">
                    <th class="p-3 border border-slate-200 dark:border-slate-700 font-bold">Signal</th>
                    <th class="p-3 border border-slate-200 dark:border-slate-700 font-bold">Red Flag (Fake Discount)</th>
                    <th class="p-3 border border-slate-200 dark:border-slate-700 font-bold">Green Flag (Real Deal)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="p-3 border border-slate-200 dark:border-slate-700 font-medium">Historical Low</td>
                    <td class="p-3 border border-slate-200 dark:border-slate-700">Price is identical to last month\'s everyday price</td>
                    <td class="p-3 border border-slate-200 dark:border-slate-700">Price is lower than the 60-day moving average</td>
                </tr>
                <tr>
                    <td class="p-3 border border-slate-200 dark:border-slate-700 font-medium">Coupon Stacking</td>
                    <td class="p-3 border border-slate-200 dark:border-slate-700">Coupon just offsets a recent price increase</td>
                    <td class="p-3 border border-slate-200 dark:border-slate-700">Coupon applies on top of an already discounted baseline</td>
                </tr>
                <tr>
                    <td class="p-3 border border-slate-200 dark:border-slate-700 font-medium">Multi-Retailer Check</td>
                    <td class="p-3 border border-slate-200 dark:border-slate-700">Only 1 seller has the item with no comparison</td>
                    <td class="p-3 border border-slate-200 dark:border-slate-700">Major verified retailers show price match or drop</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Conclusion</h2>
    <p>
        Smart shopping isn\'t about chasing huge discount badges. It\'s about verifiable pricing history and cross-retailer intelligence. Use LatestDeal\'s live price verifier and historical tracking tools to guarantee you never overpay.
    </p>
</div>',
                'published_at' => Carbon::now()->subDays(14),
                'seo_metadata' => [
                    'title' => 'How to Spot Fake Discounts & Inflated MRPs on Amazon (2026 Edition)',
                    'description' => 'A comprehensive guide to identifying artificial discounts, inflated MRPs, and price manipulation tactics on Indian e-commerce sites.',
                    'keywords' => 'fake discounts, Amazon MRP trick, price history, price tracker india, deal verification',
                ],
            ],
            [
                'title' => 'Smart TV Buying Guide: 4K, OLED, QLED & What Truly Matters',
                'slug' => 'smart-tv-buying-guide-4k-oled-qled',
                'category_id' => $categories['electronics']->id,
                'featured_image' => 'https://images.unsplash.com/photo-1593784991095-a205069470b6?w=1200&auto=format&fit=crop&q=80',
                'summary' => 'Confused between OLED, QLED, and Mini-LED? Don\'t waste money on marketing buzzwords. Here is what panel technology, refresh rate, and OS you actually need.',
                'content' => '
<div class="space-y-6">
    <p class="lead text-lg font-medium text-slate-700 dark:text-slate-300">
        Choosing a Smart TV today is a minefield of acronyms: OLED, QLED, Neo-QLED, Mini-LED, Dolby Vision IQ, and HDMI 2.1. In this guide, we break down what specifications actually impact your living room viewing experience and what price points offer the highest value.
    </p>

    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">1. Panel Technology: OLED vs QLED vs LED</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 my-4">
        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
            <h3 class="font-bold text-base text-slate-900 dark:text-white mb-2">OLED (Organic LED)</h3>
            <p class="text-sm text-slate-600 dark:text-slate-300">Each pixel emits its own light. Delivers infinite contrast and perfect blacks. Best for dark room movie lovers, but premium priced and moderately dimmer in sunlight.</p>
        </div>
        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
            <h3 class="font-bold text-base text-slate-900 dark:text-white mb-2">QLED (Quantum Dot LED)</h3>
            <p class="text-sm text-slate-600 dark:text-slate-300">An LED backlit TV with quantum dot color filtering. Extremely bright (ideal for bright living rooms with windows), vivid colors, and zero burn-in risk at mid-range pricing.</p>
        </div>
    </div>

    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">2. Resolution & Refresh Rates: 60Hz vs 120Hz</h2>
    <p>
        For movies, sports, and streaming OTT (Netflix, Prime, Hotstar), a true 4K UHD 60Hz panel with MEMC (motion smoothing) is more than sufficient. You only need a native <strong>120Hz or 144Hz panel with HDMI 2.1</strong> if you own a PlayStation 5, Xbox Series X, or a modern gaming PC for 4K@120fps gaming.
    </p>

    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">3. Operating System Showdown</h2>
    <ul class="list-disc pl-6 space-y-2">
        <li><strong>Google TV:</strong> Best app catalog, native Chromecast, personalized suggestions, and smooth voice integration. (Sony, TCL, Xiaomi)</li>
        <li><strong>LG webOS:</strong> Clean cursor-based Magic Remote interface, snappy multitasking, and exceptional Apple AirPlay 2 support.</li>
        <li><strong>Samsung Tizen OS:</strong> Extensive Samsung ecosystem synergy and Samsung TV Plus free channels, though occasionally heavy on sponsored promotions.</li>
    </ul>

    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Target Budget Recommendations</h2>
    <p>
        For a 55-inch display in India, the sweet spot for budget 4K LED is <strong>₹28,000 to ₹35,000</strong>. For premium QLED, budget between <strong>₹45,000 and ₹65,000</strong>. For flagship OLED panels, wait for festive price drops below <strong>₹90,000</strong>.
    </p>
</div>',
                'published_at' => Carbon::now()->subDays(11),
                'seo_metadata' => [
                    'title' => 'Smart TV Buying Guide: 4K, OLED, QLED & What Truly Matters',
                    'description' => 'Everything you need to know before buying a smart TV in India: OLED vs QLED comparison, HDR standards, refresh rates, and budget recommendations.',
                    'keywords' => 'smart tv guide, oled vs qled, best 4k tv india, 55 inch smart tv deals',
                ],
            ],
            [
                'title' => 'Best Noise Cancelling Headphones in India: Real Price & ANC Breakdown',
                'slug' => 'best-anc-headphones-buying-guide-india',
                'category_id' => $categories['electronics']->id,
                'featured_image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=1200&auto=format&fit=crop&q=80',
                'summary' => 'Active Noise Cancellation (ANC) can silence plane cabins and bustling offices. We compare flagship vs budget noise cancelling technology and show you the best buying prices.',
                'content' => '
<div class="space-y-6">
    <p class="lead text-lg font-medium text-slate-700 dark:text-slate-300">
        Whether you are flying frequently, working in a noisy open office, or commuting on metro trains, high-performance Active Noise Cancellation is life-changing. But do you really need to spend ₹30,000 when sub-₹8,000 models now offer dual-feed ANC?
    </p>

    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">How Modern ANC Works</h2>
    <p>
        Passive noise isolation blocks sound through physical padding. Active Noise Cancellation uses exterior and interior microphones to sample environmental frequencies, generating inverted sound waves (anti-phase) to mathematically cancel incoming noise before it reaches your eardrum.
    </p>

    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Flagship vs Mid-Range Comparison</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm border-collapse border border-slate-200 dark:border-slate-700 my-4">
            <thead>
                <tr class="bg-slate-100 dark:bg-slate-800">
                    <th class="p-3 border border-slate-200 dark:border-slate-700 font-bold">Tier</th>
                    <th class="p-3 border border-slate-200 dark:border-slate-700 font-bold">Key Models</th>
                    <th class="p-3 border border-slate-200 dark:border-slate-700 font-bold">ANC Effectiveness</th>
                    <th class="p-3 border border-slate-200 dark:border-slate-700 font-bold">Target Deal Price</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="p-3 border border-slate-200 dark:border-slate-700 font-medium">Flagship</td>
                    <td class="p-3 border border-slate-200 dark:border-slate-700">Sony WH-1000XM5, Bose QuietComfort Ultra</td>
                    <td class="p-3 border border-slate-200 dark:border-slate-700">98% (Silences low drone + voices)</td>
                    <td class="p-3 border border-slate-200 dark:border-slate-700">₹24,990 – ₹28,990</td>
                </tr>
                <tr>
                    <td class="p-3 border border-slate-200 dark:border-slate-700 font-medium">Value Pro</td>
                    <td class="p-3 border border-slate-200 dark:border-slate-700">Sony WH-1000XM4, Sennheiser Accentum Plus</td>
                    <td class="p-3 border border-slate-200 dark:border-slate-700">92% (Foldable, exceptional audio)</td>
                    <td class="p-3 border border-slate-200 dark:border-slate-700">₹17,990 – ₹19,990</td>
                </tr>
                <tr>
                    <td class="p-3 border border-slate-200 dark:border-slate-700 font-medium">Budget Champion</td>
                    <td class="p-3 border border-slate-200 dark:border-slate-700">Soundcore Space One, JBL Live 770NC</td>
                    <td class="p-3 border border-slate-200 dark:border-slate-700">80% (Handles transit rumble well)</td>
                    <td class="p-3 border border-slate-200 dark:border-slate-700">₹6,499 – ₹7,999</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Key Features to Check Before Buying</h2>
    <ul class="list-disc pl-6 space-y-2">
        <li><strong>Multipoint Bluetooth:</strong> Crucial for seamless switching between your laptop and smartphone.</li>
        <li><strong>Hi-Res Audio Codecs:</strong> LDAC or aptX Adaptive provides significantly higher bitrate on Android devices.</li>
        <li><strong>Comfort & Weight:</strong> Sub-260g weight and plush ear cushions prevent fatigue during 4+ hour listening sessions.</li>
    </ul>
</div>',
                'published_at' => Carbon::now()->subDays(8),
                'seo_metadata' => [
                    'title' => 'Best Noise Cancelling Headphones in India: Real Price & ANC Breakdown',
                    'description' => 'Compare the top ANC headphones in India. Learn the difference between Sony, Bose, and budget ANC alternatives with target buying prices.',
                    'keywords' => 'best anc headphones, sony wh1000xm4 price, bose quietcomfort deals, noise cancelling headphones india',
                ],
            ],
            [
                'title' => 'Robot Vacuum Cleaners in India: Mopping, Suction & Pet Hair Tested',
                'slug' => 'robot-vacuum-cleaners-buying-guide-india',
                'category_id' => $categories['home-kitchen']->id,
                'featured_image' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=1200&auto=format&fit=crop&q=80',
                'summary' => 'Can a robot vacuum handle Indian marble floors, sticky kitchen spices, and pet hair? We break down LiDAR mapping, suction ratings, and maintenance costs.',
                'content' => '
<div class="space-y-6">
    <p class="lead text-lg font-medium text-slate-700 dark:text-slate-300">
        Indian homes present a unique challenge for automated cleaning: tiled and marble flooring, dust accumulation, low furniture obstacles, and daily cooking splatters. Here is everything you need to know before buying a robot vacuum in India.
    </p>

    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">1. Navigation: LiDAR vs Optical Camera</h2>
    <p>
        Always avoid entry-level "bump-and-turn" gyroscopic models that clean randomly. Insist on <strong>Laser LiDAR navigation (LDS)</strong>. LiDAR creates millimeter-accurate floor plans in minutes, functions flawlessly in pitch-dark rooms, and allows setting virtual no-go zones around pooja rooms and wire clusters.
    </p>

    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">2. Suction Power Benchmarks</h2>
    <p>
        In Indian environments with open balconies and fan dust, minimum <strong>3,000 Pa</strong> suction is recommended for hard floors, and <strong>4,000 to 6,000 Pa</strong> if your house has rugs, door mats, or heavy shedding pets.
    </p>

    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">3. Wet Mopping Realities</h2>
    <p>
        A passive microfiber drag pad will not remove dried curry stains or oil films. Look for units equipped with <strong>dual rotating mop pads or sonic vibrating mop modules (e.g. Roborock VibraRise, Dreame L10 series, ECOVACS)</strong> that apply downward pressure against the floor.
    </p>

    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Maintenance & Consumable Costs</h2>
    <p>
        Factor in ₹2,500 to ₹3,500 annually for replacement HEPA filters, side brushes, and microfiber mopping pads. Self-emptying dock stations save immense daily effort if you are away from home during workday cleaning schedules.
    </p>
</div>',
                'published_at' => Carbon::now()->subDays(6),
                'seo_metadata' => [
                    'title' => 'Robot Vacuum Cleaners in India: Mopping, Suction & Pet Hair Tested',
                    'description' => 'Comprehensive buying guide for robot vacuums in India. LiDAR vs camera navigation, mopping effectiveness on marble, and maintenance costs.',
                    'keywords' => 'robot vacuum cleaner india, roborock dreame ecovacs, robot mop for indian homes, robotic vacuum deals',
                ],
            ],
            [
                'title' => 'The Ultimate Price Tracking Guide: How to Buy at All-Time Lows',
                'slug' => 'ultimate-price-tracking-guide-all-time-lows',
                'category_id' => $categories['shopping-tips']->id,
                'featured_image' => 'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?w=1200&auto=format&fit=crop&q=80',
                'summary' => 'Stop overpaying. Learn how algorithmic price tracking works, how to stack bank discount credit cards, and how to set automated instant price alerts.',
                'content' => '
<div class="space-y-6">
    <p class="lead text-lg font-medium text-slate-700 dark:text-slate-300">
        Prices on e-commerce giants fluctuate dozens of times every week. Amazon and Flipkart use dynamic pricing algorithms that react to search velocity, competing inventory levels, and even time of day. Here is how you can use data intelligence to turn the tables in your favor.
    </p>

    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">1. Understand Dynamic Price Cycles</h2>
    <p>
        Major consumer electronics like smartphones, laptops, and tablets follow predictable price decay curves:
    </p>
    <ul class="list-disc pl-6 space-y-2">
        <li><strong>Launch to 90 Days:</strong> Strict price discipline; discounts are rare and limited to card cashbacks.</li>
        <li><strong>Month 4 to 8:</strong> First organic price drops occur (typically 12% to 20% off MRP) as secondary distributors compete.</li>
        <li><strong>Month 9 to 12 (Festive Season):</strong> All-time low territory ahead of successor model announcements.</li>
    </ul>

    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">2. The Bank Card Stacking Secret</h2>
    <p>
        The most common way seasoned bargain hunters hit record prices is through <strong>triple-stack savings</strong>:
    </p>
    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-2 text-sm">
        <p><strong>Layer 1:</strong> Organic seller price drop (e.g. ₹2,000 drop)</p>
        <p><strong>Layer 2:</strong> Merchant check-box coupon code (e.g. ₹1,000 voucher at checkout)</p>
        <p><strong>Layer 3:</strong> 10% Instant Bank Discount on HDFC / ICICI / SBI credit cards (up to ₹1,500)</p>
        <p class="font-bold text-emerald-600 dark:text-emerald-400">Total Effective Savings: ₹4,500 below standard street price.</p>
    </div>

    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">3. Automated Price Tracking</h2>
    <p>
        You do not need to refresh product pages all day. By adding items to your LatestDeal Watchlist or setting instant Telegram price alerts, our local price monitoring engine notifies you within seconds of a price crater.
    </p>
</div>',
                'published_at' => Carbon::now()->subDays(4),
                'seo_metadata' => [
                    'title' => 'The Ultimate Price Tracking Guide: How to Buy at All-Time Lows',
                    'description' => 'Master the art of price tracking. Understand algorithmic price fluctuations, festive sale cycles, and bank card stacking.',
                    'keywords' => 'price tracking guide, buy at all-time low, amazon price drops, telegram deal alert',
                ],
            ],
            [
                'title' => 'Air Fryer Buying Guide: Capacity, Wattage, and Healthy Cooking Facts',
                'slug' => 'air-fryer-buying-guide-capacity-wattage-facts',
                'category_id' => $categories['home-kitchen']->id,
                'featured_image' => 'https://images.unsplash.com/photo-1584992236310-6edddc08acff?w=1200&auto=format&fit=crop&q=80',
                'summary' => 'Is an air fryer worth the kitchen counter space? Discover how convection cooking works, compare basket capacities from 3L to 6.5L, and learn real power consumption.',
                'content' => '
<div class="space-y-6">
    <p class="lead text-lg font-medium text-slate-700 dark:text-slate-300">
        Air fryers have transitioned from a kitchen fad into an essential countertop appliance. By combining a high-output heating element with a rapid convection fan, an air fryer circulates superheated air around foods, delivering crispy textures using up to 90% less oil than deep frying.
    </p>

    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">1. Capacity Guide for Indian Households</h2>
    <ul class="list-disc pl-6 space-y-2">
        <li><strong>Small (2.5L to 3.5L):</strong> Ideal for bachelors or couples. Can handle 1-2 chicken breasts or a small bowl of french fries, but requires batch cooking for snacks like samosas or paneer tikka.</li>
        <li><strong>Medium (4L to 5.5L):</strong> The sweet spot for a typical family of 3-4 members. Fits whole tandoori platters, whole fish, or 600g of cut vegetables comfortably.</li>
        <li><strong>Large / Dual-Zone (6L to 9L):</strong> Ideal for larger families and party entertaining. Dual baskets allow cooking mains and side dishes at different temperatures simultaneously.</li>
    </ul>

    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">2. Wattage & Power Efficiency</h2>
    <p>
        Most quality air fryers operate between <strong>1400W and 1800W</strong>. While this sounds substantial, cooking times are drastically shorter than conventional ovens (typically 12-18 minutes instead of 40-50 minutes). A standard cooking session consumes less than 0.35 kWh of electricity, costing roughly ₹2 to ₹3 in most Indian metro cities.
    </p>

    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">3. Non-Stick Coating vs Ceramic Baskets</h2>
    <p>
        Always check the basket material:
    </p>
    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-sm">
        Look for <strong>BPA-free, PFOA-free ceramic or heavy-duty food-grade Teflon</strong> coatings. Dishwasher-safe removable crisper trays significantly speed up post-meal cleanup.
    </div>

    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Top Brands and Price Sweet Spots</h2>
    <p>
        For reliable build quality and heating consistency, look at <strong>Philips (the market benchmark), Instant Pot / Instant Vortex, Pigeon, and Agaro</strong>. Target deal prices: 4L models frequently drop to <strong>₹3,999 – ₹5,499</strong> during flash sales.
    </p>
</div>',
                'published_at' => Carbon::now()->subDays(2),
                'seo_metadata' => [
                    'title' => 'Air Fryer Buying Guide: Capacity, Wattage, and Healthy Cooking Facts',
                    'description' => 'Everything to know before buying an air fryer in India: capacity sizing, wattage consumption, basket coatings, and best festive sale deals.',
                    'keywords' => 'air fryer buying guide, philips air fryer deals, air fryer capacity comparison, healthy cooking appliance',
                ],
            ],
        ];

        foreach ($guides as $data) {
            Article::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'title' => $data['title'],
                    'category_id' => $data['category_id'],
                    'author_id' => $author->id,
                    'featured_image' => $data['featured_image'],
                    'summary' => $data['summary'],
                    'content' => $data['content'],
                    'status' => Article::STATUS_PUBLISHED,
                    'published_at' => $data['published_at'],
                    'seo_metadata' => $data['seo_metadata'],
                ]
            );
        }

        $this->command->info('Seeded ' . count($guides) . ' comprehensive evergreen buying guides successfully.');
    }
}
