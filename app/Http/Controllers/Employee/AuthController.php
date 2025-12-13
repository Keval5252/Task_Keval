<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginIndex()
    {
        return view('employee.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            toastr()->error('Invalid email or password');
            return redirect()->route('employee.login')->withInput($request->only('email'));
        }

        /** @var \App\Models\User $user */
        $user = $request->user();

        if (!$user || $user->role !== 'employee') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            toastr()->error('You are not allowed to login from here');
            return redirect()->route('employee.login');
        }

        if (!$user->hasVerifiedEmail()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            toastr()->error('Please verify your email before logging in.');
            return redirect()->route('employee.login');
        }

        $request->session()->regenerate();

        return redirect()->route('employee.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        toastr()->success('Logout successful!');
        return redirect()->route('employee.login');
    }
}
