@extends('frontend.layouts.app')

@section('title', $post->title . ' - Digital Signature')

@section('content')
    <article class="bg-white">
        <section class="relative overflow-hidden bg-zinc-950 py-16 text-white sm:py-20">
            <div class="absolute inset-0 ui-mesh-bg opacity-70"></div>
            <div class="relative mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <a href="{{ route('blog.category', $post->category->slug) }}"
                   class="text-sm font-semibold uppercase text-amber-200 hover:text-white">
                    {{ $post->category->name }}
                </a>

                <h1 class="site-page-title mt-5 text-left">
                    {{ $post->title }}
                </h1>

                <div class="mt-6 flex flex-wrap gap-3 text-sm text-zinc-200">
                    <span>{{ $post->user?->name ?? 'Admin' }}</span>
                    <span>•</span>
                    <span>{{ $post->published_at?->format('d/m/Y H:i') }}</span>
                    <span>•</span>
                    <span>{{ number_format($post->views) }} lượt xem</span>
                </div>
            </div>
        </section>

        <section class="site-section-cool">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                @if ($post->thumbnail)
                    <img
                        src="{{ asset('storage/' . $post->thumbnail) }}"
                        alt="{{ $post->title }}"
                        class="mb-8 max-h-[320px] w-full rounded-lg object-cover sm:mb-10 sm:max-h-[460px]"
                        loading="lazy"
                        decoding="async"
                    >
                @endif

                @if ($post->summary)
                    <p class="site-highlight-card mb-8">
                        {{ $post->summary }}
                    </p>
                @endif

                <div class="post-content rounded-lg border border-slate-200/80 bg-white/95 p-6 shadow-[0_24px_70px_-48px_rgb(15_23_42/0.5)] sm:p-8">
                    {!! $post->content !!}
                </div>
            </div>
        </section>

        <section id="comments" class="site-section-warm scroll-mt-24 border-t border-amber-200/70">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-3xl font-extrabold text-slate-950 sm:text-4xl">
                        Thảo luận (<span data-comments-count>{{ $commentsCount ?? $post->comments->count() }}</span>)
                    </h2>
                </div>

                @auth
                    <form action="{{ route('posts.comments.store', $post) }}"
                          method="POST"
                          data-comment-create-form
                          data-preserve-scroll
                          class="site-feature-card mt-6">
                        @csrf

                        <x-ui.textarea
                            id="comment-content"
                            name="content"
                            label="Nội dung thảo luận"
                            :value="old('parent_id') ? '' : old('content')"
                            rows="4"
                            maxlength="2000"
                            required
                            placeholder="Nhập ý kiến của bạn..."
                        />

                        <p class="mt-2 hidden text-sm font-medium text-red-600" data-comment-form-error></p>

                        <div class="mt-4 flex justify-end">
                            <x-ui.button type="submit">
                                Gửi thảo luận
                            </x-ui.button>
                        </div>
                    </form>
                @else
                    <div class="mt-6 rounded-lg border border-amber-100 bg-amber-50 p-5 text-sm text-amber-950">
                        <a href="{{ route('login') }}"
                           class="font-semibold text-amber-700 hover:text-amber-900">
                            Đăng nhập
                        </a>
                        để tham gia thảo luận chủ đề này.
                    </div>
                @endauth

                <div class="mt-8 space-y-5" data-comments-list>
                    @forelse ($post->comments as $comment)
                        @include('frontend.components.comment-thread', [
                            'comment' => $comment,
                            'post' => $post,
                            'level' => 0,
                            'openCommentIds' => $openCommentIds ?? [],
                        ])
                        @continue

                        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="flex min-w-0 items-start gap-3">
                                    <x-ui.avatar :user="$comment->user" size="sm" />

                                    <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-950">
                                        {{ $comment->user?->name ?? 'Người dùng' }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $comment->created_at->format('d/m/Y H:i') }}
                                        @if ($comment->edited_at)
                                            <span class="ml-2">Đã chỉnh sửa</span>
                                        @endif
                                    </p>
                                    </div>
                                </div>

                                @auth
                                    <div class="flex items-center gap-2 text-sm font-semibold">
                                        @can('update', $comment)
                                            <details class="relative">
                                                <summary class="ui-focus list-none cursor-pointer rounded-full px-3 py-1.5 text-amber-700 transition hover:bg-amber-50 hover:text-amber-900 [&::-webkit-details-marker]:hidden">
                                                    Sửa
                                                </summary>
                                                <form action="{{ route('comments.update', $comment) }}"
                                                      method="POST"
                                                      class="absolute right-0 z-10 mt-3 w-[calc(100vw-2rem)] max-w-sm rounded-lg border border-slate-200 bg-white p-4 shadow-xl sm:w-96">
                                                    @csrf
                                                    @method('PATCH')

                                                    <x-ui.textarea
                                                        name="content"
                                                        :value="old('content', $comment->content)"
                                                        rows="4"
                                                        maxlength="2000"
                                                        required
                                                    />
                                                    <x-ui.button type="submit" class="mt-3" size="sm">
                                                        Lưu
                                                    </x-ui.button>
                                                </form>
                                            </details>
                                        @endcan

                                        @can('delete', $comment)
                                            <x-ui.confirm-delete
                                                :action="route('comments.destroy', $comment)"
                                                trigger="Xóa"
                                                title="Xóa bình luận?"
                                                description="Bình luận này sẽ bị xóa khỏi bài viết."
                                                confirm-text="Xóa bình luận"
                                                button-variant="ghost"
                                                trigger-class="rounded-full text-red-600 hover:bg-red-50 hover:text-red-800"
                                            />
                                        @endcan
                                    </div>
                                @endauth
                            </div>

                            <p class="mt-4 whitespace-pre-line text-slate-700">{{ $comment->content }}</p>

                            @include('frontend.components.comment-vote-controls', ['comment' => $comment])

                            @auth
                                <details class="mt-4">
                                    <summary class="ui-focus inline-flex cursor-pointer list-none rounded-full px-3 py-1.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-50 hover:text-amber-900 [&::-webkit-details-marker]:hidden">
                                        Trả lời
                                    </summary>

                                    <form action="{{ route('posts.comments.store', $post) }}"
                                          method="POST"
                                          class="mt-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
                                        @csrf
                                        <input type="hidden"
                                               name="parent_id"
                                               value="{{ $comment->id }}">
                                        <x-ui.textarea
                                            name="content"
                                            :value="old('parent_id') == $comment->id ? old('content') : ''"
                                            rows="3"
                                            maxlength="2000"
                                            required
                                            placeholder="Nhập trả lời của bạn..."
                                        />
                                        @error('parent_id')
                                            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                                        @enderror
                                        <x-ui.button type="submit" variant="dark" class="mt-3" size="sm">
                                            Gửi trả lời
                                        </x-ui.button>
                                    </form>
                                </details>
                            @endauth

                            @if ($comment->replies->isNotEmpty())
                                <div class="mt-5 space-y-4 border-l-2 border-amber-200 pl-4 sm:pl-5">
                                    @foreach ($comment->replies as $reply)
                                        <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                            <div class="flex flex-wrap items-start justify-between gap-3">
                                                <div class="flex min-w-0 items-start gap-3">
                                                    <x-ui.avatar :user="$reply->user" size="sm" />

                                                    <div class="min-w-0">
                                                    <p class="truncate font-semibold text-slate-950">
                                                        {{ $reply->user?->name ?? 'Người dùng' }}
                                                    </p>
                                                    <p class="mt-1 text-xs text-slate-500">
                                                        {{ $reply->created_at->format('d/m/Y H:i') }}
                                                        @if ($reply->edited_at)
                                                            <span class="ml-2">Đã chỉnh sửa</span>
                                                        @endif
                                                    </p>
                                                    </div>
                                                </div>

                                                @auth
                                                    <div class="flex items-center gap-2 text-sm font-semibold">
                                                        @can('update', $reply)
                                                            <details class="relative">
                                                                <summary class="ui-focus list-none cursor-pointer rounded-full px-3 py-1.5 text-amber-700 transition hover:bg-amber-50 hover:text-amber-900 [&::-webkit-details-marker]:hidden">
                                                                    Sửa
                                                                </summary>
                                                                <form action="{{ route('comments.update', $reply) }}"
                                                                      method="POST"
                                                                      class="absolute right-0 z-10 mt-3 w-[calc(100vw-2rem)] max-w-sm rounded-lg border border-slate-200 bg-white p-4 shadow-xl sm:w-96">
                                                                    @csrf
                                                                    @method('PATCH')

                                                                    <x-ui.textarea
                                                                        name="content"
                                                                        :value="old('content', $reply->content)"
                                                                        rows="4"
                                                                        maxlength="2000"
                                                                        required
                                                                    />
                                                                    <x-ui.button type="submit" class="mt-3" size="sm">
                                                                        Lưu
                                                                    </x-ui.button>
                                                                </form>
                                                            </details>
                                                        @endcan

                                                        @can('delete', $reply)
                                                            <x-ui.confirm-delete
                                                                :action="route('comments.destroy', $reply)"
                                                                trigger="Xóa"
                                                                title="Xóa phản hồi?"
                                                                description="Phản hồi này sẽ bị xóa khỏi bài viết."
                                                                confirm-text="Xóa phản hồi"
                                                                button-variant="ghost"
                                                                trigger-class="rounded-full text-red-600 hover:bg-red-50 hover:text-red-800"
                                                            />
                                                        @endcan
                                                    </div>
                                                @endauth
                                            </div>

                                            <p class="mt-4 whitespace-pre-line text-slate-700">{{ $reply->content }}</p>

                                            @include('frontend.components.comment-vote-controls', ['comment' => $reply])
                                        </article>
                                    @endforeach
                                </div>
                            @endif
                        </article>
                    @empty
                        <x-ui.empty-state data-comments-empty description="Chưa có thảo luận nào." />
                    @endforelse
                </div>
            </div>
        </section>
    </article>

    <section class="bg-gray-50 py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-950 sm:text-3xl">Chủ đề liên quan</h2>

            <div class="mt-8 grid gap-6 md:grid-cols-3">
                @forelse ($relatedPosts as $relatedPost)
                    @include('frontend.components.post-card', ['post' => $relatedPost])
                @empty
                    <x-ui.empty-state
                        class="md:col-span-3"
                        description="Chưa có chủ đề liên quan."
                    />
                @endforelse
            </div>
        </div>
    </section>
@endsection
