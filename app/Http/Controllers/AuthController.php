<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AuthenticateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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

        if (\Sentinel::forceAuthenticateAndRemember($credentials)) {
            return redirect()->route('home');
        } else {
            return response()->json(['errors' => 'Wrond Credentials']);
        }
    }

    public function getLogout(): RedirectResponse
    {
        \Sentinel::logout(null, true);

        return redirect('auth/login');
    }
}
