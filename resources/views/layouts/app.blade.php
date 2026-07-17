<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'DEE IT Repair') }}</title>

    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <style>
        body { background:#f5f6f8; font-size:14px; }
        .dee-topbar { background:#1f7f4c; box-shadow:0 2px 6px rgba(0,0,0,.12); }
        .dee-topbar .navbar-brand { font-weight:700; letter-spacing:.2px; }
        .dee-topbar .nav-link, .dee-topbar .navbar-brand { color:#fff !important; }
        .dee-topbar .nav-link { padding:.45rem .65rem; font-weight:600; }
        .dee-topbar .nav-link:hover, .dee-topbar .dropdown.show > .nav-link { background:rgba(255,255,255,.12); border-radius:4px; }
        .dee-topbar .dropdown-menu { border:0; box-shadow:0 8px 18px rgba(0,0,0,.12); font-size:14px; }
        .dee-topbar .dropdown-item { padding:.55rem 1rem; }
        .dee-avatar { width:32px; height:32px; border-radius:50%; object-fit:cover; border:2px solid rgba(255,255,255,.75); background:#e9ecef; }
        .dee-avatar-placeholder { width:32px; height:32px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-weight:700; background:#e9ecef; color:#1f7f4c; border:2px solid rgba(255,255,255,.75); }
        .dee-user-name { max-width:210px; overflow:hidden; white-space:nowrap; text-overflow:ellipsis; display:inline-block; vertical-align:middle; }
        .dee-workflow { background:#e8f6f0; border-bottom:1px solid #cbeadb; color:#235c43; font-size:13px; padding:8px 15px; }
        .content-wrap { padding:18px 15px; }
        .card { border:0; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.07); }
        .dashboard-card { display:block; color:#222; text-decoration:none !important; height:100%; }
        .dashboard-card .card-body { padding:14px 12px; min-height:112px; }
        .dashboard-card .card { transition:.12s ease-in-out; border-left:4px solid #1f7f4c; }
        .dashboard-card:hover .card { transform:translateY(-2px); box-shadow:0 5px 16px rgba(0,0,0,.13); }
        .dashboard-card-count { font-size:24px; font-weight:800; line-height:1.1; }
        .dashboard-card-title { font-weight:700; font-size:14px; margin-top:6px; }
        .dashboard-card-text { font-size:12px; color:#6c757d; margin-top:4px; }
        .dash-border-success { border-left-color:#28a745 !important; }
        .dash-border-warning { border-left-color:#ffc107 !important; }
        .dash-border-info { border-left-color:#17a2b8 !important; }
        .dash-border-primary { border-left-color:#007bff !important; }
        .dash-border-danger { border-left-color:#dc3545 !important; }
        .dash-border-secondary { border-left-color:#6c757d !important; }
        .dash-border-dark { border-left-color:#343a40 !important; }
        @media (max-width: 991.98px) {
            .dee-user-name { max-width:100%; }
            .dee-topbar .navbar-nav.ml-auto { margin-top:8px; }
        }
    </style>
    @stack('styles')
</head>
<body>
<div id="app">
    @auth
        @php
            $user = Auth::user();

            // Safe employee relation access. Do not call employee methods on App\User.
            $employee = null;
            try {
                $employee = $user->employee;
            } catch (\Exception $e) {
                $employee = null;
            }

            $userHasRole = function ($roles) use ($user) {
                $roles = is_array($roles) ? $roles : [$roles];

                if (!$user) {
                    return false;
                }

                if (method_exists($user, 'hasAnyRole')) {
                    return $user->hasAnyRole($roles);
                }

                if (method_exists($user, 'hasRole')) {
                    foreach ($roles as $r) {
                        if ($user->hasRole($r)) {
                            return true;
                        }
                    }
                    return false;
                }

                $fallbackRole = isset($user->role) ? $user->role : 'employee';
                return in_array($fallbackRole, $roles);
            };

            $role = method_exists($user, 'roleLabel') ? $user->roleLabel() : ucwords(str_replace('_',' ', ($user->role ?? 'employee')));

            $isEmployeeAdmin = $userHasRole(['superuser','admin','college_admin','department_admin','director']);
            $canViewStore = $userHasRole(['superuser','admin','college_admin','department_admin','director','storekeeper']);
            $canManageStore = $userHasRole(['superuser','storekeeper']);

            // IMPORTANT: hasActiveCharge() belongs to Employee, not User.
            $hasStoreInchargeCharge =   $employee && method_exists($employee, 'hasActiveCharge') && $employee->hasActiveCharge('Store Incharge');

            $canSeeStorekeeperQueue = $userHasRole(['superuser','storekeeper']);
            $canSeeProgrammerQueue = $userHasRole(['superuser','programmer']) || $hasStoreInchargeCharge;
            $canSeeD4Queue = $userHasRole(['superuser','d4_seat']);

            $deptName = null;
            $collegeName = null;
            try {
                $deptName = $employee && $employee->department ? $employee->department->name : optional($user->department)->name;
                $collegeName = $employee && $employee->college ? $employee->college->name : optional($user->college)->name;
            } catch (\Exception $e) {
                $deptName = null;
                $collegeName = null;
            }
        @endphp

        <nav class="navbar navbar-expand-lg navbar-dark dee-topbar">
            <a class="navbar-brand" href="{{ route('home') }}">DEE IT Repair</a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Dashboard</a></li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="requestsMenu" role="button" data-toggle="dropdown">Requests</a>
                        <div class="dropdown-menu" aria-labelledby="requestsMenu">
                            <a class="dropdown-item" href="{{ route('repair-requests.index') }}">All Repair Requests</a>
                            <a class="dropdown-item" href="{{ route('repair-requests.create') }}">New Repair Request</a>

                            @if($canSeeStorekeeperQueue || $canSeeProgrammerQueue || $canSeeD4Queue)
                                <div class="dropdown-divider"></div>
                            @endif

                            @if($canSeeStorekeeperQueue)
                                <a class="dropdown-item" href="{{ route('repair-requests.index', ['handler' => 'storekeeper']) }}">Pending with Storekeeper</a>
                            @endif

                            @if($canSeeProgrammerQueue)
                                <a class="dropdown-item" href="{{ route('repair-requests.index', ['handler' => 'programmer']) }}">Verification Pending</a>
                            @endif

                            @if($canSeeD4Queue)
                                <a class="dropdown-item" href="{{ route('repair-requests.index', ['handler' => 'd4_seat']) }}">D-4 Manual Files</a>
                            @endif
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="assetsMenu" role="button" data-toggle="dropdown">Assets</a>
                        <div class="dropdown-menu" aria-labelledby="assetsMenu">
                            <a class="dropdown-item" href="{{ route('assets.index') }}">Assets</a>
                            @if($canManageStore)
                                <a class="dropdown-item" href="{{ route('assets.create') }}">Add Asset</a>
                            @endif
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="storeMenu" role="button" data-toggle="dropdown">Store</a>
                        <div class="dropdown-menu" aria-labelledby="storeMenu">
                            <a class="dropdown-item" href="{{ route('store-indents.index') }}">Store Indents</a>
                            <a class="dropdown-item" href="{{ route('store-indents.create') }}">New Indent</a>

                            @if($canViewStore)
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('store-items.index') }}">Store Stock</a>
                                <a class="dropdown-item" href="{{ route('store-items.index', ['low_stock' => 1]) }}">Low Stock Items</a>
                            @endif

                            @if($canManageStore)
                                <a class="dropdown-item" href="{{ route('store-items.create') }}">Add Stock Item</a>
                            @endif
                        </div>
                    </li>

                    @if($isEmployeeAdmin)
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminMenu" role="button" data-toggle="dropdown">Admin</a>
                            <div class="dropdown-menu" aria-labelledby="adminMenu">
                                <a class="dropdown-item" href="{{ route('employees.index') }}">Employees</a>
                                <a class="dropdown-item" href="{{ route('employees.create') }}">Add Employee</a>
                                <div class="dropdown-divider"></div>
                                @include('layouts.master_menu_links')
                                <a class="dropdown-item" href="{{ route('masters.vendors') }}">Vendors</a>
                                <a class="dropdown-item" href="{{ route('masters.problem-templates') }}">Problem Templates</a>
                                <a class="dropdown-item" href="{{ route('masters.repair-categories') }}">Repair Categories</a>
                                <a class="dropdown-item" href="{{ route('masters.routing-rules') }}">Routing Rules</a>
                            </div>
                        </li>
                    @endif
                </ul>

                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userMenu" role="button" data-toggle="dropdown">
                            @if($employee && $employee->photo_url)
                                <img src="{{ $employee->photo_url }}" alt="Profile" class="dee-avatar mr-2">
                            @else
                                <span class="dee-avatar-placeholder mr-2">{{ strtoupper(substr($user->name ?? 'U',0,1)) }}</span>
                            @endif
                            <span class="dee-user-name">{{ $user->name ?? 'User' }}</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userMenu">
                            <h6 class="dropdown-header">
                                {{ $role }}<br>
                                <small>{{ $deptName ?: $collegeName ?: 'Own Records' }}</small>
                            </h6>
                            <a class="dropdown-item" href="{{ route('profile.show') }}">My Profile / Photo</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('profile.password.change') }}">Change Password</a>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="dee-workflow">
            <strong>Workflow:</strong> Employee Request → Storekeeper Estimate PDF → Programmer / Store Incharge Verification → Proforma Print → Manual D-4 Process
        </div>
    @endauth

    @guest
        <nav class="navbar navbar-dark dee-topbar">
            <a class="navbar-brand" href="{{ url('/') }}">DEE IT Repair</a>
        </nav>
    @endguest

    <main class="content-wrap">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @yield('content')
    </main>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
@stack('scripts')
</body>
</html>
