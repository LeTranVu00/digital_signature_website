@extends('layouts.admin')

@section('title', 'Quản lý người dùng')

@section('content')
    <x-ui.page-header
        title="Quản lý người dùng"
        description="Quản lý quyền, trạng thái và hoạt động bình luận của tài khoản."
    />

    <form
        action="{{ route('admin.users.index') }}"
        method="GET"
        class="mb-6 grid gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:grid-cols-4"
    >
        <div class="lg:col-span-2">
            <x-ui.input
                type="search"
                name="search"
                label="Tìm theo tên/email"
                :value="$filters['search'] ?? ''"
                placeholder="Nhập tên hoặc email..."
            />
        </div>

        <x-ui.select name="role" label="Role">
            <option value="">Tất cả</option>
            <option value="admin" @selected(($filters['role'] ?? '') === 'admin')>
                Admin
            </option>
            <option value="user" @selected(($filters['role'] ?? '') === 'user')>
                User
            </option>
        </x-ui.select>

        <x-ui.select name="status" label="Trạng thái">
            <option value="">Tất cả</option>
            <option value="active" @selected(($filters['status'] ?? '') === 'active')>
                Active
            </option>
            <option value="blocked" @selected(($filters['status'] ?? '') === 'blocked')>
                Blocked
            </option>
        </x-ui.select>

        <div class="flex items-end gap-3 lg:col-span-4">
            <x-ui.button type="submit">
                Lọc người dùng
            </x-ui.button>

            <x-ui.button :href="route('admin.users.index')" variant="secondary">
                Xóa lọc
            </x-ui.button>
        </div>
    </form>

    <x-ui.table table-class="w-full">
        <x-slot name="head">
            <tr>
                <th>Người dùng</th>
                <th>Role</th>
                <th>Trạng thái</th>
                <th>Ngày đăng ký</th>
                <th>Số bình luận</th>
                <th class="text-right">Thao tác</th>
            </tr>
        </x-slot>

        @forelse ($users as $user)
            <tr>
                <td>
                    <div class="font-semibold text-slate-950">{{ $user->name }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ $user->email }}</div>

                    @if (auth()->id() === $user->id)
                        <x-ui.badge class="mt-2" variant="primary">Bạn</x-ui.badge>
                    @endif
                </td>

                <td>
                    <x-ui.badge :variant="$user->isAdmin() ? 'admin' : 'neutral'">
                        {{ $user->isAdmin() ? 'Admin' : 'User' }}
                    </x-ui.badge>
                </td>

                <td>
                    <x-ui.badge :variant="$user->isBlocked() ? 'blocked' : 'active'">
                        {{ $user->isBlocked() ? 'Blocked' : 'Active' }}
                    </x-ui.badge>
                </td>

                <td>{{ $user->created_at?->format('d/m/Y H:i') }}</td>
                <td>{{ number_format($user->comments_count) }}</td>

                <td>
                    <div class="ui-table-actions">
                        @if ($user->isAdmin())
                            <x-ui.confirm-delete
                                :action="route('admin.users.role', $user)"
                                method="PATCH"
                                trigger="Hạ quyền"
                                title="Chuyển admin thành user?"
                                description="Tài khoản này sẽ không còn truy cập khu vực quản trị."
                                confirm-text="Hạ quyền"
                                variant="warning"
                                button-variant="warning"
                                :disabled="auth()->id() === $user->id"
                            >
                                <input type="hidden" name="role" value="user">
                            </x-ui.confirm-delete>
                        @else
                            <form action="{{ route('admin.users.role', $user) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="role" value="admin">

                                <x-ui.button type="submit" size="xs">
                                    Lên admin
                                </x-ui.button>
                            </form>
                        @endif

                        @if ($user->isBlocked())
                            <form action="{{ route('admin.users.status', $user) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="active">

                                <x-ui.button type="submit" variant="success" size="xs">
                                    Mở khóa
                                </x-ui.button>
                            </form>
                        @else
                            <x-ui.confirm-delete
                                :action="route('admin.users.status', $user)"
                                method="PATCH"
                                trigger="Khóa"
                                title="Khóa tài khoản?"
                                description="Tài khoản này sẽ bị chặn hoạt động cho đến khi được mở khóa."
                                confirm-text="Khóa tài khoản"
                                button-size="xs"
                                :disabled="auth()->id() === $user->id"
                            >
                                <input type="hidden" name="status" value="blocked">
                            </x-ui.confirm-delete>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">
                    <x-ui.empty-state description="Không có người dùng phù hợp." />
                </td>
            </tr>
        @endforelse
    </x-ui.table>

    <x-ui.pagination :paginator="$users" />
@endsection
