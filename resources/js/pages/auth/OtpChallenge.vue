<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';

defineOptions({
    layout: {
        title: 'Verifikasi OTP',
        description: 'Masukkan kode verifikasi',
    },
});

const props = defineProps<{
    email: string;
    otpCode?: string;
    status?: string;
}>();

const form = useForm({ otp: '' });
const resendForm = useForm({});
const otpInputs = ref<string[]>(['', '', '', '', '', '']);

const handleInput = (index: number, event: Event) => {
    const input = event.target as HTMLInputElement;
    const val = input.value.replace(/\D/g, '').slice(-1);
    otpInputs.value[index] = val;
    form.otp = otpInputs.value.join('');

    if (val && index < 5) {
        document.getElementById(`otp-${index + 1}`)?.focus();
    }
};

const handleKeydown = (index: number, event: KeyboardEvent) => {
    if (event.key === 'Backspace' && !otpInputs.value[index] && index > 0) {
        document.getElementById(`otp-${index - 1}`)?.focus();
    }
};

const handlePaste = (event: ClipboardEvent) => {
    const pasted = event.clipboardData?.getData('text').replace(/\D/g, '').slice(0, 6) ?? '';
    pasted.split('').forEach((char, i) => {
        otpInputs.value[i] = char;
    });
    form.otp = otpInputs.value.join('');
    event.preventDefault();
};

const submit = () => form.post('/login/otp');
const resend = () => resendForm.post('/login/otp/resend');

onMounted(() => {
    document.getElementById('otp-0')?.focus();
});
</script>

<template>
    <Head title="Verifikasi OTP" />

    <div class="flex flex-col gap-6">
        <!-- Lock icon -->
        <div class="flex justify-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-500/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
        </div>

        <!-- Subtitle -->
        <p class="text-center text-sm text-muted-foreground">
            Kode 6 digit telah dikirim ke<br />
            <strong class="text-foreground">{{ props.email }}</strong>
        </p>

        <!-- Status message -->
        <div v-if="props.status" class="rounded-lg bg-emerald-500/10 px-4 py-3 text-center text-sm font-medium text-emerald-500">
            {{ props.status }}
        </div>

        <!-- Error -->
        <InputError :message="form.errors.otp" class="text-center" />

        <!-- OTP Code Display from DB -->
        <div v-if="props.otpCode" class="rounded-lg border border-dashed border-emerald-500/30 bg-emerald-500/5 px-4 py-3 text-center">
            <p class="text-xs text-muted-foreground mb-1">Kode OTP Anda</p>
            <p class="text-2xl font-black tracking-[0.4em] text-emerald-500 font-mono">{{ props.otpCode }}</p>
        </div>

        <!-- OTP Input boxes -->
        <div class="flex justify-center gap-2" @paste="handlePaste">
            <input
                v-for="(_, i) in otpInputs"
                :key="i"
                :id="`otp-${i}`"
                type="text"
                inputmode="numeric"
                maxlength="1"
                class="h-12 w-10 rounded-lg border-2 border-input bg-background text-center text-lg font-bold text-foreground outline-none transition-all duration-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 font-mono sm:h-14 sm:w-12 sm:rounded-xl sm:text-xl"
                :class="{
                    'border-emerald-500 bg-emerald-500/5': otpInputs[i],
                    'border-destructive bg-destructive/5': form.errors.otp,
                }"
                :value="otpInputs[i]"
                @input="handleInput(i, $event)"
                @keydown="handleKeydown(i, $event)"
            />
        </div>

        <!-- Verify button -->
        <Button
            @click="submit"
            class="mt-2 w-full text-emerald-700"
            :disabled="form.otp.length < 6 || form.processing"
        >
            <Spinner v-if="form.processing" />
            {{ form.processing ? 'Memverifikasi...' : 'Verifikasi' }}
        </Button>

        <!-- Resend -->
        <div class="text-center text-sm text-muted-foreground">
            Tidak menerima kode?
            <button
                @click="resend"
                class="font-semibold text-emerald-500 hover:text-emerald-400 underline underline-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="resendForm.processing"
            >
                {{ resendForm.processing ? 'Mengirim...' : 'Kirim ulang' }}
            </button>
        </div>

        <!-- Back to login -->
        <div class="text-center">
            <Link href="/login" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                ← Kembali ke Login
            </Link>
        </div>
    </div>
</template>
