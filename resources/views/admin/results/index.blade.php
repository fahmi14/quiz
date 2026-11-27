@extends('layouts.admin')

@section('title', 'Hasil Kuis & Rekapitulasi')

@section('content')
<div class="space-y-6 animate-slide-up" x-data="resultsManager()">
    
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

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('results.formulation') }}" 
               class="px-4 py-2.5 rounded-2xl bg-pastel-lavender-50 text-pastel-lavender-600 border border-pastel-lavender-200 hover:bg-pastel-lavender-100 text-xs font-bold transition-all flex items-center gap-2 shadow-sm">
                <i data-lucide="calculator" class="w-4 h-4 text-pastel-lavender-500"></i>
                <span>Formulasi Nilai Gabungan</span>
            </a>

            @if($selectedQuiz)
                <a href="{{ route('results.export', $selectedQuiz) }}" 
                   class="px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 hover:shadow-lg transition-all flex items-center gap-2">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                    <span>Export Hasil ke Excel (.xlsx)</span>
                </a>
            @endif
        </div>
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
            @if(request('search') || request('status'))
                <a href="{{ route('results.index', ['quiz_id' => $selectedQuiz?->id]) }}" class="text-xs text-rose-500 hover:underline">Reset</a>
            @endif
        </form>
    </div>

    @if(!$selectedQuiz)
        <div class="glass-card p-12 rounded-3xl text-center text-slate-400">
            <i data-lucide="award" class="w-12 h-12 mx-auto text-slate-300 mb-2"></i>
            <h4 class="font-bold text-slate-700">Belum Ada Kuis yang Dipilih</h4>
            <p class="text-xs">Silakan buat kuis dan bagikan ke peserta terlebih dahulu.</p>
        </div>
    @else
        <!-- Statistic Overview Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            
            <div class="glass-card p-5 rounded-3xl border border-white/90 shadow-soft">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Total Peserta</span>
                <div class="font-display font-extrabold text-2xl text-slate-800">{{ $stats['total'] }}</div>
                <span class="text-[11px] text-pastel-sky-500 font-medium">Orang terdaftar</span>
            </div>

            <div class="glass-card p-5 rounded-3xl border border-white/90 shadow-soft">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Sudah Menyelesaikan</span>
                <div class="font-display font-extrabold text-2xl text-emerald-600">{{ $stats['completed'] }}</div>
                <span class="text-[11px] text-emerald-500 font-medium">Selesai dinilai</span>
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

                <div class="flex items-center gap-3">
                    <!-- Bulk Delete Button (Muncul jika ada peserta tercentang) -->
                    <button type="button" 
                            x-show="selectedIds.length > 0"
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-90"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-90"
                            @click="confirmBulkDelete()"
                            class="px-3.5 py-1.5 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200/80 text-xs font-semibold flex items-center gap-1.5 shadow-sm transition-all animate-pulse">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5 text-rose-500"></i>
                        <span>Hapus Terpilih (<span x-text="selectedIds.length"></span>)</span>
                    </button>

                    <a href="{{ route('results.export', $selectedQuiz) }}" 
                       class="px-3.5 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        Export (.xlsx)
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/70 border-b border-slate-100 text-[11px] uppercase tracking-wider text-slate-400 font-bold">
                        <tr>
                            <th class="px-4 py-4 text-center w-12">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                           :checked="isAllSelected" 
                                           @change="toggleSelectAll($event.target.checked)"
                                           class="w-4 h-4 rounded-lg border-slate-300 text-pastel-sage-500 focus:ring-pastel-sage-300 transition-all cursor-pointer">
                                </label>
                            </th>
                            <th class="px-4 py-4 text-center w-16">Peringkat</th>
                            <th class="px-6 py-4">Nama Peserta</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Skor Akhir</th>
                            <th class="px-6 py-4 text-center">Benar / Terjawab</th>
                            <th class="px-6 py-4 text-center">Waktu Mulai</th>
                            <th class="px-6 py-4 text-center">Waktu Selesai</th>
                            <th class="px-6 py-4 text-center">Durasi</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($participants as $idx => $p)
                            <tr class="transition-colors"
                                :class="{
                                    'bg-rose-50/50 hover:bg-rose-50/80': selectedIds.includes({{ $p->id }}),
                                    'hover:bg-pastel-sage-50/40': !selectedIds.includes({{ $p->id }})
                                }">
                                <!-- Checkbox Centang -->
                                <td class="px-4 py-4 text-center">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               :value="{{ $p->id }}" 
                                               :checked="selectedIds.includes({{ $p->id }})"
                                               @change="toggleSelect({{ $p->id }}, $event.target.checked)"
                                               class="w-4 h-4 rounded-lg border-slate-300 text-pastel-sage-500 focus:ring-pastel-sage-300 transition-all cursor-pointer">
                                    </label>
                                </td>
                                <td class="px-4 py-4 text-center font-mono font-bold">
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
                                    <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                        @if($p->class_name)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-semibold">
                                                <i data-lucide="graduation-cap" class="w-3 h-3 text-slate-400"></i>
                                                {{ $p->class_name }}
                                            </span>
                                        @endif
                                        @if($p->attendance_number)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-pastel-sky-50 text-pastel-sky-600 border border-pastel-sky-200/60 text-[10px] font-mono font-bold">
                                                <span class="text-slate-400 font-normal">Absen:</span>
                                                {{ $p->attendance_number }}
                                            </span>
                                        @endif
                                        @if($p->identifier)
                                            <span class="text-[11px] text-slate-400">{{ $p->identifier }}</span>
                                        @endif
                                    </div>
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
                                    {{ $p->started_at ? $p->started_at->format('d/m/y H:i') . ' WIB' : '-' }}
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-[11px] text-slate-500">
                                    {{ $p->finished_at ? $p->finished_at->format('d/m/y H:i') . ' WIB' : '-' }}
                                </td>
                                <td class="px-6 py-4 text-center text-xs text-slate-600">
                                    @if($p->started_at && $p->finished_at)
                                        {{ $p->started_at->diff($p->finished_at)->format('%im %ss') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('results.participant', [$selectedQuiz, $p]) }}" 
                                           class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold inline-flex items-center gap-1 transition-colors">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                            Review
                                        </a>
                                        <button type="button" 
                                                @click="confirmDeleteOne({{ $p->id }}, '{{ addslashes($p->name) }}')"
                                                class="p-1.5 rounded-xl text-slate-300 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                                title="Hapus Peserta">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="p-8 text-center text-slate-400 text-xs">
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

