<?php

namespace App\Http\Requests;

use App\Services\HtmlSanitizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
{
    /**
     * Kiểm tra quyền cập nhật bài viết.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Chuẩn hóa dữ liệu trước validation.
     */
    protected function prepareForValidation(): void
    {
        $title = trim((string) $this->input('title'));

        $this->merge([
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => app(HtmlSanitizer::class)->clean(
                (string) $this->input('content')
            ),
        ]);
    }

    /**
     * Các quy tắc validation.
     */
    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'required',
                'string',
                'max:255',
            ],

            'summary' => [
                'nullable',
                'string',
                'max:500',
            ],

            'content' => [
                'required',
                'string',
            ],

            'thumbnail' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:5120',
            ],

            'status' => [
                'required',
                Rule::in([
                    'draft',
                    'published',
                ]),
            ],
        ];
    }

    /**
     * Thông báo lỗi bằng tiếng Việt.
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Bạn chưa chọn danh mục.',

            'category_id.integer' => 'Danh mục không hợp lệ.',

            'category_id.exists' => 'Danh mục đã chọn không tồn tại.',

            'title.required' => 'Tiêu đề bài viết không được để trống.',

            'title.string' => 'Tiêu đề bài viết không hợp lệ.',

            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',

            'slug.required' => 'Không thể tạo đường dẫn cho bài viết.',

            'slug.max' => 'Đường dẫn không được vượt quá 255 ký tự.',

            'summary.string' => 'Mô tả ngắn không hợp lệ.',

            'summary.max' => 'Mô tả ngắn không được vượt quá 500 ký tự.',

            'content.required' => 'Nội dung bài viết không được để trống.',

            'content.string' => 'Nội dung bài viết không hợp lệ.',

            'thumbnail.image' => 'Ảnh đại diện phải là một file hình ảnh.',

            'thumbnail.mimes' => 'Ảnh đại diện chỉ hỗ trợ JPG, JPEG, PNG hoặc WEBP.',

            'thumbnail.max' => 'Ảnh đại diện không được vượt quá 5 MB.',

            'status.required' => 'Bạn chưa chọn trạng thái bài viết.',

            'status.in' => 'Trạng thái bài viết không hợp lệ.',
        ];
    }

    /**
     * Tên hiển thị của các trường.
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'danh mục',
            'title' => 'tiêu đề',
            'slug' => 'đường dẫn',
            'summary' => 'mô tả ngắn',
            'content' => 'nội dung',
            'thumbnail' => 'ảnh đại diện',
            'status' => 'trạng thái',
        ];
    }
}
