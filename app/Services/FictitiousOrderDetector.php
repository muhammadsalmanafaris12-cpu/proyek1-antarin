<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderSuspicion;

class FictitiousOrderDetector
{
    private array $flags  = [];
    private int   $score  = 0;

    public function analyze(Order $order): OrderSuspicion
    {
        $this->flags = [];
        $this->score = 0;

        $this->checkNewAccount($order);
        $this->checkEmptyAddress($order);
        $this->checkCoordinateAnomaly($order);
        $this->checkCancelPattern($order);
        $this->checkHighValueAnomaly($order);

        $this->score = min(100, $this->score);
        $level = $this->calculateLevel();

        $suspicion = OrderSuspicion::firstOrNew(['order_id' => $order->id]);
        
        if (!$suspicion->exists) {
            $suspicion->reviewed = false;
        }
        
        $suspicion->fill([
            'score' => $this->score,
            'flags' => $this->flags,
            'level' => $level,
        ])->save();

        // Update order flags
        $order->update([
            'is_suspicious'   => $this->score >= 30,
            'suspicion_score' => $this->score,
        ]);

        return $suspicion;
    }

    /** Cek: akun customer baru (< 7 hari) */
    private function checkNewAccount(Order $order): void
    {
        $customer = $order->customer;
        if ($customer && $customer->created_at->diffInDays(now()) < 7) {
            $this->flags[] = 'Akun customer baru dibuat (kurang dari 7 hari).';
            $this->score  += 25;
        }
    }

    /** Cek: alamat pengiriman kosong atau terlalu pendek */
    private function checkEmptyAddress(Order $order): void
    {
        $address = trim($order->delivery_address ?? '');
        if (strlen($address) < 10) {
            $this->flags[] = 'Alamat pengiriman tidak lengkap atau kosong.';
            $this->score  += 30;
        }

        $suspiciousKeywords = ['kosong', 'tidak jelas', 'unknown', 'test', 'coba', 'asal'];
        foreach ($suspiciousKeywords as $kw) {
            if (str_contains(strtolower($address), $kw)) {
                $this->flags[] = 'Alamat mengandung kata mencurigakan: "' . $kw . '".';
                $this->score  += 20;
                break;
            }
        }
    }

    /** Cek: koordinat di luar area wajar (Indonesia) */
    private function checkCoordinateAnomaly(Order $order): void
    {
        $lat = $order->delivery_lat;
        $lng = $order->delivery_lng;

        if ($lat !== null && $lng !== null) {
            // Indonesia bounds: lat -11 to 6, lng 95 to 141
            if ($lat < -11 || $lat > 6 || $lng < 95 || $lng > 141) {
                $this->flags[] = 'Koordinat pengiriman berada di luar wilayah Indonesia.';
                $this->score  += 35;
            }
        } elseif ($lat === null && $lng === null) {
            $this->flags[] = 'Tidak ada data koordinat pengiriman.';
            $this->score  += 10;
        }
    }

    /** Cek: customer sering cancel pesanan (> 3x) */
    private function checkCancelPattern(Order $order): void
    {
        $customer = $order->customer;
        if ($customer && $customer->cancel_count > 3) {
            $this->flags[] = "Customer memiliki riwayat {$customer->cancel_count}x pembatalan pesanan.";
            $this->score  += 20;
        }
    }

    /** Cek: nilai order jauh di atas rata-rata customer */
    private function checkHighValueAnomaly(Order $order): void
    {
        $customer = $order->customer;
        if (!$customer || $customer->order_count < 3) return;

        $avgOrder = $customer->orders()
            ->where('status', 'delivered')
            ->avg('total_amount') ?? 0;

        if ($avgOrder > 0 && $order->total_amount > ($avgOrder * 3)) {
            $this->flags[] = 'Total pesanan jauh melebihi rata-rata historis customer (3x lipat).';
            $this->score  += 20;
        }
    }

    private function calculateLevel(): string
    {
        if ($this->score >= 60) return 'high';
        if ($this->score >= 30) return 'medium';
        if ($this->score >  0)  return 'low';
        return 'safe';
    }
}
