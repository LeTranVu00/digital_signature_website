<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public static function valueFor(string $key): array
    {
        $default = self::defaults()[$key] ?? [];
        $stored = self::query()->where('key', $key)->first()?->value;
        $stored = is_array($stored) ? self::normalizeStoredValue($key, $stored) : [];

        return self::mergeDefaults($default, $stored);
    }

    public static function putValue(string $key, array $value): self
    {
        return self::query()->updateOrCreate(
            ['key' => $key],
            ['value' => self::mergeDefaults(self::defaults()[$key] ?? [], $value)]
        );
    }

    public static function defaults(): array
    {
        return [
            'home' => [
                'hero_title' => 'Một nơi để doanh nghiệp tra cứu, tải phần mềm và nhận hỗ trợ ký số.',
                'hero_copy' => 'Digital Signature giúp bạn chọn đúng gói dịch vụ, cài đặt công cụ cần thiết và xử lý các vướng mắc khi dùng chữ ký số, hóa đơn điện tử hoặc hợp đồng điện tử.',
                'intro_text' => 'Chúng tôi tập trung vào chữ ký số, hóa đơn điện tử, hợp đồng điện tử và phần mềm hỗ trợ doanh nghiệp. Mục tiêu là giúp khách hàng chọn đúng dịch vụ, triển khai nhanh và có nơi hỏi đáp rõ ràng khi phát sinh vấn đề.',
                'services' => [
                    ['title' => 'Chữ ký số', 'desc' => 'Tư vấn gói cá nhân, doanh nghiệp, token và ký số từ xa theo nhu cầu sử dụng.'],
                    ['title' => 'Hóa đơn điện tử', 'desc' => 'Hỗ trợ phát hành, ký hóa đơn, xử lý lỗi môi trường và thao tác vận hành.'],
                    ['title' => 'Hợp đồng điện tử', 'desc' => 'Gợi ý quy trình ký, lưu trữ, tra cứu và xác thực giao dịch điện tử.'],
                    ['title' => 'Hỗ trợ kỹ thuật', 'desc' => 'Đồng hành trong cài đặt phần mềm, kiểm tra lỗi và hướng dẫn sử dụng.'],
                ],
                'process_intro' => 'Từ tư vấn đến cài đặt đều có bước rõ ràng.',
                'process_steps' => [
                    ['title' => 'Tiếp nhận nhu cầu', 'desc' => 'Ghi nhận dịch vụ, thời hạn, thiết bị và tình trạng hồ sơ hiện tại.'],
                    ['title' => 'Tư vấn phương án', 'desc' => 'Đề xuất gói phù hợp, báo giá và các thông tin cần chuẩn bị.'],
                    ['title' => 'Triển khai sử dụng', 'desc' => 'Hỗ trợ kích hoạt, cài phần mềm và kiểm tra thao tác ký.'],
                ],
                'stats' => [
                    ['value' => '24/7', 'label' => 'tiếp nhận yêu cầu online'],
                    ['value' => '4+', 'label' => 'nhóm giải pháp chính'],
                    ['value' => '1:1', 'label' => 'hỗ trợ theo từng hồ sơ'],
                ],
                'cta_title' => 'Cần báo giá hoặc hỗ trợ cài đặt ngay?',
                'cta_copy' => 'Gửi thông tin, đội ngũ tư vấn sẽ liên hệ lại để kiểm tra nhu cầu và hướng dẫn bước tiếp theo.',
            ],
            'pricing' => [
                'hero_title' => 'Cập nhật báo giá dịch vụ chữ ký số và giải pháp điện tử.',
                'hero_copy' => 'Chọn nhóm dịch vụ bạn quan tâm hoặc gửi yêu cầu để đội ngũ tư vấn kiểm tra nhu cầu và phản hồi báo giá phù hợp.',
                'plans' => [
                    [
                        'name' => 'Chữ ký số cá nhân',
                        'price' => 'Liên hệ',
                        'desc' => 'Phù hợp cá nhân cần ký hồ sơ điện tử, giao dịch trực tuyến và xử lý thủ tục hành chính.',
                        'features' => ['Tư vấn loại chứng thư phù hợp', 'Hỗ trợ kích hoạt', 'Hướng dẫn sử dụng ban đầu'],
                        'images' => [],
                    ],
                    [
                        'name' => 'Chữ ký số doanh nghiệp',
                        'price' => 'Liên hệ',
                        'desc' => 'Dành cho doanh nghiệp khai thuế, bảo hiểm, hải quan, ngân hàng và ký văn bản.',
                        'features' => ['Tư vấn gói theo nhu cầu', 'Hỗ trợ cài đặt token', 'Hỗ trợ xử lý lỗi ký số'],
                        'images' => [],
                    ],
                    [
                        'name' => 'Hóa đơn điện tử',
                        'price' => 'Liên hệ',
                        'desc' => 'Tư vấn phát hành, ký số, quản lý và vận hành hóa đơn điện tử theo quy trình doanh nghiệp.',
                        'features' => ['Tư vấn phát hành', 'Hướng dẫn thao tác', 'Hỗ trợ kết nối chữ ký số'],
                        'images' => [],
                    ],
                ],
                'notes_title' => 'Gửi thông tin để nhận báo giá chính xác hơn.',
                'notes_copy' => 'Một số gói dịch vụ phụ thuộc thời hạn, loại chứng thư và số lượng hồ sơ. Form liên hệ giúp công ty phản hồi đúng nhu cầu của bạn.',
                'notes' => [
                    'Báo giá có thể thay đổi theo thời hạn sử dụng, nhà cung cấp và số lượng cần đăng ký.',
                    'Doanh nghiệp có nhu cầu nhiều dịch vụ có thể gửi thông tin để nhận báo giá tổng hợp.',
                    'Khi có chương trình ưu đãi mới, nội dung báo giá sẽ được cập nhật tại trang này.',
                ],
            ],
            'contact' => [
                'hero_title' => 'Gửi yêu cầu tư vấn hoặc hỗ trợ kỹ thuật.',
                'hero_copy' => 'Để lại thông tin doanh nghiệp, nhu cầu dịch vụ hoặc lỗi đang gặp. Đội ngũ hỗ trợ sẽ liên hệ lại để tư vấn và xử lý.',
                'cards' => [
                    ['title' => 'Hotline', 'value' => '0900 000 000', 'desc' => 'Tiếp nhận tư vấn và hỗ trợ kỹ thuật'],
                    ['title' => 'Email', 'value' => 'support@example.com', 'desc' => 'Gửi yêu cầu chi tiết hoặc tài liệu cần kiểm tra'],
                    ['title' => 'Thời gian hỗ trợ', 'value' => '8:00 - 17:30', 'desc' => 'Thứ 2 đến Thứ 6, trừ ngày lễ'],
                ],
                'form_title' => 'Công ty sẽ phản hồi theo thông tin bạn để lại.',
                'form_copy' => 'Vui lòng nhập đúng số điện thoại, email và nội dung cần hỗ trợ để đội ngũ tư vấn chuẩn bị thông tin trước khi liên hệ.',
                'qr_cards' => [
                    ['label' => 'QR hỗ trợ doanh nghiệp', 'image' => 'images/qr-vip-ho-tro-doanh-nghiep.jpg', 'alt' => 'QR hỗ trợ doanh nghiệp'],
                    ['label' => 'QR Zalo hỗ trợ', 'image' => 'images/zalo-qr-business-support.jpg', 'alt' => 'QR Zalo hỗ trợ'],
                ],
                'company_name' => 'Digital Signature',
                'address' => 'Cập nhật địa chỉ công ty tại đây.',
                'phone' => '0900 000 000',
                'email' => 'support@example.com',
                'bank_accounts' => [
                    ['bank' => 'Ngân hàng A', 'account' => '0000 0000 0000', 'owner' => 'CONG TY DIGITAL SIGNATURE'],
                    ['bank' => 'Ngân hàng B', 'account' => '1111 1111 1111', 'owner' => 'CONG TY DIGITAL SIGNATURE'],
                ],
            ],
            'software' => [
                'hero_title' => 'Tải phần mềm cần thiết cho chữ ký số, hóa đơn và kê khai.',
                'hero_copy' => 'Danh sách này ưu tiên link tải ngoài hoặc link nhà cung cấp để website nhẹ hơn và người dùng luôn tải đúng phiên bản mới.',
                'notice' => 'Chỉ tải phần mềm từ nguồn chính thức hoặc link đã được công ty xác nhận. Nếu không chắc nên cài bản nào, vui lòng liên hệ bộ phận hỗ trợ trước khi cài đặt.',
                'items' => [
                    ['name' => 'Phần mềm ký số USB Token', 'desc' => 'Công cụ cài đặt và nhận diện USB Token khi ký tờ khai, hóa đơn hoặc văn bản điện tử.', 'type' => 'Link nhà cung cấp', 'url' => 'https://www.google.com/search?q=phan+mem+ky+so+usb+token'],
                    ['name' => 'Công cụ ký hóa đơn điện tử', 'desc' => 'Hỗ trợ ký và kiểm tra trạng thái chữ ký khi phát hành hóa đơn điện tử.', 'type' => 'Link hỗ trợ', 'url' => 'https://www.google.com/search?q=cong+cu+ky+hoa+don+dien+tu'],
                    ['name' => 'Ứng dụng hỗ trợ kê khai', 'desc' => 'Dành cho doanh nghiệp cần kê khai, nộp tờ khai và xử lý lỗi môi trường Java/trình duyệt.', 'type' => 'Link tải ngoài', 'url' => 'https://www.google.com/search?q=ung+dung+ho+tro+ke+khai'],
                    ['name' => 'Phần mềm điều khiển hỗ trợ từ xa', 'desc' => 'Dùng khi kỹ thuật viên cần hỗ trợ cài đặt, kiểm tra lỗi ký số hoặc hướng dẫn thao tác.', 'type' => 'Link tải ngoài', 'url' => 'https://www.google.com/search?q=phan+mem+ho+tro+tu+xa'],
                ],
                'categories' => [
                    [
                        'name' => 'Chữ ký số',
                        'desc' => 'Công cụ cài đặt và nhận diện thiết bị ký số.',
                        'items' => [
                            ['name' => 'Phần mềm ký số USB Token', 'desc' => 'Công cụ cài đặt và nhận diện USB Token khi ký tờ khai, hóa đơn hoặc văn bản điện tử.', 'type' => 'Link nhà cung cấp', 'url' => 'https://www.google.com/search?q=phan+mem+ky+so+usb+token'],
                        ],
                    ],
                    [
                        'name' => 'Hóa đơn điện tử',
                        'desc' => 'Công cụ phục vụ ký và kiểm tra hóa đơn điện tử.',
                        'items' => [
                            ['name' => 'Công cụ ký hóa đơn điện tử', 'desc' => 'Hỗ trợ ký và kiểm tra trạng thái chữ ký khi phát hành hóa đơn điện tử.', 'type' => 'Link hỗ trợ', 'url' => 'https://www.google.com/search?q=cong+cu+ky+hoa+don+dien+tu'],
                        ],
                    ],
                    [
                        'name' => 'Kê khai và hỗ trợ',
                        'desc' => 'Phần mềm hỗ trợ kê khai và hỗ trợ kỹ thuật từ xa.',
                        'items' => [
                            ['name' => 'Ứng dụng hỗ trợ kê khai', 'desc' => 'Dành cho doanh nghiệp cần kê khai, nộp tờ khai và xử lý lỗi môi trường Java/trình duyệt.', 'type' => 'Link tải ngoài', 'url' => 'https://www.google.com/search?q=ung+dung+ho+tro+ke+khai'],
                            ['name' => 'Phần mềm điều khiển hỗ trợ từ xa', 'desc' => 'Dùng khi kỹ thuật viên cần hỗ trợ cài đặt, kiểm tra lỗi ký số hoặc hướng dẫn thao tác.', 'type' => 'Link tải ngoài', 'url' => 'https://www.google.com/search?q=phan+mem+ho+tro+tu+xa'],
                        ],
                    ],
                ],
                'support_title' => 'Gặp lỗi khi cài đặt hoặc ký số?',
                'support_copy' => 'Gửi thông tin liên hệ kèm mô tả lỗi để kỹ thuật viên kiểm tra môi trường máy tính, trình duyệt, token và phần mềm cần thiết.',
                'checklist' => [
                    'Loại chữ ký số hoặc nhà cung cấp đang dùng.',
                    'Ảnh chụp màn hình lỗi nếu có.',
                    'Phiên bản Windows/trình duyệt đang sử dụng.',
                    'Số điện thoại để kỹ thuật viên liên hệ lại.',
                ],
            ],
        ];
    }

    private static function normalizeStoredValue(string $key, array $stored): array
    {
        if (
            $key === 'software'
            && ! array_key_exists('categories', $stored)
            && ! empty($stored['items'] ?? [])
        ) {
            $stored['categories'] = [[
                'name' => 'Phần mềm chung',
                'desc' => 'Các phần mềm hỗ trợ đang có.',
                'items' => array_values($stored['items']),
            ]];
        }

        return $stored;
    }

    private static function mergeDefaults(array $defaults, array $value): array
    {
        foreach ($value as $key => $item) {
            if (
                array_key_exists($key, $defaults)
                && is_array($defaults[$key])
                && is_array($item)
                && ! array_is_list($defaults[$key])
                && ! array_is_list($item)
            ) {
                $defaults[$key] = self::mergeDefaults($defaults[$key], $item);
            } else {
                $defaults[$key] = $item;
            }
        }

        return $defaults;
    }
}
