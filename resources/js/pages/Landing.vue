<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { onMounted, onUnmounted, ref } from 'vue'
import { Coffee, ArrowRight, Leaf, ClipboardList, Package, Printer, Coins, Star, ChevronDown } from 'lucide-vue-next'
import { login } from '@/routes'

const menuItems = [
    { emoji: '🍚', nama: 'Nasi Kucing', harga: '1.500', desc: 'Nasi khas angkringan porsi kecil' },
    { emoji: '🍢', nama: 'ATI Bakar', harga: '2.800', desc: 'Ati sapi bumbu kecap bakar' },
    { emoji: '🥚', nama: 'Telor Bakar', harga: '1.800', desc: 'Telur bulat bumbu kecap' },
    { emoji: '🍗', nama: 'Ceker', harga: '1.000', desc: 'Ceker ayam empuk berbumbu' },
    { emoji: '🫙', nama: 'Usus', harga: '1.000', desc: 'Usus ayam gurih kecoklatan' },
    { emoji: '🧅', nama: 'Gorengan', harga: '800', desc: 'Tempe, tahu, bakwan renyah' },
    { emoji: '🍡', nama: 'Baceman', harga: '800', desc: 'Aneka baceman khas Jawa' },
    { emoji: '☕', nama: 'Wedang Jahe', harga: 'Spesial', desc: 'Jahe hangat asli rempah Klaten' },
]

const fiturItems = [
    {
        icon: ClipboardList,
        judul: 'Catat Transaksi Harian',
        desc: 'Input barang yang dibawa, sisa, dan harga — dengan opsi override harga jika ada kenaikan mendadak.',
        color: 'emerald',
    },
    {
        icon: Package,
        judul: 'Kelola Master Barang',
        desc: 'Tambah, edit, atau nonaktifkan barang dagangan. Harga default disimpan per barang.',
        color: 'blue',
    },
    {
        icon: Printer,
        judul: 'Cetak Laporan PDF',
        desc: 'Download laporan harian dalam format PDF yang identik dengan form buku warung tradisional.',
        color: 'violet',
    },
    {
        icon: Coins,
        judul: 'Rekap Otomatis',
        desc: 'Jumlah total dan setor dihitung otomatis — tidak perlu kalkulator lagi.',
        color: 'amber',
    },
]

const statsItems = [
    { value: '18+', label: 'Menu Tersedia' },
    { value: '100%', label: 'Bahan Segar' },
    { value: 'Asli', label: 'Racikan Klaten' },
    { value: 'Tiap', label: 'Malam Buka' },
]

// ── Scroll reveal via Intersection Observer ──
const observers = []

function useScrollReveal() {
    onMounted(() => {
        const elements = document.querySelectorAll('[data-reveal]')
        const io = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        const delay = entry.target.dataset.delay || 0
                        setTimeout(() => {
                            entry.target.classList.add('revealed')
                        }, Number(delay))
                        io.unobserve(entry.target)
                    }
                })
            },
            { threshold: 0.1, rootMargin: '0px 0px -60px 0px' }
        )
        elements.forEach((el) => io.observe(el))
        observers.push(io)
    })
    onUnmounted(() => observers.forEach((io) => io.disconnect()))
}

useScrollReveal()

// ── floating particles ──
const particles = Array.from({ length: 18 }, (_, i) => ({
    id: i,
    size: Math.random() * 3 + 1,
    x: Math.random() * 100,
    y: Math.random() * 100,
    duration: Math.random() * 12 + 8,
    delay: Math.random() * 6,
    opacity: Math.random() * 0.25 + 0.05,
}))

// ── typing effect for hero ──
const typed = ref('')
const fullText = 'Angkringan Asli Klaten'
let ti = 0
let isDeleting = false
let typingTimer = null

const typeLoop = () => {
    if (!isDeleting && ti < fullText.length) {
        // Ngetik maju
        typed.value += fullText[ti]
        ti++
        typingTimer = setTimeout(typeLoop, 80)
    } else if (isDeleting && ti > 0) {
        // Hapus mundur (backspace)
        typed.value = fullText.substring(0, ti - 1)
        ti--
        typingTimer = setTimeout(typeLoop, 40)
    } else if (!isDeleting && ti === fullText.length) {
        // Jeda bentar saat teks udah lengkap
        isDeleting = true
        typingTimer = setTimeout(typeLoop, 3000)
    } else if (isDeleting && ti === 0) {
        // Jeda bentar sebelum mulai ngetik lagi
        isDeleting = false
        typingTimer = setTimeout(typeLoop, 800)
    }
}

