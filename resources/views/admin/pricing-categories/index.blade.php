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

    <x-ui.table table-class="w-full">
        <x-slot name="head">
            <tr>
                <th class="w-32">Ảnh bảng giá</th>
                <th>Tên danh mục</th>
                <th class="w-36 text-center">Trạng thái</th>
                <th class="w-36 text-center">Thao tác</th>
            </tr>
        </x-slot>

        @forelse ($pricingCategories as $category)
            <tr>
                <td class="w-32 py-4">
                    @php
                        $imageUrl = $category->imageUrl();
                    @endphp
                    @if ($imageUrl)
                        <img
                            src="{{ $imageUrl }}"
                            alt="{{ $category->name }}"
                            class="h-20 w-28 rounded-lg border border-slate-200 object-cover"
                            loading="lazy"
                            decoding="async"
                        >
                    @else
                        <div class="flex h-20 w-28 items-center justify-center rounded-lg border-2 border-dashed border-slate-300 bg-slate-50">
                            <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @endif
                </td>
                <td class="py-4">
                    <p class="font-semibold text-slate-950">{{ $category->name }}</p>
                    <p class="mt-1 break-words whitespace-pre-line text-sm text-slate-500">{{ $category->description ?: 'Chưa có mô tả' }}</p>
                    <p class="mt-1 text-xs font-medium text-slate-400">{{ $category->slug }}</p>
                </td>
                <td class="w-36 py-4 text-center">
                    <x-ui.badge :variant="$category->is_active ? 'published' : 'draft'">
                        {{ $category->is_active ? 'Đang hiển thị' : 'Đang ẩn' }}
                    </x-ui.badge>
                </td>
                <td class="w-36 py-4">
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
                <td colspan="4">
                    <x-ui.empty-state description="Chưa có danh mục báo giá nào." />
                </td>
            </tr>
        @endforelse
    </x-ui.table>

    <x-ui.pagination :paginator="$pricingCategories" />
@endsection
