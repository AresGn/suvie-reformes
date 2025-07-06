@extends('layout.app')

@section('content')
<div class="data-table-area mg-b-15">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="sparkline13-list">
                    <div class="sparkline13-hd">
                        <div class="main-sparkline13-hd">
                            <h1>Modifier la sous-activité</h1>
                            <div class="btn-group">
                                <a href="{{ route('activites.sous-activites.index', $activitePrincipale->id) }}" class="btn btn-default">
                                    <i class="fa fa-arrow-left"></i> Retour aux sous-activités
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informations sur l'activité principale -->
                    <div class="alert alert-info">
                        <h4><i class="fa fa-info-circle"></i> Activité Principale</h4>
                        <p><strong>Libellé:</strong> {{ $activitePrincipale->libelle }}</p>
                        <p><strong>Réforme:</strong> {{ $activitePrincipale->reforme->titre ?? 'Non définie' }}</p>
                    </div>
                    
                    <div class="sparkline13-graph">
                        <div class="datatable-dashv1-list custom-datatable-overright">
                            <form action="{{ route('activites.sous-activites.update', [$activitePrincipale->id, $sousActivite->id]) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">
                                            <i class="fa fa-edit"></i> Informations de la sous-activité
                                        </h3>
                                    </div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="libelle">Libellé <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="libelle" name="libelle" 
                                                           value="{{ old('libelle', $sousActivite->libelle) }}" required>
                                                    @error('libelle')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="poids">Poids (%) <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control" id="poids" name="poids" 
                                                           min="1" max="{{ $poidsRestant + $sousActivite->poids }}" 
                                                           value="{{ old('poids', $sousActivite->poids) }}" required>
                                                    <small class="text-muted">
                                                        Poids disponible: {{ $poidsRestant + $sousActivite->poids }}% 
                                                        (incluant le poids actuel de {{ $sousActivite->poids }}%)
                                                    </small>
                                                    @error('poids')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="date_debut">Date de début <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control" id="date_debut" name="date_debut" 
                                                           value="{{ old('date_debut', $sousActivite->date_debut) }}" required>
                                                    @error('date_debut')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="date_fin_prevue">Date de fin prévue <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control" id="date_fin_prevue" name="date_fin_prevue" 
                                                           value="{{ old('date_fin_prevue', $sousActivite->date_fin_prevue) }}" required>
                                                    @error('date_fin_prevue')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="date_fin">Date de fin réelle</label>
                                                    <input type="date" class="form-control" id="date_fin" name="date_fin" 
                                                           value="{{ old('date_fin', $sousActivite->date_fin) }}">
                                                    @error('date_fin')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Statut actuel</label>
                                                    <div class="form-control-static">
                                                        {!! $sousActivite->status_badge !!}
                                                        <small class="text-muted d-block">Le statut ne peut pas être modifié manuellement</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="structure_responsable">Structure responsable <span class="text-danger">*</span></label>
                                                    <select class="form-control" id="structure_responsable" name="structure_responsable" required>
                                                        <option value="">Sélectionner une structure</option>
                                                        @foreach($structures as $structure)
                                                            <option value="{{ $structure->id }}" 
                                                                {{ old('structure_responsable', $sousActivite->structure_responsable) == $structure->id ? 'selected' : '' }}>
                                                                {{ $structure->lib_court }} - {{ $structure->lib_long }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('structure_responsable')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fa fa-save"></i> Mettre à jour
                                                    </button>
                                                    <a href="{{ route('activites.sous-activites.index', $activitePrincipale->id) }}" class="btn btn-default">
                                                        <i class="fa fa-times"></i> Annuler
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Information sur la gestion automatique des statuts
    console.log('=== GESTION AUTOMATIQUE DES STATUTS ===');
    console.log('Les statuts sont maintenant gérés automatiquement par le système');
    console.log('Statut actuel:', '{{ $sousActivite->statut }}');
    console.log('Progression: C (À commencer) → P (En cours) → A (Achevée)');
});
</script>
@endsection
