@extends('layouts.app')

@section('title', 'Country Comparison Engine')

@section('styles')
    <!-- Pastikan Flag Icons terintegrasi dengan baik -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css" />
@endsection

@section('content')
    <!-- 🎛️ HEADER HALAMAN -->
    <div class="border-b border-slate-200 pb-5 mb-6">
        <h1 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
            <span>Country Comparison Engine</span>
            <i class="fa-solid fa-scale-balanced text-blue-600 text-sm"></i>
        </h1>
        <p class="text-xs text-slate-500 mt-0.5">Komparasi parameter makroekonomi, cuaca operasional, dan indeks risiko antar negara destinasi logistik secara "side-by-side".</p>
    </div>

    <!-- 🔄 PANEL SELECTOR NEGARA (Kanan & Kiri) -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs mb-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-600 via-purple-600 to-cyan-500"></div>
        
        <div class="grid grid-cols-1 md:grid-cols-11 gap-4 items-center">
            <!-- Negara Pertama (A) -->
            <div class="md:col-span-5">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span> Negara Pertama (A):
                </label>
                <div class="relative">
                    <select id="countrySelectA" onchange="resetComparisonView()" class="w-full appearance-none bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-700 shadow-2xs focus:outline-none focus:border-blue-500 cursor-pointer pr-10">
                        <option value="">-- Pilih Negara A --</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->country_code }}">{{ strtoupper($country->country_code) }} - {{ $country->name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 border-l border-slate-200">
                        <i class="fa-solid fa-chevron-down text-[10px]"></i>
                    </div>
                </div>
            </div>

            <!-- VS Badge Interseptor -->
            <div class="md:col-span-1 flex justify-center pt-3 md:pt-4">
                <span class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-[10px] font-black text-slate-400 tracking-tighter shadow-2xs">VS</span>
            </div>

            <!-- Negara Kedua (B) -->
            <div class="md:col-span-5">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-cyan-500"></span> Negara Kedua (B):
                </label>
                <div class="relative">
                    <select id="countrySelectB" onchange="resetComparisonView()" class="w-full appearance-none bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-700 shadow-2xs focus:outline-none focus:border-cyan-500 cursor-pointer pr-10">
                        <option value="">-- Pilih Negara B --</option>
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

        <div class="mt-5 text-center">
            <button onclick="executeMatrixComparison()" class="bg-slate-900 hover:bg-slate-800 text-white text-[11px] font-bold py-2.5 px-6 rounded-xl shadow-xs uppercase tracking-wide transition-all cursor-pointer inline-flex items-center gap-2">
                <i class="fa-solid fa-gavel text-[10px]"></i> Hitung Matriks Komparasi
            </button>
        </div>
    </div>

    <!-- 📊 DOCK WINDOW MATRIX HASIL KOMPARASI -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs mb-12">
        <!-- Placeholder Kosong Awal -->
        <div id="comparisonPlaceholder" class="py-12 text-center">
            <div class="w-12 h-12 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 mx-auto mb-3 shadow-2xs">
                <i class="fa-solid fa-chart-columns text-sm"></i>
            </div>
            <h4 class="text-xs font-bold text-slate-900">Sistem Matriks Belum Dieksekusi</h4>
            <p class="text-[11px] text-slate-400 max-w-xs mx-auto mt-0.5">Tentukan dua negara destinasi pada menu dropdown di atas, kemudian tekan tombol hitung untuk menghasilkan analisis komparatif rute kargo.</p>
        </div>

        <!-- Tampilan Konten Komparasi Utama (Hidden Awal) -->
        <div id="comparisonContent" class="hidden space-y-4">
            
            <!-- Baris Judul Identitas Kepala Negara -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-slate-100 pb-4 items-center text-center md:text-left">
                <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Parameter Analisis</div>
                <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-3 flex items-center justify-center gap-2.5">
                    <span id="flagIconA" class="shadow-xs rounded-xs w-5 h-3.5 block"></span>
                    <span id="labelCountryA" class="text-xs font-black text-blue-950 uppercase tracking-wide">-</span>
                </div>
                <div class="bg-cyan-50/50 border border-cyan-100 rounded-xl p-3 flex items-center justify-center gap-2.5">
                    <span id="flagIconB" class="shadow-xs rounded-xs w-5 h-3.5 block"></span>
                    <span id="labelCountryB" class="text-xs font-black text-cyan-950 uppercase tracking-wide">-</span>
                </div>
            </div>

            <!-- Jajaran Parameter Rows (Card-Style Modern) -->
            <!-- 1. GROSS DOMESTIC PRODUCT -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center border border-slate-100 rounded-xl p-3 bg-slate-50/30">
                <div class="flex items-center gap-2.5 text-xs font-bold text-slate-500">
                    <div class="w-6 h-6 bg-white border border-slate-200 rounded-md flex items-center justify-center text-blue-600 shadow-2xs text-[10px]"><i class="fa-solid fa-coins"></i></div>
                    <span>Gross Domestic Product</span>
                </div>
                <div id="wrapperGdpA" class="p-2 text-center rounded-lg font-black text-xs text-slate-800 transition-colors">-</div>
                <div id="wrapperGdpB" class="p-2 text-center rounded-lg font-black text-xs text-slate-800 transition-colors">-</div>
            </div>

            <!-- 2. TINGKAT INFLASI -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center border border-slate-100 rounded-xl p-3 bg-slate-50/30">
                <div class="flex items-center gap-2.5 text-xs font-bold text-slate-500">
                    <div class="w-6 h-6 bg-white border border-slate-200 rounded-md flex items-center justify-center text-rose-500 shadow-2xs text-[10px]"><i class="fa-solid fa-arrow-trend-up"></i></div>
                    <span>Tingkat Inflasi Tahunan</span>
                </div>
                <div id="wrapperInflationA" class="p-2 text-center rounded-lg font-black text-xs text-slate-800 transition-colors">-</div>
                <div id="wrapperInflationB" class="p-2 text-center rounded-lg font-black text-xs text-slate-800 transition-colors">-</div>
            </div>

            <!-- 3. CUACA IBU KOTA -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center border border-slate-100 rounded-xl p-3 bg-slate-50/30">
                <div class="flex items-center gap-2.5 text-xs font-bold text-slate-500">
                    <div class="w-6 h-6 bg-white border border-slate-200 rounded-md flex items-center justify-center text-sky-500 shadow-2xs text-[10px]"><i class="fa-solid fa-cloud-sun-rain"></i></div>
                    <span>Cuaca Ibu Kota & Angin</span>
                </div>
                <div id="wrapperWeatherA" class="p-2 text-center rounded-lg font-bold text-xs text-slate-800">-</div>
                <div id="wrapperWeatherB" class="p-2 text-center rounded-lg font-bold text-xs text-slate-800">-</div>
            </div>

            <!-- 4. AKUMULASI SKOR RISIKO -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center border border-slate-100 rounded-xl p-3 bg-slate-50/30">
                <div class="flex items-center gap-2.5 text-xs font-bold text-slate-500">
                    <div class="w-6 h-6 bg-white border border-slate-200 rounded-md flex items-center justify-center text-amber-500 shadow-2xs text-[10px]"><i class="fa-solid fa-shield-halved"></i></div>
                    <span>Akumulasi Skor Risiko</span>
                </div>
                <div class="flex justify-center" id="containerRiskA">-</div>
                <div class="flex justify-center" id="containerRiskB">-</div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function resetComparisonView() {
            document.getElementById('comparisonContent').classList.add('hidden');
            document.getElementById('comparisonPlaceholder').classList.remove('hidden');
        }

        function executeMatrixComparison() {
            const codeA = document.getElementById('countrySelectA').value;
            const codeB = document.getElementById('countrySelectB').value;

            if (!codeA || !codeB) {
                alert("Harap pilih kedua negara terlebih dahulu sebelum melakukan kalkulasi matriks!");
                return;
            }

            if (codeA === codeB) {
                alert("Gagal memproses! Anda tidak dapat membandingkan entitas negara yang sama.");
                return;
            }

            // Ambil nama teks teks negara dari elemen opsi terpilih
            const selA = document.getElementById('countrySelectA');
            const nameA = selA.options[selA.selectedIndex].text.split(' - ')[1];
            const selB = document.getElementById('countrySelectB');
            const nameB = selB.options[selB.selectedIndex].text.split(' - ')[1];

            // Trigger animasi loading pemrosesan
            document.getElementById('comparisonPlaceholder').classList.add('hidden');
            const content = document.getElementById('comparisonContent');
            content.classList.remove('hidden');

            // Set Identitas Kepala & Pasang Bendera Secara Dinamis
            document.getElementById('flagIconA').className = `fi fi-${codeA.toLowerCase()} shadow-xs rounded-xs w-5 h-3.5 block`;
            document.getElementById('flagIconB').className = `fi fi-${codeB.toLowerCase()} shadow-xs rounded-xs w-5 h-3.5 block`;
            document.getElementById('labelCountryA').innerText = nameA;
            document.getElementById('labelCountryB').innerText = nameB;

            // Panggil API Backend Berurutan untuk mengambil rincian data
            Promise.all([
                fetch(`/api/country-details/${codeA}`).then(res => res.json()),
                fetch(`/api/country-details/${codeB}`).then(res => res.json())
            ]).then(([dataA, dataB]) => {
                if (dataA.status === 'success' && dataB.status === 'success') {
                    
                    // --- PARSING & RENDER DATA VALUE ---
                    const rawGdpA = dataA.economic.gdp && !isNaN(dataA.economic.gdp) ? parseFloat(dataA.economic.gdp) : 0;
                    const rawGdpB = dataB.economic.gdp && !isNaN(dataB.economic.gdp) ? parseFloat(dataB.economic.gdp) : 0;
                    
                    document.getElementById('wrapperGdpA').innerText = rawGdpA > 0 ? '$' + rawGdpA.toLocaleString('id-ID') : 'N/A';
                    document.getElementById('wrapperGdpB').innerText = rawGdpB > 0 ? '$' + rawGdpB.toLocaleString('id-ID') : 'N/A';

                    const infA = dataA.economic.inflation_rate && !isNaN(dataA.economic.inflation_rate) ? parseFloat(dataA.economic.inflation_rate) : 0;
                    const infB = dataB.economic.inflation_rate && !isNaN(dataB.economic.inflation_rate) ? parseFloat(dataB.economic.inflation_rate) : 0;

                    document.getElementById('wrapperInflationA').innerText = infA.toFixed(2) + ' %';
                    document.getElementById('wrapperInflationB').innerText = infB.toFixed(2) + ' %';

                    document.getElementById('wrapperWeatherA').innerHTML = `${dataA.weather.temperature} <span class="text-[10px] text-slate-400 block font-normal font-sans">Angin: ${dataA.weather.windspeed}</span>`;
                    document.getElementById('wrapperWeatherB').innerHTML = `${dataB.weather.temperature} <span class="text-[10px] text-slate-400 block font-normal font-sans">Angin: ${dataB.weather.windspeed}</span>`;

                    const ptsA = parseInt(dataA.logistics_risk.score_positive || 50);
                    const ptsB = parseInt(dataB.logistics_risk.score_positive || 50);

                    document.getElementById('containerRiskA').innerHTML = `<span class="px-3 py-1 rounded-full bg-slate-900 text-white font-mono text-[10px] font-bold shadow-2xs">${ptsA} Pts</span>`;
                    document.getElementById('containerRiskB').innerHTML = `<span class="px-3 py-1 rounded-full bg-slate-900 text-white font-mono text-[10px] font-bold shadow-2xs">${ptsB} Pts</span>`;

                    // --- 🔥 STRATEGI HIGHLIGHTING PEMENANG SECARA INTERAKTIF ---
                    const winClass = "bg-emerald-50 border border-emerald-200 text-emerald-700 font-extrabold shadow-3xs";
                    const normalClass = "p-2 text-center rounded-lg font-black text-xs text-slate-800 transition-colors";

                    // Reset Class
                    document.getElementById('wrapperGdpA').className = normalClass;
                    document.getElementById('wrapperGdpB').className = normalClass;
                    document.getElementById('wrapperInflationA').className = normalClass;
                    document.getElementById('wrapperInflationB').className = normalClass;

                    // Gdp Winner (Semakin Tinggi Semakin Baik Ekonominya)
                    if (rawGdpA > rawGdpB) {
                        document.getElementById('wrapperGdpA').className = `${normalClass} ${winClass}`;
                    } else if (rawGdpB > rawGdpA) {
                        document.getElementById('wrapperGdpB').className = `${normalClass} ${winClass}`;
                    }

                    // Inflation Winner (Semakin Rendah Semakin Bagus Jalur Stabil)
                    if (infA < infB) {
                        document.getElementById('wrapperInflationA').className = `${normalClass} ${winClass}`;
                    } else if (infB < infA) {
                        document.getElementById('wrapperInflationB').className = `${normalClass} ${winClass}`;
                    }
                }
            }).catch(err => {
                alert("Gagal melakukan sinkronisasi komparatif multi-API.");
                resetComparisonView();
            });
        }
    </script>
@endsection