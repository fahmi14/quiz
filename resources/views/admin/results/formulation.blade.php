@extends('layouts.admin')

@section('title', 'Formulasi Nilai Multi-Quiz')

@section('content')
<div class="space-y-6 animate-slide-up" x-data="formulationApp()">
    
    <!-- Header -->
    <div class="glass-card p-6 rounded-3xl shadow-soft flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-pastel-lavender-100 text-pastel-lavender-600 text-xs font-semibold mb-2">
                <i data-lucide="calculator" class="w-3.5 h-3.5"></i>
                Kalkulasi & Rekap Multi-Kuis
            </div>
            <h1 class="font-display font-black text-2xl text-slate-800 tracking-tight">Formulasi Nilai Akhir</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Gabungkan skor beberapa kuis dengan persentase bobot khusus (misal 30% + 70%), cocokkan nama peserta yang sedikit berbeda, dan unduh rekapan Excel.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('results.index') }}" 
               class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold flex items-center gap-2 shadow-sm transition-all">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Hasil Kuis</span>
            </a>
            
            <template x-if="calculatedRows.length > 0">
                <button type="button" 
                        @click="exportToExcel()"
                        :disabled="isExporting"
                        class="px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs font-bold shadow-md shadow-emerald-600/20 hover:shadow-lg transition-all flex items-center gap-2">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                    <span x-text="isExporting ? 'Memproses...' : 'Export Hasil ke Excel (.xlsx)'"></span>
                </button>
            </template>
        </div>
    </div>

    <!-- Step 1: Pemilihan Kuis & Pengaturan Bobot -->
    <div class="glass-card p-6 rounded-3xl shadow-soft space-y-5">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-xl bg-pastel-sage-100 text-pastel-sage-600 font-bold text-xs flex items-center justify-center">1</span>
                <h3 class="font-display font-bold text-base text-slate-800">Pilih Kuis & Tentukan Bobot Persentase (%)</h3>
            </div>
            <div class="text-xs font-semibold" :class="totalWeight === 100 ? 'text-emerald-600' : 'text-rose-500'">
                Total Bobot: <span class="font-bold text-sm" x-text="totalWeight"></span>% 
                <span x-show="totalWeight !== 100">(Harus 100%)</span>
                <span x-show="totalWeight === 100" class="inline-flex items-center gap-1"><i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Siap</span>
            </div>
        </div>

        <form method="GET" action="{{ route('results.formulation') }}" id="quiz-filter-form" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                @foreach($allQuizzes as $q)
                    @php
                        $isChecked = in_array($q->id, $selectedQuizIds);
                        $currentWeight = $weights[$q->id] ?? 0;
                    @endphp
                    <div class="p-4 rounded-2xl border transition-all duration-200"
                         :class="selectedQuizzes.includes({{ $q->id }}) ? 'border-pastel-sage-400 bg-pastel-sage-50/50 shadow-sm' : 'border-slate-200/80 bg-white hover:border-slate-300'">
                        <div class="flex items-start justify-between gap-2">
                            <label class="flex items-start gap-3 cursor-pointer select-none flex-1">
                                <input type="checkbox" 
                                       name="quiz_ids[]" 
                                       value="{{ $q->id }}" 
                                       @change="toggleQuiz({{ $q->id }})"
                                       :checked="selectedQuizzes.includes({{ $q->id }})"
                                       class="mt-1 rounded-lg border-slate-300 text-pastel-sage-500 focus:ring-pastel-sage-400">
                                <div>
                                    <div class="font-bold text-xs sm:text-sm text-slate-800 line-clamp-1">{{ $q->title }}</div>
                                    <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-1">
                                        <span class="inline-flex items-center gap-1 font-mono text-[10px] bg-slate-100 px-1.5 py-0.5 rounded">
                                            {{ $q->code }}
                                        </span>
                                        <span>&bull;</span>
                                        <span>{{ $q->participants_count }} peserta selesai</span>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Input Bobot -->
                        <div class="mt-3 pt-3 border-t border-slate-100/80 flex items-center justify-between"
                             x-show="selectedQuizzes.includes({{ $q->id }})" x-transition>
                            <label class="text-[11px] font-semibold text-slate-600">Bobot Persentase:</label>
                            <div class="flex items-center gap-1.5">
                                <input type="number" 
                                       name="weights[{{ $q->id }}]" 
                                       x-model.number="weights[{{ $q->id }}]"
                                       @input="recalculate()"
                                       min="0" max="100" step="1"
                                       class="w-16 p-1.5 text-center text-xs font-bold rounded-lg border border-slate-200 focus:border-pastel-sage-400 focus:outline-none">
                                <span class="text-xs font-bold text-slate-500">%</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center justify-between pt-2">
                <button type="submit" 
                        class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition-all shadow-sm flex items-center gap-1.5">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                    <span>Terapkan Pilihan Kuis</span>
                </button>

                <button type="button" 
                        @click="autoDistributeWeights()"
                        x-show="selectedQuizzes.length > 0"
                        class="px-3.5 py-2 rounded-xl bg-pastel-lavender-50 hover:bg-pastel-lavender-100 text-pastel-lavender-600 border border-pastel-lavender-200 text-xs font-semibold transition-all">
                    Bagi Rata Bobot (100% / <span x-text="selectedQuizzes.length"></span>)
                </button>
            </div>
        </form>
    </div>

    <!-- Step 2: Pencocokan Peserta & Preview Nilai Akhir -->
    <template x-if="selectedQuizzes.length >= 2">
        <div class="space-y-6">
            
            <!-- Pencocokan Nama Peserta Interaktif -->
            <div class="glass-card p-6 rounded-3xl shadow-soft space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-xl bg-pastel-sky-100 text-pastel-sky-600 font-bold text-xs flex items-center justify-center">2</span>
                        <div>
                            <h3 class="font-display font-bold text-base text-slate-800">Pencocokan Nama Peserta (Linking & Matching)</h3>
                            <p class="text-[11px] text-slate-500">
                                Sistem secara otomatis mencocokkan nama yang sama. Jika ada peserta dengan variasi ejaan atau saltik nama, Anda dapat memilih pasangan namanya secara manual pada dropdown.
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="autoMatchParticipants()"
                                class="px-3.5 py-1.5 rounded-xl bg-pastel-sky-50 hover:bg-pastel-sky-100 text-pastel-sky-600 border border-pastel-sky-200 text-xs font-bold transition-all flex items-center gap-1.5">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                            <span>Cocokkan Otomatis (Auto-Match)</span>
                        </button>
                    </div>
                </div>

                <!-- Notifikasi Info Matching -->
                <div class="p-3.5 rounded-2xl bg-amber-50/70 border border-amber-200/60 text-amber-800 text-xs flex items-start gap-2.5">
                    <i data-lucide="info" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                    <div>
                        <span>Basis Nama Utama diambil dari peserta kuis pertama: <strong>"<span x-text="quizzesMeta[selectedQuizzes[0]]?.title"></span>"</strong>.</span>
                        <div class="mt-1 text-[11px] text-amber-700">Untuk setiap baris peserta, Anda dapat menghubungkan / mencocokkan nama peserta yang sesuai dari kuis-kuis lainnya jika namanya belum terhubung.</div>
                    </div>
                </div>

                <!-- Tabel Mapping Manual -->
                <div class="overflow-x-auto rounded-2xl border border-slate-100">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-pastel-cream-100/60 text-slate-700 font-bold border-b border-slate-200/70">
                                <th class="p-3.5 w-12 text-center">#</th>
                                <th class="p-3.5 min-w-[200px]">
                                    <span class="text-pastel-sage-600 font-bold">Peserta Utama (Kuis 1)</span>
                                    <div class="text-[10px] text-slate-400 font-normal" x-text="quizzesMeta[selectedQuizzes[0]]?.title"></div>
                                </th>
                                <template x-for="(qId, idx) in selectedQuizzes.slice(1)" :key="qId">
                                    <th class="p-3.5 min-w-[220px]">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-pastel-sky-600 font-bold" x-text="'Pencocokan Kuis ' + (idx + 2)"></span>
                                            <span class="px-1.5 py-0.5 rounded text-[10px] bg-white border border-slate-200 font-mono" x-text="weights[qId] + '%'"></span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 font-normal truncate max-w-[200px]" x-text="quizzesMeta[qId]?.title"></div>
                                    </th>
                                </template>
                                <th class="p-3.5 text-center w-28 bg-pastel-sage-100/40">Nilai Akhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(row, rIndex) in matchedRows" :key="row.primaryId">
                                <tr class="hover:bg-pastel-sage-50/30 transition-colors">
                                    <td class="p-3.5 text-center font-bold text-slate-400" x-text="rIndex + 1"></td>
                                    
                                    <!-- Nama Utama & Skor Kuis 1 -->
                                    <td class="p-3.5">
                                        <div class="font-bold text-slate-800" x-text="row.primaryName"></div>
                                        <div class="text-[11px] text-slate-500 flex items-center gap-1.5 mt-0.5">
                                            <span>Skor:</span>
                                            <span class="font-bold text-pastel-sage-600" x-text="row.quizScores[selectedQuizzes[0]] ?? 0"></span>
                                            <span class="text-slate-400">/ 100</span>
                                        </div>
                                    </td>

                                    <!-- Dropdown Seleksi Peserta Kuis 2, 3 dst -->
                                    <template x-for="qId in selectedQuizzes.slice(1)" :key="qId">
                                        <td class="p-3.5">
                                            <select class="w-full text-xs font-semibold p-2 rounded-xl border border-slate-200 bg-white focus:border-pastel-sky-400 focus:outline-none"
                                                    :value="row.mappings[qId] || ''"
                                                    @change="updateMapping(row.primaryId, qId, $event.target.value)">
                                                <option value="">-- Belum / Tidak Ada Pasangan (0) --</option>
                                                <template x-for="p in (quizParticipantsMap[qId] || [])" :key="p.id">
                                                    <option :value="p.id" 
                                                            :selected="row.mappings[qId] == p.id"
                                                            x-text="p.name + ' (Skor: ' + p.score + ')'">
                                                    </option>
                                                </template>
                                            </select>
                                            <div class="mt-1 flex items-center justify-between text-[10px] text-slate-400 px-1">
                                                <span>Skor Terpilih:</span>
                                                <span class="font-bold text-slate-700" x-text="row.quizScores[qId] ?? 0"></span>
                                            </div>
                                        </td>
                                    </template>

                                    <!-- Nilai Akhir Live Preview -->
                                    <td class="p-3.5 text-center bg-pastel-sage-100/20">
                                        <span class="font-mono font-black text-sm px-2.5 py-1 rounded-xl"
                                              :class="row.finalScore >= 70 ? 'bg-emerald-100/70 text-emerald-800' : 'bg-rose-100/70 text-rose-700'"
                                              x-text="row.finalScore">
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Step 3: Tabel Hasil Akhir & Peringkat -->
            <div class="glass-card p-6 rounded-3xl shadow-soft space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-xl bg-pastel-lavender-100 text-pastel-lavender-600 font-bold text-xs flex items-center justify-center">3</span>
                        <div>
                            <h3 class="font-display font-bold text-base text-slate-800">Tabel Peringkat & Hasil Akhir Formulasi</h3>
                            <p class="text-[11px] text-slate-500">Urutan peringkat otomatis dihitung berdasarkan Nilai Akhir Tertinggi.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-slate-500">Total Peserta: <strong class="text-slate-800" x-text="calculatedRows.length"></strong></span>
                    </div>
                </div>

                <!-- Ringkasan Statistik Pastel -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
                    <div class="p-4 rounded-2xl bg-pastel-sage-50/60 border border-pastel-sage-200/60">
                        <div class="text-[11px] font-bold text-slate-400 uppercase">Rata-rata Nilai</div>
                        <div class="text-xl font-black text-pastel-sage-600 mt-1" x-text="summaryStats.avg"></div>
                    </div>
                    <div class="p-4 rounded-2xl bg-pastel-sky-50/60 border border-pastel-sky-200/60">
                        <div class="text-[11px] font-bold text-slate-400 uppercase">Nilai Tertinggi</div>
                        <div class="text-xl font-black text-pastel-sky-600 mt-1" x-text="summaryStats.max"></div>
                    </div>
                    <div class="p-4 rounded-2xl bg-pastel-cream-100/70 border border-pastel-cream-200/70">
                        <div class="text-[11px] font-bold text-slate-400 uppercase">Nilai Terendah</div>
                        <div class="text-xl font-black text-slate-600 mt-1" x-text="summaryStats.min"></div>
                    </div>
                    <div class="p-4 rounded-2xl bg-pastel-lavender-50/60 border border-pastel-lavender-200/60">
                        <div class="text-[11px] font-bold text-slate-400 uppercase">Kelulusan (&ge;70)</div>
                        <div class="text-xl font-black text-emerald-600 mt-1" x-text="summaryStats.passCount + ' (' + summaryStats.passRate + '%)'"></div>
                    </div>
                </div>

                <!-- Table Leaderboard -->
                <div class="overflow-x-auto rounded-2xl border border-slate-100">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                                <th class="p-3.5 w-16 text-center">Rank</th>
                                <th class="p-3.5 min-w-[200px]">Nama Peserta</th>
                                <template x-for="qId in selectedQuizzes" :key="qId">
                                    <th class="p-3.5 text-center min-w-[120px]">
                                        <div x-text="quizzesMeta[qId]?.title"></div>
                                        <div class="text-[10px] text-slate-400 font-normal" x-text="'Bobot ' + weights[qId] + '%'"></div>
                                    </th>
                                </template>
                                <th class="p-3.5 text-center w-28 bg-pastel-sage-100/50">Nilai Akhir</th>
                                <th class="p-3.5 text-center w-24">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, idx) in calculatedRows" :key="item.primaryId">
                                <tr class="hover:bg-pastel-sage-50/30 transition-colors">
                                    <!-- Rank badge -->
                                    <td class="p-3.5 text-center">
                                        <template x-if="item.rank === 1">
                                            <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 font-black text-xs inline-flex items-center justify-center shadow-sm">1</span>
                                        </template>
                                        <template x-if="item.rank === 2">
                                            <span class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 font-black text-xs inline-flex items-center justify-center shadow-sm">2</span>
                                        </template>
                                        <template x-if="item.rank === 3">
                                            <span class="w-6 h-6 rounded-full bg-amber-200/70 text-amber-900 font-black text-xs inline-flex items-center justify-center shadow-sm">3</span>
                                        </template>
                                        <template x-if="item.rank > 3">
                                            <span class="font-bold text-slate-400 text-xs" x-text="item.rank"></span>
                                        </template>
                                    </td>

                                    <!-- Nama Peserta -->
                                    <td class="p-3.5 font-bold text-slate-800" x-text="item.name"></td>

                                    <!-- Nilai masing-masing quiz -->
                                    <template x-for="qId in selectedQuizzes" :key="qId">
                                        <td class="p-3.5 text-center font-semibold text-slate-600" x-text="item.quiz_scores[qId] ?? 0"></td>
                                    </template>

                                    <!-- Nilai Akhir -->
                                    <td class="p-3.5 text-center bg-pastel-sage-100/30 font-mono font-black text-sm text-pastel-sage-700" x-text="item.final_score"></td>

                                    <!-- Status Lulus / Remedial -->
                                    <td class="p-3.5 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                              :class="item.final_score >= 70 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-600 border border-rose-200'"
                                              x-text="item.final_score >= 70 ? 'Lulus' : 'Remedial'">
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </template>

    <template x-if="selectedQuizzes.length < 2">
        <div class="glass-card p-12 rounded-3xl text-center space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-pastel-lavender-100 text-pastel-lavender-500 mx-auto flex items-center justify-center">
                <i data-lucide="layers" class="w-6 h-6"></i>
            </div>
            <h3 class="font-display font-bold text-lg text-slate-800">Pilih Minimal 2 Kuis</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto">
                Silakan centang minimal dua kuis pada panel langkah 1 di atas untuk mulai membuat formulasi nilai gabungan.
            </p>
        </div>
    </template>

