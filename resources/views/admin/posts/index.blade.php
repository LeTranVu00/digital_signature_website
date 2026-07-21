@extends('layouts.admin')

@section('title', 'Quản lý bài viết')

@section('content')
    @if (session('success'))
        <div
            class="mb-6 rounded-lg border border-green-200
                   bg-green-50 p-4 text-sm text-green-800"
        >
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Danh sách bài viết
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Quản lý nội dung bài viết trên website.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a
                href="{{ route('admin.posts.trash') }}"
                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100"
            >
                Thùng rác
            </a>

            <a
                href="{{ route('admin.posts.create') }}"
                class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700"
            >
                + Thêm bài viết
            </a>
        </div>
    </div>

    <form
        action="{{ route('admin.posts.index') }}"
        method="GET"
        class="mb-6 grid gap-4 rounded-xl bg-white p-5 shadow-sm lg:grid-cols-5"
    >
        <div class="lg:col-span-2">
            <label
                for="search"
                class="mb-2 block text-sm font-medium text-gray-900"
            >
                Tìm theo tiêu đề
            </label>

            <input
                type="search"
                id="search"
                name="search"
                value="{{ $filters['search'] ?? '' }}"
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="Nhập từ khóa..."
            >
        </div>

        <div>
            <label
                for="category_id"
                class="mb-2 block text-sm font-medium text-gray-900"
            >
                Danh mục
            </label>

            <select
                id="category_id"
                name="category_id"
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm focus:border-blue-500 focus:ring-blue-500"
            >
                <option value="">Tất cả</option>

                @foreach ($categories as $category)
                    <option
                        value="{{ $category->id }}"
                        @selected(($filters['category_id'] ?? '') == $category->id)
                    >
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label
                for="status"
                class="mb-2 block text-sm font-medium text-gray-900"
            >
                Trạng thái
            </label>

            <select
                id="status"
                name="status"
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm focus:border-blue-500 focus:ring-blue-500"
            >
                <option value="">Tất cả</option>
                <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>
                    Bản nháp
                </option>
                <option value="published" @selected(($filters['status'] ?? '') === 'published')>
                    Đã xuất bản
                </option>
            </select>
        </div>

        <div>
            <label
                for="sort"
                class="mb-2 block text-sm font-medium text-gray-900"
            >
                Sắp xếp
            </label>

            <select
                id="sort"
                name="sort"
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm focus:border-blue-500 focus:ring-blue-500"
            >
                <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>
                    Mới nhất
                </option>
                <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>
                    Cũ nhất
                </option>
            </select>
        </div>

        <div class="flex items-end gap-3 lg:col-span-5">
            <button
                type="submit"
                class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700"
            >
                Lọc bài viết
            </button>

            <a
                href="{{ route('admin.posts.index') }}"
                class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100"
            >
                Xóa lọc
            </a>
        </div>
    </form>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase text-gray-700">
                    <tr>
                        <th class="px-6 py-4">Thumbnail</th>
                        <th class="px-6 py-4">Tiêu đề</th>
                        <th class="px-6 py-4">Danh mục</th>
                        <th class="px-6 py-4">Tác giả</th>
                        <th class="px-6 py-4">Trạng thái</th>
                        <th class="px-6 py-4">Ngày tạo</th>
                        <th class="px-6 py-4">Ngày xuất bản</th>
                        <th class="px-6 py-4 text-right">Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($posts as $post)
                        <tr class="border-t border-gray-100 hover:bg-gray-50">
                            <td class="px-6 py-4">
                                @if ($post->thumbnail)
                                    <img
                                        src="{{ asset('storage/' . $post->thumbnail) }}"
                                        alt="{{ $post->title }}"
                                        class="h-14 w-20 rounded-lg object-cover"
                                    >
                                @else
                                    <div
                                        class="flex h-14 w-20 items-center justify-center rounded-lg bg-gray-100 text-xs text-gray-400"
                                    >
                                        No image
                                    </div>
                                @endif
                            </td>

                            <td class="px-6 py-4 font-medium text-gray-900">
                                <div>{{ $post->title }}</div>
                                <div class="mt-1 text-xs font-normal text-gray-400">
                                    {{ $post->slug }}
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                {{ $post->category?->name ?? 'Không còn danh mục' }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $post->user?->name ?? 'Không còn tác giả' }}
                            </td>

                            <td class="px-6 py-4">
                                @if ($post->status === 'published')
                                    <span class="rounded-full bg-green-100 px-3 py-1
                                                 text-xs font-medium text-green-700">
                                        Đã xuất bản
                                    </span>
                                @else
                                    <span class="rounded-full bg-yellow-100 px-3 py-1
                                                 text-xs font-medium text-yellow-700">
                                        Bản nháp
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                {{ $post->created_at?->format('d/m/Y H:i') }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $post->published_at?->format('d/m/Y H:i') ?? 'Chưa xuất bản' }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <a
                                        href="{{ route('admin.posts.preview', $post) }}"
                                        target="_blank"
                                        class="rounded-lg bg-gray-700 px-3 py-2
                                               text-xs font-medium text-white
                                               hover:bg-gray-800"
                                    >
                                        Xem
                                    </a>

                                    <a
                                        href="{{ route('admin.posts.edit', $post) }}"
                                        class="rounded-lg bg-amber-500 px-3 py-2
                                               text-xs font-medium text-white
                                               hover:bg-amber-600"
                                    >
                                        Sửa
                                    </a>

                                    <form
                                        action="{{ route('admin.posts.destroy', $post) }}"
                                        method="POST"
                                        onsubmit="return confirm(
                                            'Bạn chắc chắn muốn chuyển bài viết này vào thùng rác?'
                                        )"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="rounded-lg bg-red-600 px-3 py-2
                                                text-xs font-medium text-white
                                                hover:bg-red-700"
                                        >
                                            Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="8"
                                class="px-6 py-12 text-center text-gray-500"
                            >
                                Chưa có bài viết nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $posts->links() }}
    </div>
@endsection
