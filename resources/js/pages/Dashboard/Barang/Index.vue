<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Search, Download, Eye } from 'lucide-vue-next';
import * as barangWay from '@/routes/barang';
import Swal from 'sweetalert2';

interface Barang {
    id: number;
    nama_barang: string;
    harga_per_unit: number;
    satuan: string;
    is_active: boolean;
}

const props = defineProps<{ barangs: Barang[] }>();

const isModalOpen = ref(false);
const editingBarang = ref<Barang | null>(null);
const searchQuery = ref('');

const form = useForm({
    nama_barang: '',
    harga_per_unit: 0,
    satuan: 'pcs',
    is_active: true,
});

const openCreateModal = () => {
    editingBarang.value = null;
    form.reset();
    isModalOpen.value = true;
};

const openEditModal = (barang: Barang) => {
    editingBarang.value = barang;
    form.nama_barang    = barang.nama_barang;
    form.harga_per_unit = barang.harga_per_unit;
    form.satuan         = barang.satuan;
    form.is_active      = !!barang.is_active;
    isModalOpen.value = true;
};

const submitForm = () => {
    if (editingBarang.value) {
        form.put(barangWay.update.url(editingBarang.value.id), {
            onSuccess: () => { isModalOpen.value = false; },
        });
    } else {
        form.post(barangWay.store.url(), {
            onSuccess: () => { isModalOpen.value = false; form.reset(); },
        });
    }
};

