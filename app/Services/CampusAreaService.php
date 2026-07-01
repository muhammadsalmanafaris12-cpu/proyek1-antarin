<?php

namespace App\Services;

class CampusAreaService
{
    // Pusat area operasional kampus (ULBI, Sarijadi, Bandung)
    public const CENTER_LAT = -6.8770;
    public const CENTER_LNG = 107.5870;
    public const MAX_RADIUS_KM = 3.0;

    /**
     * Menghitung jarak antara dua koordinat menggunakan Haversine Formula.
     */
    public static function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Memeriksa apakah koordinat berada di dalam radius operasional kampus.
     */
    public static function isWithinOperationalArea(?float $lat, ?float $lng): bool
    {
        if ($lat === null || $lng === null) {
            return false;
        }

        $distance = self::calculateDistance(self::CENTER_LAT, self::CENTER_LNG, $lat, $lng);

        return $distance <= self::MAX_RADIUS_KM;
    }
}
