<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Storage;
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
        $data = $request->validated();

        $data['user_id'] = $request->user()->id;
        $data['views'] = 0;

        $data['published_at'] =
            $data['status'] === 'published'
                ? now()
                : null;

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request
                ->file('thumbnail')
                ->store('posts', 'public');
        }

        Post::create($data);

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Thêm bài viết thành công.');
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
        $data = $request->validated();

        /*
        * Quản lý thời gian xuất bản.
        */
        if ($data['status'] === 'published') {
            /*
            * Bài chưa từng xuất bản:
            * gán thời gian hiện tại.
            *
            * Bài đã xuất bản:
            * giữ nguyên ngày xuất bản cũ.
            */
            $data['published_at'] = $post->published_at ?? now();
        } else {
            /*
            * Chuyển về bản nháp.
            */
            $data['published_at'] = null;
        }

        /*
        * Ghi nhớ thumbnail cũ trước khi cập nhật.
        */
        $oldThumbnail = $post->thumbnail;

        /*
        * Nếu Admin chọn ảnh mới thì lưu ảnh mới.
        */
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request
                ->file('thumbnail')
                ->store('posts', 'public');
        }

        /*
        * Cập nhật dữ liệu bài viết.
        */
        $post->update($data);

        /*
        * Chỉ xóa ảnh cũ sau khi bài viết đã cập nhật thành công.
        */
        if (
            $request->hasFile('thumbnail') &&
            $oldThumbnail &&
            Storage::disk('public')->exists($oldThumbnail)
        ) {
            Storage::disk('public')->delete($oldThumbnail);
        }

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Cập nhật bài viết thành công.');
    }

    /**
     * Xóa bài viết.
     *
     * Sẽ bổ sung xử lý thumbnail ở bài sau.
     */
    /**
     * Chuyển bài viết vào thùng rác.
     */
    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()
            ->route('admin.posts.index')
            ->with(
                'success',
                'Đã chuyển bài viết vào thùng rác.'
            );
    }
}