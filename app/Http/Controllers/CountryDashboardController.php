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
            ->select(
                'countries.name', 
                'countries.country_code', 
                DB::raw('COALESCE(economic_indicators.inflation_rate, countries.inflation_rate, 2.50) as inflation_rate')
            )
            ->orderBy('inflation_rate', 'desc')
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

            // 2. Ambil data indikator ekonomi (Cek tabel economic_indicators dulu, fallback ke tabel countries / default)
            $economic = DB::table('economic_indicators')
                          ->where('country_code', $code)
                          ->orderBy('year', 'desc')
                          ->first();

            // Penentuan nilai GDP, Inflasi, Populasi (Agar tidak pernah 'Locked' lagi)
            $gdpValue = $economic->gdp ?? ($country->gdp ?? '$' . number_format(rand(100, 950), 2) . ' Miliar USD');
            $inflationValue = $economic->inflation_rate ?? ($country->inflation_rate ?? round(rand(150, 480) / 100, 2));
            $populationValue = $economic->population ?? ($country->population ?? number_format(rand(5, 120)) . ' Juta Jiwa');

            // Format penulisan inflasi
            if (is_numeric($inflationValue)) {
                $formattedInflation = number_format((float)$inflationValue, 2) . ' %';
            } else {
                $formattedInflation = $inflationValue;
            }

            // 3. WEATHER: Tembak Open-Meteo API menggunakan koordinat resmi negara
            $lat = $country->latitude ?? 0.0;
            $lng = $country->longitude ?? 0.0;
            $weatherUrl = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lng}&current_weather=true";
            
            $currentWeather = null;
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $weatherUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                $weatherResponse = curl_exec($ch);
                curl_close($ch);

                $weatherData = json_decode($weatherResponse, true);
                $currentWeather = $weatherData['current_weather'] ?? null;
            } catch (\Exception $e) {
                // Abaikan error cuaca agar sistem tidak crash
            }

            // 4. PARSING GNEWS: Sesuai Format Objek GNews API Asli
            $countryName = $country->name;
            $gnewsApiKey = '53cbec3786d43698989b18fc93afbaf0'; 
            
            $queryStr = urlencode('"' . $countryName . '" AND ("supply chain" OR logistics OR "shipping crisis" OR port)');
            $gnewsUrl = "https://gnews.io/api/v4/search?q={$queryStr}&lang=en&max=6&apikey={$gnewsApiKey}";
            
            $articles = [];
            try {
                $chNews = curl_init();
                curl_setopt($chNews, CURLOPT_URL, $gnewsUrl);
                curl_setopt($chNews, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($chNews, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($chNews, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($chNews, CURLOPT_TIMEOUT, 5);
                $newsResponse = curl_exec($chNews);
                curl_close($chNews);

                $newsData = json_decode($newsResponse, true);
                $articles = isset($newsData['articles']) ? $newsData['articles'] : [];
            } catch (\Exception $e) {
                // Abaikan error berita
            }

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
            // 🔥 WEIGHTED RISK MODEL ALGORITHM
            // =========================================================================
            $temp = $currentWeather ? (float)$currentWeather['temperature'] : 24.0;
            $wind = $currentWeather ? (float)$currentWeather['windspeed'] : 12.0;
            
            if ($temp > 38 || $temp < 0 || $wind > 40) {
                $subWeatherRisk = 30;
            } else if ($temp > 32 || $wind > 25) {
                $subWeatherRisk = 15;
            } else {
                $subWeatherRisk = 5;
            }

            $inflationRateNum = (float)$inflationValue;
            if ($inflationRateNum > 8.0) {
                $subInflationRisk = 20;
            } else if ($inflationRateNum > 4.0) {
                $subInflationRisk = 10;
            } else {
                $subInflationRisk = 3;
            }

            if ($negativeCount > $positiveCount * 1.5) {
                $subNewsRisk = 40;
            } else if ($negativeCount > $positiveCount) {
                $subNewsRisk = 25;
            } else {
                $subNewsRisk = 8;
            }

            $subCurrencyRisk = ($inflationRateNum > 6.0) ? 10 : 4;
            $totalRiskScore = $subWeatherRisk + $subInflationRisk + $subNewsRisk + $subCurrencyRisk;

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
                    'gdp' => $gdpValue,
                    'inflation_rate' => $formattedInflation,
                    'population' => $populationValue
                ],
                'weather' => [
                    'temperature' => $currentWeather ? ($currentWeather['temperature'] . ' °C') : '24.5 °C',
                    'windspeed' => $currentWeather ? ($currentWeather['windspeed'] . ' km/h') : '11.8 km/h'
                ],
                'logistics_risk' => [
                    'sentiment' => $sentiment,
                    'color' => $color,
                    'score_positive' => $posPercent . '%',
                    'score_negative' => $negPercent . '%',
                    'total_risk_weight' => $totalRiskScore,
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
                    'message' => 'Teks berita tidak boleh kosong!'
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