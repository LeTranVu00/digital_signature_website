<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public static function valueFor(string $key): array
    {
        $default = self::defaults()[$key] ?? [];
        $stored = self::query()->where('key', $key)->first()?->value;
        $stored = is_array($stored) ? self::normalizeStoredValue($key, $stored) : [];

        return self::mergeDefaults($default, $stored);
    }

    public static function putValue(string $key, array $value): self
    {
        return self::query()->updateOrCreate(
            ['key' => $key],
            ['value' => self::mergeDefaults(self::defaults()[$key] ?? [], $value)]
        );
    }

    public static function defaults(): array
    {
        return [
            'home' => [
                'hero_title' => '',
                'hero_copy' => '',
                'hero_slides' => [],
                'intro_text' => '',
                'services' => [],
                'process_intro' => '',
                'youtube_embed_url' => '',
                'video_thumbnail' => '',
                'popup_enabled' => false,
                'popup_image' => '',
                'process_steps' => [],
                'stats' => [],
                'cta_title' => '',
                'cta_copy' => '',
            ],
            'pricing' => [
                'hero_title' => '',
                'hero_copy' => '',
                'plans' => [],
            ],
            'contact' => [
                'hero_title' => '',
                'hero_copy' => '',
                'cards' => [],
                'form_title' => '',
                'form_copy' => '',
                'qr_card' => [
                    'label' => '',
                    'image' => '',
                ],
                'support_links' => [],
            ],
            'software' => [
                'hero_title' => '',
                'hero_copy' => '',
                'notice' => '',
                'items' => [],
                'categories' => [],
            ],
        ];
    }

    private static function normalizeStoredValue(string $key, array $stored): array
    {
        if (
            $key === 'contact'
            && ! array_key_exists('qr_card', $stored)
            && ! empty($stored['qr_cards'] ?? [])
        ) {
            $legacyQrCards = array_values($stored['qr_cards']);
            $legacyZaloCard = $legacyQrCards[1] ?? $legacyQrCards[0] ?? [];

            $stored['qr_card'] = [
                'label' => $legacyZaloCard['label'] ?? '',
                'url' => '',
            ];
        }

        if (
            $key === 'software'
            && ! array_key_exists('categories', $stored)
            && ! empty($stored['items'] ?? [])
        ) {
            $stored['categories'] = [[
                'name' => '',
                'desc' => '',
                'items' => array_values($stored['items']),
            ]];
        }

        return $stored;
    }

    private static function mergeDefaults(array $defaults, array $value): array
    {
        foreach ($value as $key => $item) {
            if (
                array_key_exists($key, $defaults)
                && is_array($defaults[$key])
                && is_array($item)
                && ! array_is_list($defaults[$key])
                && ! array_is_list($item)
            ) {
                $defaults[$key] = self::mergeDefaults($defaults[$key], $item);
            } else {
                $defaults[$key] = $item;
            }
        }

        return $defaults;
    }
}
