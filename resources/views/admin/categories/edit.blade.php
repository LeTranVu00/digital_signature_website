@extends('layouts.admin')

@section('title', 'Sửa danh mục')

@section('content')
    <div class="mx-auto max-w-3xl">
        <x-ui.page-header
            title="Sửa danh mục"
            description="Cập nhật tên và mô tả của danh mục."
        >
            <x-slot name="breadcrumb">
                <x-ui.breadcrumb :items="[
                    ['label' => 'Danh mục', 'url' => route('admin.categories.index')],
                    ['label' => $category->name],
                ]" />
            </x-slot>
        </x-ui.page-header>

        <x-ui.card>
            <form
                action="{{ route('admin.categories.update', $category) }}"
                method="POST"
                x-data="{ submitting: false }"
                x-on:submit="submitting = true"
            >
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <x-ui.input
                        name="name"
                        label="Tên danh mục"
                        :value="old('name', $category->name)"
                        maxlength="100"
                        autofocus
                        required
                    />

                    <x-ui.input
                        id="slug-preview"
                        label="Đường dẫn hiện tại"
                        :value="$category->slug"
                        disabled
                        helper="Slug sẽ được tạo lại tự động khi tên danh mục thay đổi."
                    />

                    <x-ui.textarea
                        name="description"
                        label="Mô tả"
                        :value="old('description', $category->description)"
                        rows="5"
                        maxlength="1000"
                        helper="Tối đa 1.000 ký tự."
                    />
                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end">
                    <x-ui.button :href="route('admin.categories.index')" variant="secondary">
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
