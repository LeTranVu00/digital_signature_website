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

    <x-ui.table table-class="w-full table-fixed">
        <x-slot name="head">
            <tr>
                <th class="w-16">ID</th>
                <th class="w-[22%]">Tên danh mục</th>
                <th class="w-[22%]">Slug</th>
                <th>Mô tả</th>
                <th class="w-44 text-right">Thao tác</th>
            </tr>
        </x-slot>

        @forelse ($categories as $category)
            <tr>
                <td>{{ $category->id }}</td>
                <td class="break-words font-semibold text-slate-950">{{ $category->name }}</td>
                <td class="break-words text-sm text-slate-600">{{ $category->slug }}</td>
                <td class="break-words">{{ $category->description ?: 'Chưa có mô tả' }}</td>
                <td class="whitespace-nowrap">
                    <div class="ui-table-actions">
                        <x-ui.button
                            :href="route('admin.categories.edit', $category)"
                            variant="secondary"
                            size="xs"
                        >
                            Sửa
                        </x-ui.button>

                        <x-ui.confirm-delete
                            :action="route('admin.categories.destroy', $category)"
                            trigger="Xóa"
                            button-variant="ghost"
                            trigger-class="text-red-600 hover:bg-red-50 hover:text-red-700"
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
