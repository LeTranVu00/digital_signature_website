@extends('layouts.admin')

@section('title', 'Thùng rác bài viết')

@section('content')
    <x-ui.page-header
        title="Thùng rác bài viết"
        description="Khôi phục hoặc xóa vĩnh viễn các bài viết đã xóa."
    >
        <x-slot name="actions">
            <x-ui.button :href="route('admin.posts.index')" variant="secondary">
                ← Quay lại danh sách
            </x-ui.button>
        </x-slot>
    </x-ui.page-header>

    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm leading-6 text-red-800">
        <strong>Khu vực nguy hiểm:</strong>
        xóa vĩnh viễn sẽ loại bỏ bài viết khỏi hệ thống và không thể hoàn tác.
    </div>

    <x-ui.table>
        <x-slot name="head">
            <tr>
                <th>ID</th>
                <th>Tiêu đề</th>
                <th>Danh mục</th>
                <th>Tác giả</th>
                <th>Ngày xóa</th>
                <th class="text-right">Thao tác</th>
            </tr>
        </x-slot>

        @forelse ($posts as $post)
            <tr>
                <td>{{ $post->id }}</td>
                <td class="font-semibold text-slate-950">{{ $post->title }}</td>
                <td>{{ $post->category?->name ?? 'Không còn danh mục' }}</td>
                <td>{{ $post->user?->name ?? 'Không còn tác giả' }}</td>
                <td>{{ $post->deleted_at?->format('d/m/Y H:i') }}</td>
                <td>
                    <div class="ui-table-actions">
                        <form
                            action="{{ route('admin.posts.restore', ['trashedPost' => $post->id]) }}"
                            method="POST"
                        >
                            @csrf
                            @method('PATCH')

                            <x-ui.button type="submit" variant="success" size="xs">
                                Khôi phục
                            </x-ui.button>
                        </form>

                        <x-ui.confirm-delete
                            :action="route('admin.posts.force-delete', ['trashedPost' => $post->id])"
                            trigger="Xóa vĩnh viễn"
                            title="Xóa vĩnh viễn bài viết?"
                            description="Thao tác này không thể hoàn tác. Bạn chắc chắn muốn xóa vĩnh viễn?"
                            confirm-text="Xóa vĩnh viễn"
                        />
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">
                    <x-ui.empty-state description="Thùng rác hiện đang trống." />
                </td>
            </tr>
        @endforelse
    </x-ui.table>

    <x-ui.pagination :paginator="$posts" />
@endsection
