<?php
require __DIR__ . '/../../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$views = [
    'about' => '/about',
    'how-it-works' => '/how-it-works',
    'editorial-team' => '/editorial-team',
    'editorial-policy' => '/editorial-policy',
    'corrections-policy' => '/corrections-policy',
    'cookie-policy' => '/cookie-policy',
    'privacy' => '/privacy',
    'terms' => '/terms',
    'affiliate-disclosure' => '/affiliate-disclosure',
    'contact' => '/contact'
];

echo "VERIFYING FOOTER TRUST PAGES:\n";
echo str_repeat("=", 75) . "\n";

$allPassed = true;

foreach ($views as $view => $path) {
    try {
        $html = view($view)->render();
        $hasBreadcrumb = str_contains($html, 'aria-label="Breadcrumb"');
        $hasDarkProse = str_contains($html, 'dark:prose-invert') || str_contains($html, 'dark:bg-slate-900');
        $hasFontSans = str_contains($html, 'font-sans');
        $hasGradient = str_contains($html, 'bg-clip-text');
        $hasCard = str_contains($html, 'rounded-3xl');
        $hasTrustNav = str_contains($html, 'Related') || str_contains($html, 'Explore more') || str_contains($html, 'Learn more');

        $status = ($hasBreadcrumb && $hasDarkProse && $hasFontSans && $hasGradient && $hasCard && $hasTrustNav) ? "PASS" : "FAIL";
        if ($status === "FAIL") $allPassed = false;

        echo sprintf("[%s] %-22s | Breadcrumb: %s | DarkMode: %s | FontSans: %s | Card: %s\n",
            $status,
            $view,
            $hasBreadcrumb ? 'YES' : 'NO',
            $hasDarkProse ? 'YES' : 'NO',
            $hasFontSans ? 'YES' : 'NO',
            $hasCard ? 'YES' : 'NO'
        );
    } catch (\Throwable $e) {
        echo "[ERROR] $view: " . $e->getMessage() . "\n";
        $allPassed = false;
    }
}

$guide = \App\Models\Article::where('status', 'published')->first();
if ($guide) {
    $indexHtml = view('articles.index', [
        'articles' => \App\Models\Article::where('status', 'published')->paginate(10),
        'seoMeta' => ['title' => 'Guides', 'description' => 'Guides', 'canonical' => url('/guides')]
    ])->render();
    $showHtml = view('articles.show', [
        'article' => $guide,
        'seoMeta' => ['title' => $guide->title, 'description' => $guide->summary, 'canonical' => url('/guides/' . $guide->slug)]
    ])->render();

    echo sprintf("[%s] %-22s | Breadcrumb: %s | DarkMode: %s | FontSans: %s\n",
        str_contains($indexHtml, 'aria-label="Breadcrumb"') ? "PASS" : "FAIL",
        "articles.index",
        str_contains($indexHtml, 'aria-label="Breadcrumb"') ? "YES" : "NO",
        str_contains($indexHtml, 'dark:') ? "YES" : "NO",
        str_contains($indexHtml, 'font-sans') ? "YES" : "NO"
    );

    echo sprintf("[%s] %-22s | Breadcrumb: %s | DarkMode: %s | FontSans: %s\n",
        str_contains($showHtml, 'aria-label="Breadcrumb"') ? "PASS" : "FAIL",
        "articles.show",
        str_contains($showHtml, 'aria-label="Breadcrumb"') ? "YES" : "NO",
        str_contains($showHtml, 'dark:') ? "YES" : "NO",
        str_contains($showHtml, 'font-sans') ? "YES" : "NO"
    );
}

echo str_repeat("=", 75) . "\n";
echo $allPassed ? "ALL 10 FOOTER PAGES FULLY VERIFIED!\n" : "SOME CHECKS FAILED!\n";
