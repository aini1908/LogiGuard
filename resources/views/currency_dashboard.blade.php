<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogiGuard - Currency Impact Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen font-sans">

    <div class="container mx-auto px-6 py-8">
        <!-- Header Dashboard -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-700 pb-5 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-emerald-400">📊 Currency Impact Dashboard</h1>
                <p class="text-slate-400 mt-1">Analisis tren nilai tukar mata uang global terhadap IDR untuk mitigasi risiko logistik maritim LogiGuard.</p>
            </div>
            
            <!-- Dropdown -->
            <div class="mt-4 md:mt-0 flex items-center space-x-3">
                <label for="countrySelect" class="text-sm font-medium text-slate-300">Pilih Negara Tujuan:</label>
                <select id="countrySelect" class="bg-slate-800 border border-slate-600 rounded-lg px-4 py-2 text-slate-100 focus:outline-none focus:border-emerald-500 transition-colors">
                    @foreach($countries as $country)
                        <option value="{{ $country->country_code }}" {{ $country->country_code == 'US' ? 'selected' : '' }}>
                            {{ $country->name }} ({{ $country->country_code }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Area Grafik -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 bg-slate-800 border border-slate-700 rounded-xl p-6 shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-slate-200" id="chartTitle">Loading data...</h3>
                    <span class="bg-emerald-500/10 text-emerald-400 text-xs px-2.5 py-0.5 rounded-full font-medium" id="currencyBadge">-</span>
                </div>
                <div class="relative w-full h-72">
                    <canvas id="currencyChart"></canvas>
                </div>
            </div>

            <!-- Box Insight -->
            <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 shadow-xl flex flex-col justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-200 mb-4">💡 LogiGuard Financial Insight</h3>
                    <p class="text-slate-400 text-sm leading-relaxed mb-4">
                        Perubahan nilai tukar ini berdampak langsung pada biaya operasional pengapalan kontainer, ongkos bahan bakar kapal (bunker), serta tarif bea cukai di pelabuhan tujuan.
                    </p>
                </div>
                <div class="bg-slate-700/40 rounded-lg p-4 border border-slate-600">
                    <span class="text-xs text-slate-400 block uppercase font-bold tracking-wider">Live Exchange Rate</span>
                    <div class="flex items-baseline space-x-2 mt-1">
                        <span class="text-2xl font-black text-white" id="liveRate">Rp0</span>
                        <span class="text-xs text-slate-400">per 1 mata uang asing</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let myChart = null;

        function updateChart(countryCode) {
            fetch(`/api/currency-trend?country_code=${countryCode}`)
                .then(response => response.json())
                .then(res => {
                    if (res.status === 'success') {
                        // Update Info Teks
                        document.getElementById('chartTitle').innerText = `Tren Pergerakan Kurs ${res.country_name}`;
                        document.getElementById('currencyBadge').innerText = res.currency_code;
                        
                        // Format mata uang anti RpNaN
                        const rateValue = parseFloat(res.current_rate) || 0;
                        document.getElementById('liveRate').innerText = new Intl.NumberFormat('id-ID', { 
                            style: 'currency', 
                            currency: 'IDR',
                            maximumFractionDigits: 0 
                        }).format(rateValue);

                        // Render Ulang Chart.js
                        const ctx = document.getElementById('currencyChart').getContext('2d');
                        if (myChart) {
                            myChart.destroy();
                        }

                        myChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: res.labels,
                                datasets: [{
                                    label: `Kurs ${res.currency_code} ke IDR`,
                                    data: res.rates,
                                    borderColor: '#10b981',
                                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                    fill: true,
                                    tension: 0.4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: { grid: { color: '#334155' }, ticks: { color: '#94a3b8' } },
                                    x: { grid: { display: false }, ticks: { color: '#94a3b8' } }
                                },
                                plugins: { legend: { display: false } }
                            }
                        });
                    }
                })
                .catch(err => console.error("Error AJAX:", err));
        }

        document.addEventListener('DOMContentLoaded', () => {
            const select = document.getElementById('countrySelect');
            updateChart(select.value);

            select.addEventListener('change', (e) => {
                updateChart(e.target.value);
            });
        });
    </script>
</body>
</html>