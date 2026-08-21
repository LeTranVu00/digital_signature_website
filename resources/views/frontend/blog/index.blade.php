@extends('frontend.layouts.app')

@section('title', 'Diễn đàn - CHỮ KÝ SỐ VIP')

@section('content')
    <section class="relative overflow-hidden bg-zinc-950 py-20 text-white sm:py-24" data-scroll-section="Diễn đàn">
        <div class="absolute inset-0 ui-mesh-bg opacity-70"></div>
        <div class="relative mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8" data-reveal="fade-up">
            <h1 class="site-page-title">
                Nơi thảo luận về kế toán, chữ ký số, hóa đơn điện tử và vận hành doanh nghiệp.
            </h1>
            <p class="site-page-copy">
                Tìm kiếm chủ đề, xem hướng dẫn và tham gia bình luận để cùng trao đổi các tình huống thực tế.
            </p>

            <form action="{{ route('blog.index') }}" method="GET" class="mx-auto mt-8 max-w-3xl">
                <label for="forum-search" class="sr-only">Tìm kiếm chủ đề diễn đàn</label>
                <div class="flex flex-col gap-3 rounded-lg border border-white/20 bg-white p-2 shadow-2xl shadow-slate-950/30 sm:flex-row sm:items-center">
                    <input
                        id="forum-search"
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        class="min-h-12 flex-1 rounded-lg border-0 px-4 text-base font-semibold text-slate-900 placeholder:text-slate-400 focus:ring-0"
                        placeholder="Nhập từ khóa cần tìm..."
                    >
                    <button
                        type="submit"
                        class="inline-flex min-h-12 items-center justify-center rounded-lg bg-red-600 px-6 text-sm font-bold text-white transition hover:bg-red-700"
                    >
                        Tìm kiếm
                    </button>
                </div>
            </form>
        </div>
    </section>

    <section class="site-section-cool" data-scroll-section="Danh sách chủ đề">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 flex flex-wrap justify-center gap-3" data-reveal="fade-up">
                <a href="{{ route('blog.index') }}"
                   class="rounded-full bg-zinc-950 px-4 py-2 text-sm font-semibold text-white shadow-sm">
                    Tất cả chủ đề
                </a>

                @foreach ($categories as $category)
                    <a href="{{ route('blog.category', $category->slug) }}"
                       class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-amber-700 hover:bg-amber-50 hover:text-amber-700">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($posts as $post)
                    <div data-reveal="fade-up" data-reveal-delay="{{ $loop->index * 70 }}">
                        @include('frontend.components.post-card', ['post' => $post])
                    </div>
                @empty
                    <x-ui.empty-state
                        class="md:col-span-2 lg:col-span-3"
                        title="Chưa có chủ đề công khai"
                        description="Các chủ đề thảo luận mới sẽ được cập nhật tại đây."
                    />
                @endforelse
            </div>

            <x-ui.pagination :paginator="$posts" class="mt-10" />
        </div>
    </section>
@endsection
