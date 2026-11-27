@extends('layouts.student')

@section('title', 'Gabung Kuis - ' . $quiz->title)

@section('content')
<div class="w-full max-w-lg animate-slide-up">
    
    <!-- Top badge & Info -->
    <div class="text-center mb-6">
        <a href="{{ route('student.enter-code') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 mb-3 transition-colors">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Ganti Kode Kuis</span>
        </a>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-pastel-sky-100 text-pastel-sky-500 text-xs font-bold font-mono tracking-wider mb-2">
            KODE: {{ $quiz->code }}
        </div>
        <h1 class="font-display font-extrabold text-2xl sm:text-3xl text-slate-800 tracking-tight">{{ $quiz->title }}</h1>
        @if($quiz->description)
            <p class="text-xs sm:text-sm text-slate-500 mt-2 max-w-md mx-auto">{{ $quiz->description }}</p>
        @endif
    </div>

    <!-- Card Form Nama & Aturan Kuis -->
    <div class="glass-card rounded-3xl p-6 sm:p-8 shadow-soft-lg border border-white/80 relative">
        
        <!-- Quick stats chip -->
        <div class="grid grid-cols-3 gap-3 mb-6 p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200/60 text-center">
            <div class="p-2">
                <span class="block text-slate-400 text-[10px] font-bold uppercase tracking-wider">Durasi</span>
                <span class="font-display font-bold text-slate-700 text-base flex items-center justify-center gap-1 mt-0.5">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-pastel-sage-500"></i>
                    {{ $quiz->duration_minutes }} Mnt
                </span>
            </div>
            <div class="p-2 border-x border-slate-200/60">
                <span class="block text-slate-400 text-[10px] font-bold uppercase tracking-wider">Jumlah Soal</span>
                <span class="font-display font-bold text-slate-700 text-base flex items-center justify-center gap-1 mt-0.5">
                    <i data-lucide="help-circle" class="w-3.5 h-3.5 text-pastel-sky-500"></i>
                    {{ $quiz->questions_count }} Soal
                </span>
            </div>
            <div class="p-2">
                <span class="block text-slate-400 text-[10px] font-bold uppercase tracking-wider">Acak Soal</span>
                <span class="font-display font-bold text-slate-700 text-xs sm:text-sm flex items-center justify-center gap-1 mt-1">
                    @if($quiz->shuffle_questions)
                        <span class="text-emerald-600 font-semibold flex items-center gap-0.5">
                            <i data-lucide="shuffle" class="w-3 h-3"></i> Ya
                        </span>
                    @else
                        <span class="text-slate-500">Urut</span>
                    @endif
                </span>
            </div>
        </div>

        @if(session('error'))
            <div class="mb-5 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2.5">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 text-rose-500"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('student.start', $quiz->code) }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                    Nama Lengkap Peserta <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}" 
                           placeholder="Masukkan nama lengkap Anda..."
                           required
                           autofocus
                           class="w-full px-4 py-3.5 rounded-2xl border-2 border-slate-200 bg-white/90 text-slate-800 placeholder:text-slate-300 focus:outline-none focus:border-pastel-sage-400 focus:ring-4 focus:ring-pastel-sage-100 transition-all shadow-sm text-sm">
                    <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </div>
                </div>
                @error('name')
                    <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- No Absen dan Kelas (Opsional) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label for="attendance_number" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                        No. Absen <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="attendance_number" 
                               name="attendance_number" 
                               value="{{ old('attendance_number') }}" 
                               placeholder="Contoh: 14"
                               class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-white/90 text-slate-800 placeholder:text-slate-300 focus:outline-none focus:border-pastel-sage-400 focus:ring-4 focus:ring-pastel-sage-100 transition-all shadow-sm text-sm">
                        <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="hash" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="class_name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                        Kelas <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="class_name" 
                               name="class_name" 
                               value="{{ old('class_name') }}" 
                               placeholder="Contoh: XII IPA 1"
                               class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-white/90 text-slate-800 placeholder:text-slate-300 focus:outline-none focus:border-pastel-sage-400 focus:ring-4 focus:ring-pastel-sage-100 transition-all shadow-sm text-sm">
                        <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label for="identifier" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                    Email / Nomor HP / Instansi (Opsional)
                </label>
                <div class="relative">
                    <input type="text" 
                           id="identifier" 
                           name="identifier" 
                           value="{{ old('identifier') }}" 
                           placeholder="Contoh: siswa@sekolah.sch.id atau No. Presensi"
                           class="w-full px-4 py-3.5 rounded-2xl border-2 border-slate-200 bg-white/90 text-slate-800 placeholder:text-slate-300 focus:outline-none focus:border-pastel-sage-400 focus:ring-4 focus:ring-pastel-sage-100 transition-all shadow-sm text-sm">
                    <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="id-card" class="w-5 h-5"></i>
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" 
                        class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-pastel-sage-500 to-pastel-sage-600 text-white font-semibold text-sm shadow-md shadow-pastel-sage-500/20 hover:from-pastel-sage-600 hover:to-pastel-sage-700 hover:shadow-lg hover:shadow-pastel-sage-500/30 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 flex items-center justify-center gap-2 group">
                    <i data-lucide="play" class="w-4 h-4 fill-white"></i>
                    <span>Mulai Kerjakan Sekarang</span>
                </button>
            </div>
        </form>

        <p class="text-[11px] text-center text-slate-400 mt-4">
            Pastikan koneksi internet stabil. Waktu pengerjaan akan langsung berjalan saat Anda menekan tombol mulai.
        </p>

    </div>
</div>
@endsection
