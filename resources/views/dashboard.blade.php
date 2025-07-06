@extends('layout.app')

@section('content')

<div class="analytics-sparkle-area">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                <div class="analytics-sparkle-line reso-mg-b-30">
                    <div class="analytics-content">
                        <h5>Utilisateurs</h5>
                        <h2><span class="counter">{{ $totalUsers }}</span> <span class="tuition-fees">Utilisateurs</span></h2>
                        <span class="text-success">{{ $userPercent }}%</span>
                        <div class="progress m-b-0">
                            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="{{ $userPercent }}" aria-valuemin="0" aria-valuemax="100" style="width:{{ $userPercent }}%;"> <span class="sr-only">{{ $userPercent }}% Complete</span> </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                <div class="analytics-sparkle-line reso-mg-b-30">
                    <div class="analytics-content">
                        <h5>Réformes</h5>
                        <h2><span class="counter">{{ $totalReformes }}</span> <span class="tuition-fees">Réformes</span></h2>
                        <span class="text-danger">{{ $reformePercent }}%</span>
                        <div class="progress m-b-0">
                            <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="{{ $reformePercent }}" aria-valuemin="0" aria-valuemax="100" style="width:{{ $reformePercent }}%;"> <span class="sr-only">{{ $reformePercent }}% Complete</span> </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                <div class="analytics-sparkle-line reso-mg-b-30 table-mg-t-pro dk-res-t-pro-30">
                    <div class="analytics-content">
                        <h5>Activités</h5>
                        <h2><span class="counter">{{ $totalActivites }}</span> <span class="tuition-fees">Activités</span></h2>
                        <span class="text-info">{{ $activitePercent }}%</span>
                        <div class="progress m-b-0">
                            <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="{{ $activitePercent }}" aria-valuemin="0" aria-valuemax="100" style="width:{{ $activitePercent }}%;"> <span class="sr-only">{{ $activitePercent }}% Complete</span> </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                <div class="analytics-sparkle-line table-mg-t-pro dk-res-t-pro-30">
                    <div class="analytics-content">
                        <h5>Indicateurs</h5>
                        <h2><span class="counter">{{ $totalIndicateurs }}</span> <span class="tuition-fees">Indicateurs</span></h2>
                        <span class="text-inverse">{{ $indicateurPercent }}%</span>
                        <div class="progress m-b-0">
                            <div class="progress-bar progress-bar-inverse" role="progressbar" aria-valuenow="{{ $indicateurPercent }}" aria-valuemin="0" aria-valuemax="100" style="width:{{ $indicateurPercent }}%;"> <span class="sr-only">{{ $indicateurPercent }}% Complete</span> </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Section pour les réformes par type -->
<div class="product-sales-area mg-tb-30">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="product-sales-chart">
                    <div class="portlet-title">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="caption pro-sl-hd">
                                    <span class="caption-subject text-uppercase"><b>Réformes par type</b></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="analytics-rounded mg-b-30">
                                <div class="analytics-rounded-content">
                                    <ul>
                                        @foreach($reformesParType as $type)
                                        <li><span class="text-info">{{ $type->lib }}</span>  <span class="text-success">{{ $type->total }}</span></li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Section pour les activités récentes -->
<div class="product-sales-area mg-tb-30">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="product-sales-chart">
                    <div class="portlet-title">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="caption pro-sl-hd">
                                    <span class="caption-subject text-uppercase"><b>Activités récentes</b></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="analytics-edu-wrap">
                                <div class="analytics-edu-wrap-1">
                                    <ul>
                                        @foreach($activitesRecentes as $activite)
                                        <li><span class="tuition-fees">{{ $activite->created_at->format('d/m/Y') }}</span><span class="text-info">{{ $activite->libelle ?? 'Activité' }}</span></li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection