@extends('frontend.layouts.app')

@section('title', 'Giới thiệu - Digital Signature')

@section('content')
    <section class="bg-gray-950 py-20 text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="text-sm font-semibold uppercase text-blue-200">Giới thiệu</p>
            <h1 class="mt-4 max-w-3xl text-4xl font-bold leading-tight">
                Đơn vị tư vấn giải pháp chữ ký số và giao dịch điện tử cho doanh nghiệp
            </h1>
        </div>
    </section>

    <section class="bg-white py-16">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-950">Tập trung vào triển khai thực tế</h2>
                <p class="mt-5 leading-8 text-gray-600">
                    Digital Signature hỗ trợ doanh nghiệp chuẩn hóa hồ sơ, kích hoạt chữ ký số và áp dụng vào các quy trình thuế, hóa đơn, hợp đồng, bảo hiểm và giao dịch trực tuyến.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach (['Tư vấn rõ nhu cầu', 'Hồ sơ gọn', 'Kích hoạt nhanh', 'Hỗ trợ sau bán'] as $item)
                    <div class="rounded-xl border border-gray-200 p-5 text-sm font-semibold text-gray-800">
                        {{ $item }}
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
