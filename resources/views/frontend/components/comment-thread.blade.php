@props([
    'comment',
    'post',
    'level' => 0,
    'depth' => 0,
    'appendTargetId' => null,
    'countTargetIds' => [],
    'openCommentIds' => [],
])

@php
    $maxThreadDepth = 2;
    $depth = min(max((int) $depth, 0), $maxThreadDepth);
    $level = min(max((int) $level, 0), $maxThreadDepth);
    $nextDepth = min($depth + 1, $maxThreadDepth);
    $nextLevel = min($level + 1, $maxThreadDepth);
    $appendTargetId = $appendTargetId ?? $comment->getKey();
    $replyAppendTargetId = $depth >= $maxThreadDepth ? $appendTargetId : $comment->getKey();
    $canOwnReplyList = $depth < $maxThreadDepth;
    $showBranchLine = $depth < $maxThreadDepth;

    $flattenReplies = function ($replies) use (&$flattenReplies) {
        return $replies->flatMap(function ($reply) use (&$flattenReplies) {
            return collect([$reply])->merge($flattenReplies($reply->replies ?? collect()));
        })->values();
    };

    $allReplies = $flattenReplies($comment->replies);
    $renderedReplies = $canOwnReplyList
        ? ($nextDepth >= $maxThreadDepth ? $allReplies : $comment->replies)
        : collect();

    $replyCount = $canOwnReplyList ? $allReplies->count() : 0;
    $replyFormOpen = (int) old('parent_id') === $comment->getKey();
    $editingFormOpen = (int) old('_editing_comment_id') === $comment->getKey();
    $repliesOpen = $replyFormOpen || in_array($comment->getKey(), $openCommentIds, true);
    $focused = (int) session('focus_comment_id') === $comment->getKey();
    $countTargetIds = collect($countTargetIds ?? [])
        ->push($comment->getKey())
        ->unique()
        ->values();
@endphp

<article
    id="comment-{{ $comment->getKey() }}"
    data-comment-node="{{ $comment->getKey() }}"
    x-data="{ editingOpen: @js($editingFormOpen), replyOpen: @js($replyFormOpen), repliesOpen: @js($repliesOpen), replyCount: @js($replyCount) }"
    x-on:comment:reply-added="replyCount += Number($event.detail.increment ?? 1); repliesOpen = true"
    x-on:comment:reply-removed="replyCount = Math.max(0, replyCount - Number($event.detail.decrement ?? 1))"
    x-on:comment:reply-submitted="replyOpen = false"
    x-on:comment:updated="editingOpen = false"
    class="scroll-mt-24"
