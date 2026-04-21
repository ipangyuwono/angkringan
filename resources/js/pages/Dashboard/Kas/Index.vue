<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Wallet, Calendar, CheckCircle } from 'lucide-vue-next';
import { ref } from 'vue';

interface Kas {
    id: number;
    tanggal: string;
    sesi: number;
    jumlah: number;
}

const showRekapModal = ref(false);

const props = defineProps<{
    kas_data: Kas[];
    rekap_kas?: { sesi: number; total: number }[];
    total_kas: number;
    is_admin: boolean;
    session: number | null;
}>();

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

const mapSesiToName = (sesi: number | null) => {
    switch (sesi) {
        case 1: return 'Mas Prihana';
        case 2: return 'Mas Hartanto';
        case 3: return 'Mas Sarino';
        case 4: return 'Mas Alan';
        default: return sesi ? `Transaksi ${sesi}` : '';
    }
};
</script>

<template>
    <Head title="Manajemen Kas" />

    <!-- ── STAT CARDS ── -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon-wrap green-icon"><Wallet :size="22" /></div>
            <div class="stat-body">
                <p class="stat-label">Total Nominal Kas</p>
                <h3 class="stat-value">{{ formatCurrency(props.total_kas) }}</h3>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap blue-icon"><CheckCircle :size="22" /></div>
            <div class="stat-body">
                <p class="stat-label">Total Catatan Transaksi</p>
                <h3 class="stat-value">{{ props.kas_data.length }} catatan</h3>
            </div>
        </div>
    </div>

    <!-- ── TABLE PANEL ── -->
    <div class="argon-panel">
        <div class="panel-head">
            <div class="panel-head-content">
                <div>
                    <h5 class="panel-title">Manajemen Kas</h5>
                    <p class="panel-subtitle" v-if="!is_admin">Sewa Gerobak + Kas</p>
                    <p class="panel-subtitle" v-else>Monitoring kas harian seluruh penjual</p>
                </div>

                <button v-if="props.is_admin" @click="showRekapModal = true" class="btn-kpi">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                    <span>Rekap Per Penjual</span>
                </button>
            </div>
        </div>

        <div class="table-wrap">
            <table class="argon-table">
                <thead>
                    <tr style="text-align: left;">
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama Penjual</th>
                        <th class="text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(kas, index) in props.kas_data" :key="kas.id" class="data-row">
                        <td class="fw-600 text-muted" style="width: 50px; text-align: center;">{{ index + 1 }}</td>
                        <td>
                            <div class="date-cell">
                                <span class="date-text">{{ formatDate(kas.tanggal) }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="sesi-badge">{{ mapSesiToName(kas.sesi) }}</span>
                        </td>
                        <td class="text-right text-green fw-700">
                            {{ formatCurrency(kas.jumlah) }}
                        </td>
                    </tr>
                    <tr v-if="props.kas_data.length === 0">
                        <td colspan="4" class="text-center" style="padding: 60px 20px;">
                            <div style="display: flex; flex-direction: column; align-items: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#e5e7eb" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 14px;">
                                    <path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"></path><path d="M4 6v12c0 1.1.9 2 2 2h14v-4"></path><path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z"></path>
                                </svg>
                                <p style="margin: 0; font-size: 14px; font-weight: 600; color: #6b7280;">Belum ada catatan kas</p>
                                <p style="margin: 4px 0 0; font-size: 11.5px; color: #9ca3af;">Transaksi setoran kas akan ditampilkan di tabel ini.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── REKAP MODAL ── -->
    <div v-if="showRekapModal" class="modal-overlay" @click.self="showRekapModal = false">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rekapitulasi Kas Penjual</h5>
                <button class="close-btn" @click="showRekapModal = false">&times;</button>
            </div>
            <div class="modal-body">
                <div class="rekap-list">
                    <div v-for="rekap in props.rekap_kas" :key="rekap.sesi" class="rekap-item">
                        <div class="item-left">
                            <div class="rekap-dot" :class="'sesi-' + rekap.sesi"></div>
                            <span class="rekap-name">{{ mapSesiToName(rekap.sesi) }}</span>
                        </div>
                        <span class="rekap-value">{{ formatCurrency(rekap.total) }}</span>
                    </div>
                </div>
                <div class="total-divider"></div>
                <div class="rekap-total">
                    <span>Total Seluruh Kas</span>
                    <span class="total-value">{{ formatCurrency(props.total_kas) }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.stats-row {
    display: flex; flex-wrap: nowrap;
    gap: 16px; margin-bottom: 22px;
    overflow-x: auto; padding-bottom: 8px;
    -webkit-overflow-scrolling: touch;
}
.stats-row::-webkit-scrollbar { height: 6px; }
.stats-row::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
.stat-card {
    flex: 1 1 calc(50% - 16px);
    min-width: 240px;
    background: #fff; border-radius: 14px; padding: 18px 20px;
    display: flex; align-items: center; gap: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06); border-left: 4px solid transparent;
}
.stat-icon-wrap {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.green-icon { background: #dcfce7; color: #16a34a; }
.blue-icon  { background: #dbeafe; color: #2563eb; }
.stat-label { font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; margin: 0 0 4px; }
.stat-value { font-size: 20px; font-weight: 800; color: #111827; margin: 0; line-height: 1.2; }

.argon-panel { background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06); overflow: hidden; }
.panel-head { padding: 18px 22px; border-bottom: 1px solid #f3f4f6; }
.panel-head-content { display: flex; align-items: center; justify-content: space-between; width: 100%; }
.panel-title   { font-size: 15px; font-weight: 700; color: #111827; margin: 0 0 2px; }
.panel-subtitle { font-size: 12px; color: #9ca3af; margin: 0; }

.btn-kpi {
    display: flex; align-items: center; gap: 8px;
    background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0;
    padding: 7px 14px; border-radius: 10px; font-size: 12px; font-weight: 700;
    cursor: pointer; transition: all 0.2s ease;
}
.btn-kpi:hover {
    background: #16a34a; color: white;
    transform: translateY(-1px); box-shadow: 0 4px 12px rgba(22, 163, 74, 0.2);
}

.table-wrap { overflow-x: auto; }
.argon-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.argon-table thead tr { background: #f8fafc; }
.argon-table th {
    padding: 12px 16px; font-size: 11px; font-weight: 700; color: #6b7280;
    text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #f3f4f6;
}
.argon-table td { padding: 14px 16px; border-bottom: 1px solid #f9fafb; color: #374151; vertical-align: middle; }
.argon-table tbody tr:hover { background: #f8fafc; }

.fw-600  { font-weight: 600; color: #111827; }
.fw-700  { font-weight: 700; color: #111827; }
.text-muted  { color: #94a3b8; font-size: 11.5px; }
.text-green  { color: #16a34a; }
.text-right  { text-align: right; }

.date-cell { display: flex; flex-direction: column; line-height: 1.35; }
.date-text { font-weight: 600; color: #111827; font-size: 13px; }

.sesi-badge {
    display: inline-block; padding: 4px 12px; border-radius: 20px;
    font-size: 11.5px; font-weight: 700;
    background: #dbeafe; color: #018532ff;
}

.empty-row { padding: 48px; text-align: center; color: #9ca3af; font-style: italic; }

/* Modal Styling */
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);
    display: flex; align-items: center; justify-content: center; z-index: 9999;
}
.modal-content {
    background: white; width: 90%; max-width: 400px; border-radius: 20px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    animation: zoomIn 0.2s ease-out;
}
@keyframes zoomIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
.modal-header {
    padding: 20px; border-bottom: 1px solid #f1f5f9;
    display: flex; justify-content: space-between; align-items: center;
}
.modal-title { font-size: 16px; font-weight: 800; color: #1e293b; margin: 0; }
.close-btn { background: none; border: none; font-size: 24px; color: #94a3b8; cursor: pointer; }

.modal-body { padding: 20px; }
.rekap-list { display: flex; flex-direction: column; gap: 14px; }
.rekap-item { display: flex; justify-content: space-between; align-items: center; }
.item-left { display: flex; align-items: center; gap: 12px; }
.rekap-dot { width: 10px; height: 10px; border-radius: 50%; }
.rekap-name { font-weight: 600; color: #64748b; font-size: 13px; }
.rekap-value { font-weight: 700; color: #1e293b; }

.total-divider { height: 1px; background: #f1f5f9; margin: 16px 0; }
.rekap-total { display: flex; justify-content: space-between; align-items: center; font-weight: 800; }
.total-value { color: #16a34a; font-size: 17px; }

.sesi-1 { background: #3b82f6; }
.sesi-2 { background: #10b981; }
.sesi-3 { background: #f59e0b; }
.sesi-4 { background: #ef4444; }
</style>
