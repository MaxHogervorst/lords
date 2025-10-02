<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AuthenticateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'getLogout']);
    }

    public function getLogin(): View
    {
        return view('user.login');
    }

    public function postAuthenticate(AuthenticateRequest $request): JsonResponse|RedirectResponse
    {
        $credentials = [
            'email' => $request->get('username'),
            'password' => $request->get('password'),
        ];

        if (Auth::attempt($credentials, remember: true)) {
            $request->session()->regenerate();

            return redirect()->route('home');
        }

        return response()->json(['errors' => 'Wrong Credentials']);
    }

    public function getLogout(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('auth/login');
    }
}