>
    <div class="flex items-start gap-2.5">
        <x-ui.avatar :user="$comment->user" size="sm" class="mt-0.5 shrink-0" />

        <div class="min-w-0 flex-1">
            <div class="inline-block max-w-full rounded-2xl px-4 py-2.5 shadow-sm ring-1 transition duration-300 {{ $focused ? 'bg-red-50 ring-red-300' : 'bg-white ring-slate-200' }}">
                <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                    <p class="font-bold leading-5 text-slate-950">
                        {{ $comment->user?->name ?? 'Người dùng' }}
                    </p>

                    <p class="text-xs font-medium text-slate-500">
                        {{ $comment->created_at->diffForHumans() }}
                        @if ($comment->edited_at)
                            <span class="ml-1" data-comment-edited-label>Đã chỉnh sửa</span>
                        @else
                            <span class="ml-1 hidden" data-comment-edited-label>Đã chỉnh sửa</span>
                        @endif
                    </p>
                </div>

                <p class="mt-1 whitespace-pre-line break-words text-[15px] leading-6 text-slate-800" data-comment-content>{{ $comment->content }}</p>
            </div>

            <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 pl-2 text-xs font-bold text-slate-500">
                @include('frontend.components.comment-vote-controls', ['comment' => $comment])

                @auth
                    <button
                        type="button"
                        class="ui-focus rounded-full px-2 py-1 transition hover:bg-slate-100 hover:text-red-700"
                        x-on:click="replyOpen = ! replyOpen"
                        x-bind:aria-expanded="replyOpen.toString()"
                    >
                        Trả lời
                    </button>

                    @can('update', $comment)
                        <button
                            type="button"
                            class="ui-focus rounded-full px-2 py-1 transition hover:bg-slate-100 hover:text-red-700"
                            x-on:click="editingOpen = ! editingOpen"
                            x-bind:aria-expanded="editingOpen.toString()"
                        >
                            Sửa
                        </button>
                    @endcan

                    @can('delete', $comment)
                        <x-ui.confirm-delete
                            :action="route('comments.destroy', $comment)"
                            trigger="Xóa"
                            title="Xóa thảo luận?"
                            description="Nội dung này sẽ bị xóa khỏi chủ đề."
                            confirm-text="Xóa thảo luận"
                            button-variant="ghost"
                            button-size="xs"
                            trigger-class="!rounded-full !border-transparent !px-2 !py-1 !text-xs !shadow-none text-red-600 hover:bg-red-50 hover:text-red-800"
                        />
                    @endcan
                @endauth
            </div>

            @can('update', $comment)
                <form
                    x-show="editingOpen"
                    x-cloak
                    x-transition:enter="duration-200 ease-out"
                    x-transition:enter-start="-translate-y-1 opacity-0"
                    x-transition:enter-end="translate-y-0 opacity-100"
                    action="{{ route('comments.update', $comment) }}"
                    method="POST"
                    data-comment-update-form
                    data-preserve-scroll
                    class="mt-2 max-w-xl rounded-2xl border border-slate-200 bg-white p-3 shadow-sm"
                >
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="_editing_comment_id" value="{{ $comment->getKey() }}">

                    <x-ui.textarea
                        name="content"
                        :value="old('content', $comment->content)"
                        rows="3"
                        maxlength="2000"
                        required
                    />

                    <p class="mt-2 hidden text-sm font-medium text-red-600" data-comment-form-error></p>

                    <div class="mt-3 flex justify-end gap-2">
                        <x-ui.button
                            type="button"
                            variant="secondary"
                            size="sm"
                            x-on:click="editingOpen = false"
                        >
                            Hủy
                        </x-ui.button>

                        <x-ui.button type="submit" size="sm">
                            Lưu
                        </x-ui.button>
                    </div>
                </form>
            @endcan

            @auth
                <form
                    x-show="replyOpen"
                    x-cloak
                    x-transition:enter="duration-200 ease-out"
                    x-transition:enter-start="-translate-y-1 opacity-0"
                    x-transition:enter-end="translate-y-0 opacity-100"
                    action="{{ route('posts.comments.store', $post) }}"
                    method="POST"
                    data-comment-create-form
                    data-comment-parent-id="{{ $comment->getKey() }}"
                    data-comment-append-target="{{ $replyAppendTargetId }}"
                    data-comment-count-targets='@json($countTargetIds)'
                    data-preserve-scroll
                    class="mt-2 flex items-start gap-2"
                >
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                    <input type="hidden" name="_render_level" value="{{ $nextLevel }}">
                    <input type="hidden" name="_render_depth" value="{{ $nextDepth }}">
                    <input type="hidden" name="_append_target_id" value="{{ $replyAppendTargetId }}">
                    <input type="hidden" name="_count_target_ids" value="{{ $countTargetIds->toJson() }}">

                    <x-ui.avatar :user="auth()->user()" size="sm" class="shrink-0" />

                    <div class="min-w-0 flex-1">
                        <x-ui.textarea
                            name="content"
                            :value="old('parent_id') == $comment->id ? old('content') : ''"
                            rows="2"
                            maxlength="2000"
                            required
                            placeholder="Trả lời {{ $comment->user?->name ?? 'thảo luận' }}..."
                        />

                        @error('parent_id')
                            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror

                        <p class="mt-2 hidden text-sm font-medium text-red-600" data-comment-form-error></p>

                        <div class="mt-2 flex justify-end">
                            <x-ui.button type="submit" size="sm">
                                Gửi
                            </x-ui.button>
                        </div>
                    </div>
                </form>
            @endauth

            @if ($canOwnReplyList)
                <button
                    type="button"
                    x-show="replyCount > 0"
                    x-cloak
                    class="ui-focus mt-1 inline-flex items-center gap-2 rounded-full px-2 py-1 text-sm font-bold text-slate-600 transition hover:bg-slate-100 hover:text-red-700"
                    x-on:click="repliesOpen = ! repliesOpen"
                    x-bind:aria-expanded="repliesOpen.toString()"
                >
                    <svg class="h-4 w-4 transition duration-200" x-bind:class="repliesOpen ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                    </svg>
                    <span x-text="repliesOpen ? 'Ẩn phản hồi' : 'Xem ' + replyCount + ' phản hồi'"></span>
                </button>

                <div
                    x-show="repliesOpen"
                    x-cloak
                    x-transition:enter="duration-200 ease-out"
                    x-transition:enter-start="-translate-y-1 opacity-0"
                    x-transition:enter-end="translate-y-0 opacity-100"
                    data-comment-replies="{{ $comment->getKey() }}"
                    class="relative mt-1.5 {{ $showBranchLine ? 'comment-branch pl-3 sm:pl-4' : 'pl-0' }}"
                >
                    @foreach ($renderedReplies as $reply)
                        @include('frontend.components.comment-thread', [
                            'comment' => $reply,
                            'post' => $post,
                            'level' => $nextLevel,
                            'depth' => $nextDepth,
                            'appendTargetId' => $comment->getKey(),
                            'countTargetIds' => $countTargetIds,
                            'openCommentIds' => $openCommentIds,
                        ])
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</article>
