@extends('layouts.admin')

@section('title', 'Sửa bài viết')

@section('content')
    <div class="mx-auto max-w-5xl">
        <x-ui.page-header
            title="Sửa bài viết"
            description="Cập nhật nội dung và trạng thái bài viết."
        >
            <x-slot name="breadcrumb">
                <x-ui.breadcrumb :items="[
                    ['label' => 'Bài viết', 'url' => route('admin.posts.index')],
                    ['label' => $post->title],
                ]" />
            </x-slot>
        </x-ui.page-header>

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
                <h3 class="font-semibold text-red-800">Dữ liệu chưa hợp lệ</h3>
                <ul class="mt-2 list-inside list-disc text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <x-ui.card>
            <form
                action="{{ route('admin.posts.update', $post) }}"
                method="POST"
                enctype="multipart/form-data"
                x-data="postEditorForm()"
                x-on:submit="markSubmitting()"
            >
                @csrf
                @method('PUT')

                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="space-y-6 lg:col-span-2">
                        <x-ui.input
                            name="title"
                            label="Tiêu đề"
                            :value="old('title', $post->title)"
                            maxlength="255"
                            autofocus
                            required
                            placeholder="Nhập tiêu đề bài viết"
                        />

                        <x-ui.textarea
                            name="summary"
                            label="Mô tả ngắn"
                            :value="old('summary', $post->summary)"
                            rows="4"
                            maxlength="500"
                            placeholder="Nhập phần giới thiệu ngắn"
                            helper="Tối đa 500 ký tự."
                        />

                        <x-ui.textarea
                            name="content"
                            label="Nội dung"
                            :value="old('content', $post->content)"
                            rows="16"
                            required
                            class="tinymce-editor"
                            placeholder="Nhập nội dung bài viết"
                        />
                    </div>

                    <div class="space-y-6">
                        <x-ui.select
                            name="category_id"
                            label="Danh mục"
                            required
                        >
                            <option value="">-- Chọn danh mục --</option>

                            @foreach ($categories as $category)
                                <option
                                    value="{{ $category->id }}"
                                    @selected(old('category_id', $post->category_id) == $category->id)
                                >
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.select
                            name="status"
                            label="Trạng thái"
                            required
                        >
                            <option value="draft" @selected(old('status', $post->status) === 'draft')>
                                Bản nháp
                            </option>

                            <option value="published" @selected(old('status', $post->status) === 'published')>
                                Xuất bản
                            </option>
                        </x-ui.select>

                        <div>
                            <p class="ui-label">Ảnh đại diện hiện tại</p>

                            @if ($post->thumbnail)
                                <img
                                    src="{{ asset('storage/' . $post->thumbnail) }}"
                                    alt="{{ $post->title }}"
                                    class="h-44 w-full rounded-xl border border-slate-200 object-cover"
                                    loading="lazy"
                                    decoding="async"
                                >
                            @else
                                <x-ui.empty-state
                                    class="py-12"
                                    description="Chưa có ảnh đại diện"
                                />
                            @endif
                        </div>

                        <div>
                            <x-ui.input
                                type="file"
                                name="thumbnail"
                                label="Thay ảnh đại diện"
                                accept=".jpg,.jpeg,.png,.webp"
                                x-on:change="previewThumbnail($event)"
                                helper="Không chọn ảnh mới thì hệ thống giữ ảnh hiện tại."
                            />

                            <div
                                x-show="thumbnailPreview"
                                x-cloak
                                class="mt-3 overflow-hidden rounded-xl border border-blue-200 bg-blue-50"
                            >
                                <p class="px-3 py-2 text-xs font-semibold text-blue-700">
                                    Preview ảnh mới
                                </p>
                                <img
                                    x-bind:src="thumbnailPreview"
                                    alt="Preview ảnh đại diện mới"
                                    class="h-44 w-full object-cover"
                                >
                            </div>
                        </div>

                        <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 text-sm leading-6 text-blue-800">
                            <p><strong>Slug:</strong> {{ $post->slug }}</p>
                            <p class="mt-2">
                                <strong>Ngày xuất bản:</strong>
                                {{ $post->published_at?->format('d/m/Y H:i') ?? 'Chưa xuất bản' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end">
                    <x-ui.button :href="route('admin.posts.index')" variant="secondary">
                        Hủy
                    </x-ui.button>

                    <x-ui.submit-button loading-text="Đang lưu...">
                        Lưu thay đổi
                    </x-ui.submit-button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
