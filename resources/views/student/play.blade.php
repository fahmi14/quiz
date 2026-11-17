@extends('layouts.student')

@section('title', 'Pengerjaan Kuis - ' . $quiz->title)

@section('content')
<div class="w-full max-w-4xl py-2" x-data="quizPlayer()">
    
    <!-- Top Sticky Bar: Nama, Progress, Timer -->
    <div class="glass-card rounded-2xl p-4 sm:p-5 shadow-soft border border-white/90 mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 sticky top-4 z-30">
        
        <!-- Left: Peserta & Info -->
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-pastel-sage-100 text-pastel-sage-600 flex items-center justify-center font-bold text-sm">
                {{ substr($participant->name, 0, 1) }}
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800 leading-tight">{{ $participant->name }}</h3>
                <span class="text-xs text-slate-500 font-medium">{{ $quiz->title }}</span>
            </div>
        </div>

        <!-- Center: Question Progress Tracker -->
        <div class="w-full sm:w-64">
            <div class="flex items-center justify-between text-xs mb-1.5 font-semibold">
                <span class="text-slate-500">Terjawab: <span class="text-slate-800 font-bold" x-text="answeredCount"></span> / {{ count($orderedQuestions) }}</span>
                <span class="text-pastel-sage-600 font-bold" x-text="progressPercentage + '%'"></span>
            </div>
            <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-pastel-sage-400 to-pastel-sky-500 rounded-full transition-all duration-300" 
                     :style="'width: ' + progressPercentage + '%'"></div>
            </div>
        </div>

        <!-- Right: Timer Calm Soft Pulse -->
        <div class="flex items-center gap-2 px-4 py-2 rounded-xl border"
             :class="isTimeWarning ? 'bg-rose-50 border-rose-200 text-rose-600 animate-pulse' : 'bg-pastel-sage-50 border-pastel-sage-200 text-pastel-sage-700'">
            <i data-lucide="clock" class="w-4 h-4"></i>
            <span class="font-mono font-bold text-sm" x-text="timerDisplay"></span>
        </div>
    </div>

    <!-- Main Question Box & Navigation -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        
        <!-- Main Quiz Arena (3 Cols) -->
        <div class="lg:col-span-3 space-y-6">
            
            <template x-for="(q, idx) in questions" :key="q.id">
                <div x-show="currentIndex === idx" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="glass-card rounded-3xl p-6 sm:p-8 shadow-soft-lg border border-white/90 relative">
                    
                    <!-- Question Header Tag -->
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-pastel-sage-100 text-pastel-sage-700 text-xs font-bold">
                            Soal No. <span x-text="idx + 1"></span> dari <span x-text="questions.length"></span>
                        </span>
                        <span class="text-xs font-semibold text-slate-400 flex items-center gap-1">
                            <i data-lucide="award" class="w-3.5 h-3.5 text-pastel-amber-500"></i>
                            Poin: <span x-text="q.points"></span>
                        </span>
                    </div>

                    <!-- Question Body -->
                    <h2 class="text-base sm:text-lg font-medium text-slate-800 leading-relaxed mb-6 font-display" 
                        x-text="q.text"></h2>

                    <!-- Options list with interactive pastel feedback -->
                    <div class="space-y-3">
                        <template x-for="opt in q.options" :key="opt.original_key">
                            <button type="button" 
                                    @click="chooseOption(q.id, opt.original_key)"
                                    class="w-full text-left p-4 rounded-2xl border-2 transition-all duration-200 flex items-start gap-3.5 group relative"
                                    :class="q.saved_answer === opt.original_key 
                                        ? 'bg-pastel-sage-100/90 border-pastel-sage-500 shadow-sm text-slate-900 font-medium' 
                                        : 'bg-white/80 border-slate-200/80 hover:border-pastel-sage-300 hover:bg-pastel-sage-50/40 text-slate-700'">
                                
                                <!-- Option letter circle badge -->
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs shrink-0 transition-all"
                                     :class="q.saved_answer === opt.original_key 
                                         ? 'bg-pastel-sage-500 text-white shadow-sm' 
                                         : 'bg-slate-100 text-slate-500 group-hover:bg-pastel-sage-200 group-hover:text-slate-800'">
                                    <span x-text="opt.display_label"></span>
                                </div>

                                <!-- Option text -->
                                <div class="flex-1 text-sm pt-1 leading-snug" x-text="opt.text"></div>

                                <!-- Checked icon -->
                                <div x-show="q.saved_answer === opt.original_key" class="text-pastel-sage-600 pt-1 shrink-0">
                                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                                </div>
                            </button>
                        </template>
                    </div>

                    <!-- Bottom Nav buttons -->
                    <div class="flex items-center justify-between mt-8 pt-5 border-t border-slate-100">
                        <button type="button" 
                                @click="prevQuestion()"
                                :disabled="currentIndex === 0"
                                class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2 transition-all">
                            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                            Sebelumnya
                        </button>

                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    x-show="currentIndex < questions.length - 1"
                                    @click="nextQuestion()"
                                    class="px-5 py-2.5 rounded-xl bg-pastel-sage-500 hover:bg-pastel-sage-600 text-white text-xs font-semibold flex items-center gap-2 shadow-sm transition-all">
                                Selanjutnya
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </button>

                            <button type="button" 
                                    x-show="currentIndex === questions.length - 1"
                                    @click="confirmFinish()"
                                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white text-xs font-semibold flex items-center gap-2 shadow-sm transition-all">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                Selesai & Kumpulkan
                            </button>
                        </div>
                    </div>

                </div>
            </template>

        </div>

        <!-- Sidebar Question Navigator (1 Col) -->
        <div class="lg:col-span-1">
            <div class="glass-card rounded-3xl p-5 shadow-soft border border-white/90 sticky top-28">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center justify-between">
                    <span>Nomor Soal</span>
                    <span class="text-[10px] text-pastel-sage-600 font-semibold" x-text="answeredCount + '/' + questions.length + ' Selesai'"></span>
                </h4>

                <!-- Grid buttons -->
                <div class="grid grid-cols-4 sm:grid-cols-5 lg:grid-cols-4 gap-2">
                    <template x-for="(q, idx) in questions" :key="q.id">
                        <button type="button" 
                                @click="jumpToQuestion(idx)"
                                class="h-10 rounded-xl font-bold text-xs flex items-center justify-center transition-all relative border"
                                :class="{
                                    'ring-2 ring-pastel-sage-500 ring-offset-2 scale-105 z-10': currentIndex === idx,
                                    'bg-pastel-sage-500 text-white border-pastel-sage-600 shadow-sm': q.saved_answer,
                                    'bg-white text-slate-600 border-slate-200 hover:border-pastel-sage-300': !q.saved_answer
                                }">
                            <span x-text="idx + 1"></span>
                        </button>
                    </template>
                </div>

                <!-- Legend -->
                <div class="mt-5 pt-4 border-t border-slate-100 space-y-1.5 text-[11px] text-slate-500">
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-md bg-pastel-sage-500 border border-pastel-sage-600 inline-block"></span>
                        <span>Sudah dijawab</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-md bg-white border border-slate-300 inline-block"></span>
                        <span>Belum dijawab</span>
                    </div>
                </div>

                <!-- Finish Early Button -->
                <div class="mt-6 pt-4 border-t border-slate-100">
                    <button type="button" 
                            @click="confirmFinish()"
                            class="w-full py-2.5 px-4 rounded-xl border border-rose-200 bg-rose-50/70 hover:bg-rose-100 text-rose-700 text-xs font-semibold flex items-center justify-center gap-2 transition-colors">
                        <i data-lucide="check-square" class="w-3.5 h-3.5"></i>
                        Kumpulkan Kuis
                    </button>
                </div>

            </div>
        </div>

    </div>

    <!-- Hidden Form for Finish -->
    <form id="finish-form" action="{{ route('student.finish', $token) }}" method="POST" class="hidden">
        @csrf
    </form>

