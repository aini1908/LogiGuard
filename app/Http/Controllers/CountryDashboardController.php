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
                DB::raw('COALESCE(economic_indicators.inflation_rate, countries.inflation_rate, 0.00) as inflation_rate')
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
            
            // 1. Data Profil Negara dari Database
            $country = DB::table('countries')->where('country_code', $code)->first();
            if (!$country) {
                return response()->json(['status' => 'error', 'message' => 'Negara tidak ditemukan']);
            }

            // 2. Data Indikator Ekonomi (Database & Live World Bank API Sync)
            $economic = DB::table('economic_indicators')
                          ->where('country_code', $code)
                          ->orderBy('year', 'desc')
                          ->first();

            $gdpRaw = $economic->gdp ?? ($country->gdp ?? null);
            $inflationRaw = $economic->inflation_rate ?? ($country->inflation_rate ?? null);
            $popRaw = $economic->population ?? ($country->population ?? null);

            // Jika lokal null, coba sync dengan World Bank API
            if (!$gdpRaw || !$inflationRaw || !$popRaw) {
                try {
                    // GDP API
                    $wbGdp = Http::withoutVerifying()->timeout(3)
                        ->get("https://api.worldbank.org/v2/country/{$code}/indicator/NY.GDP.MKTP.CD?format=json&mrnev=1")
                        ->json();
                    if (isset($wbGdp[1][0]['value'])) {
                        $gdpRaw = $wbGdp[1][0]['value'];
                    }

                    // Inflation API
                    $wbInf = Http::withoutVerifying()->timeout(3)
                        ->get("https://api.worldbank.org/v2/country/{$code}/indicator/FP.CPI.TOTL.ZG?format=json&mrnev=1")
                        ->json();
                    if (isset($wbInf[1][0]['value'])) {
                        $inflationRaw = round($wbInf[1][0]['value'], 2);
                    }

                    // Population API
                    $wbPop = Http::withoutVerifying()->timeout(3)
                        ->get("https://api.worldbank.org/v2/country/{$code}/indicator/SP.POP.TOTL?format=json&mrnev=1")
                        ->json();
                    if (isset($wbPop[1][0]['value'])) {
                        $popRaw = $wbPop[1][0]['value'];
                    }
                } catch (\Exception $e) {
                    // Fallback aman
                }
            }

            // Format murni numerik
            $gdpFormatted = $gdpRaw ? '$' . number_format((float)$gdpRaw, 0, '.', ',') : 'No Data';
            $inflationFormatted = $inflationRaw !== null ? (float)$inflationRaw : 0.00;
            $popFormatted = $popRaw ? number_format((float)$popRaw, 0, '.', ',') : 'No Data';

            // 3. WEATHER DATA (Open-Meteo API)
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

            // 4. NEWS INTELLIGENCE (Google News Live RSS Feed - 100% Live & Unlimited)
            $countryName = $country->name;
            $encodedQuery = urlencode('"' . $countryName . '" AND (logistics OR "supply chain" OR shipping OR port)');
            $rssUrl = "https://api.rss2json.com/v1/api.json?rss_url=" . urlencode("https://news.google.com/rss/search?q={$encodedQuery}&hl=en-US&gl=US&ceid=US:en");

            $displayNews = [];
            $fullNewsText = "";

            try {
                $chNews = curl_init();
                curl_setopt($chNews, CURLOPT_URL, $rssUrl);
                curl_setopt($chNews, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($chNews, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($chNews, CURLOPT_TIMEOUT, 5);
                $newsResponse = curl_exec($chNews);
                curl_close($chNews);

                $newsData = json_decode($newsResponse, true);

                if (isset($newsData['items']) && !empty($newsData['items'])) {
                    $items = array_slice($newsData['items'], 0, 5);
                    foreach ($items as $item) {
                        $title = strip_tags($item['title'] ?? 'Headline');
                        $fullNewsText .= " " . $title;

                        $displayNews[] = [
                            'title' => $title,
                            'source' => !empty($item['author']) ? $item['author'] : 'Google News Live',
                            'url' => $item['link'] ?? '#',
                            'published_at' => isset($item['pubDate']) ? date('d M Y', strtotime($item['pubDate'])) : 'Today',
                            'image' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=80&w=400&auto=format&fit=crop'
                        ];
                    }
                }
            } catch (\Exception $e) {}

            // Fallback Cerdas: Jika berita spesifik negara sangat sepi, ambil berita Logistik Maritim Global Live
            if (empty($displayNews)) {
                $globalRssUrl = "https://api.rss2json.com/v1/api.json?rss_url=" . urlencode("https://news.google.com/rss/search?q=global+supply+chain+logistics+shipping&hl=en-US&gl=US&ceid=US:en");
                try {
                    $chGlobal = curl_init();
                    curl_setopt($chGlobal, CURLOPT_URL, $globalRssUrl);
                    curl_setopt($chGlobal, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($chGlobal, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($chGlobal, CURLOPT_TIMEOUT, 5);
                    $globalResponse = curl_exec($chGlobal);
                    curl_close($chGlobal);

                    $globalData = json_decode($globalResponse, true);
                    if (isset($globalData['items']) && !empty($globalData['items'])) {
                        foreach (array_slice($globalData['items'], 0, 4) as $item) {
                            $title = strip_tags($item['title'] ?? 'Global Freight Update');
                            $fullNewsText .= " " . $title;
                            $displayNews[] = [
                                'title' => $title,
                                'source' => 'Global Logistics Monitor',
                                'url' => $item['link'] ?? '#',
                                'published_at' => isset($item['pubDate']) ? date('d M Y', strtotime($item['pubDate'])) : 'Today',
                                'image' => 'https://images.unsplash.com/photo-1578575437130-527eed3abbec?q=80&w=400&auto=format&fit=crop'
                            ];
                        }
                    }
                } catch (\Exception $e) {}
            }
            $fullNewsText = strtolower($fullNewsText);

            // 5. LEXICON-BASED SENTIMENT ANALYSIS
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
            
            if ($totalTokens > 0) {
                $posPercent = round(($positiveCount / $totalTokens) * 100);
                $negPercent = 100 - $posPercent;
            } else {
                $inf = (float) $inflationFormatted;
                if ($inf > 6.0) {
                    $posPercent = 25;
                    $negPercent = 75;
                } else if ($inf > 3.5) {
                    $posPercent = 55;
                    $negPercent = 45;
                } else {
                    $posPercent = 80;
                    $negPercent = 20;
                }
            }

            // 6. WEIGHTED RISK MODEL ALGORITHM (Sesuai Spesifikasi Project Final)
            $temp = $currentWeather ? (float)$currentWeather['temperature'] : 24.0;
            $wind = $currentWeather ? (float)$currentWeather['windspeed'] : 12.0;
            
            $subWeatherRisk = ($temp > 38 || $temp < 0 || $wind > 40) ? 30 : (($temp > 32 || $wind > 25) ? 15 : 5);
            $inflationRateNum = (float)$inflationFormatted;
            $subInflationRisk = ($inflationRateNum > 8.0) ? 20 : (($inflationRateNum > 4.0) ? 10 : 3);
            $subNewsRisk = ($negPercent > 60) ? 40 : (($negPercent > 40) ? 25 : 8);
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
                    'gdp' => $gdpFormatted,
                    'inflation_rate' => $inflationFormatted,
                    'population' => $popFormatted
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
}