@extends('layouts.admin')

@section('title', 'Hasil Kuis & Rekapitulasi')

@section('content')
<div class="space-y-6 animate-slide-up">
    
    <!-- Header with Selector & Export (Sesuai Diagram Alur) -->
    <div class="glass-card p-6 rounded-3xl shadow-soft flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-pastel-lavender-100 text-pastel-lavender-600 text-xs font-semibold mb-2">
                <i data-lucide="award" class="w-3.5 h-3.5"></i>
                Rekap & Evaluasi Nilai
            </div>
            <h1 class="font-display font-black text-2xl text-slate-800 tracking-tight">Hasil Kuis & Export Excel</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Alur diagram: Pilih kuis, export Excel berformat rapi, dan analisa tabel hasil seluruh peserta.</p>
        </div>

        @if($selectedQuiz)
            <div class="flex items-center gap-3">
                <a href="{{ route('results.export', $selectedQuiz) }}" 
                   class="px-5 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 hover:shadow-lg transition-all flex items-center gap-2">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                    <span>Export Hasil ke Excel (.xlsx)</span>
                </a>
            </div>
        @endif
    </div>

    <!-- Filter & Quiz Selector Card -->
    <div class="glass-card p-5 rounded-3xl shadow-soft flex flex-col sm:flex-row items-center justify-between gap-4">
        <form action="{{ route('results.index') }}" method="GET" class="w-full sm:w-auto flex-1 flex flex-wrap items-center gap-3">
            
            <!-- Pilih Kuis -->
            <div class="w-full sm:w-64">
                <select name="quiz_id" onchange="this.form.submit()" class="w-full text-xs font-semibold p-2.5 rounded-xl border border-slate-200 bg-white">
                    @foreach($quizzes as $q)
                        <option value="{{ $q->id }}" {{ $selectedQuiz && $selectedQuiz->id === $q->id ? 'selected' : '' }}>
                            {{ $q->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Search Nama -->
            <div class="relative flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama peserta..." class="w-full text-xs p-2.5 pl-8 rounded-xl border border-slate-200 bg-white">
                <i data-lucide="search" class="w-3.5 h-3.5 absolute left-2.5 top-3 text-slate-400"></i>
            </div>

            <!-- Filter Status -->
            <div>
                <select name="status" onchange="this.form.submit()" class="text-xs p-2.5 rounded-xl border border-slate-200 bg-white">
                    <option value="">Semua Status</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Sedang Mengerjakan</option>
                </select>
            </div>

            <button type="submit" class="px-4 py-2.5 rounded-xl bg-pastel-sage-500 text-white text-xs font-semibold">
                Filter
            </button>
        </form>
    </div>

    @if(!$selectedQuiz)
        <div class="glass-card p-12 rounded-3xl text-center text-slate-400">
            <i data-lucide="award" class="w-12 h-12 mx-auto text-slate-300 mb-2"></i>
            <h4 class="font-bold text-slate-700">Belum Ada Kuis Terpilih</h4>
        </div>
    @else
        <!-- Stats Summary -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            
            <div class="glass-card p-5 rounded-3xl border border-white/90 shadow-soft">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Total Peserta</span>
                <div class="font-display font-extrabold text-2xl text-slate-800">{{ $stats['total'] }}</div>
                <span class="text-[11px] text-pastel-sky-500 font-medium">Terdaftar di sesi ini</span>
            </div>

            <div class="glass-card p-5 rounded-3xl border border-white/90 shadow-soft">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Selesai Menjawab</span>
                <div class="font-display font-extrabold text-2xl text-emerald-600">{{ $stats['completed'] }}</div>
                <span class="text-[11px] text-emerald-600 font-medium">Sudah kumpul jawaban</span>
            </div>

            <div class="glass-card p-5 rounded-3xl border border-white/90 shadow-soft">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Rata-Rata Nilai</span>
                <div class="font-display font-extrabold text-2xl text-slate-800">{{ $stats['avg_score'] }}</div>
                <span class="text-[11px] text-pastel-lavender-500 font-medium">Dari seluruh peserta</span>
            </div>

            <div class="glass-card p-5 rounded-3xl border border-white/90 shadow-soft">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Nilai Memuaskan (>=70)</span>
                <div class="font-display font-extrabold text-2xl text-pastel-sage-600">{{ $stats['pass_count'] }}</div>
                <span class="text-[11px] text-pastel-sage-600 font-medium">Peserta kategori tinggi</span>
            </div>

        </div>

        <!-- Table Card Tampilan Hasil (Sesuai Diagram Alur) -->
        <div class="glass-card rounded-3xl shadow-soft border border-white/90 overflow-hidden">
            
            <div class="p-6 border-b border-slate-100 flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h3 class="font-display font-bold text-base text-slate-800">Tabel Rekapitulasi Hasil: {{ $selectedQuiz->title }}</h3>
                    <p class="text-xs text-slate-400">Kode Kuis: <span class="font-mono font-bold text-slate-700">{{ $selectedQuiz->code }}</span></p>
                </div>

                <a href="{{ route('results.export', $selectedQuiz) }}" 
                   class="px-3.5 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                    Export (.xlsx)
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/70 border-b border-slate-100 text-[11px] uppercase tracking-wider text-slate-400 font-bold">
                        <tr>
                            <th class="px-6 py-4 text-center w-16">Peringkat</th>
                            <th class="px-6 py-4">Nama Peserta</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Skor Akhir</th>
                            <th class="px-6 py-4 text-center">Benar / Terjawab</th>
                            <th class="px-6 py-4 text-center">Waktu Mulai</th>
                            <th class="px-6 py-4 text-center">Waktu Selesai</th>
                            <th class="px-6 py-4 text-center">Durasi</th>
                            <th class="px-6 py-4 text-right">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($participants as $idx => $p)
                            <tr class="hover:bg-pastel-sage-50/40 transition-colors">
                                <td class="px-6 py-4 text-center font-mono font-bold">
                                    @php $rank = $participants->firstItem() + $idx; @endphp
                                    @if($rank === 1)
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-amber-100 text-amber-800 text-xs font-bold">1</span>
                                    @elseif($rank === 2)
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold">2</span>
                                    @elseif($rank === 3)
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-orange-100 text-orange-800 text-xs font-bold">3</span>
                                    @else
                                        <span class="text-slate-400 text-xs">#{{ $rank }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800 text-sm">{{ $p->name }}</div>
                                    @if($p->identifier)
                                        <div class="text-[11px] text-slate-400">{{ $p->identifier }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($p->status === 'completed')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Selesai
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            Mengerjakan
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-display font-extrabold text-base text-slate-800">{{ round($p->score, 1) }}</span>
                                </td>
                                <td class="px-6 py-4 text-center font-medium">
                                    <span class="text-emerald-600 font-bold">{{ $p->total_correct }}</span> / {{ $p->total_answered }}
                                    <span class="text-slate-400 text-[11px]">({{ $p->total_questions ?: $selectedQuiz->questions->count() }} soal)</span>
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-[11px] text-slate-500">
                                    {{ $p->started_at ? $p->started_at->format('d/m/y H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-[11px] text-slate-500">
                                    {{ $p->finished_at ? $p->finished_at->format('d/m/y H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-center text-xs text-slate-600">
                                    @if($p->started_at && $p->finished_at)
                                        {{ $p->started_at->diff($p->finished_at)->format('%im %ss') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('results.participant', [$selectedQuiz, $p]) }}" 
                                       class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold inline-flex items-center gap-1 transition-colors">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        Review
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-8 text-center text-slate-400 text-xs">
                                    Tidak ada data peserta yang cocok dengan kriteria pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($participants->hasPages())
                <div class="p-5 border-t border-slate-100">
                    {{ $participants->links() }}
                </div>
            @endif

        </div>
    @endif

</div>
@endsection
