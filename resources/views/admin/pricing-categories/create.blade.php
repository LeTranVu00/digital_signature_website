@extends('layouts.admin')

@section('title', 'Thêm danh mục báo giá')

@section('content')
    <div class="mx-auto max-w-4xl">
        <x-ui.page-header
            title="Thêm danh mục báo giá"
            description="Tạo danh mục đại diện và upload ảnh bảng giá cho người dùng xem."
        >
            <x-slot name="breadcrumb">
                <x-ui.breadcrumb :items="[
                    ['label' => 'Danh mục báo giá', 'url' => route('admin.pricing-categories.index')],
                    ['label' => 'Thêm mới'],
                ]" />
            </x-slot>
        </x-ui.page-header>

        @include('admin.pricing-categories.form', [
            'action' => route('admin.pricing-categories.store'),
            'method' => 'POST',
            'pricingCategory' => null,
        ])
    </div>
@endsection
