<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class PricingCategoryController extends Controller
{
    public function index(): View
    {
        $pricingCategories = PricingCategory::query()
            ->ordered()
            ->paginate(12);

        return view('admin.pricing-categories.index', compact('pricingCategories'));
    }

    public function create(): View
    {
        return view('admin.pricing-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['image_path'] = $request->file('image')->store('pricing', 'public');

        try {
            PricingCategory::create($data);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($data['image_path']);

            throw $exception;
        }

        return redirect()
            ->route('admin.pricing-categories.index')
            ->with('success', 'Đã tạo danh mục báo giá.');
    }

    public function edit(PricingCategory $pricingCategory): View
    {
        return view('admin.pricing-categories.edit', compact('pricingCategory'));
    }

    public function update(Request $request, PricingCategory $pricingCategory): RedirectResponse
    {
        $data = $this->validated($request, $pricingCategory);
        $data['slug'] = $this->uniqueSlug($data['name'], $pricingCategory);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $oldImage = $pricingCategory->image_path;
        $newImage = null;

        if ($request->hasFile('image')) {
            $newImage = $request->file('image')->store('pricing', 'public');
            $data['image_path'] = $newImage;
        }

        try {
            $pricingCategory->update($data);
        } catch (Throwable $exception) {
            if ($newImage) {
                Storage::disk('public')->delete($newImage);
            }

            throw $exception;
        }

        if ($newImage && $oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()
            ->route('admin.pricing-categories.index')
            ->with('success', 'Đã cập nhật danh mục báo giá.');
    }

    public function destroy(PricingCategory $pricingCategory): RedirectResponse
    {
        $image = $pricingCategory->image_path;
        $pricingCategory->delete();

        if ($image) {
            Storage::disk('public')->delete($image);
        }

        return redirect()
            ->route('admin.pricing-categories.index')
            ->with('success', 'Đã xóa danh mục báo giá.');
    }

    private function validated(Request $request, ?PricingCategory $pricingCategory = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'image' => [
                $pricingCategory ? 'nullable' : 'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:8192',
            ],
        ]);
    }

    private function uniqueSlug(string $name, ?PricingCategory $ignore = null): string
    {
        $baseSlug = Str::slug($name) ?: 'bang-gia';
        $slug = $baseSlug;
        $counter = 2;

        while (
            PricingCategory::query()
                ->where('slug', $slug)
                ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->getKey()))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
