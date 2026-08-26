@php
    $isEdit = filled($pricingCategory);
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
        <h3 class="font-semibold text-red-800">Dữ liệu chưa hợp lệ</h3>
        <ul class="mt-2 list-inside list-disc text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<x-ui.card>
    <form
        action="{{ $action }}"
        method="POST"
        enctype="multipart/form-data"
        x-data="{ imagePreview: null, submitting: false }"
        x-on:submit="submitting = true"
    >
        @csrf

        @if ($method !== 'POST')
            @method($method)
        @endif

        <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="space-y-5">
                <x-ui.input
                    name="name"
                    label="Tên danh mục"
                    :value="old('name', $pricingCategory?->name)"
                    maxlength="160"
                    autofocus
                />

                <x-ui.textarea
                    name="description"
                    label="Mô tả ngắn"
                    :value="old('description', $pricingCategory?->description)"
                    rows="4"
                    maxlength="1000"
                    placeholder="Mô tả nhóm dịch vụ hoặc ghi chú ngắn cho bảng giá"
                />

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.input
                        type="number"
                        name="sort_order"
                        label="Thứ tự hiển thị"
                        :value="old('sort_order', $pricingCategory?->sort_order ?? 0)"
                        min="0"
                        max="9999"
                    />

                    <label class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            class="rounded border-slate-300 text-amber-500 focus:ring-amber-500"
                            @checked(old('is_active', $pricingCategory?->is_active ?? true))
                        >
                        Hiển thị ngoài trang báo giá
                    </label>
                </div>
            </div>

            <div class="space-y-5">
                <x-ui.input
                    type="file"
                    name="image"
                    label="Ảnh bảng giá"
                    accept=".jpg,.jpeg,.png,.webp"
                    helper="JPG, JPEG, PNG hoặc WEBP. Tối đa 8 MB. Ảnh nên rõ chữ giá tiền."
                    x-on:change="imagePreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                />

                <div
                    x-show="imagePreview"
                    x-cloak
                    class="overflow-hidden rounded-xl border border-amber-200 bg-amber-50"
                >
                    <img
                        x-bind:src="imagePreview"
                        alt="Preview ảnh bảng giá mới"
                        class="max-h-96 w-full object-contain"
                    >
                </div>

                @if ($pricingCategory?->image_path)
                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                        <div class="border-b border-slate-200 px-4 py-3 text-sm font-bold text-slate-700">
                            Ảnh hiện tại
                        </div>
                        <img
                            src="{{ $pricingCategory->imageUrl() }}"
                            alt="{{ $pricingCategory->name }}"
                            class="max-h-96 w-full object-contain"
                        >
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end">
            <x-ui.button :href="route('admin.pricing-categories.index')" variant="secondary">
                Hủy
            </x-ui.button>

            <x-ui.submit-button loading-text="Đang lưu...">
                {{ $isEdit ? 'Cập nhật danh mục' : 'Tạo danh mục' }}
            </x-ui.submit-button>
        </div>
    </form>
</x-ui.card>
