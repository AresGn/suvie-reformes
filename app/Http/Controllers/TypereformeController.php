<?php

namespace App\Http\Controllers;

use App\Models\Typereforme;
use Illuminate\Http\Request;

class TypereformeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    // Affichage de tous les types (et interface de gestion complète)
    public function index()
    {
        $typereformes = Typereforme::orderBy('created_at', 'asc')->paginate(10);
        return view('typereforme', compact('typereformes'));
    }

    // Enregistrement (depuis le modal "ajout")
    public function store(Request $request)
    {
        $request->validate([
            'lib' => 'required|string|max:255'
        ]);

        Typereforme::create([
            'lib' => $request->lib
        ]);

        return redirect()->route('typereforme.index')->with('success', 'Type de réforme ajouté.');
    }

    // Mise à jour (depuis le modal "édition")
    public function update(Request $request, $id)
    {
        $request->validate([
            'lib' => 'required|string|max:255'
        ]);

        $typereforme = Typereforme::findOrFail($id);
        $typereforme->update([
            'lib' => $request->lib
        ]);

        return redirect()->route('typereforme.index')->with('success', 'Type de réforme mis à jour.');
    }

    // Suppression (depuis le bouton du tableau)
    public function destroy($id)
    {
        $typereforme = Typereforme::findOrFail($id);
        $typereforme->delete();

        return redirect()->route('typereforme.index')->with('success', 'Type de réforme supprimé.');
    }

    public function show($id)
    {
        $typereforme = Typereforme::findOrFail($id);
        return view('typereforme.show', compact('typereforme'));
    }

}
