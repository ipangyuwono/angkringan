<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { dashboard, landing, logout as logoutRoute } from '@/routes';
import { index as transaksiIndex } from '@/routes/transaksi';
import { index as barangIndex } from '@/routes/barang';
import { ref, onMounted, onUnmounted } from 'vue';
import { index as hutangIndex, my as hutangMy } from '@/routes/hutang';
import { index as kasIndex, my as kasMy } from '@/routes/kas';

const liveClock = ref('');
const updateClock = () => {
    liveClock.value = new Date().toLocaleTimeString('id-ID', {
        hour: '2-digit', minute: '2-digit', second: '2-digit'
    }) + ' WIB';
};

let clockInterval: ReturnType<typeof setInterval>;
onMounted(() => {
    updateClock();
    clockInterval = setInterval(updateClock, 1000);
});

onUnmounted(() => {
    clearInterval(clockInterval);
});

const page = usePage();
const auth = page.props.auth as any;
const sidebarOpen = ref(typeof window !== 'undefined' ? window.innerWidth > 768 : true);

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const logout = () => {
    router.post(logoutRoute.url());
};

const pageTitle = () => {
    if (page.url === '/dashboard') return 'Dashboard';
    if (page.url?.startsWith('/dashboard/transaksi')) return 'Transaksi Harian';
    if (page.url?.startsWith('/dashboard/barang')) return 'Master Barang';
    if (page.url?.startsWith('/dashboard/kas') || page.url?.startsWith('/dashboard/my-kas')) return 'Manajemen Kas';
    if (page.url?.startsWith('/dashboard/hutang') || page.url?.startsWith('/dashboard/my-hutang')) return 'Manajemen Hutang';
    return 'Dashboard';
};

const userEmail = auth?.user?.email || '';
const isBuyerSession123 = ['ppang7@gmail.com', 'hartanto12@gmail.com', 'sarinoang7@gmail.com'].includes(userEmail);
const isBuyerSession4 = userEmail === 'alan7@gmail.com';

const keuDropdownOpen = ref(
    page.url?.startsWith('/dashboard/kas') || page.url?.startsWith('/dashboard/hutang')
);
const toggleKeuDropdown = () => keuDropdownOpen.value = !keuDropdownOpen.value;
</script>

