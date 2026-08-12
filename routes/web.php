<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\PricingCategoryController;
use App\Http\Controllers\Admin\SiteContentController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\CommentController;
use App\Http\Controllers\Frontend\CommentVoteController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\ProfileController;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])
    ->name('home');

Route::redirect('/gioi-thieu', '/')
    ->name('about');
Route::get('/bao-gia', [PageController::class, 'pricing'])
    ->name('pricing');
Route::get('/bao-gia/goi/{plan}', [PageController::class, 'pricingPlan'])
    ->whereNumber('plan')
    ->name('pricing.plan');
Route::get('/phan-mem-ho-tro', [PageController::class, 'software'])
    ->name('software');
Route::redirect('/dich-vu', '/phan-mem-ho-tro')
    ->name('services');
Route::get('/lien-he', [PageController::class, 'contact'])
    ->name('contact');
Route::post('/lien-he', [ContactController::class, 'store'])
    ->middleware('throttle:contacts')
    ->name('contact.store');

Route::redirect('/blog', '/dien-dan');
Route::get('/blog/{post:slug}', fn (Post $post) => redirect()->route('blog.show', $post->slug));
Route::get('/danh-muc/{category:slug}', fn (Category $category) => redirect()->route('blog.category', $category->slug));

Route::get('/dien-dan', [BlogController::class, 'index'])
    ->name('blog.index');
Route::get('/dien-dan/danh-muc/{category:slug}', [BlogController::class, 'category'])
    ->name('blog.category');
Route::get('/dien-dan/{post:slug}', [BlogController::class, 'show'])
    ->name('blog.show');

Route::middleware('auth')->group(function () {
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])
        ->middleware('throttle:comments')
        ->name('posts.comments.store');
    Route::patch('/comments/{comment}', [CommentController::class, 'update'])
        ->middleware('throttle:comments')
        ->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
        ->middleware('throttle:comments')
        ->name('comments.destroy');
    Route::post('/comments/{comment}/vote', [CommentVoteController::class, 'store'])
        ->middleware('throttle:votes')
        ->name('comments.vote');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/password', [ProfileController::class, 'password'])->name('profile.password.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)
            ->name('dashboard');

        Route::resource('categories', CategoryController::class)
            ->except(['show']);

        Route::resource('pricing-categories', PricingCategoryController::class)
            ->except(['show']);

        Route::get('/users', [AdminUserController::class, 'index'])
            ->name('users.index');
        Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])
            ->name('users.role');
        Route::patch('/users/{user}/status', [AdminUserController::class, 'updateStatus'])
            ->name('users.status');

        Route::get('/comments', [AdminCommentController::class, 'index'])
            ->name('comments.index');
        Route::delete('/comments/{comment}', [AdminCommentController::class, 'destroy'])
            ->name('comments.destroy');
        Route::patch(
            '/comments/{trashedComment}/restore',
            [AdminCommentController::class, 'restore']
        )
            ->whereNumber('trashedComment')
            ->name('comments.restore');
        Route::delete(
            '/comments/{trashedComment}/force-delete',
            [AdminCommentController::class, 'forceDelete']
        )
            ->whereNumber('trashedComment')
            ->name('comments.force-delete');

        Route::get('/contacts', [AdminContactController::class, 'index'])
            ->name('contacts.index');
        Route::patch('/contacts/{contact}/status', [AdminContactController::class, 'updateStatus'])
            ->name('contacts.status');

        Route::get('/site-content', [SiteContentController::class, 'index'])
            ->name('site-content.index');
        Route::patch('/site-content/{section}', [SiteContentController::class, 'update'])
            ->name('site-content.update');

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
            ->except(['show'])
            ->middlewareFor(['store', 'update'], 'throttle:uploads');
    });
require __DIR__.'/auth.php';
