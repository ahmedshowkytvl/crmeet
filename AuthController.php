<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $loginField = $request->input('email');
        $password = $request->password;
        
        // Try to authenticate with different field types
        $user = null;
        
        // 1. Try email
        if (filter_var($loginField, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $loginField)->first();
        }
        
        // 2. Try username
        if (!$user) {
            $user = User::where('username', $loginField)->first();
        }
        
        // 3. Try Employee ID (EmployeeCode)
        if (!$user && is_numeric($loginField)) {
            $user = User::where('EmployeeCode', $loginField)
                       ->orWhere('employee_id', $loginField)
                       ->first();
        }
        
        // Check password and authenticate
        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            
        // Log successful login
        // audit_login($user, true); // Temporarily disabled
            
            // Redirect based on user role
            if ($user->role && $user->role->slug === 'admin') {
                return redirect()->intended(route('dashboard'));
            } elseif ($user->role && $user->role->slug === 'manager') {
                return redirect()->intended(route('manager.dashboard'));
            } else {
                return redirect()->intended(route('dashboard'));
            }
        } else {
        // Log failed login attempt
        // Temporarily disabled
        /*
        if ($user) {
            audit_login($user, false);
        } else {
            audit_log(
                actionType: 'login',
                module: 'authentication',
                details: [
                    'login_attempt' => true,
                    'success' => false,
                    'login_field' => $loginField,
                    'timestamp' => now()->toISOString(),
                ],
                status: 'failed'
            );
        }
        */
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        // Log logout before clearing session
        // Temporarily disabled
        /*
        if (Auth::check()) {
            audit_logout(Auth::user());
        }
        */
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Get default employee role
        $employeeRole = \App\Models\Role::where('slug', 'employee')->first();
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $employeeRole ? $employeeRole->id : 1, // Default to first role if employee not found
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}