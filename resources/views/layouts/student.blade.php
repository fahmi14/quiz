<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kuis Interaktif') - Pastel Quiz</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
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
                        'soft': '0 10px 30px rgba(78, 102, 90, 0.08)',
                        'soft-lg': '0 20px 45px rgba(78, 102, 90, 0.12)',
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Canvas Confetti for Celebration -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>

    <style>
        /* Peaceful Pastel Animated Background with floating motifs */
        body {
            background-color: #F8FAF8;
            background-image: 
                radial-gradient(at 0% 0%, rgba(208, 225, 215, 0.5) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(225, 239, 247, 0.6) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(245, 205, 211, 0.4) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(248, 227, 182, 0.45) 0px, transparent 50%);
            background-attachment: fixed;
            min-height: 100vh;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.9);
        }

        /* Float animations */
        @keyframes float-shape-1 {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(25px, 20px) rotate(15deg); }
        }
        @keyframes float-shape-2 {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(-20px, -25px) rotate(-10deg); }
        }
        .shape-float-1 { animation: float-shape-1 12s ease-in-out infinite; }
        .shape-float-2 { animation: float-shape-2 15s ease-in-out infinite; }
    </style>
    @stack('styles')
</head>
<body class="flex flex-col min-h-screen relative overflow-x-hidden selection:bg-pastel-sage-200">

    <!-- Ambient background decorative pastel motifs -->
    <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden">
        <!-- Motif 1: Soft Pastel Sage Blob -->
        <div class="absolute -top-20 -left-20 w-80 h-80 rounded-full bg-pastel-sage-200/40 blur-3xl shape-float-1"></div>
        <!-- Motif 2: Soft Sky Blue Blob -->
        <div class="absolute top-1/3 -right-20 w-96 h-96 rounded-full bg-pastel-sky-200/40 blur-3xl shape-float-2"></div>
        <!-- Motif 3: Warm Blush Blob -->
        <div class="absolute -bottom-24 left-1/4 w-88 h-88 rounded-full bg-pastel-blush-200/35 blur-3xl shape-float-1"></div>
    </div>

    <!-- Main Container -->
    <main class="flex-1 flex flex-col justify-center items-center px-4 py-8 relative z-10">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="py-4 text-center text-xs text-slate-400 relative z-10">
        <p>Pastel Quiz Platform &bull; Tenang, Fokus, Menyenangkan</p>
    </footer>

    <!-- Pastel Custom Confirmation & Alert Dialog for Student -->
    <div id="student-modal-container" class="relative z-50 hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0" id="student-modal-backdrop"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto p-4 sm:p-6 flex min-h-full items-center justify-center">
            <div class="glass-card relative transform overflow-hidden rounded-3xl p-6 sm:p-8 text-center shadow-soft-lg border border-white transition-all max-w-sm w-full scale-95 opacity-0 duration-200" id="student-modal-box">
                <div id="student-modal-icon-bg" class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl mb-4 bg-pastel-sage-100 text-pastel-sage-600">
                    <span id="student-modal-icon-slot"><i data-lucide="help-circle" class="w-7 h-7"></i></span>
                </div>
                <h3 class="font-display font-black text-lg text-slate-800 tracking-tight" id="student-modal-title">Konfirmasi</h3>
                <p class="text-xs sm:text-sm text-slate-500 mt-1.5 leading-relaxed" id="student-modal-message"></p>
                <div class="mt-6 flex items-center justify-center gap-3">
                    <button type="button" id="student-modal-btn-cancel" class="w-1/2 py-2.5 px-4 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="button" id="student-modal-btn-confirm" class="w-1/2 py-2.5 px-4 rounded-xl bg-pastel-sage-500 hover:bg-pastel-sage-600 text-white text-xs font-bold transition-all shadow-sm">
                        Ya, Kumpulkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        window.StudentDialog = {
            confirm({ title, message, confirmText = 'Lanjutkan', onConfirm }) {
                const container = document.getElementById('student-modal-container');
                const backdrop = document.getElementById('student-modal-backdrop');
                const box = document.getElementById('student-modal-box');
                const titleEl = document.getElementById('student-modal-title');
                const msgEl = document.getElementById('student-modal-message');
                const btnCancel = document.getElementById('student-modal-btn-cancel');
                const btnConfirm = document.getElementById('student-modal-btn-confirm');

                titleEl.textContent = title;
                msgEl.textContent = message;
                btnConfirm.textContent = confirmText;
                btnCancel.classList.remove('hidden');

                container.classList.remove('hidden');
                setTimeout(() => {
                    backdrop.classList.remove('opacity-0');
                    backdrop.classList.add('opacity-100');
                    box.classList.remove('scale-95', 'opacity-0');
                    box.classList.add('scale-100', 'opacity-100');
                    lucide.createIcons();
                }, 10);

                const close = () => {
                    backdrop.classList.remove('opacity-100');
                    backdrop.classList.add('opacity-0');
                    box.classList.remove('scale-100', 'opacity-100');
                    box.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => container.classList.add('hidden'), 200);
                };

                btnCancel.onclick = close;
                btnConfirm.onclick = () => {
                    close();
                    if (typeof onConfirm === 'function') onConfirm();
                };
            },

            alert({ title, message, onOk }) {
                const container = document.getElementById('student-modal-container');
                const backdrop = document.getElementById('student-modal-backdrop');
                const box = document.getElementById('student-modal-box');
                const titleEl = document.getElementById('student-modal-title');
                const msgEl = document.getElementById('student-modal-message');
                const btnCancel = document.getElementById('student-modal-btn-cancel');
                const btnConfirm = document.getElementById('student-modal-btn-confirm');

                titleEl.textContent = title;
                msgEl.textContent = message;
                btnConfirm.textContent = 'Mengerti';
                btnCancel.classList.add('hidden');

                container.classList.remove('hidden');
                setTimeout(() => {
                    backdrop.classList.remove('opacity-0');
                    backdrop.classList.add('opacity-100');
                    box.classList.remove('scale-95', 'opacity-0');
                    box.classList.add('scale-100', 'opacity-100');
                    lucide.createIcons();
                }, 10);

                const close = () => {
                    backdrop.classList.remove('opacity-100');
                    backdrop.classList.add('opacity-0');
                    box.classList.remove('scale-100', 'opacity-100');
                    box.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => container.classList.add('hidden'), 200);
                };

                btnConfirm.onclick = () => {
                    close();
                    if (typeof onOk === 'function') onOk();
                };
            }
        };
    </script>
    @stack('scripts')
</body>
</html>
