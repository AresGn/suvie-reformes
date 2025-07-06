@extends('layout.app')

@section('content')
<div class="container pt-4">
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="data-table-area mg-b-15">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="sparkline13-list">
                        <div class="sparkline13-hd">
                            <div class="main-sparkline13-hd">
                                <h1>Mes <span class="table-project-n">Sessions</span> de Connexion</h1>
                                <div class="sparkline13-outline-icon">
                                    <span class="sparkline13-collapse-link"><i class="fa fa-chevron-up"></i></span>
                                    <span><i class="fa fa-wrench"></i></span>
                                    <span class="sparkline13-collapse-close"><i class="fa fa-times"></i></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Statistiques -->
                        <div class="row mg-b-15">
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="panel panel-primary">
                                    <div class="panel-body">
                                        <div class="text-center">
                                            <h3 class="text-primary">{{ $stats['total'] }}</h3>
                                            <p>Total Sessions</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="panel panel-success">
                                    <div class="panel-body">
                                        <div class="text-center">
                                            <h3 class="text-success">{{ $stats['active'] }}</h3>
                                            <p>Sessions Actives</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="panel panel-info">
                                    <div class="panel-body">
                                        <div class="text-center">
                                            <h3 class="text-info">{{ $stats['this_month'] }}</h3>
                                            <p>Ce Mois</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="sparkline13-graph">
                            <div class="datatable-dashv1-list custom-datatable-overright">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Date de Connexion</th>
                                            <th>Adresse IP</th>
                                            <th>Navigateur</th>
                                            <th>Durée</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($sessions as $session)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $session->login_at->format('d/m/Y à H:i') }}</strong>
                                                    @if($session->logout_at)
                                                        <br><small class="text-muted">Déconnecté : {{ $session->logout_at->format('d/m/Y à H:i') }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>{{ $session->ip_address }}</td>
                                            <td>
                                                <small>{{ Str::limit($session->user_agent, 50) }}</small>
                                            </td>
                                            <td>
                                                <span class="label {{ $session->isActive() ? 'label-info' : 'label-default' }}">
                                                    {{ $session->formatted_duration }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($session->isActive())
                                                    @if($session->session_id === session()->getId())
                                                        <span class="label label-success">
                                                            <i class="fa fa-circle"></i> Session Actuelle
                                                        </span>
                                                    @else
                                                        <span class="label label-warning">
                                                            <i class="fa fa-circle"></i> Active
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="label label-default">
                                                        <i class="fa fa-circle-o"></i> Fermée
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($session->isActive() && $session->session_id !== session()->getId())
                                                    <form action="{{ route('sessions.terminate', $session) }}" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir fermer cette session ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-xs" title="Fermer la session">
                                                            <i class="fa fa-sign-out"></i> Fermer
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                <div class="alert alert-info">
                                                    <i class="fa fa-info-circle"></i> Aucune session trouvée.
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                
                                <!-- Pagination -->
                                @if($sessions->hasPages())
                                <div class="text-center">
                                    {{ $sessions->links() }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.mg-b-15 {
    margin-bottom: 15px;
}

.panel-body .text-center h3 {
    margin: 10px 0;
    font-size: 2em;
    font-weight: bold;
}

.panel-body .text-center p {
    margin: 0;
    font-size: 0.9em;
    color: #666;
}

.label {
    font-size: 11px;
    padding: 4px 8px;
}

@media (max-width: 768px) {
    .col-lg-4 {
        margin-bottom: 15px;
    }
    
    .table-responsive {
        font-size: 12px;
    }
}
</style>
@endsection
