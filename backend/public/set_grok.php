<?php
if (isset($_GET['key'])) {
    $envPath = __DIR__ . '/../.env';
    
    if (file_exists($envPath)) {
        $env = file_get_contents($envPath);
        if (strpos($env, 'GROQ_API_KEY') !== false) {
            $env = preg_replace('/GROQ_API_KEY=.*/', 'GROQ_API_KEY=' . $_GET['key'], $env);
        } else {
            $env .= "\nGROQ_API_KEY=" . $_GET['key'];
        }
        file_put_contents($envPath, $env);
        
        // Also clear config cache just in case
        exec('php ' . __DIR__ . '/../artisan config:clear');
        
        echo "Groq Key Updated successfully! This file is now deleting itself for security...";
        unlink(__FILE__);
    } else {
        echo "Error: .env file not found at " . $envPath;
    }
} else {
    echo "Usage: latestdeal.in/set_grok.php?key=YOUR_KEY";
}
