<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Deal;
use App\Models\Merchant;
use App\Models\Tag;
use App\Models\PriceHistory;
use App\Models\Category;
use Illuminate\Support\Str;

class RealDealSeeder extends Seeder
{
    public function run(): void
    {
        $amazon = Merchant::updateOrCreate(
            ['name' => 'Amazon India'],
            ['domain' => 'amazon.in', 'affiliate_param_key' => 'tag', 'store_id' => 'latestdeal-21', 'status' => 1]
        );

        $flipkart = Merchant::updateOrCreate(
            ['name' => 'Flipkart'],
            ['domain' => 'flipkart.com', 'affiliate_param_key' => 'affid', 'store_id' => 'latestdeal', 'status' => 1]
        );

        $croma = Merchant::updateOrCreate(
            ['name' => 'Croma'],
            ['domain' => 'croma.com', 'affiliate_param_key' => 'affid', 'store_id' => 'latestdeal', 'status' => 1]
        );

        $reliance = Merchant::updateOrCreate(
            ['name' => 'Reliance Digital'],
            ['domain' => 'reliancedigital.in', 'affiliate_param_key' => 'affid', 'store_id' => 'latestdeal', 'status' => 1]
        );

        $myntra = Merchant::updateOrCreate(
            ['name' => 'Myntra'],
            ['domain' => 'myntra.com', 'affiliate_param_key' => 'affid', 'store_id' => 'latestdeal', 'status' => 1]
        );

        $merchantsMap = [
            'Amazon India' => $amazon,
            'Flipkart' => $flipkart,
            'Croma' => $croma,
            'Reliance Digital' => $reliance,
            'Myntra' => $myntra,
        ];

        $categoriesMap = [
            'mobiles' => Category::firstOrCreate(['slug' => 'mobiles'], ['name' => 'Mobiles']),
            'electronics' => Category::firstOrCreate(['slug' => 'electronics'], ['name' => 'Electronics']),
            'home-kitchen' => Category::firstOrCreate(['slug' => 'home-kitchen'], ['name' => 'Home & Kitchen']),
            'sports-fitness' => Category::firstOrCreate(['slug' => 'sports-fitness'], ['name' => 'Sports & Fitness']),
            'fashion-accessories' => Category::firstOrCreate(['slug' => 'fashion-accessories'], ['name' => 'Fashion & Accessories']),
            'beauty-personal-care' => Category::firstOrCreate(['slug' => 'beauty-personal-care'], ['name' => 'Beauty & Personal Care']),
            'gaming' => Category::firstOrCreate(['slug' => 'gaming'], ['name' => 'Gaming']),
        ];

        $dealsData = [
            [
                'title' => "EF ECOFLOW DELTA 2 Portable Power Station | 1024Wh LiFePO4 Battery",
                'original_price' => 105000,
                'discounted_price' => 65990,
                'category_slug' => "home-kitchen",
                'merchant_name' => "Amazon India",
                'url' => "https://www.amazon.in/dp/B0F38D5BH6",
                'image_path' => "https://m.media-amazon.com/images/I/51Z9aXPGFCL._SL1500_.jpg",
                'status' => "active",
                'tags' => ["Home & Kitchen", "Power Station", "EF ECOFLOW"],
                'editorial_summary' => "The EcoFlow Delta 2 represents one of the most reliable portable LiFePO4 power stations on the market. With 1024Wh capacity expandable up to 3kWh and 1800W pure sine wave AC output (2700W surge with X-Boost), it effortlessly powers heavy home appliances including refrigerators, power tools, and CPAP machines during outages. Its standout capability is ultra-fast AC recharging from 0 to 80% in just 50 minutes, paired with 3,000+ battery cycle longevity.",
                'editorial_verdict' => "A premium, fast-charging emergency power backup and outdoor workstation with industry-leading battery chemistry.",
                'pros' => ["Ultra-fast 0-80% AC recharging in 50 minutes", "LiFePO4 battery rated for 3,000+ full cycles to 80% capacity", "1800W continuous output handles 90% of household appliances", "Full remote control and telemetry via EcoFlow Wi-Fi/Bluetooth app"],
                'cons' => ["Cooling fans become audibly noticeable during peak 1800W loads", "At 12 kg, transport requires intentional lifting"],
                'best_for' => ["Home emergency backup during load shedding", "RV travelers", "and outdoor mobile production rigs."],
                'not_for' => ["Users looking for lightweight pocket power banks under 2 kg."],
            ],
            [
                'title' => "Fitkit by Cult Walking Pad Neo (BLDC 3.5HP Peak Power) Compact Under-desk Treadmill",
                'original_price' => 19990,
                'discounted_price' => 7999,
                'category_slug' => "sports-fitness",
                'merchant_name' => "Amazon India",
                'url' => "https://www.amazon.in/dp/B0GYSTL6QT",
                'image_path' => "https://m.media-amazon.com/images/I/81a5TMCCZvL._SL1500_.jpg",
                'status' => "active",
                'tags' => ["Sports & Fitness", "Treadmill", "Fitkit"],
                'editorial_summary' => "The Fitkit Neo Walking Pad addresses the sedentary work routine with an ultra-compact, flat-deck motorized walking track designed to slip directly beneath standing desks or sofas. Powered by an energy-efficient 3.5HP peak brushless DC motor, it supports speeds from 1.0 to 6.0 km/h with a maximum user weight capacity of 110 kg. Integrated wheels make stowing effortless, and the package includes complimentary Cult.fit diet and training consultation.",
                'editorial_verdict' => "The best budget under-desk walking solution for remote workers striving to achieve daily 10k step goals without occupying permanent floor space.",
                'pros' => ["Brushless DC motor operates under 45dB, ideal for conference calls", "Slim 12cm profile easily slides beneath beds and standing desks", "Multi-layered shock-absorbing running deck reduces joint impact", "Includes remote control and LED real-time metric display"],
                'cons' => ["Max speed capped at 6 km/h (walking/jogging only, not for sprinting)", "Deck width is compact, requiring focused foot placement"],
                'best_for' => ["Remote professionals", "apartment residents", "and low-impact daily cardio."],
                'not_for' => ["Marathon runners or high-speed sprint interval training."],
            ],
            [
                'title' => "ZEBRONICS Juke BAR 9510WS PRO Dolby 5.1 Soundbar",
                'original_price' => 24999,
                'discounted_price' => 9499,
                'category_slug' => "electronics",
                'merchant_name' => "Amazon India",
                'url' => "https://www.amazon.in/dp/B0DGPZTGB4",
                'image_path' => "https://m.media-amazon.com/images/I/71X-wbkrhWL._SL1500_.jpg",
                'status' => "active",
                'tags' => ["Electronics", "Audio", "ZEBRONICS"],
                'editorial_summary' => "Delivering genuine 5.1 surround sound rather than virtualized acoustic trickery, the Zebronics Juke BAR 9510WS PRO pairs a central triple-driver soundbar with dual wireless satellite rear speakers and a dedicated 16.5cm subwoofer. The system pumps out 525W RMS total acoustic output with certified Dolby Audio decoding. HDMI ARC, optical input, AUX, USB, and Bluetooth 5.0 ensure seamless pairing across modern smart TVs and consoles.",
                'editorial_verdict' => "Unmatched price-to-performance ratio for home theater enthusiasts desiring discrete rear physical surround speakers under \u20b910,000.",
                'pros' => ["True physical 5.1 discrete rear wireless surround speakers", "525W RMS delivers punchy cinematic low-end bass", "Dolby Audio hardware decoding via HDMI ARC & Optical ports", "Wall-mountable sleek chassis with premium metal front grille"],
                'cons' => ["Rear satellites require wall-socket power adapters", "Treble equalizer requires manual adjustment out of the box"],
                'best_for' => ["Movie buffs and console gamers seeking immersive room-filling 5.1 audio without expensive AV receivers."],
                'not_for' => ["Audiophiles requiring uncompressed Dolby Atmos overhead spatial height channels."],
            ],
            [
                'title' => "Spacewood Optima 3 Door Wardrobe for Bedroom with Lockable Door & Hanging Rod",
                'original_price' => 34999,
                'discounted_price' => 24148,
                'category_slug' => "home-kitchen",
                'merchant_name' => "Amazon India",
                'url' => "https://www.amazon.in/dp/B0DWK616GS",
                'image_path' => "https://m.media-amazon.com/images/I/71bGIlZyPkL._SL1500_.jpg",
                'status' => "active",
                'tags' => ["Home & Kitchen", "Furniture", "Spacewood"],
                'editorial_summary' => "Crafted from premium European-grade engineered wood with water-resistant melamine coating, the Spacewood Optima provides versatile bedroom storage with three distinct full-length doors, deep internal shelving, and heavy-duty steel hanging rails. Includes lockable security drawers for valuables and ergonomic handles tested for 50,000 opening cycles.",
                'editorial_verdict' => "A spacious, sturdily built 3-door wardrobe with contemporary aesthetics that punches well above its discount pricing.",
                'pros' => ["Water and scratch-resistant melamine particle board", "Full-length internal hanging space accommodates coats and traditional attire", "Lockable internal drawer for confidential documents and jewelry", "Precision-drilled panels ensure square, rattle-free assembly"],
                'cons' => ["Assembly requires two adults or professional carpenter service", "Heavy engineered wood panels require careful moving"],
                'best_for' => ["Master bedrooms and rental apartments needing structured apparel organization."],
                'not_for' => ["Outdoor damp storage or frequent relocation setups."],
            ],
            [
                'title' => "Apple iPhone 15 (128 GB) - Blue",
                'original_price' => 79900,
                'discounted_price' => 65999,
                'category_slug' => "mobiles",
                'merchant_name' => "Amazon India",
                'url' => "https://www.amazon.in/dp/B0CHX1W1XY",
                'image_path' => "https://m.media-amazon.com/images/I/71657TiFeHL._SL1500_.jpg",
                'status' => "active",
                'tags' => ["Mobiles", "Smartphone", "Apple"],
                'editorial_summary' => "The iPhone 15 brings Apple's interactive Dynamic Island, 48MP main computational sensor with 2x lossless telephoto optical crop, and universal USB-C charging to the mainstream flagship bracket. Driven by the 4nm A16 Bionic processor and paired with a 2,000-nit peak outdoor Super Retina XDR display, it offers buttery smooth performance, all-day battery endurance, and aerospace-grade textured frosted glass durability.",
                'editorial_verdict' => "The sweet spot in the modern iPhone lineup, combining flagship camera hardware and universal USB-C convenience.",
                'pros' => ["48MP main camera captures remarkable 24MP default detail with 2x lossless zoom", "Dynamic Island seamlessly integrates live alerts and sports scores", "Universal USB-C port simplifies daily cable management", "Superb daylight visibility reaching 2,000 nits peak outdoor brightness"],
                'cons' => ["Display refresh rate remains standard 60Hz rather than 120Hz ProMotion", "Fast charging capped at 20W"],
                'best_for' => ["Apple ecosystem users upgrading from iPhone 11/12 or seeking long-term 5-year iOS software support."],
                'not_for' => ["Mobile gamers demanding 120Hz high refresh rates or multi-window Android multitasking."],
            ],
            [
                'title' => "OnePlus Nord CE4 5G (8GB RAM, 128GB Storage, Celadon Marble)",
                'original_price' => 24999,
                'discounted_price' => 19998,
                'category_slug' => "mobiles",
                'merchant_name' => "Amazon India",
                'url' => "https://www.amazon.in/dp/B0CX58MTNN",
                'image_path' => "https://m.media-amazon.com/images/I/61nxQ62qglL._SL1500_.jpg",
                'status' => "active",
                'tags' => ["Mobiles", "Smartphone", "OnePlus"],
                'editorial_summary' => "Powered by Qualcomm's efficient 4nm Snapdragon 7 Gen 3 silicon and an expansive 5,500mAh dual-cell battery, the OnePlus Nord CE4 sets a benchmark for mid-range endurance. It features blistering 100W SuperVOOC flash charging that fuels from 1% to 100% in under 30 minutes. The 6.7-inch 120Hz Fluid AMOLED display supports Aqua Touch technology for reliable touch detection even with wet fingertips.",
                'editorial_verdict' => "The gold standard for sub-\u20b920k battery endurance and rapid charging performance.",
                'pros' => ["Huge 5,500mAh battery delivers over 9 hours of screen-on time", "Blazing 100W SuperVOOC charger bundled in the retail box", "Snapdragon 7 Gen 3 handles casual gaming and daily tasks fluidly", "Aqua Touch ensures responsive screen input during rain or sweaty workouts"],
                'cons' => ["No dedicated telephoto lens; secondary camera is 8MP ultrawide", "Plastic frame construction"],
                'best_for' => ["Power users and commuters prioritizing all-day battery life and ultra-fast turnaround charging."],
                'not_for' => ["Heavy photo enthusiasts wanting dedicated telephoto optical zoom."],
            ],
            [
                'title' => "Realme Narzo 70 Pro 5G (8GB RAM, 128GB Storage, Glass Green)",
                'original_price' => 24999,
                'discounted_price' => 17999,
                'category_slug' => "mobiles",
                'merchant_name' => "Amazon India",
                'url' => "https://www.amazon.in/dp/B0CX1Y49P2",
                'image_path' => "https://m.media-amazon.com/images/I/71Qwha+BcbL._SL1500_.jpg",
                'status' => "active",
                'tags' => ["Mobiles", "Smartphone", "Realme"],
                'editorial_summary' => "The Realme Narzo 70 Pro 5G stands out in the budget mid-range tier by bringing a flagship-grade 50MP Sony IMX890 sensor with optical image stabilization (OIS) below \u20b918,000. It features an exquisite dual-tone glass back, 120Hz AMOLED display with 2000 nits peak brightness, 67W SuperVOOC charging, and innovative Air Gesture controls for hands-free navigation.",
                'editorial_verdict' => "The premier camera smartphone pick under \u20b918,000, packing Sony's flagship IMX890 OIS hardware into a stunning glass chassis.",
                'pros' => ["Flagship 50MP Sony IMX890 with OIS captures exceptional low-light photographs", "Premium glass back panel rarely seen in this budget category", "Bright 120Hz AMOLED panel with 2,000 nits peak brightness", "67W fast charging restores 50% in approximately 19 minutes"],
                'cons' => ["Secondary 2MP macro sensor is mostly ornamental", "Occasional pre-installed app recommendations requiring initial cleanup"],
                'best_for' => ["Mobile photography lovers seeking flagship low-light camera capabilities on a strict sub-\u20b920", "000 budget."],
                'not_for' => ["Purists who demand clean stock Android with zero promotional notifications."],
            ],
            [
                'title' => "Samsung Galaxy M35 5G (6GB RAM, 128GB Storage, Daybreak Blue)",
                'original_price' => 24499,
                'discounted_price' => 16999,
                'category_slug' => "mobiles",
                'merchant_name' => "Amazon India",
                'url' => "https://www.amazon.in/dp/B0D7Z8FJZ9",
                'image_path' => "https://m.media-amazon.com/images/I/81nt-RGKpyL._SL1500_.jpg",
                'status' => "active",
                'tags' => ["Mobiles", "Smartphone", "Samsung"],
                'editorial_summary' => "Backed by a monstrous 6,000mAh battery and Samsung's Knox Vault hardware security, the Galaxy M35 5G is engineered for maximum reliability. It boasts a 6.6-inch FHD+ 120Hz Super AMOLED display protected by Corning Gorilla Glass Victus+, a 50MP OIS triple camera array, and a generous commitment of 4 OS upgrades and 5 years of security patches.",
                'editorial_verdict' => "The safest, most durable long-term mid-range purchase with unmatched 6,000mAh battery life and 5-year software support.",
                'pros' => ["Massive 6,000mAh battery lasts 2 full days between charges", "Corning Gorilla Glass Victus+ provides class-leading drop resistance", "Samsung Knox Vault with 4 OS upgrades and 5 years of security updates", "50MP OIS camera delivers stable video and crisp daylight shots"],
                'cons' => ["Wall charger is NOT included in the retail box", "Weighs 222g due to the colossal 6,000mAh battery"],
                'best_for' => ["Students and working professionals wanting a dependable smartphone to use for 4-5 years without slowing down."],
                'not_for' => ["Users looking for featherweight", "ultra-slim handsets."],
            ],
            [
                'title' => "Sony WH-1000XM5 Wireless Noise Cancelling Headphones",
                'original_price' => 34990,
                'discounted_price' => 26990,
                'category_slug' => "electronics",
                'merchant_name' => "Amazon India",
                'url' => "https://www.amazon.in/dp/B09Y2MYL5C",
                'image_path' => "https://m.media-amazon.com/images/I/51hrEIBMDzL._SL1320_.jpg",
                'status' => "out_of_stock",
                'tags' => ["Electronics", "Audio", "Headphones"],
                'editorial_summary' => "Sony's flagship WH-1000XM5 headphones lead the active noise cancellation arena with dual processors and 8 microphones that automatically optimize noise canceling based on wearing conditions and atmospheric pressure. Custom 30mm carbon fiber composite drivers produce pristine high-resolution wireless audio via LDAC, complemented by 30 hours of battery life and Speak-to-Chat voice pickup.",
                'editorial_verdict' => "The unrivaled benchmark for active noise cancellation, travel comfort, and wireless call clarity.",
                'pros' => ["Industry-leading active noise cancellation silences engine roar and chatter", "Ultra-comfortable lightweight noiseless design with soft-fit leather", "30-hour battery life with 3-minute quick charge giving 3 hours playback", "Crystal-clear microphone array powered by 4 beamforming mics and AI reduction"],
                'cons' => ["Earcups fold flat but do not collapse inward like earlier XM4 models", "Premium price tier"],
                'best_for' => ["Frequent flyers", "open-office professionals", "and audiophiles demanding state-of-the-art noise isolation."],
                'not_for' => ["Casual listeners seeking budget gym headphones."],
            ],
            [
                'title' => "Noise ColorFit Pulse 2 Max 1.85\" Display Bluetooth Calling Smart Watch",
                'original_price' => 5999,
                'discounted_price' => 1499,
                'category_slug' => "electronics",
                'merchant_name' => "Amazon India",
                'url' => "https://www.amazon.in/dp/B0B6BLTGTT",
                'image_path' => "https://m.media-amazon.com/images/I/61bm7UEdGAL._SL1500_.jpg",
                'status' => "active",
                'tags' => ["Electronics", "Smartwatch", "Noise"],
                'editorial_summary' => "Featuring a generous 1.85-inch TFT LCD with 550 nits peak brightness, the Noise ColorFit Pulse 2 Max delivers clear readability under direct sunlight. Tru Sync technology provides stable Bluetooth calling with low power consumption, while the Noise Health Suite monitors 24/7 heart rate, SpO2 blood oxygen, sleep cycles, and 100 sports modes with up to 10 days of battery life.",
                'editorial_verdict' => "An unbeatable entry-level Bluetooth calling smartwatch offering a large, vibrant display under \u20b91,500.",
                'pros' => ["Expansive 1.85\" screen with sharp 550 nits outdoor visibility", "Tru Sync Bluetooth calling with built-in microphone and speaker", "Full Noise Health Suite tracking heart rate, sleep, and SpO2", "Strong 7-10 day standard battery life on a single charge"],
                'cons' => ["TFT panel does not feature Always-On Display", "No standalone onboard GPS (relies on paired phone GPS)"],
                'best_for' => ["Budget-conscious shoppers wanting wrist call alerts and daily activity tracking."],
                'not_for' => ["Serious athletic runners needing untethered standalone GPS route mapping."],
            ],
            [
                'title' => "Noise Buds VS102 Plus Wireless Earbuds with 70H Playtime",
                'original_price' => 3499,
                'discounted_price' => 999,
                'category_slug' => "electronics",
                'merchant_name' => "Amazon India",
                'url' => "https://www.amazon.in/dp/B0DS2XNXZB",
                'image_path' => "https://m.media-amazon.com/images/I/61Sok9EsgjL._SL1500_.jpg",
                'status' => "active",
                'tags' => ["Electronics", "Audio", "Noise"],
                'editorial_summary' => "The Noise Buds VS102 Plus earphones pack an astonishing 70 hours of total combined playback into an ergonomic charging case. Equipped with 11mm dynamic drivers, environmental noise cancellation (ENC) for voice calls, IPX5 sweat resistance, and Instacharge (10 minutes charge gives 120 minutes playtime), they deliver reliable daily wireless audio under \u20b91,000.",
                'editorial_verdict' => "A pocket-friendly endurance champion offering clean sound and mammoth battery life for daily commuters.",
                'pros' => ["70 hours of total playtime reduces recharging to once every few weeks", "Instacharge delivers 2 hours of listening from just 10 minutes of plug-in", "11mm drivers output punchy bass for contemporary playlists", "IPX5 rating shields against rain showers and gym sweat"],
                'cons' => ["Touch sensor controls can be sensitive to accidental taps", "No active noise cancellation (uses passive ear-tip seal + mic ENC)"],
                'best_for' => ["Daily bus and train commuters", "podcast listeners", "and gym enthusiasts."],
                'not_for' => ["High-end audio producers needing lossless aptX HD codec support."],
            ],
            [
                'title' => "ASUS TUF Gaming F15 Laptop",
                'original_price' => 74990,
                'discounted_price' => 54990,
                'category_slug' => "gaming",
                'merchant_name' => "Amazon India",
                'url' => "https://www.amazon.in/dp/B0C27SD9YR",
                'image_path' => "https://m.media-amazon.com/images/I/6104CfOboiL._SL1500_.jpg",
                'status' => "active",
                'tags' => ["Gaming", "Laptops", "ASUS"],
                'editorial_summary' => "Built to MIL-STD-810H military durability specifications, the ASUS TUF Gaming F15 combines an 11th Gen Intel Core i5-11400H 6-core processor with NVIDIA GeForce RTX 2050 graphics and 16GB dual-channel DDR4 RAM. The 15.6-inch FHD 144Hz IPS display ensures fluid tearing-free gameplay, supported by self-cleaning dual cooling fans and comprehensive I/O including Thunderbolt 4.",
                'editorial_verdict' => "A robust, upgradeable entry gaming and video editing workstation offering exceptional structural rigidity.",
                'pros' => ["16GB RAM out of the box with easily upgradeable dual SO-DIMM slots", "Fluid 144Hz IPS-level display with adaptive sync support", "MIL-STD-810H certified military chassis withstands travel vibrations", "Thunderbolt 4 port allows high-speed external storage and display connections"],
                'cons' => ["RTX 2050 is tailored for 1080p medium-high settings rather than ultra 4K", "Chunky charging brick adds carry weight"],
                'best_for' => ["College engineering students and esports gamers wanting a durable laptop under \u20b955", "000."],
                'not_for' => ["Ultrabook fans wanting sub-1.3kg featherweight travel portability."],
            ],
            [
                'title' => "Motorola Edge 50 Fusion 5G (8GB RAM, 128GB Storage, Marshmallow Blue)",
                'original_price' => 25999,
                'discounted_price' => 21999,
                'category_slug' => "mobiles",
                'merchant_name' => "Flipkart",
                'url' => "https://www.flipkart.com/motorola-edge-50-fusion/p/itm7d39b15599c7e",
                'image_path' => "https://rukminim2.flixcart.com/image/832/832/xif0q/mobile/5/t/j/edge-50-fusion-pb300002in-motorola-resized-original-imahywzrfagkuyxx.jpeg",
                'status' => "active",
                'tags' => ["Mobiles", "Smartphone", "Motorola"],
                'editorial_summary' => "The Motorola Edge 50 Fusion redefines mid-range design with an IP68 underwater-certified curved pOLED display operating at a blazing 144Hz refresh rate. Outfitted with Sony's premium LYT-700C 50MP sensor with OIS, 68W TurboPower charging, and near-stock Hello UI on Android 14, it delivers an impeccably clean, premium user experience.",
                'editorial_verdict' => "The most elegant curved AMOLED handset in the \u20b920,000-\u20b923,000 bracket with genuine IP68 water resistance.",
                'pros' => ["IP68 dust and water resistance certified for immersion up to 1.5m", "Ultra-smooth 144Hz 3D curved 10-bit pOLED screen with 1600 nits brightness", "50MP Sony LYTIA 700C camera produces flagship-grade daylight portraits", "Clean, bloatware-free Hello UI based on Android 14 with Moto gestures"],
                'cons' => ["Curved screen edge requires curved tempered glass protector", "No 3.5mm headphone jack"],
                'best_for' => ["Style-conscious users wanting sleek curved aesthetics", "waterproof security", "and pure Android software."],
                'not_for' => ["Flat-screen purists who dislike curved glass edges."],
            ],
            [
                'title' => "Nothing Phone (2a) 5G (8GB RAM, 128GB Storage, Black)",
                'original_price' => 25999,
                'discounted_price' => 23999,
                'category_slug' => "mobiles",
                'merchant_name' => "Flipkart",
                'url' => "https://www.flipkart.com/nothing-phone-2a-5g-black-128-gb/p/itm85c6bca5edadc",
                'image_path' => "https://rukminim2.flixcart.com/image/832/832/xif0q/mobile/z/g/z/-resized-original-imahfptqbnyebxjg.jpeg",
                'status' => "active",
                'tags' => ["Mobiles", "Smartphone", "Nothing"],
                'editorial_summary' => "Featuring Nothing's transparent industrial aesthetic with customizable Glyph Interface LEDs, the Phone (2a) runs on a custom MediaTek Dimensity 7200 Pro chipset co-engineered with Nothing. It offers a symmetrical-bezel 6.7-inch 120Hz flexible AMOLED display, dual 50MP rear cameras (Main + Ultrawide), and clean Nothing OS 2.5 with zero pre-loaded bloatware.",
                'editorial_verdict' => "The most distinctive smartphone of 2026, combining head-turning industrial design with ultra-refined software.",
                'pros' => ["Iconic transparent back with functional Glyph notification lighting", "Clean Nothing OS offers unique dot-matrix widgets and zero bloatware", "Dual 50MP camera setup avoids low-grade 2MP macro filler lenses", "Equal-width slim bezels provide a balanced, high-end front profile"],
                'cons' => ["Charger is not included in the standard retail box", "Plastic rear casing is prone to micro-scratches without a protective case"],
                'best_for' => ["Minimalists and design enthusiasts seeking a distinctive phone that stands out from generic slabs."],
                'not_for' => ["Users who prefer traditional metal and ceramic finishes."],
            ],
            [
                'title' => "Apple iPad 10th Gen (10.9 Inch, Wi-Fi, 64GB, Silver)",
                'original_price' => 34900,
                'discounted_price' => 30990,
                'category_slug' => "electronics",
                'merchant_name' => "Croma",
                'url' => "https://www.croma.com/apple-ipad-10th-generation-wi-fi-64gb-silver/p/264263",
                'image_path' => "https://m.media-amazon.com/images/I/61kJjroQyvL._SL1500_.jpg",
                'status' => "active",
                'tags' => ["Electronics", "Tablets", "Apple"],
                'editorial_summary' => "The redesigned 10th generation iPad brings modern flat-edge aluminum aesthetics, a 10.9-inch Liquid Retina display, and landscape-oriented 12MP Ultra Wide front camera with Center Stage. Powered by the A14 Bionic chip and modernized with USB-C connectivity, it handles multitasking in iPadOS, note-taking with Apple Pencil, and high-definition video streaming effortlessly.",
                'editorial_verdict' => "The gold standard general-purpose tablet for education, creative drafting, and digital media consumption.",
                'pros' => ["Vibrant 10.9-inch Liquid Retina display with true tone comfort", "Landscape 12MP Center Stage front camera keeps you centered during video calls", "Universal USB-C port enables straightforward external display and hub hookups", "A14 Bionic provides years of responsive app and gaming performance"],
                'cons' => ["Base storage is 64GB without microSD expansion", "Display is not fully laminated (minor air gap)"],
                'best_for' => ["Students", "digital illustrators", "and families wanting a reliable tablet with long battery endurance."],
                'not_for' => ["Professional colorists needing P3 wide-gamut laminated displays (better served by iPad Air)."],
            ],
            [
                'title' => "Sony WH-CH720N Wireless Over-Ear Active Noise Cancelling Headphones",
                'original_price' => 14990,
                'discounted_price' => 9990,
                'category_slug' => "electronics",
                'merchant_name' => "Reliance Digital",
                'url' => "https://www.reliancedigital.in/sony-wh-ch720n-active-noise-cancelling-wireless-headphones/p/493666244",
                'image_path' => "https://m.media-amazon.com/images/I/51O7ABQd1pL._SL1500_.jpg",
                'status' => "active",
                'tags' => ["Electronics", "Audio", "Sony"],
                'editorial_summary' => "Incorporating the Integrated Processor V1 from Sony's flagship XM5 series, the WH-CH720N brings dual noise sensor technology down to an accessible price point. Weighing only 192 grams, it is Sony's lightest overhead active noise cancelling wireless headphone. It boasts multipoint pairing for two devices simultaneously, up to 35 hours of battery life with ANC enabled, and Sony Headphones Connect app customization with custom EQ.",
                'editorial_verdict' => "The best lightweight, sub-\u20b910,000 active noise cancelling over-ear headphones on the market.",
                'pros' => ["Flagship Integrated Processor V1 delivers class-leading noise reduction for its price", "Featherweight 192g build with plush earcups enables all-day fatigue-free wear", "35-hour battery life with 3-minute quick charging delivering 1 hour listening", "Seamless multipoint Bluetooth pairing between phone and laptop"],
                'cons' => ["Earcups swivel flat but do not fold inward into a ball", "Plastic earcups feel utilitarian compared to premium XM5 series"],
                'best_for' => ["Students and work-from-home professionals wanting effective ANC and long-wear comfort under \u20b910", "000."],
                'not_for' => ["Extreme bassheads wanting vibrating bass haptics."],
            ],
            [
                'title' => "Puma Men Softride Rift Tech Running Shoes",
                'original_price' => 6499,
                'discounted_price' => 2924,
                'category_slug' => "fashion-accessories",
                'merchant_name' => "Myntra",
                'url' => "https://www.myntra.com/sports-shoes/puma/puma-men-softride-rift-tech-running-shoes/24681324/buy",
                'image_path' => "https://m.media-amazon.com/images/I/71D9ImsvEtL._SL1500_.jpg",
                'status' => "active",
                'tags' => ["Fashion & Accessories", "Shoes", "Puma"],
                'editorial_summary' => "Engineered for daily jogging and active walking, the Puma Softride Rift Tech features Puma's proprietary SOFTRIDE foam midsole compound that provides extreme cushioning and all-day step-in comfort. Combined with a SoftFoam+ plush sockliner, breathable engineered knit mesh upper, and high-abrasion zoned rubber outsole pods, it offers responsive shock absorption and reliable lateral stability.",
                'editorial_verdict' => "A superbly cushioned daily trainer that excels in walking, casual gym workouts, and road running comfort.",
                'pros' => ["SOFTRIDE midsole foam delivers pillowy shock absorption on hard pavement", "SoftFoam+ insole conforms to the foot's contour for immediate out-of-the-box comfort", "Breathable knit mesh upper keeps feet ventilated during extended workouts", "Zoned rubber traction pods ensure grip on wet tile and asphalt"],
                'cons' => ["Cushioning is tuned for comfort/training rather than carbon-plated race speeds", "Runs slightly snug in toe box; consider ordering half-size up for wide feet"],
                'best_for' => ["Daily walkers", "fitness beginners", "and gym-goers seeking soft cushioning under \u20b93", "000."],
                'not_for' => ["Competitive marathon runners seeking firm energy return."],
            ]
        ];

        foreach ($dealsData as $data) {
            $tags = $data['tags'] ?? [];
            unset($data['tags']);

            $catSlug = $data['category_slug'];
            unset($data['category_slug']);
            $data['category_id'] = $categoriesMap[$catSlug]->id ?? $categoriesMap['electronics']->id;

            $mName = $data['merchant_name'];
            unset($data['merchant_name']);
            $data['merchant_id'] = $merchantsMap[$mName]->id ?? $amazon->id;

            $data['editorial_status'] = Deal::STATUS_PUBLISHED;
            $data['is_editor_pick'] = true;
            $data['reviewed_at'] = now();

            if (\Illuminate\Support\Facades\Schema::hasColumn('deals', 'ai_score')) {
                $data['ai_score'] = 94;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('deals', 'confidence_score')) {
                $data['confidence_score'] = 95;
            }

            $deal = Deal::where('url', $data['url'])->first();
            if (!$deal) {
                $deal = Deal::where('title', $data['title'])->first();
            }

            Deal::withoutEvents(function () use (&$deal, $data) {
                if ($deal) {
                    $deal->update($data);
                    if (!str_ends_with($deal->slug, '-' . $deal->id)) {
                        $deal->slug = Str::slug($deal->title) . '-' . $deal->id;
                        $deal->saveQuietly();
                    }
                } else {
                    $data['slug'] = Str::slug($data['title']);
                    $deal = Deal::create($data);
                    if (!str_ends_with($deal->slug, '-' . $deal->id)) {
                        $deal->slug = $deal->slug . '-' . $deal->id;
                        $deal->saveQuietly();
                    }
                }
            });

            // Attach tags
            $tagIds = [];
            foreach ($tags as $tagName) {
                $tag = Tag::firstOrCreate([
                    'slug' => Str::slug($tagName)
                ], [
                    'name' => $tagName
                ]);
                $tagIds[] = $tag->id;
            }
            $deal->tags()->sync($tagIds);

            // Clean up invalid price histories
            $deal->priceHistories()->where('price', '>', $data['original_price'] * 1.05)->delete();

            // Set clean 3-point price history
            if ($deal->priceHistories()->count() < 2) {
                $deal->priceHistories()->delete();

                $midPrice = (int) round($data['discounted_price'] + ($data['original_price'] - $data['discounted_price']) * 0.4);

                PriceHistory::create([
                    'deal_id' => $deal->id,
                    'price' => $data['original_price'],
                    'recorded_at' => now()->subDays(15)
                ]);
                PriceHistory::create([
                    'deal_id' => $deal->id,
                    'price' => $midPrice,
                    'recorded_at' => now()->subDays(5)
                ]);
                PriceHistory::create([
                    'deal_id' => $deal->id,
                    'price' => $data['discounted_price'],
                    'recorded_at' => now()
                ]);
            }

            if (\Illuminate\Support\Facades\Schema::hasColumn('deals', 'price_intelligence')) {
                $deal->update(['price_intelligence' => null]);
            }
        }

        // Recount all categories, brands, and merchants
        if (class_exists(\App\Services\Catalog\BrandCounter::class)) {
            app(\App\Services\Catalog\BrandCounter::class)->recountAll();
        }
    }
}
