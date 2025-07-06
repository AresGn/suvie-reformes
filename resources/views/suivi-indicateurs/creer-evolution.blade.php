@extends('layout.app')

@section('styles')
<style>
    .evolution-form {
        max-width: 600px;
        margin: 0 auto;
    }
    
    .indicateur-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 30px;
    }
    
    .evolution-history {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .evolution-item {
        border-left: 3px solid #667eea;
        padding: 15px;
        margin-bottom: 15px;
        background: #f8f9fa;
        border-radius: 5px;
    }
    
    .evolution-item:hover {
        background: #e9ecef;
    }
    
    .valeur-evolution {
        font-size: 1.2rem;
        font-weight: bold;
        color: #2c3e50;
    }
    
    .date-evolution {
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .variation-badge {
        font-size: 0.8rem;
        padding: 3px 8px;
        border-radius: 10px;
        font-weight: bold;
    }
    
    .variation-positive { background-color: #d4edda; color: #155724; }
    .variation-negative { background-color: #f8d7da; color: #721c24; }
    .variation-stable { background-color: #fff3cd; color: #856404; }
    
    .form-group label {
        font-weight: 600;
        color: #2c3e50;
    }
    
    .btn-retour {
        background: #6c757d;
        border-color: #6c757d;
        color: white;
    }
    
    .btn-retour:hover {
        background: #5a6268;
        border-color: #545b62;
        color: white;
    }
</style>
@endsection

@section('content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Ajouter une Évolution</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
                    <li><a href="{{ route('suivi-indicateurs.index') }}">Suivi Indicateurs</a></li>
                    <li><a href="{{ route('suivi-indicateurs.tableau-bord', $reformeIndicateur->reforme_id) }}">{{ $reformeIndicateur->reforme->titre }}</a></li>
                    <li class="active">Ajouter Évolution</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="animated fadeIn">
        
        <!-- Informations sur l'indicateur -->
        <div class="row">
            <div class="col-12">
                <div class="indicateur-info">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-2">{{ $reformeIndicateur->indicateur->libelle }}</h4>
                            <p class="mb-1">
                                <i class="fa fa-project-diagram"></i>
                                Réforme: {{ $reformeIndicateur->reforme->titre }}
                            </p>
                            <p class="mb-0">
                                <i class="fa fa-ruler"></i>
                                Unité: {{ $reformeIndicateur->indicateur->unite }}
                            </p>
                        </div>
                        <div class="col-md-4 text-right">
                            @if($reformeIndicateur->valeur_actuelle !== null)
                            <div>
                                <h3 class="mb-1">{{ number_format($reformeIndicateur->valeur_actuelle, 2, ',', ' ') }}</h3>
                                <small>Valeur actuelle</small>
                            </div>
                            @else
                            <div>
                                <h3 class="mb-1">-</h3>
                                <small>Aucune donnée</small>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Formulaire d'ajout -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">
                            <i class="fa fa-plus"></i>
                            Nouvelle Évolution
                        </strong>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('suivi-indicateurs.stocker-evolution', $reformeIndicateur->id) }}" 
                              method="POST" class="evolution-form">
                            @csrf
                            
                            <div class="form-group">
                                <label for="date_evolution">Date de l'évolution *</label>
                                <input type="date" 
                                       class="form-control @error('date_evolution') is-invalid @enderror" 
                                       id="date_evolution" 
                                       name="date_evolution" 
                                       value="{{ old('date_evolution', date('Y-m-d')) }}" 
                                       max="{{ date('Y-m-d') }}"
                                       required>
                                @error('date_evolution')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    La date ne peut pas être dans le futur
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="valeur">Valeur *</label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control @error('valeur') is-invalid @enderror" 
                                           id="valeur" 
                                           name="valeur" 
                                           value="{{ old('valeur') }}" 
                                           step="0.01"
                                           required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">{{ $reformeIndicateur->indicateur->unite }}</span>
                                    </div>
                                    @error('valeur')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="form-text text-muted">
                                    Entrez la valeur de l'indicateur pour cette date
                                </small>
                            </div>

                            @if($reformeIndicateur->valeur_actuelle !== null)
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i>
                                <strong>Valeur actuelle:</strong> {{ number_format($reformeIndicateur->valeur_actuelle, 2, ',', ' ') }} {{ $reformeIndicateur->indicateur->unite }}
                                <br>
                                <small>Cette nouvelle valeur remplacera la valeur actuelle si la date est plus récente.</small>
                            </div>
                            @endif

                            <div class="form-group text-center">
                                <a href="{{ route('suivi-indicateurs.tableau-bord', $reformeIndicateur->reforme_id) }}" 
                                   class="btn btn-retour mr-2">
                                    <i class="fa fa-arrow-left"></i> Retour
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Enregistrer l'évolution
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Historique des évolutions -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">
                            <i class="fa fa-history"></i>
                            Historique des Évolutions ({{ $reformeIndicateur->evolutions->count() }})
                        </strong>
                    </div>
                    <div class="card-body">
                        @if($reformeIndicateur->evolutions->count() > 0)
                        <div class="evolution-history">
                            @foreach($reformeIndicateur->evolutions->sortByDesc('date_evolution') as $evolution)
                            <div class="evolution-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="valeur-evolution">
                                            {{ number_format($evolution->valeur, 2, ',', ' ') }} {{ $reformeIndicateur->indicateur->unite }}
                                        </div>
                                        <div class="date-evolution">
                                            <i class="fa fa-calendar"></i>
                                            {{ $evolution->date_formatee }}
                                            @if($evolution->is_recente)
                                            <span class="badge badge-success ml-1">Récent</span>
                                            @endif
                                        </div>
                                        
                                        @if($evolution->variation_precedente !== null)
                                        <div class="mt-2">
                                            <span class="variation-badge 
                                                @if($evolution->type_variation == 'hausse') variation-positive
                                                @elseif($evolution->type_variation == 'baisse') variation-negative
                                                @else variation-stable
                                                @endif">
                                                <i class="{{ $evolution->icone_variation }}"></i>
                                                {{ $evolution->variation_precedente > 0 ? '+' : '' }}{{ $evolution->variation_precedente }}%
                                            </span>
                                        </div>
                                        @endif
                                    </div>
                                    
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                type="button" data-toggle="dropdown">
                                            <i class="fa fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" 
                                               href="{{ route('suivi-indicateurs.modifier-evolution', [$reformeIndicateur->id, $evolution->date_evolution]) }}">
                                                <i class="fa fa-edit"></i> Modifier
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" 
                                               href="{{ route('suivi-indicateurs.supprimer-evolution', [$reformeIndicateur->id, $evolution->date_evolution]) }}"
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette évolution ?')">
                                                <i class="fa fa-trash"></i> Supprimer
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-4">
                            <i class="fa fa-chart-line fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">Aucune évolution enregistrée</h6>
                            <p class="text-muted">Cette sera la première évolution de cet indicateur.</p>
                        </div>
                        @endif
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
    // Validation en temps réel
    $('#valeur').on('input', function() {
        const valeur = parseFloat($(this).val());
        const valeurActuelle = {{ $reformeIndicateur->valeur_actuelle ?? 'null' }};
        
        if (valeurActuelle !== null && !isNaN(valeur)) {
            const variation = ((valeur - valeurActuelle) / valeurActuelle) * 100;
            let message = '';
            let classe = '';
            
            if (Math.abs(variation) < 1) {
                message = 'Variation stable';
                classe = 'text-warning';
            } else if (variation > 0) {
                message = `Hausse de ${variation.toFixed(2)}%`;
                classe = 'text-success';
            } else {
                message = `Baisse de ${Math.abs(variation).toFixed(2)}%`;
                classe = 'text-danger';
            }
            
            // Afficher l'aperçu de la variation
            let apercu = $('#apercu-variation');
            if (apercu.length === 0) {
                apercu = $('<small id="apercu-variation" class="form-text"></small>');
                $('#valeur').parent().append(apercu);
            }
            
            apercu.removeClass('text-success text-danger text-warning')
                  .addClass(classe)
                  .html(`<i class="fa fa-calculator"></i> ${message}`);
        }
    });
    
    // Vérification de date existante
    $('#date_evolution').on('change', function() {
        const date = $(this).val();
        const reformeIndicateurId = {{ $reformeIndicateur->id }};
        
        if (date) {
            // Vérifier si une évolution existe déjà pour cette date
            const evolutionsExistantes = @json($reformeIndicateur->evolutions->pluck('date_evolution')->map(function($date) { return $date->format('Y-m-d'); }));
            
            if (evolutionsExistantes.includes(date)) {
                let avertissement = $('#avertissement-date');
                if (avertissement.length === 0) {
                    avertissement = $('<div id="avertissement-date" class="alert alert-warning mt-2"></div>');
                    $('#date_evolution').parent().append(avertissement);
                }
                
                avertissement.html('<i class="fa fa-exclamation-triangle"></i> Une évolution existe déjà pour cette date. Elle sera mise à jour.');
            } else {
                $('#avertissement-date').remove();
            }
        }
    });
    
    // Auto-focus sur le champ valeur
    $('#valeur').focus();
});
</script>
@endsection
