<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 30)->nullable();
            $table->string('company')->nullable();
            $table->string('service')->nullable();
            $table->text('message');
            $table->enum('status', ['new', 'contacted', 'completed', 'spam'])
                ->default('new');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
