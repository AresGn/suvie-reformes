<nav id="sidebar" class="">
    <div class="sidebar-header">
        <a href="/"><img class="main-logo" src="{{ asset('img/logo/logo.png') }}" alt="" /></a>
        <strong><a href="/"><img src="{{ asset('img/logo/logosn.png') }}" alt="" /></a></strong>
    </div>
    <div class="left-custom-menu-adp-wrap comment-scrollbar">
        <nav class="sidebar-nav left-sidebar-menu-pro">
            <ul class="metismenu" id="menu1">
                <!-- Tableau de bord -->
                <li class="{{ request()->is('/') ? 'active' : '' }}">
                    <a href="{{ url('/') }}">
                        <i class="educate-icon educate-home icon-wrap"></i>
                        <span class="mini-click-non">Tableau de bord</span>
                    </a>
                </li>

                <!-- Gestion des Réformes -->
                <li class="{{ request()->is('reformes*') ? 'active' : '' }}">
                    <a href="#" aria-expanded="false">
                        <i class="educate-icon educate-library icon-wrap"></i>
                        <span class="mini-click-non">Réformes</span>
                    </a>
                    <ul class="submenu-angle" aria-expanded="false">
                        <li><a href="#"><i class="fa fa-list-ul sub-icon-mg"></i> <span class="mini-sub-pro">Liste des réformes</span></a></li>
                        <li><a href="#"><i class="fa fa-plus sub-icon-mg"></i> <span class="mini-sub-pro">Ajouter une réforme</span></a></li>
                    </ul>
                </li>

                <!-- Gestion des Activités -->
                <li class="{{ request()->is('activites*') ? 'active' : '' }}">
                    <a href="#" aria-expanded="false">
                        <i class="educate-icon educate-course icon-wrap"></i>
                        <span class="mini-click-non">Activités</span>
                    </a>
                    <ul class="submenu-angle" aria-expanded="false">
                        <li><a href="{{ route('activites.index') }}"><i class="fa fa-tasks sub-icon-mg"></i> <span class="mini-sub-pro">Activités principales</span></a></li>
                        <li><a href="#"><i class="fa fa-sitemap sub-icon-mg"></i> <span class="mini-sub-pro">Sous-activités</span></a></li>
                    </ul>
                </li>

                <!-- Suivi des Activités -->
                <li class="{{ request()->is('suivi-activites*') ? 'active' : '' }}">
                    <a href="{{ route('suivi-activites.index') }}">
                        <i class="educate-icon educate-analytics icon-wrap"></i>
                        <span class="mini-click-non">Suivi des activités</span>
                    </a>
                </li>

                <!-- Rapports et Statistiques -->
                <li class="{{ request()->is('rapports*') ? 'active' : '' }}">
                    <a href="#" aria-expanded="false">
                        <i class="educate-icon educate-charts icon-wrap"></i>
                        <span class="mini-click-non">Rapports</span>
                    </a>
                    <ul class="submenu-angle" aria-expanded="false">
                        <li><a href="#"><i class="fa fa-bar-chart sub-icon-mg"></i> <span class="mini-sub-pro">Statistiques</span></a></li>
                        <li><a href="#"><i class="fa fa-file-pdf-o sub-icon-mg"></i> <span class="mini-sub-pro">Rapports PDF</span></a></li>
                        <li><a href="#"><i class="fa fa-calendar sub-icon-mg"></i> <span class="mini-sub-pro">Planning</span></a></li>
                    </ul>
                </li>

                <!-- Administration -->
                @if(auth()->user() && auth()->user()->hasRole('admin'))
                <li class="{{ request()->is('admin*') ? 'active' : '' }}">
                    <a href="#" aria-expanded="false">
                        <i class="educate-icon educate-settings icon-wrap"></i>
                        <span class="mini-click-non">Administration</span>
                    </a>
                    <ul class="submenu-angle" aria-expanded="false">
                        <li><a href="#"><i class="fa fa-users sub-icon-mg"></i> <span class="mini-sub-pro">Utilisateurs</span></a></li>
                        <li><a href="#"><i class="fa fa-shield sub-icon-mg"></i> <span class="mini-sub-pro">Rôles et permissions</span></a></li>
                        <li><a href="#"><i class="fa fa-cog sub-icon-mg"></i> <span class="mini-sub-pro">Paramètres système</span></a></li>
                    </ul>
                </li>
                @endif

                <!-- Aide et Support -->
                <li>
                    <a href="#" aria-expanded="false">
                        <i class="educate-icon educate-message icon-wrap"></i>
                        <span class="mini-click-non">Aide</span>
                    </a>
                    <ul class="submenu-angle" aria-expanded="false">
                        <li><a href="#"><i class="fa fa-question-circle sub-icon-mg"></i> <span class="mini-sub-pro">Documentation</span></a></li>
                        <li><a href="#"><i class="fa fa-life-ring sub-icon-mg"></i> <span class="mini-sub-pro">Support</span></a></li>
                        <li><a href="#"><i class="fa fa-info-circle sub-icon-mg"></i> <span class="mini-sub-pro">À propos</span></a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
</nav>