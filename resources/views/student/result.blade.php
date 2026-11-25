@extends('layouts.student')

@section('title', 'Hasil Kuis - ' . $quiz->title)

@section('content')
<div class="w-full max-w-lg animate-slide-up text-center">
    
    <!-- Celebration Card -->
    <div class="glass-card rounded-3xl p-8 sm:p-10 shadow-soft-lg border border-white/90 relative overflow-hidden">
        
        <!-- Subtle decorative motif -->
        <div class="absolute -top-12 -left-12 w-36 h-36 bg-gradient-to-br from-pastel-sage-200/40 to-transparent rounded-full pointer-events-none"></div>
        <div class="absolute -bottom-12 -right-12 w-36 h-36 bg-gradient-to-tl from-pastel-sky-200/40 to-transparent rounded-full pointer-events-none"></div>

        <!-- Trophy / Badge Icon -->
        <div class="w-20 h-20 mx-auto rounded-3xl bg-gradient-to-tr from-pastel-sage-100 to-pastel-sky-100 border border-pastel-sage-200 flex items-center justify-center text-pastel-sage-600 mb-6 shadow-sm animate-float">
            <i data-lucide="award" class="w-10 h-10 text-pastel-sage-600"></i>
        </div>

        <h1 class="font-display font-black text-2xl sm:text-3xl text-slate-800 tracking-tight">Kuis Selesai!</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Terima kasih telah berpartisipasi, <strong class="text-slate-700">{{ $participant->name }}</strong>.</p>

        <!-- Score Ring / Box -->
        <div class="my-8 p-6 rounded-3xl bg-gradient-to-b from-slate-50/90 to-pastel-sage-50/50 border border-slate-200/70 shadow-inner">
            <span class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-1">Skor Akhir Anda</span>
            <div class="font-display font-extrabold text-5xl text-pastel-sage-600 tracking-tight my-1">
                {{ round($participant->score, 1) }}
            </div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-pastel-sage-100 text-pastel-sage-700 text-xs font-semibold mt-2">
                Persentase Nilai: {{ $percentage }}%
            </div>
        </div>

        <!-- Breakdown Details -->
        <div class="grid grid-cols-3 gap-3 mb-8 text-center">
            <div class="p-3 rounded-2xl bg-white/70 border border-slate-200/60 shadow-sm">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Benar</span>
                <span class="font-display font-bold text-emerald-600 text-lg flex items-center justify-center gap-1 mt-0.5">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    {{ $participant->total_correct }}
                </span>
            </div>
            <div class="p-3 rounded-2xl bg-white/70 border border-slate-200/60 shadow-sm">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Terjawab</span>
                <span class="font-display font-bold text-slate-700 text-lg flex items-center justify-center gap-1 mt-0.5">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                    {{ $participant->total_answered }}
                </span>
            </div>
            <div class="p-3 rounded-2xl bg-white/70 border border-slate-200/60 shadow-sm">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Soal</span>
                <span class="font-display font-bold text-slate-700 text-lg flex items-center justify-center gap-1 mt-0.5">
                    <i data-lucide="help-circle" class="w-4 h-4"></i>
                    {{ $participant->total_questions }}
                </span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-3">
            <a href="{{ route('student.enter-code') }}" 
               class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-pastel-sage-500 to-pastel-sage-600 hover:from-pastel-sage-600 hover:to-pastel-sage-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-pastel-sage-500/20 hover:shadow-lg transition-all flex items-center justify-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Ikuti Kuis Lainnya</span>
            </a>

            <button type="button" onclick="celebrate()" 
                    class="w-full py-3 px-6 rounded-2xl bg-white border border-slate-200/80 hover:bg-slate-50 text-slate-600 font-semibold text-xs transition-colors flex items-center justify-center gap-2">
                <i data-lucide="sparkles" class="w-4 h-4 text-pastel-amber-500"></i>
                <span>Rayakan Lagi (Tepuk Tangan & Konfeti)!</span>
            </button>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    // Audio synthesizer untuk suara tepuk tangan (applause) menggunakan Web Audio API
    function playApplauseSound() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();

            // Durasi tepuk tangan riuh (~3 detik)
            const duration = 2.8;
            const sampleRate = ctx.sampleRate;
            const bufferSize = sampleRate * duration;
            const buffer = ctx.createBuffer(1, bufferSize, sampleRate);
            const data = buffer.getChannelData(0);

            // Generate noise tepukan acak berlapis
            for (let i = 0; i < bufferSize; i++) {
                data[i] = Math.random() * 2 - 1;
            }

            const noise = ctx.createBufferSource();
            noise.buffer = buffer;

            // Bandpass filter untuk suara clapping manusia (sekitar 800-1400Hz)
            const bandpass = ctx.createBiquadFilter();
            bandpass.type = 'bandpass';
            bandpass.frequency.value = 1100;
            bandpass.Q.value = 1.2;

            // Gain node untuk amplop suara fade-in dan fade-out halus
            const gain = ctx.createGain();
            const now = ctx.currentTime;
            gain.gain.setValueAtTime(0.01, now);
            gain.gain.exponentialRampToValueAtTime(0.7, now + 0.3); // Suara mulai riuh
            gain.gain.exponentialRampToValueAtTime(0.5, now + 1.8);
            gain.gain.exponentialRampToValueAtTime(0.001, now + duration); // Perlahan mereda

            noise.connect(bandpass);
            bandpass.connect(gain);
            gain.connect(ctx.destination);

            noise.start(now);
            noise.stop(now + duration);

            // Simulasi ketukan tepuk tangan individu yang menonjol
            const numClaps = 28;
            for (let c = 0; c < numClaps; c++) {
                const clapTime = now + (c * (duration / numClaps)) + (Math.random() * 0.08);
                const clapBuffer = ctx.createBuffer(1, sampleRate * 0.06, sampleRate);
                const clapData = clapBuffer.getChannelData(0);
                for (let j = 0; j < clapData.length; j++) {
                    clapData[j] = (Math.random() * 2 - 1) * Math.exp(-j / (sampleRate * 0.015));
                }
                const clapSource = ctx.createBufferSource();
                clapSource.buffer = clapBuffer;

                const clapFilter = ctx.createBiquadFilter();
                clapFilter.type = 'bandpass';
                clapFilter.frequency.value = 1200 + (Math.random() * 400 - 200);
                clapFilter.Q.value = 2.0;

                const clapGain = ctx.createGain();
                clapGain.gain.setValueAtTime(0.4 + Math.random() * 0.3, clapTime);

                clapSource.connect(clapFilter);
                clapFilter.connect(clapGain);
                clapGain.connect(ctx.destination);

                clapSource.start(clapTime);
                clapSource.stop(clapTime + 0.06);
            }
        } catch (e) {
            console.warn('AudioContext playback error:', e);
        }
    }

    function triggerConfetti() {
        if (typeof confetti === 'function') {
            confetti({
                particleCount: 90,
                spread: 75,
                origin: { y: 0.6 },
                colors: ['#8EB59E', '#6FAFD6', '#DF7B8F', '#E0A838', '#9B8AC4']
            });
        }
    }

    function celebrate() {
        playApplauseSound();
        triggerConfetti();
    }

    // Auto-fire konfeti & suara tepuk tangan saat halaman hasil selesai dimuat
    window.addEventListener('load', () => {
        celebrate();
    });

    // Handle jika browser memblokir audio autoplay sebelum interaksi pengguna
    document.addEventListener('click', () => {
        // Jika belum sempat bunyi saat load karena autoplay policy browser
        if (!window.__soundPlayed) {
            playApplauseSound();
            window.__soundPlayed = true;
        }
    }, { once: true });
</script>
@endpush
