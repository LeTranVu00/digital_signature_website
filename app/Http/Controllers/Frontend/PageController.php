<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PricingCategory;
use App\Models\SiteSetting;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        $latestPosts = Post::query()
            ->published()
            ->with(['category', 'user'])
            ->latest('published_at')
            ->limit(3)
            ->get();

        $homeContent = SiteSetting::valueFor('home');
        $heroSlides = collect($homeContent['hero_slides'] ?? [])
            ->map(function (mixed $path): string {
                $path = is_array($path) ? ($path['path'] ?? '') : $path;
                $path = trim((string) $path);

                return filter_var($path, FILTER_VALIDATE_URL)
                    ? $path
                    : asset('storage/'.ltrim($path, '/'));
            })
            ->filter()
            ->values();

        if ($heroSlides->isEmpty()) {
            $heroSlides = collect(glob(public_path('images/home-slider/*.{jpg,jpeg,png,webp,avif}'), GLOB_BRACE) ?: [])
                ->sort()
                ->map(fn (string $path) => asset('images/home-slider/'.basename($path)))
                ->values();
        }

        if ($heroSlides->isEmpty()) {
            $heroSlides = collect([
                asset('images/digital-signature-cybersecurity-hero.png'),
                asset('images/electronic-contract-tablet.png'),
                asset('images/digital-invoice-finance.png'),
            ]);
        }

        $services = $homeContent['services'] ?? [];
        $processSteps = $homeContent['process_steps'] ?? [];
        $stats = $homeContent['stats'] ?? [];

        return view('frontend.home', compact(
            'heroSlides',
            'homeContent',
            'latestPosts',
            'processSteps',
            'services',
            'stats'
        ));
    }

    public function about(): View
    {
        return view('frontend.about');
    }

    public function services(): View
    {
        $homeContent = SiteSetting::valueFor('home');
        $services = collect($homeContent['services'] ?? [])
            ->filter(fn (array $service): bool => trim((string) ($service['title'] ?? '')) !== '' || trim((string) ($service['desc'] ?? '')) !== '')
            ->values()
            ->map(fn (array $service, int $index): array => [
                'number' => str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'title' => trim((string) ($service['title'] ?? '')),
                'description' => trim((string) ($service['desc'] ?? '')),
            ])
            ->all();
        $steps = collect($homeContent['process_steps'] ?? [])
            ->filter(fn (array $step): bool => trim((string) ($step['title'] ?? '')) !== '' || trim((string) ($step['desc'] ?? '')) !== '')
            ->values()
            ->all();

        return view('frontend.services.index', compact('homeContent', 'services', 'steps'));
    }

    public function pricing(): View
    {
        $pricingContent = SiteSetting::valueFor('pricing');
        $pricingPlans = $pricingContent['plans'] ?? [];
        $pricingCategories = PricingCategory::query()
            ->active()
            ->ordered()
            ->get()
            ->map(fn (PricingCategory $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image_url' => $category->imageUrl(),
            ]);

        return view('frontend.pricing', compact(
            'pricingCategories',
            'pricingContent',
            'pricingPlans'
        ));
    }

    public function pricingPlan(int $plan): View
    {
        $pricingContent = SiteSetting::valueFor('pricing');
        $pricingPlans = array_values($pricingContent['plans'] ?? []);
        $planIndex = $plan - 1;

        abort_unless(isset($pricingPlans[$planIndex]), 404);

        $pricingPlan = $pricingPlans[$planIndex];

        return view('frontend.pricing-plan', compact('plan', 'pricingContent', 'pricingPlan'));
    }

    public function software(): View
    {
        $softwareContent = SiteSetting::valueFor('software');
        $softwareCategories = $this->softwareCategories($softwareContent);
        $softwareItems = collect($softwareCategories)
            ->flatMap(fn (array $category): array => $category['items'] ?? [])
            ->values()
            ->all();

        return view('frontend.software', compact('softwareCategories', 'softwareContent', 'softwareItems'));
    }

    private function softwareCategories(array $softwareContent): array
    {
        $categories = array_values($softwareContent['categories'] ?? []);

        if ($categories === [] && ! empty($softwareContent['items'] ?? [])) {
            $categories = [[
                'name' => '',
                'desc' => '',
                'items' => array_values($softwareContent['items'] ?? []),
            ]];
        }

        return collect($categories)
            ->map(fn (array $category): array => [
                'name' => trim((string) ($category['name'] ?? '')),
                'desc' => trim((string) ($category['desc'] ?? '')),
                'items' => collect($category['items'] ?? [])
                    ->filter(fn (array $item): bool => ! empty($item['name']) && ! empty($item['url']))
                    ->values()
                    ->all(),
            ])
            ->filter(fn (array $category): bool => $category['name'] !== '' || $category['items'] !== [])
            ->values()
            ->all();
    }

    public function contact(): View
    {
        $contactContent = SiteSetting::valueFor('contact');
        $contacts = $contactContent['cards'] ?? [];
        $qrCard = $contactContent['qr_card'] ?? [];

        if (! is_array($qrCard)) {
            $qrCard = [];
        }

        $qrCard = [
            'label' => trim((string) ($qrCard['label'] ?? '')),
            'image' => trim((string) ($qrCard['image'] ?? '')),
            'url' => trim((string) ($qrCard['url'] ?? '')),
        ];

        return view('frontend.contact', compact('contactContent', 'contacts', 'qrCard'));
    }
}
