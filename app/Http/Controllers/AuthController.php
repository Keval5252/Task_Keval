<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginIndex()
    {
        return view('login');
    }

    public function registerIndex()
    {
        return view('register');
    }

    public function CustomerRegisterIndex()
    {
        return view('customer-register');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8',
        ]);
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            if (!Auth::user()->hasVerifiedEmail()) {
                Auth::logout();
                toastr()->error('Please verify your email before logging in.');
                return redirect()->route('login');
            }

            if (Auth::user()->role === 'admin') {
                return redirect('/');
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            toastr()->error('You are not allowed to login from here');
            return redirect()->route('login');
        }
        toastr()->error('Invalid email or password');
        return redirect()->route('login');
    }

    public function register(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->password = \Hash::make($request->password);
        $user->role = 'admin';
        $user->save();

        $user->sendEmailVerificationNotification();
        toastr()->success('Registration successful! Please verify your email.');

        return redirect()->route('login');
    }


    public function CustomerRegister(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->password = \Hash::make($request->password);
        $user->role = 'customer';
        $user->save();

        $user->sendEmailVerificationNotification();
        toastr()->success('Registration successful! Please verify your email.');

        return redirect()->route('login');
    }

    public function logout()
    {
        Auth::logout();
        toastr()->success('Logout successful!');
        return redirect('login');
    }
}
