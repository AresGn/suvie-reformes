@extends('layout.app')

@section('content')


<div class="container pt-4">
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
</div>
<div class="data-table-area mg-b-15">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="sparkline13-list">
                    <div class="sparkline13-hd">
                        <div class="main-sparkline13-hd">
                            <h1>Listes <span class="table-project-n">des</span> Roles </h1>
                            <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">
                                + Ajouter
                            </button>
                        </div>
                    </div>
                    <div class="sparkline13-graph">
                        <div class="datatable-dashv1-list custom-datatable-overright">
                            <div id="toolbar">
                                <select class="form-control dt-tb">
                                    <option value="">Export Basic</option>
                                    <option value="all">Export All</option>
                                    <option value="selected">Export Selected</option>
                                </select>
                            </div>
                            <table id="table" data-toggle="table" data-pagination="true" data-search="true" data-show-columns="true" data-show-pagination-switch="true" data-show-refresh="true" data-key-events="true" data-show-toggle="true" data-resizable="true" data-cookie="true"
                                   data-cookie-id-table="saveId" data-show-export="true" data-click-to-select="true" data-toolbar="#toolbar">
                                <thead>
                                    <tr>
                                        <th data-field="state" data-checkbox="true"></th>
                                        <th data-field="id">ID</th>
                                        <th data-field="role_name" data-editable="true">Libellé</th>
                                        <th data-field="permissions_html" data-editable="true" data-formatter="permissionsFormatter" data-escape="false">Permissions</th>
                                        <th data-field="action">Actions</th>
                
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($roles as $role)
                                    <tr>
                                        <td></td>
                                        <td>{{ $role->id }}</td>
                                        <td>{{ $role->role_name }}</td>
                                        <td>
                                            {!! $role->permissions_html !!}
                                        </td>
                                        <td>
                                            <!-- Voir -->
                                            <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#viewModal{{ $role->id }}"><i class="fa fa-eye"></i></button>

                                            <!-- Modifier -->
                                            <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal{{ $role->id }}"><i class="fa fa-edit"></i></button>

                                            <!-- Supprimer -->
                                            <form action="{{ route('role.destroy', $role->id) }}" method="POST" style="display:inline-block;">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce rôle ?')"><i class="fa fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Modal Visualisation -->
                                    <div class="modal fade" id="viewModal{{ $role->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Détails du rôle</h5>
                                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Nom:</strong> {{ $role->role_name }}</p>
                                                    <p><strong>Permissions:</strong></p>
                                                    <ul>
                                                        @foreach($role->permissionMenus as $pm)
                                                        <li>{{ $pm->menu->libelle ?? '-' }} - {{ $pm->permission->permission_name ?? '-' }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <div class="modal-footer">
                                                    <button class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal Édition -->
                                    <div class="modal fade" id="editModal{{ $role->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form action="{{ route('role.update', $role->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Modifier Rôle</h5>
                                                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label>Nom du rôle</label>
                                                            <input type="text" name="role_name" class="form-control" value="{{ $role->role_name }}" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Permissions (Menu - Action)</label>
                                                            <select name="permissions[]" class="form-control" multiple>
                                                                @foreach($menus as $menu)
                                                                    @foreach($menu->permissionMenus as $pm)
                                                                    <option value="{{ $pm->id }}" {{ in_array($pm->id, $role->permissionMenus->pluck('id')->toArray()) ? 'selected' : '' }}>
                                                                        {{ $menu->libelle }} - {{ $pm->permission->permission_name }}
                                                                    </option>
                                                                    @endforeach
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button class="btn btn-success" type="submit">Mettre à jour</button>
                                                        <button class="btn btn-secondary" data-dismiss="modal" type="button">Annuler</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajout -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('role.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un rôle</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nom du rôle</label>
                        <input type="text" name="role_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Permissions (Menu - Action)</label>
                        <select id="permission_menus" name="permission_menus[]" multiple="multiple" class="form-control">
                            @foreach($menus as $menu)
                                @foreach($menu->permissionMenus as $pm)
                                <option value="{{ $pm->id }}">
                                    {{ $menu->libelle }} - {{ $pm->permission->permission_name }}
                                </option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Créer</button>
                    <button class="btn btn-secondary" data-dismiss="modal" type="button">Annuler</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('.js-example-basic-multiple').select2();
    });

     function permissionsFormatter(value, row, index) {
         return value;
     }
</script>
@endsection