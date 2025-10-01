<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'getLogout']);
    }

    public function getLogin()
    {
        return view('user.login');
    }

    public function postAuthenticate(Request $request)
    {
        $v = Validator::make($request->all(), ['username' => 'required', 'password' => 'required']);

        if (!$v->passes()) {
            return Response::json(['errors' => $v->errors()]);
        } else {
            $credentials = [
                'email'    => $request->get('username'),
                'password' => $request->get('password'),
            ];

            if (\Sentinel::forceAuthenticateAndRemember($credentials)) {
                return redirect()->action('HomeController@getIndex');
            } else {
                return Response::json(['errors' => 'Wrond Credentials']);
            }
        }
    }

    public function getLogout()
    {
        \Sentinel::logout(null, true);
        return redirect('auth/login');
    }
}