const deleteBarang = (barang: Barang) => {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: `Hapus "${barang.nama_barang}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(barangWay.destroy.url(barang.id));
        }
    });
};

const downloadPdf = () => {
    window.open(barangWay.pdf.url(), '_blank');
};

const isPreviewModalOpen = ref(false);
const previewUrl = ref('');

const previewPdf = () => {
    previewUrl.value = barangWay.pdf.url() + '?preview=1';
    isPreviewModalOpen.value = true;
};

const filteredBarangs = () => {
    if (!searchQuery.value) return props.barangs;
    return props.barangs.filter(b =>
        b.nama_barang.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
};

const formatCurrency = (value: number) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
</script>

<template>
    <Head title="Master Barang" />

    <!-- ── TOP STATS ── -->
    <div class="page-stats-row">
        <div class="pstat-card">
            <div class="pstat-icon">🛍️</div>
            <div>
                <div class="pstat-value">{{ props.barangs.length }}</div>
                <div class="pstat-label">Total Barang</div>
            </div>
        </div>
        <div class="pstat-card">
            <div class="pstat-icon">✅</div>
            <div>
                <div class="pstat-value">{{ props.barangs.filter(b => b.is_active).length }}</div>
                <div class="pstat-label">Barang Aktif</div>
            </div>
        </div>
        <div class="pstat-card">
            <div class="pstat-icon">⏸️</div>
            <div>
                <div class="pstat-value">{{ props.barangs.filter(b => !b.is_active).length }}</div>
                <div class="pstat-label">Non-aktif</div>
            </div>
        </div>
    </div>

    <!-- ── TABLE PANEL ── -->
    <div class="argon-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <h5 class="panel-title">Daftar Master Barang</h5>
            </div>
            <div class="panel-head-right">
                <button @click="previewPdf" class="btn-outline-green" style="margin-right: 6px;">
                    <Eye :size="14" />
                    Preview
                </button>
                <button @click="downloadPdf" class="btn-outline-green">
                    <Download :size="14" />
                    Download Form PDF
                </button>
                <button @click="openCreateModal" class="btn-primary">
                    <Plus :size="15" />
                    Tambah Barang
                </button>
                <div class="search-wrap">
                    <Search :size="14" class="search-icon" />
                    <input v-model="searchQuery" class="search-input" placeholder="Cari barang..." />
                </div>
            </div>
        </div>

        <div class="panel-body">
            <div class="table-wrap">
                <table class="argon-table">
                    <thead>
                        <tr>
                            <th style="width:60px">ID</th>
                            <th>Nama Barang</th>
                            <th>Harga Default</th>
                            <th>Satuan</th>
                            <th style="text-align: center;">Status</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="barang in filteredBarangs()" :key="barang.id">
                            <td class="row-id">{{ barang.id }}</td>
                            <td class="fw-600">{{ barang.nama_barang }}</td>
                            <td>{{ formatCurrency(barang.harga_per_unit) }}</td>
                            <td><span class="satuan-badge">{{ barang.satuan }}</span></td>
                            <td class="text-center">
                                <span :class="['status-badge', barang.is_active ? 'active' : 'inactive']">
                                    {{ barang.is_active ? 'Aktif' : 'Non-aktif' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="action-btns">
                                    <button type="button" @click.stop="openEditModal(barang)" class="btn-icon edit" title="Edit">
                                        <Pencil :size="13" />
                                    </button>
                                    <button type="button" @click.stop="deleteBarang(barang)" class="btn-icon del" title="Hapus">
                                        <Trash2 :size="13" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredBarangs().length === 0">
                            <td colspan="6" class="empty-row">Tidak ada data barang ditemukan.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ── MODAL ── -->
    <div v-if="isModalOpen" class="modal-overlay" @click.self="isModalOpen = false">
        <div class="modal-box">
            <div class="modal-head">
                <h5>{{ editingBarang ? 'Edit Barang' : 'Tambah Barang Baru' }}</h5>
                <button @click="isModalOpen = false" class="modal-close">✕</button>
            </div>
            <form @submit.prevent="submitForm" class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Barang</label>
                    <input v-model="form.nama_barang" class="form-input" placeholder="Contoh: Nasi Kucing" :disabled="form.processing" />
                    <span v-if="form.errors.nama_barang" class="form-error">{{ form.errors.nama_barang }}</span>
                </div>
                <div class="form-row-2">
                    <div class="form-group">
                        <label class="form-label">Harga Default (Rp)</label>
                        <input type="number" v-model="form.harga_per_unit" class="form-input" :disabled="form.processing" />
                        <span v-if="form.errors.harga_per_unit" class="form-error">{{ form.errors.harga_per_unit }}</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Satuan</label>
                        <input v-model="form.satuan" class="form-input" placeholder="pcs / bungkus" :disabled="form.processing" />
                        <span v-if="form.errors.satuan" class="form-error">{{ form.errors.satuan }}</span>
                    </div>
                </div>
                <div v-if="editingBarang" class="form-check">
                    <input type="checkbox" id="is_active" v-model="form.is_active" />
                    <label for="is_active">Barang Aktif (tampil di form transaksi)</label>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="isModalOpen = false" class="btn-outline" :disabled="form.processing">Batal</button>
                    <button type="submit" class="btn-primary" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── PDF PREVIEW MODAL ── -->
    <div v-if="isPreviewModalOpen" class="modal-overlay" @click.self="isPreviewModalOpen = false">
        <div class="modal-box" style="max-width: 800px; width: 90vw;">
            <div class="modal-head">
                <h5>Preview Form Harian (A6)</h5>
                <button @click="isPreviewModalOpen = false" class="modal-close">✕</button>
            </div>
            <div class="modal-body" style="padding: 0; background: #525659; overflow: hidden; border-bottom-left-radius: 18px; border-bottom-right-radius: 18px;">
                <iframe :src="previewUrl" style="width: 100%; aspect-ratio: 1.414; border: none; display: block;"></iframe>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ── STAT ROW ── */
.page-stats-row {
    display: flex; flex-wrap: nowrap;
    gap: 16px; margin-bottom: 22px;
    overflow-x: auto; padding-bottom: 8px;
    -webkit-overflow-scrolling: touch;
}
.page-stats-row::-webkit-scrollbar { height: 6px; }
.page-stats-row::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
.pstat-card {
    flex: 1 1 calc(33.333% - 16px);
    min-width: 240px;
    background: #fff;
    border-radius: 14px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border-left: 4px solid transparent;
    transition: transform 0.2s;
}
.pstat-card:hover { transform: translateY(-2px); }
.pstat-card.green { border-left-color: #22c55e; }
.pstat-card.blue  { border-left-color: #3b82f6; }
.pstat-card.red   { border-left-color: #ef4444; }
.pstat-icon { font-size: 28px; }
.pstat-value { font-size: 24px; font-weight: 800; color: #111827; line-height: 1; }
.pstat-label { font-size: 11.5px; color: #9ca3af; font-weight: 600; margin-top: 3px; text-transform: uppercase; letter-spacing: 0.4px; }

/* ── PANEL ── */
.argon-panel {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 16px rgba(0,0,0,0.06);
    overflow: hidden;
}
.panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 22px;
    border-bottom: 1px solid #f3f4f6;
    gap: 12px;
    flex-wrap: wrap;
}
.panel-head-left {}
.panel-title { font-size: 15px; font-weight: 700; color: #111827; margin: 0 0 2px; }
.panel-subtitle { font-size: 12px; color: #9ca3af; margin: 0; }
.panel-head-right { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.panel-body { padding: 0; }

/* ── SEARCH ── */
.search-wrap { position: relative; }
.search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; }
.search-input {
    padding: 8px 12px 8px 30px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 13px;
    outline: none;
    width: 200px;
    transition: border-color 0.2s;
    font-family: inherit;
}
.search-input:focus { border-color: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,0.1); }

/* ── BUTTONS ── */
.btn-primary {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
}
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(22,163,74,0.3); }
.btn-outline {
    padding: 8px 16px;
    background: transparent;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    color: #6b7280;
    transition: all 0.2s;
    font-family: inherit;
}
.btn-outline:hover { background: #f9fafb; border-color: #d1d5db; }
.btn-outline-green {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: #fff;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    color: #16a34a;
    transition: all 0.2s;
    font-family: inherit;
}
.btn-outline-green:hover { background: #f0fdf4; box-shadow: 0 2px 8px rgba(22,163,74,0.12); }

/* ── TABLE ── */
.table-wrap { overflow-x: auto; }
.argon-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
.argon-table thead tr { background: #f8fafc; }
.argon-table th {
    padding: 12px 16px;
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    border-bottom: 1px solid #f3f4f6;
    white-space: nowrap;
}
.argon-table td {
    padding: 14px 16px;
    border-bottom: 1px solid #f9fafb;
    color: #374151;
}
.argon-table tbody tr:hover { background: #f9fafb; }
.argon-table tbody tr:last-child td { border-bottom: none; }
.text-center { text-align: center; }
.text-right { text-align: right; }
.fw-600 { font-weight: 600; color: #111827; }
.row-id { font-size: 12px; font-weight: 700; color: #9ca3af; font-family: monospace; }

.satuan-badge {
    padding: 3px 10px;
    background: #f3f4f6;
    border-radius: 20px;
    font-size: 11.5px;
    color: #6b7280;
    font-weight: 600;
}
.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 700;
}
.status-badge.active  { background: #dcfce7; color: #16a34a; }
.status-badge.inactive{ background: #fee2e2; color: #ef4444; }

.action-btns { display: flex; justify-content: flex-end; gap: 6px; }
.btn-icon {
    width: 30px; height: 30px;
    border-radius: 7px;
    border: 1px solid #e5e7eb;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: all 0.18s;
    background: #fff;
}
.btn-icon.edit { color: #3b82f6; }
.btn-icon.edit:hover { background: #eff6ff; border-color: #bfdbfe; }
.btn-icon.del  { color: #ef4444; }
.btn-icon.del:hover  { background: #fef2f2; border-color: #fecaca; }

.empty-row { padding: 48px 16px; text-align: center; color: #9ca3af; font-style: italic; }

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
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-label { font-size: 12.5px; font-weight: 600; color: #374151; }
.form-input {
    padding: 9px 13px; border: 1px solid #e5e7eb; border-radius: 9px;
    font-size: 13.5px; outline: none; transition: border-color 0.2s;
    font-family: inherit; width: 100%; box-sizing: border-box;
}
.form-input:focus { border-color: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,0.1); }
.form-error { font-size: 11.5px; color: #ef4444; }
.form-check { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #374151; }
.form-check input { accent-color: #22c55e; }
.modal-footer {
    display: flex; justify-content: flex-end; gap: 8px;
    padding-top: 4px; border-top: 1px solid #f3f4f6; margin-top: 4px;
}
</style>
