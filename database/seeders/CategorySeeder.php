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
            'Tin tức',
            'Chữ ký số',
            'Hóa đơn điện tử',
            'Tin công nghệ',
            'Hướng dẫn',
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category,
                'slug' => Str::slug($category),
                'description' => $category,
            ]);
        }
    }
}