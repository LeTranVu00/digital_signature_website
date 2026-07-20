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
        Schema::create('comment_votes', function (Blueprint $table) {
            $table->id();

            // Bình luận được vote
            $table->foreignId('comment_id')
                ->constrained()
                ->cascadeOnDelete();

            // Người thực hiện vote
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // 1 = Like, -1 = Dislike
            $table->tinyInteger('vote');

            $table->timestamps();

            // Một người chỉ được vote một lần cho một bình luận
            $table->unique([
                'comment_id',
                'user_id'
            ]);
        });
    }
};
