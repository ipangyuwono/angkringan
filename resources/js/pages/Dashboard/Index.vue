<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { TrendingUp, Calendar, ShoppingBag, Users } from 'lucide-vue-next';
import * as barangRoutes from '@/routes/barang';
import * as transaksiRoutes from '@/routes/transaksi';
import { onMounted, ref } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps<{
    total_hari_ini: number;
    total_bulan_ini: number;
    persentase_bulan_ini: number;
    chart_7_hari: { tanggal: string; total: number }[];
    chart_6_bulan: { bulan: string; total: number }[];
    top_menu: { nama_barang: string; terjual: number }[];
    history_harga: { 
        id: number; 
        nama_barang: string; 
        harga_lama: number; 
        harga_baru: number; 
        updated_at: string 
    }[];
}>();

const chartRef = ref<HTMLCanvasElement | null>(null);
const chart6BulanRef = ref<HTMLCanvasElement | null>(null);
const topMenuRef = ref<HTMLCanvasElement | null>(null);

onMounted(() => {
    // 7 Hari Line Chart
    if (chartRef.value) {
    new Chart(chartRef.value, {
        type: 'line',
        data: {
            labels: props.chart_7_hari?.map((d) => d.tanggal) || [],
            datasets: [
                {
                    label: 'Omzet (Rp)',
                    data: props.chart_7_hari?.map((d) => d.total) || [],
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(34,197,94,0.1)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#16a34a',
                    pointRadius: 4,
                    fill: true,
                    tension: 0.4,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx: any) =>
                            'Rp ' + (ctx.parsed.y || 0).toLocaleString('id-ID'),
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (val) =>
                            'Rp ' + Number(val).toLocaleString('id-ID'),
                        font: { size: 10 },
                    },
                    grid: { color: '#f3f4f6' },
                },
                x: {
                    ticks: { font: { size: 10 } },
                    grid: { display: false },
                },
            },
        },
    });
    }

    // 6 Bulan Line Chart
    if (chart6BulanRef.value) {
        new Chart(chart6BulanRef.value, {
            type: 'line',
            data: {
                labels: props.chart_6_bulan?.map((d) => d.bulan).reverse() || [],
                datasets: [
                    {
                        label: 'Omzet',
                        data: props.chart_6_bulan?.map((d) => d.total).reverse() || [],
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        borderWidth: 2.5,
                        pointBackgroundColor: '#2563eb',
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx: any) => 'Rp ' + (ctx.parsed.y || 0).toLocaleString('id-ID'),
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (val) => 'Rp ' + Number(val).toLocaleString('id-ID'),
                            font: { size: 10 },
                        },
                        grid: { color: '#f3f4f6' },
                    },
                    x: {
                        ticks: { font: { size: 10 } },
                        grid: { display: false },
                    },
                },
            },
        });
    }

    // Top 5 Menu Doughnut Chart
    if (topMenuRef.value) {
        new Chart(topMenuRef.value, {
            type: 'doughnut',
            data: {
                labels: props.top_menu?.map((d) => d.nama_barang) || [],
                datasets: [
                    {
                        data: props.top_menu?.map((d) => d.terjual) || [],
                        backgroundColor: ['#22c55e', '#3b82f6', '#f59e0b', '#eab308', '#a855f7'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { boxWidth: 12, usePointStyle: true, font: { size: 11 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx: any) => ` ${ctx.label}: ${ctx.parsed} terjual`,
                        },
                    },
                },
            },
        });
    }
});

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const formatDate = (dateString: string) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date).replace('.', ':');
};
</script>

