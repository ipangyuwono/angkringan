<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { Plus, Trash2, CheckCircle, AlertCircle, Wallet } from 'lucide-vue-next';
import * as hutangWay from '@/routes/hutang';

interface Hutang {
    id: number;
    nama_penjual: string;
    tanggal: string;
    jumlah_hutang: number;
    jumlah_bayar: number;
    sisa: number;
    keterangan: string | null;
    status: 'belum' | 'lunas';
    tanggal_lunas: string | null;
}

const props = defineProps<{
    hutangs: Hutang[];
    total_belum_lunas: number;
    total_lunas: number;
}>();

const isModalOpen = ref(false);
const isBayarModalOpen = ref(false);
const selectedHutang = ref<Hutang | null>(null);

const form = useForm({
    nama_penjual  : '',
    tanggal       : new Date().toISOString().split('T')[0],
    jumlah_hutang : 0,
    keterangan    : '',
});

const bayarForm = useForm({
    jumlah_bayar: 0,
});

const submitForm = () => {
    form.post(hutangWay.store.url(), {
        onSuccess: () => { isModalOpen.value = false; form.reset(); },
    });
};

const openBayar = (hutang: Hutang) => {
    selectedHutang.value = hutang;
    bayarForm.jumlah_bayar = hutang.sisa;
    isBayarModalOpen.value = true;
};

const submitBayar = () => {
    if (!selectedHutang.value) return;
    bayarForm.post(hutangWay.bayar.url(selectedHutang.value.id), {
        onSuccess: () => { isBayarModalOpen.value = false; bayarForm.reset(); },
    });
};

const deleteHutang = (hutang: Hutang) => {
    if (confirm(`Hapus hutang "${hutang.nama_penjual}"?`)) {
        router.delete(hutangWay.destroy.url(hutang.id));
    }
};

const formatCurrency = (value: number) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);

