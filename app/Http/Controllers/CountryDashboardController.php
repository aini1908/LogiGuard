<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CountryDashboardController extends Controller
{
    public function index()
    {
        $countries = DB::table('countries')->orderBy('name', 'asc')->get();
        
        $topRisks = DB::table('countries')
            ->leftJoin('economic_indicators', 'countries.country_code', '=', 'economic_indicators.country_code')
            ->select(
                'countries.name', 
                'countries.country_code', 
                DB::raw('COALESCE(economic_indicators.inflation_rate, countries.inflation_rate, 2.84) as inflation_rate')
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
            
            $country = DB::table('countries')->where('country_code', $code)->first();
            if (!$country) {
                return response()->json(['status' => 'error', 'message' => 'Negara tidak ditemukan']);
            }

            // 1. Ambil data indikator dari tabel economic_indicators lokal dulu
            $economic = DB::table('economic_indicators')
                          ->where('country_code', $code)
                          ->orderBy('year', 'desc')
                          ->first();

            $gdpValue = $economic->gdp ?? null;
            $inflationValue = $economic->inflation_rate ?? null;
            $populationValue = $economic->population ?? null;

            // 2. Jika data lokal kosong, coba ambil LIVE DATA dari API World Bank Resmi
            if (!$gdpValue || !$inflationValue) {
                try {
                    // Fetch GDP dari World Bank API
                    $wbGdp = Http::withoutVerifying()->timeout(3)
                        ->get("https://api.worldbank.org/v2/country/{$code}/indicator/NY.GDP.MKTP.CD?format=json&mrnev=1")
                        ->json();
                    if (isset($wbGdp[1][0]['value'])) {
                        $val = $wbGdp[1][0]['value'];
                        $gdpValue = '$' . number_format($val / 1000000000, 2) . ' Miliar USD';
                    }

                    // Fetch Inflation dari World Bank API
                    $wbInf = Http::withoutVerifying()->timeout(3)
                        ->get("https://api.worldbank.org/v2/country/{$code}/indicator/FP.CPI.TOTL.ZG?format=json&mrnev=1")
                        ->json();
                    if (isset($wbInf[1][0]['value'])) {
                        $inflationValue = round($wbInf[1][0]['value'], 2);
                    }

                    // Fetch Population dari World Bank API
                    $wbPop = Http::withoutVerifying()->timeout(3)
                        ->get("https://api.worldbank.org/v2/country/{$code}/indicator/SP.POP.TOTL?format=json&mrnev=1")
                        ->json();
                    if (isset($wbPop[1][0]['value'])) {
                        $valPop = $wbPop[1][0]['value'];
                        $populationValue = number_format($valPop / 1000000, 1) . ' Juta Jiwa';
                    }
                } catch (\Exception $e) {
                    // Fail silently jika API timeout
                }
            }

            // Fallback Angka Riil Statis Makroekonomi jika World Bank API gagal
            $gdpValue = $gdpValue ?? ($country->gdp ?? '$1.37 Triliun USD');
            $inflationValue = $inflationValue ?? ($country->inflation_rate ?? 2.84);
            $populationValue = $populationValue ?? ($country->population ?? '273.8 Juta Jiwa');

            // Format bersih tanpa double %
            $formattedInflation = is_numeric($inflationValue) ? number_format((float)$inflationValue, 2) : $inflationValue;

            // 3. WEATHER: Open-Meteo API
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
                curl_setopt($ch, CURLOPT_TIMEOUT, 4);
                $weatherResponse = curl_exec($ch);
                curl_close($ch);

                $weatherData = json_decode($weatherResponse, true);
                $currentWeather = $weatherData['current_weather'] ?? null;
            } catch (\Exception $e) {}

            // 4. NEWS: GNews API
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
                curl_setopt($chNews, CURLOPT_TIMEOUT, 4);
                $newsResponse = curl_exec($chNews);
                curl_close($chNews);

                $newsData = json_decode($newsResponse, true);
                $articles = isset($newsData['articles']) ? $newsData['articles'] : [];
            } catch (\Exception $e) {}

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

            // 5. KALKULASI SENTIMEN
            $positiveWords = DB::table('positive_words')->pluck('word')->toArray();
            $negativeWords = DB::table('negative_words')->pluck('word')->toArray();

            $positiveCount = 0;
            $negativeCount = 0;

            if (!empty(trim($fullNewsText))) {
                foreach ($positiveWords as $word) {
                    $cleanWord = trim(strtolower($word));
                    if (!empty($cleanWord) && stripos($fullNewsText, $cleanWord) !== false) { $positiveCount++; }
                }
                foreach ($negativeWords as $word) {
                    $cleanWord = trim(strtolower($word));
                    if (!empty($cleanWord) && stripos($fullNewsText, $cleanWord) !== false) { $negativeCount++; }
                }
            }

            $totalTokens = $positiveCount + $negativeCount;
            $posPercent = $totalTokens > 0 ? round(($positiveCount / $totalTokens) * 100) : 0;
            $negPercent = $totalTokens > 0 ? round(($negativeCount / $totalTokens) * 100) : 0;

            // WEIGHTED RISK MODEL
            $temp = $currentWeather ? (float)$currentWeather['temperature'] : 24.0;
            $wind = $currentWeather ? (float)$currentWeather['windspeed'] : 12.0;
            
            $subWeatherRisk = ($temp > 38 || $temp < 0 || $wind > 40) ? 30 : (($temp > 32 || $wind > 25) ? 15 : 5);
            $inflationRateNum = (float)$formattedInflation;
            $subInflationRisk = ($inflationRateNum > 8.0) ? 20 : (($inflationRateNum > 4.0) ? 10 : 3);
            $subNewsRisk = ($negativeCount > $positiveCount * 1.5) ? 40 : (($negativeCount > $positiveCount) ? 25 : 8);
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
                return response()->json(['status' => 'error', 'message' => 'Teks berita tidak boleh kosong!']);
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

            $sentiment = ($positiveCount > $negativeCount) ? 'POSITIF (Jalur Aman & Stabil)' : (($negativeCount > $positiveCount) ? 'NEGATIF (Jalur Berisiko / Delay)' : 'NETRAL (Kondisi Normal)');
            $color = ($positiveCount > $negativeCount) ? 'green' : (($negativeCount > $positiveCount) ? 'red' : 'orange');

            return response()->json([
                'status' => 'success',
                'sentiment' => $sentiment,
                'color' => $color,
                'score' => ['positive' => $positiveCount, 'negative' => $negativeCount],
                'words_found' => ['positive' => $matchedPositive, 'negative' => $matchedNegative]
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}