@extends('layouts.admin')

@section('title', 'Danh mục báo giá')

@section('content')
    <x-ui.page-header
        title="Danh mục báo giá"
        description="Quản lý các danh mục và ảnh bảng giá hiển thị ở trang báo giá."
    >
        <x-slot name="actions">
            <x-ui.button :href="route('admin.pricing-categories.create')">
                + Thêm danh mục
            </x-ui.button>
        </x-slot>
    </x-ui.page-header>

    @if (session('success'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <x-ui.table table-class="w-full table-fixed">
        <x-slot name="head">
            <tr>
                <th class="w-40">Ảnh bảng giá</th>
                <th>Tên danh mục</th>
                <th class="w-20">Thứ tự</th>
                <th class="w-32">Trạng thái</th>
                <th class="w-44 text-right">Thao tác</th>
            </tr>
        </x-slot>

        @forelse ($pricingCategories as $category)
            <tr>
                <td class="w-40">
                    <img
                        src="{{ $category->imageUrl() }}"
                        alt="{{ $category->name }}"
                        class="h-20 w-32 rounded-lg border border-slate-200 object-cover"
                        loading="lazy"
                    >
                </td>
                <td class="break-words">
                    <p class="font-semibold text-slate-950">{{ $category->name }}</p>
                    <p class="mt-1 break-words text-sm text-slate-500">{{ $category->description ?: 'Chưa có mô tả' }}</p>
                    <p class="mt-2 break-all text-xs font-medium text-slate-400">{{ $category->slug }}</p>
                </td>
                <td>{{ $category->sort_order }}</td>
                <td>
                    <x-ui.badge :variant="$category->is_active ? 'published' : 'draft'">
                        {{ $category->is_active ? 'Đang hiển thị' : 'Đang ẩn' }}
                    </x-ui.badge>
                </td>
                <td class="whitespace-nowrap">
                    <div class="ui-table-actions">
                        <x-ui.button
                            :href="route('admin.pricing-categories.edit', $category)"
                            variant="secondary"
                            size="xs"
                        >
                            Sửa
                        </x-ui.button>

                        <x-ui.confirm-delete
                            :action="route('admin.pricing-categories.destroy', $category)"
                            trigger="Xóa"
                            button-variant="ghost"
                            trigger-class="text-red-600 hover:bg-red-50 hover:text-red-700"
                            title="Xóa danh mục báo giá?"
                            description="Danh mục và ảnh bảng giá đã upload sẽ bị xóa khỏi website."
                            confirm-text="Xóa danh mục"
                        />
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5">
                    <x-ui.empty-state description="Chưa có danh mục báo giá nào." />
                </td>
            </tr>
        @endforelse
    </x-ui.table>

    <x-ui.pagination :paginator="$pricingCategories" />
@endsection
