<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<div style="display: flex;">

    <!-- Sidebar -->
    <div style="
        position: fixed; 
        top: 0; 
        left: 0; 
        width: 250px; 
        height: 100vh; 
        background-color: #4e73df; 
        color: white; 
        padding-top: 20px; 
        overflow-y: auto; 
        z-index: 100; /* Assure que le menu reste au-dessus du contenu */
    ">

        <!-- Sidebar - Brand -->
        <div style="text-align: center; padding: 10px;">
            <!--<i class="fas fa-laugh-wink" style="font-size: 24px;"></i>
            <h5>SB Admin</h5>-->
            <img src="{{ asset('img/logo/logomcvt.png') }} " alt="" style="background-color: white;">
        </div>

        <hr style="border-color: rgba(255,255,255,0.2);">

        <!-- Menu Dynamique -->
        <ul class="list-unstyled" style="padding-left: 20px; padding-bottom: 50px;">
            @forelse($menus as $menu)
                <li class="nav-item" style="padding: 10px 0;">
                    <a href="{{ $menu->url }}" style="color: white; text-decoration: none; display: flex; align-items: center;">
                        <i class="fas {{ $menu->icon }}" style="margin-right: 10px;"></i>
                        {{ $menu->libelle ?? 'Menu sans titre' }}
                    </a>
                </li>
            @empty
                <div style="padding: 20px;">Aucun menu disponible.</div>
            @endforelse
        </ul>

    </div>

    <!-- Page Content -->
    <div style="margin-left: 250px; padding: 20px; margin-top: 20px;">
        @yield('content')
    </div>

</div>
