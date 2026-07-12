<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShippingCalculatorController extends Controller
{
    // 🔥 PENGUSIR ERROR: Fungsi untuk menampilkan halaman utama kalkulator
    public function showCalculator()
    {
        $ports = DB::table('ports')->orderBy('port_name', 'asc')->get();
        return view('shipping_calculator', compact('ports'));
    }

    // Fungsi hitung logistik + ETA + Kurs + Engine Manajemen Risiko
    public function calculateCost(Request $request)
    {
        $originPortId = $request->input('origin_port');
        $destinationPortId = $request->input('destination_port');
        $weight = (float) $request->input('weight', 1);
        $containerType = $request->input('container_type', 'std_20');

        $origin = DB::table('ports')->where('id', $originPortId)->first();
        $destination = DB::table('ports')->where('id', $destinationPortId)->first();

        if (!$origin || !$destination) {
            return response()->json(['status' => 'error', 'message' => 'Pelabuhan tidak ditemukan.']);
        }

        // 1. Jarak Haversine
        $earthRadius = 6371;
        $dLat = deg2rad($destination->latitude - $origin->latitude);
        $dLon = deg2rad($destination->longitude - $origin->longitude);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($origin->latitude)) * cos(deg2rad($destination->latitude)) *
             sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;

        // 2. Container Multiplier
        $containerMultiplier = 1.0; 
        if ($containerType === 'std_40') {
            $containerMultiplier = 1.5;
        } elseif ($containerType === 'reefer') {
            $containerMultiplier = 2.2;
        }

        // 3. Kurs Database
        $latestRate = DB::table('currency_history')->orderBy('recorded_date', 'desc')->value('exchange_rate') ?? 16000;

        // 4. Hitung Biaya
        $baseRateUsd = 0.05; 
        $totalCostUsd = $distance * $weight * $baseRateUsd * $containerMultiplier;
        $totalCostIdr = $totalCostUsd * $latestRate;

        // 5. Hitung ETA Maritim
        $shipSpeedKmh = 37; 
        $totalHours = $distance / $shipSpeedKmh;
        $days = floor($totalHours / 24);
        $hours = round($totalHours % 24);
        $etaText = $days > 0 ? "{$days} Hari {$hours} Jam" : "{$hours} Jam";

        // 6. ENGINE MANAJEMEN RISIKO
        $riskLevel = 'Low Risk (Aman)';
        $riskColor = 'text-emerald-400 border-emerald-500/30 bg-emerald-500/10';
        $mitigation = 'Rute pelayaran aman dan stabil. Direkomendasikan menggunakan prosedur operasional standar (SOP) pengapalan reguler.';

        if ($distance > 8000) {
            $riskLevel = 'High Risk (Risiko Tinggi)';
            $riskColor = 'text-rose-400 border-rose-500/30 bg-rose-500/10';
            $mitigation = 'Jarak pelayaran antar-benua sangat jauh (>8.000 KM) & rentan cuaca ekstrem maritim. Wajib menambahkan Asuransi Lambung Kapal (Marine Hull Insurance) dan mengunci kurs valuta asing (Hedging) untuk menghindari kerugian operasional akibat fluktuasi mata uang internasional.';
        } elseif ($distance > 4000 || $containerType === 'reefer') {
            $riskLevel = 'Medium Risk (Risiko Sedang)';
            $riskColor = 'text-amber-400 border-amber-500/30 bg-amber-500/10';
            $mitigation = 'Jarak menengah atau muatan menggunakan kontainer berpendingin (Reefer). Pastikan monitoring daya listrik generator kapal berjalan 24 jam untuk mencegah kerusakan kargo akibat kegagalan suhu pembekuan pelabuhan tujuan.';
        }

        return response()->json([
            'status' => 'success',
            'origin_port' => $origin->port_name,
            'destination_port' => $destination->port_name,
            'distance' => round($distance, 2) . ' KM',
            'cost_usd' => round($totalCostUsd, 2),
            'cost_idr' => round($totalCostIdr, 0),
            'exchange_rate_used' => $latestRate,
            'eta' => $etaText,
            'risk_level' => $riskLevel,
            'risk_color' => $riskColor,
            'mitigation' => $mitigation
        ]);
    }
}