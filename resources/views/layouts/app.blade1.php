<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'DEE Employee & IT Repair Management System') }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background:#f5f7f9; }
        .navbar { background:#198754; }
        .navbar a, .navbar-brand { color:#fff !important; }
        .dropdown-menu a { color:#212529 !important; }
        .card-header { background:#e8f5ee; font-weight:600; }
        .required:after { content:' *'; color:red; }
        .photo-thumb { width:50px; height:50px; object-fit:cover; border-radius:50%; }
        .timeline-line { border-left:3px solid #198754; padding-left:12px; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-md navbar-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('home') }}">DEE IT Repair</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navbarNav">
            @auth
            <ul class="navbar-nav mr-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('repair-requests.index') }}">Repair Requests</a></li>
                @if(Auth::user()->role == 'employee' || Auth::user()->isRole(['admin','college_admin','department_admin','director','storekeeper']))
                    <li class="nav-item"><a class="nav-link" href="{{ route('repair-requests.create') }}">New Repair</a></li>
                @endif
                <li class="nav-item"><a class="nav-link" href="{{ route('store-indents.index') }}">Store Indents</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('assets.index') }}">Assets</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('store-items.index') }}">Store Stock</a></li>
                @if(Auth::user()->isRole(['admin','college_admin','department_admin','director']))
                    <li class="nav-item"><a class="nav-link" href="{{ route('employees.index') }}">Employees</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">Masters</a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('masters.colleges') }}">Colleges / Directorates</a>
                            <a class="dropdown-item" href="{{ route('masters.departments') }}">Departments / Offices / KVKs</a>
                            <a class="dropdown-item" href="{{ route('masters.designations') }}">Designations</a>
                            <a class="dropdown-item" href="{{ route('masters.sections') }}">Sections</a>
                            <a class="dropdown-item" href="{{ route('masters.vendors') }}">Vendors</a>
                            <a class="dropdown-item" href="{{ route('masters.repair-categories') }}">Repair Categories</a>
                            <a class="dropdown-item" href="{{ route('masters.problem-templates') }}">Default Problems</a>
                            <a class="dropdown-item" href="{{ route('masters.routing-rules') }}">Routing Rules</a>
                        </div>
                    </li>
                @endif
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item"><span class="nav-link">{{ Auth::user()->name }} ({{ ucwords(str_replace('_',' ', Auth::user()->role)) }}) - {{ \App\Support\AccessScope::scopeLabel() }}</span></li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
                </li>
            </ul>
            @endauth
        </div>
    </div>
</nav>
<div class="container-fluid">
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @yield('content')
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
@stack('scripts')
</body>
</html>
