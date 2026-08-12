@extends('layouts.admin')

@section('title', 'Quản lý bình luận')

@section('content')
    <x-ui.page-header
        title="Quản lý bình luận"
        description="Tìm kiếm, lọc và xử lý bình luận trên các bài viết."
    />

    <form
        action="{{ route('admin.comments.index') }}"
        method="GET"
        class="mb-6 grid gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:grid-cols-5"
    >
        <div class="lg:col-span-2">
            <x-ui.input
                type="search"
                name="search"
                label="Tìm nội dung"
                :value="$filters['search'] ?? ''"
                placeholder="Nhập từ khóa..."
            />
        </div>

        <div class="lg:col-span-2">
            <x-ui.select name="post_id" label="Bài viết">
                <option value="">Tất cả bài viết</option>

                @foreach ($posts as $post)
                    <option
                        value="{{ $post->id }}"
                        @selected(($filters['post_id'] ?? '') == $post->id)
                    >
                        {{ $post->title }}
                    </option>
                @endforeach
            </x-ui.select>
        </div>

        <x-ui.select name="deleted" label="Trạng thái xóa">
            <option value="all" @selected(($filters['deleted'] ?? 'all') === 'all')>
                Tất cả
            </option>
            <option value="active" @selected(($filters['deleted'] ?? '') === 'active')>
                Đang hiển thị
            </option>
            <option value="trashed" @selected(($filters['deleted'] ?? '') === 'trashed')>
                Đã xóa
            </option>
        </x-ui.select>

        <div class="flex items-end gap-3 lg:col-span-5">
            <x-ui.button type="submit">
                Lọc bình luận
            </x-ui.button>

            <x-ui.button :href="route('admin.comments.index')" variant="secondary">
                Xóa lọc
            </x-ui.button>
        </div>
    </form>

    <x-ui.table>
        <x-slot name="head">
            <tr>
                <th>Nội dung</th>
                <th>Người bình luận</th>
                <th>Bài viết</th>
                <th>Cha / con</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th class="text-right">Thao tác</th>
            </tr>
        </x-slot>

        @forelse ($comments as $comment)
            <tr>
                <td class="max-w-md">
                    <p class="whitespace-pre-line font-semibold text-slate-950">
                        {{ \Illuminate\Support\Str::limit($comment->content, 180) }}
                    </p>

                    @if ($comment->edited_at)
                        <p class="mt-2 text-xs text-slate-400">
                            Đã chỉnh sửa: {{ $comment->edited_at->format('d/m/Y H:i') }}
                        </p>
                    @endif
                </td>

                <td>
                    <div class="font-semibold text-slate-950">
                        {{ $comment->user?->name ?? 'Không còn user' }}
                    </div>
                    <div class="mt-1 text-xs text-slate-400">
                        {{ $comment->user?->email }}
                    </div>
                </td>

                <td class="min-w-64">
                    @if ($comment->post)
                        <div class="font-semibold text-slate-950">
                            {{ $comment->post->title }}
                        </div>

                        <div class="mt-2 flex flex-wrap gap-2">
                            @unless ($comment->post->trashed())
                                <x-ui.button
                                    :href="route('admin.posts.preview', $comment->post)"
                                    target="_blank"
                                    variant="dark"
                                    size="xs"
                                    title="Xem bài viết trong admin"
                                    aria-label="Xem bài viết trong admin"
                                >
                                    Xem admin
                                </x-ui.button>

                                @if ($comment->post->status === 'published')
                                    <x-ui.button
                                        :href="route('blog.show', $comment->post->slug)"
                                        target="_blank"
                                        variant="secondary"
                                        size="xs"
                                        title="Xem bài viết trên website"
                                        aria-label="Xem bài viết trên website"
                                    >
                                        Xem web
                                    </x-ui.button>
                                @endif
                            @else
                                <span class="text-xs text-slate-400">Bài viết đã bị xóa</span>
                            @endunless
                        </div>
                    @else
                        <span class="text-slate-400">Không còn bài viết</span>
                    @endif
                </td>

                <td class="min-w-56">
                    @if ($comment->parent)
                        <x-ui.badge variant="primary">Reply</x-ui.badge>
                        <p class="mt-2 text-xs text-slate-500">
                            Cha: {{ \Illuminate\Support\Str::limit($comment->parent->content, 80) }}
                        </p>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ $comment->parent->user?->name ?? 'Không còn user' }}
                        </p>
                    @else
                        <x-ui.badge>Comment gốc</x-ui.badge>
                        <p class="mt-2 text-xs text-slate-500">
                            {{ $comment->replies_count }} reply
                        </p>
                    @endif
                </td>

                <td>
                    @if ($comment->trashed())
                        <x-ui.badge variant="danger">Đã xóa</x-ui.badge>
                        <p class="mt-2 text-xs text-slate-400">
                            {{ $comment->deleted_at?->format('d/m/Y H:i') }}
                        </p>
                    @else
                        <x-ui.badge variant="success">Đang hiển thị</x-ui.badge>
                    @endif
                </td>

                <td>{{ $comment->created_at?->format('d/m/Y H:i') }}</td>

                <td>
                    <div class="ui-table-actions">
                        @if ($comment->trashed())
                            <form
                                action="{{ route('admin.comments.restore', ['trashedComment' => $comment->id]) }}"
                                method="POST"
                            >
                                @csrf
                                @method('PATCH')

                                <x-ui.button type="submit" variant="success" size="xs">
                                    Khôi phục
                                </x-ui.button>
                            </form>

                            <x-ui.confirm-delete
                                :action="route('admin.comments.force-delete', ['trashedComment' => $comment->id])"
                                trigger="Xóa vĩnh viễn"
                                title="Xóa vĩnh viễn bình luận?"
                                description="Thao tác này không thể hoàn tác. Bạn chắc chắn muốn xóa vĩnh viễn?"
                                confirm-text="Xóa vĩnh viễn"
                            />
                        @else
                            <x-ui.confirm-delete
                                :action="route('admin.comments.destroy', $comment)"
                                trigger="Xóa"
                                title="Chuyển bình luận vào thùng rác?"
                                description="Bạn chắc chắn muốn chuyển bình luận này vào thùng rác?"
                                confirm-text="Chuyển vào thùng rác"
                            />
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7">
                    <x-ui.empty-state description="Không có bình luận nào phù hợp." />
                </td>
            </tr>
        @endforelse
    </x-ui.table>

    <x-ui.pagination :paginator="$comments" />
@endsection
