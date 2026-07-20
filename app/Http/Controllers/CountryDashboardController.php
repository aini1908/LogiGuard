<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CountryDashboardController extends Controller
{
    public function index()
    {
        // Mengambil daftar negara dari database untuk dipasang di dropdown select view
        $countries = DB::table('countries')->orderBy('name', 'asc')->get();
        
        $topRisks = DB::table('countries')
            ->leftJoin('economic_indicators', 'countries.country_code', '=', 'economic_indicators.country_code')
            ->select('countries.name', 'countries.country_code', 'economic_indicators.inflation_rate')
            ->orderBy('economic_indicators.inflation_rate', 'desc')
            ->take(5)
            ->get();

        return view('country_dashboard', compact('countries', 'topRisks'));
    }

    public function getCountryData($code)
    {
        try {
            $code = strtoupper($code);
            
            // 1. Ambil data profil negara dari database lokal
            $country = DB::table('countries')->where('country_code', $code)->first();
            if (!$country) {
                return response()->json(['status' => 'error', 'message' => 'Negara tidak ditemukan']);
            }

            // 2. Ambil data indikator ekonomi dari database lokal
            $economic = DB::table('economic_indicators')
                          ->where('country_code', $code)
                          ->orderBy('year', 'desc')
                          ->first();

            // 3. WEATHER: Tembak Open-Meteo API menggunakan koordinat resmi negara
            $lat = $country->latitude ?? 0.0;
            $lng = $country->longitude ?? 0.0;
            $weatherUrl = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lng}&current_weather=true";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $weatherUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $weatherResponse = curl_exec($ch);
            curl_close($ch);

            $weatherData = json_decode($weatherResponse, true);
            $currentWeather = $weatherData['current_weather'] ?? null;

            // 4. PARSING GNEWS: Sesuai Format Objek GNews API Asli
            $countryName = $country->name;
            $gnewsApiKey = '53cbec3786d43698989b18fc93afbaf0'; 
            
            $queryStr = urlencode('"' . $countryName . '" AND ("supply chain" OR logistics OR "shipping crisis" OR port)');
            $gnewsUrl = "https://gnews.io/api/v4/search?q={$queryStr}&lang=en&max=6&apikey={$gnewsApiKey}";
            
            $chNews = curl_init();
            curl_setopt($chNews, CURLOPT_URL, $gnewsUrl);
            curl_setopt($chNews, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($chNews, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($chNews, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($chNews, CURLOPT_TIMEOUT, 10);
            $newsResponse = curl_exec($chNews);
            curl_close($chNews);

            $newsData = json_decode($newsResponse, true);
            $articles = isset($newsData['articles']) ? $newsData['articles'] : [];

            $fullNewsText = "";
            $displayNews = [];
            
            if (!empty($articles) && is_array($articles)) {
                foreach ($articles as $article) {
                    $fullNewsText .= " " . ($article['title'] ?? '') . " " . ($article['description'] ?? '');
                    
                    $displayNews[] = [
                        'title' => $article['title'] ?? 'No Headline Available',
                        'source' => isset($article['source']['name']) ? $article['source']['name'] : 'Global News',
                        'url' => $article['url'] ?? '#',
                        'image' => $article['image'] ?? 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=80&w=400&auto=format&fit=crop'
                    ];
                }
            }
            $fullNewsText = strtolower($fullNewsText);

            // 5. KALKULASI SENTIMEN BERDASARKAN KATA KUNCI DATABASE LOKAL
            $positiveWords = DB::table('positive_words')->pluck('word')->toArray();
            $negativeWords = DB::table('negative_words')->pluck('word')->toArray();

            $positiveCount = 0;
            $negativeCount = 0;

            if (!empty(trim($fullNewsText))) {
                foreach ($positiveWords as $word) {
                    $cleanWord = trim(strtolower($word));
                    if (!empty($cleanWord) && stripos($fullNewsText, $cleanWord) !== false) { 
                        $positiveCount++; 
                    }
                }
                foreach ($negativeWords as $word) {
                    $cleanWord = trim(strtolower($word));
                    if (!empty($cleanWord) && stripos($fullNewsText, $cleanWord) !== false) { 
                        $negativeCount++; 
                    }
                }
            }

            $totalTokens = $positiveCount + $negativeCount;
            $posPercent = $totalTokens > 0 ? round(($positiveCount / $totalTokens) * 100) : 0;
            $negPercent = $totalTokens > 0 ? round(($negativeCount / $totalTokens) * 100) : 0;

            // =========================================================================
            // 🔥 IMPLEMENTASI AI / DATA SCIENCE: WEIGHTED RISK MODEL ALGORITHM (UAS)
            // =========================================================================
            // Bobot Aturan Dosen: Weather (30%), Inflation (20%), News Risk (40%), Currency/Default (10%)
            
            // A. Weather Risk Component (Max: 30%)
            $temp = $currentWeather ? (float)$currentWeather['temperature'] : 20.0;
            $wind = $currentWeather ? (float)$currentWeather['windspeed'] : 10.0;
            if ($temp > 38 || $temp < 0 || $wind > 40) {
                $subWeatherRisk = 30; // Cuaca Ekstrem / Badai
            } else if ($temp > 32 || $wind > 25) {
                $subWeatherRisk = 15; // Waspada
            } else {
                $subWeatherRisk = 5;  // Aman
            }

            // B. Inflation Risk Component (Max: 20%)
            $inflationRate = isset($economic->inflation_rate) ? (float)$economic->inflation_rate : 0.0;
            if ($inflationRate > 8.0) {
                $subInflationRisk = 20; // Inflasi Tinggi Krisis
            } else if ($inflationRate > 4.0) {
                $subInflationRisk = 10; // Moderat
            } else {
                $subInflationRisk = 3;  // Stabil
            }

            // C. Political / News Sentiment Risk Component (Max: 40%)
            if ($negativeCount > $positiveCount * 1.5) {
                $subNewsRisk = 40; // Konflik Geopolitik / Gangguan Rantai Pasok Berat
            } else if ($negativeCount > $positiveCount) {
                $subNewsRisk = 25; // Hambatan Logistik Ringan
            } else {
                $subNewsRisk = 8;  // Stabil / Positif
            }

            // D. Base Currency stability Risk Component (Max: 10%)
            $subCurrencyRisk = ($inflationRate > 6.0) ? 10 : 4;

            // TOTAL WEIGHTED RISK CALCULATION
            $totalRiskScore = $subWeatherRisk + $subInflationRisk + $subNewsRisk + $subCurrencyRisk;

            // Ambil kesimpulan berdasarkan akumulasi total bobot nilai
            if ($totalRiskScore >= 60) {
                $sentiment = "NEGATIF (High Risk - Jalur Distribusi Terganggu)";
                $color = 'red';
            } else if ($totalRiskScore >= 30) {
                $sentiment = "NETRAL (Medium Risk - Potensi Hambatan Operasional)";
                $color = 'orange';
            } else {
                $sentiment = "POSITIF (Low Risk - Jalur Operasional Aman)";
                $color = 'green';
            }

            // 6. Return Data Bundling Lengkap Ke Halaman Frontend
            return response()->json([
                'status' => 'success',
                'country' => $country,
                'economic' => [
                    'gdp' => isset($economic->gdp) ? $economic->gdp : 'World Bank Data Locked',
                    'inflation_rate' => isset($economic->inflation_rate) ? $economic->inflation_rate : '0',
                    'population' => isset($economic->population) ? $economic->population : 'No Data'
                ],
                'weather' => [
                    'temperature' => $currentWeather ? ($currentWeather['temperature'] . ' °C') : '18.4 °C',
                    'windspeed' => $currentWeather ? ($currentWeather['windspeed'] . ' km/h') : '12.2 km/h'
                ],
                'logistics_risk' => [
                    'sentiment' => $sentiment,
                    'color' => $color,
                    'score_positive' => $posPercent . '%',
                    'score_negative' => $negPercent . '%',
                    'total_risk_weight' => $totalRiskScore, // Output Nilai Angka Kuantitatif Model Risiko
                    'latest_headlines' => $displayNews
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function analyzeSentiment(Request $request)
    {
        try {
            $text = strtolower($request->input('text', ''));
            
            if (empty(trim($text))) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Teks berita tidak boleh kosong, wee!'
                ]);
            }

            $positiveWords = DB::table('positive_words')->pluck('word')->toArray();
            $negativeWords = DB::table('negative_words')->pluck('word')->toArray();

            $positiveCount = 0;
            $negativeCount = 0;
            $matchedPositive = [];
            $matchedNegative = [];

            foreach ($positiveWords as $word) {
                if (str_contains($text, strtolower($word))) {
                    $positiveCount++;
                    $matchedPositive[] = $word;
                }
            }

            foreach ($negativeWords as $word) {
                if (str_contains($text, strtolower($word))) {
                    $negativeCount++;
                    $matchedNegative[] = $word;
                }
            }

            if ($positiveCount > $negativeCount) {
                $sentiment = 'POSITIF (Jalur Aman & Stabil)';
                $color = 'green';
            } elseif ($negativeCount > $positiveCount) {
                $sentiment = 'NEGATIF (Jalur Berisiko / Delay)';
                $color = 'red';
            } else {
                $sentiment = 'NETRAL (Kondisi Normal)';
                $color = 'orange';
            }

            return response()->json([
                'status' => 'success',
                'sentiment' => $sentiment,
                'color' => $color,
                'score' => [
                    'positive' => $positiveCount,
                    'negative' => $negativeCount
                ],
                'words_found' => [
                    'positive' => $matchedPositive,
                    'negative' => $matchedNegative
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}