@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <!-- Header Halaman Control Panel -->
    <div class="border-b border-slate-200 pb-5 mb-6">
        <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Admin Operations Control Panel ⚙️</h1>
        <p class="text-xs text-slate-500 mt-0.5">Pusat kendali internal untuk manajemen otentikasi user, pemeliharaan dataset pelabuhan maritim, dan publikasi intelijen rute.</p>
    </div>

    <!-- 📊 GRID RINGKASAN DATA STATISTIK UTAMA -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center justify-between border-b-4 border-b-blue-600">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Total Entitas User</span>
                <div id="statUserCount" class="text-2xl font-black text-slate-900">3</div>
                <span class="text-[9px] text-slate-500 block mt-0.5">Pengguna aktif terdaftar (Admin & Client)</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-2xs"><i class="fa-solid fa-users"></i></div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center justify-between border-b-4 border-b-amber-500">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Dataset Pelabuhan</span>
                <div class="text-2xl font-black text-slate-900">4.400+</div>
                <span class="text-[9px] text-slate-500 block mt-0.5">Node maritim tersinkronisasi database</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 shadow-2xs"><i class="fa-solid fa-anchor"></i></div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center justify-between border-b-4 border-b-emerald-600">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Artikel Intelijen</span>
                <div id="statArticleCount" class="text-2xl font-black text-slate-900">8</div>
                <span class="text-[9px] text-slate-500 block mt-0.5">Laporan gangguan rute terpublikasi</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-2xs"><i class="fa-solid fa-file-invoice"></i></div>
        </div>
    </div>

    <!-- 🛠️ TWO-COLUMN ENTERPRISE LAYOUT -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
        
        <!-- BAGIAN KIRI: TABEL MANAGEMENT USER -->
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                    <div>
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                            <span>Daftar Pengelolaan Akses Akun Sistem</span>
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Kelola hak otentikasi level akses Administrator maupun User/Client platform secara terpusat.</p>
                    </div>
                    <button id="btnTambahUser" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-[10px] px-3 py-1.5 rounded-lg transition-all shadow-2xs cursor-pointer">
                        <i class="fa-solid fa-user-plus me-1"></i> Tambah User
                    </button>
                </div>

                <div class="overflow-hidden border border-slate-100 rounded-xl">
                    <table class="min-w-full divide-y divide-slate-100 text-left">
                        <thead class="bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Nama Identitas</th>
                                <th class="px-4 py-3">Email Akun</th>
                                <th class="px-4 py-3">Hak Akses Role</th>
                                <th class="px-4 py-3 text-right">Manajemen Kontrol</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody" class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                            <!-- Data dirender dinamis -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 📑 BAGIAN KANAN: TAB INTERAKTIF (METRICS VS INTEL PUBLICATOR) -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex flex-col">
            <!-- Navigasi Tab Internal -->
            <div class="flex border-b border-slate-100 mb-4 text-[11px] font-bold uppercase tracking-wider">
                <button onclick="switchTab('metrics')" id="tabBtnMetrics" class="flex-1 pb-2.5 border-b-2 border-blue-600 text-blue-600 text-center cursor-pointer">
                    <i class="fa-solid fa-chart-pie me-1"></i> System Metrics
                </button>
                <button onclick="switchTab('intel')" id="tabBtnIntel" class="flex-1 pb-2.5 border-b-2 border-transparent text-slate-400 hover:text-slate-600 text-center cursor-pointer">
                    <i class="fa-solid fa-bullhorn me-1"></i> Publish Intel rute
                </button>
            </div>

            <!-- ISI TAB 1: CORE METRICS & SECURITY LOGS -->
            <div id="tabContentMetrics" class="space-y-5">
                <div>
                    <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Engine Core Metrics</h4>
                    <div class="space-y-2">
                        <div>
                            <div class="flex justify-between text-[10px] font-semibold text-slate-500 mb-0.5">
                                <span>DATABASE BUFFER MEMORY</span>
                                <span class="font-mono text-blue-600 font-bold">42%</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1">
                                <div class="bg-blue-600 h-1 rounded-full" style="width: 42%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-[10px] font-semibold text-slate-500 mb-0.5">
                                <span>API OVER WORLD BANK REQUESTS</span>
                                <span class="font-mono text-emerald-600 font-bold">Active (12ms)</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1">
                                <div class="bg-emerald-500 h-1 rounded-full" style="width: 85%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="border-slate-100">
                <div>
                    <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Security Audit Logs</h4>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-2.5 text-[10px]">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 mt-1 shrink-0"></span>
                            <p class="text-slate-600 leading-tight">Session <span class="font-bold text-slate-900">admin@logiguard.id</span> granted successfully. <span class="text-slate-400 font-mono block text-[9px] mt-0.5">Just Now</span></p>
                        </li>
                        <li class="flex items-start gap-2.5 text-[10px]">
                            <span class="w-2 h-2 rounded-full bg-blue-500 mt-1 shrink-0"></span>
                            <p class="text-slate-600 leading-tight">New network entity token synchronization completed. <span class="text-slate-400 font-mono block text-[9px] mt-0.5">3m ago</span></p>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- ✨ ISI TAB 2: MANAJEMEN PUBLIKASI MAKLUMAT / INTELIJEN INTERNAL -->
            <div id="tabContentIntel" class="hidden flex-1 flex flex-col justify-between">
                <form onsubmit="publishIntelReport(event)" class="space-y-3">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Judul Intelijen Internal / Maklumat Jalur:</label>
                        <input type="text" id="intelTitle" required placeholder="Contoh: Penyesuaian Rute Internal Armada LG-02" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Pilih Status Evaluasi Risiko Jalur:</label>
                        <select id="intelStatus" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:border-blue-500 cursor-pointer">
                            <option value="Tinggi">CRITICAL (High Risk - Hambatan Ekstrem)</option>
                            <option value="Sedang">NETRAL (Medium Risk - Hambatan Operasional)</option>
                            <option value="Rendah">AMAN (Low Risk - Jalur Lancar)</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold py-2.5 px-4 rounded-xl shadow-xs uppercase transition-colors cursor-pointer">
                        Broadcast Maklumat ke Feed User 📢
                    </button>
                </form>
                <div class="mt-4 bg-emerald-50 border border-emerald-200/60 p-3 rounded-xl text-[10px] text-emerald-800 leading-relaxed">
                    <i class="fa-solid fa-circle-info me-1"></i> <strong>Informasi Spek:</strong> Fitur ini digunakan Admin untuk menerbitkan analisis risiko internal perusahaan secara mandiri, melengkapi feed berita global yang sudah ditarik otomatis via API.
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Data dasar bawaan sistem
        let baseUsers = [
            { id: 1, name: "Hurul Aini", email: "admin@logiguard.id", role: "Administrator", isStatic: true, isSuspended: false },
            { id: 2, name: "Yudhika Widianto", email: "yudhika.nasution@client.com", role: "Client Logistik", isStatic: false, isSuspended: false }
        ];

        let currentArticleCount = 8;

        function renderTable() {
            const tbody = document.getElementById('userTableBody');
            if(!tbody) return;
            tbody.innerHTML = "";

            let registeredUsers = JSON.parse(localStorage.getItem('registeredUsers') || '[]');
            let fullUsersList = [...baseUsers];

            registeredUsers.forEach((u, i) => {
                if (!fullUsersList.some(user => user.email.toLowerCase() === u.email.toLowerCase())) {
                    fullUsersList.push({
                        id: 100 + i,
                        name: u.name,
                        email: u.email,
                        role: "Client Logistik",
                        isStatic: false,
                        isSuspended: false
                    });
                }
            });

            fullUsersList.forEach(user => {
                const tr = document.createElement('tr');
                tr.className = `transition-colors ${user.isSuspended ? 'bg-rose-50/40 text-slate-400 line-through' : 'hover:bg-slate-50/60'}`;

                const initials = user.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                const avatarBg = user.role === 'Administrator' ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-600';
                
                const roleBadge = user.role === 'Administrator' 
                    ? `<span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-bold text-[9px] border border-blue-200 uppercase">Administrator</span>`
                    : `<span class="px-2 py-0.5 rounded bg-slate-50 text-slate-600 font-bold text-[9px] border border-slate-200 uppercase">${user.role}</span>`;

                let controlAction = `<span class="text-slate-300 italic text-[10px]">Sistem Utama</span>`;
                if (!user.isStatic) {
                    controlAction = `
                        <button onclick="editUser('${user.email}')" class="text-blue-600 hover:underline font-bold text-[10px] me-2 cursor-pointer no-underline">Edit</button>
                        <button onclick="toggleSuspendRow(this)" class="text-rose-600 hover:underline font-bold text-[10px] cursor-pointer no-underline">Suspend</button>
                    `;
                }

                tr.innerHTML = `
                    <td class="px-4 py-3 font-bold text-slate-900 flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full ${avatarBg} font-black flex items-center justify-center text-[10px]">${initials}</div>
                        <span>${user.name}</span>
                    </td>
                    <td class="px-4 py-3 font-mono text-slate-500">${user.email}</td>
                    <td class="px-4 py-3">${roleBadge}</td>
                    <td class="px-4 py-3 text-right">${controlAction}</td>
                `;
                tbody.appendChild(tr);
            });

            const statUserCount = document.getElementById('statUserCount');
            if(statUserCount) statUserCount.innerText = fullUsersList.length;
        }

        // Kontrol Perpindahan Tab Kanan Atas
        function switchTab(tab) {
            const btnMetrics = document.getElementById('tabBtnMetrics');
            const btnIntel = document.getElementById('tabBtnIntel');
            const contentMetrics = document.getElementById('tabContentMetrics');
            const contentIntel = document.getElementById('tabContentIntel');

            if(tab === 'metrics') {
                btnMetrics.className = "flex-1 pb-2.5 border-b-2 border-blue-600 text-blue-600 text-center cursor-pointer";
                btnIntel.className = "flex-1 pb-2.5 border-b-2 border-transparent text-slate-400 hover:text-slate-600 text-center cursor-pointer";
                contentMetrics.classList.remove('hidden');
                contentIntel.classList.add('hidden');
            } else {
                btnIntel.className = "flex-1 pb-2.5 border-b-2 border-emerald-600 text-emerald-600 text-center cursor-pointer";
                btnMetrics.className = "flex-1 pb-2.5 border-b-2 border-transparent text-slate-400 hover:text-slate-600 text-center cursor-pointer";
                contentMetrics.classList.add('hidden');
                contentIntel.classList.remove('hidden');
            }
        }

        // Kontrol Broadcast Artikel Laporan Krisis Maritim Baru
        function publishIntelReport(e) {
            e.preventDefault();
            const title = document.getElementById('intelTitle').value;
            const status = document.getElementById('intelStatus').value;

            // Naikkan jumlah counter statistik secara dinamis di layar atas
            currentArticleCount++;
            document.getElementById('statArticleCount').innerText = currentArticleCount;

            alert(`BERHASIL DISIARKAN! Isu "${title}" dengan status tingkat risiko ${status} telah terpublikasi secara global ke Feed Dashboard Pengguna.`);
            document.getElementById('intelTitle').value = ""; // Reset form input
        }

        document.addEventListener("DOMContentLoaded", renderTable);

        document.getElementById('btnTambahUser').addEventListener('click', function() {
            const newName = prompt("Masukkan nama lengkap user baru:");
            if (!newName) return;
            
            const newEmail = prompt(`Masukkan alamat email untuk ${newName}:`);
            if (!newEmail) return;

            let registeredUsers = JSON.parse(localStorage.getItem('registeredUsers') || '[]');
            registeredUsers.push({ name: newName, email: newEmail });
            localStorage.setItem('registeredUsers', JSON.stringify(registeredUsers));

            renderTable();
            alert(`Sukses menambahkan entitas user baru: ${newName}.`);
        });

        function editUser(currentEmail) {
            const newEmail = prompt(`Masukkan email baru untuk menggantikan ${currentEmail}:`, currentEmail);
            if (!newEmail) return;

            let registeredUsers = JSON.parse(localStorage.getItem('registeredUsers') || '[]');
            let found = false;
            registeredUsers.forEach(u => {
                if(u.email.toLowerCase() === currentEmail.toLowerCase()) {
                    u.email = newEmail;
                    found = true;
                }
            });

            if(found) {
                localStorage.setItem('registeredUsers', JSON.stringify(registeredUsers));
            } else {
                alert("Catatan: User default sistem tidak dapat diubah emailnya.");
                return;
            }

            renderTable();
            alert(`Perubahan email berhasil disimpan!`);
        }

        function toggleSuspendRow(btn) {
            const tr = btn.closest('tr');
            if (confirm("Apakah Anda yakin ingin mengubah status pemblokiran jaringan akun ini?")) {
                tr.classList.toggle('line-through');
                tr.classList.toggle('bg-rose-50/40');
                tr.classList.toggle('text-slate-400');
            }
        }
    </script>
@endsection