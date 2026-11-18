@extends('layouts.admin')

@section('title', 'Edit Kuis - ' . $quiz->title)

@section('content')
<div class="max-w-4xl mx-auto space-y-6 animate-slide-up" x-data="quizEditor()">
    
    <!-- Header -->
    <div class="flex items-center justify-between glass-card p-6 rounded-3xl shadow-soft">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('quizzes.show', $quiz) }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 transition-colors">
                    &larr; Kembali ke Detail Kuis
                </a>
                <span class="text-slate-300">&bull;</span>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-pastel-sky-50 text-pastel-sky-600 border border-pastel-sky-200/60 font-mono font-bold text-xs">
                    {{ $quiz->code }}
                </span>
            </div>
            <h1 class="font-display font-black text-2xl text-slate-800 tracking-tight">Edit Pengaturan Kuis</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Ubah nama kuis, kode kuis, masa aktif (jadwal), durasi pengerjaan, dan opsi acak.</p>
        </div>

        <a href="{{ route('quizzes.show', $quiz) }}" class="px-4 py-2 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
            Batal
        </a>
    </div>

    <!-- Edit Form -->
    <form action="{{ route('quizzes.update', $quiz) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. IDENTITAS KUIS & KODE -->
        <div class="glass-card p-6 sm:p-8 rounded-3xl shadow-soft border border-white/90">
            <h3 class="font-display font-bold text-base text-slate-800 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-pastel-sage-200 text-pastel-sage-800 text-xs font-bold flex items-center justify-center">1</span>
                Identitas & Kode Kuis
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                
                <div class="md:col-span-2">
                    <label for="title" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Nama Kuis <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="{{ old('title', $quiz->title) }}" 
                           required
                           class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none focus:border-pastel-sage-400 focus:ring-4 focus:ring-pastel-sage-100 transition-all shadow-sm">
                    @error('title') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="code" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5 flex items-center justify-between">
                        <span>Kode Kuis Peserta <span class="text-rose-500">*</span></span>
                        <span class="text-[10px] text-pastel-sage-600 font-normal">Dipakai peserta untuk masuk</span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="code" 
                               name="code" 
                               value="{{ old('code', $quiz->code) }}" 
                               required
                               style="letter-spacing: 0.1em;"
                               class="w-full uppercase font-mono font-bold px-4 py-3 rounded-2xl border-2 border-slate-200 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none focus:border-pastel-sage-400 focus:ring-4 focus:ring-pastel-sage-100 transition-all shadow-sm">
                        <button type="button" @click="generateNewCode()" title="Acak Kode Baru"
                                class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-pastel-sage-600">
                            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        </button>
                    </div>
                    @error('code') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="duration_minutes" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Durasi Pengerjaan (Menit) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" 
                               id="duration_minutes" 
                               name="duration_minutes" 
                               value="{{ old('duration_minutes', $quiz->duration_minutes) }}" 
                               min="1" 
                               max="360"
                               required
                               class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none focus:border-pastel-sage-400 focus:ring-4 focus:ring-pastel-sage-100 transition-all shadow-sm">
                        <span class="absolute inset-y-0 right-4 flex items-center text-xs text-slate-400 pointer-events-none">Menit</span>
                    </div>
                </div>

                <!-- Jumlah Pilihan Ganda -->
                <div>
                    <label for="options_count" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5 flex items-center justify-between">
                        <span>Jumlah Pilihan Ganda <span class="text-rose-500">*</span></span>
                        <span class="text-[10px] text-pastel-sage-600 font-semibold" x-text="optionsCount + ' Pilihan (A - ' + letters[optionsCount - 1] + ')'"></span>
                    </label>
                    <select id="options_count" 
                            name="options_count" 
                            x-model.number="optionsCount"
                            required
                            class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-white text-slate-800 text-xs sm:text-sm font-semibold focus:outline-none focus:border-pastel-sage-400 focus:ring-4 focus:ring-pastel-sage-100 transition-all shadow-sm">
                        <template x-for="n in 19" :key="n + 1">
                            <option :value="n + 1" :selected="optionsCount === (n + 1)" x-text="(n + 1) + ' Pilihan (A s/d ' + letters[n] + ')'"></option>
                        </template>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Deskripsi / Petunjuk untuk Peserta (Opsional)
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="2" 
                              placeholder="Tuliskan petunjuk pengerjaan atau catatan singkat untuk peserta..."
                              class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none focus:border-pastel-sage-400 focus:ring-4 focus:ring-pastel-sage-100 transition-all shadow-sm">{{ old('description', $quiz->description) }}</textarea>
                </div>

            </div>
        </div>

        <!-- 2. MASA AKTIF & STATUS -->
        <div class="glass-card p-6 sm:p-8 rounded-3xl shadow-soft border border-white/90">
            <h3 class="font-display font-bold text-base text-slate-800 mb-2 pb-2 border-b border-slate-100 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-pastel-sky-200 text-pastel-sky-800 text-xs font-bold flex items-center justify-center">2</span>
                Masa Aktif & Status Akses
            </h3>
            <p class="text-xs text-slate-500 mb-4">Atur jadwal mulai dan batas akhir pengerjaan kuis.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                
                <div>
                    <label for="starts_at" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Waktu Mulai Kuis (Opsional)
                    </label>
                    <input type="datetime-local" 
                           id="starts_at" 
                           name="starts_at" 
                           value="{{ old('starts_at', $quiz->starts_at ? $quiz->starts_at->format('Y-m-d\TH:i') : '') }}"
                           class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none focus:border-pastel-sage-400 focus:ring-4 focus:ring-pastel-sage-100 transition-all shadow-sm">
                    <p class="text-[11px] text-slate-400 mt-1">Kosongkan jika kuis bisa langsung dikerjakan kapan saja.</p>
                </div>

                <div>
                    <label for="expires_at" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Masa Aktif Berakhir (Batas Waktu)
                    </label>
                    <input type="datetime-local" 
                           id="expires_at" 
                           name="expires_at" 
                           value="{{ old('expires_at', $quiz->expires_at ? $quiz->expires_at->format('Y-m-d\TH:i') : '') }}"
                           class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none focus:border-pastel-sage-400 focus:ring-4 focus:ring-pastel-sage-100 transition-all shadow-sm">
                    <p class="text-[11px] text-slate-400 mt-1">Setelah waktu ini, peserta tidak dapat lagi memulai pengerjaan.</p>
                </div>

            </div>

            <!-- Status Aktif Toggle -->
            <div class="p-4 rounded-2xl border-2 border-slate-200 bg-slate-50/50 flex items-center justify-between">
                <div>
                    <span class="block text-xs font-bold text-slate-800">Status Kuis Aktif</span>
                    <span class="text-[11px] text-slate-500">Jika dinonaktifkan, peserta tidak akan bisa bergabung meskipun masa aktif belum habis.</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $quiz->is_active) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                </label>
            </div>
        </div>

        <!-- 3. OPSI ACAK (RANDOMISASI) -->
        <div class="glass-card p-6 sm:p-8 rounded-3xl shadow-soft border border-white/90">
            <h3 class="font-display font-bold text-base text-slate-800 mb-2 pb-2 border-b border-slate-100 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-pastel-amber-200 text-pastel-amber-800 text-xs font-bold flex items-center justify-center">3</span>
                Pengaturan Acak Soal & Pilihan
            </h3>
            <p class="text-xs text-slate-500 mb-4">Pengaturan ini akan diterapkan ke setiap peserta yang baru memulai kuis.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <label class="p-4 rounded-2xl border-2 transition-all flex items-start gap-3 cursor-pointer"
                       :class="shuffleQuestions ? 'border-pastel-sage-400 bg-pastel-sage-50/70' : 'border-slate-200 bg-white hover:border-slate-300'">
                    <input type="checkbox" name="shuffle_questions" value="1" x-model="shuffleQuestions" class="mt-1 w-4 h-4 rounded text-pastel-sage-500 border-slate-300 focus:ring-pastel-sage-400">
                    <div>
                        <span class="block text-xs font-bold text-slate-800">Acak Urutan Soal</span>
                        <span class="text-[11px] text-slate-500 leading-snug">Urutan butir soal diacak untuk tiap peserta.</span>
                    </div>
                </label>

                <label class="p-4 rounded-2xl border-2 transition-all flex items-start gap-3 cursor-pointer"
                       :class="shuffleOptions ? 'border-pastel-sky-400 bg-pastel-sky-50/70' : 'border-slate-200 bg-white hover:border-slate-300'">
                    <input type="checkbox" name="shuffle_options" value="1" x-model="shuffleOptions" class="mt-1 w-4 h-4 rounded text-pastel-sky-500 border-slate-300 focus:ring-pastel-sky-400">
                    <div>
                        <span class="block text-xs font-bold text-slate-800">Acak Pilihan Jawaban (A, B, C, D, E)</span>
                        <span class="text-[11px] text-slate-500 leading-snug">Urutan opsi A s.d E diacak posisinya secara dinamis.</span>
                    </div>
                </label>

            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('quizzes.show', $quiz) }}" class="px-6 py-3 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                Batal
            </a>

            <button type="submit" 
                    class="px-8 py-3.5 rounded-2xl bg-gradient-to-r from-pastel-sage-500 to-pastel-sage-600 hover:from-pastel-sage-600 hover:to-pastel-sage-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-pastel-sage-500/20 hover:shadow-lg transition-all flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>Simpan Perubahan Kuis</span>
            </button>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
    function quizEditor() {
        return {
            optionsCount: {{ old('options_count', $quiz->options_count ?: 5) }},
            letters: ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T'],
            shuffleQuestions: {{ old('shuffle_questions', $quiz->shuffle_questions) ? 'true' : 'false' }},
            shuffleOptions: {{ old('shuffle_options', $quiz->shuffle_options) ? 'true' : 'false' }},

            generateNewCode() {
                const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                let code = '';
                for (let i = 0; i < 6; i++) {
                    code += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                document.getElementById('code').value = code;
            }
        };
    }
</script>
@endpush
