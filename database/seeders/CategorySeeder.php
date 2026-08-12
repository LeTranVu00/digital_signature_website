<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Hội kế toán',
            'Chữ ký số',
            'Hóa đơn điện tử',
            'Hỏi đáp doanh nghiệp',
            'Phần mềm hỗ trợ',
            'Báo giá',
        ];

        foreach ($categories as $category) {
            Category::query()->firstOrCreate([
                'slug' => Str::slug($category),
            ], [
                'name' => $category,
                'description' => $category,
            ]);
        }
    }
}
