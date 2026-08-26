<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$data = [
    'popup_enabled' => true,
    'popup_image' => 'home-popup/placeholder.png',
    'youtube_embed_url' => '',
    'video_thumbnail' => '',
];

$setting = App\Models\SiteSetting::updateOrCreate(
    ['key' => 'home'],
    ['value' => json_encode($data)]
);

echo "✅ Home setting created/updated!\n";
echo "popup_enabled: true\n";
echo "popup_image: home-popup/placeholder.png\n";
?>
