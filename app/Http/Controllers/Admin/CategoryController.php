<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Hiển thị danh sách danh mục.
     */
    public function index(): View
    {
        $categories = Category::latest()->paginate(10);

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Hiển thị form thêm danh mục.
     */
    public function create(): View
    {
        return view('admin.categories.create');
    }

    /**
     * Lưu danh mục mới.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Thêm danh mục thành công.');
    }

    /**
     * Hiển thị form sửa danh mục.
     */
    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Cập nhật danh mục.
     */
    public function update(
        UpdateCategoryRequest $request,
        Category $category
    ): RedirectResponse {
        $category->update($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Cập nhật danh mục thành công.');
    }

    /**
     * Xóa danh mục.
     */
    public function destroy(Category $category): RedirectResponse
    {
        if ($category->posts()->exists()) {
            return redirect()
                ->route('admin.categories.index')
                ->with(
                    'error',
                    'Không thể xóa danh mục đang chứa bài viết.'
                );
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Xóa danh mục thành công.');
    }
}
