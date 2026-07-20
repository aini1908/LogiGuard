<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogiGuard Core - @yield('title')</title>
    <!-- Tailwind CSS v4 -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <!-- FontAwesome untuk Icon Pendukung -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @yield('styles')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght=400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            .print-content { width: 100% !important; padding: 0 !important; margin: 0 !important; }
        }
    </style>
</head>
<body class="bg-[#f4f6fa] text-slate-700 min-h-screen flex flex-col selection:bg-blue-500/10 selection:text-blue-600">

    <!-- 🟦 TOP NAVBAR HORIZONTAL -->
    <header class="no-print bg-gradient-to-r from-blue-900 via-blue-800 to-blue-700 shadow-md shrink-0 text-white">
        <!-- Baris Atas: Branding & Profile -->
        <div class="max-w-7xl mx-auto px-6 py-3 flex justify-between items-center border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-blue-600 to-cyan-500 flex items-center justify-center shadow-md shadow-blue-500/20">
                    <span class="text-xs font-black text-white">LG</span>
                </div>
                <div>
                    <h1 class="text-sm font-black tracking-widest uppercase leading-none">LogiGuard</h1>
                    <span class="text-[9px] text-cyan-400 font-bold uppercase tracking-widest block mt-0.5">Core Engine <span class="font-mono text-[8px] bg-white/20 px-1 rounded">v3.5</span></span>
                </div>
            </div>
            
            <!-- Identitas Developer & Status / Login Button -->
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 text-[11px] bg-white/10 border border-white/20 px-2.5 py-1 rounded-lg">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="font-medium font-mono">System: Operational</span>
                </div>
                
                <!-- Area Kanan Atas Dinamis (Nama User atau Masuk/Register) -->
                <div id="authHeaderArea"></div>
            </div>
        </div>

       <!-- Baris Bawah: Menu Tab Navigasi Horizontal Protektif -->
       <div class="max-w-7xl mx-auto px-6 py-1.5 flex gap-2 overflow-x-auto whitespace-nowrap scrollbar-none items-center w-full">
            
            <!-- 📱 GRUP NAVIGASI KHUSUS OPERASIONAL USER / CLIENT -->
            <div id="navGrupUser" class="flex gap-2">
                <a href="/country-dashboard" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold tracking-wide transition-all duration-150 {{ Request::is('country-dashboard') || Request::is('/') ? 'bg-white text-blue-950 shadow-xs font-bold' : 'hover:bg-white/10 text-blue-100 hover:text-white' }}">
                    <i class="fa-solid fa-globe"></i> Global Dashboard
                </a>

                <!-- ✨ MENU TAB REAL-TIME NEWS (TERPUSAT DI ATAS SEBELAH GLOBAL) -->
                <button onclick="switchViewToNews()" id="tabBtnNewsGlobal" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold tracking-wide transition-all duration-150 hover:bg-white/10 text-blue-100 hover:text-white cursor-pointer border-none bg-transparent">
                    <i class="fa-solid fa-satellite-dish"></i> Berita Logistik
                </button>

                <a href="/currency-dashboard" onclick="return checkAuth(event, '/currency-dashboard')" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold tracking-wide transition-all duration-150 {{ Request::is('currency-dashboard') ? 'bg-white text-blue-950 shadow-xs font-bold' : 'hover:bg-white/10 text-blue-100 hover:text-white' }}">
                    <i class="fa-solid fa-chart-line"></i> Tren Kurs & Grafik
                </a>

                <a href="/port-dashboard" onclick="return checkAuth(event, '/port-dashboard')" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold tracking-wide transition-all duration-150 {{ Request::is('port-dashboard') ? 'bg-white text-blue-950 shadow-xs font-bold' : 'hover:bg-white/10 text-blue-100 hover:text-white' }}">
                    <i class="fa-solid fa-anchor"></i> Lokasi Pelabuhan
                </a>

                <a href="/comparison-engine" onclick="return checkAuth(event, '/comparison-engine')" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold tracking-wide transition-all duration-150 {{ Request::is('comparison-engine') ? 'bg-white text-blue-950 shadow-xs font-bold' : 'hover:bg-white/10 text-blue-100 hover:text-white' }}">
                    <i class="fa-solid fa-scale-balanced"></i> Komparasi Negara
                </a>

                <a href="/shipping-calculator" onclick="return checkAuth(event, '/shipping-calculator')" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold tracking-wide transition-all duration-150 {{ Request::is('shipping-calculator') ? 'bg-white text-blue-950 shadow-xs font-bold' : 'hover:bg-white/10 text-blue-100 hover:text-white' }}">
                    <i class="fa-solid fa-calculator"></i> Kalkulator Biaya
                </a>
            </div>

            <!-- ⚙️ GRUP NAVIGASI KHUSUS CONTROL PANEL ADMINISTRATOR -->
            <div id="navGrupAdmin" class="flex gap-2">
                <a id="adminMenuBtn" href="/admin-panel" onclick="return checkAuth(event, '/admin-panel')" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold tracking-wide transition-all duration-150 {{ Request::is('admin-panel') ? 'bg-white text-blue-950 shadow-xs font-bold' : 'hover:bg-white/10 text-blue-100 hover:text-white' }}">
                    <i class="fa-solid fa-user-gear"></i> Control Panel Admin
                </a>
            </div>

            <!-- Tombol Keluar Global -->
            <button id="logoutBtn" onclick="handleLogout()" class="hidden items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold tracking-wide transition-all duration-150 text-rose-300 hover:bg-rose-500/20 hover:text-white ms-auto cursor-pointer">
                <i class="fa-solid fa-right-from-bracket"></i> Keluar Sistem
            </button>
       </div>
    </header>

    <!-- 📦 AREA KONTEN UTAMA -->
    <main id="mainContentContainer" class="print-content flex-1 overflow-y-auto p-6 md:p-8">
        <div class="w-full max-w-7xl mx-auto">
            @yield('content')
        </div>
    </main>

    <!-- 🔐 MODAL DIALOG LOGIN & REGISTER GATEKEEPER -->
    <div id="loginModal" class="hidden fixed inset-0 bg-slate-900/80 backdrop-blur-md flex items-center justify-center p-4 z-50">
        <div class="max-w-md w-full bg-white border border-slate-200 rounded-3xl p-7 shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-600 to-cyan-500"></div>

            <!-- 🟦 BAGIAN FORM LOGIN -->
            <div id="modalLoginForm">
                <div class="text-center mb-6">
                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-sm font-bold mx-auto mb-2"><i class="fa-solid fa-lock"></i></div>
                    <h3 class="text-base font-extrabold text-slate-900">LogiGuard Gateway Terkunci 🔐</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Otentikasi kredensial jaringan Anda untuk membuka akses penuh platform.</p>
                </div>
                <form onsubmit="executeMockLogin(event)" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Email Address:</label>
                        <input type="email" id="authUsername" required placeholder="name@company.com" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Password:</label>
                        <input type="password" id="authPassword" required placeholder="••••••••" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Hak Akses Role:</label>
                        <select id="authRole" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-xs font-semibold text-slate-700 focus:outline-none focus:border-blue-500 cursor-pointer">
                            <option value="user">CLIENT LOGISTIK</option>
                            <option value="admin">ADMINISTRATOR SYSTEM</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white text-[11px] font-bold py-2.5 px-4 rounded-xl shadow-xs transition-all uppercase cursor-pointer">
                        Unlock Core Engine ⚡
                    </button>
                </form>
                <div class="mt-4 text-center text-[11px] text-slate-500">
                    Belum punya akun? <button onclick="toggleAuthMode('register')" class="text-blue-600 font-bold hover:underline cursor-pointer">Daftar Pendaftaran Baru</button>
                </div>
            </div>

            <!-- 📝 BAGIAN FORM REGISTER -->
            <div id="modalRegisterForm" class="hidden">
                <div class="text-center mb-5">
                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-sm font-bold mx-auto mb-2"><i class="fa-solid fa-user-plus"></i></div>
                    <h3 class="text-base font-extrabold text-slate-900">Registrasi Akun Baru 📝</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Buat entitas pengguna logistik baru dalam LogiGuard Core Network.</p>
                </div>
                <form onsubmit="executeMockRegister(event)" class="space-y-3">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-0.5">Nama Lengkap:</label>
                        <input type="text" id="regName" required placeholder="Contoh: Ahmad Rizki" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-1.5 text-xs font-semibold focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-0.5">Alamat Email:</label>
                        <input type="email" id="regEmail" required placeholder="name@company.com" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-1.5 text-xs font-semibold focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-0.5">Perusahaan / Institusi:</label>
                        <input type="text" id="regCompany" placeholder="Contoh: Universitas Malikussaleh" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-1.5 text-xs font-semibold focus:outline-none focus:border-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-0.5">Password:</label>
                            <input type="password" id="regPassword" required placeholder="••••••••" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-1.5 text-xs font-semibold focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-0.5">Konfirmasi Password:</label>
                            <input type="password" id="regPasswordConfirm" required placeholder="••••••••" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-1.5 text-xs font-semibold focus:outline-none focus:border-blue-500">
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold py-2.5 px-4 rounded-xl shadow-xs transition-all uppercase cursor-pointer mt-2">
                        Register & Login 🚀
                    </button>
                </form>
                <div class="mt-4 text-center text-[11px] text-slate-500">
                    Sudah punya akun? <button onclick="toggleAuthMode('login')" class="text-blue-600 font-bold hover:underline cursor-pointer">Masuk Gateway</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 🚀 JAVASCRIPT PROTECTIONS ENGINE -->
    <script>
        let targetUrlAfterLogin = null;

        document.addEventListener("DOMContentLoaded", function() {
            refreshAuthUI();
        });

        function refreshAuthUI() {
            const isLoggedIn = sessionStorage.getItem('isLoggedIn') === 'true';
            const role = sessionStorage.getItem('userRole');
            const name = sessionStorage.getItem('userName'); 
            
            const authHeaderArea = document.getElementById('authHeaderArea');
            const logoutBtn = document.getElementById('logoutBtn');
            const navUser = document.getElementById('navGrupUser');
            const navAdmin = document.getElementById('navGrupAdmin');
            const mainContent = document.getElementById('mainContentContainer');

            if (isLoggedIn && name) {
                if(mainContent) mainContent.style.display = 'block'; 
                
                const initials = name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                authHeaderArea.innerHTML = `
                    <div class="bg-white/10 border border-white/20 rounded-xl px-3 py-1 flex items-center gap-2 text-xs">
                        <div class="w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center font-bold text-[10px] text-white">${initials}</div>
                        <span class="font-semibold hidden sm:inline">${name}</span>
                    </div>
                `;
                if(logoutBtn) logoutBtn.classList.remove('hidden');
                if(logoutBtn) logoutBtn.classList.add('flex');

                // 🚪 PEMISAHAN TOTAL MENU DI LEVEL NAVIGASI NAVBAR
                if (role === 'admin') {
                    if(navUser) navUser.style.setProperty('display', 'none', 'important'); 
                    if(navAdmin) navAdmin.style.display = 'flex';
                    
                    if (window.location.pathname === '/' || window.location.pathname === '/country-dashboard') {
                        window.location.href = '/admin-panel';
                    }
                } else {
                    if(navUser) navUser.style.display = 'flex';
                    if(navAdmin) navAdmin.style.setProperty('display', 'none', 'important'); 
                    
                    if (window.location.pathname === '/admin-panel') {
                        window.location.href = '/country-dashboard';
                    }
                }
                
                document.getElementById('loginModal').classList.add('hidden');
            } else {
                if(mainContent) mainContent.style.display = 'none'; 
                
                authHeaderArea.innerHTML = `
                    <button onclick="openLoginModal(null)" class="bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs px-4 py-1.5 rounded-xl border border-blue-400/30 transition-colors shadow-xs cursor-pointer flex items-center gap-1">
                        <i class="fa-solid fa-right-to-bracket text-[10px]"></i> Masuk / Register
                    </button>
                `;
                if(logoutBtn) logoutBtn.classList.remove('flex');
                if(logoutBtn) logoutBtn.classList.add('hidden');
                if(navUser) navUser.style.display = 'none';
                if(navAdmin) navAdmin.style.display = 'none';

                document.getElementById('loginModal').classList.remove('hidden');
            }
        }

        function checkAuth(e, url) {
            const isLoggedIn = sessionStorage.getItem('isLoggedIn') === 'true';
            if (!isLoggedIn) {
                e.preventDefault();
                openLoginModal(url);
                return false;
            }
            return true;
        }

        function openLoginModal(redirectUrl) {
            targetUrlAfterLogin = redirectUrl;
            document.getElementById('loginModal').classList.remove('hidden');
            toggleAuthMode('login');
        }

        document.addEventListener("keydown", function(e) {
            const isLoggedIn = sessionStorage.getItem('isLoggedIn') === 'true';
            if (!isLoggedIn && e.key === "Escape") {
                e.preventDefault();
            }
        });

        function toggleAuthMode(mode) {
            if(mode === 'login') {
                document.getElementById('modalLoginForm').classList.remove('hidden');
                document.getElementById('modalRegisterForm').classList.add('hidden');
            } else {
                document.getElementById('modalLoginForm').classList.add('hidden');
                document.getElementById('modalRegisterForm').classList.remove('hidden');
            }
        }

        function executeMockLogin(e) {
            e.preventDefault();
            const role = document.getElementById('authRole').value;
            const inputEmail = document.getElementById('authUsername').value.trim().toLowerCase();
            
            if (role === 'admin' && inputEmail !== 'admin@logiguard.id') {
                alert("Akses Ditolak! Akun ini tidak terdaftar sebagai Administrator Utama LogiGuard Core.");
                return false;
            }

            sessionStorage.setItem('isLoggedIn', 'true');
            sessionStorage.setItem('userRole', role);
            
            if(role === 'admin') {
                sessionStorage.setItem('userName', 'Hurul Aini');
            } else {
                const registeredUsers = JSON.parse(localStorage.getItem('registeredUsers') || '[]');
                const foundUser = registeredUsers.find(u => u.email.toLowerCase() === inputEmail);
                sessionStorage.setItem('userName', foundUser ? foundUser.name : inputEmail.split('@')[0]);
            }

            refreshAuthUI();

            if (targetUrlAfterLogin) {
                window.location.href = targetUrlAfterLogin;
            } else {
                window.location.href = role === 'admin' ? '/admin-panel' : '/country-dashboard';
            }
        }

        function executeMockRegister(e) {
            e.preventDefault();
            const name = document.getElementById('regName').value;
            const email = document.getElementById('regEmail').value.trim();
            const password = document.getElementById('regPassword').value;
            const confirmPassword = document.getElementById('regPasswordConfirm').value;

            if (password !== confirmPassword) {
                alert("Gagal registrasi! Kolom password dan konfirmasi password tidak cocok.");
                return false;
            }

            let registeredUsers = JSON.parse(localStorage.getItem('registeredUsers') || '[]');
            registeredUsers.push({ name: name, email: email });
            localStorage.setItem('registeredUsers', JSON.stringify(registeredUsers));

            sessionStorage.setItem('isLoggedIn', 'true');
            sessionStorage.setItem('userRole', 'user');
            sessionStorage.setItem('userName', name);

            refreshAuthUI();
            alert(`Pendaftaran Akun ${name} Berhasil! Membuka gerbang dasbor.`);
            window.location.href = '/country-dashboard';
        }

        function handleLogout() {
            sessionStorage.clear();
            refreshAuthUI();
            alert('Sukses keluar dari LogiGuard Core.');
            window.location.href = '/country-dashboard'; 
        }
    </script>
    @yield('scripts')
</body>
</html>