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

    <!-- Tableau des sous-activités -->
    <div class="data-table-area mg-b-15">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="sparkline13-list">
                        <div class="sparkline13-hd">
                            <div class="main-sparkline13-hd">
                                <h1>Sous-activités de <span class="table-project-n">{{ $activitePrincipale->libelle }}</span></h1>
                                <div class="btn-group">
                                    <a href="{{ route('activites.index') }}" class="btn btn-default">
                                        <i class="fa fa-arrow-left"></i> Retour aux activités
                                    </a>
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addSousActiviteModal">
                                        <i class="fa fa-plus"></i> Ajouter une sous-activité
                                    </button>
                                </div>
                            </div>
                        </div>
                    
                    <!-- Informations sur l'activité principale -->
                    <div class="alert alert-info">
                        <h4><i class="fa fa-info-circle"></i> Activité Principale</h4>
                        <p><strong>Libellé:</strong> {{ $activitePrincipale->libelle }}</p>
                        <p><strong>Réforme:</strong> {{ $activitePrincipale->reforme->titre ?? 'Non définie' }}</p>
                        <p><strong>Progression totale:</strong> {{ $statistiques['poids_total'] ?? 0 }}% ({{ $statistiques['total'] ?? 0 }} sous-activités)</p>
                    </div>
                    
                    <div class="sparkline13-graph">
                        <div class="datatable-dashv1-list custom-datatable-overright">
                            <div id="toolbar">
                                <select class="form-control dt-tb">
                                    <option value="">Exporter les données de base</option>
                                    <option value="all">Exporter toutes les données</option>
                                    <option value="selected">Exporter les données sélectionnées</option>
                                </select>
                            </div>
                            <table id="table" data-toggle="table" data-pagination="true" data-search="true" data-show-columns="true" data-show-pagination-switch="true" data-show-refresh="true" data-key-events="true" data-show-toggle="true" data-resizable="true" data-cookie="true" data-cookie-id-table="saveId" data-show-export="true" data-click-to-select="true" data-toolbar="#toolbar">
                                <thead>
                                    <tr>
                                        <th data-field="state" data-checkbox="true"></th>
                                        <th data-field="id">ID</th>
                                        <th data-field="libelle">Libellé</th>
                                        <th data-field="dates">Dates</th>
                                        <th data-field="poids">Poids (%)</th>
                                        <th data-field="statut">Statut</th>
                                        <th data-field="structure">Structure</th>
                                        <th data-field="actions">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sousActivites as $sousActivite)
                                    <tr>
                                        <td></td>
                                        <td>{{ $sousActivite->id }}</td>
                                        <td>
                                            <strong>{{ $sousActivite->libelle }}</strong>
                                            <br><small class="text-muted">Créée le {{ $sousActivite->created_at->format('d/m/Y') }}</small>
                                        </td>
                                        <td>
                                            <small>
                                                <strong>Début:</strong> {{ \Carbon\Carbon::parse($sousActivite->date_debut)->format('d/m/Y') }}<br>
                                                <strong>Fin prévue:</strong> {{ \Carbon\Carbon::parse($sousActivite->date_fin_prevue)->format('d/m/Y') }}<br>
                                                @if($sousActivite->date_fin)
                                                    <strong>Fin réelle:</strong> {{ \Carbon\Carbon::parse($sousActivite->date_fin)->format('d/m/Y') }}
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <span class="label label-info">{{ $sousActivite->poids }}%</span>
                                        </td>
                                        <td>
                                            {!! $sousActivite->status_badge !!}
                                        </td>
                                        <td>
                                            @php
                                                $structure = $structures->firstWhere('id', $sousActivite->structure_responsable);
                                            @endphp
                                            {{ $structure ? $structure->lib_court : 'Non définie' }}
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('activites.sous-activites.edit', [$activitePrincipale->id, $sousActivite->id]) }}" 
                                                   class="btn btn-warning btn-xs" title="Modifier">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger btn-xs" 
                                                        onclick="confirmDelete({{ $sousActivite->id }})" title="Supprimer">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div class="alert alert-info">
                                                <i class="fa fa-info-circle"></i> Aucune sous-activité trouvée pour cette activité.
                                                <br><br>
                                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addSousActiviteModal">
                                                    <i class="fa fa-plus"></i> Ajouter la première sous-activité
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
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

<!-- Modal d'ajout de sous-activité -->
<div class="modal fade" id="addSousActiviteModal" tabindex="-1" role="dialog" aria-labelledby="addSousActiviteModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('activites.sous-activites.store', $activitePrincipale->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="addSousActiviteModalLabel">
                        <i class="fa fa-plus"></i> Ajouter une sous-activité
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="libelle">Libellé <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="libelle" name="libelle" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="poids">Poids (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="poids" name="poids" min="1" max="{{ $poidsRestant ?? 100 }}" required>
                                <small class="text-muted">Poids restant disponible: {{ $poidsRestant ?? 100 }}%</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_debut">Date de début <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="date_debut" name="date_debut" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_fin_prevue">Date de fin prévue <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="date_fin_prevue" name="date_fin_prevue" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_fin">Date de fin réelle</label>
                                <input type="date" class="form-control" id="date_fin" name="date_fin">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="poids">Poids (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="poids" name="poids" min="1" max="100" required>
                                <small class="text-muted">Le statut sera automatiquement défini à "À commencer"</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="poids">Poids (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="poids" name="poids" min="1" max="100" required>
                                <small class="text-muted">Pourcentage de l'activité parent</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="structure_responsable">Structure responsable <span class="text-danger">*</span></label>
                                <select class="form-control" id="structure_responsable" name="structure_responsable" required>
                                    <option value="">Sélectionner une structure</option>
                                    @foreach($structures as $structure)
                                        <option value="{{ $structure->id }}">
                                            {{ $structure->lib_court }} - {{ $structure->lib_long }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i>
                                <strong>Information :</strong> Le statut sera automatiquement défini à "À commencer" lors de la création.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Enregistrer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Vérifier si on doit ouvrir automatiquement le modal d'ajout
    @if(request()->has('openModal') && request()->get('openModal') == '1')
        setTimeout(function() {
            $('#addSousActiviteModal').modal('show');
        }, 500); // Petit délai pour s'assurer que la page est complètement chargée
    @endif

    // Vérifier aussi le localStorage pour la compatibilité avec l'ancien système
    var activiteId = {{ $activitePrincipale->id }};
    if (localStorage.getItem('openAddModal_' + activiteId) === 'true') {
        setTimeout(function() {
            $('#addSousActiviteModal').modal('show');
        }, 500);
        localStorage.removeItem('openAddModal_' + activiteId);
    }

    // Vérifier aussi la variable PHP passée depuis le contrôleur
    @if(isset($openAddModal) && $openAddModal)
        setTimeout(function() {
            $('#addSousActiviteModal').modal('show');
        }, 500);
    @endif
});

function confirmDelete(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette sous-activité ?')) {
        // Créer un formulaire pour la suppression
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("activites.sous-activites.destroy", [$activitePrincipale->id, ":id"]) }}'.replace(':id', id);
        
        var csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        var methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
