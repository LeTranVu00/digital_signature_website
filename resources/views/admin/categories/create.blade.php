@extends('layouts.admin')

@section('title', 'Thêm danh mục')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('admin.categories.index') }}"
               class="text-sm font-medium text-blue-600 hover:text-blue-800">
                ← Quay lại danh sách
            </a>

            <h2 class="mt-3 text-2xl font-bold text-gray-900">
                Thêm danh mục mới
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Tạo danh mục để phân loại các bài viết trên website.
            </p>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm">
            <form action="{{ route('admin.categories.store') }}"
                  method="POST">
                @csrf

                {{-- Tên danh mục --}}
                <div class="mb-6">
                    <label for="name"
                           class="mb-2 block text-sm font-medium text-gray-900">
                        Tên danh mục
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        maxlength="100"
                        autofocus
                        placeholder="Ví dụ: Chữ ký số"
                        class="block w-full rounded-lg border p-3 text-sm
                               {{ $errors->has('name')
                                    ? 'border-red-500 bg-red-50 text-red-900'
                                    : 'border-gray-300 bg-gray-50 text-gray-900' }}
                               focus:border-blue-500 focus:ring-blue-500"
                    >

                    @error('name')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Mô tả --}}
                <div class="mb-6">
                    <label for="description"
                           class="mb-2 block text-sm font-medium text-gray-900">
                        Mô tả
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        maxlength="1000"
                        placeholder="Nhập mô tả ngắn cho danh mục..."
                        class="block w-full rounded-lg border p-3 text-sm
                               {{ $errors->has('description')
                                    ? 'border-red-500 bg-red-50 text-red-900'
                                    : 'border-gray-300 bg-gray-50 text-gray-900' }}
                               focus:border-blue-500 focus:ring-blue-500"
                    >{{ old('description') }}</textarea>

                    @error('description')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                    <p class="mt-2 text-xs text-gray-500">
                        Tối đa 1.000 ký tự.
                    </p>
                </div>
                {{-- Nút thao tác --}}
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
                    <a href="{{ route('admin.categories.index') }}"
                       class="rounded-lg border border-gray-300 bg-white px-5 py-2.5
                              text-sm font-medium text-gray-700 hover:bg-gray-100">
                        Hủy
                    </a>

                    <button type="submit"
                            class="rounded-lg bg-blue-600 px-5 py-2.5
                                   text-sm font-medium text-white
                                   hover:bg-blue-700
                                   focus:outline-none focus:ring-4 focus:ring-blue-300">
                        Lưu danh mục
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection