<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { Printer, Calendar, User, TrendingUp, ShoppingCart, Download, Save, RotateCcw, Eye } from 'lucide-vue-next';
import * as transaksiWay from '@/routes/transaksi';
import Swal from 'sweetalert2';

interface Barang {
    id: number;
    nama_barang: string;
    harga_per_unit: number;
    satuan: string;
}

interface Transaksi {
    id: number | null;
    barang_id: number;
    nama_barang: string;
    harga_per_unit: number;
    jumlah_bawa: number;
    sisa: number;
    jumlah: number;
    catatan: string | null;
}

const props = defineProps<{ 
    barangs: Barang[];
    user_role: string;
    nama_penjual: string;
    user_email: string;
}>();

const initSesi = () => {
    if (props.user_role !== 'admin') {
        if (props.user_email === 'ppang7@gmail.com') return 1;
        if (props.user_email === 'hartanto12@gmail.com') return 2;
        if (props.user_email === 'sarinoang7@gmail.com') return 3;
        if (props.user_email === 'alan7@gmail.com') return 4;
        return 1;
    }
    return 1;
};

const today = new Date().toISOString().split('T')[0];
const selectedDate = ref(today);
const selectedSesi = ref(initSesi());
const transaksis = ref<Transaksi[]>([]);
const totalOmset = ref(0);
const isLoading = ref(false);

// Per-row saving state
const savingRows = ref<Set<number>>(new Set());

const namaInput = ref(props.user_role === 'admin' ? '' : props.nama_penjual);
const setorAmount = ref<number | string>('');
const setorError = ref('');

const sendToSeller = () => {
    Swal.fire({
        title: 'Berhasil!',
        text: 'Data barang bawaan berhasil dikirim ke Penjual!',
        icon: 'success',
        confirmButtonColor: '#16a34a',
    });
};

