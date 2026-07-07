<?php
$db = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $db->exec("ALTER TABLE deals ADD COLUMN features JSON;");
} catch(Exception $e) {}
try {
    $db->exec("ALTER TABLE deals ADD COLUMN verdict TEXT;");
} catch(Exception $e) {}
try {
    $db->exec("ALTER TABLE deals ADD COLUMN trust_metrics VARCHAR;");
} catch(Exception $e) {}
try {
    $db->exec("ALTER TABLE deals ADD COLUMN ai_caption TEXT;");
} catch(Exception $e) {}

echo "Table altered successfully.\n";
