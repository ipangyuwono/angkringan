<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { landing } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();

const particles = Array.from({ length: 18 }, (_, i) => ({
    id: i,
    size: Math.random() * 3 + 1,
    x: Math.random() * 100,
    y: Math.random() * 100,
    duration: Math.random() * 12 + 8,
    delay: Math.random() * 6,
    opacity: Math.random() * 0.25 + 0.05,
}));
</script>

<template>
    <div class="bg-page dark flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10 relative overflow-hidden text-white">
        
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

        <div class="w-full max-w-sm relative z-10">
            <div class="nav-glass flex flex-col gap-8 p-8 rounded-2xl shadow-[0_0_40px_rgba(16,185,129,0.15)] border border-emerald-500/20">
                <div class="flex flex-col items-center gap-4">
                    <Link
                        :href="landing()"
                        class="flex flex-col items-center gap-2 font-medium"
                    >
                        <div class="mb-1 flex items-center justify-center rounded-md">
                            <img src="/assets/img/logo.webp" alt="Logo" class="w-14 h-14 object-contain drop-shadow-md" />
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-2 text-center text-glow-stone">
                        <h1 class="text-2xl font-bold tracking-wide">{{ title }}</h1>
                        <p class="text-center text-sm text-stone-400">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped>
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

.nav-glass {
    background: rgba(8, 8, 8, 0.6);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);

    /* Force white text constraint and readable button inside emerald background */
    --foreground: hsl(0 0% 100%);
    --primary: hsl(0 0% 100%); 
    --primary-foreground: hsl(160 80% 20%);
    --muted: hsl(0 0% 15%);
    --muted-foreground: hsl(0 0% 80%);
    --background: hsl(0 0% 5%);
    --input: hsl(0 0% 40%);
    --border: hsl(0 0% 30%);
    --ring: hsl(160 80% 50%);
}

.text-glow-stone {
    text-shadow:
        0 0 20px rgba(52, 211, 153, 0.5),
        0 0 40px rgba(16, 185, 129, 0.3);
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
</style>
