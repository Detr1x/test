<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {

        // Validate the input
        $request->validate([
            'uname' => 'required|string',
            'password' => 'required|string',
        ]);

        // Attempt to authenticate the user
        if (
            Auth::attempt([
                'uname' => $request->uname,
                'password' => $request->password
            ])
        ) {
            if (Auth::user()->role === "admin") {
                return redirect('/admin'); // Прямая переадресация
            }
            return redirect('/');
        }

        return back()->withErrors([
            'uname' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}
