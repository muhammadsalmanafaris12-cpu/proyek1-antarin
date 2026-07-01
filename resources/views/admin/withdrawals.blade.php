@extends('layouts.app')
@section('title', 'Manajemen Penarikan Dana')

@section('content')
{{-- Admin Header --}}
<header class="sticky-header" style="padding:16px;">
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <h1 style="font-size:18px;font-weight:800;color:#111827;">Penarikan Dana (WD)</h1>
            <p style="font-size:11px;color:#6b7280;">Daftar Pengajuan Withdraw Driver</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" style="background:#f3f4f6;color:#374151;border-radius:10px;padding:8px 12px;font-size:12px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
    </div>
</header>

<div class="page-content fade-in" style="padding-bottom:32px;">

    {{-- Success/Error Alerts --}}
    @if(session('success'))
    <div class="alert alert-success" style="margin-bottom:16px;">
        <i class="fas fa-check-circle" style="font-size:16px;"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-error" style="margin-bottom:16px;">
        <i class="fas fa-exclamation-circle" style="font-size:16px;"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- List Card --}}
    <div class="card" style="border-radius:16px;overflow:hidden;">
        <div style="padding:16px;border-bottom:1px solid #f3f4f6;background:#f9fafb;">
            <h2 style="font-size:14px;font-weight:700;color:#111827;">Semua Pengajuan Penarikan</h2>
        </div>

        @if($withdrawals->isEmpty())
        <div style="text-align:center;padding:48px 20px;">
            <div style="width:64px;height:64px;background:#f3f4f6;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;color:#9ca3af;">
                <i class="fas fa-money-bill-transfer" style="font-size:28px;"></i>
            </div>
            <h3 style="font-size:16px;color:#374151;margin-bottom:4px;">Tidak ada data</h3>
            <p style="font-size:13px;color:#9ca3af;">Belum ada pengajuan penarikan dana dari driver.</p>
        </div>
        @else
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;text-align:left;font-size:13px;">
                <thead>
                    <tr style="border-bottom:1.5px solid #e5e7eb;background:#f3f4f6;color:#4b5563;font-weight:600;">
                        <th style="padding:12px 16px;">Driver</th>
                        <th style="padding:12px 16px;">Nominal</th>
                        <th style="padding:12px 16px;">Info Rekening</th>
                        <th style="padding:12px 16px;">Tanggal</th>
                        <th style="padding:12px 16px;">Status</th>
                        <th style="padding:12px 16px;text-align:right;">Aksi / Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($withdrawals as $wd)
                    <tr style="border-bottom:1px solid #f3f4f6;color:#374151;">
                        <td style="padding:14px 16px;font-weight:600;">
                            <div>
                                <p style="color:#111827;margin-bottom:2px;">{{ $wd->driver->user->name }}</p>
                                <p style="font-size:11px;color:#6b7280;font-weight:normal;">ID: {{ $wd->driver->id }} &bull; {{ $wd->driver->phone }}</p>
                            </div>
                        </td>
                        <td style="padding:14px 16px;font-weight:700;color:#10b981;font-size:14px;">
                            Rp {{ number_format($wd->amount,0,',','.') }}
                        </td>
                        <td style="padding:14px 16px;">
                            <div>
                                <p style="font-weight:700;color:#111827;margin-bottom:2px;">{{ $wd->bank_name }}</p>
                                <p style="font-size:11px;color:#4b5563;font-family:monospace;">{{ $wd->account_number }}</p>
                                <p style="font-size:11px;color:#6b7280;">A/N: {{ $wd->account_name }}</p>
                            </div>
                        </td>
                        <td style="padding:14px 16px;font-size:11px;color:#6b7280;">
                            {{ $wd->created_at->timezone('Asia/Jakarta')->format('d M Y') }}
                            <p style="font-size:10px;color:#9ca3af;margin-top:2px;">{{ $wd->created_at->timezone('Asia/Jakarta')->format('H:i') }} WIB</p>
                        </td>
                        <td style="padding:14px 16px;">
                            @if($wd->status === 'pending')
                            <span class="badge badge-orange" style="font-size:10px;font-weight:700;">Pending</span>
                            @elseif($wd->status === 'approved')
                            <span class="badge badge-green" style="font-size:10px;font-weight:700;background:#dcfce7;color:#15803d;">Disetujui</span>
                            @else
                            <span class="badge badge-gray" style="font-size:10px;font-weight:700;background:#fee2e2;color:#b91c1c;">Ditolak</span>
                            @endif
                        </td>
                        <td style="padding:14px 16px;text-align:right;">
                            @if($wd->status === 'pending')
                            <div style="display:flex;gap:6px;justify-content:flex-end;align-items:center;">
                                {{-- Reject Form --}}
                                <form method="POST" action="{{ route('admin.withdrawals.reject', $wd) }}" style="display:inline-flex;align-items:center;gap:4px;">
                                    @csrf
                                    <input type="text" name="admin_notes" placeholder="Alasan ditolak..." required style="height:32px;font-size:11px;border:1px solid #d1d5db;border-radius:6px;padding:0 8px;width:140px;outline:none;">
                                    <button type="submit" class="btn" style="background:#fee2e2;color:#b91c1c;padding:6px 10px;font-size:11px;font-weight:700;border:none;border-radius:6px;cursor:pointer;height:32px;">
                                        Tolak
                                    </button>
                                </form>

                                {{-- Approve Form --}}
                                <form method="POST" action="{{ route('admin.withdrawals.approve', $wd) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-primary" style="background:#059669;color:#fff;padding:6px 12px;font-size:11px;font-weight:700;border:none;border-radius:6px;cursor:pointer;height:32px;box-shadow:none;">
                                        Setujui
                                    </button>
                                </form>
                            </div>
                            @else
                            <div style="font-size:11px;color:#6b7280;text-align:right;">
                                @if($wd->status === 'rejected' && $wd->admin_notes)
                                <span style="display:block;color:#991b1b;font-style:italic;">"{{ $wd->admin_notes }}"</span>
                                @else
                                <span style="color:#9ca3af;">Tidak ada aksi tambahan</span>
                                @endif
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($withdrawals->hasPages())
        <div style="padding:16px;border-top:1px solid #f3f4f6;display:flex;justify-content:center;">
            {{ $withdrawals->links() }}
        </div>
        @endif
        @endif
    </div>

</div>
@endsection
