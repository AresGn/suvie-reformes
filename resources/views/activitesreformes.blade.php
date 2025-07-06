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
    

    <!-- Tableau des activités réformes -->
    <div class="data-table-area mg-b-15">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="sparkline13-list">
                        <div class="sparkline13-hd">
                            <div class="main-sparkline13-hd">
                                <h1>Liste <span class="table-project-n">des </span>Activités</h1>
                                <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">
                                    + Ajouter une activité
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
                                            <th data-field="reforme">Réforme</th>
                                            <th data-field="libelle" data-editable="true">Libellé</th>
                                            <th data-field="date_debut" data-editable="true">Date Début</th>
                                            <th data-field="date_fin_prevue" data-editable="true">Date Fin Prévue</th>
                                            <th data-field="date_fin" data-editable="true">Date Fin</th>
                                            <th data-field="poids" data-editable="true">Poids (%)</th>
                                            <th data-field="statut">Statut</th>
                                            <th data-field="structure_responsable" data-editable="true">Structure Responsable</th>
                                            <th data-field="action">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activites as $activite)
                                        <tr>
                                            <td></td>
                                            <td>{{ $activite->id }}</td>
                                            <td>{{ $activite->reforme->titre }}</td>
                                            <td>{{ $activite->libelle }}</td>
                                            <td>{{ $activite->date_debut }}</td>
                                            <td>{{ $activite->date_fin_prevue }}</td>
                                            <td>{{ $activite->date_fin }}</td>
                                            <td>{{ $activite->poids }}</td>
                                            <td>
                                                <span class="label label-{{ $activite->statut == 'A' ? 'success' : ($activite->statut == 'P' ? 'warning' : 'danger') }}" 
                                                      data-id="{{ $activite->id }}" 
                                                      title="Cliquer pour voir les détails">
                                                    {{ $activite->statut_label }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $structureInfo = $structures->firstWhere('id', $activite->structure_responsable);
                                                @endphp
                                                {{ $structureInfo ? $structureInfo->lib_court . ' - ' . $structureInfo->lib_long : 'Non définie' }}
                                            </td>
                                            <td>
                                                <!-- Voir -->
                                                <button class="btn btn-info btn-xs" data-toggle="modal" data-target="#viewModal{{ $activite->id }}">
                                                    <i class="fa fa-eye"></i>
                                                </button>

                                                <!-- Modifier -->
                                                <button class="btn btn-warning btn-xs" data-toggle="modal" data-target="#editModal{{ $activite->id }}">
                                                    <i class="fa fa-edit"></i>
                                                </button>

                                                <!-- Supprimer -->
                                                <form action="{{ route('activites.destroy', $activite->id) }}" method="POST" style="display:inline-block;">
                                                    @csrf @method('DELETE')
                                                    <button style="display: inline-block;" class="btn btn-danger btn-xs" onclick="return confirm('Supprimer cette activité ?')">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                        <!-- Modal de visualisation des détails -->
                                        <div class="modal fade" id="viewModal{{ $activite->id }}" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel{{ $activite->id }}" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="viewModalLabel{{ $activite->id }}">Détails de l'activité</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><strong>ID:</strong> {{ $activite->id }}</p>
                                                        <p><strong>Réforme:</strong> {{ $activite->reforme->titre }}</p>
                                                        <p><strong>Libellé:</strong> {{ $activite->libelle }}</p>
                                                        <p><strong>Date Début:</strong> {{ $activite->date_debut }}</p>
                                                        <p><strong>Date Fin Prévue:</strong> {{ $activite->date_fin_prevue }}</p>
                                                        <p><strong>Date Fin:</strong> {{ $activite->date_fin ?? 'Non définie' }}</p>
                                                        <p><strong>Poids:</strong> {{ $activite->poids }}%</p>
                                                        <p><strong>Statut:</strong>
                                                            <span class="label label-{{ $activite->statut == 'A' ? 'success' : ($activite->statut == 'P' ? 'warning' : 'danger') }}">
                                                                {{ $activite->statut_label }}
                                                            </span>
                                                        </p>
                                                        <p><strong>Structure Responsable:</strong> 
                                                            @php
                                                                $structureInfo = $structures->firstWhere('id', $activite->structure_responsable);
                                                            @endphp
                                                            {{ $structureInfo ? $structureInfo->lib_court . ' - ' . $structureInfo->lib_long : 'Non définie' }}
                                                        </p>
                                                        
                                                        @if($activite->parent === null)
                                                            <p><strong>Progression des sous-activités:</strong> 
                                                                @php
                                                                    $totalPoids = $activite->sousActivites->sum('poids');
                                                                @endphp
                                                                {{ $totalPoids }}%
                                                            </p>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                                        @if($activite->parent === null)
                                                            <a href="{{ route('activites.sous-activites.index', $activite->id) }}" class="btn btn-primary">
                                                                <i class="fa fa-list"></i> Voir les sous-activités
                                                            </a>
                                                            <button type="button" class="btn btn-success" onclick="openSousActiviteModal({{ $activite->id }}, '{{ addslashes($activite->libelle) }}', '{{ addslashes($activite->reforme->titre ?? '') }}')">
                                                                <i class="fa fa-plus"></i> Ajouter une sous-activité
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal de modification -->
                                        <div class="modal fade" id="editModal{{ $activite->id }}" tabindex="-1" role="dialog">
                                            <div class="modal-dialog modal-dialog modal-lg" role="document">
                                                <form action="{{ route('activites.update', $activite->id) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                        <ul id="myTabedu1" class="tab-review-design">
                                                            <li class="active"><a href="#description">Modifier Activite Reforme</a></li>
                                                        </ul>
                                                    </div>
                                                        <div class="modal-body">
                                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                            <div class="mb-3">
                                                                <label>Réforme</label>
                                                                <select name="reforme_id" class="form-control" required>
                                                                    @foreach($reformes ?? [] as $reforme)
                                                                        <option value="{{ $reforme->id }}" {{ $activite->reforme_id == $reforme->id ? 'selected' : '' }}>
                                                                            {{ $reforme->titre }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Libellé</label>
                                                                <input type="text" class="form-control" name="libelle" value="{{ $activite->libelle }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Date Début</label>
                                                                <input type="date" class="form-control" name="date_debut" value="{{ $activite->date_debut }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Date Fin Prévue</label>
                                                                <input type="date" class="form-control" name="date_fin_prevue" value="{{ $activite->date_fin_prevue }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                            <div class="mb-3">
                                                                <label>Date Fin</label>
                                                                <input type="date" class="form-control" name="date_fin" value="{{ $activite->date_fin }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Poids (%)</label>
                                                                <input type="number" class="form-control" name="poids" value="{{ $activite->poids }}" min="1" max="100" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Statut actuel</label>
                                                                <div class="form-control-static">
                                                                    {!! $activite->status_badge !!}
                                                                    <br><small class="text-muted">Géré automatiquement par le système</small>
                                                                </div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Structure Responsable</label>
                                                                <select name="structure_responsable" class="form-control" required>
                                                                    <option value="">Sélectionner une structure</option>
                                                                    @foreach($structures as $structure)
                                                                        <option value="{{ $structure->id }}" {{ $activite->structure_responsable == $structure->id ? 'selected' : '' }}>
                                                                            {{ $structure->lib_court }} - {{ $structure->lib_long }}
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
        <form action="{{ route('activites.store') }}" method="POST" id="add-activite" class="add-activite">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <ul id="myTabedu1" class="tab-review-design">
                        <li class="active"><a href="#description">Ajouter activité</a></li>
                    </ul>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="mb-3">
                                <label>Réforme</label>
                                <select name="reforme_id" class="form-control" required>
                                    <option value="">Sélectionner une réforme</option>
                                    @if(isset($reformes) && $reformes->count() > 0)
                                        @foreach($reformes as $reforme)
                                            <option value="{{ $reforme->id }}">{{ $reforme->titre }}</option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>Aucune réforme disponible</option>
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Libellé</label>
                                <input type="text" name="libelle" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Date de début</label>
                                <input type="date" name="date_debut" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="mb-3">
                                <label>Date de fin prévue</label>
                                <input type="date" name="date_fin_prevue" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Poids (%)</label>
                                <input type="number" name="poids" class="form-control" min="1" max="100" required>
                            </div>
                            <div class="mb-3">
                                <label>Structure Responsable</label>
                                <select name="structure_responsable" class="form-control" required>
                                    <option value="">Sélectionner une structure</option>
                                    @if(isset($structures) && $structures->count() > 0)
                                        @foreach($structures as $structure)
                                            <option value="{{ $structure->id }}">
                                                {{ $structure->lib_court }} - {{ $structure->lib_long }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>Aucune structure disponible</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Enregistrer</button>
                    <button class="btn btn-default" type="button" data-dismiss="modal">Annuler</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal d'ajout de sous-activité -->
<div class="modal fade" id="addSousActiviteModal" tabindex="-1" role="dialog" aria-labelledby="addSousActiviteModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <form id="add-sous-activite-form" class="add-sous-activite">
            @csrf
            <input type="hidden" id="activite_parent_id" name="activite_parent_id">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <ul id="myTabedu2" class="tab-review-design">
                        <li class="active"><a href="#description">Ajouter sous-activité</a></li>
                    </ul>
                </div>
                <div class="modal-body">
                    <!-- Informations sur l'activité parent -->
                    <div class="alert alert-info" id="parent-activity-info" style="display: none;">
                        <h4><i class="fa fa-info-circle"></i> Activité Principale</h4>
                        <p><strong>Libellé:</strong> <span id="parent-libelle"></span></p>
                        <p><strong>Réforme:</strong> <span id="parent-reforme"></span></p>
                        <p><strong>Poids restant disponible:</strong> <span id="poids-restant"></span>%</p>
                    </div>

                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="mb-3">
                                <label>Libellé <span class="text-danger">*</span></label>
                                <input type="text" name="libelle" id="sous_activite_libelle" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Date de début <span class="text-danger">*</span></label>
                                <input type="date" name="date_debut" id="sous_activite_date_debut" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Date de fin prévue <span class="text-danger">*</span></label>
                                <input type="date" name="date_fin_prevue" id="sous_activite_date_fin_prevue" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="mb-3">
                                <label>Poids (%) <span class="text-danger">*</span></label>
                                <input type="number" name="poids" id="sous_activite_poids" class="form-control" min="1" max="100" required>
                                <small class="text-muted">Poids disponible: <span id="poids-disponible-text">0</span>%</small>
                            </div>
                            <div class="mb-3">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i>
                                    <strong>Statut automatique :</strong> La nouvelle sous-activité sera créée avec le statut "À commencer"
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Structure Responsable <span class="text-danger">*</span></label>
                                <select name="structure_responsable" id="sous_activite_structure" class="form-control" required>
                                    <option value="">Sélectionner une structure</option>
                                    @if(isset($structures) && $structures->count() > 0)
                                        @foreach($structures as $structure)
                                            <option value="{{ $structure->id }}">
                                                {{ $structure->lib_court }} - {{ $structure->lib_long }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>Aucune structure disponible</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit" id="btn-save-sous-activite">
                        <i class="fa fa-save"></i> Enregistrer
                    </button>
                    <button class="btn btn-default" type="button" data-dismiss="modal">Annuler</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Débogage temporaire - à supprimer après correction -->
@if(isset($structures))
    <div class="alert alert-info" style="display: none;">
        <p>Nombre de structures: {{ $structures->count() }}</p>
        <ul>
            @foreach($structures as $structure)
                <li>ID: {{ $structure->id }}, Libellé court: {{ $structure->lib_court }}</li>
            @endforeach
        </ul>
    </div>
@else
    <div class="alert alert-danger" style="display: none;">
        <p>Variable $structures non définie</p>
    </div>
@endif

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Validation du formulaire d'ajout d'activité
    $("#add-activite").validate({
        rules: {
            reforme_id: {
                required: true
            },
            libelle: {
                required: true,
                maxlength: 255
            },
            date_debut: {
                required: true
            },
            date_fin_prevue: {
                required: true
            },
            poids: {
                required: true,
                min: 1,
                max: 100
            },
            structure_responsable: {
                required: true
            }
        },
        messages: {
            reforme_id: {
                required: "Veuillez sélectionner une réforme"
            },
            libelle: {
                required: "Veuillez entrer un libellé",
                maxlength: "Le libellé ne doit pas dépasser 255 caractères"
            },
            date_debut: {
                required: "Veuillez entrer une date de début"
            },
            date_fin_prevue: {
                required: "Veuillez entrer une date de fin prévue"
            },
            poids: {
                required: "Veuillez entrer un poids",
                min: "Le poids minimum est de 1%",
                max: "Le poids maximum est de 100%"
            },
            structure_responsable: {
                required: "Veuillez sélectionner une structure responsable"
            }
        },
        submitHandler: function(form) {
            // Désactiver le bouton de soumission pour éviter les soumissions multiples
            $(form).find('button[type="submit"]').prop('disabled', true);
            form.submit();
        }
    });
});
</script>
@endsection

@push('scripts')
<script>
// Fonction pour ouvrir le modal de sous-activité
function openSousActiviteModal(activiteId, activiteLibelle, reformeTitre) {
    // Remplir les informations de l'activité parent
    $('#activite_parent_id').val(activiteId);
    $('#parent-libelle').text(activiteLibelle);
    $('#parent-reforme').text(reformeTitre);

    // Calculer le poids restant disponible via AJAX
    $.ajax({
        url: "{{ url('api/activites') }}/" + activiteId + "/poids-restant",
        type: 'GET',
        success: function(response) {
            var poidsRestant = response.poids_restant || 100;
            $('#poids-restant').text(poidsRestant);
            $('#poids-disponible-text').text(poidsRestant);
            $('#sous_activite_poids').attr('max', poidsRestant);
        },
        error: function() {
            // Valeur par défaut en cas d'erreur
            $('#poids-restant').text('100');
            $('#poids-disponible-text').text('100');
            $('#sous_activite_poids').attr('max', 100);
        }
    });

    // Afficher les informations de l'activité parent
    $('#parent-activity-info').show();

    // Réinitialiser le formulaire
    $('#add-sous-activite-form')[0].reset();
    $('#activite_parent_id').val(activiteId); // Remettre l'ID après reset

    // Ouvrir le modal
    $('#addSousActiviteModal').modal('show');
}

// Gestion de la soumission du formulaire de sous-activité
$(document).ready(function() {
    // Validation en temps réel du poids
    $('#sous_activite_poids').on('input', function() {
        var poids = parseInt($(this).val());
        var maxPoids = parseInt($(this).attr('max'));

        if (poids > maxPoids) {
            $(this).closest('.form-group').addClass('has-error');
            $(this).next('.text-muted').addClass('text-danger').text('Poids supérieur au disponible (' + maxPoids + '%)');
        } else {
            $(this).closest('.form-group').removeClass('has-error');
            $(this).next('.text-muted').removeClass('text-danger').text('Poids disponible: ' + maxPoids + '%');
        }
    });

    // Validation des dates
    $('#sous_activite_date_debut, #sous_activite_date_fin_prevue').on('change', function() {
        var dateDebut = $('#sous_activite_date_debut').val();
        var dateFinPrevue = $('#sous_activite_date_fin_prevue').val();

        if (dateDebut && dateFinPrevue && dateDebut > dateFinPrevue) {
            $('#sous_activite_date_fin_prevue').closest('.form-group').addClass('has-error');
            alert('La date de fin prévue doit être postérieure à la date de début');
        } else {
            $('#sous_activite_date_fin_prevue').closest('.form-group').removeClass('has-error');
        }
    });

    $('#add-sous-activite-form').on('submit', function(e) {
        e.preventDefault();

        // Validation finale avant soumission
        var poids = parseInt($('#sous_activite_poids').val());
        var maxPoids = parseInt($('#sous_activite_poids').attr('max'));

        if (poids > maxPoids) {
            alert('Le poids saisi (' + poids + '%) dépasse le poids disponible (' + maxPoids + '%)');
            return false;
        }

        var formData = $(this).serialize();
        var activiteId = $('#activite_parent_id').val();

        // Désactiver le bouton de soumission
        $('#btn-save-sous-activite').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enregistrement...');

        $.ajax({
            url: "{{ url('activites') }}/" + activiteId + "/sous-activites",
            type: 'POST',
            data: formData,
            success: function(response) {
                // Fermer le modal
                $('#addSousActiviteModal').modal('hide');

                // Afficher un message de succès
                if (typeof toastr !== 'undefined') {
                    toastr.success('Sous-activité créée avec succès!');
                } else {
                    alert('Sous-activité créée avec succès!');
                }

                // Recharger la page pour afficher la nouvelle sous-activité
                setTimeout(function() {
                    location.reload();
                }, 1000);
            },
            error: function(xhr) {
                var errors = xhr.responseJSON?.errors || {};
                var errorMessage = 'Erreur lors de la création de la sous-activité.';

                if (Object.keys(errors).length > 0) {
                    errorMessage = Object.values(errors).flat().join('\n');
                }

                if (typeof toastr !== 'undefined') {
                    toastr.error(errorMessage);
                } else {
                    alert(errorMessage);
                }
            },
            complete: function() {
                // Réactiver le bouton
                $('#btn-save-sous-activite').prop('disabled', false).html('<i class="fa fa-save"></i> Enregistrer');
            }
        });
    });
});

// Fonction de compatibilité (à supprimer plus tard)
function redirectToSousActivites(activiteId) {
    // Rediriger vers la page des sous-activités
    window.location.href = "{{ url('activites') }}/" + activiteId + "/sous-activites";
}
</script>
@endpush




