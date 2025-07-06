<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Login;
use App\Models\User;
use App\Models\Personne;
use App\Models\Role;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $personne = Personne::where('email', $request->email)->first();

        if ($personne && $personne->user && Hash::check($request->password, $personne->user->pwd)) {
            Auth::login($personne->user , $request->filled('remember'));

            // Déclencher manuellement l'événement Login pour assurer la création de session
            event(new Login('web', $personne->user, $request->filled('remember')));

            return redirect()->route('dashboard');
        }

        return back()->withErrors(['email' => 'Identifiants invalides.']);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }






}

