<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogiGuard - Shipping Cost Calculator</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <style>
        /* Sembunyikan area tanda tangan kalau di layar web biasa */
        .hanya-saat-print {
            display: none;
        }

        @media print {
            body { background: white !important; color: #000000 !important; font-size: 12px; }
            form, h1, p.text-slate-400, .btn-calc { display: none !important; }
            .bg-slate-800 { background: white !important; border: none !important; box-shadow: none !important; padding: 0 !important; margin: 0 !important; }
            
            #printHeader { display: flex !important; }
            #resultBox { display: block !important; background: white !important; border: none !important; padding: 0 !important; }
            
            .invoice-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 15px; }
            .metric-card { background: #f8fafc !important; border: 1px solid #e2e8f0 !important; }
            .total-box { background: #f1f5f9 !important; border: 1px solid #cbd5e1 !important; padding: 15px !important; }
            
            /* 🔥 FORMAT CETAK UNTUK KARTU RISIKO */
            #riskCard { background: #ffffff !important; border: 2px solid #000000 !important; padding: 12px !important; }
            #riskLevel { color: #000000 !important; font-weight: bold !important; }
            #riskMitigation { color: #334155 !important; }

            #resultBox *, #printHeader * { color: #000000 !important; }
            
            /* 🔥 PAKSA TANDA TANGAN MUNCUL HANYA SAAT DI-PRINT */
            .hanya-saat-print { 
                display: flex !important; 
                justify-content: flex-end !important;
                margin-top: 50px !important;
            }
            .hanya-saat-print * { color: #000000 !important; }
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen font-sans flex items-center justify-center p-4 pb-16">

    <div class="w-full max-w-3xl bg-slate-800 border border-slate-700 rounded-2xl p-8 shadow-2xl my-10">
        
        <h1 class="text-3xl font-black text-emerald-400 mb-2 flex items-center gap-2 print:hidden">🚢 Shipping Cost Calculator</h1>
        <p class="text-slate-400 text-sm mb-6 border-b border-slate-700 pb-4 print:hidden">Integrasi otomatis koordinat Overpass Port API & tren kurs mata uang riil database.</p>

        <div id="printHeader" class="hidden justify-between items-center border-b-2 border-slate-900 pb-4 mb-6">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">LOGIGUARD LOGISTICS SYSTEM</h2>
                <p class="text-xs text-slate-500">International Maritime Freight Forwarding Quotation</p>
            </div>
            <div class="text-right text-xs text-slate-600">
                <p><strong>Document ID:</strong> LG-QT-{{ rand(10000, 99999) }}</p>
                <p><strong>Date Generated:</strong> {{ date('d M Y / H:i') }} WIB</p>
            </div>
        </div>

        <form id="calcForm" class="space-y-5 print:hidden">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Pelabuhan Asal (Origin):</label>
                    <select name="origin_port" required class="w-full bg-slate-700 border border-slate-600 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                        @foreach($ports as $port)
                            <option value="{{ $port->id }}">{{ $port->port_name }} ({{ $port->country_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Pelabuhan Tujuan (Destination):</label>
                    <select name="destination_port" required class="w-full bg-slate-700 border border-slate-600 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                        @foreach($ports as $port)
                            <option value="{{ $port->id }}">{{ $port->port_name }} ({{ $port->country_code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Total Muatan Kontainer (Ton):</label>
                    <input type="number" name="weight" min="1" value="1" required class="w-full bg-slate-700 border border-slate-600 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Jenis Kontainer (Container Type):</label>
                    <select name="container_type" required class="w-full bg-slate-700 border border-slate-600 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                        <option value="std_20">20 Feet Standard Container (Tarif Normal)</option>
                        <option value="std_40">40 Feet Standard Container (Tarif 1.5x)</option>
                        <option value="reefer">Refrigerated Container / Reefer (Tarif Pendingin 2.2x)</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-calc w-full bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black py-3 px-6 rounded-xl transition-all shadow-lg shadow-emerald-500/20 cursor-pointer text-center">
                CALCULATE FREIGHT CHARGES ⚡
            </button>
        </form>

        <div id="resultBox" class="hidden mt-8 space-y-6">
            
            <div class="bg-slate-700/40 border-l-4 border-emerald-400 p-4 rounded-r-xl print:border-slate-900 print:bg-slate-50">
                <span class="text-xs font-bold text-slate-400 block uppercase tracking-wide print:text-slate-600">Transit Cargo Route</span>
                <span id="ruteResult" class="text-xl font-bold text-white print:text-slate-900"></span>
            </div>

            <div class="invoice-grid grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="metric-card bg-slate-700/30 border border-slate-600/50 p-4 rounded-xl">
                    <span class="text-xs text-slate-400 block font-semibold">📏 JARAK TEMPUH PELAYARAN</span>
                    <span id="jarakResult" class="text-lg font-bold text-slate-100 mt-1 block"></span>
                </div>
                <div class="metric-card bg-slate-700/30 border border-slate-600/50 p-4 rounded-xl">
                    <span class="text-xs text-slate-400 block font-semibold">⏳ ESTIMASI WAKTU TIBA (ETA)</span>
                    <span id="etaResult" class="text-lg font-bold text-amber-400 mt-1 block"></span>
                </div>
                <div class="metric-card bg-slate-700/30 border border-slate-600/50 p-4 rounded-xl">
                    <span class="text-xs text-slate-400 block font-semibold">💵 BIAYA DASAR (GLOBAL FREIGHT)</span>
                    <span id="usdResult" class="text-lg font-bold text-emerald-400 mt-1 block"></span>
                </div>
                <div class="metric-card bg-slate-700/30 border border-slate-600/50 p-4 rounded-xl">
                    <span class="text-xs text-slate-400 block font-semibold">💱 KURS DATABASE TERPAKAI</span>
                    <span id="rateResult" class="text-sm font-medium text-slate-300 mt-1 block italic"></span>
                </div>
            </div>

            <div id="riskCard" class="border p-4 rounded-xl flex flex-col gap-1 transition-all">
                <span class="text-xs font-black uppercase tracking-wider block text-slate-400 print:text-slate-600">🚨 LOGIGUARD RISK MANAGEMENT ALERTS:</span>
                <span id="riskLevel" class="text-sm font-extrabold block"></span>
                <p id="riskMitigation" class="text-xs mt-1 leading-relaxed text-slate-300"></p>
            </div>

            <div class="total-box bg-slate-900/60 border border-slate-600 p-6 rounded-xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <span class="text-xs uppercase font-extrabold text-emerald-400 tracking-wider block print:text-slate-600">TOTAL NET FREIGHT CHARGE (IDR)</span>
                    <span id="idrResult" class="text-3xl font-black text-white tracking-tight"></span>
                </div>
                <div class="text-left md:text-right text-xs text-slate-400 print:text-slate-500">
                    <p>• Term: Ocean Freight Prepaid</p>
                    <p>• Status: Calculated Successfully</p>
                </div>
            </div>

            <div class="hanya-saat-print text-xs">
                <div class="text-center" style="width: 200px;">
                    <p style="margin-bottom: 60px;">Authorized Logistics Officer,</p>
                    <p class="font-bold" style="border-top: 1px solid #000000; padding-top: 4px;">LogiGuard Operations Team</p>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-700/50 flex justify-between items-center print:hidden">
                <p class="text-xs text-slate-500 italic">Valid data powered by LogiGuard Core Analytics Engine.</p>
                <button type="button" onclick="window.print()" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black py-2.5 px-6 rounded-xl text-xs transition-all cursor-pointer shadow-md tracking-wider uppercase">
                    🖨️ Export Corporate Invoice
                </button>
            </div>
        </div>
    </div>

    <script>
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
                    
                    // 🔥 UPDATE DATA KARTU RISIKO SECARA LIVE LEWAT AJAX
                    const riskCard = document.getElementById('riskCard');
                    riskCard.className = `border p-4 rounded-xl flex flex-col gap-1 transition-all ${res.risk_color}`;
                    document.getElementById('riskLevel').innerText = res.risk_level;
                    document.getElementById('riskMitigation').innerText = res.mitigation;
                }
            });
        });
    </script>
</body>
</html>