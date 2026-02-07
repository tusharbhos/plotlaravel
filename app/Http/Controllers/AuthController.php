<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Show Login Page
     */
    public function loginPage()
    {
        if (Session::has('user_id')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Show Registration Page
     */
    public function registerPage()
    {
        if (Session::has('user_id')) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    /**
     * Handle Login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->checkPassword($request->password)) {
            return redirect()->back()
                ->with('error', 'Invalid email or password.')
                ->withInput(['email' => $request->email]);
        }

        if (!$user->is_active) {
            return redirect()->back()
                ->with('error', 'Your account has been deactivated.');
        }

        // Set session
        Session::put('user_id', $user->id);
        Session::put('user_name', $user->name);
        Session::put('user_email', $user->email);
        Session::put('user_role', $user->role);

        // Update last login
        $user->update(['last_login_at' => now()]);

        return redirect()->route('dashboard')
            ->with('success', 'Welcome back, ' . $user->name . '!');
    }

    /**
     * Handle Registration
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|min:2|max:100',
            'phone'     => 'required|string|min:7|max:15|unique:users',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:6|max:100',
            're_password' => 'required|same:password',
        ], [
            'name.required'        => 'Name is required.',
            'phone.required'       => 'Phone number is required.',
            'phone.unique'         => 'This phone number is already registered.',
            'email.required'       => 'Email address is required.',
            'email.email'          => 'Please enter a valid email.',
            'email.unique'         => 'This email is already registered.',
            'password.required'    => 'Password is required.',
            'password.min'         => 'Password must be at least 6 characters.',
            're_password.required' => 'Please confirm your password.',
            're_password.same'     => 'Passwords do not match.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create user
        $user = User::create([
            'name'     => $request->name,
            'phone'    => $request->phone,
            'email'    => $request->email,
            'password' => $request->password, // Mutator will hash it
            'role'     => 'user',
            'is_active' => true,
        ]);

        // Auto-login after register
        Session::put('user_id', $user->id);
        Session::put('user_name', $user->name);
        Session::put('user_email', $user->email);
        Session::put('user_role', $user->role);
        $user->update(['last_login_at' => now()]);

        return redirect()->route('dashboard')
            ->with('success', 'Registration successful! Welcome, ' . $user->name . '!');
    }

    /**
     * Logout
     */
    public function logout()
    {
        Session::flush();
        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully.');
    }
}