<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SiteContentController extends Controller
{
    public function index(Request $request): View
    {
        $tabs = $this->tabs();
        $activeTab = $request->query('tab', 'home');

        if (! array_key_exists($activeTab, $tabs)) {
            $activeTab = 'home';
        }

        $settings = collect(array_keys($tabs))
            ->mapWithKeys(fn (string $key): array => [$key => SiteSetting::valueFor($key)])
            ->all();

        return view('admin.site-content.index', compact('activeTab', 'settings', 'tabs'));
    }

    public function update(Request $request, string $section): RedirectResponse|JsonResponse
    {
        abort_unless(array_key_exists($section, $this->tabs()), 404);

        $data = match ($section) {
            'home' => $this->homeData($request),
            'pricing' => $this->pricingData($request),
            'contact' => $this->contactData($request),
            'software' => $this->softwareData($request),
        };

        SiteSetting::putValue($section, $data);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Đã cập nhật nội dung website.',
                'data' => $data,
            ]);
        }

        return redirect()
            ->route('admin.site-content.index', ['tab' => $section])
            ->with('status', 'Đã cập nhật nội dung website.');
    }

    private function tabs(): array
    {
        return [
            'home' => 'Trang chủ',
            'pricing' => 'Báo giá',
            'contact' => 'Liên hệ',
            'software' => 'Link phần mềm',
        ];
    }

    private function homeData(Request $request): array
    {
        $validated = $request->validate([
            'hero_title' => ['required', 'string', 'max:255'],
            'hero_copy' => ['required', 'string', 'max:1000'],
            'hero_slides' => ['nullable', 'array'],
            'hero_slides.*.path' => ['nullable', 'string', 'max:255'],
            'hero_slides.*.delete' => ['nullable'],
            'hero_slides_upload' => ['nullable', 'array'],
            'hero_slides_upload.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'intro_text' => ['required', 'string', 'max:1200'],
            'services' => ['nullable', 'array'],
            'services.*.delete' => ['nullable'],
            'services.*.title' => ['nullable', 'string', 'max:120'],
            'services.*.desc' => ['nullable', 'string', 'max:500'],
            'process_intro' => ['required', 'string', 'max:255'],
            'youtube_embed_url' => ['nullable', 'url', 'max:500', $this->httpUrlRule()],
            'video_thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'popup_enabled' => ['nullable', 'boolean'],
            'popup_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'process_steps' => ['nullable', 'array'],
            'process_steps.*.delete' => ['nullable'],
            'process_steps.*.title' => ['nullable', 'string', 'max:120'],
            'process_steps.*.desc' => ['nullable', 'string', 'max:500'],
            'stats' => ['nullable', 'array'],
            'stats.*.delete' => ['nullable'],
            'stats.*.value' => ['nullable', 'string', 'max:40'],
            'stats.*.label' => ['nullable', 'string', 'max:120'],
            'cta_title' => ['required', 'string', 'max:255'],
            'cta_copy' => ['required', 'string', 'max:800'],
        ]);

        $stored = SiteSetting::valueFor('home');
        $videoThumbnail = (string) ($stored['video_thumbnail'] ?? '');
        $popupImage = (string) ($stored['popup_image'] ?? '');
        $storedSlides = collect($stored['hero_slides'] ?? [])
            ->map(fn (mixed $slide): string => is_array($slide) ? (string) ($slide['path'] ?? '') : (string) $slide)
            ->filter()
            ->values();
        $heroSlides = collect($validated['hero_slides'] ?? [])
            ->reject(fn (array $slide): bool => filter_var($slide['delete'] ?? false, FILTER_VALIDATE_BOOL))
            ->map(fn (array $slide): string => trim((string) ($slide['path'] ?? '')))
            ->filter(fn (string $path): bool => $path !== '' && $storedSlides->contains($path))
            ->values();

        $deletedSlides = $storedSlides->diff($heroSlides);
        $deletedSlides->each(fn (string $path) => Storage::disk('public')->delete($path));

        foreach ($request->file('hero_slides_upload', []) as $slide) {
            $heroSlides->push($this->storeResizedHeroSlide($slide));
        }

        if ($request->hasFile('video_thumbnail')) {
            if ($videoThumbnail !== '') {
                Storage::disk('public')->delete($videoThumbnail);
            }

            $videoThumbnail = $request->file('video_thumbnail')->store('home', 'public');
        }

        if ($request->hasFile('popup_image')) {
            if ($popupImage !== '') {
                Storage::disk('public')->delete($popupImage);
            }

            $popupImage = $request->file('popup_image')->store('home-popup', 'public');
        }

        return [
            ...$validated,
            'youtube_embed_url' => $this->youtubeEmbedUrl($validated['youtube_embed_url'] ?? ''),
            'video_thumbnail' => $videoThumbnail,
            'popup_enabled' => $request->boolean('popup_enabled'),
            'popup_image' => $popupImage,
            'hero_slides_upload' => [],
            'services' => $this->rows($validated['services'] ?? [], ['title', 'desc']),
            'process_steps' => $this->rows($validated['process_steps'] ?? [], ['title', 'desc']),
            'stats' => $this->rows($validated['stats'] ?? [], ['value', 'label']),
            'hero_slides' => $heroSlides->values()->all(),
        ];
    }

    private function storeResizedHeroSlide(UploadedFile $file): string
    {
        $source = imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if (! $source) {
            throw new \RuntimeException('Không thể đọc ảnh banner trang chủ.');
        }

        $targetWidth = 1600;
        $targetHeight = 700;
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $sourceRatio = $sourceWidth / max(1, $sourceHeight);
        $targetRatio = $targetWidth / $targetHeight;

        if ($sourceRatio > $targetRatio) {
            $cropHeight = $sourceHeight;
            $cropWidth = (int) round($sourceHeight * $targetRatio);
            $sourceX = (int) floor(($sourceWidth - $cropWidth) / 2);
            $sourceY = 0;
        } else {
            $cropWidth = $sourceWidth;
            $cropHeight = (int) round($sourceWidth / $targetRatio);
            $sourceX = 0;
            $sourceY = (int) floor(($sourceHeight - $cropHeight) / 2);
        }

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        $background = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $background);
        imagecopyresampled($canvas, $source, 0, 0, $sourceX, $sourceY, $targetWidth, $targetHeight, $cropWidth, $cropHeight);

        ob_start();
        imagejpeg($canvas, null, 86);
        $contents = ob_get_clean();

        imagedestroy($source);
        imagedestroy($canvas);

        $path = 'home-slider/'.str()->random(40).'.jpg';
        Storage::disk('public')->put($path, $contents);

        return $path;
    }

    private function pricingData(Request $request): array
    {
        $validated = $request->validate([
            'hero_title' => ['required', 'string', 'max:255'],
            'hero_copy' => ['required', 'string', 'max:1000'],
            'plans' => ['nullable', 'array'],
            'plans.*.delete' => ['nullable'],
            'plans.*.name' => ['nullable', 'string', 'max:160'],
            'plans.*.desc' => ['nullable', 'string', 'max:700'],
            'plans.*.features_text' => ['nullable', 'string', 'max:1000'],
            'plans.*.existing_images' => ['nullable', 'array'],
            'plans.*.existing_images.*' => ['nullable', 'string', 'max:255'],
            'plans.*.image_names' => ['nullable', 'array'],
            'plans.*.image_names.*' => ['nullable', 'string', 'max:160'],
            'plans.*.replace_images' => ['nullable', 'array'],
            'plans.*.replace_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'plans.*.remove_images' => ['nullable', 'array'],
            'plans.*.remove_images.*' => ['nullable', 'string', 'max:255'],
            'plans.*.new_images' => ['nullable', 'array'],
            'plans.*.new_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'plans.*.new_image_names' => ['nullable', 'array'],
            'plans.*.new_image_names.*' => ['nullable', 'string', 'max:160'],
        ]);

        // Merge incoming plans with stored plans to avoid accidental overwrite
        $incomingPlans = $request->input('plans', []);
        $stored = SiteSetting::valueFor('pricing');
        $storedPlans = $stored['plans'] ?? [];

        // Union of keys (preserve numeric/string keys provided by client)
        $allKeys = array_unique(array_merge(array_keys($incomingPlans), array_keys($storedPlans)));
        // Sort keys so order is predictable (numeric strings first)
        usort($allKeys, function ($a, $b) {
            if (is_numeric($a) && is_numeric($b)) {
                return (int) $a <=> (int) $b;
            }

            return (string) $a <=> (string) $b;
        });

        $mergedInput = [];

        foreach ($allKeys as $key) {
            if (array_key_exists($key, $incomingPlans)) {
                // Prefer incoming values but keep existing stored values as fallback
                $mergedInput[$key] = array_replace_recursive($storedPlans[$key] ?? [], (array) $incomingPlans[$key]);
            } else {
                $mergedInput[$key] = $storedPlans[$key];
            }
        }

        $plans = collect($mergedInput)
            ->map(function (array $row, int|string $index) use ($request): ?array {
                if (filter_var($row['delete'] ?? false, FILTER_VALIDATE_BOOL)) {
                    $storedImages = collect($row['images'] ?? [])
                        ->map(fn (string|array $image): string => is_array($image) ? (string) ($image['path'] ?? '') : $image);

                    collect($row['existing_images'] ?? [])
                        ->merge($storedImages)
                        ->filter()
                        ->unique()
                        ->each(fn (string $path) => Storage::disk('public')->delete($path));

                    return null;
                }

                $removeImages = collect($row['remove_images'] ?? [])->filter()->values();
                $replaceImages = $request->file("plans.{$index}.replace_images", []);
                $images = [];

                foreach (collect($row['existing_images'] ?? [])->filter()->values() as $imageIndex => $path) {
                    $imageName = trim((string) ($row['image_names'][$imageIndex] ?? ''));

                    if ($removeImages->contains($path)) {
                        Storage::disk('public')->delete($path);

                        continue;
                    }

                    $replacement = $replaceImages[$imageIndex] ?? null;

                    if ($replacement) {
                        Storage::disk('public')->delete($path);
                        $images[] = [
                            'path' => $replacement->store('pricing-plans', 'public'),
                            'name' => $imageName,
                        ];

                        continue;
                    }

                    $images[] = [
                        'path' => $path,
                        'name' => $imageName,
                    ];
                }

                foreach ($request->file("plans.{$index}.new_images", []) as $imageIndex => $image) {
                    $newImageName = trim((string) ($row['new_image_names'][$imageIndex] ?? ''));

                    $images[] = [
                        'path' => $image->store('pricing-plans', 'public'),
                        'name' => $newImageName !== '' ? $newImageName : pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME),
                    ];
                }

                return [
                    'name' => trim((string) ($row['name'] ?? '')),
                    'desc' => trim((string) ($row['desc'] ?? '')),
                    'features' => $this->lines($row['features_text'] ?? ''),
                    'images' => $images,
                ];
            })
            ->filter(fn (?array $row): bool => is_array($row) && ($this->hasAny($row, ['name', 'desc']) || count($row['features']) > 0 || count($row['images']) > 0))
            ->values()
            ->all();

        return [
            'hero_title' => $validated['hero_title'],
            'hero_copy' => $validated['hero_copy'],
            'plans' => $plans,
        ];
    }

    private function contactData(Request $request): array
    {
        $validated = $request->validate([
            'hero_title' => ['required', 'string', 'max:255'],
            'hero_copy' => ['required', 'string', 'max:1000'],
            'cards' => ['nullable', 'array'],
            'cards.*.title' => ['nullable', 'string', 'max:120'],
            'cards.*.value' => ['nullable', 'string', 'max:160'],
            'cards.*.desc' => ['nullable', 'string', 'max:500'],
            'form_title' => ['required', 'string', 'max:255'],
            'form_copy' => ['required', 'string', 'max:1000'],
            'qr_card' => ['nullable', 'array'],
            'qr_card.label' => ['nullable', 'string', 'max:160'],
            'qr_card.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'support_links' => ['nullable', 'array'],
            'support_links.*.delete' => ['nullable'],
            'support_links.*.type' => ['nullable', 'string', 'in:zalo,phone'],
            'support_links.*.label' => ['nullable', 'string', 'max:160'],
            'support_links.*.phone' => ['nullable', 'string', 'max:30'],
        ]);

        $stored = SiteSetting::valueFor('contact');
        $storedQrCard = is_array($stored['qr_card'] ?? null) ? $stored['qr_card'] : [];
        $qrImage = trim((string) ($storedQrCard['image'] ?? ''));

        if ($request->hasFile('qr_card.image')) {
            if ($qrImage !== '') {
                Storage::disk('public')->delete($qrImage);
            }

            $qrImage = $request->file('qr_card.image')->store('contact', 'public');
        }

        return [
            ...$validated,
            'cards' => $this->rows($validated['cards'] ?? [], ['title', 'value', 'desc']),
            'qr_card' => [
                'label' => trim((string) ($validated['qr_card']['label'] ?? '')),
                'image' => $qrImage,
            ],
            'support_links' => $this->supportLinks($validated['support_links'] ?? []),
        ];
    }

    private function softwareData(Request $request): array
    {
        $validated = $request->validate([
            'hero_title' => ['required', 'string', 'max:255'],
            'hero_copy' => ['required', 'string', 'max:1000'],
            'notice' => ['required', 'string', 'max:1000'],
            'categories' => ['nullable', 'array'],
            'categories.*.delete' => ['nullable'],
            'categories.*.name' => ['nullable', 'string', 'max:180'],
            'categories.*.desc' => ['nullable', 'string', 'max:500'],
            'categories.*.items' => ['nullable', 'array'],
            'categories.*.items.*.delete' => ['nullable'],
            'categories.*.items.*.name' => ['nullable', 'string', 'max:180'],
            'categories.*.items.*.desc' => ['nullable', 'string', 'max:700'],
            'categories.*.items.*.type' => ['nullable', 'string', 'max:120'],
            'categories.*.items.*.url' => ['nullable', 'url', 'max:500', $this->httpUrlRule()],
            'items' => ['nullable', 'array'],
            'items.*.delete' => ['nullable'],
            'items.*.name' => ['nullable', 'string', 'max:180'],
            'items.*.desc' => ['nullable', 'string', 'max:700'],
            'items.*.type' => ['nullable', 'string', 'max:120'],
            'items.*.url' => ['nullable', 'url', 'max:500', $this->httpUrlRule()],
        ]);

        $categories = $this->softwareCategories($validated['categories'] ?? [], $validated['items'] ?? []);
        $items = collect($categories)
            ->flatMap(fn (array $category): array => $category['items'] ?? [])
            ->values()
            ->all();

        return [
            'hero_title' => $validated['hero_title'],
            'hero_copy' => $validated['hero_copy'],
            'notice' => $validated['notice'],
            'categories' => $categories,
            'items' => $items,
        ];
    }

    private function softwareCategories(array $categories, array $legacyItems): array
    {
        if ($categories === [] && $legacyItems !== []) {
            $categories = [[
                'name' => '',
                'desc' => '',
                'items' => $legacyItems,
            ]];
        }

        return collect($categories)
            ->reject(fn (array $category): bool => filter_var($category['delete'] ?? false, FILTER_VALIDATE_BOOL))
            ->map(function (array $category): array {
                return [
                    'name' => trim((string) ($category['name'] ?? '')),
                    'desc' => trim((string) ($category['desc'] ?? '')),
                    'items' => $this->softwareItems($category['items'] ?? []),
                ];
            })
            ->filter(fn (array $category): bool => $category['name'] !== '' || $category['items'] !== [])
            ->values()
            ->all();
    }

    private function softwareItems(array $items): array
    {
        return collect($items)
            ->reject(fn (array $row): bool => filter_var($row['delete'] ?? false, FILTER_VALIDATE_BOOL))
            ->map(fn (array $row): array => [
                'name' => trim((string) ($row['name'] ?? '')),
                'desc' => trim((string) ($row['desc'] ?? '')),
                'type' => trim((string) ($row['type'] ?? '')),
                'url' => trim((string) ($row['url'] ?? '')),
            ])
            ->filter(fn (array $row): bool => $row['name'] !== '' && $row['url'] !== '')
            ->values()
            ->all();
    }

    private function rows(array $rows, array $fields): array
    {
        return collect($rows)
            ->reject(fn (array $row): bool => filter_var($row['delete'] ?? false, FILTER_VALIDATE_BOOL))
            ->map(function (array $row) use ($fields): array {
                return collect($fields)
                    ->mapWithKeys(fn (string $field): array => [$field => trim((string) ($row[$field] ?? ''))])
                    ->all();
            })
            ->filter(fn (array $row): bool => $this->hasAny($row, $fields))
            ->values()
            ->all();
    }

    private function supportLinks(array $rows): array
    {
        return collect($rows)
            ->reject(fn (array $row): bool => filter_var($row['delete'] ?? false, FILTER_VALIDATE_BOOL))
            ->map(function (array $row): array {
                $type = (string) ($row['type'] ?? 'zalo');
                $phone = trim((string) ($row['phone'] ?? ''));

                if ($phone === '') {
                    $legacyUrl = trim((string) ($row['url'] ?? ''));
                    $phone = preg_replace('/^https?:\/\/zalo\.me\//i', '', $legacyUrl) ?: $legacyUrl;
                    $phone = preg_replace('/^tel:/i', '', $phone) ?: $phone;
                }

                $phone = preg_replace('/[^0-9+]/', '', $phone) ?: '';

                return [
                    'type' => in_array($type, ['zalo', 'phone'], true) ? $type : 'zalo',
                    'label' => trim((string) ($row['label'] ?? '')),
                    'url' => $phone === '' ? '' : ($type === 'phone' ? 'tel:' . $phone : 'https://zalo.me/' . ltrim($phone, '+')),
                ];
            })
            ->filter(fn (array $row): bool => $row['url'] !== '')
            ->values()
            ->all();
    }

    private function textRows(array $rows): array
    {
        return collect($rows)
            ->map(fn (array $row): string => trim((string) ($row['text'] ?? '')))
            ->filter()
            ->values()
            ->all();
    }

    private function lines(?string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $value))
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    private function httpUrlRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            $scheme = strtolower((string) parse_url((string) $value, PHP_URL_SCHEME));

            if (! in_array($scheme, ['http', 'https'], true)) {
                $fail('Duong dan phai su dung http hoac https.');
            }
        };
    }

    private function supportUrlRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            $value = trim((string) $value);

            if ($value === '') {
                return;
            }

            $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));

            if (! in_array($scheme, ['http', 'https', 'tel'], true)) {
                $fail('Duong dan phai su dung http, https hoac tel.');
            }
        };
    }

    private function youtubeEmbedUrl(?string $url): string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return '';
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $query = [];
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        if (str_contains($host, 'youtu.be')) {
            $videoId = explode('/', $path)[0] ?? '';
        } elseif (str_contains($host, 'youtube.com') && str_starts_with($path, 'embed/')) {
            $videoId = explode('/', substr($path, strlen('embed/')))[0] ?? '';
        } elseif (str_contains($host, 'youtube.com') && str_starts_with($path, 'shorts/')) {
            $videoId = explode('/', substr($path, strlen('shorts/')))[0] ?? '';
        } elseif (str_contains($host, 'youtube.com')) {
            $videoId = (string) ($query['v'] ?? '');
        } else {
            return $url;
        }

        $videoId = preg_replace('/[^A-Za-z0-9_-]/', '', $videoId ?? '');

        return $videoId ? "https://www.youtube.com/embed/{$videoId}" : $url;
    }

    private function hasAny(array $row, array $fields): bool
    {
        foreach ($fields as $field) {
            if (trim((string) ($row[$field] ?? '')) !== '') {
                return true;
            }
        }

        return false;
    }
}
