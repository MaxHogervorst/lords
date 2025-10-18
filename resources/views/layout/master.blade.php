<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>GSRC Lords Bonnensysteem</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body>
    <div class="page">
        <!-- Sidebar -->
        <aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <h1 class="navbar-brand navbar-brand-autodark">
                    <a href="./">GSRC Lords</a>
                </h1>
                <div class="navbar-nav flex-row d-lg-none">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                            <i class="ti ti-user"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="{{ url('auth/logout') }}" class="dropdown-item">Logout</a>
                        </div>
                    </div>
                </div>
                <div class="collapse navbar-collapse" id="sidebar-menu">
                    <ul class="navbar-nav pt-lg-3">
                        <li class="nav-item">
                            <a class="nav-link {!! (Request::is('*/') ? 'active' : '') !!}" href="{{ url('/') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><i data-lucide="home"></i></span>
                                <span class="nav-link-title">Home</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {!! (Request::is('*member') ? 'active' : '') !!}" href="{{ url('member') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><i data-lucide="user"></i></span>
                                <span class="nav-link-title">Members</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {!! (Request::is('*group') ? 'active' : '') !!}" href="{{ url('group') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><i data-lucide="users"></i></span>
                                <span class="nav-link-title">Groups</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {!! (Request::is('*product') ? 'active' : '') !!}" href="{{ url('product') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><i data-lucide="glass-water"></i></span>
                                <span class="nav-link-title">Products</span>
                            </a>
                        </li>

                        @can('admin')
                            <li class="nav-item">
                                <span class="nav-link">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block"><i data-lucide="dollar-sign"></i></span>
                                    <span class="nav-link-title">Fiscus</span>
                                </span>
                            </li>
                            <li class="nav-item ms-3">
                                <a class="nav-link {!! (Request::is('*fiscus') && !Request::is('*fiscus/create') && !Request::is('*fiscus/edit') ? 'active' : '') !!}" href="{{ url('fiscus') }}">
                                    <span class="nav-link-title">View</span>
                                </a>
                            </li>
                            <li class="nav-item ms-3">
                                <a class="nav-link {!! (Request::is('*fiscus/create') ? 'active' : '') !!}" href="{{ url('fiscus/create') }}">
                                    <span class="nav-link-title">New</span>
                                </a>
                            </li>
                            <li class="nav-item ms-3">
                                <a class="nav-link {!! (Request::is('*fiscus/edit') ? 'active' : '') !!}" href="{{ url('fiscus/edit') }}">
                                    <span class="nav-link-title">Edit</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {!! (Request::is('*invoice') ? 'active' : '') !!}" href="{{ url('invoice') }}">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block"><i data-lucide="euro"></i></span>
                                    <span class="nav-link-title">Invoice</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {!! (Request::is('*sepa') ? 'active' : '') !!}" href="{{ url('sepa') }}">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block"><i data-lucide="settings"></i></span>
                                    <span class="nav-link-title">SEPA</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </div>
            </div>
        </aside>

        <!-- Header -->
        <header class="navbar navbar-expand-md d-print-none">
            <div class="container-xl">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="navbar-nav flex-row order-md-last">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                            <i class="ti ti-user"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="{{ url('auth/logout') }}" class="dropdown-item">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">
                    @if(!App\Models\InvoiceGroup::getCurrentMonth())
                        <div class="alert alert-danger" role="alert">No month selected, please contact the board</div>
                        @if(!Request::is('*invoice'))
                            <div class="alert alert-warning">Please select a month before continuing.</div>
                        @else
                            @yield('content')
                        @endif
                    @else
                        <div class="alert alert-info mb-3">Current Month: {{ App\Models\InvoiceGroup::getCurrentMonth()->name }}</div>
                        @yield('content')
                    @endif
                </div>
            </div>
        </div>
    </div>

    @yield('modal');
    @include('layout.notifications')
    @include('partials.confirm-modal')

    @yield('script')

</body>

</html>

