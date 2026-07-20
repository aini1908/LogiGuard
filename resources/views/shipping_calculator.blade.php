@extends('layouts.app')

@section('title', 'Shipping & Risk Core')

@section('styles')
    <style>
        .hanya-saat-print { display: none; }
        @media print {
            body { background: white !important; color: #000000 !important; font-size: 11px; p: 0; }
            .no-print { display: none !important; }
            #printHeader { display: flex !important; }
            #resultBox { display: block !important; background: white !important; border: none !important; padding: 0 !important; }
            .invoice-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 15px; }
            .metric-card { background: #f8fafc !important; border: 1px solid #e2e8f0 !important; }
            .total-box { background: #f1f5f9 !important; border: 1px solid #cbd5e1 !important; padding: 15px !important; }
            #riskCard { background: #ffffff !important; border: 1px solid #000000 !important; padding: 12px !important; }
            #riskLevel { color: #000000 !important; font-weight: bold !important; }
            #riskMitigation { color: #334155 !important; }
            #resultBox *, #printHeader * { color: #000000 !important; }
            .hanya-saat-print { display: flex !important; justify-content: flex-end !important; margin-top: 40px !important; }
            .hanya-saat-print * { color: #000000 !important; }
        }
        
        select, input {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2364748b' viewBox='0 0 16 16'><path fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/></svg>");
            background-repeat: no-repeat;
            background-position: right 14px center;
        }
        input[type=number] {
            background-image: none;
        }
    </style>
@endsection

@section('content')
    <!-- Main Form Card -->
    <div class="w-full bg-white border border-slate-200 rounded-2xl p-6 md:p-8 shadow-sm transition-all duration-300">
        
        <!-- Form Input Minimalis & Modis Terstandarisasi -->
        <form id="calcForm" class="space-y-6 no-print">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">Pelabuhan Asal (Origin)</label>
                    <!-- 🏢 Diberi ID id="originPortSelect" agar mudah ditangkap oleh script JavaScript -->
                    <select id="originPortSelect" name="origin_port" required class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-xs text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition-all font-medium shadow-inner">
                        @foreach($ports as $port)
                            <option value="{{ $port->id }}">{{ $port->port_name }} ({{ $port->country_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">Pelabuhan Tujuan (Destination)</label>
                    <select name="destination_port" required class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-xs text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition-all font-medium shadow-inner">
                        @foreach($ports as $port)
                            <option value="{{ $port->id }}">{{ $port->port_name }} ({{ $port->country_code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">Muatan Kargo (Ton)</label>
                    <input type="number" name="weight" min="1" value="1" required class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-xs text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition-all font-medium shadow-inner">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">Tipe Fleet Kontainer</label>
                    <select name="container_type" required class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-xs text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition-all font-medium shadow-inner">
                        <option value="std_20">20ft Standard Container</option>
                        <option value="std_40">40ft Standard Container (1.5x)</option>
                        <option value="reefer">Refrigerated Container (2.2x)</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 text-white text-xs font-extrabold tracking-widest py-3 px-6 rounded-xl transition-all duration-300 shadow-sm hover:shadow-md cursor-pointer uppercase">
                Analyze Fleet Charges ⚡
            </button>
        </form>

        <!-- 📊 OUTPUT DATA POP-UP -->
        <div id="resultBox" class="hidden mt-8 space-y-6 border-t border-slate-100 pt-6">
            
            <!-- 🏢 HEADER INVOICE PDF -->
            <div id="printHeader" class="hidden justify-between items-center border-b-2 border-slate-900 pb-4 mb-6">
                <div>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">LOGIGUARD LOGISTICS SYSTEM</h2>
                    <p class="text-[10px] text-slate-500">Maritime Freight Forwarding Quotation Document</p>
                </div>
                <div class="text-right text-[10px] text-slate-600">
                    <p><strong>Quotation ID:</strong> LG-QT-{{ rand(10000, 99999) }}</p>
                    <p><strong>Date Issued:</strong> {{ date('d M Y / H:i') }} WIB</p>
                </div>
            </div>

            <!-- Rute Card -->
            <div class="bg-blue-50/40 border border-blue-100/70 p-4 rounded-xl flex items-center justify-between print:bg-slate-50 print:border-slate-900">
                <div>
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Transit Vector</span>
                    <span id="ruteResult" class="text-sm font-bold text-slate-900 mt-0.5 block"></span>
                </div>
            </div>

            <!-- Pop-up Metrik Grid -->
            <div class="invoice-grid grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="metric-card bg-white border border-slate-200 p-5 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                    <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider block">Pelayaran Jarak</span>
                    <span id="jarakResult" class="text-base font-extrabold text-slate-900 mt-1 block"></span>
                </div>
                <div class="metric-card bg-white border border-slate-200 p-5 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                    <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider block">Estimasi ETA</span>
                    <span id="etaResult" class="text-base font-extrabold text-blue-600 mt-1 block"></span>
                </div>
                <div class="metric-card bg-white border border-slate-200 p-5 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                    <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider block">Biaya Dasar (USD)</span>
                    <span id="usdResult" class="text-base font-extrabold text-slate-900 mt-1 block font-mono"></span>
                </div>
                <div class="metric-card bg-white border border-slate-200 p-5 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                    <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider block">Konversi Basis Kurs</span>
                    <span id="rateResult" class="text-xs font-bold text-cyan-600 mt-1 block font-mono"></span>
                </div>
            </div>

            <!-- Alert Risiko Modis -->
            <div id="riskCard" class="border p-4 rounded-xl flex flex-col gap-1 transition-all duration-300">
                <span class="text-[9px] font-black uppercase tracking-widest block">Core Risk Management System:</span>
                <span id="riskLevel" class="text-xs font-extrabold block"></span>
                <p id="riskMitigation" class="text-[11px] mt-1 leading-relaxed text-slate-600"></p>
            </div>

            <!-- Total Harga -->
            <div class="total-box bg-slate-900 border border-slate-900 p-5 rounded-xl flex justify-between items-center text-white shadow-md">
                <div>
                    <span class="text-[9px] uppercase font-bold text-slate-400 tracking-widest block">Total Net Charges (IDR)</span>
                    <span id="idrResult" class="text-2xl font-black text-white tracking-tight mt-1 block"></span>
                </div>
                <div class="text-right text-[9px] text-slate-400 font-mono hidden md:block">
                    <p>Freight: Ocean Prepaid</p>
                    <p>Engine status: Nominal</p>
                </div>
            </div>

            <!-- Signature Line -->
            <div class="hanya-saat-print text-[10px]">
                <div class="text-center" style="width: 180px;">
                    <p class="mb-14">Logistics Operations Officer,</p>
                    <p class="font-bold" style="border-top: 1px solid #000000; padding-top: 4px;">LogiGuard System Anchor</p>
                </div>
            </div>

            <!-- Action Button -->
            <div class="pt-4 border-t border-slate-100 flex justify-end no-print">
                <button type="button" onclick="window.print()" class="bg-white hover:bg-slate-50 text-blue-600 border border-blue-200 hover:border-blue-400 text-[10px] font-extrabold py-2 px-5 rounded-xl transition-all shadow-sm tracking-widest uppercase cursor-pointer">
                    📥 Export Invoice PDF
                </button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // 🌟 DI SINI LOGIKA BARU PENANGKAP OTOMATIS DARI LOKASI PELABUHAN 
        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);
            const originPortName = urlParams.get('origin_port');

            if (originPortName) {
                const originSelect = document.getElementById('originPortSelect');
                if (originSelect) {
                    // Loop opsi dropdown untuk mencocokkan teks pelabuhan asal
                    for (let i = 0; i < originSelect.options.length; i++) {
                        if (originSelect.options[i].text.toLowerCase().includes(decodeURIComponent(originPortName).toLowerCase())) {
                            originSelect.selectedIndex = i;
                            
                            // Transformasi UI: Berikan background abu-abu premium pertanda terkunci aman
                            originSelect.classList.add('bg-slate-100', 'text-slate-500', 'cursor-not-allowed');
                            
                            // Kunci opsi lain agar fokus ke pemilihan tujuan saja
                            originSelect.addEventListener('change', function(e) {
                                originSelect.selectedIndex = i;
                            });
                            break;
                        }
                    }
                }
            }
        });

        // Logika Submit Form Bawaan AJAX Anda
        document.getElementById('calcForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('/api/calculate-shipping', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(res => {
                if(res.status === 'success') {
                    document.getElementById('resultBox').classList.remove('hidden');
                    document.getElementById('ruteResult').innerText = `${res.origin_port} ➔ ${res.destination_port}`;
                    document.getElementById('jarakResult').innerText = res.distance;
                    document.getElementById('etaResult').innerText = res.eta;
                    document.getElementById('usdResult').innerText = `$ ${res.cost_usd.toLocaleString('en-US')} USD`;
                    document.getElementById('idrResult').innerText = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(res.cost_idr);
                    document.getElementById('rateResult').innerText = `1 USD = Rp${res.exchange_rate_used.toLocaleString('id-ID')}`;
                    
                    const riskCard = document.getElementById('riskCard');
                    let customColor = res.risk_color;
                    
                    if (res.risk_level.includes('High')) {
                        customColor = 'text-red-700 border-red-200 bg-red-50';
                    } else if (res.risk_level.includes('Medium')) {
                        customColor = 'text-amber-700 border-amber-200 bg-amber-50';
                    } else {
                        customColor = 'text-emerald-700 border-emerald-200 bg-emerald-50';
                    }
                    
                    riskCard.className = `border p-4 rounded-xl flex flex-col gap-1 transition-all duration-300 ${customColor}`;
                    document.getElementById('riskLevel').innerText = res.risk_level;
                    document.getElementById('riskMitigation').innerText = res.mitigation;
                }
            });
        });
    </script>
@endsection