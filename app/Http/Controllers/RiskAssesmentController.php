<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Country;

class RiskAssesmentController extends Controller
{
    public function analyze (Request $request)
    {
        // 1. Validasi input dari frontend
        $request->validate([
            'country_id' => 'required|string',
            'news_text' => 'required|string',
            'weather_score' => 'required|numeric|min:0|max:5',
            'inflation_score' => 'required|numeric|min:0|max:5',
            'exchange_rate_score' => 'required|numeric|min:0|max:5',
        ]);

        // ====================================================================
        // Tambahan Pengaman: Konversi Kode Negara String (Contoh: 'AU') ke ID Angka
        // ====================================================================
        $countryCode = $request->country_id;
        $dbCountryId = null;

        // Cari di database apakah ada negara dengan country_code tersebut
        $countryObj = DB::table('countries')->where('country_code', $countryCode)->first();
        
        if ($countryObj) {
            // Jika ketemu, pakai ID aslinya dari database (Angka)
            $dbCountryId = $countryObj->id;
        } else {
            // Jika database countries kosong atau tidak ketemu, kita buatkan fallback angka acak/aman 
            // agar query updateOrInsert di bawah tidak jebol menabrak Foreign Key.
            // Kamu bisa ganti angka 1 ini dengan ID negara default di databasemu
            $dbCountryId = 1; 
        }
        // ====================================================================

        // 2. Ambil kamus kata positif dan negatif dari database
        $positiveWords = DB::table('positive_words')->pluck('word')->toArray();
        $negativeWords = DB::table('negative_words')->pluck('word')->toArray();

        // 3. Bersihkan teks berita (ubah ke huruf kecil dan pecah jadi array kata)
        $cleanText = strtolower($request->news_text);
        // Hapus tanda baca agar pencocokan kata akurat
        $cleanText = preg_replace('/[^\w\s]/', '', $cleanText);
        $wordsInNews = explode(' ', $cleanText);

        // 4. Hitung kemunculan kata berdasarkan leksikon sentimen
        $posCount = 0;
        $negCount = 0;

        foreach ($wordsInNews as $word) {
            if (in_array($word, $positiveWords)) {
                $posCount++;
            } elseif (in_array($word, $negativeWords)) {
                $negCount++;
            }
        }
        // 5. Rumus Sentimen: Jika total kata negatif lebih banyak, skor sentimen berita membengkak naik
        // Batasi rentang skor sentimen berita antara 0 sampai 5
        $totalSentimentWords = $posCount + $negCount;
        $newsSentimentScore = 0;

        if ($totalSentimentWords > 0) {
            $newsSentimentScore = ($negCount / $totalSentimentWords) * 5;
        }
        // 6. Gabungkan dengan variabel eksternal (Rumus Total Skor Risiko Gabungan)
        $weather = $request->weather_score;
        $inflation = $request->inflation_score;
        $exchange = $request->exchange_rate_score;

        $totalRiskScore = ($weather + $inflation + $exchange + $newsSentimentScore) / 4;

        if ($totalRiskScore <= 1.5) {
            $riskLevel = 'Low';
        } elseif ($totalRiskScore <= 3.5) {
            $riskLevel = 'Medium';
        } else {
            $riskLevel = 'High';
        }

        // Di sini diubah menggunakan $dbCountryId yang sudah berupa Angka murni
        DB::table('risk_scores')->updateOrInsert(
            ['country_id' => $dbCountryId],
            [
                'weather_score' => $weather,
                'inflation_score' => $inflation,
                'exchange_rate_score' => $exchange,
                'news_sentiment_score' => $newsSentimentScore,
                'total_risk_score' => $totalRiskScore,
                'risk_level' => $riskLevel,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Analisis risiko rantai pasok berhasil dihitung!',
            'results' => [
                'positive_words_found' => $posCount,
                'negative_words_found' => $negCount,
                'news_sentiment_score' => round($newsSentimentScore, 2),
                'total_risk_score' => round($totalRiskScore, 2),
                'risk_level' => $riskLevel
            ]
        ], 200);
    }
}