@extends('layouts.admin')

@section('title', 'Quản lý danh mục')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Danh sách danh mục
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Quản lý các danh mục bài viết của website.
            </p>
        </div>

        <a
            href="{{ route('admin.categories.create') }}"
            class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white
                   hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300"
        >
            + Thêm danh mục
        </a>
    </div>

    @if (session('success'))
        <div
            class="mb-6 rounded-lg border border-green-200 bg-green-50
                   p-4 text-sm text-green-800"
        >
            {{ session('success') }}
        </div>
    @endif
    
    @if (session('error'))
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
        {{ session('error') }}
    </div>
    @endif

    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase text-gray-700">
                    <tr>
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Tên danh mục</th>
                        <th class="px-6 py-4">Slug</th>
                        <th class="px-6 py-4">Mô tả</th>
                        <th class="px-6 py-4 text-right">Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($categories as $category)
                        <tr class="border-t border-gray-100 hover:bg-gray-50">
                            <td class="px-6 py-4">
                                {{ $category->id }}
                            </td>

                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $category->name }}
                            </td>

                            <td class="px-6 py-4">
                                <span class="rounded bg-gray-100 px-2 py-1 text-xs">
                                    {{ $category->slug }}
                                </span>
                            </td>

                            <td class="max-w-md px-6 py-4">
                                {{ $category->description ?: 'Chưa có mô tả' }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <a
                                        href="{{ route('admin.categories.edit', $category) }}"
                                        class="rounded-lg bg-amber-500 px-3 py-2
                                               text-xs font-medium text-white
                                               hover:bg-amber-600"
                                    >
                                        Sửa
                                    </a>

                                    <form
                                        action="{{ route('admin.categories.destroy', $category) }}"
                                        method="POST"
                                        onsubmit="return confirm('Bạn chắc chắn muốn xóa danh mục này?')"
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
                                colspan="5"
                                class="px-6 py-12 text-center text-gray-500"
                            >
                                Chưa có danh mục nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $categories->links() }}
    </div>
@endsection