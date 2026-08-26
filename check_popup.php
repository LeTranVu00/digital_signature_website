<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$settings = \App\Models\SiteSetting::valueFor('home');
echo "popup_enabled: " . json_encode($settings['popup_enabled'] ?? false) . "\n";
echo "popup_image: " . json_encode($settings['popup_image'] ?? '') . "\n";
