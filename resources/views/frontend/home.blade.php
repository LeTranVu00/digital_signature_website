@extends('frontend.layouts.app')

@section('title', 'Trang chủ - Digital Signature')

@section('content')
    <section class="relative overflow-hidden bg-gray-950 text-white">
        <img
            src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=1600&q=80"
            alt="Doanh nghiệp xử lý tài liệu điện tử"
            class="absolute inset-0 h-full w-full object-cover opacity-30"
        >
        <div class="relative mx-auto grid max-w-7xl gap-10 px-4 py-20 sm:px-6 lg:grid-cols-2 lg:px-8 lg:py-28">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-blue-200">
                    Giải pháp chuyển đổi số cho doanh nghiệp
                </p>
                <h1 class="mt-4 text-4xl font-bold leading-tight sm:text-5xl">
                    Chữ ký số an toàn, pháp lý rõ ràng, triển khai nhanh
                </h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-gray-200">
                    Hỗ trợ cá nhân và doanh nghiệp ký hồ sơ, khai thuế, hóa đơn điện tử và hợp đồng điện tử trên một quy trình gọn, dễ kiểm soát.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('contact') }}"
                       class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                        Nhận tư vấn
                    </a>
                    <a href="{{ route('services') }}"
                       class="rounded-lg border border-white/40 px-6 py-3 text-sm font-semibold text-white hover:bg-white/10">
                        Xem dịch vụ
                    </a>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-white/10 p-6 backdrop-blur">
                    <div class="text-3xl font-bold">15 phút</div>
                    <p class="mt-2 text-sm text-gray-200">Tiếp nhận và kiểm tra hồ sơ ban đầu.</p>
                </div>
                <div class="rounded-xl bg-white/10 p-6 backdrop-blur">
                    <div class="text-3xl font-bold">24/7</div>
                    <p class="mt-2 text-sm text-gray-200">Hỗ trợ kích hoạt và xử lý vướng mắc.</p>
                </div>
                <div class="rounded-xl bg-white/10 p-6 backdrop-blur sm:col-span-2">
                    <div class="text-3xl font-bold">Hồ sơ điện tử</div>
                    <p class="mt-2 text-sm text-gray-200">Giảm giấy tờ, giảm thời gian đi lại, tăng tính minh bạch.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white py-16">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
            <div>
                <p class="text-sm font-semibold uppercase text-blue-700">Giới thiệu doanh nghiệp</p>
                <h2 class="mt-3 text-3xl font-bold text-gray-950">
                    Đồng hành cùng doanh nghiệp trong mọi giao dịch số
                </h2>
            </div>
            <p class="text-base leading-8 text-gray-600">
                Chúng tôi cung cấp giải pháp chữ ký số và tài liệu điện tử theo hướng dễ dùng, dễ triển khai và phù hợp với quy trình vận hành thực tế của từng doanh nghiệp.
            </p>
        </div>
    </section>

    <section class="bg-gray-50 py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold uppercase text-blue-700">Dịch vụ chữ ký số</p>
                <h2 class="mt-3 text-3xl font-bold text-gray-950">Giải pháp cho từng nhu cầu sử dụng</h2>
            </div>

            <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @include('frontend.components.service-card', [
                    'number' => '01',
                    'title' => 'Chữ ký số cá nhân',
                    'description' => 'Ký hồ sơ cá nhân, giao dịch điện tử và thủ tục hành chính trực tuyến.',
                ])
                @include('frontend.components.service-card', [
                    'number' => '02',
                    'title' => 'Chữ ký số doanh nghiệp',
                    'description' => 'Khai thuế, bảo hiểm, hải quan, ngân hàng và các giao dịch pháp lý.',
                ])
                @include('frontend.components.service-card', [
                    'number' => '03',
                    'title' => 'Chữ ký số từ xa',
                    'description' => 'Ký trên nhiều thiết bị, không phụ thuộc USB token truyền thống.',
                ])
            </div>
        </div>
    </section>

    <section class="bg-white py-16">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            @foreach (['Tiết kiệm thời gian xử lý hồ sơ', 'Đảm bảo giá trị pháp lý', 'Quản trị giao dịch minh bạch'] as $benefit)
                <div class="rounded-xl border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-950">{{ $benefit }}</h3>
                    <p class="mt-3 text-sm leading-6 text-gray-600">
                        Quy trình số hóa giúp đội ngũ vận hành nhanh hơn, giảm lỗi thủ công và dễ theo dõi trạng thái xử lý.
                    </p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="bg-gray-950 py-16 text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold">Quy trình đăng ký</h2>
            <div class="mt-10 grid gap-6 md:grid-cols-4">
                @foreach (['Tư vấn nhu cầu', 'Chuẩn bị hồ sơ', 'Xác thực thông tin', 'Kích hoạt sử dụng'] as $step)
                    <div class="rounded-xl bg-white/10 p-5">
                        <div class="text-sm font-semibold text-blue-200">Bước {{ $loop->iteration }}</div>
                        <h3 class="mt-3 font-bold">{{ $step }}</h3>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-6">
                <div>
                    <p class="text-sm font-semibold uppercase text-blue-700">Tin tức mới nhất</p>
                    <h2 class="mt-3 text-3xl font-bold text-gray-950">Cập nhật kiến thức chữ ký số</h2>
                </div>
                <a href="{{ route('blog.index') }}"
                   class="hidden text-sm font-semibold text-blue-700 hover:text-blue-800 sm:inline-flex">
                    Xem tất cả
                </a>
            </div>

            <div class="mt-10 grid gap-6 md:grid-cols-3">
                @forelse ($latestPosts as $post)
                    @include('frontend.components.post-card', ['post' => $post])
                @empty
                    <div class="rounded-xl border border-dashed border-gray-300 p-8 text-gray-500 md:col-span-3">
                        Chưa có bài viết công khai.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="bg-gray-50 py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-center text-2xl font-bold text-gray-950">Đối tác và nền tảng hỗ trợ</h2>
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (['Thuế điện tử', 'Hóa đơn điện tử', 'Bảo hiểm xã hội', 'Hợp đồng số'] as $partner)
                    <div class="rounded-xl bg-white p-6 text-center text-sm font-semibold text-gray-700 shadow-sm">
                        {{ $partner }}
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-blue-700 py-16 text-white">
        <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
            <div>
                <h2 class="text-3xl font-bold">Sẵn sàng triển khai chữ ký số?</h2>
                <p class="mt-3 text-blue-100">Đội ngũ tư vấn sẽ giúp bạn chọn gói phù hợp và kích hoạt nhanh.</p>
            </div>
            <a href="{{ route('contact') }}"
               class="inline-flex rounded-lg bg-white px-6 py-3 text-sm font-semibold text-blue-700 hover:bg-blue-50">
                Liên hệ tư vấn
            </a>
        </div>
    </section>
@endsection