@push('scripts')
<script>
    function resultsManager() {
        return {
            pageParticipantIds: @json($participants instanceof \Illuminate\Pagination\LengthAwarePaginator ? $participants->pluck('id')->values() : collect($participants)->pluck('id')->values()),
            selectedIds: [],

            get isAllSelected() {
                return this.pageParticipantIds.length > 0 && this.selectedIds.length === this.pageParticipantIds.length;
            },

            toggleSelectAll(checked) {
                if (checked) {
                    this.selectedIds = [...this.pageParticipantIds];
                } else {
                    this.selectedIds = [];
                }
            },

            toggleSelect(id, checked) {
                if (checked) {
                    if (!this.selectedIds.includes(id)) {
                        this.selectedIds.push(id);
                    }
                } else {
                    this.selectedIds = this.selectedIds.filter(item => item !== id);
                }
            },

            confirmDeleteOne(id, name) {
                PastelDialog.confirmDelete({
                    title: 'Hapus Peserta Kuis?',
                    message: `Apakah Anda yakin ingin menghapus data peserta "${name}"? Seluruh jawaban dan riwayat pengerjaan peserta ini akan dihapus permanen.`,
                    onConfirm: () => {
                        fetch(`{{ url('admin/monitoring/participants') }}/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            }
                        })
                        .then(res => res.json())
                        .then(res => {
                            if (res.success) {
                                PastelDialog.alert({
                                    title: 'Berhasil Dihapus',
                                    message: res.message || 'Peserta berhasil dihapus.',
                                    type: 'success'
                                });
                                setTimeout(() => window.location.reload(), 800);
                            }
                        })
                        .catch(err => {
                            PastelDialog.alert({
                                title: 'Gagal Menghapus',
                                message: 'Terjadi kesalahan sistem saat mencoba menghapus peserta.',
                                type: 'error'
                            });
                        });
                    }
                });
            },

            confirmBulkDelete() {
                if (this.selectedIds.length === 0) return;

                const count = this.selectedIds.length;
                PastelDialog.confirmDelete({
                    title: `Hapus ${count} Peserta Terpilih?`,
                    message: `Anda telah memilih ${count} nama peserta. Seluruh rekapitulasi, rekaman jawaban, dan nilai mereka akan dihapus permanen dari sistem.`,
                    onConfirm: () => {
                        fetch(`{{ route('results.participants.bulk-destroy') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                ids: this.selectedIds
                            })
                        })
                        .then(res => res.json())
                        .then(res => {
                            if (res.success) {
                                PastelDialog.alert({
                                    title: 'Penghapusan Berhasil',
                                    message: res.message || `${count} peserta berhasil dihapus.`,
                                    type: 'success'
                                });
                                setTimeout(() => window.location.reload(), 800);
                            } else {
                                PastelDialog.alert({
                                    title: 'Gagal Menghapus',
                                    message: res.message || 'Gagal menghapus peserta terpilih.',
                                    type: 'error'
                                });
                            }
                        })
                        .catch(err => {
                            PastelDialog.alert({
                                title: 'Terjadi Kesalahan',
                                message: 'Terjadi kesalahan jaringan atau server saat menghapus data.',
                                type: 'error'
                            });
                        });
                    }
                });
            }
        };
    }
</script>
@endpush
