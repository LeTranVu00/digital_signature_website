@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <x-ui.page-header
        title="Dashboard quản trị"
        description="Xin chào {{ auth()->user()->name }}. Đây là tổng quan hoạt động hiện tại của website."
    />

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Tổng bài viết" :value="number_format($stats['total_posts'])" variant="primary">
            <x-slot name="icon">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 3h7l5 5v13H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 3v6h5M9 13h6M9 17h6" />
                </svg>
            </x-slot>
        </x-ui.stat-card>

        <x-ui.stat-card label="Đã xuất bản" :value="number_format($stats['published_posts'])" variant="success">
            <x-slot name="icon">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 6 9 17l-5-5" />
                </svg>
            </x-slot>
        </x-ui.stat-card>

        <x-ui.stat-card label="Bản nháp" :value="number_format($stats['draft_posts'])" variant="warning">
            <x-slot name="icon">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z" />
                </svg>
            </x-slot>
        </x-ui.stat-card>

        <x-ui.stat-card label="Trong thùng rác" :value="number_format($stats['trashed_posts'])" variant="danger">
            <x-slot name="icon">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" />
                </svg>
            </x-slot>
        </x-ui.stat-card>

        <x-ui.stat-card label="Tổng user" :value="number_format($stats['total_users'])" variant="neutral">
            <x-slot name="icon">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 21v-2a4 4 0 0 0-3-3.87" />
                </svg>
            </x-slot>
        </x-ui.stat-card>

        <x-ui.stat-card label="Tổng bình luận" :value="number_format($stats['total_comments'])" variant="info">
            <x-slot name="icon">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z" />
                </svg>
            </x-slot>
        </x-ui.stat-card>

        <div class="sm:col-span-2">
            <x-ui.stat-card label="Lượt xem bài viết" :value="number_format($stats['total_views'])" variant="primary">
                <x-slot name="icon">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                </x-slot>
            </x-ui.stat-card>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <x-ui.card title="Bài viết theo tháng" description="12 tháng gần nhất" class="xl:col-span-2">
            <div class="overflow-x-auto">
                <div class="flex h-72 min-w-[42rem] items-end gap-3 border-b border-l border-slate-200 px-3 pt-6">
                    @foreach ($monthlyPosts as $month)
                        <div class="flex h-full flex-1 flex-col justify-end gap-2">
                            <div
                                class="min-h-[4px] rounded-t-lg bg-blue-600 transition duration-300 hover:bg-blue-700"
                                style="height: {{ max(($month['count'] / max($maxMonthlyPosts, 1)) * 100, $month['count'] > 0 ? 8 : 0) }}%"
                                title="{{ $month['label'] }}: {{ $month['count'] }}"
                            ></div>
                            <div class="text-center text-xs font-semibold text-slate-500">
                                {{ $month['count'] }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 grid min-w-[42rem] grid-cols-12 gap-3 px-3 text-center text-[11px] font-medium text-slate-400">
                    @foreach ($monthlyPosts as $month)
                        <span>{{ $month['label'] }}</span>
                    @endforeach
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Top bài theo views">
            <div class="space-y-4">
                @forelse ($topPosts as $post)
                    <div class="border-b border-slate-100 pb-4 last:border-b-0 last:pb-0">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="line-clamp-2 font-semibold text-slate-950">
                                    {{ $post->title }}
                                </p>
                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $post->category?->name ?? 'Không còn danh mục' }}
                                </p>
                            </div>
                            <x-ui.badge>{{ number_format($post->views) }}</x-ui.badge>
                        </div>
                    </div>
                @empty
                    <x-ui.empty-state description="Chưa có bài viết nào." />
                @endforelse
            </div>
        </x-ui.card>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <x-ui.card title="Bài viết mới">
            <x-slot name="actions">
                <x-ui.button :href="route('admin.posts.index')" variant="ghost" size="sm">
                    Xem tất cả
                </x-ui.button>
            </x-slot>

            <div class="space-y-4">
                @forelse ($latestPosts as $post)
                    <div class="flex flex-col gap-3 border-b border-slate-100 pb-4 last:border-b-0 last:pb-0 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="line-clamp-2 font-semibold text-slate-950">
                                {{ $post->title }}
                            </p>
                            <p class="mt-1 text-xs text-slate-400">
                                {{ $post->created_at?->format('d/m/Y H:i') }}
                                &middot; {{ $post->comments_count }} bình luận
                            </p>
                        </div>

                        <x-ui.badge :variant="$post->status === 'published' ? 'published' : 'draft'">
                            {{ $post->status === 'published' ? 'Published' : 'Draft' }}
                        </x-ui.badge>
                    </div>
                @empty
                    <x-ui.empty-state description="Chưa có bài viết nào." />
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card title="Bình luận mới">
            <x-slot name="actions">
                <x-ui.button :href="route('admin.comments.index')" variant="ghost" size="sm">
                    Xem tất cả
                </x-ui.button>
            </x-slot>

            <div class="space-y-4">
                @forelse ($latestComments as $comment)
                    <div class="border-b border-slate-100 pb-4 last:border-b-0 last:pb-0">
                        <p class="line-clamp-3 whitespace-pre-line font-semibold text-slate-950">
                            {{ \Illuminate\Support\Str::limit($comment->content, 120) }}
                        </p>
                        <p class="mt-2 text-xs text-slate-400">
                            {{ $comment->user?->name ?? 'Không còn user' }}
                            &middot; {{ $comment->post?->title ?? 'Không còn bài viết' }}
                            &middot; {{ $comment->created_at?->format('d/m/Y H:i') }}
                        </p>
                    </div>
                @empty
                    <x-ui.empty-state description="Chưa có bình luận nào." />
                @endforelse
            </div>
        </x-ui.card>
    </div>
@endsection
