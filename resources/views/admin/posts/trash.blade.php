@extends('layouts.admin')

@section('title', 'Thùng rác bài viết')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Thùng rác bài viết
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Khôi phục hoặc xóa vĩnh viễn các bài viết đã xóa.
            </p>
        </div>

        <a
            href="{{ route('admin.posts.index') }}"
            class="rounded-lg border border-gray-300 bg-white
                   px-4 py-2.5 text-sm font-medium text-gray-700
                   hover:bg-gray-100"
        >
            ← Quay lại danh sách
        </a>
    </div>

    @if (session('success'))
        <div
            class="mb-6 rounded-lg border border-green-200
                   bg-green-50 p-4 text-sm text-green-800"
        >
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase text-gray-700">
                    <tr>
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Tiêu đề</th>
                        <th class="px-6 py-4">Danh mục</th>
                        <th class="px-6 py-4">Tác giả</th>
                        <th class="px-6 py-4">Ngày xóa</th>
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
                                {{ $post->category?->name
                                    ?? 'Không còn danh mục' }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $post->user?->name
                                    ?? 'Không còn tác giả' }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $post->deleted_at?->format('d/m/Y H:i') }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    {{-- Khôi phục --}}
                                    <form
                                        action="{{ route(
                                            'admin.posts.restore',
                                            ['trashedPost' => $post->id]
                                        ) }}"
                                        method="POST"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="rounded-lg bg-green-600
                                                   px-3 py-2 text-xs
                                                   font-medium text-white
                                                   hover:bg-green-700"
                                        >
                                            Khôi phục
                                        </button>
                                    </form>

                                    {{-- Xóa vĩnh viễn --}}
                                    <form
                                        action="{{ route(
                                            'admin.posts.force-delete',
                                            ['trashedPost' => $post->id]
                                        ) }}"
                                        method="POST"
                                        onsubmit="return confirm(
                                            'Thao tác này không thể hoàn tác. Bạn chắc chắn muốn xóa vĩnh viễn?'
                                        )"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="rounded-lg bg-red-600
                                                   px-3 py-2 text-xs
                                                   font-medium text-white
                                                   hover:bg-red-700"
                                        >
                                            Xóa vĩnh viễn
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
                                Thùng rác hiện đang trống.
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
