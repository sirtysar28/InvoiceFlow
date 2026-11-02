{{-- Cluster aksi header: ikon notifikasi + dropdown profil (dipakai mobile & desktop) --}}
@php $hu = auth()->user(); @endphp
<div class="d-flex align-items-center gap-2 ha-cluster">

    {{-- ===== Notifikasi ===== --}}
    <div class="dropdown">
        <button class="ha-btn ha-bell" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" title="Notifikasi">
            <i class="bi bi-bell"></i>
            @if($headerNotifCount > 0)
                <span class="ha-badge">{{ $headerNotifCount > 9 ? '9+' : $headerNotifCount }}</span>
            @endif
        </button>
        <div class="dropdown-menu dropdown-menu-end ha-menu ha-notif-menu">
            <div class="ha-menu-head">
                <span>Notifikasi</span>
                @if($headerNotifCount > 0)
                    <span class="badge rounded-pill" style="background:#ef4444;">{{ $headerNotifCount }}</span>
                @endif
            </div>
            @if(count($headerNotifs) > 0)
                @foreach($headerNotifs as $n)
                    <a href="{{ $n['url'] }}" class="dropdown-item ha-notif-item">
                        <span class="ha-notif-icon" style="background: {{ $n['color'] }};"><i class="bi bi-{{ $n['icon'] }}"></i></span>
                        <span class="ha-notif-body">
                            <span class="ha-notif-title">{{ $n['title'] }}</span>
                            <span class="ha-notif-desc">{{ $n['desc'] }}</span>
                        </span>
                    </a>
                @endforeach
            @else
                <div class="ha-notif-empty"><i class="bi bi-check2-circle d-block mb-1" style="font-size:1.5rem;"></i>Semua beres, tidak ada notifikasi 🎉</div>
            @endif
        </div>
    </div>

    {{-- ===== Profil ===== --}}
    <div class="dropdown">
        <button class="ha-btn ha-avatar" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" title="{{ $hu->name }}">
            {{ strtoupper(substr($hu->name, 0, 1)) }}
        </button>
        <div class="dropdown-menu dropdown-menu-end ha-menu">
            <div class="ha-user-head">
                <div class="ha-user-avatar">{{ strtoupper(substr($hu->name, 0, 1)) }}</div>
                <div class="text-truncate" style="min-width:0;">
                    <div class="fw-bold text-truncate" style="font-family:'Sora',sans-serif; font-size:.9rem;">{{ $hu->name }}</div>
                    <span class="badge bg-{{ $hu->roleColor() }}" style="font-size:.66rem;">{{ $hu->roleLabel() }}</span>
                </div>
            </div>
            <a class="dropdown-item ha-item" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            @if($hu->canManageSettings())
                <a class="dropdown-item ha-item" href="{{ route('profile.business') }}"><i class="bi bi-building me-2"></i>Profil Bisnis</a>
            @endif
            @if($hu->isKlien())
                <a class="dropdown-item ha-item" href="{{ route('customers.history') }}"><i class="bi bi-clock-history me-2"></i>Riwayat Transaksi</a>
            @endif
            @if($hu->isAdmin())
                <a class="dropdown-item ha-item" href="{{ route('users.index') }}"><i class="bi bi-person-badge me-2"></i>Master User</a>
            @endif
            <div class="dropdown-divider"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item ha-item text-danger fw-semibold">
                    <i class="bi bi-box-arrow-right me-2"></i>Keluar
                </button>
            </form>
        </div>
    </div>
</div>
