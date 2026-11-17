@extends('layouts.admin')

@section('title', 'Daftar & Manajemen Kuis')

@section('content')
<div class="space-y-6 animate-slide-up">
    
    <!-- Hero / Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 glass-card p-6 rounded-3xl shadow-soft">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-pastel-sage-100 text-pastel-sage-700 text-xs font-semibold mb-2">
                <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                Pusat Kontrol Admin
            </div>
            <h1 class="font-display font-black text-2xl text-slate-800 tracking-tight">Manajemen Kuis Interaktif</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Buat kuis baru, import template Excel, atur masa aktif, dan monitor peserta secara live.</p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('quizzes.download-template') }}" 
               class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-300 shadow-sm transition-all flex items-center gap-2">
                <i data-lucide="download" class="w-4 h-4 text-pastel-sky-500"></i>
                <span>Unduh Template Excel</span>
            </a>

            <a href="{{ route('quizzes.create') }}" 
               class="px-5 py-2.5 rounded-2xl bg-gradient-to-r from-pastel-sage-500 to-pastel-sage-600 hover:from-pastel-sage-600 hover:to-pastel-sage-700 text-white text-xs font-semibold shadow-md shadow-pastel-sage-500/20 hover:shadow-lg transition-all flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Buat Kuis Baru</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards (Pastel Tone) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        
        <div class="glass-card p-5 rounded-3xl border border-white/90 shadow-soft">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Kuis</span>
                <div class="w-9 h-9 rounded-2xl bg-pastel-sage-100 text-pastel-sage-600 flex items-center justify-center">
                    <i data-lucide="book-open" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="font-display font-extrabold text-2xl text-slate-800">{{ $totalQuizzes }}</div>
            <span class="text-[11px] text-pastel-sage-600 font-medium">Tersedia di sistem</span>
        </div>

        <div class="glass-card p-5 rounded-3xl border border-white/90 shadow-soft">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kuis Aktif</span>
                <div class="w-9 h-9 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="font-display font-extrabold text-2xl text-emerald-700">{{ $activeQuizzes }}</div>
            <span class="text-[11px] text-emerald-600 font-medium">Bisa dikerjakan peserta</span>
        </div>

        <div class="glass-card p-5 rounded-3xl border border-white/90 shadow-soft">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Peserta</span>
                <div class="w-9 h-9 rounded-2xl bg-pastel-sky-100 text-pastel-sky-500 flex items-center justify-center">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="font-display font-extrabold text-2xl text-slate-800">{{ $totalParticipants }}</div>
            <span class="text-[11px] text-pastel-sky-500 font-medium">Peserta terdaftar</span>
        </div>

        <div class="glass-card p-5 rounded-3xl border border-white/90 shadow-soft">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Selesai Kuis</span>
                <div class="w-9 h-9 rounded-2xl bg-pastel-lavender-100 text-pastel-lavender-500 flex items-center justify-center">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="font-display font-extrabold text-2xl text-slate-800">{{ $completedParticipants }}</div>
            <span class="text-[11px] text-pastel-lavender-500 font-medium">Selesai dievaluasi</span>
        </div>

    </div>

    <!-- Quiz Table Card -->
    <div class="glass-card rounded-3xl shadow-soft border border-white/90 overflow-hidden">
        
        <div class="p-6 border-b border-slate-100 flex items-center justify-between flex-wrap gap-4">
            <div>
                <h3 class="font-display font-bold text-base text-slate-800">Daftar Semua Kuis</h3>
                <p class="text-xs text-slate-500">Klik kuis untuk melihat detail butir soal atau kelola peserta</p>
            </div>
            <span class="text-xs text-slate-400 font-medium">Total: {{ $quizzes->total() }} kuis</span>
        </div>

        @if($quizzes->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 mx-auto rounded-3xl bg-pastel-sage-100 text-pastel-sage-500 flex items-center justify-center mb-3">
                    <i data-lucide="inbox" class="w-8 h-8"></i>
                </div>
                <h4 class="font-display font-bold text-base text-slate-700">Belum Ada Kuis yang Dibuat</h4>
                <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1 mb-5">Mulai dengan membuat kuis baru atau import dari template Excel.</p>
                <a href="{{ route('quizzes.create') }}" class="px-5 py-2.5 rounded-2xl bg-pastel-sage-500 text-white text-xs font-semibold inline-flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i> Buat Kuis Sekarang
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/70 border-b border-slate-100 text-[11px] uppercase tracking-wider text-slate-400 font-bold">
                        <tr>
                            <th class="px-6 py-4">Kuis & Kode</th>
                            <th class="px-6 py-4">Masa Aktif & Durasi</th>
                            <th class="px-6 py-4 text-center">Soal</th>
                            <th class="px-6 py-4 text-center">Peserta</th>
                            <th class="px-6 py-4 text-center">Opsi Acak</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($quizzes as $quiz)
                            <tr class="hover:bg-pastel-sage-50/40 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800 text-sm mb-1 hover:text-pastel-sage-600 transition-colors">
                                        <a href="{{ route('quizzes.show', $quiz) }}">{{ $quiz->title }}</a>
                                    </div>
                                    <div class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-pastel-sky-50 text-pastel-sky-600 border border-pastel-sky-200/60 font-mono font-bold text-[11px]">
                                        <i data-lucide="key" class="w-3 h-3"></i>
                                        {{ $quiz->code }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1.5 text-slate-700 font-medium">
                                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                                        @if($quiz->expires_at)
                                            s.d {{ $quiz->expires_at->format('d M Y H:i') }}
                                        @else
                                            <span class="text-slate-400">Tidak terbatas</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        Durasi: {{ $quiz->duration_minutes }} menit
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs inline-flex items-center gap-1">
                                        <i data-lucide="help-circle" class="w-3 h-3 text-slate-400"></i>
                                        {{ $quiz->questions_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 rounded-xl bg-pastel-sky-100 text-pastel-sky-600 font-bold text-xs inline-flex items-center gap-1">
                                        <i data-lucide="users" class="w-3 h-3"></i>
                                        {{ $quiz->participants_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1 text-[11px]">
                                        <span class="px-2 py-0.5 rounded-md {{ $quiz->shuffle_questions ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-400' }}" title="Acak Soal">
                                            Soal: {{ $quiz->shuffle_questions ? 'Ya' : 'Tidak' }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded-md {{ $quiz->shuffle_options ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-400' }}" title="Acak Pilihan">
                                            Opsi: {{ $quiz->shuffle_options ? 'Ya' : 'Tidak' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($quiz->isExpired())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-200">
                                            Kedaluwarsa
                                        </span>
                                    @elseif($quiz->is_active)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('monitoring.index', ['quiz_id' => $quiz->id]) }}" 
                                           class="p-2 rounded-xl bg-pastel-sky-50 text-pastel-sky-600 hover:bg-pastel-sky-100 transition-colors" title="Monitor Live">
                                            <i data-lucide="radio" class="w-4 h-4"></i>
                                        </a>

                                        <a href="{{ route('results.index', ['quiz_id' => $quiz->id]) }}" 
                                           class="p-2 rounded-xl bg-pastel-lavender-50 text-pastel-lavender-500 hover:bg-pastel-lavender-100 transition-colors" title="Hasil & Export">
                                            <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                                        </a>

                                        <a href="{{ route('quizzes.edit', $quiz) }}" 
                                           class="p-2 rounded-xl bg-pastel-amber-50 text-pastel-amber-600 hover:bg-pastel-amber-100 transition-colors" title="Edit Kuis (Kode & Jadwal)">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </a>

                                        <a href="{{ route('quizzes.show', $quiz) }}" 
                                           class="p-2 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors" title="Kelola Soal">
                                            <i data-lucide="settings" class="w-4 h-4"></i>
                                        </a>

                                        <form id="delete-quiz-{{ $quiz->id }}" action="{{ route('quizzes.destroy', $quiz) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                    onclick="PastelDialog.confirmDelete({
                                                        title: 'Hapus Kuis & Seluruh Data?',
                                                        message: 'Apakah Anda yakin ingin menghapus kuis {{ addslashes($quiz->title) }}? Seluruh butir soal, sesi, dan riwayat peserta akan hilang permanen.',
                                                        onConfirm: () => document.getElementById('delete-quiz-{{ $quiz->id }}').submit()
                                                    })"
                                                    class="p-2 rounded-xl text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus Kuis">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-5 border-t border-slate-100">
                {{ $quizzes->links() }}
            </div>
        @endif

    </div>

</div>
@endsection
