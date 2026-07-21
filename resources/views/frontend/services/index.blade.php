@extends('frontend.layouts.app')

@section('title', 'Dịch vụ - Digital Signature')

@section('content')
    <section class="bg-gray-950 py-20 text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="text-sm font-semibold uppercase text-blue-200">Dịch vụ</p>
            <h1 class="mt-4 max-w-3xl text-4xl font-bold leading-tight">
                Bộ giải pháp chữ ký số và tài liệu điện tử cho doanh nghiệp hiện đại
            </h1>
        </div>
    </section>

    <section class="bg-gray-50 py-16">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 md:grid-cols-2 lg:grid-cols-3 lg:px-8">
            @include('frontend.components.service-card', [
                'number' => '01',
                'title' => 'Chữ ký số cá nhân',
                'description' => 'Phù hợp cá nhân cần ký hồ sơ điện tử, khai báo và giao dịch trực tuyến.',
            ])
            @include('frontend.components.service-card', [
                'number' => '02',
                'title' => 'Chữ ký số doanh nghiệp',
                'description' => 'Dành cho doanh nghiệp khai thuế, bảo hiểm, hải quan, ngân hàng và ký văn bản.',
            ])
            @include('frontend.components.service-card', [
                'number' => '03',
                'title' => 'Chữ ký số từ xa',
                'description' => 'Ký mọi lúc trên thiết bị được cấp quyền, không phụ thuộc USB token.',
            ])
            @include('frontend.components.service-card', [
                'number' => '04',
                'title' => 'Hóa đơn điện tử',
                'description' => 'Tư vấn phát hành, ký số và quản lý hóa đơn điện tử theo quy trình doanh nghiệp.',
            ])
            @include('frontend.components.service-card', [
                'number' => '05',
                'title' => 'Hợp đồng điện tử',
                'description' => 'Chuẩn hóa quy trình ký kết, lưu trữ và tra cứu hợp đồng điện tử.',
            ])
        </div>
    </section>
@endsection
