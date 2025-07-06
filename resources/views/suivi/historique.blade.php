@extends('layout.app')

@section('content')
<div class="data-table-area mg-b-15">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="sparkline13-list">
                    <div class="sparkline13-hd">
                        <div class="main-sparkline13-hd">
                            <h1>Historique des suivis</h1>
                        </div>
                    </div>
                    
                    <div class="row mg-b-15">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Informations sur l'activité</h3>
                                </div>
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Réforme:</strong> {{ $activite->reforme->titre }}</p>
                                            <p><strong>Activité principale:</strong> {{ $activite->parentActivite->libelle }}</p>
                                            <p><strong>Sous-activité:</strong> {{ $activite->libelle }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Date début:</strong> {{ $activite->date_debut }}</p>
                                            <p><strong>Date fin prévue:</strong> {{ $activite->date_fin_prevue }}</p>
                                            <p><strong>Statut:</strong> 
                                                <span class="badge badge-{{ $activite->statut == 'A' ? 'success' : ($activite->statut == 'P' ? 'warning' : 'danger') }}">
                                                    {{ $activite->statut_label }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="sparkline13-graph">
                        <div class="datatable-dashv1-list custom-datatable-overright">
                            <table id="table" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Date du suivi</th>
                                        <th>Actions réalisées</th>
                                        <th>Actions à faire</th>
                                        <th>Difficultés</th>
                                        <th>Solutions</th>
                                        <th>Observations</th>
                                        <th>Créé par</th>
                                        <th>Date création</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($suivis as $suivi)
                                    <tr>
                                        <td>{{ $suivi->suivi_date }}</td>
                                        <td>{{ $suivi->actions_fait }}</td>
                                        <td>{{ $suivi->actions_a_fait }}</td>
                                        <td>{{ $suivi->difficultes ?: 'N/A' }}</td>
                                        <td>{{ $suivi->solutions ?: 'N/A' }}</td>
                                        <td>{{ $suivi->observations ?: 'N/A' }}</td>
                                        <td>{{ $suivi->creator->name ?? 'N/A' }}</td>
                                        <td>{{ $suivi->created_at }}</td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-xs supprimer-suivi" data-id="{{ $suivi->id }}">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <a href="{{ route('suivi-activites.index') }}" class="btn btn-primary">
                                <i class="fa fa-arrow-left"></i> Retour à la liste
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Supprimer un suivi
    $('.supprimer-suivi').click(function() {
        var id = $(this).data('id');
        
        if (confirm('Êtes-vous sûr de vouloir supprimer ce suivi?')) {
            $.ajax({
                url: "{{ url('suivi-activites') }}/" + id,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert('Erreur: ' + response.message);
                    }
                },
                error: function(xhr) {
                    alert('Erreur: ' + xhr.responseText);
                }
            });
        }
    });
});
</script>
@endpush
@endsection