<template>



    <div class="argon-layout" :class="{ 'sidebar-mobile-open': sidebarOpen }">
        <div class="sidebar-overlay" @click="sidebarOpen = false"></div>

        <!-- ── SIDEBAR ── -->
        <aside class="argon-sidebar">
            <!-- Green Gradient Top -->
            <div class="sidebar-bg-gradient"></div>

            <!-- Brand -->
            <div class="sidebar-brand">
                <div class="brand-logo"><img src="/assets/img/logo.webp" alt="Logo" style="width: 100%; height: 100%; object-fit: contain; padding: 4px;"></div>
                <div class="brand-text">
                    <div class="brand-name">Angkringan</div>
                    <div class="brand-sub">Asli Klaten</div>
                </div>
            </div>

            <!-- Divider -->
            <div class="sidebar-divider"></div>

            <!-- Nav -->
            <nav class="sidebar-nav">
                <p class="nav-section-label">Menu Utama</p>

                <template v-if="auth?.user?.role === 'admin'">
                    <Link 
                        :href="dashboard.url()"
                        :class="['sidebar-link', page.url === '/dashboard' ? 'active' : '']"
                    >
                        <span class="link-icon-wrap">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                        </span>
                        <span class="link-text">Dashboard</span>
                        <span v-if="page.url === '/dashboard'" class="link-active-dot"></span>
                    </Link>
                </template>

                <Link
                    :href="transaksiIndex.url()"
                    :class="['sidebar-link', page.url?.startsWith('/dashboard/transaksi') ? 'active' : '']"
                >
                    <span class="link-icon-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M9 12h6"/><path d="M9 16h6"/></svg>
                    </span>
                    <span class="link-text">Transaksi Harian</span>
                    <span v-if="page.url?.startsWith('/dashboard/transaksi')" class="link-active-dot"></span>
                </Link>

                <template v-if="auth?.user?.role === 'admin'">
                    <Link 
                        :href="barangIndex.url()" 
                        :class="['sidebar-link', page.url?.startsWith('/dashboard/barang') ? 'active' : '']"
                    >
                        <span class="link-icon-wrap">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                        </span>
                        <span class="link-text">Master Barang</span>
                        <span v-if="page.url?.startsWith('/dashboard/barang')" class="link-active-dot"></span>
                    </Link>

                    <!-- Dropdown Manajemen Keuangan (Admin Only) -->
                    <div class="sidebar-dropdown">
                        <button 
                            @click="toggleKeuDropdown"
                            :class="['sidebar-link w-100', (page.url?.startsWith('/dashboard/kas') || page.url?.startsWith('/dashboard/hutang')) ? 'active' : '']"
                            style="border: none; outline: none; background: transparent; text-align: left;"
                        >
                            <span class="link-icon-wrap">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                                </svg>
                            </span>
                            <span class="link-text">Manajemen Keuangan</span>
                            <span class="action-arrow" :style="{ transform: keuDropdownOpen ? 'rotate(90deg)' : 'rotate(0)' }" style="transition: transform 0.2s;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                            </span>
                        </button>
                        
                        <div v-if="keuDropdownOpen" class="dropdown-menu">
                            <Link 
                                :href="kasIndex.url()"
                                :class="['dropdown-link', page.url?.startsWith('/dashboard/kas') ? 'active' : '']"
                            >
                                Manajemen Kas
                            </Link>
                            <Link 
                                :href="hutangIndex.url()"
                                :class="['dropdown-link', page.url?.startsWith('/dashboard/hutang') ? 'active' : '']"
                            >
                                Manajemen Hutang
                            </Link>
                        </div>
                    </div>
                </template>

                <!-- For Sellers -->
                <template v-else>
                    <Link 
                        v-if="isBuyerSession123"
                        :href="kasMy.url()"
                        :class="['sidebar-link', page.url?.startsWith('/dashboard/my-kas') ? 'active' : '']"
                    >
                        <span class="link-icon-wrap">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>
                            </svg>
                        </span>
                        <span class="link-text">Manajemen Kas</span>
                    </Link>

                    <Link 
                        v-if="isBuyerSession4"
                        :href="hutangMy.url()"
                        :class="['sidebar-link', page.url?.startsWith('/dashboard/my-hutang') ? 'active' : '']"
                    >
                        <span class="link-icon-wrap">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                            </svg>
                        </span>
                        <span class="link-text">Manajemen Hutang</span>
                    </Link>
                </template>
            </nav>

            <!-- Sidebar Footer -->
            <div class="sidebar-footer">
                <div class="sidebar-divider" style="margin-bottom: 10px;"></div>

                <div class="sidebar-user-card">
                    <div class="user-avatar">{{ auth?.user?.name?.charAt(0)?.toUpperCase() || 'U' }}</div>
                    <div class="user-info">
                        <div class="user-name">{{ auth?.user?.name || 'User' }}</div>
                        <div class="user-role">
                            {{ auth?.user?.role === 'admin' ? 'Administrator'    : 'Penjual' }}
                        </div>
                    </div>
                    <button @click="logout" class="logout-btn" title="Keluar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- ── MAIN CONTENT ── -->
        <div class="argon-main">

            <!-- Argon-style Top Banner -->
            <div class="argon-banner">
                <div class="banner-inner">
                    <!-- Left: Breadcrumb + Title -->
                    <div class="banner-left" style="display: flex; align-items: center; gap: 16px;">
                        <button class="mobile-menu-btn" @click="toggleSidebar">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                        </button>
                        <div>
                            <nav class="breadcrumb-nav">
                                <Link :href="dashboard.url()" class="breadcrumb-home">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                                </Link>
                                <span class="breadcrumb-sep">/</span>
                                <span class="breadcrumb-current">{{ pageTitle() }}</span>
                            </nav>
                            <h4 class="banner-title">{{ pageTitle() }}</h4>
                        </div>
                    </div>

                    <!-- Right: Date + toggle -->
                    <div class="banner-right">
                        <div class="banner-date" style="display: flex; align-items: center; gap: 8px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                            </svg>
                            <span style="font-variant-numeric: tabular-nums;">
                                {{ liveClock }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <main class="argon-content">
                <slot />
            </main>

            <!-- Footer -->
            <footer class="argon-footer">
                <span style="flex: 1; text-align: right;">Sistem Manajemen Angkringan</span>
            </footer>
        </div>
    </div>
</template>

<style>
/* Prevent iOS auto-zoom by ensuring inputs/selects are at least 16px on mobile */
@media (max-width: 768px) {
    input[type="text"], input[type="number"], input[type="date"], select, textarea {
        font-size: 16px !important;
    }
}
</style>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

/* ─────────────────────────────────────────
   ROOT LAYOUT
───────────────────────────────────────── */
.argon-layout {
    display: flex;
    min-height: 100vh;
    background: #f8fafc;
    font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
}

/* ─────────────────────────────────────────
   SIDEBAR
───────────────────────────────────────── */
.argon-sidebar {
    width: 250px;
    flex-shrink: 0;
    background: linear-gradient(195deg, rgba(26,122,74,0.85) 0%, rgba(24,165,94,0.85) 40%, rgba(34,197,94,0.85) 100%);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    display: flex;
    flex-direction: column;
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
    overflow-x: hidden;
    z-index: 100;
    box-shadow: 4px 0 24px rgba(22, 101, 52, 0.25);
    transition: width 0.3s ease;
}

.sidebar-bg-gradient {
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

/* Brand */
.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 22px 20px 18px;
    position: relative;
}
.brand-logo {
    width: 44px;
    height: 44px;
    background: rgba(0, 0, 0, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255,255,255,0.25);
    flex-shrink: 0;
}
.brand-name {
    font-weight: 800;
    font-size: 15px;
    color: #ffffff;
    letter-spacing: -0.3px;
}
.brand-sub {
    font-size: 11px;
    color: rgba(255,255,255,0.65);
    font-weight: 500;
    letter-spacing: 0.4px;
    margin-top: 1px;
}

.sidebar-divider {
    height: 1px;
    background: rgba(255,255,255,0.15);
    margin: 0 20px;
}

/* Nav */
.sidebar-nav {
    flex: 1;
    padding: 14px 8px;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.nav-section-label {
    font-size: 10px;
    font-weight: 700;
    color: rgba(255,255,255,0.5);
    letter-spacing: 1.2px;
    text-transform: uppercase;
    padding: 0 12px 10px;
    margin: 0;
}
.sidebar-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 14px;
    border-radius: 10px;
    color: rgba(255,255,255,0.75);
    font-size: 13.5px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
    position: relative;
    border: 1px solid transparent;
}
.sidebar-link:hover {
    background: rgba(255,255,255,0.15);
    color: #ffffff;
    border-color: rgba(255,255,255,0.1);
}
.sidebar-link.active {
    background: #ffffff;
    color: #16a34a;
    font-weight: 700;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    border-color: rgba(255,255,255,0.9);
}
.link-icon-wrap {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: rgba(255,255,255,0.1);
    flex-shrink: 0;
    transition: all 0.2s ease;
}
.sidebar-link.active .link-icon-wrap {
    background: rgba(22, 163, 74, 0.12);
    color: #16a34a;
}
.sidebar-link:hover .link-icon-wrap {
    background: rgba(255,255,255,0.2);
}
.link-text { flex: 1; }
.link-active-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #16a34a;
    flex-shrink: 0;
}

