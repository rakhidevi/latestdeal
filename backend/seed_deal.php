<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$d = \App\Models\Deal::find(31);
if ($d) {
    $d->features = [
        '🌑 100% True Blackout Technology', 
        '🌡️ Thermal Insulated to reduce energy bills', 
        '🔇 Noise Reducing for better sleep', 
        '🧵 Premium solid burgundy design'
    ];
    $d->verdict = 'LatestDeal.in Home Decor Pick – Instantly upgrade your bedroom aesthetics while enjoying perfect pitch-black sleep! At 71% off, this premium set is an absolute steal.';
    $d->trust_metrics = '⭐ 4.6/5 Rated (20k+ reviews)';
    $d->save();
    echo "Deal 31 updated with matching curtain data!\n";
}
