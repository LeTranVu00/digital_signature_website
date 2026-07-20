<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();

            // Bài viết
            $table->foreignId('post_id')
                ->constrained()
                ->cascadeOnDelete();

            // Người bình luận
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Bình luận cha (reply)
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('comments')
                ->cascadeOnDelete();

            // Nội dung
            $table->text('content');

            // Thời gian chỉnh sửa
            $table->timestamp('edited_at')
                ->nullable();

            // Soft Delete
            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
