<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'total_posts' => Post::query()->count(),
            'published_posts' => Post::query()->where('status', 'published')->count(),
            'draft_posts' => Post::query()->where('status', 'draft')->count(),
            'trashed_posts' => Post::onlyTrashed()->count(),
            'total_users' => User::query()->count(),
            'total_comments' => Comment::query()->count(),
            'total_views' => Post::query()->sum('views'),
        ];

        $latestPosts = Post::query()
            ->with(['category', 'user'])
            ->withCount('comments')
            ->latest()
            ->limit(5)
            ->get();

        $latestComments = Comment::query()
            ->with(['user', 'post'])
            ->latest()
            ->limit(5)
            ->get();

        $topPosts = Post::query()
            ->with(['category', 'user'])
            ->withCount('comments')
            ->orderByDesc('views')
            ->limit(5)
            ->get();

        $monthlyPosts = collect(range(11, 0))
            ->map(function (int $monthsAgo): array {
                $month = now()->startOfMonth()->subMonths($monthsAgo);

                return [
                    'label' => $month->format('m/Y'),
                    'count' => Post::query()
                        ->whereBetween('created_at', [
                            Carbon::parse($month)->startOfMonth(),
                            Carbon::parse($month)->endOfMonth(),
                        ])
                        ->count(),
                ];
            });

        $maxMonthlyPosts = max($monthlyPosts->max('count'), 1);

        return view('admin.dashboard', compact(
            'latestComments',
            'latestPosts',
            'maxMonthlyPosts',
            'monthlyPosts',
            'stats',
            'topPosts'
        ));
    }
}
