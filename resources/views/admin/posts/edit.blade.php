@extends('layouts.admin')

@section('title', 'Sửa bài viết')

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="mb-6">
            <a
                href="{{ route('admin.posts.index') }}"
                class="text-sm font-medium text-blue-600 hover:text-blue-800"
            >
                ← Quay lại danh sách
            </a>

            <h2 class="mt-3 text-2xl font-bold text-gray-900">
                Sửa bài viết
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Cập nhật nội dung và trạng thái bài viết.
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">
                <h3 class="font-medium text-red-800">
                    Dữ liệu chưa hợp lệ
                </h3>

                <ul class="mt-2 list-inside list-disc text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            action="{{ route('admin.posts.update', $post) }}"
            method="POST"
            enctype="multipart/form-data"
            class="rounded-xl bg-white p-6 shadow-sm"
        >
            @csrf
            @method('PUT')

            <div class="grid gap-6 lg:grid-cols-3">
                {{-- Cột nội dung chính --}}
                <div class="space-y-6 lg:col-span-2">
                    {{-- Tiêu đề --}}
                    <div>
                        <label
                            for="title"
                            class="mb-2 block text-sm font-medium text-gray-900"
                        >
                            Tiêu đề
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="{{ old('title', $post->title) }}"
                            maxlength="255"
                            autofocus
                            class="block w-full rounded-lg border p-3 text-sm
                                   {{ $errors->has('title')
                                        ? 'border-red-500 bg-red-50'
                                        : 'border-gray-300 bg-gray-50' }}
                                   focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Nhập tiêu đề bài viết"
                        >

                        @error('title')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Mô tả ngắn --}}
                    <div>
                        <label
                            for="summary"
                            class="mb-2 block text-sm font-medium text-gray-900"
                        >
                            Mô tả ngắn
                        </label>

                        <textarea
                            id="summary"
                            name="summary"
                            rows="4"
                            maxlength="500"
                            class="block w-full rounded-lg border p-3 text-sm
                                   {{ $errors->has('summary')
                                        ? 'border-red-500 bg-red-50'
                                        : 'border-gray-300 bg-gray-50' }}
                                   focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Nhập phần giới thiệu ngắn"
                        >{{ old('summary', $post->summary) }}</textarea>

                        @error('summary')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                        <p class="mt-2 text-xs text-gray-500">
                            Tối đa 500 ký tự.
                        </p>
                    </div>

                    {{-- Nội dung --}}
                    <div>
                        <label
                            for="content"
                            class="mb-2 block text-sm font-medium text-gray-900"
                        >
                            Nội dung
                            <span class="text-red-500">*</span>
                        </label>

                        <textarea
                            id="content"
                            name="content"
                            rows="16"
                            class="tinymce-editor block w-full rounded-lg border p-3 text-sm
                                   {{ $errors->has('content')
                                         ? 'border-red-500 bg-red-50'
                                        : 'border-gray-300 bg-gray-50' }}
                                   focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Nhập nội dung bài viết"
                        >{{ old('content', $post->content) }}</textarea>

                        @error('content')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                {{-- Cột thiết lập --}}
                <div class="space-y-6">
                    {{-- Danh mục --}}
                    <div>
                        <label
                            for="category_id"
                            class="mb-2 block text-sm font-medium text-gray-900"
                        >
                            Danh mục
                            <span class="text-red-500">*</span>
                        </label>

                        <select
                            id="category_id"
                            name="category_id"
                            class="block w-full rounded-lg border p-3 text-sm
                                   {{ $errors->has('category_id')
                                        ? 'border-red-500 bg-red-50'
                                        : 'border-gray-300 bg-gray-50' }}
                                   focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">
                                -- Chọn danh mục --
                            </option>

                            @foreach ($categories as $category)
                                <option
                                    value="{{ $category->id }}"
                                    @selected(
                                        old(
                                            'category_id',
                                            $post->category_id
                                        ) == $category->id
                                    )
                                >
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('category_id')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Trạng thái --}}
                    <div>
                        <label
                            for="status"
                            class="mb-2 block text-sm font-medium text-gray-900"
                        >
                            Trạng thái
                            <span class="text-red-500">*</span>
                        </label>

                        <select
                            id="status"
                            name="status"
                            class="block w-full rounded-lg border
                                   border-gray-300 bg-gray-50 p-3 text-sm
                                   focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option
                                value="draft"
                                @selected(
                                    old('status', $post->status) === 'draft'
                                )
                            >
                                Bản nháp
                            </option>

                            <option
                                value="published"
                                @selected(
                                    old('status', $post->status) === 'published'
                                )
                            >
                                Xuất bản
                            </option>
                        </select>

                        @error('status')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Thumbnail hiện tại --}}
                    <div>
                        <p class="mb-2 text-sm font-medium text-gray-900">
                            Ảnh đại diện hiện tại
                        </p>

                        @if ($post->thumbnail)
                            <img
                                src="{{ asset('storage/' . $post->thumbnail) }}"
                                alt="{{ $post->title }}"
                                class="h-44 w-full rounded-lg border
                                       border-gray-200 object-cover"
                            >
                        @else
                            <div
                                class="flex h-44 items-center justify-center
                                       rounded-lg border border-dashed
                                       border-gray-300 bg-gray-50
                                       text-sm text-gray-500"
                            >
                                Chưa có ảnh đại diện
                            </div>
                        @endif
                    </div>

                    {{-- Thumbnail mới --}}
                    <div>
                        <label
                            for="thumbnail"
                            class="mb-2 block text-sm font-medium text-gray-900"
                        >
                            Thay ảnh đại diện
                        </label>

                        <input
                            type="file"
                            id="thumbnail"
                            name="thumbnail"
                            accept=".jpg,.jpeg,.png,.webp"
                            class="block w-full rounded-lg border
                                   border-gray-300 bg-gray-50 text-sm
                                   file:mr-4 file:border-0
                                   file:bg-blue-600 file:px-4 file:py-3
                                   file:text-sm file:font-medium
                                   file:text-white hover:file:bg-blue-700"
                        >

                        @error('thumbnail')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                        <p class="mt-2 text-xs text-gray-500">
                            Không chọn ảnh mới thì hệ thống giữ ảnh hiện tại.
                        </p>
                    </div>

                    {{-- Thông tin xuất bản --}}
                    <div class="rounded-lg bg-blue-50 p-4 text-sm text-blue-800">
                        <p>
                            <strong>Slug:</strong>
                            {{ $post->slug }}
                        </p>

                        <p class="mt-2">
                            <strong>Ngày xuất bản:</strong>

                            @if ($post->published_at)
                                {{ $post->published_at->format('d/m/Y H:i') }}
                            @else
                                Chưa xuất bản
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3 border-t border-gray-200 pt-6">
                <a
                    href="{{ route('admin.posts.index') }}"
                    class="rounded-lg border border-gray-300 bg-white
                           px-5 py-2.5 text-sm font-medium text-gray-700
                           hover:bg-gray-100"
                >
                    Hủy
                </a>

                <button
                    type="submit"
                    class="rounded-lg bg-blue-600 px-5 py-2.5
                           text-sm font-medium text-white
                           hover:bg-blue-700
                           focus:outline-none focus:ring-4 focus:ring-blue-300"
                >
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
@endsection
