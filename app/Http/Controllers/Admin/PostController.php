<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Hiển thị danh sách bài viết.
     */
    public function index(): View
    {
        $posts = Post::query()
            ->with(['category', 'user'])
            ->latest()
            ->paginate(10);

        return view('admin.posts.index', compact('posts'));
    }

    /**
     * Hiển thị form thêm bài viết.
     */
    public function create(): View
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get();

        return view('admin.posts.create', compact('categories'));
    }

    /**
     * Lưu bài viết mới.
     *
     * Sẽ hoàn thiện ở bài Validation và Upload thumbnail.
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        return redirect()
            ->route('admin.posts.index');
    }

    /**
     * Hiển thị form sửa bài viết.
     */
    public function edit(Post $post): View
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get();

        return view(
            'admin.posts.edit',
            compact('post', 'categories')
        );
    }

    /**
     * Cập nhật bài viết.
     *
     * Sẽ hoàn thiện sau khi viết UpdatePostRequest.
     */
    public function update(
        UpdatePostRequest $request,
        Post $post
    ): RedirectResponse {
        return redirect()
            ->route('admin.posts.index');
    }

    /**
     * Xóa bài viết.
     *
     * Sẽ bổ sung xử lý thumbnail ở bài sau.
     */
    public function destroy(Post $post): RedirectResponse
    {
        return redirect()
            ->route('admin.posts.index');
    }
}