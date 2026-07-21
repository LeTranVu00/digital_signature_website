@extends('layouts.admin')

@section('title', 'Thêm bài viết')

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
                Thêm bài viết
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Nhập thông tin để tạo một bài viết mới.
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
            action="{{ route('admin.posts.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="rounded-xl bg-white p-6 shadow-sm"
        >
            @csrf

            <div class="grid gap-6 lg:grid-cols-3">
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
                            value="{{ old('title') }}"
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
                            placeholder="Nhập phần giới thiệu ngắn cho bài viết"
                        >{{ old('summary') }}</textarea>

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
                        >{{ old('content') }}</textarea>

                        @error('content')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

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
                                        old('category_id') == $category->id
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
                                @selected(old('status', 'draft') === 'draft')
                            >
                                Bản nháp
                            </option>

                            <option
                                value="published"
                                @selected(old('status') === 'published')
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

                    {{-- Thumbnail --}}
                    <div>
                        <label
                            for="thumbnail"
                            class="mb-2 block text-sm font-medium text-gray-900"
                        >
                            Ảnh đại diện
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
                            JPG, JPEG, PNG hoặc WEBP. Tối đa 5 MB.
                        </p>
                    </div>

                    <div class="rounded-lg bg-blue-50 p-4 text-sm text-blue-800">
                        Slug sẽ được tạo tự động từ tiêu đề bài viết.
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
                    Lưu bài viết
                </button>
            </div>
        </form>
    </div>
@endsection
