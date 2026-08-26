<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$current = App\Models\SiteSetting::where('key', 'home')->first();
if ($current) {
    $data = json_decode($current->value, true) ?? [];
    $currentImage = $data['popup_image'] ?? '';
    
    echo "=== Current State ===\n";
    echo "popup_enabled: " . json_encode($data['popup_enabled'] ?? false) . "\n";
    echo "popup_image: $currentImage\n";
    
    // Update nếu trống hoặc không tồn tại
    if (empty($currentImage) || strpos($currentImage, 'placeholder.png') === false) {
        $data['popup_image'] = 'home-popup/placeholder.png';
        $current->value = json_encode($data);
        $current->save();
        echo "\n✅ Updated popup_image to: home-popup/placeholder.png\n";
    } else {
        echo "\n✅ Already set to placeholder\n";
    }
} else {
    echo "❌ No home setting found\n";
}
?>
