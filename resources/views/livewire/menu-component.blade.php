<div class="left-custom-menu-adp-wrap comment-scrollbar">
<nav class="sidebar-nav left-sidebar-menu-pro">
    <ul class="metismenu" id="menu1">
        @forelse($menus as $menu)
        <li class="active">
            <a href="{{ $menu->url }}">
                <span class="{{ $menu->icon }}"></span>
                <span class="mini-click-non">{{ $menu->libelle ?? 'Menu sans titre' }}</span>
            </a>
        </li>
        @empty
        <li>Aucun menu disponible.</li>
        @endforelse
    </ul>
</nav>
</div>
