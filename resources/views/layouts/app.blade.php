<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogiGuard Core - @yield('title')</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @yield('styles')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            .print-content { width: 100% !important; padding: 0 !important; margin: 0 !important; }
        }
    </style>
</head>
<!-- Gunakan layout dasar flex-row agar sidebar dan area konten utama berdampingan secara benar -->
<body class="bg-[#f4f6fa] text-slate-700 min-h-screen flex selection:bg-blue-500/10 selection:text-blue-600">

   <!-- 🌐 SIDEBAR NAVIGASI DENGAN WARNA SOFT ICE BLUE (MAKIN HIDUP & PRO!) -->
   <!-- 🌐 SIDEBAR NAVIGASI DENGAN DEEP SLATE LIGHT BLUE (LEBIH TEGAS & KELIHATAN!) -->
   <aside class="no-print w-64 bg-[#e2e8f0] border-r border-slate-300/70 flex flex-col justify-between p-6 shrink-0 hidden md:flex">
        <div>
            <!-- Branding Premium -->
            <div class="flex items-center gap-3 mb-10 px-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-blue-600 to-cyan-500 flex items-center justify-center shadow-md shadow-blue-500/20">
                    <span class="text-xs font-black text-white">LG</span>
                </div>
                <div>
                    <h1 class="text-sm font-black tracking-widest text-slate-900 uppercase">LogiGuard</h1>
                    <span class="text-[9px] text-blue-600 font-bold uppercase tracking-widest block -mt-0.5">Core Engine</span>
                </div>
            </div>

            <!-- Navigasi Menu Dinamis -->
            <div class="space-y-1">
                <!-- Teks kategori digelapkan dikit agar kontras dengan background baru -->
                <span class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-3">Main Menu</span>
                
                <!-- Halaman 1: Analisis Tren Kurs -->
                <a href="/currency-dashboard" class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide transition-all duration-200 
                    {{ Request::is('currency-dashboard') ? 'bg-white border border-slate-300 text-blue-600 shadow-md font-bold' : 'hover:bg-white/40 text-slate-600 hover:text-blue-600' }}">
                    <span class="tracking-wide">Analisis Tren Kurs</span>
                </a>

                <!-- Halaman 2: Kalkulator & Risiko -->
                <a href="/shipping-calculator" class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide transition-all duration-200 
                    {{ Request::is('shipping-calculator') ? 'bg-white border border-slate-300 text-blue-600 shadow-md font-bold' : 'hover:bg-white/40 text-slate-600 hover:text-blue-600' }}">
                    <span class="tracking-wide">Kalkulator & Risiko</span>
                </a>
            </div>
        </div>

        <!-- Profil Developer Minimalis -->
        <div class="bg-white border border-slate-300/60 rounded-xl p-3 flex items-center gap-3 shadow-sm">
            <div class="w-8 h-8 rounded-full bg-blue-50 border border-blue-100 flex items-center justify-center font-bold text-xs text-blue-600">
                HA
            </div>
            <div class="overflow-hidden">
                <p class="text-xs font-bold text-slate-900 truncate">Hurul Aini</p>
                <p class="text-[9px] text-slate-500 font-mono tracking-wider truncate">NIM 240180109</p>
            </div>
        </div>
    </aside>

    <!-- 💻 WORKSPACE AREA KANAN ULAH FLEX-COL AGAR HEADER TETAP DIATAS -->
    <div class="flex-1 flex flex-col min-w-0">
        
        <!-- 🟦 HEADER HORIZONTAL (FIXED ATAS) -->
        <header class="no-print bg-gradient-to-r from-blue-900 via-blue-800 to-blue-700 px-6 py-4 flex justify-between items-center shrink-0 shadow-md">
            <div>
                <!-- Ganti bagian judul di header menjadi dynamic yield -->
        <h2 class="text-base font-bold text-white tracking-tight flex items-center gap-2">
             <span>@yield('title', 'Dashboard Core')</span>
             <span class="text-[10px] font-semibold text-white bg-white/20 px-2 py-0.5 rounded-full border border-white/20 font-mono">v3.5</span>
        </h2>
                <p class="text-xs text-blue-100/80 mt-0.5">Hitung biaya pengiriman internasional secara akurat dengan konversi kurs rupiah terbaru.</p>
            </div>
            
            <div class="flex items-center gap-2 text-xs bg-white/10 border border-white/20 px-3 py-1.5 rounded-xl text-white">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="font-medium font-mono text-[10px]">System: Operational</span>
            </div>
        </header>

        <!-- 📦 AREA KONTEN UTAMA (Kalkulator Halaman Masuk Sini) -->
        <main class="print-content flex-1 overflow-y-auto p-6 md:p-8 bg-[#f4f6fa]">
            <div class="w-full max-w-4xl mx-auto">
                @yield('content')
            </div>
        </main>

    </div>

    @yield('scripts')
</body>
</html>