const formatDate = (date: string) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Manajemen Hutang" />

    <!-- ── STAT CARDS ── -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon-wrap red-icon"><AlertCircle :size="22" /></div>
            <div class="stat-body">
                <p class="stat-label">Total Hutang Belum Lunas</p>
                <h3 class="stat-value">{{ formatCurrency(props.total_belum_lunas) }}</h3>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap green-icon"><CheckCircle :size="22" /></div>
            <div class="stat-body">
                <p class="stat-label">Total Sudah Lunas</p>
                <h3 class="stat-value">{{ props.total_lunas }} transaksi</h3>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap blue-icon"><Wallet :size="22" /></div>
            <div class="stat-body">
                <p class="stat-label">Total Catatan</p>
                <h3 class="stat-value">{{ props.hutangs.length }} catatan</h3>
            </div>
        </div>
    </div>

    <!-- ── TABLE PANEL ── -->
    <div class="argon-panel">
        <div class="panel-head">
            <div>
                <h5 class="panel-title">Catatan Hutang Penjual</h5>
            </div>
            <button @click="isModalOpen = true" class="btn-primary">
                <Plus :size="15" />
                Tambah Hutang
            </button>
        </div>

        <div class="table-wrap">
            <table class="argon-table">
                <thead>
                    <tr style="text-align: left;">
                        <th>Tanggal</th>
                        <th>Nama Penjual</th>
                        <th>Total Hutang</th>
                        <th>Sudah Bayar</th>
                        <th>Sisa</th>
                        <th>Keterangan</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="h in props.hutangs" :key="h.id"
                        :class="{ 'row-lunas': h.status === 'lunas' }" class="data-row">
                        <!-- Date First Column -->
                        <td>
                            <div class="date-cell">
                                <span class="date-text">{{ formatDate(h.tanggal) }}</span>
                                <span v-if="h.keterangan?.includes('Sesi')" class="date-tag">Transaksi 4</span>
                            </div>
                        </td>
                        <!-- Name Second Column -->
                        <td>
                            <div class="user-cell">
                                <div class="user-info">
                                    <span class="user-name">{{ h.nama_penjual }}</span>
                                    <span class="user-sub text-muted">Penjual</span>
                                </div>
                            </div>
                        </td>
                        <td class="text-danger fw-700">{{ formatCurrency(h.jumlah_hutang) }}</td>
                        <td class="text-green fw-600">{{ formatCurrency(h.jumlah_bayar) }}</td>
                        <td>
                            <div class="sisa-box" :class="{ 'sisa-none': h.sisa <= 0 }">
                                {{ formatCurrency(h.sisa) }}
                            </div>
                        </td>
                        <td class="text-muted italic">{{ h.keterangan && !h.keterangan.includes('Sesi') ? h.keterangan : '-' }}</td>
                        <td class="text-center">
                            <span :class="['status-badge', h.status === 'lunas' ? 'lunas' : 'belum']">
                                {{ h.status === 'lunas' ? '✓ Lunas' : '⏳ Menunggu' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="action-btns">
                                <button
                                    v-if="h.status === 'belum'"
                                    @click="openBayar(h)"
                                    class="btn-icon bayar"
                                    title="Catat Pembayaran"
                                >
                                    <CheckCircle :size="13" />
                                </button>
                                <button @click="deleteHutang(h)" class="btn-icon del" title="Hapus">
                                    <Trash2 :size="13" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="props.hutangs.length === 0">
                        <td colspan="8" class="empty-row text-center">Belum ada catatan hutang.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── MODAL TAMBAH HUTANG ── -->
    <div v-if="isModalOpen" class="modal-overlay" @click.self="isModalOpen = false">
        <div class="modal-box">
            <div class="modal-head">
                <h5>Tambah Catatan Hutang</h5>
                <button @click="isModalOpen = false" class="modal-close">✕</button>
            </div>
            <form @submit.prevent="submitForm" class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Penjual</label>
                    <input v-model="form.nama_penjual" class="form-input" placeholder="Nama penjual..." />
                    <span v-if="form.errors.nama_penjual" class="form-error">{{ form.errors.nama_penjual }}</span>
                </div>
                <div class="form-row-2">
                    <div class="form-group">
                        <label class="form-label">Tanggal</label>
                        <input type="date" v-model="form.tanggal" class="form-input" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jumlah Hutang (Rp)</label>
                        <input type="number" v-model="form.jumlah_hutang" class="form-input" min="0" />
                        <span v-if="form.errors.jumlah_hutang" class="form-error">{{ form.errors.jumlah_hutang }}</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Keterangan (opsional)</label>
                    <input v-model="form.keterangan" class="form-input" placeholder="Misal: Kurang setor sesi 2..." />
                </div>
                <div class="modal-footer">
                    <button type="button" @click="isModalOpen = false" class="btn-outline">Batal</button>
                    <button type="submit" class="btn-primary" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── MODAL BAYAR ── -->
    <div v-if="isBayarModalOpen" class="modal-overlay" @click.self="isBayarModalOpen = false">
        <div class="modal-box">
            <div class="modal-head">
                <h5>Catat Pembayaran</h5>
                <button @click="isBayarModalOpen = false" class="modal-close">✕</button>
            </div>
            <form @submit.prevent="submitBayar" class="modal-body">
                <div class="info-box" v-if="selectedHutang">
                    <div class="info-row">
                        <span>Penjual</span>
                        <strong>{{ selectedHutang.nama_penjual }}</strong>
                    </div>
                    <div class="info-row">
                        <span>Sisa Hutang</span>
                        <strong class="text-danger">{{ formatCurrency(selectedHutang.sisa) }}</strong>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Jumlah Bayar (Rp)</label>
                    <input type="number" v-model="bayarForm.jumlah_bayar" class="form-input" min="1" />
                    <span v-if="bayarForm.errors.jumlah_bayar" class="form-error">{{ bayarForm.errors.jumlah_bayar }}</span>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="isBayarModalOpen = false" class="btn-outline">Batal</button>
                    <button type="submit" class="btn-primary" :disabled="bayarForm.processing">
                        {{ bayarForm.processing ? 'Menyimpan...' : 'Catat Bayar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
.stats-row {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 16px; margin-bottom: 22px;
}
.stat-card {
    background: #fff; border-radius: 14px; padding: 18px 20px;
    display: flex; align-items: center; gap: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06); border-left: 4px solid transparent;
}
.stat-card.red   { border-left-color: #ef4444; }
.stat-card.green { border-left-color: #22c55e; }
.stat-card.blue  { border-left-color: #3b82f6; }
.stat-icon-wrap {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.red-icon   { background: #fee2e2; color: #ef4444; }
.green-icon { background: #dcfce7; color: #16a34a; }
.blue-icon  { background: #dbeafe; color: #2563eb; }
.stat-label { font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; margin: 0 0 4px; }
.stat-value { font-size: 20px; font-weight: 800; color: #111827; margin: 0; }

.argon-panel { background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06); overflow: hidden; }
.panel-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 22px; border-bottom: 1px solid #f3f4f6;
}
.panel-title   { font-size: 15px; font-weight: 700; color: #111827; margin: 0 0 2px; }
.panel-subtitle{ font-size: 12px; color: #9ca3af; margin: 0; }

.table-wrap { overflow-x: auto; }
.argon-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.argon-table thead tr { background: #f8fafc; }
.argon-table th {
    padding: 12px 16px; font-size: 11px; font-weight: 700; color: #6b7280;
    text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #f3f4f6;
}
.argon-table td { padding: 14px 16px; border-bottom: 1px solid #f9fafb; color: #374151; vertical-align: middle; }
.argon-table tbody tr:hover { background: #f8fafc; }
.row-lunas { opacity: 0.6; background: #fafafa; }
.fw-600  { font-weight: 600; color: #111827; }
.fw-700  { font-weight: 700; color: #111827; }
.text-muted  { color: #94a3b8; font-size: 11.5px; }
.italic { font-style: italic; }
.text-danger { color: #ef4444; }
.text-green  { color: #16a34a; }
.text-center { text-align: center; }
.text-right  { text-align: right; }

/* Custom Cells */
.user-cell { display: flex; align-items: center; gap: 10px; }
.user-avatar {
    width: 34px; height: 34px; border-radius: 50%; background: #f0fdf4; color: #16a34a;
    display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12.5px;
    border: 1.5px solid #dcfce7;
}
.user-info { display: flex; flex-direction: column; line-height: 1.25; }
.user-name { font-weight: 700; color: #111827; font-size: 13.2px; }
.user-sub  { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }

.date-cell { display: flex; flex-direction: column; line-height: 1.35; }
.date-text { font-weight: 600; color: #111827; font-size: 13px; }
.date-tag  { 
    font-size: 10px; font-weight: 800; color: #fff; background: #16a34a; 
    padding: 1px 6px; border-radius: 4px; width: fit-content; text-transform: uppercase;
    margin-top: 3px;
}

.sisa-box {
    display: inline-block; padding: 5px 12px; border-radius: 10px;
    background: #111827; color: #fff;
    font-weight: 800; font-size: 13px;
}
.sisa-none { background: #f1f5f9; color: #94a3b8; border: 1px dashed #cbd5e1; }

.status-badge {
    display: inline-block; padding: 4px 12px; border-radius: 20px;
    font-size: 11.5px; font-weight: 700;
}
.status-badge.lunas { background: #dcfce7; color: #16a34a; }
.status-badge.belum { background: #fee2e2; color: #ef4444; }

.action-btns { display: flex; justify-content: flex-end; gap: 6px; }
.btn-icon {
    width: 30px; height: 30px; border-radius: 7px; border: 1px solid #e5e7eb;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; background: #fff; transition: all 0.18s;
}
.btn-icon.bayar { color: #16a34a; }
.btn-icon.bayar:hover { background: #dcfce7; border-color: #bbf7d0; }
.btn-icon.del   { color: #ef4444; }
.btn-icon.del:hover   { background: #fef2f2; border-color: #fecaca; }

.empty-row { padding: 48px; text-align: center; color: #9ca3af; font-style: italic; }

.btn-primary {
    display: flex; align-items: center; gap: 6px; padding: 9px 16px;
    background: linear-gradient(135deg, #22c55e, #16a34a); color: white;
    border: none; border-radius: 8px; font-size: 13px; font-weight: 600;
    cursor: pointer; font-family: inherit; transition: all 0.2s;
}
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(22,163,74,0.3); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
.btn-outline {
    padding: 9px 16px; background: transparent; border: 1px solid #e5e7eb;
    border-radius: 8px; font-size: 13px; font-weight: 600;
    cursor: pointer; color: #6b7280; font-family: inherit;
}

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
}
.modal-body { padding: 20px 22px; display: flex; flex-direction: column; gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-label { font-size: 12.5px; font-weight: 600; color: #374151; }
.form-input {
    padding: 9px 13px; border: 1px solid #e5e7eb; border-radius: 9px;
    font-size: 13.5px; outline: none; font-family: inherit; width: 100%; box-sizing: border-box;
}
.form-input:focus { border-color: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,0.1); }
.form-error { font-size: 11.5px; color: #ef4444; }
.modal-footer {
    display: flex; justify-content: flex-end; gap: 8px;
    padding-top: 4px; border-top: 1px solid #f3f4f6; margin-top: 4px;
}

.info-box {
    background: #f8fafc; border-radius: 10px; padding: 12px 16px;
    display: flex; flex-direction: column; gap: 8px; border: 1px solid #e5e7eb;
}
.info-row {
    display: flex; justify-content: space-between; align-items: center;
    font-size: 13px; color: #6b7280;
}
</style>