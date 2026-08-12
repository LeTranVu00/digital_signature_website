@extends('layouts.admin')

@section('title', 'Thêm danh mục')

@section('content')
    <div class="mx-auto max-w-3xl">
        <x-ui.page-header
            title="Thêm danh mục mới"
            description="Tạo danh mục để phân loại các bài viết trên website."
        >
            <x-slot name="breadcrumb">
                <x-ui.breadcrumb :items="[
                    ['label' => 'Danh mục', 'url' => route('admin.categories.index')],
                    ['label' => 'Thêm mới'],
                ]" />
            </x-slot>
        </x-ui.page-header>

        <x-ui.card>
            <form
                action="{{ route('admin.categories.store') }}"
                method="POST"
                x-data="{ submitting: false }"
                x-on:submit="submitting = true"
            >
                @csrf

                <div class="space-y-6">
                    <x-ui.input
                        name="name"
                        label="Tên danh mục"
                        :value="old('name')"
                        maxlength="100"
                        autofocus
                        required
                        placeholder="Ví dụ: Chữ ký số"
                    />

                    <x-ui.textarea
                        name="description"
                        label="Mô tả"
                        :value="old('description')"
                        rows="5"
                        maxlength="1000"
                        placeholder="Nhập mô tả ngắn cho danh mục..."
                        helper="Tối đa 1.000 ký tự."
                    />
                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-end">
                    <x-ui.button :href="route('admin.categories.index')" variant="secondary">
                        Hủy
                    </x-ui.button>

                    <x-ui.submit-button loading-text="Đang lưu...">
                        Lưu danh mục
                    </x-ui.submit-button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
