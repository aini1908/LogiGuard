@extends('layouts.app')

@section('title', 'Currency Trend Analysis')

@section('styles')
    <!-- Taruh script Chart.js di sini agar hanya dimuat di halaman ini -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* CSS tambahan khusus halaman grafik jika ada, taruh di sini */
    </style>
@endsection

@section('content')
    <!-- Main White Container (Seragam dengan halaman kalkulator) -->
    <div class="w-full bg-white border border-slate-200 rounded-2xl p-6 md:p-8 shadow-sm transition-all duration-300">
        
        <!-- Bagian Grafik Pop-Up -->
        <div class="mb-6">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider font-mono mb-4">Mata Uang Global vs Rupiah</h3>
            
            <!-- Tempatkan Canvas Chart.js milikmu di sini -->
            <div class="relative w-full h-[350px] bg-slate-50 p-4 rounded-xl border border-slate-100">
                <canvas id="currencyChart"></canvas>
            </div>
        </div>

        <!-- Jika ada ringkasan data grid kurs di bawah grafik, buat jadi pop-up seragam -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div class="bg-white border border-slate-200 p-5 rounded-xl shadow-sm">
                <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider block">Kurs Tertinggi</span>
                <span class="text-base font-extrabold text-slate-900 mt-1 block">Rp17.367</span>
            </div>
            <div class="bg-white border border-slate-200 p-5 rounded-xl shadow-sm">
                <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider block">Kurs Terendah</span>
                <span class="text-base font-extrabold text-slate-900 mt-1 block">Rp16.850</span>
            </div>
            <div class="bg-white border border-slate-200 p-5 rounded-xl shadow-sm">
                <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider block">Status Volatilitas</span>
                <span class="text-base font-extrabold text-blue-600 mt-1 block">Stabil</span>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        // Taruh seluruh logika inisialisasi Chart.js kamu di sini
        const ctx = document.getElementById('currencyChart').getContext('2d');
        
        // Contoh data dummy (ganti dengan logika pembacaan data database/API milikmu)
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'],
                datasets: [{
                    label: 'USD ke IDR',
                    data: [17100, 17250, 17180, 17310, 17367],
                    borderColor: '#2563eb', // Warna biru matching header
                    backgroundColor: 'rgba(37, 99, 235, 0.05)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
@endsection