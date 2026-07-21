<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PostController extends Controller
{
    /**
     * Hiển thị danh sách bài viết.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],
            'category_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],
            'status' => [
                'nullable',
                'in:draft,published',
            ],
            'sort' => [
                'nullable',
                'in:latest,oldest',
            ],
        ]);

        $posts = Post::query()
            ->with(['category', 'user'])
            ->search($filters['search'] ?? null)
            ->category($filters['category_id'] ?? null)
            ->status($filters['status'] ?? null)
            ->sortByCreatedDate($filters['sort'] ?? 'latest')
            ->paginate(10)
            ->withQueryString();

        $categories = Category::query()
            ->orderBy('name')
            ->get();

        return view('admin.posts.index', compact(
            'categories',
            'filters',
            'posts'
        ));
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
    public function store(
        StorePostRequest $request,
        SlugService $slugService
    ): RedirectResponse
    {
        $data = $request->validated();

        $data['user_id'] = $request->user()->id;
        $data['views'] = 0;
        $data['slug'] = $slugService->forPostTitle($data['title']);

        $data['published_at'] =
            $data['status'] === 'published'
                ? now()
                : null;

        $newThumbnail = null;

        if ($request->hasFile('thumbnail')) {
            $newThumbnail = $request
                ->file('thumbnail')
                ->store('posts', 'public');

            $data['thumbnail'] = $newThumbnail;
        }

        try {
            Post::create($data);
        } catch (Throwable $exception) {
            if (
                $newThumbnail &&
                Storage::disk('public')->exists($newThumbnail)
            ) {
                Storage::disk('public')->delete($newThumbnail);
            }

            throw $exception;
        }

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
        Post $post,
        SlugService $slugService
    ): RedirectResponse {
        $data = $request->validated();
        $data['slug'] = $slugService->forPostTitle($data['title'], $post);

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
        $newThumbnail = null;

        /*
        * Nếu Admin chọn ảnh mới thì lưu ảnh mới.
        */
        if ($request->hasFile('thumbnail')) {
            $newThumbnail = $request
                ->file('thumbnail')
                ->store('posts', 'public');

            $data['thumbnail'] = $newThumbnail;
        }

        /*
        * Cập nhật dữ liệu bài viết.
        */
        try {
            $post->update($data);
        } catch (Throwable $exception) {
            if (
                $newThumbnail &&
                Storage::disk('public')->exists($newThumbnail)
            ) {
                Storage::disk('public')->delete($newThumbnail);
            }

            throw $exception;
        }

        /*
        * Chỉ xóa ảnh cũ sau khi bài viết đã cập nhật thành công.
        */
        if (
            $newThumbnail &&
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
     * Xem trước bài viết trong khu vực quản trị.
     */
    public function preview(Post $post): View
    {
        $post->load(['category', 'user']);

        return view('admin.posts.preview', compact('post'));
    }

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
    /**
     * Hiển thị danh sách bài viết đã xóa mềm.
     */
    public function trash(): View
    {
        $posts = Post::onlyTrashed()
            ->with(['category', 'user'])
            ->latest('deleted_at')
            ->paginate(10);

        return view('admin.posts.trash', compact('posts'));
    }
    /**
     * Khôi phục bài viết từ thùng rác.
     */
    public function restore(int $trashedPost): RedirectResponse
    {
        $post = Post::onlyTrashed()
            ->findOrFail($trashedPost);

        $post->restore();

        return redirect()
            ->route('admin.posts.trash')
            ->with('success', 'Khôi phục bài viết thành công.');
    }
    /**
     * Xóa vĩnh viễn bài viết và thumbnail.
     */
    public function forceDelete(int $trashedPost): RedirectResponse
    {
        $post = Post::onlyTrashed()
            ->findOrFail($trashedPost);

        $thumbnail = $post->thumbnail;

        /*
        * Xóa vĩnh viễn bản ghi trước.
        */
        $post->forceDelete();

        /*
        * Sau khi database đã xóa thành công,
        * tiến hành xóa thumbnail.
        */
        if (
            $thumbnail &&
            Storage::disk('public')->exists($thumbnail)
        ) {
            Storage::disk('public')->delete($thumbnail);
        }

        return redirect()
            ->route('admin.posts.trash')
            ->with('success', 'Đã xóa vĩnh viễn bài viết.');
    }
}
