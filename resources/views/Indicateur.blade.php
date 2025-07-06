@extends('layout.app')

@section('content')
<div class="container-fluid">

<!--table des indicateurs-->

        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="sparkline13-list">
                        <div class="sparkline13-hd">
                            <div class="main-sparkline13-hd">
                                <h1>liste <span class="table-project-n">des </span>Indicateurs</h1>
                                <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">
                                    + Ajouter un indicateur
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
                                            <th data-field="libelle" data-editable="true">libelle</th>
                                            <th data-field="unite" data-editable="true">unite</th>
                                            <th data-field="action">Actions</th>
                                            
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($indicateurs as $indicateur)
                                        <tr>
                                            <td></td>
                                            <td>{{ $indicateur->id }}</td>
                                            <td>{{ $indicateur->libelle }}</td>
                                            <td>{{ $indicateur->unite }}</td>
                                            
                                            <td>
                                                <!-- Voir -->
                                                <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#viewModal{{ $indicateur->id }}">
                                                    <i class="fa fa-eye"></i>
                                                </button>

                                                <!-- Modifier -->
                                                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal{{ $indicateur->id }}">
                                                    <i class="fa fa-edit"></i>
                                                </button>

                                                <!-- Supprimer -->
                                                <form action="{{ route('indicateurs.destroy', $indicateur->id) }}" method="POST" style="display:inline-block;">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cet indicateur ?')">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                        <!-- Modal de visualisation -->
                                        <div class="modal fade" id="viewModal{{ $indicateur->id }}" tabindex="-1" role="dialog" aria-labelledby="viewModal{{ $indicateur->id }}" aria-hidden="true">
                                            <div class="modal-dialog " role="document">
                                                <div class="modal-content">
                                                <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                        <ul id="myTabedu1" class="tab-review-design">
                                                            <li class="active"><a href="#description">Détail de l'indicateur</a></li>
                                                        </ul>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><strong>ID:</strong> {{ $indicateur->id }}</p>
                                                        <p><strong>Libelle:</strong> {{ $indicateur->libelle }}</p>
                                                        <p><strong>Unité:</strong> {{ $indicateur->unite }}</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button class="btn btn-default" data-dismiss="modal">Fermer</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal de modification -->
                                        <div class="modal fade" id="editModal{{ $indicateur->id }}" tabindex="-1" role="dialog">
                                            <div class="modal-dialog " role="document">
                                                <form action="{{ route('indicateurs.update', $indicateur->id) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                        <ul id="myTabedu1" class="tab-review-design">
                                                            <li class="active"><a href="#description">Modifier indicateur</a></li>
                                                        </ul>
                                                    </div>
                                                        <div class="modal-body">
                                                        
                                                            <div class="mb-3">
                                                                <label>Libellé</label>
                                                                <input type="text" class="form-control" name="libelle" value="{{ $indicateur->libelle }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Unité</label>
                                                                <textarea class="form-control" name="unite">{{ $indicateur->unite }}</textarea>
                                                            </div>
                                                            
                                                        
                                                                
                                                        <div class="modal-footer">
                                                            <button class="btn btn-primary" type="submit">Mettre à jour</button>
                                                            <button class="btn btn-default" type="button" data-dismiss="modal">Annuler</button>
                                                        </div>
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

<!-- Modal d’ajout -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel">
    <div class="modal-dialog" role="document">
        <form action="{{ route('indicateurs.store') }}" method="POST" id="add-department" class="add-department">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="addModalLabel">Ajouter indicateur</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="lib">Libellé</label>
                        <input type="text" class="form-control" name="libelle" required>
                        @error('lib') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label for="lib">Unité</label>
                        <input type="text" class="form-control" name="unite" required>
                        @error('unite') <div class="text-danger">{{ $message }}</div> @enderror
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

</div>

@endsection
