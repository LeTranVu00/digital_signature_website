@extends('frontend.layouts.app')

@section('title', 'Liên hệ - Digital Signature')

@section('content')
    <section class="bg-gray-950 py-20 text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="text-sm font-semibold uppercase text-blue-200">Liên hệ</p>
            <h1 class="mt-4 max-w-3xl text-4xl font-bold leading-tight">
                Nhận tư vấn giải pháp chữ ký số phù hợp với nhu cầu của bạn
            </h1>
        </div>
    </section>

    <section class="bg-white py-16">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div class="rounded-xl border border-gray-200 p-6">
                <h2 class="font-bold text-gray-950">Hotline</h2>
                <p class="mt-3 text-gray-600">0900 000 000</p>
            </div>
            <div class="rounded-xl border border-gray-200 p-6">
                <h2 class="font-bold text-gray-950">Email</h2>
                <p class="mt-3 text-gray-600">support@example.com</p>
            </div>
            <div class="rounded-xl border border-gray-200 p-6">
                <h2 class="font-bold text-gray-950">Thời gian hỗ trợ</h2>
                <p class="mt-3 text-gray-600">Thứ 2 - Thứ 6, 8:00 - 17:30</p>
            </div>
        </div>
    </section>
@endsection
