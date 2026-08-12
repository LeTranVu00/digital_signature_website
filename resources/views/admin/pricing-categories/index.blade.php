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

    <x-ui.table>
        <x-slot name="head">
            <tr>
                <th>Ảnh bảng giá</th>
                <th>Tên danh mục</th>
                <th>Thứ tự</th>
                <th>Trạng thái</th>
                <th class="text-right">Thao tác</th>
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
                <td>
                    <p class="font-semibold text-slate-950">{{ $category->name }}</p>
                    <p class="mt-1 max-w-xl text-sm text-slate-500">{{ $category->description ?: 'Chưa có mô tả' }}</p>
                    <x-ui.badge class="mt-2">{{ $category->slug }}</x-ui.badge>
                </td>
                <td>{{ $category->sort_order }}</td>
                <td>
                    <x-ui.badge :variant="$category->is_active ? 'published' : 'draft'">
                        {{ $category->is_active ? 'Đang hiển thị' : 'Đang ẩn' }}
                    </x-ui.badge>
                </td>
                <td>
                    <div class="ui-table-actions">
                        <x-ui.button
                            :href="route('admin.pricing-categories.edit', $category)"
                            variant="warning"
                            size="xs"
                        >
                            Sửa
                        </x-ui.button>

                        <x-ui.confirm-delete
                            :action="route('admin.pricing-categories.destroy', $category)"
                            trigger="Xóa"
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
