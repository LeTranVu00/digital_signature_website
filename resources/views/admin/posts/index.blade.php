@extends('layouts.admin')

@section('title', 'Quản lý bài viết')

@section('content')
    <x-ui.page-header
        title="Danh sách bài viết"
        description="Quản lý nội dung bài viết trên website."
    >
        <x-slot name="actions">
            <x-ui.button :href="route('admin.posts.trash')" variant="secondary">
                Thùng rác
            </x-ui.button>

            <x-ui.button :href="route('admin.posts.create')">
                + Thêm bài viết
            </x-ui.button>
        </x-slot>
    </x-ui.page-header>

    <form
        action="{{ route('admin.posts.index') }}"
        method="GET"
        class="mb-6 grid gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:grid-cols-5"
    >
        <div class="lg:col-span-2">
            <x-ui.input
                type="search"
                name="search"
                label="Tìm theo tiêu đề"
                :value="$filters['search'] ?? ''"
                placeholder="Nhập từ khóa..."
            />
        </div>

        <div>
            <x-ui.select
                name="category_id"
                label="Danh mục"
            >
                <option value="">Tất cả</option>

                @foreach ($categories as $category)
                    <option
                        value="{{ $category->id }}"
                        @selected(($filters['category_id'] ?? '') == $category->id)
                    >
                        {{ $category->name }}
                    </option>
                @endforeach
            </x-ui.select>
        </div>

        <div>
            <x-ui.select
                name="status"
                label="Trạng thái"
            >
                <option value="">Tất cả</option>
                <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>
                    Bản nháp
                </option>
                <option value="published" @selected(($filters['status'] ?? '') === 'published')>
                    Đã xuất bản
                </option>
            </x-ui.select>
        </div>

        <div>
            <x-ui.select
                name="sort"
                label="Sắp xếp"
            >
                <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>
                    Mới nhất
                </option>
                <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>
                    Cũ nhất
                </option>
            </x-ui.select>
        </div>

        <div class="flex items-end gap-3 lg:col-span-5">
            <x-ui.button type="submit">
                Lọc bài viết
            </x-ui.button>

            <x-ui.button :href="route('admin.posts.index')" variant="secondary">
                Xóa lọc
            </x-ui.button>
        </div>
    </form>

    <x-ui.table>
        <x-slot name="head">
            <tr>
                <th>Thumbnail</th>
                <th>Tiêu đề</th>
                <th>Danh mục</th>
                <th>Tác giả</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th>Ngày xuất bản</th>
                <th class="text-right">Thao tác</th>
            </tr>
        </x-slot>

        @forelse ($posts as $post)
            <tr>
                <td>
                    @if ($post->thumbnail)
                        <img
                            src="{{ asset('storage/' . $post->thumbnail) }}"
                            alt="{{ $post->title }}"
                            class="h-14 w-20 rounded-lg object-cover"
                            loading="lazy"
                            decoding="async"
                        >
                    @else
                        <div class="flex h-14 w-20 items-center justify-center rounded-lg bg-slate-100 text-xs text-slate-400">
                            No image
                        </div>
                    @endif
                </td>

                <td class="font-semibold text-slate-950">
                    <div>{{ $post->title }}</div>
                    <div class="mt-1 text-xs font-normal text-slate-400">{{ $post->slug }}</div>
                </td>

                <td>{{ $post->category?->name ?? 'Không còn danh mục' }}</td>
                <td>{{ $post->user?->name ?? 'Không còn tác giả' }}</td>

                <td>
                    <x-ui.badge :variant="$post->status === 'published' ? 'published' : 'draft'">
                        {{ $post->status === 'published' ? 'Đã xuất bản' : 'Bản nháp' }}
                    </x-ui.badge>
                </td>

                <td>{{ $post->created_at?->format('d/m/Y H:i') }}</td>
                <td>{{ $post->published_at?->format('d/m/Y H:i') ?? 'Chưa xuất bản' }}</td>

                <td>
                    <div class="ui-table-actions">
                        <x-ui.button
                            :href="route('admin.posts.preview', $post)"
                            target="_blank"
                            variant="dark"
                            size="xs"
                            title="Xem trước bài viết {{ $post->title }}"
                            aria-label="Xem trước bài viết {{ $post->title }}"
                        >
                            Xem
                        </x-ui.button>

                        <x-ui.button
                            :href="route('admin.posts.edit', $post)"
                            variant="warning"
                            size="xs"
                            title="Sửa bài viết {{ $post->title }}"
                            aria-label="Sửa bài viết {{ $post->title }}"
                        >
                            Sửa
                        </x-ui.button>

                        <x-ui.confirm-delete
                            :action="route('admin.posts.destroy', $post)"
                            trigger="Xóa"
                            title="Chuyển vào thùng rác?"
                            description="Bạn chắc chắn muốn chuyển bài viết này vào thùng rác?"
                            confirm-text="Chuyển vào thùng rác"
                        />
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8">
                    <x-ui.empty-state description="Chưa có bài viết nào." />
                </td>
            </tr>
        @endforelse
    </x-ui.table>

    <x-ui.pagination :paginator="$posts" />
@endsection
