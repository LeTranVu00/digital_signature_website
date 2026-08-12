@extends('frontend.layouts.app')

@section('title', 'Dịch vụ - Digital Signature')

@section('content')
    @php
        $services = [
            ['number' => '01', 'title' => 'Chữ ký số cá nhân', 'description' => 'Phù hợp cá nhân cần ký hồ sơ điện tử, khai báo và giao dịch trực tuyến.'],
            ['number' => '02', 'title' => 'Chữ ký số doanh nghiệp', 'description' => 'Dành cho doanh nghiệp khai thuế, bảo hiểm, hải quan, ngân hàng và ký văn bản.'],
            ['number' => '03', 'title' => 'Chữ ký số từ xa', 'description' => 'Ký mọi lúc trên thiết bị được cấp quyền, không phụ thuộc USB token.'],
            ['number' => '04', 'title' => 'Hóa đơn điện tử', 'description' => 'Tư vấn phát hành, ký số và quản lý hóa đơn điện tử theo quy trình doanh nghiệp.'],
            ['number' => '05', 'title' => 'Hợp đồng điện tử', 'description' => 'Chuẩn hóa quy trình ký kết, lưu trữ và tra cứu hợp đồng điện tử.'],
            ['number' => '06', 'title' => 'Hỗ trợ triển khai', 'description' => 'Đồng hành trong cài đặt, kích hoạt và xử lý các vướng mắc sau bàn giao.'],
        ];

        $steps = ['Tư vấn nhu cầu', 'Chọn gói phù hợp', 'Chuẩn bị hồ sơ', 'Kích hoạt sử dụng'];
    @endphp

    <section class="relative overflow-hidden bg-zinc-950 py-16 text-white sm:py-20 lg:py-24" data-scroll-section="Dịch vụ">
        <div class="absolute inset-0 ui-mesh-bg opacity-70"></div>
        <div class="relative mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8" data-reveal="fade-up">
            <h1 class="site-page-title">
                Bộ giải pháp chữ ký số và tài liệu điện tử cho doanh nghiệp hiện đại.
            </h1>
            <p class="site-page-copy">
                Từ chữ ký số cá nhân, doanh nghiệp đến hóa đơn và hợp đồng điện tử, mỗi dịch vụ đều được tư vấn theo nhu cầu sử dụng thực tế.
            </p>
        </div>
    </section>

    <section class="site-section-cool" data-scroll-section="Nhóm dịch vụ">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center" data-reveal="fade-up">
                <h2 class="site-section-title">
                    Chọn đúng giải pháp ngay từ đầu.
                </h2>
            </div>

            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $index => $service)
                    <div data-reveal="fade-up" data-reveal-delay="{{ $index * 70 }}">
                        @include('frontend.components.service-card', $service)
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="site-section-warm" data-scroll-section="Quy trình">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center" data-reveal="fade-up">
                <h2 class="site-section-title">
                    Triển khai theo từng bước rõ ràng.
                </h2>
            </div>

            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($steps as $index => $step)
                    <article class="site-feature-card" data-reveal="fade-up" data-reveal-delay="{{ $index * 80 }}">
                        <div class="text-4xl font-extrabold text-amber-700">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</div>
                        <h3 class="mt-4 text-xl font-extrabold text-slate-950">{{ $step }}</h3>
                        <p class="mt-3 text-base font-medium leading-7 text-slate-600">Có người phụ trách theo sát để hồ sơ được xử lý mạch lạc.</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection
