@extends('layouts.admin')

@section('title', 'Quản lý danh mục')

@section('content')
    <x-ui.page-header
        title="Danh sách danh mục"
        description="Quản lý các danh mục bài viết của website."
    >
        <x-slot name="actions">
            <x-ui.button :href="route('admin.categories.create')">
                + Thêm danh mục
            </x-ui.button>
        </x-slot>
    </x-ui.page-header>

    <x-ui.table>
        <x-slot name="head">
            <tr>
                <th>ID</th>
                <th>Tên danh mục</th>
                <th>Slug</th>
                <th>Mô tả</th>
                <th class="text-right">Thao tác</th>
            </tr>
        </x-slot>

        @forelse ($categories as $category)
            <tr>
                <td>{{ $category->id }}</td>
                <td class="font-semibold text-slate-950">{{ $category->name }}</td>
                <td>
                    <x-ui.badge>{{ $category->slug }}</x-ui.badge>
                </td>
                <td class="max-w-md">{{ $category->description ?: 'Chưa có mô tả' }}</td>
                <td>
                    <div class="ui-table-actions">
                        <x-ui.button
                            :href="route('admin.categories.edit', $category)"
                            variant="warning"
                            size="xs"
                            title="Sửa danh mục {{ $category->name }}"
                            aria-label="Sửa danh mục {{ $category->name }}"
                        >
                            Sửa
                        </x-ui.button>

                        <x-ui.confirm-delete
                            :action="route('admin.categories.destroy', $category)"
                            trigger="Xóa"
                            title="Xóa danh mục?"
                            description="Bạn chắc chắn muốn xóa danh mục này?"
                            confirm-text="Xóa danh mục"
                        />
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5">
                    <x-ui.empty-state description="Chưa có danh mục nào." />
                </td>
            </tr>
        @endforelse
    </x-ui.table>

    <x-ui.pagination :paginator="$categories" />
@endsection
