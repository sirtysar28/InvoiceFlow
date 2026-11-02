<!DOCTYPE html>
<html lang="id">
<head>
    <?php
        // Profil bisnis: dipakai untuk favicon & logo header — SCOPE PER PERUSAHAAN (SaaS)
        $headerProfile = auth()->check()
            ? \App\Models\BusinessProfile::current()
            : null;

        // ===== Notifikasi header =====
        $headerNotifs = [];
        $hu = auth()->user();
        if ($hu && $hu->isKlien()) {
            // Klien: invoice-nya sendiri yang belum dibayar
            \App\Models\Invoice::where('client_email', $hu->email)
                ->where('status', 'unpaid')->orderBy('due_date')->limit(6)
                ->get()
                ->each(function ($inv) use (&$headerNotifs) {
                    $lewat = $inv->due_date && $inv->due_date->isPast();
                    $headerNotifs[] = [
                        'icon'  => $lewat ? 'exclamation-triangle' : 'receipt-cutoff',
                        'color' => $lewat ? '#ef4444' : '#f59e0b',
                        'title' => $inv->invoice_number . ($lewat ? ' lewat jatuh tempo' : ' belum dibayar'),
                        'desc'  => 'Jatuh tempo ' . optional($inv->due_date)->format('d/m/Y') . ' · Rp ' . number_format($inv->total, 0, ',', '.'),
                        'url'   => route('invoices.show', $inv->id),
                    ];
                });
        } elseif ($hu) {
            $seesAll = $hu->isAdmin() || $hu->isFinance();

            // Pembayaran menunggu verifikasi (paling urgent → di depan)
            if ($hu->canVerifyPayments()) {
                $pendingPay = \App\Models\Payment::where('status', 'pending')->count();
                if ($pendingPay > 0) {
                    $headerNotifs[] = [
                        'icon'  => 'credit-card',
                        'color' => '#6366f1',
                        'title' => $pendingPay . ' pembayaran menunggu verifikasi',
                        'desc'  => 'Segera cek menu Pembayaran',
                        'url'   => route('payments.index', ['status' => 'pending']),
                    ];
                }
            }

            // Lewat jatuh tempo
            $q = \App\Models\Invoice::where('status', 'unpaid')->whereDate('due_date', '<', today());
            if (!$seesAll) { $q->where('user_id', $hu->id); }
            $q->orderBy('due_date')->limit(4)->get()
                ->each(function ($inv) use (&$headerNotifs) {
                    $headerNotifs[] = [
                        'icon'  => 'exclamation-triangle',
                        'color' => '#ef4444',
                        'title' => $inv->invoice_number . ' lewat jatuh tempo',
                        'desc'  => $inv->client_name . ' · Rp ' . number_format($inv->total, 0, ',', '.'),
                        'url'   => route('invoices.show', $inv->id),
                    ];
                });

            // Jatuh tempo ≤ 3 hari
            $q2 = \App\Models\Invoice::where('status', 'unpaid')
                ->whereBetween('due_date', [today(), today()->addDays(3)]);
            if (!$seesAll) { $q2->where('user_id', $hu->id); }
            $q2->orderBy('due_date')->limit(4)->get()
                ->each(function ($inv) use (&$headerNotifs) {
                    $headerNotifs[] = [
                        'icon'  => 'alarm',
                        'color' => '#f59e0b',
                        'title' => $inv->invoice_number . ' jatuh tempo ' . $inv->due_date->format('d/m/Y'),
                        'desc'  => 'Sisa ' . today()->diffInDays($inv->due_date) . ' hari · ' . $inv->client_name,
                        'url'   => route('invoices.show', $inv->id),
                    ];
                });
        }
        $headerNotifCount = count($headerNotifs);
    ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'InvoiceFlow'); ?> · InvoiceFlow</title>

    
    <?php if($headerProfile?->logo): ?>
        <link rel="icon" type="image/png" href="<?php echo e(asset('storage/' . $headerProfile->logo)); ?>">
        <link rel="shortcut icon" type="image/png" href="<?php echo e(asset('storage/' . $headerProfile->logo)); ?>">
        <link rel="apple-touch-icon" href="<?php echo e(asset('storage/' . $headerProfile->logo)); ?>">
    <?php else: ?>
        <link rel="icon" type="image/svg+xml" href="<?php echo e(asset('img/favicon.svg')); ?>">
        <link rel="shortcut icon" type="image/svg+xml" href="<?php echo e(asset('img/favicon.svg')); ?>">
    <?php endif; ?>

    
    <link rel="manifest" href="<?php echo e(route('pwa.manifest')); ?>">
    <meta name="theme-color" content="#6366f1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?php echo e($headerProfile?->business_name ?: config('app.name')); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #4338ca;
            --sidebar-bg: #1e1b4b;
            --sidebar-hover: #312e81;
            --grad-brand: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            --grad-success: linear-gradient(135deg, #16a34a 0%, #4ade80 100%);
            --grad-warning: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            --grad-danger: linear-gradient(135deg, #ef4444 0%, #fb7185 100%);
            --shadow-sm: 0 1px 3px rgba(15, 23, 42, .06), 0 1px 2px rgba(15, 23, 42, .04);
            --shadow-md: 0 10px 28px -8px rgba(15, 23, 42, .14);
        }
        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        h1, h2, h3, h4, h5, h6,
        .brand, .page-title, .font-header {
            font-family: 'Sora', 'Inter', sans-serif !important;
            letter-spacing: -0.015em;
        }
        body {
            background: #f4f6fb;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
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
            transition: transform 0.3s, width 0.3s ease;
            overflow-y: auto;
            overflow-x: hidden;
        }
        .sidebar .brand {
            padding: 20px;
            color: #fff;
            font-size: 24px;
            font-weight: 700;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar .brand i { color: #818cf8; }
        .sidebar .nav-section {
            padding: 16px 24px 6px;
            font-family: 'Sora', 'Inter', sans-serif;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: rgba(165, 180, 252, 0.55);
        }
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
            transition: margin-left 0.3s ease;
        }
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 999;
            background: rgba(15, 23, 42, 0.45);
        }
        /* ===== Sidebar Toggle (Collapse) ===== */
        .sidebar-toggle {
            flex: 0 0 34px;
            width: 34px;
            height: 34px;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .14);
            color: #a5b4fc;
            border-radius: 10px;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all .2s ease;
        }
        .sidebar-toggle:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }
        .sidebar.collapsed .sidebar-toggle i {
            transform: rotate(90deg);
            transition: transform .3s ease;
        }
        @media (min-width: 769px) {
            /* Mode collapsed: icon saja */
            body.sidebar-collapsed .main-content { margin-left: 78px; }
            .sidebar.collapsed { width: 78px; }
            .sidebar.collapsed .brand {
                flex-direction: column;
                gap: 10px;
                padding: 18px 10px 14px;
                font-size: 22px;
            }
            .sidebar.collapsed .brand-text { display: none; }
            .sidebar.collapsed .nav-section {
                height: 1px;
                padding: 0;
                margin: 12px 16px 6px;
                background: rgba(255, 255, 255, .14);
                font-size: 0;
                color: transparent;
                overflow: hidden;
            }
            .sidebar.collapsed .nav-link {
                justify-content: center;
                padding: 12px 0;
                margin: 4px 10px;
                text-align: center !important;
            }
            .sidebar.collapsed .nav-link .link-text { display: none; }
            .sidebar.collapsed hr { margin: 14px 12px !important; }
            /* Tooltip saat collapsed */
            .sidebar.collapsed .nav-link { position: relative; }
            .sidebar.collapsed .nav-link::after {
                content: attr(data-title);
                position: absolute;
                left: calc(100% + 14px);
                top: 50%;
                transform: translateY(-50%) translateX(-6px);
                background: #0f172a;
                color: #fff;
                padding: 6px 12px;
                border-radius: 8px;
                font-size: .78rem;
                font-weight: 500;
                font-family: 'Inter', sans-serif;
                white-space: nowrap;
                opacity: 0;
                pointer-events: none;
                transition: opacity .18s ease, transform .18s ease;
                z-index: 1100;
                box-shadow: 0 8px 20px -6px rgba(0, 0, 0, .45);
            }
            .sidebar.collapsed .nav-link:hover::after {
                opacity: 1;
                transform: translateY(-50%) translateX(0);
            }
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
            border: 1px solid rgba(226, 232, 240, .9);
            border-radius: 16px;
            background: #fff;
            box-shadow: var(--shadow-sm);
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }
        .card-stat:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-color: transparent;
        }
        .card-stat .icon-box {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #fff;
            box-shadow: 0 8px 18px -6px rgba(15, 23, 42, .28);
        }
        .btn-brand {
            background: var(--grad-brand);
            border: none;
            padding: .55rem 1.15rem;
            border-radius: 12px;
            font-weight: 600;
            box-shadow: 0 8px 18px -6px rgba(99, 102, 241, .55);
            transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
        }
        .btn-brand:hover {
            filter: brightness(1.07);
            transform: translateY(-1px);
            box-shadow: 0 12px 24px -8px rgba(99, 102, 241, .65);
        }
        /* Upgrade otomatis tombol lama dengan warna inline #4f46e5 */
        .btn[style*="background:#4f46e5"],
        .btn[style*="background: #4f46e5"] {
            background: var(--grad-brand) !important;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            box-shadow: 0 8px 18px -6px rgba(99, 102, 241, .55);
        }
        .badge-status {
            font-size: 0.75rem;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            letter-spacing: .01em;
        }
        .table-container {
            background: #fff;
            border-radius: 16px;
            padding: 0;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(226, 232, 240, .7);
            max-width: 100%;
        }
        .table { vertical-align: middle; }
        .table th {
            background: #f8fafc;
            font-weight: 600;
            font-family: 'Sora', 'Inter', sans-serif;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            white-space: nowrap;
        }
        .table tbody tr { transition: background .15s ease; }
        .table-hover tbody tr:hover { background: #f8faff; }
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
            border-radius: 12px;
        }
        .modal-content {
            border: none;
            border-radius: 18px;
            box-shadow: 0 24px 64px -16px rgba(15, 23, 42, .35);
        }
        .modal-header { border-bottom: 1px solid #eef2f7; }
        .modal-footer { border-top: 1px solid #eef2f7; }
        .pagination {
            flex-wrap: wrap;
            gap: 4px;
        }
        /* ===== Pagination Kompak (panah kecil rapi) ===== */
        .pagination .page-item { margin: 0; }
        .pagination .page-link {
            min-width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 8px;
            font-size: .875rem;
            font-weight: 600;
            line-height: 1;
            color: #475569;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px !important;
            transition: all .15s ease;
        }
        .pagination .page-link:hover {
            color: #4f46e5;
            background: #eef2ff;
            border-color: #c7d2fe;
        }
        .pagination .page-item.active .page-link {
            color: #fff;
            background: linear-gradient(135deg, #4f46e5, #6d28d9);
            border-color: transparent;
            box-shadow: 0 6px 14px -6px rgba(79, 70, 229, .6);
        }
        .pagination .page-item.disabled .page-link {
            color: #cbd5e1;
            background: #f8fafc;
            border-color: #e2e8f0;
        }
        /* Panah prev/next: kecil & tegas */
        .pagination .page-link[rel="prev"],
        .pagination .page-link[rel="next"],
        .pagination .page-item.disabled .page-link[aria-hidden="true"] {
            font-size: 1.05rem;
            line-height: 0;
            padding-bottom: 2px;
        }
        /* Cegah SVG/icon apapun di dalam pagination membesar */
        .pagination svg { width: 14px; height: 14px; }
        .pagination img { width: auto; height: auto; }
        /* ===== WhatsApp Floating Chat ===== */
        .wa-fab {
            position: fixed;
            right: 22px;
            bottom: 24px;
            z-index: 998;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
            color: #fff;
            font-size: 1.7rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: 0 12px 28px -8px rgba(18, 140, 126, .65);
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .wa-fab:hover {
            color: #fff;
            transform: scale(1.08) translateY(-2px);
            box-shadow: 0 16px 34px -10px rgba(18, 140, 126, .75);
        }
        .wa-fab::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: rgba(37, 211, 102, .5);
            z-index: -1;
            animation: waPulse 2.4s ease-out infinite;
        }
        @keyframes waPulse {
            0%   { transform: scale(1); opacity: .65; }
            70%  { transform: scale(1.7); opacity: 0; }
            100% { transform: scale(1.7); opacity: 0; }
        }
        .wa-tip {
            position: absolute;
            right: calc(100% + 12px);
            top: 50%;
            transform: translateY(-50%) translateX(6px);
            background: #0f172a;
            color: #fff;
            font-size: .75rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            white-space: nowrap;
            padding: 7px 13px;
            border-radius: 10px;
            opacity: 0;
            pointer-events: none;
            transition: opacity .2s ease, transform .2s ease;
            box-shadow: 0 10px 24px -8px rgba(0, 0, 0, .45);
        }
        .wa-fab:hover .wa-tip {
            opacity: 1;
            transform: translateY(-50%) translateX(0);
        }

        /* ===== Header Actions: ikon notifikasi + dropdown profil (mobile & desktop) ===== */
        .ha-cluster { position: relative; }
        .ha-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #fff;
            border: 1px solid #e2e8f0;
            color: #334155;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            box-shadow: var(--shadow-sm);
            position: relative;
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .ha-btn:hover { transform: translateY(-1px); box-shadow: var(--shadow-md); }
        .ha-btn::after { display: none !important; }
        .ha-btn.show { border-color: var(--primary); }
        .ha-bell { color: #475569; }
        .ha-badge {
            position: absolute;
            top: -4px;
            right: -6px;
            min-width: 18px;
            height: 18px;
            border-radius: 9px;
            background: #ef4444;
            color: #fff;
            font-size: .64rem;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            border: 2px solid #fff;
            pointer-events: none;
        }
        .ha-avatar {
            background: var(--grad-brand);
            color: #fff;
            border: none;
            font-family: 'Sora', sans-serif;
            font-weight: 700;
            font-size: .9rem;
            box-shadow: 0 6px 14px -6px rgba(99, 102, 241, .55);
        }
        .ha-menu {
            min-width: 310px;
            max-width: calc(100vw - 24px);
            border: 1px solid #e8edf4;
            border-radius: 16px;
            box-shadow: 0 18px 44px -12px rgba(15, 23, 42, .22);
            padding: 8px;
            margin-top: 10px !important;
        }
        .ha-menu-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 10px 10px;
            font-family: 'Sora', 'Inter', sans-serif;
            font-weight: 700;
            font-size: .92rem;
            color: #0f172a;
            border-bottom: 1px solid #eef2f7;
            margin-bottom: 6px;
        }
        .ha-notif-menu { max-height: min(420px, 70vh); overflow-y: auto; }
        .ha-notif-item {
            display: flex !important;
            gap: 10px;
            align-items: flex-start;
            padding: 10px !important;
            border-radius: 12px;
            white-space: normal;
            line-height: 1.3;
        }
        .ha-notif-item:hover { background: #f4f6fb; }
        .ha-notif-icon {
            flex: 0 0 34px;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .95rem;
        }
        .ha-notif-body { min-width: 0; display: flex; flex-direction: column; gap: 2px; }
        .ha-notif-title {
            font-weight: 600;
            font-size: .84rem;
            color: #0f172a;
        }
        .ha-notif-desc { font-size: .75rem; color: #64748b; }
        .ha-notif-empty {
            text-align: center;
            color: #94a3b8;
            padding: 26px 12px;
            font-size: .84rem;
        }
        .ha-user-head {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eef2f7;
            margin-bottom: 6px;
        }
        .ha-user-avatar {
            flex: 0 0 42px;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: var(--grad-brand);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-family: 'Sora', sans-serif;
            font-weight: 700;
        }
        .ha-item {
            border-radius: 10px;
            padding: 8px 10px !important;
            font-size: .875rem;
            display: flex;
            align-items: center;
        }
        .ha-item:hover { background: #f4f6fb; }
        .ha-item.text-danger:hover { background: #fef2f2; }
        .ha-item i { width: 20px; text-align: center; }

        /* ===== Mobile Header & Bottom Nav ===== */
        .mobile-header,
        .bottom-nav { display: none; }
        @media (max-width: 768px) {
            .sidebar-toggle { display: none; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .sidebar.show + .sidebar-backdrop { display: block; }
            .main-content {
                margin-left: 0;
                padding: 18px;
            }
            body { padding-bottom: 78px; }

            /* --- Mobile Top Header --- */
            .mobile-header {
                display: flex;
                position: sticky;
                top: 0;
                z-index: 1040;
                align-items: center;
                gap: 12px;
                padding: 10px 14px;
                margin: -18px -18px 16px;
                background: rgba(244, 246, 251, .92);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border-bottom: 1px solid rgba(226, 232, 240, .9);
            }
            .mh-burger {
                flex: 0 0 40px;
                width: 40px;
                height: 40px;
                border-radius: 12px;
                background: #fff;
                border: 1px solid #e2e8f0;
                color: #0f172a;
                font-size: 1.25rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                box-shadow: var(--shadow-sm);
            }
            .mh-brand {
                display: flex;
                align-items: center;
                gap: 10px;
                text-decoration: none;
                color: #0f172a;
                min-width: 0;
            }
            .mh-logo {
                width: 36px;
                height: 36px;
                border-radius: 11px;
                object-fit: cover;
                border: 1px solid #e2e8f0;
                background: #fff;
                flex: 0 0 36px;
                padding: 2px;
            }
            .mh-logo-fallback {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: var(--primary);
                font-size: 1.1rem;
                padding: 0;
            }
            .mh-brand { justify-content: center; }

            /* --- Bottom Navigation (5 icon) --- */
            .bottom-nav {
                display: flex;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                z-index: 1045;
                background: rgba(255, 255, 255, .95);
                backdrop-filter: blur(14px);
                -webkit-backdrop-filter: blur(14px);
                border-top: 1px solid #e2e8f0;
                box-shadow: 0 -10px 30px -14px rgba(15, 23, 42, .25);
                padding: 6px 6px calc(7px + env(safe-area-inset-bottom, 0px));
            }
            .bn-item {
                flex: 1 1 0;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: flex-end;
                gap: 3px;
                min-width: 0;
                padding: 4px 2px;
                text-decoration: none;
                color: #94a3b8;
                font-size: .64rem;
                font-weight: 600;
            }
            .bn-item i { font-size: 1.3rem; line-height: 1; }
            .bn-item.active { color: var(--primary); }
            .bn-fab {
                width: 46px;
                height: 46px;
                border-radius: 50%;
                background: var(--grad-brand);
                color: #fff;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 1.25rem;
                margin-top: -26px;
                border: 4px solid #f4f6fb;
                box-shadow: 0 10px 22px -8px rgba(99, 102, 241, .6);
                transition: transform .2s ease;
            }
            .bn-create:active .bn-fab { transform: scale(.92); }
            .bn-form { display: flex; flex: 1 1 0; min-width: 0; }
            .bn-btn {
                width: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: flex-end;
                gap: 3px;
                padding: 4px 2px;
                background: none;
                border: 0;
                color: #94a3b8;
                font-size: .64rem;
                font-weight: 600;
            }
            .bn-btn i { font-size: 1.3rem; line-height: 1; }

            /* --- WA Fab: posisi di atas bottom nav (mobile) --- */
            .wa-fab {
                right: 14px;
                bottom: calc(90px + env(safe-area-inset-bottom, 0px));
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
            }
            .wa-tip { display: none; }
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
            .mobile-header {
                margin: -14px -12px 14px;
                padding: 8px 12px;
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
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="brand d-flex align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-receipt-cutoff"></i> <span class="brand-text">InvoiceFlow</span>
            </div>
            <button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Perkecil / perbesar menu" title="Perkecil / perbesar menu">
                <i class="bi bi-list"></i>
            </button>
        </div>
        <div class="mt-2 pb-4">
            <?php $u = auth()->user(); ?>

            <?php if($u?->isSuperAdmin()): ?>
            <!-- ===== SUPER ADMIN ===== -->
            <div class="nav-section">Super Admin</div>
            <a href="<?php echo e(route('superadmin.monitoring')); ?>" class="nav-link <?php echo e(request()->routeIs('superadmin.*') ? 'active' : ''); ?>" data-title="Monitoring Tenant">
                <i class="bi bi-radar"></i> <span class="link-text">Monitoring Tenant</span>
            </a>
            <a href="<?php echo e(route('companies.index')); ?>" class="nav-link <?php echo e(request()->routeIs('companies.*') ? 'active' : ''); ?>" data-title="Manajemen Perusahaan">
                <i class="bi bi-building-gear"></i> <span class="link-text">Perusahaan</span>
            </a>
            <a href="<?php echo e(route('packages.index')); ?>" class="nav-link <?php echo e(request()->routeIs('packages.*') ? 'active' : ''); ?>" data-title="Paket Subscription">
                <i class="bi bi-box-seam"></i> <span class="link-text">Paket Subscription</span>
            </a>
            <a href="<?php echo e(route('system-settings.index')); ?>" class="nav-link <?php echo e(request()->routeIs('system-settings.*') ? 'active' : ''); ?>" data-title="Pengaturan Sistem">
                <i class="bi bi-sliders"></i> <span class="link-text">Pengaturan Sistem</span>
            </a>
            <a href="<?php echo e(route('landing.manage')); ?>" class="nav-link <?php echo e(request()->routeIs('landing.manage') ? 'active' : ''); ?>" data-title="Kelola Landing Page">
                <i class="bi bi-window-desktop"></i> <span class="link-text">Kelola Landing Page</span>
            </a>
            <?php endif; ?>

            <!-- ===== MENU UTAMA ===== -->
            <div class="nav-section">Menu Utama</div>
            <a href="<?php echo e(route('dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>" data-title="Dashboard">
                <i class="bi bi-speedometer2"></i> <span class="link-text">Dashboard</span>
            </a>

            <?php if($u?->canManageCustomers()): ?>
            <a href="<?php echo e(route('clients.index')); ?>" class="nav-link <?php echo e(request()->routeIs('clients.*') ? 'active' : ''); ?>" data-title="Customer">
                <i class="bi bi-people"></i> <span class="link-text">Customer</span>
            </a>
            <?php endif; ?>

            <?php if($u?->canManageProducts()): ?>
            <a href="<?php echo e(route('products.index')); ?>" class="nav-link <?php echo e(request()->routeIs('products.*') ? 'active' : ''); ?>" data-title="Produk & Jasa">
                <i class="bi bi-box-seam"></i> <span class="link-text">Produk/Jasa</span>
            </a>
            <?php endif; ?>

            <?php if($u?->canManageQuotations()): ?>
            <a href="<?php echo e(route('quotations.index')); ?>" class="nav-link <?php echo e(request()->routeIs('quotations.*') ? 'active' : ''); ?>" data-title="Quotation">
                <i class="bi bi-file-earmark-text"></i> <span class="link-text">Quotation</span>
            </a>
            <?php endif; ?>

            <?php if($u?->canManageOrders()): ?>
            <a href="<?php echo e(route('orders.index')); ?>" class="nav-link <?php echo e(request()->routeIs('orders.*') ? 'active' : ''); ?>" data-title="Sales Order">
                <i class="bi bi-clipboard-check"></i> <span class="link-text">Order</span>
            </a>
            <?php endif; ?>

            <a href="<?php echo e(route('invoices.index')); ?>" class="nav-link <?php echo e(request()->routeIs('invoices.index', 'invoices.show', 'invoices.edit', 'invoices.public') ? 'active' : ''); ?>" data-title="<?php echo e($u?->isKlien() ? 'Invoice Saya' : 'Invoice'); ?>">
                <i class="bi bi-receipt-cutoff"></i> <span class="link-text"><?php echo e($u?->isKlien() ? 'Invoice Saya' : 'Invoice'); ?></span>
            </a>

            <?php if($u?->canManageInvoices() && !$u?->isKlien()): ?>
            <a href="<?php echo e(route('invoices.create')); ?>" class="nav-link <?php echo e(request()->routeIs('invoices.create') ? 'active' : ''); ?>" data-title="Buat Invoice">
                <i class="bi bi-plus-circle"></i> <span class="link-text">Buat Invoice</span>
            </a>
            <?php endif; ?>

            <?php if($u?->canVerifyPayments()): ?>
            <a href="<?php echo e(route('payments.index')); ?>" class="nav-link <?php echo e(request()->routeIs('payments.*') ? 'active' : ''); ?>" data-title="Pembayaran & Verifikasi">
                <i class="bi bi-credit-card-2-front"></i> <span class="link-text">Pembayaran</span>
            </a>
            <?php endif; ?>

            <?php if($u?->canViewReports()): ?>
            <a href="<?php echo e(route('reports.index')); ?>" class="nav-link <?php echo e(request()->routeIs('reports.*') ? 'active' : ''); ?>" data-title="Laporan">
                <i class="bi bi-graph-up-arrow"></i> <span class="link-text">Laporan</span>
            </a>
            <?php endif; ?>

            <?php if($u?->isKlien()): ?>
            <a href="<?php echo e(route('customers.history')); ?>" class="nav-link <?php echo e(request()->routeIs('customers.*') ? 'active' : ''); ?>" data-title="Riwayat Transaksi">
                <i class="bi bi-clock-history"></i> <span class="link-text">Riwayat Transaksi</span>
            </a>
            <?php endif; ?>

            <?php if($u?->isAdmin()): ?>
            <!-- ===== MASTER DATA ===== -->
            <div class="nav-section">Master Data</div>
            <a href="<?php echo e(route('users.index')); ?>" class="nav-link <?php echo e(request()->routeIs('users.*') ? 'active' : ''); ?>" data-title="Master User">
                <i class="bi bi-person-badge"></i> <span class="link-text">Master User</span>
            </a>
            <?php endif; ?>

            <?php if($u?->canManageSettings()): ?>
            <!-- ===== PENGATURAN ===== -->
            <div class="nav-section">Pengaturan</div>
            <a href="<?php echo e(route('notifications.index')); ?>" class="nav-link <?php echo e(request()->routeIs('notifications.*') ? 'active' : ''); ?>" data-title="Notifikasi & Pengingat">
                <i class="bi bi-bell"></i> <span class="link-text">Notifikasi &amp; Pengingat</span>
            </a>

            <?php if($u?->isSuperAdmin() || $u?->role === 'admin'): ?>
            <a href="<?php echo e(route('notifications.index')); ?>#smtp" class="nav-link" data-title="Pengaturan SMTP">
                <i class="bi bi-envelope-gear"></i> <span class="link-text">Pengaturan SMTP</span>
            </a>
            <?php endif; ?>

            <a href="<?php echo e(route('templates.index')); ?>" class="nav-link <?php echo e(request()->routeIs('templates.*') ? 'active' : ''); ?>" data-title="Template Pesan">
                <i class="bi bi-chat-square-text"></i> <span class="link-text">Template Pesan</span>
            </a>
            <a href="<?php echo e(route('profile.business')); ?>" class="nav-link <?php echo e(request()->routeIs('profile.*') ? 'active' : ''); ?>" data-title="Profil Bisnis">
                <i class="bi bi-building"></i> <span class="link-text">Profil Bisnis</span>
            </a>

            <a href="<?php echo e(route('activity-logs.index')); ?>" class="nav-link <?php echo e(request()->routeIs('activity-logs.*') ? 'active' : ''); ?>" data-title="Aktivitas Log">
                <i class="bi bi-clock-history"></i> <span class="link-text">Aktivitas Log</span>
            </a>
            <?php endif; ?>

            <hr style="border-color: rgba(255,255,255,0.1); margin: 20px 12px;">
            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="nav-link border-0 bg-transparent w-100" style="cursor:pointer;" data-title="Keluar">
                    <i class="bi bi-box-arrow-right"></i> <span class="link-text">Keluar</span>
                </button>
            </form>
        </div>
    </nav>
    <div class="sidebar-backdrop" onclick="document.getElementById('sidebar').classList.remove('show')"></div>

    <!-- Main Content -->
    <div class="main-content d-flex flex-column">
        <?php
            // Logo & nama bisnis untuk header mobile (dari Profil Bisnis)
            $mobileProfile = auth()->check()
                ? \App\Models\BusinessProfile::where('user_id', auth()->id())->first()
                : null;
        ?>

        <!-- Mobile Header: hamburger (kiri) + logo perusahaan (tanpa teks) + notif & avatar -->
        <header class="mobile-header">
            <button class="mh-burger" type="button" aria-label="Buka menu" onclick="document.getElementById('sidebar').classList.toggle('show')">
                <i class="bi bi-list"></i>
            </button>
            <a href="<?php echo e(route('dashboard')); ?>" class="mh-brand" title="<?php echo e($mobileProfile?->business_name ?? 'InvoiceFlow'); ?>">
                <?php if($mobileProfile?->logo): ?>
                    <img src="<?php echo e(asset('storage/' . $mobileProfile->logo)); ?>" alt="<?php echo e($mobileProfile->business_name ?? 'Logo'); ?>" class="mh-logo">
                <?php else: ?>
                    <span class="mh-logo mh-logo-fallback"><i class="bi bi-receipt-cutoff"></i></span>
                <?php endif; ?>
            </a>
            <div class="ms-auto">
                <?php echo $__env->make('layouts.partials.header-actions', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
        </header>

        <!-- Desktop greeting + aksi header -->
        <div class="d-none d-md-flex justify-content-end align-items-center mb-4 gap-3">
            <div>
                <span class="text-muted">Halo,</span>
                <strong><?php echo e(auth()->user()->name); ?></strong>
            </div>
            <?php echo $__env->make('layouts.partials.header-actions', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

        <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php echo $__env->yieldContent('content'); ?>

        <footer class="app-footer mt-auto pt-4">
            <div class="text-center" style="font-size:.78rem; color:#94a3b8; padding:16px 0 4px; border-top:1px solid #e2e8f0; margin-top:24px;">
                © <?php echo e(date('Y')); ?> InvoiceFlow · Developed by
                <a href="https://digimagine.web.id" target="_blank" rel="noopener noreferrer"
                   style="color:#4f46e5; font-weight:600; text-decoration:none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">Digital Imagine Indonesia</a>
            </div>
        </footer>
    </div>

    <!-- Mobile Bottom Navigation (5 icon — hanya login & mobile, per role) -->
    <?php if(auth()->guard()->check()): ?>
    <?php $u = auth()->user(); ?>
    <nav class="bottom-nav">
        <?php if($u?->isKlien()): ?>
            <a href="<?php echo e(route('dashboard')); ?>" class="bn-item <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                <i class="bi bi-speedometer2"></i><span>Home</span>
            </a>
            <a href="<?php echo e(route('invoices.index')); ?>" class="bn-item <?php echo e(request()->routeIs('invoices.*') ? 'active' : ''); ?>">
                <i class="bi bi-receipt-cutoff"></i><span>Invoice</span>
            </a>
            <a href="<?php echo e(url('/')); ?>" class="bn-item bn-create">
                <span class="bn-fab"><i class="bi bi-house"></i></span><span>Beranda</span>
            </a>
            <a href="<?php echo e(route('customers.history')); ?>" class="bn-item <?php echo e(request()->routeIs('customers.*') ? 'active' : ''); ?>">
                <i class="bi bi-clock-history"></i><span>Riwayat</span>
            </a>
            <form method="POST" action="<?php echo e(route('logout')); ?>" class="bn-form">
                <?php echo csrf_field(); ?>
                <button type="submit" class="bn-btn"><i class="bi bi-box-arrow-right"></i><span>Keluar</span></button>
            </form>
        <?php elseif($u?->isSales()): ?>
            <a href="<?php echo e(route('dashboard')); ?>" class="bn-item <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                <i class="bi bi-speedometer2"></i><span>Home</span>
            </a>
            <a href="<?php echo e(route('clients.index')); ?>" class="bn-item <?php echo e(request()->routeIs('clients.*') ? 'active' : ''); ?>">
                <i class="bi bi-people"></i><span>Customer</span>
            </a>
            <a href="<?php echo e(route('quotations.create')); ?>" class="bn-item bn-create <?php echo e(request()->routeIs('quotations.*') ? 'active' : ''); ?>">
                <span class="bn-fab"><i class="bi bi-plus-lg"></i></span><span>Quote</span>
            </a>
            <a href="<?php echo e(route('orders.index')); ?>" class="bn-item <?php echo e(request()->routeIs('orders.*') ? 'active' : ''); ?>">
                <i class="bi bi-clipboard-check"></i><span>Order</span>
            </a>
            <a href="<?php echo e(route('invoices.index')); ?>" class="bn-item <?php echo e(request()->routeIs('invoices.*') ? 'active' : ''); ?>">
                <i class="bi bi-receipt-cutoff"></i><span>Invoice</span>
            </a>
        <?php elseif($u?->isFinance()): ?>
            <a href="<?php echo e(route('dashboard')); ?>" class="bn-item <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                <i class="bi bi-speedometer2"></i><span>Home</span>
            </a>
            <a href="<?php echo e(route('invoices.index')); ?>" class="bn-item <?php echo e(request()->routeIs('invoices.*') ? 'active' : ''); ?>">
                <i class="bi bi-receipt-cutoff"></i><span>Invoice</span>
            </a>
            <a href="<?php echo e(route('payments.index', ['status' => 'pending'])); ?>" class="bn-item bn-create <?php echo e(request()->routeIs('payments.*') ? 'active' : ''); ?>">
                <span class="bn-fab"><i class="bi bi-credit-card"></i></span><span>Bayar</span>
            </a>
            <a href="<?php echo e(route('reports.index')); ?>" class="bn-item <?php echo e(request()->routeIs('reports.*') ? 'active' : ''); ?>">
                <i class="bi bi-graph-up-arrow"></i><span>Laporan</span>
            </a>
            <form method="POST" action="<?php echo e(route('logout')); ?>" class="bn-form">
                <?php echo csrf_field(); ?>
                <button type="submit" class="bn-btn"><i class="bi bi-box-arrow-right"></i><span>Keluar</span></button>
            </form>
        <?php else: ?>
            <a href="<?php echo e(route('dashboard')); ?>" class="bn-item <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                <i class="bi bi-speedometer2"></i><span>Home</span>
            </a>
            <a href="<?php echo e(route('invoices.index')); ?>" class="bn-item <?php echo e(request()->routeIs('invoices.*') ? 'active' : ''); ?>">
                <i class="bi bi-receipt-cutoff"></i><span>Invoice</span>
            </a>
            <a href="<?php echo e(route('invoices.create')); ?>" class="bn-item bn-create <?php echo e(request()->routeIs('invoices.create') ? 'active' : ''); ?>">
                <span class="bn-fab"><i class="bi bi-plus-lg"></i></span><span>Buat</span>
            </a>
            <a href="<?php echo e(route('clients.index')); ?>" class="bn-item <?php echo e(request()->routeIs('clients.*') ? 'active' : ''); ?>">
                <i class="bi bi-people"></i><span>Customer</span>
            </a>
            <a href="<?php echo e(route('profile.business')); ?>" class="bn-item <?php echo e(request()->routeIs('profile.*') ? 'active' : ''); ?>">
                <i class="bi bi-building"></i><span>Profil</span>
            </a>
        <?php endif; ?>
    </nav>
    <?php endif; ?>

    <!-- Floating WhatsApp Chat Admin (hanya login) -->
    <?php if(auth()->guard()->check()): ?>
    <?php
        $waMessage = rawurlencode(
            'Halo Admin InvoiceFlow 👋\n\n'
            . 'Saya *' . auth()->user()->name . '* (' . auth()->user()->email . ')\n'
            . 'Butuh bantuan terkait aplikasi InvoiceFlow.\n\n'
            . 'Halaman: ' . url()->current()
        );
    ?>
    <a href="https://wa.me/<?php echo e(config('services.whatsapp.support_number')); ?>?text=<?php echo e($waMessage); ?>"
       class="wa-fab" target="_blank" rel="noopener noreferrer"
       aria-label="Chat WhatsApp Admin" title="Chat Admin via WhatsApp">
        <span class="wa-tip">Butuh bantuan? Chat Admin 💬</span>
        <i class="bi bi-whatsapp"></i>
    </a>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js').catch(function () {});
        });
    }
    </script>
    <script>
        // ===== Sidebar Collapse (hamburger) — state tersimpan di localStorage =====
        (function () {
            const sidebarEl = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            const KEY = 'invoiceflow_sidebar_collapsed';

            // Restore state sebelumnya (hanya efek di desktop)
            if (window.innerWidth > 768 && localStorage.getItem(KEY) === '1') {
                sidebarEl.classList.add('collapsed');
                document.body.classList.add('sidebar-collapsed');
            }

            toggleBtn?.addEventListener('click', function () {
                const collapsed = sidebarEl.classList.toggle('collapsed');
                document.body.classList.toggle('sidebar-collapsed', collapsed);
                localStorage.setItem(KEY, collapsed ? '1' : '0');
            });
        })();
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /Users/permatabank/Desktop/tysar/a Project/APLIKASI LARAVEL INVOICE FLOW/Invoiceflow-App/resources/views/layouts/app.blade.php ENDPATH**/ ?>