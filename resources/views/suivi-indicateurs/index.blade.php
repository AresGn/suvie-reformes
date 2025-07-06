@extends('layout.app')

@section('styles')
<style>
    .card-stat {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .card-stat h3 {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .card-stat p {
        margin: 0;
        opacity: 0.9;
    }
    
    .score-badge {
        font-size: 1.2rem;
        padding: 8px 15px;
        border-radius: 20px;
        font-weight: bold;
    }
    
    .score-excellent { background-color: #28a745; color: white; }
    .score-bon { background-color: #17a2b8; color: white; }
    .score-moyen { background-color: #ffc107; color: black; }
    .score-faible { background-color: #dc3545; color: white; }
    
    .tendance-icon {
        font-size: 1.5rem;
        margin-right: 10px;
    }
    
    .alert-item {
        border-left: 4px solid;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 5px;
    }
    
    .alert-warning { border-left-color: #ffc107; background-color: #fff3cd; }
    .alert-info { border-left-color: #17a2b8; background-color: #d1ecf1; }
    
    .reforme-card {
        transition: transform 0.2s;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    
    .reforme-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
</style>
@endsection

@section('content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Suivi des Indicateurs</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
                    <li class="active">Suivi des Indicateurs</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="animated fadeIn">
        
        <!-- Statistiques générales -->
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="card-stat">
                    <h3>{{ count($rapport) }}</h3>
                    <p><i class="fa fa-project-diagram"></i> Réformes avec indicateurs</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card-stat">
                    <h3>{{ collect($rapport)->sum(function($r) { return $r['statistiques']['total_indicateurs']; }) }}</h3>
                    <p><i class="fa fa-chart-line"></i> Total indicateurs</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card-stat">
                    <h3>{{ collect($rapport)->sum(function($r) { return $r['statistiques']['indicateurs_avec_donnees']; }) }}</h3>
                    <p><i class="fa fa-database"></i> Avec données</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card-stat">
                    <h3>{{ count($alertes) }}</h3>
                    <p><i class="fa fa-exclamation-triangle"></i> Alertes</p>
                </div>
            </div>
        </div>

        <!-- Alertes -->
        @if(count($alertes) > 0)
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">
                            <i class="fa fa-exclamation-triangle text-warning"></i>
                            Alertes de Suivi ({{ count($alertes) }})
                        </strong>
                        <div class="float-right">
                            <a href="{{ route('suivi-indicateurs.alertes') }}" class="btn btn-sm btn-outline-primary">
                                Voir toutes les alertes
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @foreach(array_slice($alertes, 0, 5) as $alerte)
                        <div class="alert-item alert-{{ $alerte['type'] }}">
                            <i class="fa fa-{{ $alerte['type'] == 'warning' ? 'exclamation-triangle' : 'info-circle' }}"></i>
                            {{ $alerte['message'] }}
                            @if(isset($alerte['reforme_id']))
                            <a href="{{ route('suivi-indicateurs.tableau-bord', $alerte['reforme_id']) }}" 
                               class="btn btn-sm btn-outline-primary float-right">
                                Voir
                            </a>
                            @endif
                        </div>
                        @endforeach
                        
                        @if(count($alertes) > 5)
                        <div class="text-center mt-3">
                            <a href="{{ route('suivi-indicateurs.alertes') }}" class="btn btn-outline-primary">
                                Voir {{ count($alertes) - 5 }} autres alertes
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Liste des réformes -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">
                            <i class="fa fa-list"></i>
                            Réformes et leurs Indicateurs
                        </strong>
                        <div class="float-right">
                            <a href="{{ route('suivi-indicateurs.import-lot') }}" class="btn btn-sm btn-success">
                                <i class="fa fa-upload"></i> Import en lot
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @forelse($rapport as $item)
                        <div class="reforme-card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h5 class="card-title mb-2">
                                            <a href="{{ route('suivi-indicateurs.tableau-bord', $item['reforme']->id) }}" 
                                               class="text-decoration-none">
                                                {{ $item['reforme']->titre }}
                                            </a>
                                        </h5>
                                        <p class="text-muted mb-1">
                                            <i class="fa fa-calendar"></i>
                                            {{ \Carbon\Carbon::parse($item['reforme']->date_debut)->format('d/m/Y') }} - 
                                            {{ \Carbon\Carbon::parse($item['reforme']->date_fin_prevue)->format('d/m/Y') }}
                                        </p>
                                        <p class="text-muted mb-0">
                                            <i class="fa fa-chart-line"></i>
                                            {{ $item['statistiques']['total_indicateurs'] }} indicateur(s)
                                        </p>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="mb-2">
                                                <span class="score-badge 
                                                    @if($item['score_suivi'] >= 80) score-excellent
                                                    @elseif($item['score_suivi'] >= 60) score-bon
                                                    @elseif($item['score_suivi'] >= 40) score-moyen
                                                    @else score-faible
                                                    @endif">
                                                    {{ $item['score_suivi'] }}%
                                                </span>
                                            </div>
                                            <small class="text-muted">Score de suivi</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            @if($item['statistiques']['tendances']['hausse'] > 0)
                                            <div class="tendance-icon text-success">
                                                <i class="fa fa-arrow-up"></i> {{ $item['statistiques']['tendances']['hausse'] }}
                                            </div>
                                            @endif
                                            @if($item['statistiques']['tendances']['baisse'] > 0)
                                            <div class="tendance-icon text-danger">
                                                <i class="fa fa-arrow-down"></i> {{ $item['statistiques']['tendances']['baisse'] }}
                                            </div>
                                            @endif
                                            @if($item['statistiques']['tendances']['stable'] > 0)
                                            <div class="tendance-icon text-warning">
                                                <i class="fa fa-minus"></i> {{ $item['statistiques']['tendances']['stable'] }}
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-1">
                                        <a href="{{ route('suivi-indicateurs.tableau-bord', $item['reforme']->id) }}" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Barre de progression -->
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted">Completion des données</small>
                                        <small class="text-muted">{{ $item['statistiques']['pourcentage_completion'] }}%</small>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar 
                                            @if($item['statistiques']['pourcentage_completion'] >= 80) bg-success
                                            @elseif($item['statistiques']['pourcentage_completion'] >= 60) bg-info
                                            @elseif($item['statistiques']['pourcentage_completion'] >= 40) bg-warning
                                            @else bg-danger
                                            @endif" 
                                            style="width: {{ $item['statistiques']['pourcentage_completion'] }}%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <i class="fa fa-chart-line fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucune réforme avec indicateurs</h5>
                            <p class="text-muted">Commencez par associer des indicateurs à vos réformes.</p>
                        </div>
                        @endforelse
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
    // Animation des cartes au survol
    $('.reforme-card').hover(
        function() {
            $(this).addClass('shadow-lg');
        },
        function() {
            $(this).removeClass('shadow-lg');
        }
    );
    
    // Actualisation automatique des statistiques toutes les 5 minutes
    setInterval(function() {
        location.reload();
    }, 300000);
});
</script>
@endsection
