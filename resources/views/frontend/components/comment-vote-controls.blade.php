@php
    $currentVote = (int) ($comment->votes->first()?->vote ?? 0);
@endphp

<div class="flex flex-wrap items-center gap-1.5"
     data-comment-vote-panel>
    <button type="button"
            data-vote-button
            data-vote-url="{{ route('comments.vote', $comment) }}"
            data-vote-value="1"
            data-login-url="{{ route('login') }}"
            aria-label="Thích thảo luận"
            class="ui-focus inline-flex items-center gap-1 rounded-full px-2 py-1 transition duration-200 ease-out {{ $currentVote === 1 ? 'bg-red-600 text-white' : 'text-slate-500 hover:bg-slate-100 hover:text-red-700' }}">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 11v9M7 11H4.5A1.5 1.5 0 0 0 3 12.5v6A1.5 1.5 0 0 0 4.5 20H7m0-9 4.6-6.2A2 2 0 0 1 15.2 6l-.7 4H19a2 2 0 0 1 2 2.3l-.9 6A2 2 0 0 1 18.1 20H7" />
        </svg>
        <span data-vote-count="likes">{{ $comment->likes_count ?? 0 }}</span>
    </button>

    <button type="button"
            data-vote-button
            data-vote-url="{{ route('comments.vote', $comment) }}"
            data-vote-value="-1"
            data-login-url="{{ route('login') }}"
            aria-label="Không thích thảo luận"
            class="ui-focus inline-flex items-center gap-1 rounded-full px-2 py-1 transition duration-200 ease-out {{ $currentVote === -1 ? 'bg-red-600 text-white' : 'text-slate-500 hover:bg-slate-100 hover:text-red-600' }}">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 13V4m0 9h2.5A1.5 1.5 0 0 0 21 11.5v-6A1.5 1.5 0 0 0 19.5 4H17m0 9-4.6 6.2A2 2 0 0 1 8.8 18l.7-4H5a2 2 0 0 1-2-2.3l.9-6A2 2 0 0 1 5.9 4H17" />
        </svg>
        <span data-vote-count="dislikes">{{ $comment->dislikes_count ?? 0 }}</span>
    </button>
</div>