<template>
    <Head title="Dashboard" />

    <!-- ── STAT CARDS ROW ── -->
    <div class="stats-row">
        <!-- Omzet Hari Ini -->
        <div class="stat-card">
            <div class="stat-icon-wrap green">
                <TrendingUp class="stat-icon" />
            </div>
            <div class="stat-body">
                <p class="stat-label">Omzet Hari Ini</p>
                <h3 class="stat-value">
                    {{ formatCurrency(props.total_hari_ini) }}
                </h3>
            </div>
        </div>

        <!-- Omzet Bulan Ini -->
        <div class="stat-card">
            <div class="stat-icon-wrap blue">
                <Calendar class="stat-icon" />
            </div>
            <div class="stat-body">
                <p class="stat-label">Omzet Bulan Ini</p>
                <h3 class="stat-value">
                    {{ formatCurrency(props.total_bulan_ini) }}
                </h3>
            </div>
        </div>

        <!-- Total Barang -->
        <div class="stat-card">
            <div class="stat-icon-wrap orange">
                <ShoppingBag class="stat-icon" />
            </div>
            <div class="stat-body">
                <p class="stat-label">Total Menu</p>
                <h3 class="stat-value">18</h3>
            </div>
        </div>

        <!-- Pertumbuhan Bulanan -->
        <div class="stat-card">
            <div class="stat-icon-wrap" :class="props.persentase_bulan_ini >= 0 ? 'green' : 'red'">
                <TrendingUp class="stat-icon" v-if="props.persentase_bulan_ini >= 0" />
                <TrendingUp class="stat-icon" v-else style="transform: rotate(180deg);" />
            </div>
            <div class="stat-body">
                <p class="stat-label">Kenaikan MoM</p>
                <h3 class="stat-value" :class="props.persentase_bulan_ini >= 0 ? 'text-green' : 'text-red'">
                    <span v-if="props.persentase_bulan_ini > 0">+</span>{{ props.persentase_bulan_ini }}%
                </h3>
            </div>
        </div>
    </div>

    <!-- ── ROW 1: Tren 6 Bulan & Top Menu ── -->
    <div class="bottom-row" style="margin-bottom: 20px;">
        <!-- 6 Bulan Panel -->
        <div class="panel activity-panel">
            <div class="panel-header">
                <h5 class="panel-title">Omzet 6 Bulan Terakhir</h5>
            </div>
            <div class="panel-body" style="height: 260px; position: relative">
                <canvas ref="chart6BulanRef"></canvas>
            </div>
        </div>

        <!-- Top 5 Menu Panel -->
        <div class="panel history-panel">
            <div class="panel-header">
                <h5 class="panel-title">Top 5 Menu Terlaris</h5>
                <span class="panel-badge">Bulan Ini</span>
            </div>
            <div class="panel-body" style="height: 260px; position: relative; padding: 20px;">
                <canvas ref="topMenuRef"></canvas>
            </div>
        </div>
    </div>

    <!-- ── ROW 2: 7 Hari & History Harga ── -->
    <div class="bottom-row">
        <!-- Activity Panel -->
        <div class="panel activity-panel">
            <div class="panel-header">
                <h5 class="panel-title">Omzet 7 Hari Terakhir</h5>
                <span class="panel-badge">{{
                    new Date().toLocaleDateString('id-ID', {
                        month: 'long',
                        year: 'numeric',
                    })
                }}</span>
            </div>
            <div class="panel-body" style="height: 260px; position: relative">
                <canvas ref="chartRef"></canvas>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="panel history-panel">
    <div class="panel-header">
        <h5 class="panel-title">Riwayat Perubahan Harga</h5>
        <Link :href="barangRoutes.index.url()" class="view-all-link">Lihat Semua</Link>
    </div>
    
    <div class="panel-body history-list">
        <div v-if="props.history_harga?.length === 0" class="empty-history">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#e5e7eb" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 12px;">
                <path d="M14 2H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="9" y1="15" x2="15" y2="15"></line>
            </svg>  
            <p style="margin: 0; font-size: 13.5px; font-weight: 600; color: #6b7280;">Belum ada riwayat harga</p>
            <p style="margin: 4px 0 0; font-size: 11px; color: #9ca3af;">Perubahan harga barang akan dicatat di sini.</p>
        </div>
        
        <div v-for="item in props.history_harga" :key="item.id" class="history-item">
            <div class="history-indicator" :class="item.harga_baru > item.harga_lama ? 'up' : 'down'">
                <TrendingUp v-if="item.harga_baru > item.harga_lama" :size="14" />
                <TrendingUp v-else :size="14" style="transform: rotate(180deg);" />
            </div>

            <div class="history-content">
                <div class="history-main">
                    <span class="history-name">{{ item.nama_barang }}</span>
                    <span class="history-time">{{ formatDate(item.updated_at) }}</span>
                </div>
        
                <div class="history-details">
                    <span class="old-price">{{ formatCurrency(item.harga_lama) }}</span>
                    <span class="price-arrow">→</span>
                    <span class="new-price">{{ formatCurrency(item.harga_baru) }}</span>
                </div>
            </div> 
        </div> 
    </div> 
</div>
    </div>
</template>

<style scoped>
/* ── STATS ROW ── */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 24px;
}

@media (max-width: 1100px) {
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 600px) {
    .stats-row {
        grid-template-columns: 1fr;
    }
}

.stat-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 20px;
    display: flex;
    align-items: flex-start;
    gap: 16px;
    box-shadow: 0 2px 16px rgba(0, 0, 0, 0.06);
    border: 1px solid rgba(0, 0, 0, 0.04);
    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;
}
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 28px rgba(0, 0, 0, 0.1);
}