onMounted(() => {
    typeLoop()
})
onUnmounted(() => clearTimeout(typingTimer))
</script>

<template>
    <Head title="Home" />

    <div class="min-h-screen bg-page text-white selection:bg-emerald-500/30 overflow-x-hidden relative">

        <!-- ── FLOATING PARTICLES ── -->
        <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden">
            <div
                v-for="p in particles"
                :key="p.id"
                class="particle absolute rounded-full bg-emerald-400"
                :style="{
                    width: p.size + 'px',
                    height: p.size + 'px',
                    left: p.x + '%',
                    top: p.y + '%',
                    opacity: p.opacity,
                    animationDuration: p.duration + 's',
                    animationDelay: p.delay + 's',
                }"
            />
        </div>

        <!-- ── NAV ── -->
        <nav class="absolute top-6 left-0 w-full z-50">
            <div class="max-w-6xl mx-auto px-6">

        <!-- Pill full width -->
        <div class="flex items-center justify-between bg-white/[0.03] border border-white/10 rounded-full px-3 py-1.5 backdrop-blur-xl w-full">

            <!-- Kiri: Logo + Nama -->
            <div class="flex items-center gap-2">
                <img src="/assets/img/logo.webp" alt="Logo" class="w-8 h-8 object-contain drop-shadow-md" />
                <span class="font-bold text-lg tracking-wide text-white">Angkringan</span>
            </div>

            <!-- Tengah: Menu -->
            <div class="flex items-center gap-1">
                <a href="#dashboard" class="text-gray-400 hover:text-white px-4 py-1.5 rounded-full text-sm transition hover:bg-white/5"></a>
                <a href="#menu" class="text-gray-400 hover:text-white px-4 py-1.5 rounded-full text-sm transition hover:bg-white/5"></a>
                <a href="#fitur" class="text-gray-400 hover:text-white px-4 py-1.5 rounded-full text-sm transition hover:bg-white/5"></a>
            </div>

            <!-- Kanan: Tombol -->
            <Link :href="login()" class="px-5 py-1.5 rounded-full border border-emerald-500 text-white font-medium text-sm hover:bg-emerald-500 transition-colors">
                Login
            </Link>
            </div>
        </div>
        </nav>

        <!-- ── HERO ── -->
        <header class="relative min-h-[92vh] flex items-center px-6 overflow-hidden">
            <!-- glow blobs -->
            <div class="absolute top-1/4 right-1/4 w-[500px] h-[500px] bg-emerald-500/8 rounded-full blur-[120px] pointer-events-none"></div>
            <div class="absolute bottom-1/4 left-1/4 w-[300px] h-[300px] bg-emerald-600/10 rounded-full blur-[80px] pointer-events-none"></div>

            <!-- grid lines -->
            <div class="absolute inset-0 grid-lines opacity-30 pointer-events-none"></div>

            <div class="max-w-6xl mx-auto w-full flex flex-col md:flex-row-reverse items-center justify-between gap-12 relative z-10 py-32">

                <!-- BAGIAN KANAN: TEKS BESAR -->
                <div class="flex-1 text-center md:text-left z-20">
                    <!-- heading typed -->
                    <h1 class="font-playfair text-5xl md:text-7xl lg:text-[4.5rem] font-bold leading-[1.1] mb-6">
                        <span class="block text-stone-100 text-glow-stone">{{ typed }}<span class="cursor-blink font-mono-dm font-light">|</span></span>
                    </h1>

                    <p data-reveal data-delay="200" class="reveal-up text-stone-400 text-xl md:text-2xl mb-12 mx-auto md:mx-0 font-light italic tracking-wide" style="font-family: 'Playfair Display', serif;">
                        Khas Jawa Selera Dunia
                    </p>
                </div>

                <!-- BAGIAN KIRI: GEROBAK 3D -->
                <div data-reveal data-delay="300" class="reveal-scale flex-1 relative w-full flex justify-center md:justify-start mt-12 md:mt-0">
                    <div class="relative w-full max-w-[600px]">
                        <!-- Backing glow -->
                        <div class="absolute inset-0 bg-emerald-500/10 blur-[100px] rounded-full top-1/4"></div>

                        <!-- Gambar Utuh -->
                        <img src="/assets/img/gerobag.webp" alt="Gerobak Angkringan 3D" class="w-full h-auto object-contain relative z-10 drop-shadow-[0_20px_50px_rgba(0,0,0,0.5)] hover:scale-105 transition-transform duration-700 ease-out">
                    </div>
                </div>
            </div>
        </header>

        <!-- ── FOOTER ── -->
        <footer class="relative z-10 py-10 px-6 border-t border-white/5">
            <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-2.5">
                    <span class="font-bold text-sm text-stone-400 tracking-wide">Angkringan Asli Klaten</span>
                </div>
                <p class="text-gray-400 text-xs font-medium">
                    © {{ new Date().getFullYear() }} All rights reserved.
                </p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* ──────────── FONTS ──────────── */
