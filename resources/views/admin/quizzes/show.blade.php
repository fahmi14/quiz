@extends('layouts.admin')

@section('title', 'Detail Kuis - ' . $quiz->title)

@section('content')
<div class="space-y-6 animate-slide-up" x-data="{ 
    showAddQuestionModal: false, 
    showEditQuestionModal: false,
    optionsCount: {{ $quiz->options_count ?: 5 }},
    letters: ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T'],
    editQuestionData: {
        id: '',
        question_text: '',
        options: {},
        correct_answer: 'A',
        points: 10
    },
    openEditModal(q) {
        const opts = {};
        for (let i = 0; i < this.optionsCount; i++) {
            const letter = this.letters[i];
            const prop = 'option_' + letter.toLowerCase();
            if (q.options_data && q.options_data[letter] !== undefined) {
                opts[letter] = q.options_data[letter];
            } else if (q[prop] !== undefined) {
                opts[letter] = q[prop] || '';
            } else {
                opts[letter] = '';
            }
        }
        this.editQuestionData = {
            id: q.id,
            question_text: q.question_text,
            options: opts,
            correct_answer: (q.correct_answer || 'A').toUpperCase(),
            points: q.points || 10
        };
        this.showEditQuestionModal = true;
    }
}">
    
    <!-- Top Bar -->
    <div class="glass-card p-6 rounded-3xl shadow-soft flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2 flex-wrap">
                <a href="{{ route('quizzes.index') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 transition-colors">
                    &larr; Kembali ke Daftar
                </a>
                <span class="text-slate-300">&bull;</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-lg bg-pastel-sky-50 text-pastel-sky-600 border border-pastel-sky-200/60 font-mono font-bold text-xs">
                    KODE: {{ $quiz->code }}
                </span>

                <!-- Tombol Share Link Cepat di Halaman Detail -->
                <button type="button" 
                        onclick="copyShareLink('{{ route('student.join', $quiz->code) }}', '{{ addslashes($quiz->title) }}')"
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-pastel-sage-100/80 hover:bg-pastel-sage-200 text-pastel-sage-700 font-bold text-xs border border-pastel-sage-200 transition-all shadow-sm">
                    <i data-lucide="share-2" class="w-3.5 h-3.5 text-pastel-sage-600"></i>
                    <span>Salin Share Link Peserta</span>
                </button>
            </div>
            <h1 class="font-display font-black text-2xl text-slate-800 tracking-tight">{{ $quiz->title }}</h1>
            <p class="text-xs text-slate-500 mt-1">{{ $quiz->description ?: 'Tidak ada deskripsi tambahan.' }}</p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('quizzes.edit', $quiz) }}" 
               class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-2 shadow-sm transition-all">
                <i data-lucide="edit-3" class="w-4 h-4 text-pastel-amber-500"></i>
                Edit Pengaturan Kuis
            </a>

            <a href="{{ route('monitoring.index', ['quiz_id' => $quiz->id]) }}" 
               class="px-4 py-2.5 rounded-2xl bg-pastel-sky-50 text-pastel-sky-600 border border-pastel-sky-200 hover:bg-pastel-sky-100 text-xs font-semibold flex items-center gap-2 transition-all">
                <i data-lucide="radio" class="w-4 h-4"></i>
                Monitor Peserta
            </a>

            <a href="{{ route('results.index', ['quiz_id' => $quiz->id]) }}" 
               class="px-4 py-2.5 rounded-2xl bg-pastel-lavender-50 text-pastel-lavender-500 border border-pastel-lavender-200 hover:bg-pastel-lavender-100 text-xs font-semibold flex items-center gap-2 transition-all">
                <i data-lucide="award" class="w-4 h-4"></i>
                Hasil Kuis
            </a>

            <button type="button" @click="showAddQuestionModal = true"
                    class="px-4 py-2.5 rounded-2xl bg-pastel-sage-500 hover:bg-pastel-sage-600 text-white text-xs font-semibold shadow-sm flex items-center gap-2 transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Tambah Soal Manual
            </button>
        </div>
    </div>

    <!-- Parameter Status & Ringkasan -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="glass-card p-4 rounded-2xl shadow-soft text-center">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Durasi Pengerjaan</span>
            <span class="font-display font-bold text-slate-700 text-base mt-1 flex items-center justify-center gap-1">
                <i data-lucide="clock" class="w-3.5 h-3.5 text-pastel-sage-500"></i>
                {{ $quiz->duration_minutes }} Menit
            </span>
        </div>

        <div class="glass-card p-4 rounded-2xl shadow-soft text-center">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Jumlah Butir Soal</span>
            <span class="font-display font-bold text-slate-700 text-base mt-1 flex items-center justify-center gap-1">
                <i data-lucide="help-circle" class="w-3.5 h-3.5 text-pastel-sky-500"></i>
                {{ $quiz->questions->count() }} Soal
            </span>
        </div>

        <div class="glass-card p-4 rounded-2xl shadow-soft text-center">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Acak Soal / Opsi</span>
            <span class="font-display font-bold text-slate-700 text-xs mt-1.5 flex items-center justify-center gap-1">
                <span class="px-2 py-0.5 rounded-md {{ $quiz->shuffle_questions ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">
                    Soal: {{ $quiz->shuffle_questions ? 'Ya' : 'Urut' }}
                </span>
                <span class="px-2 py-0.5 rounded-md {{ $quiz->shuffle_options ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">
                    Opsi: {{ $quiz->shuffle_options ? 'Ya' : 'Urut' }}
                </span>
            </span>
        </div>

        <div class="glass-card p-4 rounded-2xl shadow-soft text-center">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Peserta Ikut</span>
            <span class="font-display font-bold text-pastel-sage-600 text-base mt-1 flex items-center justify-center gap-1">
                <i data-lucide="users" class="w-3.5 h-3.5"></i>
                {{ $quiz->participants->count() }} Orang
            </span>
        </div>
    </div>

    <!-- Daftar Soal -->
    <div class="glass-card rounded-3xl shadow-soft border border-white/90 p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-display font-bold text-base text-slate-800 flex items-center gap-2">
                <i data-lucide="list-ordered" class="w-4 h-4 text-pastel-sage-500"></i>
                Daftar Butir Soal ({{ $quiz->questions->count() }})
            </h3>
            <span class="text-xs text-slate-400">Total Poin: {{ $quiz->questions->sum('points') }}</span>
        </div>

        @if($quiz->questions->isEmpty())
            <div class="py-12 text-center text-slate-400">
                <i data-lucide="help-circle" class="w-12 h-12 mx-auto text-slate-300 mb-2"></i>
                <p class="text-xs">Belum ada soal pada kuis ini. Silakan klik "Tambah Soal Manual" di kanan atas.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($quiz->questions as $idx => $q)
                    <div class="p-5 rounded-2xl bg-white/80 border border-slate-200/70 shadow-sm hover:border-pastel-sage-300 transition-all">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex items-start gap-2.5">
                                <span class="w-7 h-7 rounded-xl bg-pastel-sage-100 text-pastel-sage-700 font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">
                                    {{ $idx + 1 }}
                                </span>
                                <h4 class="text-sm font-semibold text-slate-800 leading-relaxed">{{ $q->question_text }}</h4>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <span class="px-2.5 py-0.5 rounded-lg bg-pastel-amber-50 text-pastel-amber-700 border border-pastel-amber-200 text-[11px] font-bold">
                                    {{ $q->points }} Poin
                                </span>

                                <button type="button" @click="openEditModal({{ json_encode($q) }})" 
                                        class="p-1 text-slate-400 hover:text-pastel-sage-600 transition-colors" title="Edit Butir Soal">
                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                </button>

                                <form id="delete-question-{{ $q->id }}" action="{{ route('quizzes.questions.destroy', [$quiz, $q]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" 
                                            onclick="PastelDialog.confirmDelete({
                                                title: 'Hapus Butir Soal #{{ $idx + 1 }}?',
                                                message: 'Apakah Anda yakin ingin menghapus butir soal nomor {{ $idx + 1 }} ini?',
                                                onConfirm: () => document.getElementById('delete-question-{{ $q->id }}').submit()
                                            })"
                                            class="p-1 text-slate-300 hover:text-rose-500 transition-colors" title="Hapus Soal">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Options grid (Dinamis 2 - 20 Pilihan) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 text-xs pt-2 pl-9">
                            @foreach($q->getAllOptions() as $key => $optVal)
                                @if(!empty($optVal))
                                    <div class="p-2.5 rounded-xl border flex items-center gap-2 {{ strtoupper($q->correct_answer) === $key ? 'bg-emerald-50/80 border-emerald-300 text-emerald-900 font-medium' : 'bg-slate-50/50 border-slate-200/60 text-slate-600' }}">
                                        <span class="w-5 h-5 rounded-md text-[10px] font-bold flex items-center justify-center {{ strtoupper($q->correct_answer) === $key ? 'bg-emerald-500 text-white' : 'bg-white text-slate-400 border border-slate-200' }}">
                                            {{ $key }}
                                        </span>
                                        <span class="truncate flex-1">{{ $optVal }}</span>
                                        @if(strtoupper($q->correct_answer) === $key)
                                            <span class="text-[10px] text-emerald-600 font-bold uppercase tracking-wider shrink-0 flex items-center gap-0.5">
                                                <i data-lucide="check" class="w-3 h-3"></i> Kunci
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Modal Tambah Soal Manual (Dinamis 2 - 20 Pilihan) -->
    <div x-show="showAddQuestionModal" 
         x-transition.opacity 
         class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
        
        <div class="glass-card rounded-3xl p-6 sm:p-8 max-w-xl w-full shadow-soft-lg border border-white max-h-[90vh] overflow-y-auto"
             @click.away="showAddQuestionModal = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div>
                    <h3 class="font-display font-bold text-base text-slate-800">Tambah Butir Soal Baru</h3>
                    <p class="text-[11px] text-pastel-sage-600 font-medium">Format kuis ini: <span x-text="optionsCount"></span> Pilihan Ganda (A s/d <span x-text="letters[optionsCount - 1]"></span>)</p>
                </div>
                <button type="button" @click="showAddQuestionModal = false" class="text-slate-400 hover:text-slate-700">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('quizzes.questions.store', $quiz) }}" method="POST" class="space-y-3.5">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Pertanyaan <span class="text-rose-500">*</span></label>
                    <textarea name="question_text" rows="2" required placeholder="Tuliskan teks pertanyaan soal..." class="w-full text-xs p-3 rounded-xl border border-slate-200"></textarea>
                </div>

                <!-- Input Pilihan Ganda Dinamis -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-60 overflow-y-auto p-1">
                    <template x-for="idx in optionsCount" :key="idx">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">
                                Pilihan <span x-text="letters[idx - 1]"></span>
                                <span class="text-rose-500" x-show="idx <= 2">*</span>
                            </label>
                            <input type="text" 
                                   :name="'option_' + letters[idx - 1].toLowerCase()" 
                                   :required="idx <= 2"
                                   :placeholder="'Teks opsi ' + letters[idx - 1]"
                                   class="w-full text-xs p-2.5 rounded-xl border border-slate-200 focus:border-pastel-sage-400 focus:outline-none">
                        </div>
                    </template>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Kunci Jawaban <span class="text-rose-500">*</span></label>
                        <select name="correct_answer" required class="w-full text-xs p-2.5 rounded-xl border border-slate-200 font-bold bg-white">
                            <template x-for="idx in optionsCount" :key="idx">
                                <option :value="letters[idx - 1]" x-text="'Pilihan ' + letters[idx - 1]"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Bobot Poin</label>
                        <input type="number" name="points" value="10" min="1" required class="w-full text-xs p-2.5 rounded-xl border border-slate-200 font-bold">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="showAddQuestionModal = false" class="px-4 py-2 rounded-xl border text-xs text-slate-600">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-pastel-sage-500 text-white text-xs font-semibold">Simpan Soal</button>
                </div>
            </form>

        </div>
    </div>

    <!-- Modal Edit Soal (Dinamis 2 - 20 Pilihan) -->
    <div x-show="showEditQuestionModal" 
         x-transition.opacity 
         class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
        
        <div class="glass-card rounded-3xl p-6 sm:p-8 max-w-xl w-full shadow-soft-lg border border-white max-h-[90vh] overflow-y-auto"
             @click.away="showEditQuestionModal = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div>
                    <h3 class="font-display font-bold text-base text-slate-800">Edit Butir Soal</h3>
                    <p class="text-[11px] text-pastel-sage-600 font-medium">Format kuis: <span x-text="optionsCount"></span> Pilihan (A s/d <span x-text="letters[optionsCount - 1]"></span>)</p>
                </div>
                <button type="button" @click="showEditQuestionModal = false" class="text-slate-400 hover:text-slate-700">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'/admin/quizzes/{{ $quiz->id }}/questions/' + editQuestionData.id" method="POST" class="space-y-3.5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Pertanyaan <span class="text-rose-500">*</span></label>
                    <textarea name="question_text" rows="2" required x-model="editQuestionData.question_text" class="w-full text-xs p-3 rounded-xl border border-slate-200"></textarea>
                </div>

                <!-- Input Edit Pilihan Ganda Dinamis -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-60 overflow-y-auto p-1">
                    <template x-for="idx in optionsCount" :key="idx">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">
                                Pilihan <span x-text="letters[idx - 1]"></span>
                                <span class="text-rose-500" x-show="idx <= 2">*</span>
                            </label>
                            <input type="text" 
                                   :name="'option_' + letters[idx - 1].toLowerCase()" 
                                   :required="idx <= 2"
                                   x-model="editQuestionData.options[letters[idx - 1]]"
                                   :placeholder="'Teks opsi ' + letters[idx - 1]"
                                   class="w-full text-xs p-2.5 rounded-xl border border-slate-200 focus:border-pastel-sage-400 focus:outline-none">
                        </div>
                    </template>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Kunci Jawaban <span class="text-rose-500">*</span></label>
                        <select name="correct_answer" required x-model="editQuestionData.correct_answer" class="w-full text-xs p-2.5 rounded-xl border border-slate-200 font-bold bg-white">
                            <template x-for="idx in optionsCount" :key="idx">
                                <option :value="letters[idx - 1]" x-text="'Pilihan ' + letters[idx - 1]"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Bobot Poin</label>
                        <input type="number" name="points" x-model="editQuestionData.points" min="1" required class="w-full text-xs p-2.5 rounded-xl border border-slate-200 font-bold">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="showEditQuestionModal = false" class="px-4 py-2 rounded-xl border text-xs text-slate-600">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-pastel-sage-500 text-white text-xs font-semibold">Simpan Perubahan Soal</button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function copyShareLink(url, title) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).then(() => {
                PastelDialog.alert({
                    title: 'Link Berhasil Disalin! ✨',
                    message: `Link langsung untuk kuis "${title}" telah disalin ke clipboard:\n\n${url}\n\nPeserta dapat langsung masuk & mengisi nama tanpa harus mengetik kode kuis.`,
                    type: 'success'
                });
            }).catch(() => fallbackCopy(url, title));
        } else {
            fallbackCopy(url, title);
        }
    }

    function fallbackCopy(url, title) {
        const tempInput = document.createElement('input');
        tempInput.value = url;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);

        PastelDialog.alert({
            title: 'Link Berhasil Disalin! ✨',
            message: `Link langsung untuk kuis "${title}" telah disalin:\n\n${url}\n\nPeserta dapat langsung masuk tanpa input kode.`,
            type: 'success'
        });
    }
</script>
@endpush
