<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
            'intro_text' => ['required', 'string', 'max:1200'],
            'services' => ['nullable', 'array'],
            'services.*.title' => ['nullable', 'string', 'max:120'],
            'services.*.desc' => ['nullable', 'string', 'max:500'],
            'process_intro' => ['required', 'string', 'max:255'],
            'process_steps' => ['nullable', 'array'],
            'process_steps.*.title' => ['nullable', 'string', 'max:120'],
            'process_steps.*.desc' => ['nullable', 'string', 'max:500'],
            'stats' => ['nullable', 'array'],
            'stats.*.value' => ['nullable', 'string', 'max:40'],
            'stats.*.label' => ['nullable', 'string', 'max:120'],
            'cta_title' => ['required', 'string', 'max:255'],
            'cta_copy' => ['required', 'string', 'max:800'],
        ]);

        return [
            ...$validated,
            'services' => $this->rows($validated['services'] ?? [], ['title', 'desc']),
            'process_steps' => $this->rows($validated['process_steps'] ?? [], ['title', 'desc']),
            'stats' => $this->rows($validated['stats'] ?? [], ['value', 'label']),
        ];
    }

    private function pricingData(Request $request): array
    {
        $validated = $request->validate([
            'hero_title' => ['required', 'string', 'max:255'],
            'hero_copy' => ['required', 'string', 'max:1000'],
            'plans' => ['nullable', 'array'],
            'plans.*.delete' => ['nullable'],
            'plans.*.name' => ['nullable', 'string', 'max:160'],
            'plans.*.price' => ['nullable', 'string', 'max:80'],
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
            'notes_title' => ['required', 'string', 'max:255'],
            'notes_copy' => ['required', 'string', 'max:1000'],
            'notes' => ['nullable', 'array'],
            'notes.*.text' => ['nullable', 'string', 'max:600'],
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
                    'price' => trim((string) ($row['price'] ?? '')),
                    'desc' => trim((string) ($row['desc'] ?? '')),
                    'features' => $this->lines($row['features_text'] ?? ''),
                    'images' => $images,
                ];
            })
            ->filter(fn (?array $row): bool => is_array($row) && ($this->hasAny($row, ['name', 'price', 'desc']) || count($row['features']) > 0 || count($row['images']) > 0))
            ->values()
            ->all();

        return [
            'hero_title' => $validated['hero_title'],
            'hero_copy' => $validated['hero_copy'],
            'plans' => $plans,
            'notes_title' => $validated['notes_title'],
            'notes_copy' => $validated['notes_copy'],
            'notes' => $this->textRows($validated['notes'] ?? []),
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
            'qr_cards' => ['nullable', 'array'],
            'qr_cards.*.label' => ['nullable', 'string', 'max:160'],
            'qr_cards.*.image' => ['nullable', 'string', 'max:255'],
            'qr_cards.*.alt' => ['nullable', 'string', 'max:160'],
            'company_name' => ['required', 'string', 'max:160'],
            'address' => ['required', 'string', 'max:500'],
            'phone' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:160'],
            'bank_accounts' => ['nullable', 'array'],
            'bank_accounts.*.bank' => ['nullable', 'string', 'max:160'],
            'bank_accounts.*.account' => ['nullable', 'string', 'max:120'],
            'bank_accounts.*.owner' => ['nullable', 'string', 'max:160'],
        ]);

        return [
            ...$validated,
            'cards' => $this->rows($validated['cards'] ?? [], ['title', 'value', 'desc']),
            'qr_cards' => $this->rows($validated['qr_cards'] ?? [], ['label', 'image', 'alt']),
            'bank_accounts' => $this->rows($validated['bank_accounts'] ?? [], ['bank', 'account', 'owner']),
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
            'categories.*.items.*.url' => ['nullable', 'url', 'max:500'],
            'items' => ['nullable', 'array'],
            'items.*.delete' => ['nullable'],
            'items.*.name' => ['nullable', 'string', 'max:180'],
            'items.*.desc' => ['nullable', 'string', 'max:700'],
            'items.*.type' => ['nullable', 'string', 'max:120'],
            'items.*.url' => ['nullable', 'url', 'max:500'],
            'support_title' => ['required', 'string', 'max:255'],
            'support_copy' => ['required', 'string', 'max:1000'],
            'checklist' => ['nullable', 'array'],
            'checklist.*.text' => ['nullable', 'string', 'max:500'],
        ]);

        $categories = $this->softwareCategories($validated['categories'] ?? [], $validated['items'] ?? []);
        $items = collect($categories)
            ->flatMap(fn (array $category): array => $category['items'] ?? [])
            ->values()
            ->all();

        return [
            ...$validated,
            'categories' => $categories,
            'items' => $items,
            'checklist' => $this->textRows($validated['checklist'] ?? []),
        ];
    }

    private function softwareCategories(array $categories, array $legacyItems): array
    {
        if ($categories === [] && $legacyItems !== []) {
            $categories = [[
                'name' => 'Phần mềm chung',
                'desc' => 'Các phần mềm hỗ trợ đang có.',
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
            ->map(function (array $row) use ($fields): array {
                return collect($fields)
                    ->mapWithKeys(fn (string $field): array => [$field => trim((string) ($row[$field] ?? ''))])
                    ->all();
            })
            ->filter(fn (array $row): bool => $this->hasAny($row, $fields))
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
