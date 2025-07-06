<div class="header-advance-area">
    <div class="header-top-area">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="header-top-wraper">
                        <div class="row">
                        <div class="col-lg-1 col-md-0 col-sm-1 col-xs-12">
                                        <div class="menu-switcher-pro">
                                            <button type="button" id="sidebarCollapse" class="btn bar-button-pro header-drl-controller-btn btn-info navbar-btn">
													<i class="educate-icon educate-nav"></i>
												</button>
                                        </div>
                                    </div>
                            
                            <div class="col-lg-6 col-md-7 col-sm-6 col-xs-12">
                                <div class="header-top-menu tabl-d-n">
                                    <ul class="nav navbar-nav mai-top-nav">
                                        <li><a href="#">Profil</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                                <div class="header-right-info">
                                    <ul class="nav navbar-nav mai-top-nav header-right-menu">
                                        <!-- Notifications Dropdown -->
                                        <li class="nav-item dropdown notification-dropdown">
                                            <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-bell" style="font-size: 20px; color: aliceblue;"></i>
                                                <span class="badge badge-danger notification-count" id="notificationCount" style="display: none;">0</span>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right notification-menu" aria-labelledby="notificationDropdown">
                                                <div class="notification-header">
                                                    <h6 class="dropdown-header">
                                                        <span id="notificationTitle">Notifications</span>
                                                        <button class="btn btn-sm btn-link float-right" id="markAllRead" title="Marquer tout comme lu">
                                                            <i class="fas fa-check-double"></i>
                                                        </button>
                                                    </h6>
                                                </div>
                                                <div class="notification-body" id="notificationList">
                                                    <div class="text-center p-3">
                                                        <i class="fas fa-spinner fa-spin"></i> Chargement...
                                                    </div>
                                                </div>
                                                <div class="notification-footer">
                                                    <a class="dropdown-item text-center" href="{{ route('notifications.index') }}">
                                                        Voir toutes les notifications
                                                    </a>
                                                </div>
                                            </div>
                                        </li>

                                        <!-- User Profile Dropdown -->
                                        <li class="nav-item dropdown">
                                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-user" style="font-size: 18px; color: aliceblue;"></i>
                                                <span style="color: aliceblue; margin-left: 5px;">{{ Auth::user()->name ?? 'Utilisateur' }}</span>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                                                <a class="dropdown-item" href="#"><i class="fas fa-user-circle"></i> Profil</a>
                                                <a class="dropdown-item" href="{{ route('notifications.index') }}"><i class="fas fa-bell"></i> Notifications</a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="{{ route('logout') }}"
                                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                                                </a>
                                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                                    @csrf
                                                </form>
                                            </div>
                                        </li>
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
