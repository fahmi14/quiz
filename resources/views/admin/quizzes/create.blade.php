@extends('layouts.admin')

@section('title', 'Buat Kuis Baru')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 animate-slide-up" x-data="quizCreator()">
    
    <!-- Header -->
    <div class="flex items-center justify-between glass-card p-6 rounded-3xl shadow-soft">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-pastel-sage-100 text-pastel-sage-700 text-xs font-semibold mb-2">
                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                Tahap Pembuatan Kuis
            </div>
            <h1 class="font-display font-black text-2xl text-slate-800 tracking-tight">Buat Kuis & Konfigurasi Soal</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Sesuai alur: Tentukan nama, masa aktif, kode unik, import template Excel & mapping kolom.</p>
        </div>

        <a href="{{ route('quizzes.index') }}" class="px-4 py-2 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
            Kembali
        </a>
    </div>

    <!-- Form Section -->
    <form action="{{ route('quizzes.store') }}" method="POST" id="create-quiz-form" class="space-y-6">
        @csrf

        <!-- 1. IDENTITAS KUIS -->
        <div class="glass-card p-6 sm:p-8 rounded-3xl shadow-soft border border-white/90">
            <h3 class="font-display font-bold text-base text-slate-800 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-pastel-sage-200 text-pastel-sage-800 text-xs font-bold flex items-center justify-center">1</span>
                Informasi & Pengaturan Dasar Kuis
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                
                <div class="md:col-span-2">
                    <label for="title" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Nama Kuis <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="{{ old('title') }}" 
                           placeholder="Contoh: Kuis Evaluasi Pengetahuan Umum 2026"
                           required
                           class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none focus:border-pastel-sage-400 focus:ring-4 focus:ring-pastel-sage-100 transition-all shadow-sm">
                    @error('title') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="code" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5 flex items-center justify-between">
                        <span>Kode Kuis Peserta <span class="text-rose-500">*</span></span>
                        <span class="text-[10px] text-pastel-sage-600 font-normal">Unik untuk peserta bergabung</span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="code" 
                               name="code" 
                               value="{{ old('code', $suggestedCode) }}" 
                               required
                               style="letter-spacing: 0.1em;"
                               class="w-full uppercase font-mono font-bold px-4 py-3 rounded-2xl border-2 border-slate-200 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none focus:border-pastel-sage-400 focus:ring-4 focus:ring-pastel-sage-100 transition-all shadow-sm">
                        <button type="button" @click="generateNewCode()" title="Buat Kode Acak"
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
                               value="{{ old('duration_minutes', 30) }}" 
                               min="1" 
                               max="360"
                               required
                               class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none focus:border-pastel-sage-400 focus:ring-4 focus:ring-pastel-sage-100 transition-all shadow-sm">
                        <span class="absolute inset-y-0 right-4 flex items-center text-xs text-slate-400 pointer-events-none">Menit</span>
                    </div>
                </div>

                <!-- Custom Jumlah Pilihan Ganda (2 s/d 20) -->
                <div>
                    <label for="options_count" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5 flex items-center justify-between">
                        <span>Jumlah Pilihan Ganda <span class="text-rose-500">*</span></span>
                        <span class="text-[10px] text-pastel-sage-600 font-semibold" x-text="optionsCount + ' Pilihan (A - ' + letters[optionsCount - 1] + ')'"></span>
                    </label>
                    <div class="relative">
                        <select id="options_count" 
                                name="options_count" 
                                x-model.number="optionsCount"
                                @change="onOptionsCountChange()"
                                required
                                class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-white text-slate-800 text-xs sm:text-sm font-semibold focus:outline-none focus:border-pastel-sage-400 focus:ring-4 focus:ring-pastel-sage-100 transition-all shadow-sm">
                            <template x-for="n in 19" :key="n + 1">
                                <option :value="n + 1" :selected="optionsCount === (n + 1)" x-text="(n + 1) + ' Pilihan (A s/d ' + letters[n] + ')'"></option>
                            </template>
                        </select>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Dapat disesuaikan bebas antara 2 hingga 20 opsi jawaban.</p>
                </div>

                <!-- Masa Aktif -->
                <div>
                    <label for="starts_at" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Waktu Mulai Kuis (Opsional)
                    </label>
                    <input type="datetime-local" 
                           id="starts_at" 
                           name="starts_at" 
                           value="{{ old('starts_at') }}"
                           class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none focus:border-pastel-sage-400 focus:ring-4 focus:ring-pastel-sage-100 transition-all shadow-sm">
                    <p class="text-[11px] text-slate-400 mt-1">Kosongkan jika kuis bisa langsung dikerjakan sekarang.</p>
                </div>

                <div>
                    <label for="expires_at" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Masa Aktif Berakhir (Opsional)
                    </label>
                    <input type="datetime-local" 
                           id="expires_at" 
                           name="expires_at" 
                           value="{{ old('expires_at') }}"
                           class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none focus:border-pastel-sage-400 focus:ring-4 focus:ring-pastel-sage-100 transition-all shadow-sm">
                    <p class="text-[11px] text-slate-400 mt-1">Setelah tanggal ini, kuis ditutup otomatis.</p>
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Deskripsi / Petunjuk untuk Peserta (Opsional)
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="2" 
                              placeholder="Tuliskan petunjuk pengerjaan atau catatan singkat untuk peserta..."
                              class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none focus:border-pastel-sage-400 focus:ring-4 focus:ring-pastel-sage-100 transition-all shadow-sm">{{ old('description') }}</textarea>
                </div>

            </div>
        </div>

        <!-- 2. OPSI RANDOMISASI (Sesuai Diagram Alur) -->
        <div class="glass-card p-6 sm:p-8 rounded-3xl shadow-soft border border-white/90">
            <h3 class="font-display font-bold text-base text-slate-800 mb-2 pb-2 border-b border-slate-100 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-pastel-sky-200 text-pastel-sky-800 text-xs font-bold flex items-center justify-center">2</span>
                Opsi Acak Urutan Soal & Pilihan Jawaban
            </h3>
            <p class="text-xs text-slate-500 mb-4">Fitur anti-contek: setiap peserta akan menerima urutan yang berbeda jika fitur diaktifkan.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- Random Soal -->
                <label class="p-4 rounded-2xl border-2 transition-all flex items-start gap-3 cursor-pointer"
                       :class="shuffleQuestions ? 'border-pastel-sage-400 bg-pastel-sage-50/70' : 'border-slate-200 bg-white hover:border-slate-300'">
                    <input type="checkbox" name="shuffle_questions" value="1" x-model="shuffleQuestions" class="mt-1 w-4 h-4 rounded text-pastel-sage-500 border-slate-300 focus:ring-pastel-sage-400">
                    <div>
                        <span class="block text-xs font-bold text-slate-800">Acak Urutan Soal</span>
                        <span class="text-[11px] text-slate-500 leading-snug">Urutan nomor soal diacak secara dinamis untuk tiap peserta.</span>
                    </div>
                </label>

                <!-- Random Opsi Jawaban -->
                <label class="p-4 rounded-2xl border-2 transition-all flex items-start gap-3 cursor-pointer"
                       :class="shuffleOptions ? 'border-pastel-sky-400 bg-pastel-sky-50/70' : 'border-slate-200 bg-white hover:border-slate-300'">
                    <input type="checkbox" name="shuffle_options" value="1" x-model="shuffleOptions" class="mt-1 w-4 h-4 rounded text-pastel-sky-500 border-slate-300 focus:ring-pastel-sky-400">
                    <div>
                        <span class="block text-xs font-bold text-slate-800">Acak Pilihan Jawaban (A, B, C, D, E)</span>
                        <span class="text-[11px] text-slate-500 leading-snug">Tata letak opsi opsi A s.d E diacak posisinya untuk mencegah menyontek kancing.</span>
                    </div>
                </label>

            </div>
        </div>

        <!-- 3. IMPORT TEMPLATE EXCEL & TENTUKAN KOLOM SOAL (Sesuai Diagram Alur) -->
        <div class="glass-card p-6 sm:p-8 rounded-3xl shadow-soft border border-white/90">
            <div class="flex items-center justify-between mb-2 pb-2 border-b border-slate-100 flex-wrap gap-2">
                <h3 class="font-display font-bold text-base text-slate-800 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-pastel-amber-200 text-pastel-amber-800 text-xs font-bold flex items-center justify-center">3</span>
                    Import Template Excel & Mapping Kolom
                </h3>
                <a :href="'{{ route('quizzes.download-template') }}?options_count=' + optionsCount" 
                   class="px-3.5 py-1.5 rounded-xl bg-pastel-sage-100/80 hover:bg-pastel-sage-200/80 border border-pastel-sage-300 text-xs text-pastel-sage-700 font-semibold inline-flex items-center gap-1.5 shadow-xs transition-all">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i> 
                    <span>Unduh Template Excel (<span x-text="optionsCount"></span> Pilihan)</span>
                </a>
            </div>
            <p class="text-xs text-slate-500 mb-5">
                Unggah file Excel (.xlsx / .csv), lalu tentukan kolom soal, pilihan jawaban (A s/d <span x-text="letters[optionsCount - 1]"></span>), dan kunci jawaban.
            </p>

            <!-- File Upload Dropzone -->
            <div class="border-2 border-dashed rounded-3xl p-6 text-center transition-all"
                 :class="isUploading ? 'border-pastel-sage-400 bg-pastel-sage-50/50' : 'border-slate-200 bg-slate-50/40 hover:border-pastel-sage-300'">
                
                <input type="file" id="excel_file_input" accept=".xlsx,.xls,.csv" @change="uploadAndPreview($event)" class="hidden">
                
                <div x-show="!uploadedFilePath && !isUploading">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-pastel-sky-100 text-pastel-sky-600 flex items-center justify-center mb-2">
                        <i data-lucide="file-spreadsheet" class="w-6 h-6"></i>
                    </div>
                    <label for="excel_file_input" class="cursor-pointer text-xs font-bold text-pastel-sage-600 hover:text-pastel-sage-700 underline">
                        Klik untuk memilih file Excel (.xlsx / .csv)
                    </label>
                    <p class="text-[11px] text-slate-400 mt-1">Maksimal ukuran file: 10 MB</p>
                </div>

                <div x-show="isUploading" class="py-3">
                    <div class="animate-spin w-8 h-8 mx-auto border-3 border-pastel-sage-500 border-t-transparent rounded-full mb-2"></div>
                    <p class="text-xs font-semibold text-slate-700">Membaca struktur tabel Excel...</p>
                </div>

                <div x-show="uploadedFilePath && !isUploading" class="flex items-center justify-between p-3 rounded-2xl bg-white border border-slate-200">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="check" class="w-5 h-5"></i>
                        </div>
                        <div class="text-left">
                            <span class="block text-xs font-bold text-slate-800" x-text="uploadedFileName"></span>
                            <span class="text-[10px] text-slate-400" x-text="totalRows + ' baris soal terdeteksi'"></span>
                        </div>
                    </div>
                    <label for="excel_file_input" class="cursor-pointer px-3 py-1.5 rounded-xl border border-slate-200 text-[11px] font-semibold text-slate-600 hover:bg-slate-50">
                        Ganti File
                    </label>
                </div>
            </div>

            <!-- Mapping Column Dropdowns (Muncul setelah file diupload) -->
            <input type="hidden" name="import_file_path" :value="uploadedFilePath">

            <div x-show="uploadedFilePath" x-transition class="mt-6 p-5 rounded-2xl bg-slate-50/70 border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <i data-lucide="sliders" class="w-4 h-4 text-pastel-sage-500"></i>
                        Tentukan Kolom Soal & Pilihan Jawaban
                    </h4>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-pastel-sage-100 text-pastel-sage-700 font-semibold">Mapping Otomatis Sesuai Template</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5">
                    
                    <!-- Soal -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Kolom Soal Pertanyaan <span class="text-rose-500">*</span></label>
                        <select name="col_question" x-model="mapping.question" class="w-full text-xs p-2.5 rounded-xl border border-slate-200 bg-white">
                            <template x-for="(label, col) in availableHeaders" :key="col">
                                <option :value="col" x-text="col + ': ' + label"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Kunci Jawaban -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">
                            Kolom Kunci Jawaban (A-<span x-text="letters[optionsCount - 1]"></span>) <span class="text-rose-500">*</span>
                        </label>
                        <select name="col_key" x-model="mapping.key" class="w-full text-xs p-2.5 rounded-xl border border-slate-200 bg-white">
                            <template x-for="(label, col) in availableHeaders" :key="col">
                                <option :value="col" x-text="col + ': ' + label"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Poin -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Kolom Bobot Poin (Opsional)</label>
                        <select name="col_points" x-model="mapping.points" class="w-full text-xs p-2.5 rounded-xl border border-slate-200 bg-white">
                            <option value="">-- Default (10 poin) --</option>
                            <template x-for="(label, col) in availableHeaders" :key="col">
                                <option :value="col" x-text="col + ': ' + label"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Pilihan Ganda Dinamis Sesuai Jumlah Opsi (2 s/d 20) -->
                    <template x-for="idx in optionsCount" :key="idx">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">
                                Kolom Pilihan <span x-text="letters[idx - 1]"></span>
                                <span class="text-rose-500" x-show="idx <= 2">*</span>
                            </label>
                            <select :name="'col_opt_' + letters[idx - 1].toLowerCase()" 
                                    x-model="mapping['opt_' + letters[idx - 1].toLowerCase()]" 
                                    class="w-full text-xs p-2.5 rounded-xl border border-slate-200 bg-white">
                                <option value="" x-show="idx > 2">-- Kosong --</option>
                                <template x-for="(label, col) in availableHeaders" :key="col">
                                    <option :value="col" x-text="col + ': ' + label"></option>
                                </template>
                            </select>
                        </div>
                    </template>

                </div>

                <!-- Preview Mini Table -->
                <div class="mt-4 pt-3 border-t border-slate-200/80">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-2">Pratinjau 3 Baris Pertama:</span>
                    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                        <table class="w-full text-left text-[11px]">
                            <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                                <tr>
                                    <template x-for="(hdr, col) in availableHeaders" :key="col">
                                        <th class="px-3 py-1.5 font-bold" x-text="col + ' (' + hdr + ')'"></th>
                                    </template>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, idx) in previewRows.slice(0, 3)" :key="idx">
                                    <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">
                                        <template x-for="(hdr, col) in availableHeaders" :key="col">
                                            <td class="px-3 py-1.5 text-slate-600 truncate max-w-xs" x-text="row[col] || '-'"></td>
                                        </template>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('quizzes.index') }}" class="px-6 py-3 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                Batal
            </a>

            <button type="submit" 
                    class="px-8 py-3.5 rounded-2xl bg-gradient-to-r from-pastel-sage-500 to-pastel-sage-600 hover:from-pastel-sage-600 hover:to-pastel-sage-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-pastel-sage-500/20 hover:shadow-lg transition-all flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>Simpan & Terbitkan Kuis</span>
            </button>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
    function quizCreator() {
        return {
            optionsCount: {{ old('options_count', 5) }},
            letters: ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T'],
            shuffleQuestions: {{ old('shuffle_questions') ? 'true' : 'false' }},
            shuffleOptions: {{ old('shuffle_options') ? 'true' : 'false' }},
            isUploading: false,
            uploadedFilePath: '',
            uploadedFileName: '',
            totalRows: 0,
            availableHeaders: {},
            previewRows: [],
            mapping: {
                question: 'A',
                opt_a: 'B',
                opt_b: 'C',
                opt_c: 'D',
                opt_d: 'E',
                opt_e: 'F',
                key: 'G',
                points: 'H'
            },

            init() {
                this.initDefaultMapping();
            },

            onOptionsCountChange() {
                if (Object.keys(this.availableHeaders).length > 0) {
                    this.autoAssignMapping();
                } else {
                    this.initDefaultMapping();
                }
                this.$nextTick(() => lucide.createIcons());
            },

            initDefaultMapping() {
                const map = {
                    question: 'A',
                    key: '',
                    points: ''
                };
                for (let i = 0; i < this.optionsCount; i++) {
                    const colLetter = String.fromCharCode(66 + i); // 66 = B
                    const optKey = this.letters[i].toLowerCase();
                    map['opt_' + optKey] = colLetter;
                }
                map.key = String.fromCharCode(66 + this.optionsCount);
                map.points = String.fromCharCode(66 + this.optionsCount + 1);
                this.mapping = map;
            },

            autoAssignMapping() {
                const keys = Object.keys(this.availableHeaders);
                if (keys.length === 0) return;

                const map = {
                    question: keys[0] || 'A',
                    key: '',
                    points: ''
                };

                for (let i = 0; i < this.optionsCount; i++) {
                    const optKey = this.letters[i].toLowerCase();
                    map['opt_' + optKey] = keys[i + 1] || '';
                }

                // Kolom kunci & poin
                map.key = keys[this.optionsCount + 1] || '';
                map.points = keys[this.optionsCount + 2] || '';
                this.mapping = map;
            },

            generateNewCode() {
                const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                let code = '';
                for (let i = 0; i < 6; i++) {
                    code += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                document.getElementById('code').value = code;
            },

            uploadAndPreview(event) {
                const file = event.target.files[0];
                if (!file) return;

                this.isUploading = true;
                this.uploadedFileName = file.name;

                const formData = new FormData();
                formData.append('excel_file', file);
                formData.append('_token', '{{ csrf_token() }}');

                fetch('{{ route('quizzes.preview-import') }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    this.isUploading = false;
                    if (data.success) {
                        this.uploadedFilePath = data.file_path;
                        this.availableHeaders = data.headers;
                        this.previewRows = data.preview_rows;
                        this.totalRows = data.total_rows;

                        this.autoAssignMapping();
                        this.$nextTick(() => lucide.createIcons());
                    } else {
                        alert(data.message || 'Gagal membaca file Excel.');
                    }
                })
                .catch(err => {
                    this.isUploading = false;
                    alert('Terjadi kesalahan saat mengunggah file.');
                    console.error(err);
                });
            }
        };
    }
</script>
@endpush
