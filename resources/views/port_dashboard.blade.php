@extends('layouts.app')

@section('title', 'Port Location Dashboard')

@section('styles')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #portMap { height: 500px; width: 100%; }
    </style>
@endsection

@section('content')
    <!-- Header Halaman -->
    <div class="border-b border-slate-200 pb-5 mb-6">
        <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Port Location Dashboard ⚓</h1>
        <p class="text-xs text-slate-500 mt-0.5">Visualisasi Geospatial untuk pencarian dan pemetaan infrastruktur pelabuhan maritim global.</p>
    </div>

    <!-- Panel Dropdown Otomatis Dari API -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs mb-6 grid grid-cols-1 gap-2">
        <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Pilih Entitas Pelabuhan (Terintegrasi API):</label>
            <div class="relative">
                <!-- Dropdown ini akan diisi secara otomatis oleh JavaScript dari endpoint API -->
                <select id="portSelect" class="w-full appearance-none bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-700 shadow-xs focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 cursor-pointer pr-10">
                    <option value="">-- Memuat daftar pelabuhan dari database... --</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 border-l border-slate-200">
                    <i class="fa-solid fa-anchor text-[11px]"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Peta Geospatial Pelabuhan -->
    <div class="bg-white border border-slate-200 rounded-2xl p-2 shadow-xs mb-10">
        <div id="portMap" class="rounded-xl z-10"></div>
    </div>
@endsection

@section('scripts')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Inisialisasi peta berpusat ke jalur maritim tengah bumi
        const portMap = L.map('portMap').setView([20, 0], 2);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(portMap);

        const markersMap = {}; // Tempat menyimpan referensi marker berdasarkan ID/Nama

        // 1. Tembak REST API internal /api/ports untuk mengisi dropdown dan memetakan marker sekaligus
        fetch('/api/ports')
            .then(res => res.json())
            .then(ports => {
                const portSelect = document.getElementById('portSelect');
                
                // Bersihkan placeholder loading awal
                portSelect.innerHTML = '<option value="">-- Pilih Rute Node Pelabuhan Destinasi --</option>';

                ports.forEach((port, index) => {
                    if (port.latitude && port.longitude) {
                        const lat = parseFloat(port.latitude);
                        const lng = parseFloat(port.longitude);
                        
                        // A. Buat marker jangkar interaktif di peta Leaflet
                        const marker = L.marker([lat, lng]).addTo(portMap);
                        // Perbaikan struktur Pop-Up agar memiliki tombol integrasi fitur kalkulator biaya
                // PERBAIKAN TOMBOL POP-UP: Warna cerah, kontras tinggi, dan teks sangat terbaca jelas
                // Perbaikan Pop-Up: Hanya satu tombol aksi logis untuk melempar Pelabuhan Asal
                // PERBAIKAN TOTAL: Tombol kontras tinggi, teks hitam tebal (sangat jelas di layar)
                    marker.bindPopup(`
                        <div class="font-sans text-xs p-1" style="min-width: 170px;">
                            <span class="text-[9px] font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded uppercase block mb-1 w-max">Node Pelabuhan</span>
                            <h6 class="font-bold text-slate-900 m-0 text-sm">${port.port_name}</h6>
                            <p class="text-slate-600 m-0 mt-0.5">Negara: <b>${port.country_name} (${port.country_code})</b></p>
                            <p class="text-[10px] text-slate-400 font-mono m-0 mt-0.5 mb-2.5">Coord: ${lat.toFixed(4)}, ${lng.toFixed(4)}</p>
                            
                            <!-- Tombol Aksi Kustom: Kontras Tinggi Cerah & Teks Gelap Mantap -->
                            <div class="border-t border-slate-200 pt-2 mt-1">
                                <a href="/shipping-calculator?origin_port=${encodeURIComponent(port.port_name)}" 
                                target="_self"
                                class="block w-full bg-amber-400 text-slate-950 font-black text-[10px] py-2 rounded-lg shadow-sm hover:bg-amber-300 transition-colors no-underline text-center">
                                    <i class="fa-solid fa-ship me-1 text-slate-950"></i> Kirim Kargo dari Sini
                                </a>
                            </div>
                        </div>
                    `);

                        // Simpan reference marker ke objek map dengan key index agar bisa dipanggil nanti
                        markersMap[index] = marker;

                        // B. Masukkan data ke dalam pilihan elemen Dropdown secara otomatis
                        const option = document.createElement('option');
                        option.value = index;
                        option.textContent = `${port.country_code} - ${port.port_name} (${port.country_name})`;
                        portSelect.appendChild(option);
                    }
                });
            })
            .catch(err => {
                console.error("Gagal memuat API Pelabuhan:", err);
                document.getElementById('portSelect').innerHTML = '<option value="">Gagal memuat data pelabuhan.</option>';
            });

        // 2. LOGIKA KETIKA DROPDOWN DIPILIH (PETA OTOMATIS TERBANG KE PELABUHAN)
        document.getElementById('portSelect').addEventListener('change', function() {
            const selectedIndex = this.value;
            if (selectedIndex === "") return;

            const selectedMarker = markersMap[selectedIndex];
            if (selectedMarker) {
                // Ambil koordinat marker yang dipilih
                const targetLatLng = selectedMarker.getLatLng();
                
                // Terbang (FlyTo) dengan zoom level 10 agar pelabuhan kelihatan jelas dekat pantai
                portMap.flyTo(targetLatLng, 10, {
                    animate: true,
                    duration: 1.5
                });

                // Buka balon pop-up informasi pelabuhan secara otomatis setelah kamera sampai
                selectedMarker.openPopup();
            }
        });
    </script>
@endsection