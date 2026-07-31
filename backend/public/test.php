<?php
$url = 'https://www.amazon.in/dp/B0BTHY81B7';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
$html = curl_exec($ch);

$cleanHtml = preg_replace('/\([^)]*?(?:per|\/|100\s*g|100\s*ml|kg|count)[^)]*?\)/i', '', $html);
$cleanHtml = preg_replace('/₹?\s*[\d,.]+\s*(?:\/|\bper\b)\s*\d*\s*(?:g|kg|ml|l|count|unit|100\s*g|100\s*ml)\b/i', '', $cleanHtml);

$output = "HTML length: " . strlen($html) . "\n\n";

if (preg_match('/M\.R\.P\.?:?\s*(?:(?:<\/?[^>]+>)|&nbsp;|\s)*₹?\s*([\d,]+)/i', $cleanHtml, $m)) {
    $output .= 'Matched MRP (New Regex): ' . $m[1] . "\n";
} else {
    $output .= "No MRP matched with new regex.\n";
}

if (preg_match('/M\.R\.P\.?:?\s*(?:<\/?[^>]+>)*\s*₹?\s*([\d,]+)/i', $cleanHtml, $m)) {
    $output .= 'Matched MRP (Old Regex): ' . $m[1] . "\n";
} else {
    $output .= "No MRP matched with old regex.\n";
    if (preg_match('/M\.R\.P\..{0,100}/i', $cleanHtml, $m3)) {
        $output .= "Raw M.R.P. block found: " . htmlentities($m3[0]) . "\n";
    }
}

if (preg_match_all('/class="a-text-price[^"]*"[^>]*>.*?class="a-offscreen"[^>]*>\s*₹?\s*([\d,.]+)/s', $cleanHtml, $matches)) {
    $output .= "Fallback old regex matches: " . implode(", ", $matches[1]) . "\n";
}

echo nl2br($output);
