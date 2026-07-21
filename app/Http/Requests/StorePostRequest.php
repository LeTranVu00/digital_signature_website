<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    /**
     * Kiểm tra người dùng có quyền gửi request hay không.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Chuẩn hóa dữ liệu trước khi validation.
     */
    protected function prepareForValidation(): void
    {
        $title = trim((string) $this->input('title'));

        $this->merge([
            'title' => $title,
            'slug' => Str::slug($title),
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
                Rule::unique('posts', 'slug'),
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
            'category_id.required' =>
                'Bạn chưa chọn danh mục.',

            'category_id.integer' =>
                'Danh mục không hợp lệ.',

            'category_id.exists' =>
                'Danh mục đã chọn không tồn tại.',

            'title.required' =>
                'Tiêu đề bài viết không được để trống.',

            'title.string' =>
                'Tiêu đề bài viết không hợp lệ.',

            'title.max' =>
                'Tiêu đề không được vượt quá 255 ký tự.',

            'slug.required' =>
                'Không thể tạo đường dẫn cho bài viết.',

            'slug.unique' =>
                'Đường dẫn của bài viết này đã tồn tại.',

            'slug.max' =>
                'Đường dẫn không được vượt quá 255 ký tự.',

            'summary.string' =>
                'Mô tả ngắn không hợp lệ.',

            'summary.max' =>
                'Mô tả ngắn không được vượt quá 500 ký tự.',

            'content.required' =>
                'Nội dung bài viết không được để trống.',

            'content.string' =>
                'Nội dung bài viết không hợp lệ.',

            'thumbnail.image' =>
                'Ảnh đại diện phải là một file hình ảnh.',

            'thumbnail.mimes' =>
                'Ảnh đại diện chỉ hỗ trợ JPG, JPEG, PNG hoặc WEBP.',

            'thumbnail.max' =>
                'Ảnh đại diện không được vượt quá 5 MB.',

            'status.required' =>
                'Bạn chưa chọn trạng thái bài viết.',

            'status.in' =>
                'Trạng thái bài viết không hợp lệ.',
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