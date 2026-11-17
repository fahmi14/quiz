<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kuis Interaktif') - Pastel Quiz</title>

    <!-- Google Fonts: Plus Jakarta Sans & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        display: ['"Outfit"', 'sans-serif'],
                    },
                    colors: {
                        pastel: {
                            sage: {
                                50: '#F4F7F5',
                                100: '#E6EFEA',
                                200: '#D0E1D7',
                                400: '#8EB59E',
                                500: '#689A7B',
                                600: '#517E62',
                            },
                            cream: {
                                50: '#FAF8F5',
                                100: '#F5F0E8',
                                200: '#EBDDCF',
                                300: '#E0CBB4',
                            },
                            sky: {
                                50: '#F0F7FB',
                                100: '#E1EFF7',
                                200: '#C7E2F2',
                                500: '#6FAFD6',
                            },
                            blush: {
                                50: '#FDF3F4',
                                100: '#FAE6E9',
                                200: '#F5CDD3',
                                500: '#DF7B8F',
                            },
                            lavender: {
                                50: '#F6F5FB',
                                100: '#EEEAF6',
                                200: '#DDD4EF',
                                500: '#9B8AC4',
                            },
                            amber: {
                                50: '#FEF9EE',
                                100: '#FCF2DC',
                                200: '#F8E3B6',
                                500: '#E0A838',
                            }
                        }
                    },
                    boxShadow: {
                        'soft': '0 8px 30px rgba(82, 107, 95, 0.08)',
                        'soft-lg': '0 16px 40px rgba(82, 107, 95, 0.12)',
                        'card': '0 4px 20px -2px rgba(112, 144, 126, 0.1)',
                        'card-hover': '0 12px 32px -4px rgba(112, 144, 126, 0.18)',
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* Anti-slop background motif: Calm Organic Shapes */
        body {
            background-color: #F8FAF8;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(208, 225, 215, 0.4) 0%, transparent 40%),
                radial-gradient(circle at 90% 15%, rgba(225, 239, 247, 0.45) 0%, transparent 35%),
                radial-gradient(circle at 50% 80%, rgba(245, 205, 211, 0.3) 0%, transparent 45%),
                radial-gradient(circle at 80% 85%, rgba(248, 227, 182, 0.3) 0%, transparent 35%);
            background-attachment: fixed;
        }

        /* Subtle organic wave pattern */
        .pattern-dots {
            background-image: radial-gradient(#8EB59E 0.8px, transparent 0.8px);
            background-size: 24px 24px;
            opacity: 0.25;
        }

        /* Glassmorphism card */
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        /* Custom soft animations */
        @keyframes float-gentle {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-8px) rotate(1.5deg); }
        }
        @keyframes pulse-soft {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.03); opacity: 0.9; }
        }
        @keyframes slide-in-up {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-float {
            animation: float-gentle 6s ease-in-out infinite;
        }
        .animate-slide-up {
            animation: slide-in-up 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* Smooth scroll & selection */
        * {
            scroll-behavior: smooth;
        }
        ::selection {
            background: #D0E1D7;
            color: #2F4858;
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-full flex flex-col text-slate-700 font-sans antialiased selection:bg-pastel-sage-200">

    <!-- Top Floating Navbar -->
    <header class="sticky top-0 z-40 px-4 sm:px-6 lg:px-8 pt-4 pb-2">
        <div class="max-w-7xl mx-auto">
            <div class="glass-card rounded-2xl px-5 py-3 shadow-card flex items-center justify-between transition-all">
                
                <!-- Logo & Brand -->
                <a href="{{ route('quizzes.index') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-pastel-sage-400 to-pastel-sky-500 flex items-center justify-center text-white shadow-sm group-hover:scale-105 transition-transform duration-300">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="font-display font-bold text-lg text-slate-800 tracking-tight flex items-center gap-1.5">
                            Pastel<span class="text-pastel-sage-600">Quiz</span>
                            <span class="inline-block px-2 py-0.5 text-[10px] font-semibold bg-pastel-sage-100 text-pastel-sage-600 rounded-full">v1.0</span>
                        </span>
                        <p class="text-[11px] text-slate-500 -mt-0.5 font-medium">Platform Kuis Kalem & Interaktif</p>
                    </div>
                </a>

                <!-- Navigation Links (Sesuai Diagram) -->
                <nav class="hidden md:flex items-center gap-1.5 bg-slate-100/70 p-1 rounded-xl border border-slate-200/50">
                    <a href="{{ route('quizzes.index') }}" 
                       class="px-4 py-2 rounded-lg text-xs font-semibold transition-all flex items-center gap-2 {{ request()->routeIs('quizzes.index', 'quizzes.show', 'dashboard') ? 'bg-white text-pastel-sage-600 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50' }}">
                        <i data-lucide="layout-grid" class="w-4 h-4"></i>
                        Daftar Quiz
                    </a>

                    <a href="{{ route('quizzes.create') }}" 
                       class="px-4 py-2 rounded-lg text-xs font-semibold transition-all flex items-center gap-2 {{ request()->routeIs('quizzes.create') ? 'bg-white text-pastel-sage-600 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50' }}">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        Create Quiz
                    </a>

                    <a href="{{ route('monitoring.index') }}" 
                       class="px-4 py-2 rounded-lg text-xs font-semibold transition-all flex items-center gap-2 {{ request()->routeIs('monitoring.index') ? 'bg-white text-pastel-sage-600 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50' }}">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        Monitor Live Quiz
                    </a>

                    <a href="{{ route('results.index') }}" 
                       class="px-4 py-2 rounded-lg text-xs font-semibold transition-all flex items-center gap-2 {{ request()->routeIs('results.index') ? 'bg-white text-pastel-sage-600 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50' }}">
                        <i data-lucide="award" class="w-4 h-4"></i>
                        Hasil Quiz
                    </a>
                </nav>

                <!-- Right Action: Mode Peserta & User Profile -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('student.enter-code') }}" target="_blank"
                       class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold bg-pastel-sky-50 text-pastel-sky-500 border border-pastel-sky-200 hover:bg-pastel-sky-100 transition-colors">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        Portal Peserta
                    </a>

                    <div class="flex items-center gap-2.5 pl-3 border-l border-slate-200">
                        <div class="w-8 h-8 rounded-full bg-pastel-cream-200 text-slate-700 flex items-center justify-center font-bold text-xs">
                            {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                        </div>
                        <div class="hidden lg:block text-left">
                            <span class="text-xs font-semibold text-slate-800 block leading-tight">{{ auth()->user()->name ?? 'Administrator' }}</span>
                            <span class="text-[10px] text-slate-500">Admin Quiz</span>
                        </div>

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" title="Keluar" 
                                    class="p-2 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-rose-50 transition-colors">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        
        <!-- Flash Notifications -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50/90 border border-emerald-200/80 text-emerald-800 flex items-start gap-3 shadow-soft animate-slide-up">
                <div class="p-1.5 rounded-xl bg-emerald-100 text-emerald-600 shrink-0">
                    <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                </div>
                <div class="flex-1 pt-0.5">
                    <h4 class="font-semibold text-xs tracking-wide uppercase text-emerald-900">Berhasil</h4>
                    <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-rose-50/90 border border-rose-200/80 text-rose-800 flex items-start gap-3 shadow-soft animate-slide-up">
                <div class="p-1.5 rounded-xl bg-rose-100 text-rose-600 shrink-0">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                </div>
                <div class="flex-1 pt-0.5">
                    <h4 class="font-semibold text-xs tracking-wide uppercase text-rose-900">Perhatian</h4>
                    <p class="text-xs text-rose-700 mt-0.5">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-auto border-t border-slate-200/60 py-6 px-4">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-3">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-pastel-sage-400"></div>
                <span>Pastel Quiz Management &bull; Alur Lengkap Diagram SVG</span>
            </div>
            <p>&copy; {{ date('Y') }} Pastel Quiz. Didesain dengan estetika pastel yang tenang, tanpa elemen slop.</p>
        </div>
    </footer>

    <!-- Global Pastel Alert & Confirmation Modal Component -->
    <div id="pastel-modal-container" class="relative z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0" id="pastel-modal-backdrop"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto p-4 sm:p-6 flex min-h-full items-center justify-center">
            <div class="glass-card relative transform overflow-hidden rounded-3xl p-6 sm:p-8 text-center shadow-soft-lg border border-white transition-all max-w-md w-full scale-95 opacity-0 duration-200" id="pastel-modal-box">
                
                <!-- Icon container -->
                <div id="pastel-modal-icon-bg" class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl mb-4 transition-all">
                    <span id="pastel-modal-icon-slot"></span>
                </div>

                <!-- Text info -->
                <h3 class="font-display font-black text-lg text-slate-800 tracking-tight" id="pastel-modal-title">Konfirmasi</h3>
                <p class="text-xs sm:text-sm text-slate-500 mt-1.5 leading-relaxed" id="pastel-modal-message"></p>

                <!-- Optional Security Random Number Challenge for Delete -->
                <div id="pastel-modal-challenge-area" class="mt-4 p-4 rounded-2xl bg-rose-50/70 border border-rose-200/80 hidden text-left">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-rose-800 mb-1">
                        Verifikasi Keamanan Hapus
                    </label>
                    <p class="text-xs text-rose-700 leading-snug mb-2.5">
                        Ketik angka acak berikut: <strong id="pastel-challenge-display" class="font-mono text-sm bg-white px-2 py-0.5 rounded-lg border border-rose-300 text-rose-900 font-bold select-all"></strong>
                    </p>
                    <input type="text" id="pastel-challenge-input" placeholder="Masukkan angka di atas..." 
                           class="w-full text-center font-mono font-bold text-sm px-3 py-2 rounded-xl border border-rose-300 bg-white focus:outline-none focus:ring-2 focus:ring-rose-300">
                    <p id="pastel-challenge-error" class="text-[11px] text-rose-600 mt-1 hidden font-semibold">Angka verifikasi tidak sesuai!</p>
                </div>

                <!-- Action buttons -->
                <div class="mt-6 flex items-center justify-center gap-3" id="pastel-modal-actions">
                    <button type="button" id="pastel-modal-btn-cancel" 
                            class="w-1/2 py-2.5 px-4 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="button" id="pastel-modal-btn-confirm" 
                            class="w-1/2 py-2.5 px-4 rounded-xl text-white text-xs font-bold transition-all shadow-sm">
                        Lanjutkan
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Initialize Lucide Icons & Global Pastel Dialog System -->
    <script>
        lucide.createIcons();

        // Global Pastel Dialog System (Anti-Slop Theme)
        window.PastelDialog = {
            confirmDelete({ title, message, onConfirm }) {
                const randomCode = Math.floor(1000 + Math.random() * 9000).toString();
                
                const container = document.getElementById('pastel-modal-container');
                const backdrop = document.getElementById('pastel-modal-backdrop');
                const box = document.getElementById('pastel-modal-box');
                const iconBg = document.getElementById('pastel-modal-icon-bg');
                const iconSlot = document.getElementById('pastel-modal-icon-slot');
                const titleEl = document.getElementById('pastel-modal-title');
                const msgEl = document.getElementById('pastel-modal-message');
                const challengeArea = document.getElementById('pastel-modal-challenge-area');
                const challengeDisplay = document.getElementById('pastel-challenge-display');
                const challengeInput = document.getElementById('pastel-challenge-input');
                const challengeError = document.getElementById('pastel-challenge-error');
                const btnCancel = document.getElementById('pastel-modal-btn-cancel');
                const btnConfirm = document.getElementById('pastel-modal-btn-confirm');

                // Set content
                titleEl.textContent = title || 'Hapus Data?';
                msgEl.textContent = message || 'Tindakan ini permanen dan tidak dapat dibatalkan.';
                
                // Styling for Delete (Pastel Rose)
                iconBg.className = 'mx-auto flex h-14 w-14 items-center justify-center rounded-2xl mb-4 bg-rose-100 text-rose-600';
                iconSlot.innerHTML = '<i data-lucide="trash-2" class="w-7 h-7"></i>';
                
                btnConfirm.className = 'w-1/2 py-2.5 px-4 rounded-xl bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white text-xs font-bold shadow-md shadow-rose-500/20 transition-all';
                btnConfirm.textContent = 'Hapus Sekarang';

                // Setup challenge
                challengeArea.classList.remove('hidden');
                challengeDisplay.textContent = randomCode;
                challengeInput.value = '';
                challengeError.classList.add('hidden');

                // Show modal with animation
                container.classList.remove('hidden');
                setTimeout(() => {
                    backdrop.classList.remove('opacity-0');
                    backdrop.classList.add('opacity-100');
                    box.classList.remove('scale-95', 'opacity-0');
                    box.classList.add('scale-100', 'opacity-100');
                    lucide.createIcons();
                    challengeInput.focus();
                }, 10);

                const closeModal = () => {
                    backdrop.classList.remove('opacity-100');
                    backdrop.classList.add('opacity-0');
                    box.classList.remove('scale-100', 'opacity-100');
                    box.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => {
                        container.classList.add('hidden');
                        challengeArea.classList.add('hidden');
                    }, 200);
                };

                btnCancel.onclick = closeModal;

                btnConfirm.onclick = () => {
                    if (challengeInput.value.trim() !== randomCode) {
                        challengeError.classList.remove('hidden');
                        challengeInput.classList.add('border-rose-500', 'ring-2', 'ring-rose-200');
                        challengeInput.focus();
                        return;
                    }
                    closeModal();
                    if (typeof onConfirm === 'function') {
                        onConfirm();
                    }
                };
            },

            alert({ title, message, type = 'success' }) {
                const container = document.getElementById('pastel-modal-container');
                const backdrop = document.getElementById('pastel-modal-backdrop');
                const box = document.getElementById('pastel-modal-box');
                const iconBg = document.getElementById('pastel-modal-icon-bg');
                const iconSlot = document.getElementById('pastel-modal-icon-slot');
                const titleEl = document.getElementById('pastel-modal-title');
                const msgEl = document.getElementById('pastel-modal-message');
                const challengeArea = document.getElementById('pastel-modal-challenge-area');
                const btnCancel = document.getElementById('pastel-modal-btn-cancel');
                const btnConfirm = document.getElementById('pastel-modal-btn-confirm');

                challengeArea.classList.add('hidden');
                btnCancel.classList.add('hidden');

                titleEl.textContent = title;
                msgEl.textContent = message;

                if (type === 'success') {
                    iconBg.className = 'mx-auto flex h-14 w-14 items-center justify-center rounded-2xl mb-4 bg-emerald-100 text-emerald-600';
                    iconSlot.innerHTML = '<i data-lucide="check-circle" class="w-7 h-7"></i>';
                    btnConfirm.className = 'w-full py-2.5 px-4 rounded-xl bg-pastel-sage-500 hover:bg-pastel-sage-600 text-white text-xs font-bold';
                    btnConfirm.textContent = 'Selesai';
                } else {
                    iconBg.className = 'mx-auto flex h-14 w-14 items-center justify-center rounded-2xl mb-4 bg-rose-100 text-rose-600';
                    iconSlot.innerHTML = '<i data-lucide="alert-circle" class="w-7 h-7"></i>';
                    btnConfirm.className = 'w-full py-2.5 px-4 rounded-xl bg-rose-500 hover:bg-rose-600 text-white text-xs font-bold';
                    btnConfirm.textContent = 'Mengerti';
                }

                container.classList.remove('hidden');
                setTimeout(() => {
                    backdrop.classList.remove('opacity-0');
                    backdrop.classList.add('opacity-100');
                    box.classList.remove('scale-95', 'opacity-0');
                    box.classList.add('scale-100', 'opacity-100');
                    lucide.createIcons();
                }, 10);

                btnConfirm.onclick = () => {
                    backdrop.classList.remove('opacity-100');
                    backdrop.classList.add('opacity-0');
                    box.classList.remove('scale-100', 'opacity-100');
                    box.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => {
                        container.classList.add('hidden');
                        btnCancel.classList.remove('hidden');
                    }, 200);
                };
            }
        };
    </script>
    @stack('scripts')
</body>
</html>
