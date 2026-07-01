<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WithdrawController extends Controller
{
    public function index()
    {
        $driver = Auth::user()->driver;
        $withdrawals = $driver->withdrawals()->orderBy('created_at', 'desc')->get();

        return view('driver.withdraw', compact('driver', 'withdrawals'));
    }

    public function store(Request $request)
    {
        $driver = Auth::user()->driver;

        $request->validate([
            'amount'         => 'required|numeric|min:10000',
            'bank_name'      => 'required|string|max:50',
            'account_number' => 'required|string|max:30',
            'account_name'   => 'required|string|max:100',
        ], [
            'amount.required'         => 'Jumlah penarikan wajib diisi.',
            'amount.numeric'          => 'Jumlah penarikan harus berupa angka.',
            'amount.min'              => 'Minimal penarikan adalah Rp 10.000.',
            'bank_name.required'      => 'Nama bank / e-wallet wajib diisi.',
            'account_number.required' => 'Nomor rekening wajib diisi.',
            'account_name.required'   => 'Nama pemilik rekening wajib diisi.',
        ]);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $driver) {
                $lockedDriver = \App\Models\Driver::where('id', $driver->id)->lockForUpdate()->first();

                if ($lockedDriver->total_earnings < $request->amount) {
                    throw new \Exception('Saldo penarikan Anda tidak mencukupi.');
                }

                $lockedDriver->decrement('total_earnings', $request->amount);

                Withdrawal::create([
                    'driver_id'      => $lockedDriver->id,
                    'amount'         => $request->amount,
                    'bank_name'      => $request->bank_name,
                    'account_number' => $request->account_number,
                    'account_name'   => $request->account_name,
                    'status'         => 'pending',
                ]);
            });
        } catch (\Exception $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }

        return redirect()->route('driver.withdraw')
            ->with('success', 'Pengajuan penarikan sebesar Rp ' . number_format($request->amount, 0, ',', '.') . ' berhasil dikirim dan sedang diproses.');
    }
}
