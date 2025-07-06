@extends('layout.app')

@section('content')


<div class="container pt-4">
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
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
                                <h1>liste <span class="table-project-n">des </span>Reforme</h1>
                                <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">
                                    + Ajouter une réforme
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
                                            <th data-field="titre" data-editable="true">titre</th>
                                            <th data-field="objectifs" data-editable="true">objectifs</th>
                                            <th data-field="budget" data-editable="true">budget</th>
                                            <th data-field="date_debut" data-editable="true">date_debut</th>
                                            <th data-field="date_fin_prevue" data-editable="true">date fin prevue</th>
                                            <th data-field="date_fin" data-editable="true">date fin</th>
                                            <th data-field="statut" data-editable="true">statut</th>
                                            <th data-field="typereforme" data-editable="true">type reforme</th>
                                            <th data-field="action">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($reformes as $reforme)
                                        <tr>
                                            <td></td>
                                            <td>{{ $reforme->id }}</td>
                                            <td>{{ $reforme->titre }}</td>
                                            <td>{{ $reforme->objectifs }}</td>
                                            <td>{{ $reforme->budget }}</td>
                                            <td>{{ $reforme->date_debut }}</td>
                                            <td>{{ $reforme->date_fin_prevue }}</td>
                                            <td>{{ $reforme->date_fin }}</td>
                                            <td>
                                                {!! $reforme->status_badge !!}
                                            </td>e
                                            <td>{{ $reforme->type->lib ?? 'Non défini' }}</td>
                                             <td class="text-center">
                                                <div class="btn-group" role="group" aria-label="Actions">
                                                    <!-- Voir -->
                                                    <button class="btn btn-info btn-sm mr-1" data-toggle="modal" data-target="#viewModal{{ $reforme->id }}">
                                                        <i class="fa fa-eye"></i>
                                                    </button>

                                                    <!-- Modifier -->
                                                    <button class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="#editModal{{ $reforme->id }}">
                                                        <i class="fa fa-edit"></i>
                                                    </button>

                                                    <!-- Supprimer -->
                                                    <form action="{{ route('reforme.destroy', $reforme->id) }}" method="POST" style="display:inline-block;">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cette réforme ?')">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <!-- Modal de visualisation -->
                                        <div class="modal fade" id="viewModal{{ $reforme->id }}" tabindex="-1" role="dialog" aria-labelledby="viewModal{{ $reforme->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                        <ul id="myTabedu1" class="tab-review-design">
                                                            <li class="active"><a href="#description">Détail de la reforme</a></li>
                                                        </ul>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><strong>ID:</strong> {{ $reforme->id }}</p>
                                                        <p><strong>Titre:</strong> {{ $reforme->titre }}</p>
                                                        <p><strong>Objectifs:</strong> {{ $reforme->objectifs }}</p>
                                                        <p><strong>Budget:</strong> {{ $reforme->budget }}</p>
                                                        <p><strong>Date début:</strong> {{ $reforme->date_debut }}</p>
                                                        <p><strong>Date fin prevue:</strong> {{ $reforme->date_fin_prevue }}</p>
                                                        <p><strong>Date fin:</strong> {{ $reforme->date_fin }}</p>
                                                        <p><strong>Statut:</strong>
                                                            <span class="badge badge-{{ $reforme->statut == 'Terminé' ? 'success' : ($reforme->statut == 'En cours' ? 'warning' : 'secondary') }}">
                                                                {{ $reforme->statut }}
                                                            </span>
                                                        </p>
                                                        <p><strong>Type:</strong> {{ $reforme->type->lib ?? 'Non défini' }}</p>
                                                        <p><strong>Pièces justificatives:</strong> {{ $reforme->pieces_justificatifs ?? 'Aucune' }}</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button class="btn btn-default" data-dismiss="modal">Fermer</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal de modification -->
                                        <div class="modal fade" id="editModal{{ $reforme->id }}" tabindex="-1" role="dialog">
                                            <div class="modal-dialog modal-dialog modal-lg" role="document">
                                                <form action="{{ route('reforme.update', $reforme->id) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                        <ul id="myTabedu1" class="tab-review-design">
                                                            <li class="active"><a href="#description">Modifier reforme</a></li>
                                                        </ul>
                                                    </div>
                                                        <div class="modal-body">
                                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                            <div class="mb-3">
                                                                <label>Titre</label>
                                                                <input type="text" class="form-control" name="titre" value="{{ $reforme->titre }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Objectifs</label>
                                                                <textarea class="form-control" name="objectifs">{{ $reforme->objectifs }}</textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Budget</label>
                                                                <input type="number" class="form-control" name="budget" value="{{ $reforme->budget }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Date Début</label>
                                                                <input type="date" class="form-control" name="date_debut" value="{{ $reforme->date_debut }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                            <div class="mb-3">
                                                                <label>Date de Fin Prevue</label>
                                                                <input type="date" name="date_fin_prevue" class="form-control" value="{{ $reforme->date_fin_prevue }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Date Fin</label>
                                                                <input type="date" class="form-control" name="date_fin" value="{{ $reforme->date_fin }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Pièce Justificatif</label>
                                                                <input type="text" name="pieces_justificatifs" class="form-control" value="{{ $reforme->pieces_justificatifs }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Type de réforme</label>
                                                                <select name="type_reforme" class="form-control">
                                                                
                                                                    @foreach($typereformes ?? [] as $type)
                                                                    <option value="{{ $type->id }}" {{ $reforme->type_reforme == $type->id ? 'selected' : '' }}>
                                                                        {{ $type->lib }}
                                                                    </option>
                                                                    @endforeach
                                                                </select>
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
        <form action="{{ route('reforme.store') }}" method="POST" id="add-department" class="add-department">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <ul id="myTabedu1" class="tab-review-design">
                        <li class="active"><a href="#description">Ajouter reforme</a></li>
                    </ul>
                </div>
                <div class="modal-body">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="mb-3">
                            <label>Titre</label>
                            <input type="text" name="titre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Objectifs</label>
                            <input type="text" name="objectifs" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Budget</label>
                            <input type="number" name="budget" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Date Début</label>
                            <input type="date" name="date_debut" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Structures concernées</label>
                            <select name="structures[]" class="form-control" multiple>
                                @foreach($structures as $structure)
                                    <option value="{{ $structure->id }}">{{ $structure->lib_long ?? $structure->lib_court ?? $structure->id }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="mb-3">
                            <label>Date de Fin Prevue</label>
                            <input type="date" name="date_fin_prevue" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Pièce Justificatif</label>
                            <input type="text" name="pieces_justificatifs" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Type de réforme</label>
                            <select name="type_reforme" class="form-control">
                                @foreach($typereformes as $type)
                                <option value="{{ $type->id }}">{{ $type->lib }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Indicateurs associés</label>
                            <select name="indicateurs[]" class="form-control" multiple>
                                @foreach($indicateurs as $indicateur)
                                    <option value="{{ $indicateur->id }}">{{ $indicateur->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
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