div, p, span, a, button, nav, footer, header {
    font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
}
.font-playfair {
    font-family: 'Playfair Display', Georgia, serif;
    letter-spacing: -0.02em;
}
.font-mono-dm {
    font-family: 'DM Mono', monospace;
}

/* ──────────── NAV ──────────── */
.nav-glass {
    background: rgba(8, 8, 8, 0.85);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}

/* ──────────── GLOWING TEXT ──────────── */
.text-glow-hero {
    color: #ecfdf5;
    text-shadow:
        0 0 20px rgba(52, 211, 153, 0.9),
        0 0 40px rgba(16, 185, 129, 0.7),
        0 0 80px rgba(16, 185, 129, 0.4),
        0 0 120px rgba(5, 150, 105, 0.2);
}
.text-glow-sm {
    color: #d1fae5;
    text-shadow:
        0 0 10px rgba(52, 211, 153, 0.8),
        0 0 25px rgba(16, 185, 129, 0.5),
        0 0 50px rgba(16, 185, 129, 0.25);
}

/* ──────────── GRID LINES ──────────── */
.grid-lines {
    background-image:
        linear-gradient(rgba(16, 185, 129, 0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(16, 185, 129, 0.04) 1px, transparent 1px);
    background-size: 60px 60px;
}

/* ──────────── TYPOGRAPHY ──────────── */
.text-gradient {
    background: linear-gradient(135deg, #10b981 0%, #34d399 50%, #6ee7b7 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.text-gradient-sub {
    background: linear-gradient(135deg, #6ee7b7 0%, #10b981 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    opacity: 0.6;
}

/* ──────────── CURSOR BLINK ──────────── */
.cursor-blink {
    animation: blink 0.9s step-end infinite;
    color: #10b981;
    font-weight: 100;
}
@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0; }
}

/* ──────────── PAGE BACKGROUND ──────────── */
.bg-page {
    background:
        radial-gradient(ellipse 80% 50% at 15% 10%, rgba(16, 185, 129, 0.18) 0%, transparent 55%),
        radial-gradient(ellipse 60% 40% at 85% 5%,  rgba(52, 211, 153, 0.12) 0%, transparent 50%),
        radial-gradient(ellipse 70% 60% at 50% 55%, rgba(5,  150, 105, 0.14) 0%, transparent 65%),
        radial-gradient(ellipse 55% 45% at 10% 90%, rgba(6,  182, 212, 0.10) 0%, transparent 55%),
        radial-gradient(ellipse 65% 50% at 90% 95%, rgba(16, 185, 129, 0.08) 0%, transparent 55%),
        linear-gradient(160deg, #080f0d 0%, #0a1210 35%, #080f0d 65%, #06100e 100%);
}

/* ──────────── CTA BUTTON ──────────── */
.cta-btn {
    background: linear-gradient(135deg, #10b981, #059669);
    box-shadow: 0 0 30px rgba(16, 185, 129, 0.35), 0 0 60px rgba(16, 185, 129, 0.1);
    color: #000;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}
.cta-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #34d399, #10b981);
    opacity: 0;
    transition: opacity 0.3s;
}
.cta-btn:hover::before { opacity: 1; }
.cta-btn:hover { box-shadow: 0 0 40px rgba(16, 185, 129, 0.5), 0 0 80px rgba(16, 185, 129, 0.15); transform: translateY(-2px); }
.cta-btn:active { transform: scale(0.97); }
.cta-btn > * { position: relative; z-index: 1; }

/* ──────────── LOGO SHIMMER ──────────── */
.logo-shimmer {
    background: linear-gradient(105deg, transparent 40%, rgba(16, 185, 129, 0.15) 50%, transparent 60%);
    animation: shimmer 3s ease-in-out infinite;
}
@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(200%); }
}

/* ──────────── HERO CARD ──────────── */
.hero-card {
    animation: float 6s ease-in-out infinite;
}
@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    33% { transform: translateY(-12px) rotate(1deg); }
    66% { transform: translateY(-6px) rotate(-1deg); }
}
.mini-card {
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.2);
    backdrop-filter: blur(8px);
}
.float-anim { animation: floatBadge 4s ease-in-out infinite; }
.float-anim-reverse { animation: floatBadge 4.5s ease-in-out infinite reverse; }
@keyframes floatBadge {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

/* ──────────── STATS BAR ──────────── */
.stats-bar {
    background: rgba(16, 185, 129, 0.03);
    border: 1px solid rgba(16, 185, 129, 0.1);
    backdrop-filter: blur(12px);
}

/* ──────────── MENU CARD ──────────── */
.menu-card {
    background: rgba(255, 255, 255, 0.025);
    border: 1px solid rgba(255, 255, 255, 0.06);
    transition: border-color 0.3s ease, transform 0.3s ease, background 0.3s ease, box-shadow 0.3s ease;
}
.menu-card:hover {
    border-color: rgba(16, 185, 129, 0.3);
    background: rgba(16, 185, 129, 0.05);
    transform: translateY(-4px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3), 0 0 20px rgba(16, 185, 129, 0.08);
}
.menu-card-shine {
    background: linear-gradient(105deg, transparent 30%, rgba(16, 185, 129, 0.04) 50%, transparent 70%);
}

/* ──────────── FITUR CARD ──────────── */
.fitur-card {
    background: rgba(255, 255, 255, 0.025);
    border: 1px solid rgba(255, 255, 255, 0.07);
    transition: border-color 0.4s ease, transform 0.4s ease, box-shadow 0.4s ease;
}
.fitur-card:hover {
    border-color: rgba(16, 185, 129, 0.25);
    transform: translateY(-6px);
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4), 0 0 30px rgba(16, 185, 129, 0.06);
}
.fitur-card-glow { background: radial-gradient(circle, rgba(16, 185, 129, 0.2), transparent 70%); }