</div>
@endsection

@push('scripts')
<script>
function formulationApp() {
    return {
        selectedQuizzes: @json($selectedQuizIds),
        weights: @json($weights),
        quizParticipantsMap: @json($quizParticipantsMap),
        quizzesMeta: {
            @foreach($allQuizzes as $q)
                {{ $q->id }}: { title: @json($q->title), code: @json($q->code) },
            @endforeach
        },
        matchedRows: [],
        calculatedRows: [],
        isExporting: false,

        init() {
            // Inisialisasi bobot jika belum terisi
            this.selectedQuizzes.forEach(id => {
                if (this.weights[id] === undefined) {
                    this.weights[id] = 0;
                }
            });

            this.autoMatchParticipants();
            this.$nextTick(() => {
                if (window.lucide) lucide.createIcons();
            });
        },

        get totalWeight() {
            let sum = 0;
            this.selectedQuizzes.forEach(id => {
                sum += Number(this.weights[id] || 0);
            });
            return sum;
        },

        get summaryStats() {
            if (this.calculatedRows.length === 0) {
                return { avg: 0, min: 0, max: 0, passCount: 0, passRate: 0 };
            }
            const scores = this.calculatedRows.map(r => r.final_score);
            const sum = scores.reduce((a, b) => a + b, 0);
            const avg = (sum / scores.length).toFixed(1);
            const min = Math.min(...scores).toFixed(1);
            const max = Math.max(...scores).toFixed(1);
            const passCount = scores.filter(s => s >= 70).length;
            const passRate = ((passCount / scores.length) * 100).toFixed(0);

            return { avg, min, max, passCount, passRate };
        },

        toggleQuiz(id) {
            const index = this.selectedQuizzes.indexOf(id);
            if (index > -1) {
                this.selectedQuizzes.splice(index, 1);
                delete this.weights[id];
            } else {
                this.selectedQuizzes.push(id);
                this.weights[id] = 0;
            }
        },

        autoDistributeWeights() {
            const count = this.selectedQuizzes.length;
            if (count === 0) return;
            const base = Math.floor(100 / count);
            const remainder = 100 % count;

            this.selectedQuizzes.forEach((id, idx) => {
                this.weights[id] = base + (idx === 0 ? remainder : 0);
            });
            this.recalculate();
        },

        // Normalisasi string untuk fuzzy matching sederhana
        cleanName(str) {
            return (str || '')
                .toLowerCase()
                .replace(/[^a-z0-9]/g, '')
                .trim();
        },

        // Otomatis mencocokkan peserta kuis 1 dengan kuis lainnya berdasarkan kemiripan nama
        autoMatchParticipants() {
            if (this.selectedQuizzes.length === 0) {
                this.matchedRows = [];
                this.calculatedRows = [];
                return;
            }

            const primaryQuizId = this.selectedQuizzes[0];
            const primaryList = this.quizParticipantsMap[primaryQuizId] || [];

            this.matchedRows = primaryList.map(primaryP => {
                const mappings = {};
                const quizScores = {};
                quizScores[primaryQuizId] = primaryP.score;

                // Cari pasangan di kuis lainnya
                this.selectedQuizzes.slice(1).forEach(otherQuizId => {
                    const otherList = this.quizParticipantsMap[otherQuizId] || [];
                    const pNameClean = this.cleanName(primaryP.name);

                    // 1. Exact or cleaned match
                    let found = otherList.find(op => this.cleanName(op.name) === pNameClean);

                    // 2. Contains match jika tidak exact
                    if (!found) {
                        found = otherList.find(op => {
                            const oClean = this.cleanName(op.name);
                            return oClean.length > 3 && (pNameClean.includes(oClean) || oClean.includes(pNameClean));
                        });
                    }

                    if (found) {
                        mappings[otherQuizId] = found.id;
                        quizScores[otherQuizId] = found.score;
                    } else {
                        mappings[otherQuizId] = '';
                        quizScores[otherQuizId] = 0;
                    }
                });

                return {
                    primaryId: primaryP.id,
                    primaryName: primaryP.name,
                    mappings: mappings,
                    quizScores: quizScores,
                    finalScore: 0
                };
            });

            this.recalculate();
        },

        updateMapping(primaryId, quizId, participantId) {
            const row = this.matchedRows.find(r => r.primaryId === primaryId);
            if (!row) return;

            row.mappings[quizId] = participantId ? Number(participantId) : '';
            if (participantId) {
                const otherList = this.quizParticipantsMap[quizId] || [];
                const found = otherList.find(p => p.id == participantId);
                row.quizScores[quizId] = found ? found.score : 0;
            } else {
                row.quizScores[quizId] = 0;
            }

            this.recalculate();
        },

        recalculate() {
            // Hitung nilai akhir masing-masing peserta
            this.matchedRows.forEach(row => {
                let total = 0;
                this.selectedQuizzes.forEach(qId => {
                    const weight = Number(this.weights[qId] || 0);
                    const score = Number(row.quizScores[qId] || 0);
                    total += (score * weight) / 100;
                });
                row.finalScore = Number(total.toFixed(2));
            });

            // Buat ranked rows untuk Step 3
            const sorted = [...this.matchedRows].map(r => ({
                primaryId: r.primaryId,
                name: r.primaryName,
                quiz_scores: { ...r.quizScores },
                final_score: r.finalScore
            }));

            sorted.sort((a, b) => b.final_score - a.final_score);

            // Berikan peringkat
            sorted.forEach((item, index) => {
                item.rank = index + 1;
            });

            this.calculatedRows = sorted;
            this.$nextTick(() => {
                if (window.lucide) lucide.createIcons();
            });
        },

        exportToExcel() {
            if (this.calculatedRows.length === 0) {
                PastelDialog.alert({
                    title: 'Data Masih Kosong',
                    message: 'Tidak ada data peserta yang dapat diekspor.',
                    type: 'error'
                });
                return;
            }

            this.isExporting = true;

            const quizzesData = this.selectedQuizzes.map(id => ({
                id: id,
                title: this.quizzesMeta[id]?.title || ('Kuis ' + id),
                weight: this.weights[id] || 0
            }));

            const payload = {
                formula_title: 'Rekapitulasi Formulasi Nilai Akhir',
                quizzes: quizzesData,
                data: this.calculatedRows
            };

            fetch('{{ route("results.formulation.export") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => {
                if (!res.ok) throw new Error('Gagal mengunduh berkas Excel');
                return res.blob();
            })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'Formulasi_Hasil_' + new Date().toISOString().slice(0, 10) + '.xlsx';
                document.body.appendChild(a);
                a.click();
                a.remove();
                this.isExporting = false;
            })
            .catch(err => {
                console.error(err);
                this.isExporting = false;
                PastelDialog.alert({
                    title: 'Gagal Export',
                    message: 'Terjadi gangguan saat membuat dokumen Excel formulasi.',
                    type: 'error'
                });
            });
        }
    };
}
</script>
@endpush
