@extends('layout.app')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css">
<style>
    .indicateur-card {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }
    
    .indicateur-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .valeur-actuelle {
        font-size: 2rem;
        font-weight: bold;
        color: #2c3e50;
    }
    
    .evolution-badge {
        font-size: 0.9rem;
        padding: 5px 10px;
        border-radius: 15px;
        font-weight: bold;
    }
    
    .evolution-positive { background-color: #d4edda; color: #155724; }
    .evolution-negative { background-color: #f8d7da; color: #721c24; }
    .evolution-stable { background-color: #fff3cd; color: #856404; }
    
    .stat-box {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        margin-bottom: 20px;
    }
    
    .stat-box h4 {
        font-size: 2rem;
        margin-bottom: 5px;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        margin: 20px 0;
    }
    
    .no-data {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }
    
    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .tendance-icon {
        font-size: 1.5rem;
        margin-right: 10px;
    }
</style>
@endsection

@section('content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Tableau de Bord - {{ $reforme->titre }}</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
                    <li><a href="{{ route('suivi-indicateurs.index') }}">Suivi Indicateurs</a></li>
                    <li class="active">{{ $reforme->titre }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="animated fadeIn">
        
        <!-- Informations de la réforme -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h4>{{ $reforme->titre }}</h4>
                                <p class="text-muted">{{ $reforme->objectifs }}</p>
                                <p>
                                    <i class="fa fa-calendar"></i>
                                    {{ \Carbon\Carbon::parse($reforme->date_debut)->format('d/m/Y') }} - 
                                    {{ \Carbon\Carbon::parse($reforme->date_fin_prevue)->format('d/m/Y') }}
                                </p>
                            </div>
                            <div class="col-md-4 text-right">
                                <a href="{{ route('suivi-indicateurs.exporter-csv', $reforme->id) }}" 
                                   class="btn btn-success">
                                    <i class="fa fa-download"></i> Exporter CSV
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="stat-box">
                    <h4>{{ $statistiques['total_indicateurs'] }}</h4>
                    <p>Indicateurs Total</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-box">
                    <h4>{{ $statistiques['indicateurs_avec_donnees'] }}</h4>
                    <p>Avec Données</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-box">
                    <h4>{{ $statistiques['pourcentage_completion'] }}%</h4>
                    <p>Completion</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-box">
                    <h4>{{ $statistiques['indicateurs_recents'] }}</h4>
                    <p>MAJ Récentes</p>
                </div>
            </div>
        </div>

        <!-- Ajouter un indicateur -->
        @if(count($indicateurs_disponibles) > 0)
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <strong>Ajouter un Indicateur</strong>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('suivi-indicateurs.associer', $reforme->id) }}" method="POST" class="form-inline">
                            @csrf
                            <div class="form-group mr-3">
                                <select name="indicateur_id" class="form-control" required>
                                    <option value="">Sélectionner un indicateur...</option>
                                    @foreach($indicateurs_disponibles as $indicateur)
                                    <option value="{{ $indicateur->id }}">
                                        {{ $indicateur->libelle }} ({{ $indicateur->unite }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-plus"></i> Associer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Liste des indicateurs -->
        <div class="row">
            @forelse($indicateurs as $item)
            <div class="col-lg-6 col-md-12">
                <div class="indicateur-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            {{ $item['indicateur']->libelle }}
                            <small class="text-muted">({{ $item['indicateur']->unite }})</small>
                        </h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                    type="button" data-toggle="dropdown">
                                Actions
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" 
                                   href="{{ route('suivi-indicateurs.creer-evolution', $item['id']) }}">
                                    <i class="fa fa-plus"></i> Ajouter évolution
                                </a>
                                <a class="dropdown-item" href="#" 
                                   onclick="afficherGraphique({{ $item['id'] }})">
                                    <i class="fa fa-chart-line"></i> Voir graphique
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" 
                                   href="{{ route('suivi-indicateurs.dissocier', [$reforme->id, $item['indicateur']->id]) }}"
                                   onclick="return confirm('Êtes-vous sûr de vouloir dissocier cet indicateur ?')">
                                    <i class="fa fa-unlink"></i> Dissocier
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        @if($item['valeur_actuelle'] !== null)
                        <div class="row">
                            <div class="col-6">
                                <div class="valeur-actuelle">
                                    {{ number_format($item['valeur_actuelle'], 2, ',', ' ') }}
                                </div>
                                <small class="text-muted">Valeur actuelle</small>
                            </div>
                            <div class="col-6 text-right">
                                @if($item['evolution_pourcentage'] !== null)
                                <span class="evolution-badge 
                                    @if($item['tendance'] == 'hausse') evolution-positive
                                    @elseif($item['tendance'] == 'baisse') evolution-negative
                                    @else evolution-stable
                                    @endif">
                                    <i class="{{ $item['icone_tendance'] }}"></i>
                                    {{ $item['evolution_pourcentage'] > 0 ? '+' : '' }}{{ $item['evolution_pourcentage'] }}%
                                </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-6">
                                <small class="text-muted">Valeur initiale:</small><br>
                                <strong>{{ $item['valeur_initiale'] ? number_format($item['valeur_initiale'], 2, ',', ' ') : 'N/A' }}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Évolutions:</small><br>
                                <strong>{{ $item['nombre_evolutions'] }}</strong>
                            </div>
                        </div>
                        
                        @if($item['derniere_evolution'])
                        <div class="mt-3">
                            <small class="text-muted">
                                Dernière mise à jour: {{ $item['derniere_evolution']->date_formatee }}
                                @if(!$item['has_data_recente'])
                                <span class="badge badge-warning ml-2">Données anciennes</span>
                                @endif
                            </small>
                        </div>
                        @endif
                        
                        @else
                        <div class="no-data">
                            <i class="fa fa-chart-line fa-2x mb-2"></i>
                            <p>Aucune donnée disponible</p>
                            <a href="{{ route('suivi-indicateurs.creer-evolution', $item['id']) }}" 
                               class="btn btn-primary btn-sm">
                                Ajouter la première évolution
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fa fa-chart-line fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucun indicateur associé</h5>
                        <p class="text-muted">Commencez par associer des indicateurs à cette réforme.</p>
                    </div>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal pour le graphique -->
<div class="modal fade" id="graphiqueModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Évolution de l'Indicateur</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="chart-container">
                    <canvas id="evolutionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
let chartInstance = null;

function afficherGraphique(reformeIndicateurId) {
    // Charger les données via AJAX
    fetch(`/api/suivi-indicateurs/${reformeIndicateurId}/graphique`)
        .then(response => response.json())
        .then(data => {
            // Détruire le graphique existant s'il y en a un
            if (chartInstance) {
                chartInstance.destroy();
            }
            
            // Créer le nouveau graphique
            const ctx = document.getElementById('evolutionChart').getContext('2d');
            chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: `${data.indicateur.libelle} (${data.unite})`,
                        data: data.donnees,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: false
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
            
            // Afficher le modal
            $('#graphiqueModal').modal('show');
        })
        .catch(error => {
            console.error('Erreur lors du chargement des données:', error);
            alert('Erreur lors du chargement du graphique');
        });
}

$(document).ready(function() {
    // Actualisation automatique des statistiques
    setInterval(function() {
        fetch(`/api/suivi-indicateurs/reforme/{{ $reforme->id }}/statistiques`)
            .then(response => response.json())
            .then(data => {
                // Mettre à jour les statistiques affichées
                updateStatistiques(data);
            })
            .catch(error => console.error('Erreur actualisation:', error));
    }, 60000); // Toutes les minutes
});

function updateStatistiques(stats) {
    // Mise à jour des boîtes de statistiques
    $('.stat-box h4').each(function(index) {
        switch(index) {
            case 0: $(this).text(stats.total_indicateurs); break;
            case 1: $(this).text(stats.indicateurs_avec_donnees); break;
            case 2: $(this).text(stats.pourcentage_completion + '%'); break;
            case 3: $(this).text(stats.indicateurs_recents); break;
        }
    });
}
</script>
@endsection
