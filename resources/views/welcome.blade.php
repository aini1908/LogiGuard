<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogiGuard - Dashboard Pemantauan Risiko Logistik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            height: 600px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            border: 1px solid #e3e6f0;
        }
        /* Style Pin Bulat Modern */
        .custom-div-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            position: absolute;
            left: 50%;
            top: 50%;
            margin: -12px 0 0 -12px;
            border: 3px solid #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.35);
            transition: all 0.3s ease;
        }
        .marker-pin-default { background-color: #0d6efd; }
        .marker-pin-low { background-color: #198754; }
        .marker-pin-medium { background-color: #ffc107; }
        .marker-pin-high { background-color: #dc3545; }
        
        .card {
            border-radius: 14px;
        }
    </style>
</head>
<body class="bg-light" style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="#">
                <span class="me-2">🛡️</span> LogiGuard
            </a>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow-sm border-0 bg-white">
                    <div class="card-body p-4">
                        <h2 class="fw-bold text-dark mb-1">Peta Pemantauan Risiko Geopolitik & Rantai Pasok</h2>
                        <p class="text-muted mb-0">Analisis sentimen berbasis AI leksikon untuk mendeteksi gangguan pasokan global secara real-time.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7 mb-4">
                <div id="map"></div>
            </div>
            
            <div class="col-lg-5">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-dark text-white fw-bold py-3">🧠 Input Analisis Berita Sentinel AI</div>
                    <div class="card-body p-4">
                        <form id="riskForm">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary">Pilih Negara Target</label>
                                <select class="form-select form-select-lg" id="countrySelect" required>
                                    <option value="">-- Pilih Negara --</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary">Teks Berita Geopolitik / Logistik (B. Inggris)</label>
                                <textarea class="form-control" id="newsText" rows="4" placeholder="Contoh: Severe war inflation and port delay blockades disrupt global trade supply chain..." required></textarea>
                            </div>

                            <div class="row mb-4">
                                <div class="col">
                                    <label class="form-label small fw-bold text-muted">Risiko Cuaca (0-5)</label>
                                    <input type="number" class="form-control form-control-lg" id="weatherScore" min="0" max="5" step="0.1" value="1.0" required>
                                </div>
                                <div class="col">
                                    <label class="form-label small fw-bold text-muted">Inflasi (0-5)</label>
                                    <input type="number" class="form-control form-control-lg" id="inflationScore" min="0" max="5" step="0.1" value="1.0" required>
                                </div>
                                <div class="col">
                                    <label class="form-label small fw-bold text-muted">Kurs (0-5)</label>
                                    <input type="number" class="form-control form-control-lg" id="exchangeScore" min="0" max="5" step="0.1" value="1.0" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">Proses Kalkulasi Risiko AI</button>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm border-0 bg-white d-none mb-4" id="resultCard">
                    <div class="card-header bg-success text-white fw-bold py-3">📊 Hasil Perhitungan AI Sentimen</div>
                    <div class="card-body p-4">
                        <div class="row text-center mb-3">
                            <div class="col">
                                <span class="text-muted small d-block mb-1">Kamus Kata (Pos/Neg)</span>
                                <h4 class="fw-bold mb-0"><span class="text-success" id="resPos">0</span> / <span class="text-danger" id="resNeg">0</span></h4>
                            </div>
                            <div class="col">
                                <span class="text-muted small d-block mb-1">Skor Sentimen Berita</span>
                                <h4 class="fw-bold text-dark mb-0" id="resSentScore">0.0</h4>
                            </div>
                        </div>
                        <hr class="text-muted">
                        <div class="d-flex justify-content-between align-items-center pt-2">
                            <div>
                                <span class="text-muted small d-block">TOTAL RISK SCORE</span>
                                <h2 class="fw-bold text-primary mb-0" id="resTotalScore">0.0</h2>
                            </div>
                            <div class="text-end">
                                <span class="text-muted small d-block mb-1">LEVEL RISIKO</span>
                                <span class="badge fs-5 px-4 py-2" id="resLevel">LOW</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        // Inisialisasi Peta Mundi
        const map = L.map('map', {
            minZoom: 2,
            maxBounds: [[-90, -180], [90, 180]],
            maxBoundsViscosity: 1.0
        }).setView([15, 20], 2);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            noWrap: true,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        let markerLayers = {};

        // Ambil data negara dinamis
        function loadCountries() {
            fetch('/api/countries')
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        const countries = result.data;
                        const selectEl = document.getElementById('countrySelect');
                        
                        countries.forEach(country => {
                            let opt = document.createElement('option');
                            opt.value = country.id;
                            opt.innerText = `${country.name} (${country.country_code})`;
                            selectEl.appendChild(opt);

                            if (country.latitude && country.longitude) {
                                createOrUpdateMarker(country, 'Default');
                            }
                        });
                    }
                });
        }

        // Gambar pin marker modern bulat berwarna
        function createOrUpdateMarker(country, riskLevel) {
            if (markerLayers[country.id]) {
                map.removeLayer(markerLayers[country.id]);
            }

            let colorClass = 'marker-pin-default';
            let levelLabel = 'Belum Dianalisis';
            if (riskLevel === 'Low') { colorClass = 'marker-pin-low'; levelLabel = '🟢 Low (Aman)'; }
            if (riskLevel === 'Medium') { colorClass = 'marker-pin-medium'; levelLabel = '🟡 Medium (Waspada)'; }
            if (riskLevel === 'High') { colorClass = 'marker-pin-high'; levelLabel = '🔴 High (Bahaya/Krisis)'; }

            let icon = L.divIcon({
                className: 'custom-marker-wrapper',
                html: `<div class="custom-div-icon ${colorClass}"></div>`,
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });

            let marker = L.marker([country.latitude, country.longitude], {icon: icon}).addTo(map);
            marker.bindPopup(`
                <div class="p-1">
                    <h6 class="fw-bold mb-1">${country.name}</h6>
                    <p class="mb-0 small">Status: <b>${levelLabel}</b></p>
                </div>
            `);

            markerLayers[country.id] = marker;
        }

        loadCountries();

// Submit Form lewat AJAX POST (Versi Perbaikan Stabil)
document.getElementById('riskForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // 1. Ambil token keamanan CSRF
            const tokenEl = document.querySelector('input[name="_token"]');
            if (!tokenEl) {
                alert('Token CSRF tidak ditemukan! Pastikan @csrf ada di dalam tag <form>');
                return;
            }
            const token = tokenEl.value;

            // 2. Ambil semua komponen nilai input
            const countryId = document.getElementById('countrySelect').value;
            const newsText = document.getElementById('newsText').value;
            const weatherScore = parseFloat(document.getElementById('weatherScore').value) || 0;
            const inflationScore = parseFloat(document.getElementById('inflationScore').value) || 0;
            const exchangeScore = parseFloat(document.getElementById('exchangeScore').value) || 0;

            const data = {
                country_id: countryId,
                news_text: newsText,
                weather_score: weatherScore,
                inflation_score: inflationScore,
                exchange_rate_score: exchangeScore
            };

            // 3. Terbangkan data ke Backend Laravel
            fetch('/api/risk/analyze', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Respon server bermasalah (Status: ' + response.status + ')');
                }
                return response.json();
            })
            .then(result => {
                if (result.status === 'success') {
                    // Munculkan card output hasil kalkulasi AI
                    const resCard = document.getElementById('resultCard');
                    resCard.classList.remove('d-none');
                    
                    document.getElementById('resPos').innerText = result.results.positive_words_found;
                    document.getElementById('resNeg').innerText = result.results.negative_words_found;
                    document.getElementById('resSentScore').innerText = result.results.news_sentiment_score;
                    document.getElementById('resTotalScore').innerText = result.results.total_risk_score;
                    
                    const lvlBadge = document.getElementById('resLevel');
                    lvlBadge.innerText = result.results.risk_level;
                    
                    // Atur warna badge dinamis
                    lvlBadge.className = 'badge fs-5 px-4 py-2 ';
                    if (result.results.risk_level === 'Low') lvlBadge.classList.add('bg-success');
                    if (result.results.risk_level === 'Medium') lvlBadge.classList.add('bg-warning', 'text-dark');
                    if (result.results.risk_level === 'High') lvlBadge.classList.add('bg-danger');

                    // 4. Detik ini juga, ubah warna pin marker di peta secara langsung!
                    const selectEl = document.getElementById('countrySelect');
                    const countryName = selectEl.options[selectEl.selectedIndex].text.split(' (')[0];
                    
                    createOrUpdateMarker({
                        id: countryId,
                        name: countryName,
                        latitude: markerLayers[countryId].getLatLng().lat,
                        longitude: markerLayers[countryId].getLatLng().lng
                    }, result.results.risk_level);
                }
            })
            .catch(error => {
                console.error('Error detail:', error);
                alert('Gagal memproses kalkulasi AI. Pastikan server MAMP MySQL Anda sedang menyala hijau aktif.');
            });
        });
    </script>
</body>
</html>