<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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

    public function postAuthenticate(Request $request): JsonResponse|RedirectResponse
    {
        $v = Validator::make($request->all(), ['username' => 'required', 'password' => 'required']);

        if (! $v->passes()) {
            return response()->json(['errors' => $v->errors()]);
        } else {
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
    }

    public function getLogout(): RedirectResponse
    {
        \Sentinel::logout(null, true);

        return redirect('auth/login');
    }
}