/* ──────────── CTA BLOCK ──────────── */
.cta-block {
    border: 1px solid rgba(16, 185, 129, 0.15);
}

/* ──────────── PARTICLES ──────────── */
.particle {
    animation: drift linear infinite;
    will-change: transform;
}
@keyframes drift {
    0% { transform: translateY(0px) translateX(0px); }
    25% { transform: translateY(-25px) translateX(10px); }
    50% { transform: translateY(-45px) translateX(-5px); }
    75% { transform: translateY(-20px) translateX(15px); }
    100% { transform: translateY(0px) translateX(0px); }
}

[data-reveal] {
    opacity: 0;
    will-change: opacity, transform;
    transition: opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1), transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
}
.reveal-up { transform: translateY(40px); }
.reveal-up.revealed { opacity: 1; transform: translateY(0); }

.reveal-scale { transform: scale(0.9) translateY(20px); }
.reveal-scale.revealed { opacity: 1; transform: scale(1) translateY(0); }

.reveal-left { transform: translateX(-40px); }
.reveal-left.revealed { opacity: 1; transform: translateX(0); }

.reveal-right { transform: translateX(40px); }
.reveal-right.revealed { opacity: 1; transform: translateX(0); }


.text-glow-stone {
    text-shadow:
        0 0 20px rgba(120, 113, 108, 1),
0 0 40px rgba(120, 113, 108, 0.8),
        0 0 80px rgba(120, 113, 108, 0.3),
        0 0 120px rgba(120, 113, 108, 0.15);
}
</style>
