@extends('layout.app')

@section('content')


<div class="container pt-4">
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Bouton Ajouter -->
    

    <!-- Tableau des réformes -->
    <div class="data-table-area mg-b-15">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="sparkline13-list">
                        <div class="sparkline13-hd">
                            <div class="main-sparkline13-hd">
                                <h1>liste <span class="table-project-n">des </span>Utilisateur</h1>
                                <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">
                                    + Ajouter un utilisateur
                                </button>
                            </div>
                        </div>
                        <div class="sparkline13-graph">
                            <div class="datatable-dashv1-list custom-datatable-overright">
                                <div id="toolbar">
                                    <select class="form-control dt-tb">
                                        <option value="">Export Basic</option>
                                        <option value="all">Export All</option>
                                        <option value="selected">Export Selected</option>
                                    </select>
                                </div>
                                <table id="table" data-toggle="table" data-pagination="true" data-search="true" data-show-columns="true" data-show-pagination-switch="true" data-show-refresh="true" data-key-events="true" data-show-toggle="true" data-resizable="true" data-cookie="true"
                                    data-cookie-id-table="saveId" data-show-export="true" data-click-to-select="true" data-toolbar="#toolbar">
                                    <thead>
                                        <tr>
                                            <th data-field="state" data-checkbox="true"></th>
                                            <th data-field="id">ID</th>
                                            <th data-field="nom" data-editable="true">nom</th>
                                            <th data-field="prenom" data-editable="true">prénom</th>
                                            <th data-field="fonction" data-editable="true">fonction</th>
                                            <th data-field="tel" data-editable="true">tel</th>
                                            <th data-field="email" data-editable="true">email</th>
                                            <th data-field="rolename" data-editable="true">role name</th>
                                            <th data-field="action">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($users as $user)
                                        <tr>
                                            <td></td>
                                            <td>{{ $user->id }}</td>
                                            <td>{{ $user->personne->nom  }}</td>
                                            <td>{{ $user->personne->prenom  }}</td>
                                            <td>{{ $user->personne->fonction  }}</td>
                                            <td>{{ $user->personne->tel }}</td>
                                            <td>{{ $user->personne->email }}</td>
                                            <td>{{ optional($user->roles->first())->role_name ?? 'Aucun rôle' }}</td>
                                            <td>
                                                <!-- Voir -->
                                                <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#viewModal{{ $user->id }}">
                                                    <i class="fa fa-eye"></i>
                                                </button>

                                                <!-- Modifier -->
                                                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal{{ $user->id }}">
                                                    <i class="fa fa-edit"></i>
                                                </button>

                                                <!-- Supprimer -->
                                                <form action="{{ route('utilisateurs.destroy', $user->id) }}" method="POST" style="display:inline-block;">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cet utilisateur ?')">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                        <!-- Modal de visualisation -->
                                        <div class="modal fade" id="viewModal{{ $user->id }}" tabindex="-1" role="dialog" aria-labelledby="viewModal{{ $user->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                        <ul id="myTabedu1" class="tab-review-design">
                                                            <li class="active"><a href="#description">Détail de l'utilisateur</a></li>
                                                        </ul>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><strong>ID:</strong> {{ $user->id }}</p>
                                                        <p><strong>Nom:</strong> {{ $user->personne->nom   }}</p>
                                                        <p><strong>Prénom:</strong> {{ $user->personne->prenom   }}</p>
                                                        <p><strong>Fonction:</strong> {{ $user->personne->fonction  }}</p>
                                                        <p><strong>Tel:</strong> {{ $user->personne->tel }}</p>
                                                        <p><strong>Email:</strong> {{ $user->personne->email  }}</p>
                                                        <p><strong>Nom du role:</strong> {{ optional($user->roles->first())->role_name ?? 'Aucun rôle' }}</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button class="btn btn-default" data-dismiss="modal">Fermer</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal de modification -->
                                        <div class="modal fade" id="editModal{{ $user->id }}" tabindex="-1" role="dialog">
                                            <div class="modal-dialog modal-dialog modal-lg" role="document">
                                                <form action="{{ route('utilisateurs.update', $user->id) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                        <ul id="myTabedu1" class="tab-review-design">
                                                            <li class="active"><a href="#description">Modifier utilisateur</a></li>
                                                        </ul>
                                                    </div>
                                                        <div class="modal-body">
                                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                            <div class="mb-3">
                                                                <label>Nom</label>
                                                                <input type="text" class="form-control" name="nom" value="{{ $user->personne->nom ?? '' }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Prénom</label>
                                                                <input type="text" class="form-control" name="prenom" value="{{ $user->personne->prenom ?? '' }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Fonction</label>
                                                                <input type="text" class="form-control" name="fonction" value="{{ $user->personne->fonction ?? '' }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Tel</label>
                                                                <input type="text" class="form-control" name="tel" value="{{ $user->personne->tel ?? '' }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                            <div class="mb-3">
                                                                <label>Email</label>
                                                                <input type="email" name="email" class="form-control" value="{{ $user->personne->email ?? '' }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Rôle</label>
                                                                <select name="role_id" class="form-control" required>
                                                                    @foreach($roles as $role)
                                                                        @if($role)
                                                                        <option value="{{ $role->id }}"
                                                                            {{ ($user->roles && $user->roles->contains('id', $role->id)) ? 'selected' : '' }}>
                                                                            {{ $role->role_name ?? 'Rôle inconnu' }}
                                                                        </option>
                                                                        @endif
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Mot de passe (laisser vide pour ne pas changer)</label>
                                                                <input type="password" name="pwd" class="form-control" placeholder="Nouveau mot de passe">
                                                            </div>
                                                        </div>
                                                        </div>
    
                                                        <div class="modal-footer">
                                                            <button class="btn btn-primary" type="submit">Mettre à jour</button>
                                                            <button class="btn btn-default" type="button" data-dismiss="modal">Annuler</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        @endforeach
                                    </tbody>
                                </table>

                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>




<!-- Modal d’ajout -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('utilisateurs.store') }}" method="POST" id="add-department" class="add-department">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <ul id="myTabedu1" class="tab-review-design">
                        <li class="active"><a href="#description">Ajouter utilisateur</a></li>
                    </ul>
                </div>
                <div class="modal-body">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <div class="mb-3">
                        <label>Nom</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Prénom</label>
                        <input type="text" name="prenom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Fonction</label>
                        <input type="text" name="fonction" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Tel</label>
                        <input type="text" name="tel" class="form-control" required>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Rôle</label>
                        <select name="role_id" class="form-control" required>
                            <option value="">Sélectionner un rôle</option>
                            @foreach($roles as $role)
                                @if($role)
                                <option value="{{ $role->id }}">{{ $role->role_name ?? 'Rôle inconnu' }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Mot de passe</label>
                        <input type="password" name="pwd" class="form-control" required>
                    </div>
                    </div>
                <div class="modal-footer ">
                    <button class="btn btn-primary" type="submit">Enregistrer</button>
                    <button class="btn btn-default" type="button" data-dismiss="modal">Annuler</button>
                </div>
            </div>
            </div>
        </form>
    </div>
</div>



@endsection

