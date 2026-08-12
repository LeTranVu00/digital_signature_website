@extends('layouts.admin')

@section('title', 'Quản lý liên hệ')

@section('content')
    <x-ui.page-header
        title="Quản lý liên hệ"
        description="Theo dõi lead mới, lọc nhu cầu và cập nhật trạng thái chăm sóc."
    />

    <form
        action="{{ route('admin.contacts.index') }}"
        method="GET"
        class="mb-6 grid gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:grid-cols-4"
    >
        <div class="lg:col-span-2">
            <x-ui.input
                type="search"
                name="search"
                label="Tìm lead"
                :value="$filters['search'] ?? ''"
                placeholder="Tên, email, điện thoại, công ty..."
            />
        </div>

        <x-ui.select name="service" label="Dịch vụ">
            <option value="">Tất cả</option>

            @foreach (\App\Models\Contact::SERVICES as $value => $label)
                <option value="{{ $value }}" @selected(($filters['service'] ?? '') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </x-ui.select>

        <x-ui.select name="status" label="Trạng thái">
            <option value="">Tất cả</option>

            @foreach (\App\Models\Contact::STATUSES as $status)
                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                    {{ ucfirst($status) }}
                </option>
            @endforeach
        </x-ui.select>

        <div class="flex items-end gap-3 lg:col-span-4">
            <x-ui.button type="submit">
                Lọc liên hệ
            </x-ui.button>

            <x-ui.button :href="route('admin.contacts.index')" variant="secondary">
                Xóa lọc
            </x-ui.button>
        </div>
    </form>

    <x-ui.table>
        <x-slot name="head">
            <tr>
                <th>Khách hàng</th>
                <th>Dịch vụ</th>
                <th>Nội dung</th>
                <th>Trạng thái</th>
                <th>Ngày gửi</th>
                <th class="text-right">Thao tác</th>
            </tr>
        </x-slot>

        @forelse ($contacts as $contact)
            <tr>
                <td class="min-w-64">
                    <div class="font-semibold text-slate-950">{{ $contact->name }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ $contact->email }}</div>
                    <div class="mt-1 text-xs text-slate-400">
                        {{ $contact->phone ?? 'Chưa có số điện thoại' }}
                    </div>

                    @if ($contact->company)
                        <div class="mt-1 text-xs text-slate-500">{{ $contact->company }}</div>
                    @endif
                </td>

                <td>{{ $contact->serviceLabel() }}</td>

                <td class="max-w-md">
                    <p class="whitespace-pre-line text-slate-900">
                        {{ \Illuminate\Support\Str::limit($contact->message, 180) }}
                    </p>
                </td>

                <td>
                    @php
                        $statusVariant = match ($contact->status) {
                            'contacted' => 'primary',
                            'completed' => 'success',
                            'spam' => 'danger',
                            default => 'warning',
                        };
                    @endphp

                    <x-ui.badge :variant="$statusVariant">
                        {{ $contact->statusLabel() }}
                    </x-ui.badge>
                </td>

                <td>{{ $contact->created_at?->format('d/m/Y H:i') }}</td>

                <td>
                    <form
                        action="{{ route('admin.contacts.status', $contact) }}"
                        method="POST"
                        class="flex justify-end gap-2"
                    >
                        @csrf
                        @method('PATCH')

                        <select
                            name="status"
                            class="ui-focus rounded-lg border-slate-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            @foreach (\App\Models\Contact::STATUSES as $status)
                                <option value="{{ $status }}" @selected($contact->status === $status)>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>

                        <x-ui.button type="submit" size="xs">
                            Lưu
                        </x-ui.button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">
                    <x-ui.empty-state description="Chưa có liên hệ nào phù hợp." />
                </td>
            </tr>
        @endforelse
    </x-ui.table>

    <x-ui.pagination :paginator="$contacts" />
@endsection
