@extends('frontend.layouts.app')

@section('title', 'Giới thiệu - CHỮ KÝ SỐ VIP')

@section('content')
    @php
        $values = [
            ['title' => 'Tư vấn rõ nhu cầu', 'desc' => 'Xác định đúng loại chứng thư, phạm vi sử dụng và quy trình ký trước khi triển khai.'],
            ['title' => 'Hồ sơ gọn', 'desc' => 'Hướng dẫn giấy tờ cần chuẩn bị theo từng nhóm khách hàng để hạn chế bổ sung nhiều lần.'],
            ['title' => 'Kích hoạt nhanh', 'desc' => 'Theo sát quá trình xác thực, bàn giao và hướng dẫn sử dụng ban đầu.'],
            ['title' => 'Hỗ trợ sau bán', 'desc' => 'Đồng hành khi khách hàng cần ký thuế, hóa đơn, hợp đồng hoặc xử lý phát sinh.'],
        ];

        $metrics = [
            ['value' => '10+', 'label' => 'năm kinh nghiệm'],
            ['value' => '4', 'label' => 'nhóm dịch vụ trọng tâm'],
            ['value' => '24/7', 'label' => 'kênh tiếp nhận hỗ trợ'],
        ];
    @endphp

    <section class="relative overflow-hidden bg-zinc-950 py-16 text-white sm:py-20 lg:py-24" data-scroll-section="Giới thiệu">
        <div class="absolute inset-0 ui-mesh-bg opacity-70"></div>
        <div class="relative mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8" data-reveal="fade-up">
            <h1 class="site-page-title">
                Đơn vị tư vấn giải pháp chữ ký số và giao dịch điện tử cho doanh nghiệp.
            </h1>
            <p class="site-page-copy">
                Chúng tôi giúp khách hàng chuẩn hóa hồ sơ, kích hoạt chữ ký số và ứng dụng vào thuế, hóa đơn, hợp đồng, bảo hiểm và giao dịch trực tuyến.
            </p>
        </div>
    </section>

    <section class="site-section-cool" data-scroll-section="Cách làm việc">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:px-8">
            <div data-reveal="fade-right">
                <h2 class="site-section-title">
                    Tập trung vào triển khai thực tế, không chỉ bán dịch vụ.
                </h2>
                <p class="mt-5 site-section-copy">
                    Mỗi khách hàng có nhu cầu ký số khác nhau. Vì vậy chúng tôi bắt đầu từ việc hiểu quy trình hiện tại, sau đó đề xuất phương án đủ dùng, dễ triển khai và dễ kiểm soát.
                </p>

                <div class="mt-8 grid gap-3 sm:grid-cols-3">
                    @foreach ($metrics as $metric)
                        <div class="rounded-lg border border-amber-200/80 bg-amber-50/70 p-4 text-center shadow-sm">
                            <div class="text-3xl font-bold text-amber-700">{{ $metric['value'] }}</div>
                            <p class="mt-1 text-xs font-semibold uppercase text-slate-500">{{ $metric['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($values as $index => $item)
                    <article class="site-feature-card" data-reveal="fade-up" data-reveal-delay="{{ $index * 80 }}">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-sm font-bold text-amber-700">
                            {{ $index + 1 }}
                        </div>
                        <h3 class="mt-4 text-xl font-extrabold text-slate-950">{{ $item['title'] }}</h3>
                        <p class="mt-3 text-base font-medium leading-7 text-slate-600">{{ $item['desc'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="site-section-warm" data-scroll-section="Cam kết">
        <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8" data-reveal="fade-up">
            <h2 class="site-section-title mx-auto mt-3 max-w-3xl">
                Rõ ràng trong tư vấn, nhất quán trong hỗ trợ.
            </h2>
            <p class="mx-auto mt-5 max-w-3xl site-section-copy">
                Từ bước đầu liên hệ đến khi khách hàng sử dụng chữ ký số trong công việc hằng ngày, mọi thông tin đều được giải thích dễ hiểu và có người phụ trách.
            </p>
        </div>
    </section>
@endsection
