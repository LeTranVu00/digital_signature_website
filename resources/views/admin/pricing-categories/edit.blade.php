@extends('layouts.admin')

@section('title', 'Sửa danh mục báo giá')

@section('content')
    <div class="mx-auto max-w-4xl">
        <x-ui.page-header
            title="Sửa danh mục báo giá"
            description="Cập nhật tên, mô tả, trạng thái hoặc thay ảnh bảng giá."
        >
            <x-slot name="breadcrumb">
                <x-ui.breadcrumb :items="[
                    ['label' => 'Danh mục báo giá', 'url' => route('admin.pricing-categories.index')],
                    ['label' => 'Sửa'],
                ]" />
            </x-slot>
        </x-ui.page-header>

        @include('admin.pricing-categories.form', [
            'action' => route('admin.pricing-categories.update', $pricingCategory),
            'method' => 'PATCH',
            'pricingCategory' => $pricingCategory,
        ])
    </div>
@endsection
