<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()
                ->route('login')
                ->with('status', 'Không thể đăng nhập bằng Google. Vui lòng thử lại.');
        }

        if (! $googleUser->getEmail()) {
            return redirect()
                ->route('login')
                ->with('status', 'Tài khoản Google chưa cung cấp email.');
        }

        $user = User::query()
            ->where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            $user->forceFill([
                'google_id' => $user->google_id ?: $googleUser->getId(),
                'provider' => 'google',
                'avatar' => $googleUser->getAvatar(),
                'email_verified_at' => $user->email_verified_at ?: now(),
            ])->save();
        } else {
            $user = User::query()->create([
                'name' => $googleUser->getName()
                    ?: Str::before($googleUser->getEmail(), '@'),
                'email' => $googleUser->getEmail(),
                'email_verified_at' => now(),
                'google_id' => $googleUser->getId(),
                'provider' => 'google',
                'avatar' => $googleUser->getAvatar(),
                'password' => Hash::make(Str::random(40)),
                'role' => 'user',
            ]);
        }

        Auth::login($user);

        request()->session()->regenerate();

        return redirect()->intended(route('home'));
    }
}
