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

        $heroSlides = collect(glob(public_path('images/home-slider/*.{jpg,jpeg,png,webp,avif}'), GLOB_BRACE) ?: [])
            ->sort()
            ->map(fn (string $path) => asset('images/home-slider/'.basename($path)))
            ->values();

        if ($heroSlides->isEmpty()) {
            $heroSlides = collect([
                asset('images/digital-signature-cybersecurity-hero.png'),
                asset('images/electronic-contract-tablet.png'),
                asset('images/digital-invoice-finance.png'),
            ]);
        }

        $homeContent = SiteSetting::valueFor('home');
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
        return view('frontend.services.index');
    }

    public function pricing(): View
    {
        $pricingContent = SiteSetting::valueFor('pricing');
        $pricingPlans = $pricingContent['plans'] ?? [];
        $notes = $pricingContent['notes'] ?? [];
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
            'notes',
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
        $softwareChecklist = $softwareContent['checklist'] ?? [];

        return view('frontend.software', compact('softwareCategories', 'softwareChecklist', 'softwareContent', 'softwareItems'));
    }

    private function softwareCategories(array $softwareContent): array
    {
        $categories = array_values($softwareContent['categories'] ?? []);

        if ($categories === [] && ! empty($softwareContent['items'] ?? [])) {
            $categories = [[
                'name' => 'Phần mềm chung',
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
        $bankAccounts = $contactContent['bank_accounts'] ?? [];
        $qrCards = $contactContent['qr_cards'] ?? [];

        return view('frontend.contact', compact('bankAccounts', 'contactContent', 'contacts', 'qrCards'));
    }
}
