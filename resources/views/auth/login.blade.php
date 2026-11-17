@extends('layouts.student')

@section('title', 'Login Admin Quiz')

@section('content')
<div class="w-full max-w-md animate-slide-up">
    
    <!-- Top Brand -->
    <div class="text-center mb-6">
        <div class="w-12 h-12 mx-auto rounded-2xl bg-gradient-to-tr from-pastel-sage-400 to-pastel-sky-500 flex items-center justify-center text-white shadow-soft mb-3">
            <i data-lucide="shield-check" class="w-6 h-6"></i>
        </div>
        <h1 class="font-display font-extrabold text-2xl sm:text-3xl text-slate-800 tracking-tight">Login Admin</h1>
        <p class="text-xs text-slate-500 mt-1">Kelola pembuatan kuis, template soal, live monitoring & rekap hasil</p>
    </div>

    <!-- Login Card -->
    <div class="glass-card rounded-3xl p-8 shadow-soft-lg border border-white/80 relative">
        
        @if(session('error'))
            <div class="mb-5 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-500 shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                @foreach($errors->all() as $err)
                    <p class="flex items-center gap-1.5"><i data-lucide="alert-circle" class="w-3.5 h-3.5 text-rose-500 shrink-0"></i> {{ $err }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('login.attempt') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                    Email atau Username
                </label>
                <div class="relative">
                    <input type="text" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           placeholder="Masukkan email atau username Anda..."
                           required 
                           autofocus
                           class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-white/90 text-slate-800 text-xs sm:text-sm focus:outline-none focus:border-pastel-sage-400 focus:ring-4 focus:ring-pastel-sage-100 transition-all shadow-sm">
                    <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </div>
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                    Kata Sandi
                </label>
                <div class="relative">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Masukkan kata sandi..."
                           required 
                           class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-white/90 text-slate-800 text-xs sm:text-sm focus:outline-none focus:border-pastel-sage-400 focus:ring-4 focus:ring-pastel-sage-100 transition-all shadow-sm">
                    <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs py-1">
                <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded text-pastel-sage-500 border-slate-300 focus:ring-pastel-sage-300">
                    <span>Ingat Saya</span>
                </label>
            </div>

            <button type="submit" 
                    class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-pastel-sage-500 to-pastel-sage-600 hover:from-pastel-sage-600 hover:to-pastel-sage-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-pastel-sage-500/20 hover:shadow-lg transition-all flex items-center justify-center gap-2">
                <i data-lucide="log-in" class="w-4 h-4"></i>
                <span>Masuk ke Dashboard Admin</span>
            </button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-100 text-center">
            <a href="{{ route('student.enter-code') }}" class="text-xs text-slate-500 hover:text-pastel-sage-600 font-medium inline-flex items-center gap-1.5 transition-colors">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali ke Halaman Peserta Kuis</span>
            </a>
        </div>

    </div>
</div>
@endsection