const fetchTransactions = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(transaksiWay.data.url(selectedDate.value) + `?sesi=${selectedSesi.value}`);
        transaksis.value = response.data.transaksis;
        totalOmset.value = Number(response.data.jumlah_total);
    } catch (error) {
        console.error('Error fetching transactions:', error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(fetchTransactions);
watch([selectedDate, selectedSesi], () => {
    setorAmount.value = '';
    setorError.value = '';
    fetchTransactions();
});

// Called whenever jumlah_bawa or sisa changes
const updateTransaction = async (t: Transaksi, idx: number) => {
    // Optimistically update local jumlah & total
    const rawTerjual = Math.max(0, Number(t.jumlah_bawa) - Number(t.sisa));
    if (t.nama_barang.toLowerCase().includes('areng')) {
        t.jumlah = (rawTerjual / 2.5) * Number(t.harga_per_unit);
    } else {
        t.jumlah = rawTerjual * Number(t.harga_per_unit);
    }

    totalOmset.value = transaksis.value.reduce((acc, cur) => acc + Number(cur.jumlah), 0);

    const isZero = Number(t.jumlah_bawa) === 0 && Number(t.sisa) === 0;

    // Skip if nothing meaningful has been entered (still 0 bawa & 0 sisa)
    if (isZero && t.id === null) return;

    savingRows.value.add(idx);
    try {
        if (isZero && t.id !== null) {
            // Delete from DB because it's reset to 0
            await axios.delete(transaksiWay.destroy.url(t.id));
            t.id = null;
            t.jumlah = 0;
        } else if (t.id === null) {
            // First touch → create
            const res = await axios.post(transaksiWay.store.url(), {
                tanggal: selectedDate.value,
                sesi: selectedSesi.value,
                barang_id: t.barang_id,
                harga_per_unit: t.harga_per_unit,
                jumlah_bawa: t.jumlah_bawa,
                sisa: t.sisa,
                catatan: t.catatan,
            });
            t.id = res.data.transaksi.id;
            t.jumlah = res.data.transaksi.jumlah;
        } else {
            // Already saved → update
            const res = await axios.put(transaksiWay.update.url(t.id), {
                harga_per_unit: t.harga_per_unit,
                jumlah_bawa: t.jumlah_bawa,
                sisa: t.sisa,
                catatan: t.catatan,
            });
            t.jumlah = res.data.transaksi.jumlah;
        }
        // Recompute total from authoritative server values
        totalOmset.value = transaksis.value.reduce((acc, cur) => acc + Number(cur.jumlah), 0);
    } catch (error) {
        console.error('Error saving transaction:', error);
        Swal.fire({
            title: 'Error!',
            text: 'Gagal menyimpan. Silakan coba lagi.',
            icon: 'error',
            confirmButtonColor: '#dc2626',
        });
        fetchTransactions();
    } finally {
        savingRows.value.delete(idx);
    }
};

const resetRow = (t: Transaksi, idx: number) => {
    t.jumlah_bawa = 0;
    t.sisa = 0;
    updateTransaction(t, idx);
};

const downloadPdf = () => {
    const setor = Number(setorAmount.value);
    const isSesi4 = selectedSesi.value === 4;

    if (!setor) {
        setorError.value = 'Masukkan jumlah setor terlebih dahulu';
        return;
    }
    // Sesi 1-3: setor must be >= total. Sesi 4: bebas (boleh kurang)
    if (!isSesi4 && setor < totalOmset.value) {
        setorError.value = `Setor harus ≥ Total Omzet (${formatCurrency(totalOmset.value)})`;
        return;
    }
    setorError.value = '';
    const nameToPrint = props.user_role === 'admin' ? (namaInput.value || '-') : props.nama_penjual;
    const nama = encodeURIComponent(nameToPrint);
    window.open(`${transaksiWay.pdf.url(selectedDate.value)}?sesi=${selectedSesi.value}&setor=${setor}&nama=${nama}`, '_blank');
};

const isPreviewModalOpen = ref(false);
const previewUrl = ref('');

const previewPdf = () => {
    const setor = Number(setorAmount.value);
    const isSesi4 = selectedSesi.value === 4;

    if (!setor) {
        setorError.value = 'Masukkan jumlah setor terlebih dahulu';
        return;
    }
    if (!isSesi4 && setor < totalOmset.value) {
        setorError.value = `Setor harus ≥ Total Omzet (${formatCurrency(totalOmset.value)})`;
        return;
    }
    setorError.value = '';
    const nameToPrint = props.user_role === 'admin' ? (namaInput.value || '-') : props.nama_penjual;
    const nama = encodeURIComponent(nameToPrint);
    previewUrl.value = `${transaksiWay.pdf.url(selectedDate.value)}?sesi=${selectedSesi.value}&setor=${setor}&nama=${nama}&preview=1`;
    isPreviewModalOpen.value = true;
};

const formatCurrency = (value: number) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);

// For sesi 4: kembalian can be negative (hutang)
const sisaKembalian = computed(() => Number(setorAmount.value) - totalOmset.value);
const isNegativeKembalian = computed(() => sisaKembalian.value < 0);

const terjualCount = computed(() =>
    transaksis.value.reduce((acc, t) => acc + Math.max(0, Number(t.jumlah_bawa) - Number(t.sisa)), 0)
);

const savedCount = computed(() => transaksis.value.filter(t => t.id !== null).length);

const formattedDate = computed(() =>
    new Date(selectedDate.value).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
);

// Sesi 4: valid jika setor diisi saja. Sesi 1-3: setor harus >= total
const setorValid = computed(() => {
    const setor = Number(setorAmount.value);
    if (!setor) return false;
    if (selectedSesi.value === 4) return true; // bebas
    return setor >= totalOmset.value;
});
</script>

<template>
    <Head title="Transaksi Harian" />

    <!-- ── TOP CONTROL BAR ── -->
    <div class="control-bar">
        <div class="control-left">
            <div class="date-wrap">
                <Calendar :size="14" class="date-icon" />
                <input type="date" v-model="selectedDate" class="date-input" />
            </div>
            <!-- Sesi Dropdown -->
            <div v-if="props.user_role === 'admin'" class="sesi-wrap">
                <select v-model="selectedSesi" class="sesi-select">
                    <option :value="1">Mas Prihana</option>
                    <option :value="2">Mas Hartanto</option>
                    <option :value="3">Mas Sarino</option>
                    <option :value="4">Mas Alan</option>
                </select>
            </div>
            <div v-else class="sesi-wrap">
                <div class="sesi-select" style="pointer-events: none; background-image: none; padding-right: 12px; color: #16a34a; background-color: #f0fdf4; border-color: #bbf7d0;">
                    Transaksi {{ selectedSesi }}
                </div>
            </div>
        </div>
        <div class="control-right">
            <button v-if="props.user_role === 'admin'" @click="sendToSeller" class="btn-outline-green" style="margin-right: 6px;">
                <Save :size="14" />
                Kirim Transaksi
            </button>
            <button @click="previewPdf" class="btn-outline-green" :disabled="!setorValid" style="margin-right: 6px;">
                <Eye :size="14" />
                Preview
            </button>
            <button @click="downloadPdf" class="btn-outline-green" :disabled="!setorValid">
                <Printer :size="14" />
                Cetak PDF
            </button>
        </div>
    </div>

    <!-- ── STAT CARDS ── -->
    <div class="stats-grid">
        <div class="stat-card green">
            <div class="stat-icon-wrap green-icon">💰</div>
            <div class="stat-body">
                <div class="stat-label">Total Omzet</div>
                <div class="stat-value">{{ formatCurrency(totalOmset) }}</div>
            </div>
        </div>
        <div class="stat-card blue">
            <div class="stat-icon-wrap blue-icon">
                <ShoppingCart :size="20" />
            </div>
            <div class="stat-body">
                <div class="stat-label">Item Tercatat</div>
                <div class="stat-value">{{ savedCount }} <span class="stat-sub">/ {{ transaksis.length }}</span></div>
            </div>
        </div>
        <div class="stat-card orange">
            <div class="stat-icon-wrap orange-icon">
                <TrendingUp :size="20" />
            </div>
            <div class="stat-body">
                <div class="stat-label">Total Terjual</div>
                <div class="stat-value">{{ terjualCount }}</div>
            </div>
        </div>
    </div>

    <!-- ── TABLE PANEL ── -->
    <div class="argon-panel">
        <div class="panel-head">
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <div>
                    <h5 class="panel-title">Catatan Transaksi Harian</h5>
                </div>
                <div class="panel-head-right">
                    <div v-if="props.user_role === 'admin'" class="nama-wrap">
                        <User :size="14" class="nama-icon" />
                        <input
                            type="text"
                            v-model="namaInput"
                            class="nama-input"
                            placeholder="Nama penjual..."
                        />
                    </div>
                </div>
            </div>
        </div>
        <div class="table-wrap">
            <table class="argon-table">
                <thead>
                    <tr>
                        <th class="th-no">No</th>
                        <th class="th-nama">Nama Barang</th>
                        <th class="th-harga">Harga / Unit</th>
                        <th class="th-bawa">
                            <span class="th-badge th-badge-gray">Membawa</span>
                        </th>
                        <th class="th-sisa">
                            <span class="th-badge th-badge-red">Sisa</span>
                        </th>
                        <th class="th-terjual">
                            <span class="th-badge th-badge-blue">Terjual</span>
                        </th>
                        <th class="th-jumlah">
                            <span class="th-badge th-badge-green">Jumlah (Rp)</span>
                        </th>
                        <th class="th-status">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Loading skeleton -->
                    <tr v-if="isLoading" v-for="i in 6" :key="'sk'+i">
                        <td colspan="8"><div class="skeleton-line"></div></td>
                    </tr>

                    <!-- Data rows -->
                    <tr
                        v-if="!isLoading"
                        v-for="(t, idx) in transaksis"
                        :key="t.barang_id"
                        class="data-row"
                        :class="{ 'row-saved': t.id !== null, 'row-empty': t.id === null }"
                    >
                        <td class="td-no">{{ idx + 1 }}</td>
                        <td class="td-nama">
                            <span class="item-name">{{ t.nama_barang }}</span>
                            <span v-if="t.catatan" class="item-note">{{ t.catatan }}</span>
                        </td>
                        <td class="td-harga">{{ formatCurrency(t.harga_per_unit) }}</td>
                        <td class="td-input">
                            <input
                                type="number"
                                step="any"
                                v-model.number="t.jumlah_bawa"
                                @change="updateTransaction(t, idx)"
                                class="inline-input"
                                min="0"
                                :disabled="props.user_role !== 'admin'"
                            />
                        </td>
                        <td class="td-input">
                            <input
                                type="number"
                                step="any"
                                v-model.number="t.sisa"
                                @change="updateTransaction(t, idx)"
                                class="inline-input sisa"
                                min="0"
                            />
                        </td>
                        <td class="td-terjual">
                            <span class="terjual-badge">{{ (Math.max(0, Number(t.jumlah_bawa) - Number(t.sisa))).toLocaleString('id-ID') }}</span>
                        </td>
                        <td class="td-jumlah">{{ formatCurrency(t.jumlah) }}</td>
                        <td class="td-status">
                            <div class="status-action-wrap">
                                <span v-if="savingRows.has(idx)" class="status-saving">
                                    <span class="spinner"></span>
                                </span>
                                <span v-else-if="t.id !== null" class="status-saved" title="Tersimpan">✓</span>
                                <span v-else class="status-empty" title="Belum diisi">–</span>

                                <button v-if="t.id !== null || t.jumlah_bawa > 0 || t.sisa > 0" @click="resetRow(t, idx)" class="btn-reset" title="Reset ke 0">
                                    <RotateCcw :size="14" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>

                <!-- ── TABLE FOOTER: Total + Setor ── -->
                <tfoot v-if="!isLoading && transaksis.length > 0">
                    <!-- Total row -->
                    <tr class="total-row">
                        <td colspan="6" class="total-label">Jumlah  </td>
                        <td class="total-value">{{ formatCurrency(totalOmset) }}</td>
                        <td></td>
                    </tr>

                    <!-- Setor row -->
                    <tr class="setor-row">
                        <td colspan="5"></td>
                        <td class="footer-label-cell">
                            <span class="setor-tag">SETOR</span>
                        </td>
                        <td class="footer-value-cell">
                            <div class="setor-input-wrap" :class="{ error: setorError }">
                                <span class="setor-prefix">Rp</span>
                                <input
                                    type="number"
                                    step="any"
                                    v-model.number="setorAmount"
                                    class="setor-input"
                                    placeholder="0"
                                    min="0"
                                    @input="setorError = ''"
                                />
                            </div>
                            <p v-if="setorError" class="setor-error-msg">⚠ {{ setorError }}</p>
                        </td>
                        <td></td>
                    </tr>
                    <!-- Tabungan / Hutang row -->
                    <tr v-if="setorAmount !== '' && Number(setorAmount) > 0" class="kembalian-row">
                        <td colspan="5"></td>
                        <td class="footer-label-cell">
                            <span
                                v-if="setorValid"
                                class="kembalian-label"
                                :class="{ 'label-hutang': isNegativeKembalian }"
                            >{{ isNegativeKembalian ? 'KURANG' : 'TABUNGAN' }}</span>
                        </td>
                        <td class="footer-value-cell">
                            <div
                                v-if="setorValid"
                                class="kembalian-box"
                                :class="{ 'kembalian-box-hutang': isNegativeKembalian }"
                            >
                                <span
                                    class="kembalian-value"
                                    :class="{ 'value-hutang': isNegativeKembalian }"
                                >{{ formatCurrency(Math.abs(sisaKembalian)) }}</span>
                            </div>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- ── PDF PREVIEW MODAL ── -->
    <div v-if="isPreviewModalOpen" class="modal-overlay" @click.self="isPreviewModalOpen = false">
        <div class="modal-box" style="max-width: 800px; width: 90vw;">
            <div class="modal-head">
                <h5>Preview Transaksi (A6)</h5>
                <button @click="isPreviewModalOpen = false" class="modal-close">✕</button>
            </div>
            <div class="modal-body" style="padding: 0; background: #525659; overflow: hidden; border-bottom-left-radius: 18px; border-bottom-right-radius: 18px;">
                <iframe :src="previewUrl" style="width: 100%; aspect-ratio: 1.414; border: none; display: block;"></iframe>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ── CONTROL BAR ── */
.control-bar {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 18px; gap: 12px; flex-wrap: wrap;
}
.control-left  { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.control-right { display: flex; align-items: center; gap: 10px; }

.date-wrap { position: relative; display: flex; align-items: center; }
.date-icon { position: absolute; left: 10px; color: #9ca3af; pointer-events: none; }
.date-input {
    padding: 9px 13px 9px 30px; border: 1.5px solid #e5e7eb; border-radius: 10px;
    font-size: 13.5px; outline: none; font-family: inherit; transition: all 0.2s; background: #fff;
}
.date-input:focus { border-color: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,0.1); }

/* Sesi select */
.sesi-wrap { display: flex; align-items: center; }
.sesi-select {
    padding: 9px 36px 9px 12px;
    border: 1.5px solid #e5e7eb; border-radius: 10px;
    font-size: 13.5px; outline: none; font-family: inherit;
    background: #fff; cursor: pointer; color: #374151; font-weight: 600;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    transition: all 0.2s;
}
.sesi-select:focus { border-color: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,0.1); }

.nama-wrap { position: relative; display: flex; align-items: center; }
.nama-icon { position: absolute; left: 10px; color: #9ca3af; pointer-events: none; }
.nama-input {
    padding: 9px 13px 9px 30px; border: 1.5px solid #e5e7eb; border-radius: 10px;
    font-size: 13.5px; outline: none; font-family: inherit; transition: all 0.2s;
    background: #fff; width: 180px;
}
.nama-input:focus { border-color: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,0.1); }

.panel-head-right { display: flex; align-items: center; gap: 10px; }

/* ── BUTTONS ── */
.btn-outline-green {
    display: flex; align-items: center; gap: 6px; padding: 9px 16px;
    background: #fff; border: 1.5px solid #bbf7d0; color: #16a34a; border-radius: 10px;
    font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; font-family: inherit;
}
.btn-outline-green:hover:not(:disabled) { background: #f0fdf4; box-shadow: 0 2px 8px rgba(22,163,74,0.15); }
.btn-outline-green:disabled { opacity: 0.5; cursor: not-allowed; }

/* ── STAT CARDS ── */
.stats-grid {
    display: flex; flex-wrap: nowrap;
    gap: 14px; margin-bottom: 20px;
    overflow-x: auto; padding-bottom: 8px;
    -webkit-overflow-scrolling: touch;
}
.stats-grid::-webkit-scrollbar { height: 6px; }
.stats-grid::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
.stat-card {
    flex: 1 1 calc(33.333% - 14px);
    min-width: 240px;
    background: #fff; border-radius: 14px;
    padding: 18px 20px; display: flex; align-items: center; gap: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.05); border: 1px solid #f1f5f9;
    transition: transform 0.2s, box-shadow 0.2s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.08); }

