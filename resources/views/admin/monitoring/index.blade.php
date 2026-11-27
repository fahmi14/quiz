@extends('layouts.admin')

@section('title', 'Monitor Live Quiz')

@section('content')
<div class="space-y-6 animate-slide-up" x-data="liveMonitor()">
    
    <!-- Header with Selector -->
    <div class="glass-card p-6 rounded-3xl shadow-soft flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-semibold mb-2">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                Live Real-Time Monitoring
            </div>
            <h1 class="font-display font-black text-2xl text-slate-800 tracking-tight">Monitoring Peserta Kuis</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Pilih kuis untuk memantau progress pengerjaan, skor peserta, dan durasi secara langsung.</p>
        </div>

        <!-- Quiz Selector Dropdown -->
        <div class="w-full md:w-72">
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Pilih Kuis yang Dipantau</label>
            <select @change="changeQuiz($event.target.value)" class="w-full text-xs font-semibold p-3 rounded-2xl border-2 border-slate-200 bg-white shadow-sm focus:border-pastel-sage-400 focus:outline-none">
                @foreach($quizzes as $q)
                    <option value="{{ $q->id }}" {{ $selectedQuiz && $selectedQuiz->id === $q->id ? 'selected' : '' }}>
                        {{ $q->title }} ({{ $q->participants_count }} Peserta)
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    @if(!$selectedQuiz)
        <div class="glass-card p-12 rounded-3xl text-center text-slate-400">
            <i data-lucide="radio" class="w-12 h-12 mx-auto text-slate-300 mb-2"></i>
            <h4 class="font-bold text-slate-700">Belum Ada Kuis yang Dipilih</h4>
            <p class="text-xs">Silakan pilih atau buat kuis terlebih dahulu untuk memantau aktivitas peserta.</p>
        </div>
    @else
        <!-- Live Stat Counters -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            
            <div class="glass-card p-5 rounded-3xl border border-white/90 shadow-soft">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Peserta</span>
                    <div class="w-8 h-8 rounded-xl bg-pastel-sky-100 text-pastel-sky-600 flex items-center justify-center">
                        <i data-lucide="users" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="font-display font-extrabold text-2xl text-slate-800" x-text="stats.total"></div>
                <span class="text-[11px] text-pastel-sky-500 font-medium">Tergabung dalam sesi</span>
            </div>

            <div class="glass-card p-5 rounded-3xl border border-white/90 shadow-soft">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Sedang Mengerjakan</span>
                    <div class="w-8 h-8 rounded-xl bg-pastel-amber-100 text-pastel-amber-600 flex items-center justify-center">
                        <i data-lucide="loader" class="w-4 h-4 animate-spin"></i>
                    </div>
                </div>
                <div class="font-display font-extrabold text-2xl text-amber-600" x-text="stats.in_progress"></div>
                <span class="text-[11px] text-amber-500 font-medium">Dalam durasi pengerjaan</span>
            </div>

            <div class="glass-card p-5 rounded-3xl border border-white/90 shadow-soft">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Selesai Submit</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="font-display font-extrabold text-2xl text-emerald-600" x-text="stats.completed"></div>
                <span class="text-[11px] text-emerald-600 font-medium">Hasil final tercatat</span>
            </div>

            <div class="glass-card p-5 rounded-3xl border border-white/90 shadow-soft">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Rata-Rata Nilai</span>
                    <div class="w-8 h-8 rounded-xl bg-pastel-lavender-100 text-pastel-lavender-500 flex items-center justify-center">
                        <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="font-display font-extrabold text-2xl text-slate-800" x-text="stats.avg_score"></div>
                <span class="text-[11px] text-pastel-lavender-500 font-medium" x-text="'Skor tertinggi: ' + stats.highest_score"></span>
            </div>

        </div>

        <!-- Dynamic Animated Table (Sesuai Diagram Alur) -->
        <div class="glass-card rounded-3xl shadow-soft border border-white/90 overflow-hidden">
            
            <div class="p-6 border-b border-slate-100 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></div>
                    <div>
                        <h3 class="font-display font-bold text-base text-slate-800">Daftar Peserta & Status Live</h3>
                        <p class="text-xs text-slate-400">Tabel bergerak dinamis dan diperbarui setiap 4 detik tanpa reload</p>
                    </div>
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

                    <span class="text-[11px] text-slate-400 flex items-center gap-1 font-mono">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                        Pembaruan Terakhir: <span x-text="lastUpdated" class="font-bold text-slate-600"></span>
                    </span>

                    <button type="button" @click="fetchLiveData()" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-pastel-sage-600 shadow-sm transition-transform active:rotate-180" title="Refresh Sekarang">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Table with Motion Animations -->
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
                            <th class="px-4 py-4 text-center w-16">Rank</th>
                            <th class="px-6 py-4">Nama Peserta</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Progress Jawaban</th>
                            <th class="px-6 py-4 text-center">Skor Berjalan</th>
                            <th class="px-6 py-4 text-center">Mulai / Selesai</th>
                            <th class="px-6 py-4 text-center">Durasi</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(p, idx) in participants" :key="p.id">
                            <tr class="transition-all duration-300 animate-slide-up"
                                :class="{
                                    'bg-rose-50/50 hover:bg-rose-50/80': selectedIds.includes(p.id),
                                    'hover:bg-pastel-sage-50/40 bg-white/60': !selectedIds.includes(p.id) && p.status === 'completed',
                                    'hover:bg-pastel-sage-50/40 bg-amber-50/20': !selectedIds.includes(p.id) && p.status !== 'completed'
                                }">
                                
                                <!-- Checkbox Baris -->
                                <td class="px-4 py-4 text-center">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               :value="p.id" 
                                               :checked="selectedIds.includes(p.id)"
                                               @change="toggleSelect(p.id, $event.target.checked)"
                                               class="w-4 h-4 rounded-lg border-slate-300 text-pastel-sage-500 focus:ring-pastel-sage-300 transition-all cursor-pointer">
                                    </label>
                                </td>

                                <!-- Rank with trophy for top 3 -->
                                <td class="px-4 py-4 text-center font-bold font-mono">
                                    <template x-if="p.rank === 1">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-amber-100 text-amber-700 font-bold text-xs shadow-sm">🥇</span>
                                    </template>
                                    <template x-if="p.rank === 2">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs shadow-sm">🥈</span>
                                    </template>
                                    <template x-if="p.rank === 3">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-orange-100 text-orange-700 font-bold text-xs shadow-sm">🥉</span>
                                    </template>
                                    <template x-if="p.rank > 3">
                                        <span class="text-slate-400 text-xs" x-text="'#' + p.rank"></span>
                                    </template>
                                </td>

                                <!-- Nama Peserta & Info Kelas/Absen -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-pastel-sage-100 to-pastel-sky-100 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0">
                                            <span x-text="p.name.charAt(0)"></span>
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-800 text-sm block" x-text="p.name"></span>
                                            <div class="flex items-center gap-2 mt-0.5" x-show="p.class_name || p.attendance_number">
                                                <span x-show="p.class_name" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-semibold">
                                                    <i data-lucide="graduation-cap" class="w-3 h-3 text-slate-400"></i>
                                                    <span x-text="p.class_name"></span>
                                                </span>
                                                <span x-show="p.attendance_number" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-pastel-sky-50 text-pastel-sky-600 border border-pastel-sky-200/60 text-[10px] font-mono font-bold">
                                                    <span class="text-slate-400 font-normal">Absen:</span>
                                                    <span x-text="p.attendance_number"></span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Status Badge -->
                                <td class="px-6 py-4 text-center">
                                    <template x-if="p.status === 'completed'">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Selesai
                                        </span>
                                    </template>
                                    <template x-if="p.status === 'in_progress'">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                            Mengerjakan
                                        </span>
                                    </template>
                                </td>

                                <!-- Progress Bar Animasi -->
                                <td class="px-6 py-4 text-center w-48">
                                    <div class="text-[11px] font-semibold text-slate-600 mb-1">
                                        <span x-text="p.total_answered"></span> / <span x-text="p.total_questions"></span> Terjawab
                                    </div>
                                    <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden shadow-inner">
                                        <div class="h-full bg-gradient-to-r from-pastel-sage-400 to-pastel-sky-500 rounded-full transition-all duration-500" 
                                             :style="'width: ' + p.progress_percent + '%'"></div>
                                    </div>
                                </td>

                                <!-- Skor -->
                                <td class="px-6 py-4 text-center">
                                    <span class="font-display font-extrabold text-base text-slate-800" x-text="p.score"></span>
                                    <div class="text-[10px] text-emerald-600 font-medium" x-text="p.total_correct + ' Benar'"></div>
                                </td>

                                <!-- Mulai / Selesai -->
                                <td class="px-6 py-4 text-center font-mono text-[11px] text-slate-500">
                                    <span x-text="p.started_at"></span>
                                    <span class="text-slate-300"> &rarr; </span>
                                    <span x-text="p.finished_at"></span>
                                </td>

                                <!-- Durasi -->
                                <td class="px-6 py-4 text-center text-xs text-slate-600 font-medium" x-text="p.duration"></td>

                                <!-- Aksi Hapus Peserta -->
                                <td class="px-6 py-4 text-right">
                                    <button type="button" @click="confirmDeleteParticipant(p)" 
                                            class="p-2 rounded-xl text-slate-300 hover:text-rose-600 hover:bg-rose-50 transition-all inline-flex items-center gap-1 group" 
                                            title="Hapus Peserta">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </td>

                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Empty state jika belum ada peserta -->
            <div x-show="participants.length === 0" class="p-10 text-center text-slate-400">
                <i data-lucide="users" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                <p class="text-xs">Belum ada peserta yang bergabung dengan kode kuis ini.</p>
                <p class="text-[11px] text-slate-400 mt-1">Bagikan kode kuis <strong class="font-mono text-slate-700">{{ $selectedQuiz->code }}</strong> ke peserta.</p>
            </div>

        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    function liveMonitor() {
        return {
            quizId: {{ $selectedQuiz ? $selectedQuiz->id : 'null' }},
            lastUpdated: '{{ now()->format('H:i:s') }}',
            stats: @json($stats),
            participants: @json($formattedParticipants ?? []),
            selectedIds: [],

            init() {
                if (this.quizId) {
                    // Auto-poll every 4 seconds
                    setInterval(() => {
                        this.fetchLiveData();
                    }, 4000);
                }
            },

            get isAllSelected() {
                return this.participants.length > 0 && this.selectedIds.length === this.participants.length;
            },

            toggleSelectAll(checked) {
                if (checked) {
                    this.selectedIds = this.participants.map(p => p.id);
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

            changeQuiz(id) {
                window.location.href = `{{ route('monitoring.index') }}?quiz_id=${id}`;
            },

            fetchLiveData() {
                if (!this.quizId) return;

                fetch(`{{ url('admin/monitoring') }}/${this.quizId}/live-data`, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    this.participants = data.participants;
                    this.stats = data.stats;
                    this.lastUpdated = data.timestamp;
                    // Filter selectedIds agar tidak ada id yang sudah terhapus
                    const currentIds = this.participants.map(p => p.id);
                    this.selectedIds = this.selectedIds.filter(id => currentIds.includes(id));
                    this.$nextTick(() => lucide.createIcons());
                })
                .catch(err => console.error('Error fetching live data:', err));
            },

            confirmDeleteParticipant(participant) {
                PastelDialog.confirmDelete({
                    title: 'Hapus Peserta Kuis?',
                    message: `Apakah Anda yakin ingin menghapus peserta "${participant.name}"? Seluruh data jawaban dan hasil nilai peserta ini akan dihapus permanen.`,
                    onConfirm: () => {
                        fetch(`{{ url('admin/monitoring/participants') }}/${participant.id}`, {
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
                                this.selectedIds = this.selectedIds.filter(id => id !== participant.id);
                                this.fetchLiveData();
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
                        fetch(`{{ route('monitoring.participants.bulk-destroy') }}`, {
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
                                this.selectedIds = [];
                                this.fetchLiveData();
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