</div>
@endsection

@push('scripts')
<script>
    function quizPlayer() {
        return {
            currentIndex: 0,
            remainingSeconds: {{ $remainingSeconds }},
            timerDisplay: '00:00',
            isTimeWarning: false,
            questions: @json($orderedQuestions),
            token: '{{ $token }}',
            csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            init() {
                this.updateTimerDisplay();
                this.startTimer();
                this.$nextTick(() => lucide.createIcons());
            },

            get answeredCount() {
                return this.questions.filter(q => q.saved_answer !== null && q.saved_answer !== undefined).length;
            },

            get progressPercentage() {
                if (this.questions.length === 0) return 0;
                return Math.round((this.answeredCount / this.questions.length) * 100);
            },

            startTimer() {
                const timerInterval = setInterval(() => {
                    if (this.remainingSeconds <= 0) {
                        clearInterval(timerInterval);
                        StudentDialog.alert({
                            title: 'Waktu Habis!',
                            message: 'Waktu pengerjaan telah selesai. Jawaban Anda akan otomatis dikumpulkan.',
                            onOk: () => document.getElementById('finish-form').submit()
                        });
                        return;
                    }

                    this.remainingSeconds--;
                    this.updateTimerDisplay();

                    if (this.remainingSeconds <= 180) { // 3 menit terakhir
                        this.isTimeWarning = true;
                    }
                }, 1000);
            },

            updateTimerDisplay() {
                const mins = Math.floor(this.remainingSeconds / 60);
                const secs = this.remainingSeconds % 60;
                this.timerDisplay = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            },

            chooseOption(questionId, optionKey) {
                const q = this.questions.find(item => item.id === questionId);
                if (!q) return;

                q.saved_answer = optionKey;
                this.$nextTick(() => lucide.createIcons());

                // Autosave via AJAX
                fetch(`/pengerjaan/${this.token}/jawaban`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        question_id: questionId,
                        selected_option: optionKey
                    })
                }).catch(err => console.error('Error saving answer:', err));
            },

            nextQuestion() {
                if (this.currentIndex < this.questions.length - 1) {
                    this.currentIndex++;
                    this.$nextTick(() => lucide.createIcons());
                }
            },

            prevQuestion() {
                if (this.currentIndex > 0) {
                    this.currentIndex--;
                    this.$nextTick(() => lucide.createIcons());
                }
            },

            jumpToQuestion(index) {
                this.currentIndex = index;
                this.$nextTick(() => lucide.createIcons());
            },

            confirmFinish() {
                const unans = this.questions.length - this.answeredCount;
                let msg = 'Apakah Anda yakin ingin menyelesaikan kuis ini dan mengirimkan jawaban Anda?';
                if (unans > 0) {
                    msg = `Masih ada ${unans} butir soal yang belum Anda jawab. Yakin ingin mengumpulkan sekarang?`;
                }

                StudentDialog.confirm({
                    title: 'Kumpulkan Kuis?',
                    message: msg,
                    confirmText: 'Ya, Kumpulkan',
                    onConfirm: () => {
                        document.getElementById('finish-form').submit();
                    }
                });
            }
        };
    }
</script>
@endpush
