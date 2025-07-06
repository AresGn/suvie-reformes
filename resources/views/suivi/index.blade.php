@extends('layout.app')

@section('content')
<div class="data-table-area mg-b-15">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="sparkline13-list">
                    <div class="sparkline13-hd">
                        <div class="main-sparkline13-hd">
                            <h1>Suivi des <span class="table-project-n">Sous-Activités</span></h1>
                            <button type="button" class="btn btn-primary mb-3" id="btnAjouterSuivi">
                                <i class="fa fa-plus"></i> Ajouter un suivi
                            </button>
                        </div>
                    </div>
                    
                    <!-- Filtres -->
                    <div class="row mg-b-15">
                        <div class="col-md-12">
                            <form action="{{ route('suivi-activites.index') }}" method="GET" class="form-inline">
                                <div class="form-group mg-r-10">
                                    <label class="mg-r-10">Réforme:</label>
                                    <select name="reforme_id" class="form-control">
                                        <option value="">Toutes les réformes</option>
                                        @foreach($reformes as $reforme)
                                            <option value="{{ $reforme->id }}" {{ request('reforme_id') == $reforme->id ? 'selected' : '' }}>
                                                {{ $reforme->titre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mg-r-10">
                                    <label class="mg-r-10">Statut:</label>
                                    <select name="statut" class="form-control">
                                        <option value="">Tous les statuts</option>
                                        <option value="C" {{ request('statut') == 'C' ? 'selected' : '' }}>Créé</option>
                                        <option value="P" {{ request('statut') == 'P' ? 'selected' : '' }}>En cours</option>
                                        <option value="A" {{ request('statut') == 'A' ? 'selected' : '' }}>Achevé</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Filtrer</button>
                                <a href="{{ route('suivi-activites.index') }}" class="btn btn-default">Réinitialiser</a>
                            </form>
                        </div>
                    </div>
                    
                    <div class="sparkline13-graph">
                        <div class="datatable-dashv1-list custom-datatable-overright">
                            <table id="table" data-toggle="table" data-pagination="true" data-search="true" data-show-columns="true" data-show-pagination-switch="true" data-show-refresh="true" data-key-events="true" data-show-toggle="true" data-resizable="true" data-cookie="true" data-cookie-id-table="saveId" data-show-export="true" data-click-to-select="true" data-toolbar="#toolbar">
                                <thead>
                                    <tr>
                                        <th data-field="id">ID</th>
                                        <th data-field="reforme">Réforme</th>
                                        <th data-field="activite_parent">Activité principale</th>
                                        <th data-field="sous_activite">Sous-activité</th>
                                        <th data-field="date_debut">Date début</th>
                                        <th data-field="date_fin_prevue">Date fin prévue</th>
                                        <th data-field="statut">Statut</th>
                                        <th data-field="avancement">Avancement</th>
                                        <th data-field="dernier_suivi">Dernier suivi</th>
                                        <th data-field="action" data-width="180">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sousActivites as $activite)
                                    <tr>
                                        <td>{{ $activite->id }}</td>
                                        <td>{{ $activite->reforme->titre }}</td>
                                        <td>{{ $activite->parentActivite->libelle }}</td>
                                        <td>{{ $activite->libelle }}</td>
                                        <td>{{ $activite->date_debut }}</td>
                                        <td>{{ $activite->date_fin_prevue }}</td>
                                        <td>
                                            <span class="label label-{{ $activite->statut == 'A' ? 'success' : ($activite->statut == 'P' ? 'warning' : 'danger') }}">
                                                {{ $activite->statut_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-{{ $activite->statut == 'A' ? 'success' : ($activite->statut == 'P' ? 'warning' : 'danger') }}" 
                                                     role="progressbar" 
                                                     aria-valuenow="{{ $activite->avancement }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100" 
                                                     style="width: {{ $activite->avancement }}%;">
                                                    {{ $activite->avancement }}%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($activite->dernierSuivi())
                                                {{ $activite->dernierSuivi()->suivi_date }}
                                                <a href="{{ route('suivi-activites.historique', $activite->id) }}" class="btn btn-xs btn-info" title="Voir l'historique">
                                                    <i class="fa fa-history"></i>
                                                </a>
                                            @else
                                                <span class="text-muted">Aucun suivi</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($activite->statut != 'A')
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-success btn-xs valider-activite" data-id="{{ $activite->id }}" title="Marquer comme terminée">
                                                        <i class="fa fa-check"></i> Terminer
                                                    </button>
                                                    <button type="button" class="btn btn-warning btn-xs ajouter-suivi"
                                                        data-id="{{ $activite->id }}" 
                                                        data-libelle="{{ $activite->libelle }}"
                                                        data-reforme="{{ $activite->reforme->titre }}"
                                                        data-parent="{{ $activite->parentActivite->libelle }}"
                                                        title="Ajouter un suivi">
                                                        <i class="fa fa-pencil"></i> Suivi
                                                    </button>
                                                </div>
                                            @else
                                                <span class="text-success"><i class="fa fa-check-circle"></i> Terminée</span>
                                            @endif
                                        </td>
                                    </tr>
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

<!-- Modal pour ajouter un suivi -->
<div class="modal fade" id="modalAjouterSuivi" tabindex="-1" role="dialog" aria-labelledby="modalAjouterSuiviLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalAjouterSuiviLabel">Ajouter un suivi</h4>
            </div>
            <div class="modal-body">
                <form id="formAjouterSuivi">
                    <input type="hidden" id="activite_id" name="activite_id">
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <p><strong>Réforme:</strong> <span id="reforme_titre"></span></p>
                                <p><strong>Activité principale:</strong> <span id="activite_parent"></span></p>
                                <p><strong>Sous-activité:</strong> <span id="activite_libelle"></span></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="suivi_date">Date du suivi</label>
                                <input type="date" class="form-control" id="suivi_date" name="suivi_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Terminer l'activité?</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="terminer" value="0" checked>
                                        Non, l'activité est toujours en cours
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="terminer" value="1">
                                        Oui, marquer comme terminée
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="actions_fait">Actions réalisées</label>
                                <textarea class="form-control" id="actions_fait" name="actions_fait" rows="4" required></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="actions_a_fait">Actions restantes à faire</label>
                                <textarea class="form-control" id="actions_a_fait" name="actions_a_fait" rows="4" required></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="difficultes">Difficultés rencontrées</label>
                                <textarea class="form-control" id="difficultes" name="difficultes" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solutions">Solutions proposées</label>
                                <textarea class="form-control" id="solutions" name="solutions" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="observations">Observations</label>
                        <textarea class="form-control" id="observations" name="observations" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="btnEnregistrerSuivi">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<!-- Ajout d'une modal pour sélectionner l'activité -->
<div class="modal fade" id="modalSelectActivite" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Sélectionner une activité</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="select_activite">Activité</label>
                    <select id="select_activite" class="form-control">
                        <option value="">Sélectionner une activité</option>
                        @foreach($sousActivites as $act)
                            @if($act->statut != 'A')
                                <option value="{{ $act->id }}" 
                                        data-libelle="{{ $act->libelle }}"
                                        data-reforme="{{ $act->reforme->titre }}"
                                        data-parent="{{ $act->parentActivite->libelle }}">
                                    {{ $act->reforme->titre }} - {{ $act->parentActivite->libelle }} - {{ $act->libelle }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="btnSelectActivite">Continuer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Diagnostic des boutons au chargement de la page
    console.log('=== DIAGNOSTIC BOUTONS SUIVI ===');
    console.log('Boutons "Terminer" trouvés:', $('.valider-activite').length);
    console.log('Boutons "Suivi" trouvés:', $('.ajouter-suivi').length);
    console.log('Modal #modalAjouterSuivi trouvé:', $('#modalAjouterSuivi').length);
    console.log('Formulaire #formAjouterSuivi trouvé:', $('#formAjouterSuivi').length);
    console.log('Bouton #btnEnregistrerSuivi trouvé:', $('#btnEnregistrerSuivi').length);
    console.log('jQuery version:', $.fn.jquery);
    console.log('Bootstrap modal disponible:', typeof $.fn.modal !== 'undefined');

    // Test de fonctionnement des boutons
    $('.valider-activite').each(function(index) {
        console.log('Bouton Terminer ' + index + ' - ID activité:', $(this).data('id'));
    });

    $('.ajouter-suivi').each(function(index) {
        console.log('Bouton Suivi ' + index + ' - ID activité:', $(this).data('id'));
    });

    // Test de connectivité des routes
    console.log('=== TEST DES ROUTES ===');
    console.log('Route store:', "{{ route('suivi-activites.store') }}");
    console.log('Route valider (template):', "{{ route('suivi-activites.valider', ':id') }}");

    // Test simple de la route store
    $.ajax({
        url: "{{ route('suivi-activites.store') }}",
        type: 'HEAD',
        success: function() {
            console.log('✅ Route store accessible');
        },
        error: function(xhr) {
            console.log('❌ Route store inaccessible:', xhr.status);
        }
    });

    // Test que les scripts personnalisés fonctionnent
    console.log('=== VÉRIFICATION SCRIPTS PERSONNALISÉS ===');
    console.log('✅ Ce script se charge via section scripts - système fonctionnel !');

    // Test simple des boutons après un délai
    setTimeout(function() {
        console.log('=== TEST BOUTONS APRÈS CHARGEMENT ===');
        var terminerBtns = $('.valider-activite');
        var suiviBtns = $('.ajouter-suivi');

        if (terminerBtns.length > 0) {
            console.log('✅ Boutons "Terminer" détectés:', terminerBtns.length);
        } else {
            console.warn('⚠️ Aucun bouton "Terminer" trouvé');
        }

        if (suiviBtns.length > 0) {
            console.log('✅ Boutons "Suivi" détectés:', suiviBtns.length);
        } else {
            console.warn('⚠️ Aucun bouton "Suivi" trouvé');
        }
    }, 500);
    // Bouton global pour ajouter un suivi (ouvre une boîte de dialogue pour sélectionner l'activité)
    $('#btnAjouterSuivi').click(function() {
        // Afficher une boîte de dialogue pour sélectionner l'activité
        $('#modalSelectActivite').modal('show');
    });
    
    // Ouvrir la modal pour ajouter un suivi (depuis le tableau) - Utilisation de la délégation d'événements
    $(document).on('click', '.ajouter-suivi', function(e) {
        e.preventDefault();
        console.log('=== CLIC BOUTON SUIVI ===');

        var button = $(this);
        var id = button.data('id');
        var libelle = button.data('libelle');
        var reforme = button.data('reforme');
        var parent = button.data('parent');

        console.log('ID activité:', id);
        console.log('Libellé:', libelle);
        console.log('Réforme:', reforme);
        console.log('Parent:', parent);

        if (!id) {
            console.error('ID activité manquant!');
            alert('Erreur: ID activité manquant');
            return;
        }

        // Remplir les champs du modal
        $('#activite_id').val(id);
        $('#activite_libelle').text(libelle || 'Non défini');
        $('#reforme_titre').text(reforme || 'Non définie');
        $('#activite_parent').text(parent || 'Non défini');

        console.log('Tentative d\'ouverture du modal...');

        // Vérifier que le modal existe
        var modal = $('#modalAjouterSuivi');
        if (modal.length === 0) {
            console.error('Modal #modalAjouterSuivi non trouvé!');
            alert('Erreur: Modal non trouvé');
            return;
        }

        // Ouvrir le modal avec fallback
        try {
            modal.modal('show');
            console.log('Modal ouvert avec succès');
        } catch (error) {
            console.error('Erreur lors de l\'ouverture du modal:', error);
            // Fallback : afficher le modal manuellement
            modal.addClass('in').show();
            $('body').addClass('modal-open');
            $('.modal-backdrop').remove();
            $('<div class="modal-backdrop fade in"></div>').appendTo('body');
        }
    });
    
    // Valider une activité (marquer comme terminée) - Utilisation de la délégation d'événements
    $(document).on('click', '.valider-activite', function(e) {
        e.preventDefault();
        console.log('=== CLIC BOUTON TERMINER ===');

        var button = $(this);
        var id = button.data('id');

        console.log('ID activité:', id);
        console.log('Bouton:', button);

        if (!id) {
            console.error('ID activité manquant!');
            alert('Erreur: ID activité manquant');
            return;
        }

        if (confirm('Êtes-vous sûr de vouloir marquer cette activité comme terminée?')) {
            console.log('Confirmation reçue, début de la requête AJAX...');
            // Désactiver le bouton pendant la requête
            button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Traitement...');

            var ajaxUrl = "{{ route('suivi-activites.valider', ':id') }}".replace(':id', id);
            console.log('URL AJAX:', ajaxUrl);
            console.log('Token CSRF:', "{{ csrf_token() }}");

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function() {
                    console.log('Envoi de la requête AJAX...');
                },
                success: function(response) {
                    console.log('Réponse AJAX reçue:', response);

                    if (response && response.success) {
                        console.log('Succès:', response.message);

                        // Afficher le message de succès avec informations de cascade
                        var message = response.message;
                        if (response.cascade_info) {
                            if (response.cascade_info.parent_validated) {
                                console.log('✅ Activité parent validée automatiquement');
                            }
                            if (response.cascade_info.reform_validated) {
                                console.log('✅ Réforme validée automatiquement');
                            }
                            if (response.cascade_info.cascade_errors && response.cascade_info.cascade_errors.length > 0) {
                                console.warn('⚠️ Erreurs cascade:', response.cascade_info.cascade_errors);
                            }
                        }

                        if (typeof toastr !== 'undefined') {
                            toastr.success(message);

                            // Afficher des notifications supplémentaires pour la cascade
                            if (response.cascade_info && response.cascade_info.parent_validated) {
                                setTimeout(function() {
                                    toastr.info('Activité parent automatiquement terminée');
                                }, 1000);
                            }
                            if (response.cascade_info && response.cascade_info.reform_validated) {
                                setTimeout(function() {
                                    toastr.info('Réforme automatiquement terminée');
                                }, 1500);
                            }
                        } else {
                            alert(message);
                        }

                        // Recharger après un délai plus long pour voir les notifications
                        setTimeout(function() {
                            location.reload();
                        }, 2500);
                    } else {
                        console.error('Erreur dans la réponse:', response);

                        var errorMsg = response && response.message ? response.message : 'Erreur inconnue';

                        if (typeof toastr !== 'undefined') {
                            toastr.error('Erreur: ' + errorMsg);
                        } else {
                            alert('Erreur: ' + errorMsg);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erreur AJAX:', xhr.responseText);
                    var errorMessage = 'Erreur lors de la validation de l\'activité';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 404) {
                        errorMessage = 'Activité non trouvée';
                    } else if (xhr.status === 403) {
                        errorMessage = 'Vous n\'avez pas les permissions nécessaires';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Erreur serveur interne';
                    }

                    if (typeof toastr !== 'undefined') {
                        toastr.error(errorMessage);
                    } else {
                        alert(errorMessage);
                    }
                },
                complete: function() {
                    // Réactiver le bouton
                    button.prop('disabled', false).html('<i class="fa fa-check"></i> Terminer');
                }
            });
        }
    });
    
    // Enregistrer un nouveau suivi
    $('#btnEnregistrerSuivi').click(function() {
        var button = $(this);
        var form = $('#formAjouterSuivi');

        // Validation côté client
        var activiteId = $('#activite_id').val();
        var suiviDate = $('#suivi_date').val();
        var actionsFait = $('#actions_fait').val();
        var actionsAFait = $('#actions_a_fait').val();

        if (!activiteId || !suiviDate || !actionsFait || !actionsAFait) {
            if (typeof toastr !== 'undefined') {
                toastr.error('Veuillez remplir tous les champs obligatoires');
            } else {
                alert('Veuillez remplir tous les champs obligatoires');
            }
            return;
        }

        // Désactiver le bouton pendant la requête
        button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enregistrement...');

        var formData = form.serialize();
        formData += '&_token={{ csrf_token() }}';

        $.ajax({
            url: "{{ route('suivi-activites.store') }}",
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Afficher le message de succès avec informations de cascade
                    var message = response.message;
                    if (response.cascade_info) {
                        if (response.cascade_info.parent_validated) {
                            console.log('✅ Activité parent validée automatiquement');
                        }
                        if (response.cascade_info.reform_validated) {
                            console.log('✅ Réforme validée automatiquement');
                        }
                        if (response.cascade_info.cascade_errors && response.cascade_info.cascade_errors.length > 0) {
                            console.warn('⚠️ Erreurs cascade:', response.cascade_info.cascade_errors);
                        }
                    }

                    if (typeof toastr !== 'undefined') {
                        toastr.success(message);

                        // Afficher des notifications supplémentaires pour la cascade
                        if (response.cascade_info && response.cascade_info.parent_validated) {
                            setTimeout(function() {
                                toastr.info('Activité parent automatiquement terminée');
                            }, 1000);
                        }
                        if (response.cascade_info && response.cascade_info.reform_validated) {
                            setTimeout(function() {
                                toastr.info('Réforme automatiquement terminée');
                            }, 1500);
                        }
                    } else {
                        alert(message);
                    }

                    $('#modalAjouterSuivi').modal('hide');

                    // Réinitialiser le formulaire
                    form[0].reset();

                    // Recharger la page après un délai plus long pour voir les notifications
                    setTimeout(function() {
                        location.reload();
                    }, 2500);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Erreur: ' + response.message);
                    } else {
                        alert('Erreur: ' + response.message);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur AJAX:', xhr.responseText);
                var errorMessage = 'Erreur lors de l\'enregistrement du suivi';

                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    // Erreurs de validation Laravel
                    var errors = xhr.responseJSON.errors;
                    var errorList = [];
                    for (var field in errors) {
                        errorList = errorList.concat(errors[field]);
                    }
                    errorMessage = errorList.join('\n');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 422) {
                    errorMessage = 'Données invalides. Veuillez vérifier les champs.';
                } else if (xhr.status === 404) {
                    errorMessage = 'Activité non trouvée';
                } else if (xhr.status === 403) {
                    errorMessage = 'Vous n\'avez pas les permissions nécessaires';
                } else if (xhr.status === 500) {
                    errorMessage = 'Erreur serveur interne';
                }

                if (typeof toastr !== 'undefined') {
                    toastr.error(errorMessage);
                } else {
                    alert(errorMessage);
                }
            },
            complete: function() {
                // Réactiver le bouton
                button.prop('disabled', false).html('Enregistrer');
            }
        });
    });
});
</script>

<!-- Script pour gérer la sélection d'activité -->
<script>
$(document).ready(function() {
    $('#btnSelectActivite').click(function() {
        var select = $('#select_activite');
        var id = select.val();
        
        if (id) {
            var option = select.find('option:selected');
            var libelle = option.data('libelle');
            var reforme = option.data('reforme');
            var parent = option.data('parent');
            
            $('#activite_id').val(id);
            $('#activite_libelle').text(libelle);
            $('#reforme_titre').text(reforme);
            $('#activite_parent').text(parent);
            
            $('#modalSelectActivite').modal('hide');
            $('#modalAjouterSuivi').modal('show');
        } else {
            alert('Veuillez sélectionner une activité');
        }
    });
});
</script>

<!-- Ajout de styles CSS pour standardiser l'apparence -->
<style>
    .progress {
        margin-bottom: 0;
        height: 20px;
    }
    
    .progress-bar {
        line-height: 20px;
        font-size: 12px;
    }
    
    .label {
        display: inline;
        padding: .2em .6em .3em;
        font-size: 75%;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: .25em;
    }
    
    .btn-group {
        display: flex;
    }
    
    .btn-group .btn {
        margin-right: 2px;
    }
</style>
@endsection






