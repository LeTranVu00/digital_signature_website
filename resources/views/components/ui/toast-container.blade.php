@php
    $toasts = [];

    foreach (['success', 'error', 'warning', 'info'] as $type) {
        if (session($type)) {
            $toasts[] = [
                'type' => $type,
                'title' => match ($type) {
                    'success' => 'Thành công',
                    'error' => 'Có lỗi xảy ra',
                    'warning' => 'Cần chú ý',
                    default => 'Thông báo',
                },
                'message' => session($type),
            ];
        }
    }

    $statusMessages = [
        'profile-updated' => [
            'type' => 'success',
            'title' => 'Đã lưu hồ sơ',
            'message' => 'Thông tin tài khoản đã được cập nhật thành công.',
        ],
        'password-updated' => [
            'type' => 'success',
            'title' => 'Đã đổi mật khẩu',
            'message' => 'Mật khẩu mới đã được lưu thành công.',
        ],
        'verification-link-sent' => [
            'type' => 'success',
            'title' => 'Đã gửi email xác minh',
            'message' => 'Vui lòng kiểm tra hộp thư để tiếp tục xác minh tài khoản.',
        ],
    ];

    if (session('status') && isset($statusMessages[session('status')])) {
        $toasts[] = $statusMessages[session('status')];
    } elseif (session('status')) {
        $toasts[] = [
            'type' => 'info',
            'title' => 'Thông báo',
            'message' => session('status'),
        ];
    }

    if ($errors->any()) {
        $count = count($errors->all());
        $toasts[] = [
            'type' => 'error',
            'title' => 'Dữ liệu chưa hợp lệ',
            'message' => $count === 1
                ? 'Vui lòng kiểm tra trường đang báo lỗi.'
                : "Vui lòng kiểm tra {$count} trường đang báo lỗi.",
        ];
    }
@endphp

@if ($toasts)
    <div
        class="pointer-events-none fixed inset-x-3 top-20 z-[80] grid gap-3 sm:left-auto sm:right-4 sm:w-full sm:max-w-md"
        aria-live="polite"
        aria-atomic="false"
    >
        @foreach ($toasts as $toast)
            <x-ui.toast
                :type="$toast['type']"
                :title="$toast['title'] ?? null"
                :message="$toast['message']"
                :duration="5200 + ($loop->index * 350)"
            />
        @endforeach
    </div>
@endif
