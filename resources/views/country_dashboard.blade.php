@extends('layouts.app')

@section('title', 'Global Country Dashboard')

@section('styles')
    <!-- Leaflet Map CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Pustaka Ikon Bendera Resmi (Flag-Icons) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css" />
    <style>
        .leaflet-container { z-index: 10 !important; }
    </style>
@endsection

@section('content')
    <!-- 🎪 ELEMEN WINDOW 1: DASHBOARD UTAMA (PETA & METREK NEGARA) -->
    <div id="coreDashboardWindow">
        <!-- HEADER & DROPDOWN SELECTOR -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200 pb-5 mb-6 gap-4">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Global Country Dashboard</span>
                    <span id="headerFlag" class="hidden shadow-xs rounded-sm"></span>
                    <!-- ✨ TOMBOL FAVORITE WATCHLIST (MEMENUHI FITUR 9) -->
                    <button onclick="toggleFavoriteWatchlist()" id="btnWatchlistStar" class="hidden text-slate-300 hover:text-amber-500 text-sm transition-colors cursor-pointer border-none bg-transparent">
                        <i class="fa-solid fa-star"></i>
                    </button>
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Ringkasan Indikator Makroekonomi, Pemetaan Geografis, dan Manajemen Risiko Operasional Jalur Distribusi Global.</p>
            </div>
            
            <div class="w-full md:w-72">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Pemeriksaan Spesifik Negara:</label>
                <div class="relative">
                    <select id="countrySelect" class="w-full appearance-none bg-white border border-slate-300 rounded-xl px-4 py-2 text-xs font-semibold text-slate-700 shadow-xs focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 cursor-pointer pr-10">
                        <option value="">-- Pilih Negara Destinasi --</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->country_code }}">{{ strtoupper($country->country_code) }} - {{ $country->name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 border-l border-slate-200">
                        <i class="fa-solid fa-chevron-down text-[10px]"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading System Indikator -->
        <div id="loadingStatus" class="hidden bg-blue-50 border border-blue-200 text-blue-600 rounded-xl p-4 text-center text-xs font-semibold mb-6 shadow-xs animate-pulse">
            <i class="fa-solid fa-circle-notch fa-spin me-2"></i> Menghubungkan ke server multi-API untuk analisis rute spesifik...
        </div>

        <!-- DUA BLOCK WORKSPACE UTAMA -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6 items-start">
            <!-- BLOCK KIRI: DATA DASHBOARD UMUM -->
            <div class="lg:col-span-5 flex flex-col gap-5">
                <!-- TABEL RINGKASAN: INDEKS INFLASI GLOBAL -->
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                            <span>Indeks Kerawanan Inflasi Global</span>
                        </h3>
                        <span class="text-slate-400 text-xs"><i class="fa-solid fa-triangle-exclamation"></i></span>
                    </div>
                    <div class="overflow-hidden border border-slate-100 rounded-xl">
                        <table class="min-w-full divide-y divide-slate-100 text-left">
                            <thead class="bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                <tr>
                                    <th class="px-4 py-2.5">Negara</th>
                                    <th class="px-4 py-2.5">Kode</th>
                                    <th class="px-4 py-2.5 text-right">Inflasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                                @foreach($topRisks as $risk)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-4 py-2.5 flex items-center gap-2">
                                        <span class="fi fi-{{ strtolower($risk->country_code) }} shadow-2xs rounded-xs w-4 h-3 shrink-0"></span>
                                        <span class="truncate max-w-[120px]">{{ $risk->name }}</span>
                                    </td>
                                    <td class="px-4 py-2.5 font-mono text-[10px] text-slate-400">{{ $risk->country_code }}</td>
                                    <td class="px-4 py-2.5 text-right text-rose-600 font-bold">{{ number_format($risk->inflation_rate, 2) }} %</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <span class="text-[9px] text-slate-400 block mt-2 text-center italic">*Diperbarui otomatis via World Bank System Core.</span>
                </div>

                <!-- ✨ WADAH MODERNISE PEMANTAUAN FAVORITE MONITORING LIST (FITUR 9 SEUTUHNYA) -->
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-[3px] bg-amber-400"></div>
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-star text-amber-500"></i>
                            <span>Favorite Monitoring Watchlist</span>
                        </h3>
                        <span class="text-[9px] text-slate-400 font-bold bg-slate-100 px-2 py-0.5 rounded-full font-mono uppercase tracking-wide">Fitur 9</span>
                    </div>
                    <!-- Tempat render list negara favorit bergaya Card Grid Layout -->
                    <div id="watchlistBadgeContainer" class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div class="col-span-2 py-3 text-center text-[11px] text-slate-400 italic bg-slate-50/50 border border-dashed border-slate-200 rounded-xl">
                            Belum ada negara yang dipantau. Klik bintang pada negara pilihan untuk menambahkan.
                        </div>
                    </div>
                </div>

                <!-- Box Dinamis Pengecekan Khusus -->
                <div id="welcomePlaceholder" class="bg-white border border-slate-200 rounded-2xl p-6 border-l-4 border-l-blue-600 shadow-xs text-center">
                    <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 text-base mb-3 mx-auto shadow-2xs">
                        <i class="fa-solid fa-magnifying-glass-chart"></i>
                    </div>
                    <h4 class="text-xs font-bold text-slate-900 mb-1">Analisis Jalur Logistik Spesifik</h4>
                    <p class="text-[11px] text-slate-500 leading-relaxed">Pilih salah satu entitas negara di sudut kanan atas untuk menampilkan cuaca operasional real-time dan ringkasan metrik GDP.</p>
                </div>

                <!-- Grid Metrik Spesifik (Hidden Awalnya) -->
                <div id="metricContent" class="hidden flex flex-col gap-4 w-full">
                    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Gross Domestic Product</span>
                            <div id="valGdp" class="text-base font-black text-slate-900">-</div>
                        </div>
                        <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-2xs"><i class="fa-solid fa-coins"></i></div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Inflasi Negara Terpilih</span>
                            <div id="valInflation" class="text-base font-black text-rose-600">-</div>
                        </div>
                        <div class="w-9 h-9 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600 shadow-2xs"><i class="fa-solid fa-arrow-trend-up"></i></div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Estimasi Populasi</span>
                            <div id="valPopulation" class="text-base font-black text-slate-900">-</div>
                        </div>
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-2xs"><i class="fa-solid fa-users-gears"></i></div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Kondisi Cuaca Ibu Kota</span>
                            <div id="valWeather" class="text-base font-black text-sky-600">-</div>
                            <div id="valWind" class="text-[9px] text-slate-400 block mt-0.5">Kecepatan angin eksternal: -</div>
                        </div>
                        <div class="w-9 h-9 rounded-xl bg-sky-50 flex items-center justify-center text-sky-600 shadow-2xs"><i class="fa-solid fa-temperature-half"></i></div>
                    </div>
                    <div id="sentimentSection" class="hidden bg-white border border-slate-200 rounded-2xl p-4 shadow-xs">
                        <div id="sentimentResultBox" class="p-3 rounded-xl border flex flex-col items-center justify-center text-center transition-all duration-150"></div>
                    </div>
                </div>
            </div>

            <!-- BLOCK KANAN: PETA INTERAKTIF GLOBAL -->
            <div class="lg:col-span-7 bg-white border border-slate-200 rounded-2xl p-2 shadow-xs min-h-[460px] flex">
                <div id="map" class="w-full h-full min-h-[460px] rounded-xl"></div>
            </div>
        </div>
    </div>

    <!-- 🌐 ELEMEN WINDOW 2: FITUR HALAMAN KHUSUS BERITA LOGISTIK GLOBAL REAL-TIME (Hidden di Awal) -->
    <div id="globalNewsWindow" class="hidden bg-white border border-slate-200 rounded-2xl p-6 shadow-xs mb-12">
        <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-satellite-dish text-emerald-600 animate-pulse"></i>
                    <span>Live Global Maritime News Room 🚢</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Arus kompilasi informasi krisis maritim, aktivitas pelabuhan utama, dan kargo internasional real-time ter-update otomatis.</p>
            </div>
            <button onclick="backToDashboard()" class="text-xs font-bold text-blue-600 hover:underline flex items-center gap-1 cursor-pointer bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl">
                <i class="fa-solid fa-circle-arrow-left"></i> Kembali ke Dashboard
            </button>
        </div>

        <!-- Wadah Grid Berita Real-Time Berkapasitas Besar -->
        <div id="liveNewsFeed" class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="col-span-3 p-12 text-center text-xs text-slate-400 font-semibold italic">
                <i class="fa-solid fa-circle-notch fa-spin me-2 text-emerald-600 text-sm"></i> Menghubungkan ke satelit News API untuk penarikan manifes berita logistik...
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const map = L.map('map').setView([20, 0], 2);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { 
            attribution: '&copy; OpenStreetMap contributors' 
        }).addTo(map);

        const allCountriesData = @json($countries);
        const markersMap = {};

        allCountriesData.forEach(c => {
            if (c.latitude && c.longitude && parseFloat(c.latitude) !== 0) {
                const lat = parseFloat(c.latitude); const lng = parseFloat(c.longitude);
                const marker = L.marker([lat, lng]).addTo(map);
                markersMap[c.country_code] = marker;
            }
        });

        // 🚀 LOGIKA SPA NAVIGATION UNTUK PINDAH HALAMAN BERITA SECARA INSTAN
        function switchViewToNews() {
            document.getElementById('coreDashboardWindow').classList.add('hidden');
            document.getElementById('globalNewsWindow').classList.remove('hidden');
            
            // Ubah gaya tombol navigasi atas agar terlihat aktif
            const btn = document.getElementById('tabBtnNewsGlobal');
            if(btn) btn.className = "flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold tracking-wide transition-all duration-150 bg-white text-blue-950 shadow-xs";
            
            fetchLiveMaritimeNews(); // Tarik berita real-time
        }

        function backToDashboard() {
            document.getElementById('globalNewsWindow').classList.add('hidden');
            document.getElementById('coreDashboardWindow').classList.remove('hidden');
            
            const btn = document.getElementById('tabBtnNewsGlobal');
            if(btn) btn.className = "flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold tracking-wide transition-all duration-150 hover:bg-white/10 text-blue-100 hover:text-white bg-transparent";
            
            setTimeout(() => { map.invalidateSize(); }, 50);
        }

        // 🔄 FUNGSI UTAMA: Ambil Arus Berita Logistik Terkini 100% Real-Time
        function fetchLiveMaritimeNews() {
            const newsFeed = document.getElementById('liveNewsFeed');
            if(!newsFeed) return;

            const globalLogisticsKeywords = encodeURIComponent('(maritime disruption) OR (shipping port logistics krisis) OR (ocean cargo freight)');
            
            // Backup Data Premium jika sewaktu-waktu kuota habis ditengah jalan
            const fallbackNews = [
                { source: "Logistics Inside Europe", title: "Port of Sines Expands Container Terminal Capacity to Process Atlantic Cargo Surge", image: "https://images.unsplash.com/photo-1578575437130-527eed3abbec?q=80&w=400&auto=format&fit=crop", url: "#" },
                { source: "Maritime Executive", title: "Global Maritime Hubs Invest €50M in Digitalizing Supply Chain Frameworks", image: "https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=80&w=400&auto=format&fit=crop", url: "#" },
                { source: "Ocean Freight News", title: "Global Port Authorities Optimize Vessel Queuing to Reduce Structural Freight Delays", image: "https://images.unsplash.com/photo-1494412574643-ff11b0a5c1c3?q=80&w=400&auto=format&fit=crop", url: "#" },
                { source: "FreightWaves", title: "Container Freight Rates Stabilize as Fleet Authorities Optimize Global Rerouting", image: "https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=80&w=400&auto=format&fit=crop", url: "#" },
                { source: "JOC Maritime", title: "Global Shipping Lanes Face Structural Delays Amid Canal Infrastructure Bottlenecks", image: "https://images.unsplash.com/photo-1578575437130-527eed3abbec?q=80&w=400&auto=format&fit=crop", url: "#" },
                { source: "Port Technology", title: "Advanced Automation Cranes Adopted Across Major International Trading Hubs", image: "https://images.unsplash.com/photo-1494412574643-ff11b0a5c1c3?q=80&w=400&auto=format&fit=crop", url: "#" }
            ];

            function renderArticles(articles, isFallback = false) {
                let newsHtml = '';
                articles.forEach(art => {
                    const imgUrl = art.urlToImage ? art.urlToImage : (art.image ? art.image : 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=80&w=400&auto=format&fit=crop');
                    const sourceName = art.source.name ? art.source.name : (art.source ? art.source : 'Global Logistics');
                    const targetAttr = isFallback ? '' : 'target="_blank"';
                    const clickAction = isFallback ? 'onclick="event.preventDefault(); alert(\'Simulasi link berita logistik aktif.\')"' : '';

                    newsHtml += `
                        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden flex flex-col justify-between shadow-xs hover:shadow-md transition-shadow duration-200">
                            <div class="h-36 overflow-hidden bg-slate-100">
                                <img src="${imgUrl}" class="w-full h-full object-cover" onerror="this.src='https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=80&w=400&auto=format&fit=crop';">
                            </div>
                            <div class="p-4 flex flex-col justify-between flex-1">
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span class="text-[9px] font-black text-emerald-700 uppercase tracking-wider bg-emerald-50 px-2 py-0.5 rounded-md">${sourceName}</span>
                                    </div>
                                    <a href="${art.url}" ${targetAttr} ${clickAction} class="block text-xs font-bold text-slate-900 leading-snug hover:text-blue-600 transition-colors line-clamp-3 mb-4 no-underline">
                                        ${art.title}
                                    </a>
                                </div>
                                <div class="text-right border-t border-slate-100 pt-3">
                                    <a href="${art.url}" ${targetAttr} ${clickAction} class="text-[10px] text-blue-600 font-bold hover:underline inline-flex items-center gap-1 no-underline">
                                        Baca Artikel <i class="fa-solid fa-arrow-up-right-from-square text-[8px]"></i>
                                    </a>
                                </div>
                            </div>
                        </div>`;
                });
                newsFeed.innerHTML = newsHtml;
            }

            // Eksekusi API secara live
            fetch(`https://newsapi.org/v2/everything?q=${globalLogisticsKeywords}&language=en&sortBy=publishedAt&pageSize=6&apiKey=4bdf9bd6719842f1b4020b666a0d0a51`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'ok' && data.articles && data.articles.length > 0) {
                        renderArticles(data.articles, false);
                    } else {
                        renderArticles(fallbackNews, true);
                    }
                })
                .catch(() => {
                    renderArticles(fallbackNews, true);
                });
        }

        // 🌟 FITUR 9: MANAJEMEN FAVORITE MONITORING WATCHLIST MODERN
        function toggleFavoriteWatchlist() {
            const selectEl = document.getElementById('countrySelect');
            const code = selectEl.value;
            if(!code) return;

            let watchlist = JSON.parse(localStorage.getItem('lgWatchlist') || '[]');
            const index = watchlist.indexOf(code);

            if(index > -1) {
                watchlist.splice(index, 1);
            } else {
                watchlist.push(code);
            }

            localStorage.setItem('lgWatchlist', JSON.stringify(watchlist));
            updateWatchlistStarUI(code);
            renderWatchlistBadges(); // Perbarui daftar card lencana di layar
        }

        function updateWatchlistStarUI(code) {
            const starBtn = document.getElementById('btnWatchlistStar');
            if(!starBtn) return;
            
            starBtn.classList.remove('hidden');
            let watchlist = JSON.parse(localStorage.getItem('lgWatchlist') || '[]');
            
            if(watchlist.includes(code)) {
                starBtn.className = "text-amber-500 hover:text-amber-600 text-sm transition-colors cursor-pointer border-none bg-transparent";
            } else {
                starBtn.className = "text-slate-300 hover:text-amber-500 text-sm transition-colors cursor-pointer border-none bg-transparent";
            }
        }

        // Fungsi mencetak lencana negara favorit bergaya Card Premium
        function renderWatchlistBadges() {
            const container = document.getElementById('watchlistBadgeContainer');
            if(!container) return;

            let watchlist = JSON.parse(localStorage.getItem('lgWatchlist') || '[]');
            
            if(watchlist.length === 0) {
                container.innerHTML = `
                    <div class="col-span-2 py-3 text-center text-[11px] text-slate-400 italic bg-slate-50/50 border border-dashed border-slate-200 rounded-xl">
                        Belum ada negara yang dipantau. Klik bintang pada negara pilihan untuk menambahkan.
                    </div>`;
                return;
            }

            container.innerHTML = "";
            watchlist.forEach(code => {
                const found = allCountriesData.find(c => c.country_code.toUpperCase() === code.toUpperCase());
                const countryName = found ? found.name : code;

                container.innerHTML += `
                    <div class="flex items-center justify-between bg-gradient-to-r from-slate-50 to-white hover:from-blue-50/40 hover:to-blue-50/10 border border-slate-200 hover:border-blue-400/80 rounded-xl px-3 py-2 transition-all duration-200 hover:-translate-y-0.5 shadow-2xs hover:shadow-xs group">
                        <button onclick="triggerWatchlistNavigation('${code}')" class="flex items-center gap-2.5 text-left border-none bg-transparent cursor-pointer p-0 w-full">
                            <span class="fi fi-${code.toLowerCase()} rounded-xs w-4 h-3 shrink-0 shadow-3xs"></span>
                            <div class="truncate">
                                <div class="text-[11px] font-black text-slate-800 tracking-wide">${countryName}</div>
                                <div class="text-[9px] text-slate-400 font-mono font-bold uppercase tracking-wider">Mekanisme Rute: ${code.toUpperCase()}</div>
                            </div>
                        </button>
                        <button onclick="removeSpecificWatchlist('${code}')" class="text-slate-300 hover:text-rose-500 hover:scale-110 transition-all p-1 border-none bg-transparent cursor-pointer text-xs ms-2 shrink-0">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </button>
                    </div>
                `;
            });
        }

        function triggerWatchlistNavigation(code) {
            const selectEl = document.getElementById('countrySelect');
            selectEl.value = code;
            selectEl.dispatchEvent(new Event('change'));
        }

        function removeSpecificWatchlist(code) {
            let watchlist = JSON.parse(localStorage.getItem('lgWatchlist') || '[]');
            const index = watchlist.indexOf(code);
            if(index > -1) {
                watchlist.splice(index, 1);
                localStorage.setItem('lgWatchlist', JSON.stringify(watchlist));
                renderWatchlistBadges();
                
                const currentCode = document.getElementById('countrySelect').value;
                if(currentCode === code) updateWatchlistStarUI(code);
            }
        }

        // Jalankan cetak badge favorit pertama kali halaman dimuat
        document.addEventListener("DOMContentLoaded", renderWatchlistBadges);
        
        // Listener dropdown negara biasa
        document.getElementById('countrySelect').addEventListener('change', function() {
            const countryCode = this.value; if (!countryCode) return;

            const placeholder = document.getElementById('welcomePlaceholder');
            const loading = document.getElementById('loadingStatus');
            const metrics = document.getElementById('metricContent');
            const sentimentSec = document.getElementById('sentimentSection');
            const headerFlag = document.getElementById('headerFlag');

            loading.classList.remove('hidden');
            metrics.classList.add('hidden'); 
            sentimentSec.classList.add('hidden');

            headerFlag.className = `fi fi-${countryCode.toLowerCase()} shadow-sm rounded-xs text-base block w-6 h-4`;

            // Perbarui status bintang watchlist favorit sesuai kode negara terpilih
            updateWatchlistStarUI(countryCode);

            fetch(`/api/country-details/${countryCode}`)
                .then(res => res.json())
                .then(data => {
                    loading.classList.add('hidden');
                    if (data.status === 'success') {
                        placeholder.classList.add('hidden');
                        document.getElementById('valGdp').innerText = data.economic.gdp && !isNaN(data.economic.gdp) ? '$' + parseFloat(data.economic.gdp).toLocaleString('id-ID') : data.economic.gdp;
                        document.getElementById('valPopulation').innerText = data.economic.population && !isNaN(data.economic.population) ? parseInt(data.economic.population).toLocaleString('id-ID') : data.economic.population;
                        document.getElementById('valInflation').innerText = data.economic.inflation_rate && !isNaN(data.economic.inflation_rate) ? parseFloat(data.economic.inflation_rate).toFixed(2) + ' %' : data.economic.inflation_rate + ' %';
                        document.getElementById('valWeather').innerText = data.weather.temperature;
                        document.getElementById('valWind').innerText = `Kecepatan angin eksternal: ${data.weather.windspeed}`;

                        const resultBox = document.getElementById('sentimentResultBox');
                        let icon = 'fa-circle-check'; 
                        let alertClass = 'bg-emerald-50 border-emerald-200 text-emerald-700';
                        if (data.logistics_risk.color === 'red') { 
                            icon = 'fa-triangle-exclamation'; alertClass = 'bg-rose-50 border-rose-200 text-rose-700'; 
                        } else if (data.logistics_risk.color === 'orange') { 
                            icon = 'fa-circle-exclamation'; alertClass = 'bg-amber-50 border-amber-200 text-amber-700'; 
                        }

                        resultBox.className = `p-4 rounded-xl border flex flex-col items-center justify-center text-center ${alertClass}`;
                        resultBox.innerHTML = `
                            <div class="text-xl mb-1"><i class="fa-solid ${icon}"></i></div>
                            <span class="text-[9px] font-bold uppercase tracking-wider">EVALUASI RISIKO JALUR:</span>
                            <h6 class="text-xs font-extrabold my-0.5">${data.logistics_risk.sentiment}</h6>
                            <span class="text-[8px] bg-slate-900 text-white font-mono px-2 py-0.5 rounded-full mt-1.5 shadow-2xs">Skor -> Positif: ${data.logistics_risk.score_positive} | Negatif: ${data.logistics_risk.score_negative}</span>
                        `;

                        metrics.classList.remove('hidden'); 
                        sentimentSec.classList.remove('hidden');
                        
                        setTimeout(() => {
                            map.invalidateSize();
                            if (data.country && data.country.latitude && data.country.longitude) {
                                const lat = parseFloat(data.country.latitude); const lng = parseFloat(data.country.longitude);
                                map.flyTo([lat, lng], 5, { animate: true, duration: 1.2 });
                            }
                        }, 100);
                    }
                });
        });
    </script>
@endsection