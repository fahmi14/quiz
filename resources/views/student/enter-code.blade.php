@extends('layouts.student')

@section('title', 'Masuk Kuis - Masukkan Kode')

@section('content')
<div class="w-full max-w-md animate-slide-up">
    
    <!-- Top badge -->
    <div class="text-center mb-6">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-pastel-sage-100 border border-pastel-sage-200 text-pastel-sage-600 text-xs font-semibold mb-3">
            <span class="w-2 h-2 rounded-full bg-pastel-sage-500 animate-pulse"></span>
            Selamat Datang di Pastel Quiz
        </div>
        <h1 class="font-display font-extrabold text-3xl text-slate-800 tracking-tight">Mulai Tantangan Kuis</h1>
        <p class="text-sm text-slate-500 mt-1.5">Masukkan kode kuis dari penyelenggara untuk memulai</p>
    </div>

    <!-- Card Input Kode -->
    <div class="glass-card rounded-3xl p-8 shadow-soft-lg relative overflow-hidden border border-white/80">
        
        <!-- Corner decorative motif -->
        <div class="absolute -top-10 -right-10 w-28 h-28 bg-gradient-to-bl from-pastel-sage-200/50 to-transparent rounded-full pointer-events-none"></div>

        @if(session('error'))
            <div class="mb-5 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2.5">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 text-rose-500"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('student.check-code') }}" method="POST" class="space-y-5">
            @csrf
            
            <div>
                <label for="code" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                    Kode Kuis
                </label>
                <div class="relative">
                    <input type="text" 
                           id="code" 
                           name="code" 
                           value="{{ old('code') }}" 
                           placeholder="CONTOH: NUSANTARA26"
                           required
                           autofocus
                           style="letter-spacing: 0.15em;"
                           class="w-full uppercase font-mono font-bold text-center text-lg px-4 py-3.5 rounded-2xl border-2 border-slate-200 bg-white/90 text-slate-800 placeholder:text-slate-300 placeholder:font-sans placeholder:normal-case focus:outline-none focus:border-pastel-sage-400 focus:ring-4 focus:ring-pastel-sage-100 transition-all shadow-sm">
                    <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="key-round" class="w-5 h-5"></i>
                    </div>
                </div>
                @error('code')
                    <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" 
                    class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-pastel-sage-500 to-pastel-sage-600 text-white font-semibold text-sm shadow-md shadow-pastel-sage-500/20 hover:from-pastel-sage-600 hover:to-pastel-sage-700 hover:shadow-lg hover:shadow-pastel-sage-500/30 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 flex items-center justify-center gap-2 group">
                <span>Lanjutkan</span>
                <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
            </button>
        </form>

        <!-- Divider -->
        <div class="mt-8 pt-6 border-t border-slate-100 text-center">
            <a href="{{ route('login') }}" class="text-xs text-slate-400 hover:text-pastel-sage-600 font-medium inline-flex items-center gap-1.5 transition-colors">
                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                <span>Masuk sebagai Admin Pengelola Kuis &rarr;</span>
            </a>
        </div>
    </div>

</div>
@endsection
