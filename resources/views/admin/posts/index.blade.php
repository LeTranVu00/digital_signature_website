@extends('layouts.admin')

@section('title', 'Quản lý bài viết')
@if (session('success'))
    <div
        class="mb-6 rounded-lg border border-green-200
               bg-green-50 p-4 text-sm text-green-800"
    >
        {{ session('success') }}
    </div>
@endif
@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Danh sách bài viết
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Quản lý nội dung bài viết trên website.
            </p>
        </div>

        <a
            href="{{ route('admin.posts.create') }}"
            class="rounded-lg bg-blue-600 px-4 py-2.5
                   text-sm font-medium text-white
                   hover:bg-blue-700"
        >
            + Thêm bài viết
        </a>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase text-gray-700">
                    <tr>
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Tiêu đề</th>
                        <th class="px-6 py-4">Danh mục</th>
                        <th class="px-6 py-4">Tác giả</th>
                        <th class="px-6 py-4">Trạng thái</th>
                        <th class="px-6 py-4 text-right">Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($posts as $post)
                        <tr class="border-t border-gray-100 hover:bg-gray-50">
                            <td class="px-6 py-4">
                                {{ $post->id }}
                            </td>

                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $post->title }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $post->category->name }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $post->user->name }}
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
                                <div class="flex justify-end gap-2">
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
                                        onsubmit="
                                            return confirm(
                                                'Bạn chắc chắn muốn chuyển bài viết này vào thùng rác?'
                                            )
                                        "
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
                                colspan="6"
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