/* Sidebar Dropdown Menu */
.sidebar-dropdown {
    display: flex;
    flex-direction: column;
}
.w-100 { width: 100%; border: none; outline: none; background: transparent; font-family: inherit; }

/* Parent button custom active state */
.sidebar-link.w-100.active {
    background: rgba(255,255,255,0.12) !important;
    color: #ffffff !important;
    box-shadow: none !important;
    border-color: transparent !important;
}
.sidebar-link.w-100.active .link-icon-wrap {
    background: rgba(255,255,255,0.2) !important;
    color: #ffffff !important;
}

.dropdown-menu {
    display: flex;
    flex-direction: column;
    padding-left: 16px;
    margin-left: 32px;
    border-left: 1px solid rgba(255,255,255,0.2);
    margin-top: 6px;
    margin-bottom: 6px;
    gap: 4px;
    animation: dropdownSlide 0.25s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@keyframes dropdownSlide {
    0% { opacity: 0; transform: translateY(-8px) translateX(-6px); }
    100% { opacity: 1; transform: translateY(0) translateX(0); }
}

.dropdown-link {
    color: rgba(255,255,255,0.6);
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    padding: 6px 12px;
    border-radius: 6px;
    transition: all 0.2s ease;
}
.dropdown-link:hover {
    color: rgba(255,255,255,0.9);
    background: rgba(255,255,255,0.08);
}
.dropdown-link.active {
    color: #fff;
    font-weight: 700;
    background: rgba(255,255,255,0.15);
}

/* Sidebar Footer */
.sidebar-footer {
    padding: 10px 8px 16px;
}
.footer-link {
    color: rgba(255,255,255,0.6) !important;
}
.footer-link:hover {
    color: rgba(255,255,255,0.9) !important;
}

.sidebar-user-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border-radius: 12px;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.18);
    margin-top: 8px;
    backdrop-filter: blur(4px);
}
.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #ffffff;
    color: #16a34a;
    font-size: 14px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.user-info { flex: 1; min-width: 0; }