.stat-icon-wrap {
    width: 48px; height: 48px; border-radius: 12px; font-size: 22px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.green-icon { background: #dcfce7; color: #16a34a; }
.blue-icon  { background: #dbeafe; color: #2563eb; }
.orange-icon{ background: #ffedd5; color: #ea580c; }

.stat-label { font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
.stat-value { font-size: 22px; font-weight: 800; color: #111827; letter-spacing: -0.5px; line-height: 1.1; }
.stat-sub   { font-size: 14px; color: #9ca3af; font-weight: 500; }

/* ── PANEL ── */
.argon-panel { background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06); overflow: hidden; }
.panel-head  { padding: 18px 22px; border-bottom: 1px solid #f3f4f6; display: flex; align-items: center; justify-content: space-between; }
.panel-title { font-size: 15px; font-weight: 700; color: #111827; margin: 0 0 2px; }
.panel-subtitle { font-size: 12px; color: #9ca3af; margin: 0; }

/* ── TABLE ── */
.table-wrap { overflow-x: auto; }
.argon-table { width: 100%; border-collapse: collapse; font-size: 13px; }

/* Header */
.argon-table thead tr { background: #f8fafc; }
.argon-table th {
    padding: 13px 16px; font-size: 11px; font-weight: 700; color: #64748b;
    text-transform: uppercase; letter-spacing: 0.5px;
    border-bottom: 2px solid #f1f5f9; white-space: nowrap; text-align: center;
}
.th-nama { text-align: left !important; }
.th-harga { text-align: right !important; }
.th-jumlah { text-align: right !important; }

/* TH badges */
.th-badge {
    display: inline-block; padding: 3px 10px; border-radius: 20px;
    font-size: 10px; font-weight: 800; letter-spacing: 0.4px;
}
.th-badge-gray  { background: #f1f5f9; color: #475569; }
.th-badge-red   { background: #fee2e2; color: #dc2626; }
.th-badge-blue  { background: #dbeafe; color: #2563eb; }
.th-badge-green { background: #dcfce7; color: #16a34a; }

/* Data cells */
.argon-table td { padding: 11px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.data-row:hover { background: #f8fafc; }
.argon-table tbody tr:last-child td { border-bottom: none; }

/* Row state hints */
.row-empty { opacity: 0.72; }
.row-empty .td-harga { color: #cbd5e1; }

.td-no { color: #94a3b8; font-size: 12px; font-weight: 600; text-align: center; width: 48px; }

.td-nama { min-width: 160px; }
.item-name { font-weight: 600; color: #111827; display: block; }
.item-note { font-size: 11px; color: #94a3b8; display: block; margin-top: 2px; font-style: italic; }

.td-harga { color: #64748b; font-size: 13px; text-align: right; white-space: nowrap; }

.td-input { text-align: center; }
.inline-input {
    width: 72px; padding: 7px 8px;
    border: 1.5px solid #e2e8f0; border-radius: 8px;
    text-align: center; font-size: 13.5px; font-weight: 700; font-family: inherit;
    outline: none; transition: all 0.18s; background: #f8fafc; color: #374151;
}
.inline-input:focus { border-color: #22c55e; background: #fff; box-shadow: 0 0 0 3px rgba(34,197,94,0.1); }
.inline-input.sisa { background: #fff5f5; border-color: #fecaca; color: #ef4444; }
.inline-input.sisa:focus { border-color: #ef4444; background: #fff; box-shadow: 0 0 0 3px rgba(239,68,68,0.1); }

.td-terjual { text-align: center; }
.terjual-badge {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 32px; height: 26px; padding: 0 8px;
    background: #dbeafe; color: #1d4ed8; border-radius: 6px;
    font-size: 13px; font-weight: 800;
}

.td-jumlah { text-align: right; font-weight: 700; color: #16a34a; font-size: 13.5px; white-space: nowrap; }

/* Status column */
.td-status { text-align: center; width: 68px; }
.status-action-wrap { display: flex; align-items: center; justify-content: center; gap: 6px; }
.btn-reset {
    background: #fff; border: 1px solid #e5e7eb; color: #9ca3af; border-radius: 6px; cursor: pointer;
    display: flex; align-items: center; justify-content: center; width: 24px; height: 24px;
    transition: all 0.2s;
}
.btn-reset:hover { background: #fee2e2; border-color: #fecaca; color: #ef4444; }

.status-saved {
    display: inline-flex; align-items: center; justify-content: center;
    width: 24px; height: 24px; border-radius: 50%;
    background: #dcfce7; color: #16a34a; font-size: 13px; font-weight: 800;
}
.status-empty {
    display: inline-flex; align-items: center; justify-content: center;
    width: 24px; height: 24px; border-radius: 50%;
    background: #f1f5f9; color: #cbd5e0; font-size: 14px; font-weight: 600;
}
.status-saving {
    display: inline-flex; align-items: center; justify-content: center;
    width: 24px; height: 24px;
}
.spinner {
    width: 16px; height: 16px; border-radius: 50%;
    border: 2.5px solid #bbf7d0; border-top-color: #16a34a;
    animation: spin 0.7s linear infinite; display: inline-block;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── TOTAL ROW ── */
.total-row { background: #f0fdf4; }
.total-row td { border-top: 2px solid #bbf7d0; padding: 13px 16px; }
.total-label { text-align: right; font-size: 11.5px; font-weight: 700; color: #16a34a; text-transform: uppercase; letter-spacing: 0.5px; }
.total-value { text-align: right; font-size: 16px; font-weight: 800; color: #16a34a; white-space: nowrap; }

/* ── SETOR ROW ── */
.setor-row { background: #fffbeb; }
.setor-row td { border-top: 1px solid #fde68a; padding: 12px 16px; vertical-align: middle; }

/* ── SETOR / KEMBALIAN aligned footer ── */
.footer-label-cell {
    text-align: right; vertical-align: middle;
    padding: 10px 12px 10px 8px; white-space: nowrap;
}
.footer-value-cell {
    padding: 10px 16px; vertical-align: middle;
    width: 160px; /* fixed width so both rows align perfectly */
}

.setor-header { display: flex; align-items: center; gap: 8px; }
.setor-tag {
    display: inline-block; padding: 3px 11px; border-radius: 20px;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff; font-size: 11px; font-weight: 800; letter-spacing: 0.5px;
}
.setor-desc { font-size: 11.5px; color: #92400e; font-weight: 500; }

.setor-input-wrap {
    display: flex; align-items: center; gap: 6px;
    background: #fff; border: 1.5px solid #fcd34d; border-radius: 10px;
    padding: 6px 12px; transition: all 0.2s; width: 140px; box-sizing: border-box;
}
.setor-input-wrap:focus-within { border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,0.15); }
.setor-input-wrap.error { border-color: #f87171; }
.setor-prefix { font-size: 12px; font-weight: 800; color: #78350f; white-space: nowrap; }
.setor-input {
    flex: 1; border: none; outline: none; font-size: 15px; font-weight: 800;
    color: #78350f; font-family: inherit; background: transparent; min-width: 0;
}
.setor-error-msg { font-size: 11px; color: #ef4444; font-weight: 600; margin: 4px 0 0; }

.kembalian-box {
    display: flex; align-items: center; justify-content: flex-end;
    width: 100%; box-sizing: border-box;
    background: #e8f8f0; border: 1.5px solid #82f1b5;
    border-radius: 10px; padding: 7px 12px;
}
.kembalian-box-hutang {
    background: #fff1f2; border-color: #fca5a5;
}
.kembalian-label { font-size: 12px; font-weight: 700; color: #058d3d; text-transform: uppercase; letter-spacing: 0.4px; }
.label-hutang { color: #dc2626; }
.kembalian-value { font-size: 14px; font-weight: 800; color: #10be4a; }
.value-hutang { color: #dc2626; }

.btn-cetak {
    display: flex; align-items: center; gap: 5px; padding: 8px 14px;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: #fff; border: none; border-radius: 8px;
    font-size: 12px; font-weight: 700; cursor: pointer;
    transition: all 0.2s; font-family: inherit; margin: auto;
}
.btn-cetak:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(22,163,74,0.35); }
.btn-cetak:disabled { opacity: 0.35; cursor: not-allowed; background: #9ca3af; }

/* Skeleton */
.skeleton-line { height: 16px; background: linear-gradient(90deg, #f3f4f6 25%, #e9ecef 50%, #f3f4f6 75%); border-radius: 6px; animation: shimmer 1.2s infinite; background-size: 200% 100%; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* ── MODAL ── */
.modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.45);
    z-index: 1000; display: flex; align-items: center; justify-content: center;
    backdrop-filter: blur(3px);
}
.modal-box {
    background: #fff; border-radius: 18px; width: 100%; max-width: 440px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    animation: modalIn 0.25s cubic-bezier(0.16,1,0.3,1);
}
@keyframes modalIn {
    from { opacity: 0; transform: scale(0.95) translateY(10px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}
.modal-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 22px; border-bottom: 1px solid #f3f4f6;
}
.modal-head h5 { font-size: 15px; font-weight: 700; color: #111827; margin: 0; }
.modal-close {
    width: 28px; height: 28px; border-radius: 7px; border: 1px solid #e5e7eb;
    background: #fff; cursor: pointer; color: #9ca3af; font-size: 13px;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.15s;
}
.modal-close:hover { background: #fef2f2; color: #ef4444; border-color: #fecaca; }
.modal-body { padding: 20px 22px; display: flex; flex-direction: column; gap: 16px; }

/* Responsive */
@media (max-width: 768px) {
    .nama-input { width: 140px; }
}
</style>
