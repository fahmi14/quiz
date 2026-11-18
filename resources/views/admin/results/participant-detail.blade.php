@extends('layouts.admin')

@section('title', 'Review Jawaban - ' . $participant->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6 animate-slide-up">
    
    <!-- Top Bar -->
    <div class="glass-card p-6 rounded-3xl shadow-soft flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('results.index', ['quiz_id' => $quiz->id]) }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 transition-colors">
                    &larr; Kembali ke Hasil Kuis
                </a>
                <span class="text-slate-300">&bull;</span>
                <span class="text-xs text-slate-500 font-medium">{{ $quiz->title }}</span>
            </div>
            <h1 class="font-display font-black text-2xl text-slate-800 tracking-tight">Review Hasil: {{ $participant->name }}</h1>
            <p class="text-xs text-slate-400 mt-0.5">Sesi Kuis: {{ $quiz->code }} &bull; Mulai: {{ $participant->started_at ? $participant->started_at->format('d M Y H:i:s') : '-' }}</p>
        </div>

        <div class="flex items-center gap-4">
            <div class="px-5 py-3 rounded-2xl bg-gradient-to-tr from-pastel-sage-50 to-pastel-sky-50 border border-pastel-sage-200 text-center">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Skor Akhir</span>
                <span class="font-display font-extrabold text-2xl text-pastel-sage-600">{{ round($participant->score, 1) }}</span>
            </div>
        </div>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-3 gap-4">
        <div class="glass-card p-4 rounded-2xl text-center">
            <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Benar</span>
            <span class="font-display font-bold text-emerald-600 text-lg mt-0.5">{{ $participant->total_correct }}</span>
        </div>
        <div class="glass-card p-4 rounded-2xl text-center">
            <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Terjawab</span>
            <span class="font-display font-bold text-slate-700 text-lg mt-0.5">{{ $participant->total_answered }}</span>
        </div>
        <div class="glass-card p-4 rounded-2xl text-center">
            <span class="text-[10px] uppercase font-bold text-slate-400 block">Jumlah Soal</span>
            <span class="font-display font-bold text-slate-700 text-lg mt-0.5">{{ $participant->total_questions ?: $quiz->questions->count() }}</span>
        </div>
    </div>

    <!-- Questions Detail Breakdown -->
    <div class="space-y-4">
        @php
            $answersMap = $participant->answers->keyBy('question_id');
        @endphp

        @foreach($quiz->questions as $idx => $q)
            @php
                $ans = $answersMap[$q->id] ?? null;
                $isCorrect = $ans && $ans->is_correct;
                $selected = $ans ? $ans->selected_option : null;
            @endphp
            <div class="glass-card p-6 rounded-3xl border {{ $isCorrect ? 'border-emerald-200 bg-white/90' : ($selected ? 'border-rose-200 bg-rose-50/20' : 'border-slate-200 bg-slate-50/40') }}">
                
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-start gap-2.5">
                        <span class="w-7 h-7 rounded-xl font-bold text-xs flex items-center justify-center shrink-0 mt-0.5 {{ $isCorrect ? 'bg-emerald-100 text-emerald-700' : ($selected ? 'bg-rose-100 text-rose-700' : 'bg-slate-200 text-slate-600') }}">
                            {{ $idx + 1 }}
                        </span>
                        <div>
                            <h4 class="text-sm font-semibold text-slate-800 leading-relaxed">{{ $q->question_text }}</h4>
                        </div>
                    </div>

                    <div class="shrink-0">
                        @if($isCorrect)
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i> Benar (+{{ $q->points }})
                            </span>
                        @elseif($selected)
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i> Salah (0)
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500">
                                Tidak Dijawab
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Options (Dinamis 2 - 20 Pilihan) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 text-xs pt-2 pl-9">
                    @foreach($q->getAllOptions() as $k => $val)
                        @if(!empty($val))
                            @php
                                $isChosen = ($selected === $k);
                                $isTheCorrectKey = (strtoupper($q->correct_answer) === $k);
                            @endphp
                            <div class="p-3 rounded-xl border flex items-center gap-2.5
                                {{ $isTheCorrectKey ? 'bg-emerald-50 border-emerald-300 font-semibold text-emerald-900' : '' }}
                                {{ $isChosen && !$isTheCorrectKey ? 'bg-rose-50 border-rose-300 text-rose-800 font-medium' : '' }}
                                {{ !$isChosen && !$isTheCorrectKey ? 'bg-white/80 border-slate-200/70 text-slate-600' : '' }}">
                                
                                <span class="w-5 h-5 rounded-md text-[10px] font-bold flex items-center justify-center
                                    {{ $isTheCorrectKey ? 'bg-emerald-500 text-white' : '' }}
                                    {{ $isChosen && !$isTheCorrectKey ? 'bg-rose-500 text-white' : '' }}
                                    {{ !$isChosen && !$isTheCorrectKey ? 'bg-slate-100 text-slate-500' : '' }}">
                                    {{ $k }}
                                </span>

                                <span class="truncate flex-1">{{ $val }}</span>

                                @if($isTheCorrectKey)
                                    <span class="text-[10px] uppercase font-bold text-emerald-600 shrink-0">Kunci Benar</span>
                                @endif
                                @if($isChosen && !$isTheCorrectKey)
                                    <span class="text-[10px] uppercase font-bold text-rose-500 shrink-0">Pilihan Peserta</span>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>

            </div>
        @endforeach
    </div>

</div>
@endsection