.user-name {
    font-size: 12.5px;
    font-weight: 700;
    color: #ffffff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.user-role {
    font-size: 10.5px;
    color: rgba(255,255,255,0.6);
    font-weight: 400;
    margin-top: 1px;
}
.logout-btn {
    width: 28px;
    height: 28px;
    border-radius: 7px;
    border: 1px solid rgba(255,255,255,0.2);
    background: rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.7);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.18s;
    flex-shrink: 0;
}
.logout-btn:hover {
    background: rgba(239,68,68,0.8);
    border-color: rgba(239,68,68,0.5);
    color: white;
}

/* ─────────────────────────────────────────
   MAIN AREA
───────────────────────────────────────── */
.argon-main {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

/* ── ARGON BANNER (signature top section) ── */
.argon-banner {
    background: linear-gradient(195deg, rgba(26,122,74,0.85) 0%, rgba(34,197,94,0.85) 100%);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    padding: 22px 28px 80px;
    position: relative;
    overflow: hidden;
}
.argon-banner::before {
    content: '';
    position: absolute;
    top: -60px;
    right: -60px;
    width: 280px;
    height: 280px;
    border-radius: 50%;
    background: rgba(255,255,255,0.05);
    pointer-events: none;
}
.argon-banner::after {
    content: '';
    position: absolute;
    bottom: -80px;
    left: 40%;
    width: 200px;
    height: 200px;
    border-radius: 50%;
    background: rgba(255,255,255,0.04);
    pointer-events: none;
}
.banner-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
    z-index: 1;
}
.mobile-menu-btn {
    display: none;
    background: transparent;
    border: none;
    color: white;
    cursor: pointer;
    padding: 0;
}
.banner-left {}
.breadcrumb-nav {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: rgba(255,255,255,0.75);
    margin-bottom: 6px;
}
.breadcrumb-home {
    color: rgba(255,255,255,0.75);
    text-decoration: none;
    display: flex;
    align-items: center;
}
.breadcrumb-home:hover { color: #fff; }
.breadcrumb-sep { opacity: 0.5; }
.breadcrumb-current { color: rgba(255,255,255,0.9); font-weight: 500; }
.banner-title {
    font-size: 22px;
    font-weight: 800;
    color: #ffffff;
    margin: 0;
    letter-spacing: -0.4px;
    text-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.banner-right {}
.banner-date {
    display: flex;
    align-items: center;
    gap: 6px;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.25);
    border-radius: 20px;
    padding: 7px 16px;
    font-size: 12px;
    font-weight: 500;
    color: rgba(255,255,255,0.95);
    backdrop-filter: blur(4px);
}

/* ── PAGE CONTENT — pulls up over the banner ── */
.argon-content {
    flex: 1;
    padding: 0 28px 28px;
    margin-top: -52px;
    position: relative;
    z-index: 2;
}

/* ── FOOTER ── */
.argon-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 28px;
    font-size: 12px;
    color: #9ca3af;
    border-top: 1px solid #e5e7eb;
    background: #ffffff;
}
.argon-footer strong { color: #16a34a; font-weight: 700; }

/* ─────────────────────────────────────────
   RESPONSIVE
───────────────────────────────────────── */
.sidebar-overlay {
    display: none; /* hidden always on desktop */
}

@media (max-width: 768px) {
    /* Hide sidebar off-screen by default on mobile */
    .argon-sidebar {
        position: fixed;
        left: -260px;
        top: 0;
        height: 100vh;
        transition: left 0.28s ease;
        z-index: 200;
    }

    /* Show sidebar when open */
    .sidebar-mobile-open .argon-sidebar {
        left: 0;
    }

    /* Dark overlay behind sidebar when open */
    .sidebar-mobile-open .sidebar-overlay {
        display: block;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.45);
        z-index: 199;
        backdrop-filter: blur(1px);
    }

    /* Show hamburger button */
    .mobile-menu-btn { display: block; }

    .argon-content { padding: 0 16px 20px; }
    .argon-banner { padding: 18px 16px 70px; }
    .banner-date { display: none; }
}
</style>
