<?php

use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoryController;

Route::middleware(['auth'])->group(function () {
    Route::resource('categories', CategoryController::class)
    ->except(['show']);
});

Route::get('/', [PageController::class, 'home'])
    ->name('home');
Route::get('/gioi-thieu', [PageController::class, 'about'])
    ->name('about');
Route::get('/dich-vu', [PageController::class, 'services'])
    ->name('services');
Route::get('/lien-he', [PageController::class, 'contact'])
    ->name('contact');

Route::get('/blog', [BlogController::class, 'index'])
    ->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])
    ->name('blog.show');
Route::get('/danh-muc/{category:slug}', [BlogController::class, 'category'])
    ->name('blog.category');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::view('/dashboard', 'admin.dashboard')
            ->name('dashboard');

        Route::resource('categories', CategoryController::class)
            ->except(['show']);
        Route::get('/posts/trash', [PostController::class, 'trash'])
            ->name('posts.trash');

        Route::patch(
            '/posts/{trashedPost}/restore',
            [PostController::class, 'restore']
        )
            ->whereNumber('trashedPost')
            ->name('posts.restore');

        Route::delete(
            '/posts/{trashedPost}/force-delete',
            [PostController::class, 'forceDelete']
        )
            ->whereNumber('trashedPost')
            ->name('posts.force-delete');

        Route::get('/posts/{post}/preview', [PostController::class, 'preview'])
            ->whereNumber('post')
            ->name('posts.preview');

        Route::resource('posts', PostController::class)
            ->except(['show']);
});
require __DIR__.'/auth.php';