.stat-icon-wrap {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
}
.stat-icon-wrap.green {
    background: linear-gradient(135deg, #22c55e, #16a34a);
}
.stat-icon-wrap.red {
    background: linear-gradient(135deg, #ef4444, #dc2626);
}
.stat-icon-wrap.blue {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}
.stat-icon-wrap.orange {
    background: linear-gradient(135deg, #f97316, #ea580c);
}
.stat-icon-wrap.purple {
    background: linear-gradient(135deg, #a855f7, #7c3aed);
}
.stat-icon {
    width: 24px;
    height: 24px;
    color: white;
}

.stat-body {
    flex: 1;
    min-width: 0;
}
.stat-label {
    font-size: 12px;
    color: #9ca3af;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0 0 4px;
}
.stat-value {
    font-size: 20px;
    font-weight: 800;
    color: #111827;
    margin: 0 0 6px;
    letter-spacing: -0.5px;
    line-height: 1.2;
}
.stat-value.text-green { color: #16a34a; }
.stat-value.text-red { color: #dc2626; }
.stat-sub {
    font-size: 11.5px;
    color: #9ca3af;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 5px;
    flex-wrap: wrap;
}
.stat-badge {
    padding: 2px 7px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
}
.badge-green {
    background: #dcfce7;
    color: #16a34a;
}
.badge-blue {
    background: #dbeafe;
    color: #2563eb;
}
.badge-orange {
    background: #ffedd5;
    color: #ea580c;
}
.badge-purple {
    background: #f3e8ff;
    color: #7c3aed;
}

/* ── BOTTOM ROW ── */
.bottom-row {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 20px;
}
@media (max-width: 900px) {
    .bottom-row {
        grid-template-columns: 1fr;
    }
}

.panel {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 2px 16px rgba(0, 0, 0, 0.06);
    border: 1px solid rgba(0, 0, 0, 0.04);
    overflow: hidden;
}
.panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 20px;
    border-bottom: 1px solid #f3f4f6;
}
.panel-title {
    font-size: 14px;
    font-weight: 700;
    color: #111827;
    margin: 0;
}
.panel-badge {
    font-size: 10.5px;
    font-weight: 600;
    background: #f0fdf4;
    color: #16a34a;
    border: 1px solid #bbf7d0;
    padding: 3px 10px;
    border-radius: 20px;
}
.panel-body {
    padding: 20px;
}

/* Chart Placeholder */
.chart-placeholder {
    height: 260px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: repeating-linear-gradient(
        0deg,
        transparent,
        transparent 39px,
        #f3f4f6 39px,
        #f3f4f6 40px
    );
}
.chart-placeholder-inner {
    text-align: center;
}
.chart-icon {
    font-size: 40px;
    margin-bottom: 12px;
    opacity: 0.5;
}
.chart-msg {
    font-size: 14px;
    font-weight: 600;
    color: #6b7280;
    margin: 0 0 4px;
}
.chart-sub {
    font-size: 12px;
    color: #9ca3af;
    margin: 0;
}

/* Quick Actions */
.action-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.18s ease;
    border: 1px solid #f3f4f6;
    margin-bottom: 10px;
    background: #fafafa;
}
.action-item:last-child {
    margin-bottom: 0;
}
.action-item:hover {
    background: #f0fdf4;
    border-color: #bbf7d0;
    transform: translateX(3px);
}
.action-icon-wrap {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: white;
}
.action-icon-wrap.green {
    background: linear-gradient(135deg, #22c55e, #16a34a);
}
.action-icon-wrap.blue {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}
.action-text {
    flex: 1;
}
.action-title {
    font-size: 13.5px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 2px;
}
.action-sub {
    font-size: 11.5px;
    color: #9ca3af;
}
.action-arrow {
    font-size: 22px;
    color: #d1d5db;
    font-weight: 300;
    transition: all 0.2s;
}
.action-item:hover .action-arrow {
    color: #16a34a;
    transform: translateX(3px);
}
/* ── HISTORY PANEL ── */
.view-all-link {
    font-size: 11px;
    font-weight: 700;
    color: #2563eb;
    text-decoration: none;
    text-transform: uppercase;
}

.history-list {
    padding: 10px 20px 20px;
    max-height: 300px;
    overflow-y: auto;
}

.history-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid #f9fafb;
}

.history-item:last-child {
    border-bottom: none;
}

.history-indicator {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 2px;
}

.history-indicator.up {
    background: #ecfdf5;
    color: #10b981;
}

.history-indicator.down {
    background: #fef2f2;
    color: #ef4444;
}

.history-content {
    flex: 1;
}

.history-main {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2px;
}

.history-name {
    font-size: 13px;
    font-weight: 700;
    color: #1f2937;
}

.history-time {
    font-size: 10px;
    color: #9ca3af;
}

.history-details {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
}

.old-price {
    color: #9ca3af;
    text-decoration: line-through;
}

.price-arrow {
    color: #d1d5db;
}

.new-price {
    font-weight: 700;
    color: #111827;
}

.empty-history {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 30px 20px;
    background: #f9fafb;
    border-radius: 12px;
    border: 1px dashed #e5e7eb;
    margin: 10px 0;
}
</style>
