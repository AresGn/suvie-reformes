<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Personne;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('personne', 'roles')->get();
        $roles = Role::all();
        return view('user', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'fonction' => 'required|string|max:255',
            'tel' => 'required|string|regex:/^\+?[0-9 ]+$/|max:50|unique:personne,tel',
            'email' => 'required|string|email|max:255|unique:personne,email',
            'pwd' => 'required|string|min:6|max:255',
            'role_id' => 'required|exists:role,id',
        ]);

        try {
            DB::beginTransaction();

            $personne = Personne::create([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'fonction' => $request->fonction,
                'tel' => $request->tel,
                'email' => $request->email,
            ]);

            $user = User::create([
                'personne_id' => $personne->id,
                'pwd' => Hash::make($request->pwd),
                'status' => 1,
            ]);

            $user->roles()->attach($request->role_id);

            DB::commit();

            return redirect()->route('utilisateurs.index')->with('success', 'Utilisateur créé avec succès.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erreur lors de la création de l\'utilisateur: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Erreur lors de la création de l\'utilisateur: ' . $e->getMessage()])->withInput();
        }
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'fonction' => 'required|string|max:255',
            'tel' => [
                'required',
                'string',
                'regex:/^\+?[0-9 ]+$/',
                'max:50',
                Rule::unique('personne', 'tel')->ignore(optional($user->personne)->id),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('personne', 'email')->ignore(optional($user->personne)->id),
            ],
            'pwd' => 'nullable|string|min:6|max:255',
            'role_id' => 'required|exists:role,id',
        ]);
    
        DB::beginTransaction();
    
        try {
            // Met à jour ou crée la personne liée
            if ($user->personne) {
                $user->personne->update([
                    'nom' => $request->nom,
                    'prenom' => $request->prenom,
                    'fonction' => $request->fonction,
                    'tel' => $request->tel,
                    'email' => $request->email,
                ]);
            } else {
                $personne = new Personne([
                    'nom' => $request->nom,
                    'prenom' => $request->prenom,
                    'fonction' => $request->fonction,
                    'tel' => $request->tel,
                    'email' => $request->email,
                ]);
                $personne->save();
    
                // Lier la personne au user
                $user->personne_id = $personne->id;
                $user->save();
            }
    
            // Met à jour le mot de passe si fourni
            if ($request->filled('pwd')) {
                $user->update(['pwd' => Hash::make($request->pwd)]);
            }
    
            // Synchronise les rôles
            $user->roles()->sync([$request->role_id]);
    
            DB::commit();
    
            return redirect()->route('utilisateurs.index')->with('success', 'Utilisateur mis à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de l\'utilisateur: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Erreur lors de la mise à jour de l\'utilisateur.'])->withInput();
        }
    }
    
    public function destroy($id)
    {
        $user = User::with('personne')->findOrFail($id);

        $user->roles()->detach();

        if ($user->personne) {
            $user->personne->delete();
        }

        $user->delete();

        return redirect()->route('utilisateurs.index')->with('success', 'Utilisateur supprimé.');
    }

    public function create()
    {
        $roles = Role::all();
        return view('user.create', compact('roles'));
    }

    public function edit($id)
    {
        $user = User::with(['personne', 'roles'])->findOrFail($id);
        $roles = Role::all();
        return view('user.edit', compact('user', 'roles'));
    }

    public function show($id)
    {
        $user = User::with('personne', 'roles')->findOrFail($id);
        return view('user.show', compact('user'));
    }
}
