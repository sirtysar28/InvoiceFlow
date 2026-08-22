<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'InvoiceFlow') - InvoiceFlow</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('img/favicon.svg') }}">
    <link rel="shortcut icon" type="image/svg+xml" href="{{ asset('img/favicon.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #4338ca;
            --sidebar-bg: #1e1b4b;
            --sidebar-hover: #312e81;
        }
        * { font-family: 'Inter', sans-serif; }
        body {
            background: #f1f5f9;
            overflow-x: hidden;
        }
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 1000;
            transition: transform 0.3s;
            overflow-y: auto;
        }
        .sidebar .brand {
            padding: 20px;
            color: #fff;
            font-size: 24px;
            font-weight: 700;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar .brand i { color: #818cf8; }
        .sidebar .nav-link {
            color: #a5b4fc;
            padding: 12px 20px;
            margin: 4px 12px;
            border-radius: 8px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: var(--sidebar-hover);
            color: #fff;
        }
        .sidebar .nav-link i { font-size: 18px; width: 24px; }
        .main-content {
            margin-left: 260px;
            padding: 24px;
            min-height: 100vh;
            max-width: 100%;
        }
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 999;
            background: rgba(15, 23, 42, 0.45);
        }
        .page-title {
            font-size: clamp(1.35rem, 2vw, 1.75rem);
            line-height: 1.25;
            overflow-wrap: anywhere;
        }
        .page-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
        }
        .responsive-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: flex-end;
        }
        .card-stat {
            border: none;
            border-radius: 12px;
            transition: transform 0.2s;
        }
        .card-stat:hover { transform: translateY(-2px); }
        .card-stat .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #fff;
        }
        .badge-status {
            font-size: 0.8rem;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
        }
        .table-container {
            background: #fff;
            border-radius: 12px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            max-width: 100%;
        }
        .table { vertical-align: middle; }
        .table th {
            background: #f8fafc;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        .table td { overflow-wrap: anywhere; }
        .btn-action { padding: 6px 10px; }
        .filter-pills .btn {
            border-radius: 20px;
            padding: 8px 20px;
            font-weight: 500;
            margin-right: 8px;
        }
        .empty-state { padding: 60px; text-align: center; color: #94a3b8; }
        .empty-state i { font-size: 60px; color: #cbd5e1; margin-bottom: 16px; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .pagination {
            flex-wrap: wrap;
            gap: 4px;
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .sidebar.show + .sidebar-backdrop { display: block; }
            .main-content {
                margin-left: 0;
                padding: 18px;
            }
        }
        @media (max-width: 767.98px) {
            .responsive-card-table,
            .responsive-card-table thead,
            .responsive-card-table tbody,
            .responsive-card-table tfoot,
            .responsive-card-table tr,
            .responsive-card-table td {
                display: block;
                width: 100%;
            }
            .responsive-card-table thead {
                position: absolute;
                width: 1px;
                height: 1px;
                padding: 0;
                margin: -1px;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
                border: 0;
            }
            .responsive-card-table tbody tr,
            .responsive-card-table tfoot tr {
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                margin-bottom: 12px;
                padding: 10px 12px;
                background: #fff;
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            }
            .responsive-card-table td {
                border: 0;
                padding: 7px 0;
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 14px;
                text-align: right !important;
            }
            .responsive-card-table td::before {
                content: attr(data-label);
                flex: 0 0 42%;
                color: #64748b;
                font-weight: 600;
                text-align: left;
                text-transform: none;
            }
            .responsive-card-table td[data-label=""]::before,
            .responsive-card-table .actions-cell::before {
                display: none;
            }
            .responsive-card-table .actions-cell {
                justify-content: flex-end;
                border-top: 1px solid #e2e8f0;
                margin-top: 4px;
                padding-top: 10px;
            }
            .responsive-card-table .responsive-actions {
                width: 100%;
                justify-content: flex-end;
            }
            .invoice-items-table td {
                display: block;
                text-align: left !important;
            }
            .invoice-items-table td::before {
                display: block;
                margin-bottom: 6px;
            }
        }
        @media (max-width: 575.98px) {
            .main-content {
                padding: 14px 12px 28px;
            }
            .main-content > .d-flex.justify-content-between.align-items-center {
                align-items: flex-start !important;
                flex-wrap: wrap;
                gap: 12px;
            }
            .main-content > .d-flex.justify-content-between.align-items-center > .btn,
            .main-content > .d-flex.justify-content-between.align-items-center > a.btn,
            .page-actions {
                width: 100%;
            }
            .page-actions .btn,
            .main-content > .d-flex.justify-content-between.align-items-center > .btn,
            .main-content > .d-flex.justify-content-between.align-items-center > a.btn {
                flex: 1 1 auto;
            }
            .card-stat.p-4,
            .table-container.p-4 {
                padding: 1rem !important;
            }
            .table-container.p-3,
            .card-stat.p-3 {
                padding: 0.85rem !important;
            }
            .filter-pills {
                flex-wrap: nowrap !important;
                overflow-x: auto;
                padding-bottom: 6px;
                margin-inline: -12px;
                padding-inline: 12px;
                scrollbar-width: thin;
            }
            .filter-pills .btn {
                flex: 0 0 auto;
                margin-right: 0;
                white-space: nowrap;
            }
            .empty-state {
                padding: 36px 16px;
            }
            .modal-dialog {
                margin: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="brand">
            <i class="bi bi-receipt-cutoff"></i> InvoiceFlow
        </div>
        <div class="mt-3">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            @if(!auth()->user()?->isKlien())
            <a href="{{ route('invoices.index') }}" class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                <i class="bi bi-file-text"></i> Invoice
            </a>
            <a href="{{ route('invoices.create') }}" class="nav-link {{ request()->routeIs('invoices.create') ? 'active' : '' }}">
                <i class="bi bi-plus-circle"></i> Buat Invoice
            </a>
            <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Master Klien
            </a>
            @else
            <a href="{{ route('invoices.index') }}" class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                <i class="bi bi-file-text"></i> Invoice Saya
            </a>
            @endif

            @if(auth()->user()?->isAdmin())
            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i> Master User
            </a>
            @endif

            @if(!auth()->user()?->isKlien())
            <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                <i class="bi bi-bell"></i> Notifikasi
            </a>
            <a href="{{ route('templates.index') }}" class="nav-link {{ request()->routeIs('templates.*') ? 'active' : '' }}">
                <i class="bi bi-chat-square-text"></i> Template Pesan
            </a>
            <a href="{{ route('profile.business') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Profil Bisnis
            </a>
            @endif
            <hr style="border-color: rgba(255,255,255,0.1); margin: 20px 12px;">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start" style="cursor:pointer;">
                    <i class="bi bi-box-arrow-right"></i> Keluar
                </button>
            </form>
        </div>
    </nav>
    <div class="sidebar-backdrop" onclick="document.getElementById('sidebar').classList.remove('show')"></div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <button class="btn btn-light d-md-none" type="button" aria-label="Buka menu" onclick="document.getElementById('sidebar').classList.toggle('show')">
                <i class="bi bi-list"></i>
            </button>
            <div>
                <span class="text-muted">Halo,</span>
                <strong>{{ auth()->user()->name }}</strong>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
