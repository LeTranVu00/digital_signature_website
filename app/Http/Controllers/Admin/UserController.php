<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'in:admin,user'],
            'status' => ['nullable', 'in:active,blocked'],
        ]);

        $users = User::query()
            ->withCount([
                'comments' => function ($query) {
                    $query->withTrashed();
                },
            ])
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['role'] ?? null, function ($query, string $role) {
                $query->where('role', $role);
            })
            ->when($filters['status'] ?? null, function ($query, string $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('filters', 'users'));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(['admin', 'user'])],
        ]);

        if ($request->user()->is($user) && $validated['role'] === 'user') {
            abort(403, 'Admin khong the tu ha quyen chinh minh.');
        }

        $user->update([
            'role' => $validated['role'],
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Cap nhat quyen nguoi dung thanh cong.');
    }

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['active', 'blocked'])],
        ]);

        if ($request->user()->is($user) && $validated['status'] === 'blocked') {
            abort(403, 'Admin khong the tu khoa tai khoan chinh minh.');
        }

        $user->update([
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Cap nhat trang thai nguoi dung thanh cong.');
    }
}
