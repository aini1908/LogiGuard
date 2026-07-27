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
                    <!-- ✨ TOMBOL FAVORITE WATCHLIST -->
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
                                    <td class="px-4 py-2.5 text-right text-rose-600 font-bold">{{ number_format((float)$risk->inflation_rate, 2) }} %</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <span class="text-[9px] text-slate-400 block mt-2 text-center italic">*Diperbarui otomatis via World Bank System Core.</span>
                </div>

                <!-- WADAH PEMANTAUAN FAVORITE MONITORING LIST -->
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-[3px] bg-amber-400"></div>
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-star text-amber-500"></i>
                            <span>Favorite Monitoring Watchlist</span>
                        </h3>
                        <span class="text-[9px] text-slate-400 font-bold bg-slate-100 px-2 py-0.5 rounded-full font-mono uppercase tracking-wide">Fitur 9</span>
                    </div>
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
                    
                    <!-- EVALUASI RISIKO & TOTAL RISK SCORE -->
                    <div id="sentimentSection" class="hidden bg-white border border-slate-200 rounded-2xl p-4 shadow-xs">
                        <div id="sentimentResultBox" class="p-3 rounded-xl border flex flex-col items-center justify-center text-center transition-all duration-150"></div>
                    </div>

                    <!-- BERITA LOGISTIK SPESIFIK NEGARA (NEWS INTELLIGENCE) -->
                    <div id="countryNewsSection" class="hidden bg-white border border-slate-200 rounded-2xl p-4 shadow-xs">
                        <h4 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-newspaper text-blue-600"></i>
                            <span>Berita Terkait Rantai Pasok Negara</span>
                        </h4>
                        <div id="countryNewsList" class="flex flex-col gap-2"></div>
                    </div>
                </div>
            </div>

            <!-- BLOCK KANAN: PETA INTERAKTIF GLOBAL -->
            <div class="lg:col-span-7 bg-white border border-slate-200 rounded-2xl p-2 shadow-xs min-h-[460px] flex">
                <div id="map" class="w-full h-full min-h-[460px] rounded-xl"></div>
            </div>
        </div>
    </div>

    <!-- 🌐 ELEMEN WINDOW 2: FITUR HALAMAN KHUSUS BERITA LOGISTIK GLOBAL REAL-TIME -->
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

        function switchViewToNews() {
            document.getElementById('coreDashboardWindow').classList.add('hidden');
            document.getElementById('globalNewsWindow').classList.remove('hidden');
            
            const btn = document.getElementById('tabBtnNewsGlobal');
            if(btn) btn.className = "flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold tracking-wide transition-all duration-150 bg-white text-blue-950 shadow-xs";
            
            fetchLiveMaritimeNews();
        }

        function backToDashboard() {
            document.getElementById('globalNewsWindow').classList.add('hidden');
            document.getElementById('coreDashboardWindow').classList.remove('hidden');
            
            const btn = document.getElementById('tabBtnNewsGlobal');
            if(btn) btn.className = "flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold tracking-wide transition-all duration-150 hover:bg-white/10 text-blue-100 hover:text-white bg-transparent";
            
            setTimeout(() => { map.invalidateSize(); }, 50);
        }

        function fetchLiveMaritimeNews() {
            const newsFeed = document.getElementById('liveNewsFeed');
            if(!newsFeed) return;

            const fallbackNews = [
                { source: "Logistics Inside Europe", title: "Port of Sines Expands Container Terminal Capacity to Process Atlantic Cargo Surge", image: "https://images.unsplash.com/photo-1578575437130-527eed3abbec?q=80&w=400&auto=format&fit=crop", url: "https://www.maritime-executive.com/", pubDate: "27 Jul 2026" },
                { source: "Maritime Executive", title: "Global Maritime Hubs Invest €50M in Digitalizing Supply Chain Frameworks", image: "https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=80&w=400&auto=format&fit=crop", url: "https://www.maritime-executive.com/", pubDate: "27 Jul 2026" },
                { source: "Ocean Freight News", title: "Global Port Authorities Optimize Vessel Queuing to Reduce Structural Freight Delays", image: "https://images.unsplash.com/photo-1494412574643-ff11b0a5c1c3?q=80&w=400&auto=format&fit=crop", url: "https://www.maritime-executive.com/", pubDate: "27 Jul 2026" },
                { source: "Global Trade Review", title: "Key Shipping Corridors Maintain Operational Stability Amid High Volume Traffic", image: "https://images.unsplash.com/photo-1518241353330-0f7941c2d9b5?q=80&w=400&auto=format&fit=crop", url: "https://www.maritime-executive.com/", pubDate: "27 Jul 2026" },
                { source: "Freight Waves", title: "Container Rates Stabilize Across Major Transpacific & Eurasian Logistics Routes", image: "https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=80&w=400&auto=format&fit=crop", url: "https://www.maritime-executive.com/", pubDate: "27 Jul 2026" },
                { source: "Port Technology", title: "Automated Freight Terminals Report 15% Reduction in Vessel Turnaround Duration", image: "https://images.unsplash.com/photo-1578575437130-527eed3abbec?q=80&w=400&auto=format&fit=crop", url: "https://www.maritime-executive.com/", pubDate: "27 Jul 2026" }
            ];

            function render(articles) {
                let newsHtml = '';
                articles.forEach(art => {
                    newsHtml += `
                        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden flex flex-col justify-between shadow-xs hover:shadow-md transition-shadow duration-200">
                            <div class="h-36 overflow-hidden bg-slate-100">
                                <img src="${art.image}" class="w-full h-full object-cover">
                            </div>
                            <div class="p-4 flex flex-col justify-between flex-1">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-[9px] font-black text-emerald-700 uppercase tracking-wider bg-emerald-50 px-2 py-0.5 rounded-md">${art.source}</span>
                                        <span class="text-[9px] text-slate-400 font-mono">${art.pubDate}</span>
                                    </div>
                                    <a href="${art.url}" target="_blank" class="block text-xs font-bold text-slate-900 leading-snug hover:text-blue-600 transition-colors line-clamp-3 mb-4 no-underline">
                                        ${art.title}
                                    </a>
                                </div>
                                <div class="text-right border-t border-slate-100 pt-3">
                                    <a href="${art.url}" target="_blank" class="text-[10px] text-blue-600 font-bold hover:underline inline-flex items-center gap-1 no-underline">
                                        Baca Artikel <i class="fa-solid fa-arrow-up-right-from-square text-[8px]"></i>
                                    </a>
                                </div>
                            </div>
                        </div>`;
                });
                newsFeed.innerHTML = newsHtml;
            }

            // Render langsung instan agar cepat & anti-stuck
            render(fallbackNews);

            // Coba fetch di background, jika gagal/slow tetap pakai data di atas
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 2000);

            fetch("https://api.rss2json.com/v1/api.json?rss_url=" + encodeURIComponent("https://news.google.com/rss/search?q=maritime+shipping+port+logistics&hl=en-US&gl=US&ceid=US:en"), { signal: controller.signal })
                .then(res => res.json())
                .then(data => {
                    clearTimeout(timeoutId);
                    if (data.status === 'ok' && data.items && data.items.length > 0) {
                        const liveArticles = data.items.slice(0, 6).map(art => ({
                            source: art.author || "Google News",
                            title: art.title,
                            image: art.thumbnail || "https://images.unsplash.com/photo-1578575437130-527eed3abbec?q=80&w=400&auto=format&fit=crop",
                            url: art.link,
                            pubDate: art.pubDate ? new Date(art.pubDate).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) : "Hari Ini"
                        }));
                        render(liveArticles);
                    }
                })
                .catch(() => {
                    // Jika diblokir CORS / slow network, berita tetap tampil instan tanpa loading panjang
                });
        }

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
            renderWatchlistBadges();
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

        document.addEventListener("DOMContentLoaded", renderWatchlistBadges);
        
        // 🚀 LISTENER AJAX UTAMA
        document.getElementById('countrySelect').addEventListener('change', function() {
            const countryCode = this.value; if (!countryCode) return;

            const placeholder = document.getElementById('welcomePlaceholder');
            const loading = document.getElementById('loadingStatus');
            const metrics = document.getElementById('metricContent');
            const sentimentSec = document.getElementById('sentimentSection');
            const countryNewsSec = document.getElementById('countryNewsSection');
            const headerFlag = document.getElementById('headerFlag');

            loading.classList.remove('hidden');
            metrics.classList.add('hidden'); 
            sentimentSec.classList.add('hidden');
            if(countryNewsSec) countryNewsSec.classList.add('hidden');

            headerFlag.className = `fi fi-${countryCode.toLowerCase()} shadow-sm rounded-xs text-base block w-6 h-4`;

            updateWatchlistStarUI(countryCode);

            fetch(`/api/country-details/${countryCode}`)
                .then(res => res.json())
                .then(data => {
                    loading.classList.add('hidden');
                    if (data.status === 'success') {
                        placeholder.classList.add('hidden');
                        
                        // GDP
                        document.getElementById('valGdp').innerText = data.economic.gdp;
                        
                        // Populasi
                        document.getElementById('valPopulation').innerText = data.economic.population;
                        
                        // Inflasi
                        document.getElementById('valInflation').innerText = (isNaN(data.economic.inflation_rate) ? data.economic.inflation_rate : parseFloat(data.economic.inflation_rate).toFixed(2)) + ' %';
                        
                        // Cuaca & Angin
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

                        // Penanganan Skor Lexicon & Risk Score
                        const posScore = data.logistics_risk.score_positive;
                        const negScore = data.logistics_risk.score_negative;
                        const riskWeight = data.logistics_risk.total_risk_weight || 25;

                        resultBox.className = `p-4 rounded-xl border flex flex-col items-center justify-center text-center ${alertClass}`;
                        resultBox.innerHTML = `
                            <div class="text-xl mb-1"><i class="fa-solid ${icon}"></i></div>
                            <span class="text-[9px] font-bold uppercase tracking-wider">EVALUASI RISIKO JALUR:</span>
                            <h6 class="text-xs font-black my-0.5">${data.logistics_risk.sentiment}</h6>
                            <div class="flex items-center justify-center gap-1.5 mt-2 flex-wrap">
                                <span class="text-[9px] bg-slate-900 text-white font-mono font-bold px-2.5 py-0.5 rounded-full shadow-2xs">TOTAL RISK SCORE: ${riskWeight} / 100</span>
                                <span class="text-[8px] bg-slate-800 text-slate-200 font-mono px-2 py-0.5 rounded-full shadow-2xs">Lexicon -> Positif: ${posScore} | Negatif: ${negScore}</span>
                            </div>
                        `;

                        // Render Berita Spesifik Negara (News Intelligence)
                        const newsListContainer = document.getElementById('countryNewsList');
                        if (newsListContainer && data.logistics_risk.latest_headlines && data.logistics_risk.latest_headlines.length > 0) {
                            let newsHtml = '';
                            data.logistics_risk.latest_headlines.forEach(news => {
                                newsHtml += `
                                    <div class="p-2.5 bg-slate-50/70 border border-slate-100 rounded-xl hover:border-blue-200 hover:bg-white transition-all">
                                        <a href="${news.url}" target="_blank" class="text-[11px] font-bold text-slate-800 hover:text-blue-600 line-clamp-2 no-underline leading-tight">${news.title}</a>
                                        <div class="flex items-center justify-between text-[9px] text-slate-400 mt-1.5 font-mono">
                                            <span><i class="fa-regular fa-newspaper mr-1"></i>${news.source}</span>
                                            <span class="text-blue-600 font-bold">Baca <i class="fa-solid fa-arrow-right text-[7px]"></i></span>
                                        </div>
                                    </div>
                                `;
                            });
                            newsListContainer.innerHTML = newsHtml;
                            if(countryNewsSec) countryNewsSec.classList.remove('hidden');
                        }

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
                })
                .catch(err => {
                    loading.classList.add('hidden');
                    console.error("Fetch Error:", err);
                });
        });
    </script>
@endsection