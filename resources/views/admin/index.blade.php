@extends('admin.admin')

@section('title', $user->exists ? 'Modifier un utilisateur' : 'Créer un utilisateur')

@section('content')

<h1>@yield('title')</h1>
<form class="vstack gap-2" action="{{ route($user->exists ? 'user.update' : 'user.store', $user) }}" method="post">
    @csrf
    @method($user->exists ? 'put' : 'post')

    <div>
        <label for="personne_id">Personne :</label>
        <select name="personne_id" class="form-select" required>
            @foreach($personnes as $personne)
                <option value="{{ $personne->id }}" {{ $user->personne_id == $personne->id ? 'selected' : '' }}>
                    {{ $personne->nom }} {{ $personne->prenom }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="pwd">Mot de passe :</label>
        <input type="password" name="pwd" class="form-control" {{ $user->exists ? '' : 'required' }}>
    </div>

    <div>
        <label for="role_id">Rôle :</label>
        <select name="role_id" class="form-select" required>
            @foreach($roles as $role)
                <option value="{{ $role->id }}"
                    @if($user->roles->contains('id', $role->id)) selected @endif>
                    {{ $role->role_name }}
                </option>
            @endforeach
        </select>
    </div>

    <button class="btn btn-primary">
        {{ $user->exists ? 'Mettre à jour' : 'Créer' }}
    </button>
</form>

@endsection
