@extends('layout.app')

@section('content')



<div class="data-table-area mg-b-15">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="sparkline13-list">
                    <div class="sparkline13-hd">
                        <div class="main-sparkline13-hd">
                            <h1>Listes <span class="table-project-n">des</span> Type de Réformes</h1>
                            <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">
                            + Ajouter
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
                                        <th data-field="name" data-editable="true">Libellé</th>
                                        <th data-field="action">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($typereformes as $item)
                                    <tr>
                                        <td></td>
                                        <td>{{ $item->id }}</td>
                                        <td>{{ $item->lib }}</td>
                                        <td>
                                            <!-- Bouton "Voir" -->
                                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#viewModal{{ $item->id }}">
                                                <i class="fa fa-eye"></i>
                                            </button>

                                            <!-- Bouton Modifier -->
                                            <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editModal{{ $item->id }}"><i class="fa fa-edit"></i></button>

                                            <!-- Formulaire de suppression -->
                                            <form action="{{ route('typereforme.destroy', $item->id) }}" method="POST" style="display:inline-block;">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <!-- Modal de modification -->
                                    <div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel{{ $item->id }}" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <form action="{{ route('typereforme.update', $item->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editModalLabel{{ $item->id }}">Modifier le type</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="lib">Libellé</label>
                                                            <input type="text" class="form-control" name="lib" value="{{ $item->lib }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <!-- Modal de visualisation -->
                                    <div class="modal fade" id="viewModal{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel{{ $item->id }}" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">

                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="viewModalLabel{{ $item->id }}">Détails du Type de Réforme</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>

                                                <div class="modal-body">
                                                    <p><strong>Libellé :</strong> {{ $item->lib }}</p>
                                                    <p><strong>ID :</strong> {{ $item->id }}</p>
                                                    <!-- Ajoute d'autres détails ici si nécessaire -->
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
                                                </div>

                                            </div>
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


<!-- Modal d’ajout -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel">
    <div class="modal-dialog" role="document">
        <form action="{{ route('typereforme.store') }}" method="POST" id="add-department" class="add-department">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="addModalLabel">Ajouter un type réforme</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="lib">Libellé</label>
                        <input type="text" class="form-control" name="lib" required>
                        @error('lib') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                </div>
            </div>
        </form>
    </div>
</div>


@endsection
