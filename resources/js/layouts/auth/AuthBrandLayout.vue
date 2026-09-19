<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, CalendarCheck, Rocket, ShieldCheck } from '@lucide/vue';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import CodeSparkLogo from '@/components/CodeSparkLogo.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();

const points = [
    { Icon: Rocket, text: 'Manage the projects shown on your site' },
    { Icon: CalendarCheck, text: 'Track bookings and schedule consultations' },
    { Icon: ShieldCheck, text: 'Private area — only your team gets in' },
];

const pointer = ref({ x: 0, y: 0 });
const tiltEnabled = ref(false);

const trackPointer = (event: MouseEvent) => {
    pointer.value = {
        x: event.clientX / window.innerWidth - 0.5,
        y: event.clientY / window.innerHeight - 0.5,
    };
};

// Parallax is a desktop flourish — the split layout only exists at lg anyway.
let finePointer: MediaQueryList | null = null;

const syncTilt = () => {
    tiltEnabled.value = finePointer?.matches ?? false;

    if (tiltEnabled.value) {
        window.addEventListener('mousemove', trackPointer, { passive: true });
    } else {
        window.removeEventListener('mousemove', trackPointer);
        pointer.value = { x: 0, y: 0 };
    }
};

onMounted(() => {
    finePointer = window.matchMedia('(min-width: 1024px) and (pointer: fine)');
    finePointer.addEventListener('change', syncTilt);
    syncTilt();
});

onBeforeUnmount(() => {
    finePointer?.removeEventListener('change', syncTilt);
    window.removeEventListener('mousemove', trackPointer);
});
</script>

<template>
    <div class="bg-background grid min-h-svh lg:grid-cols-2">
        <!-- Brand panel -->
        <aside class="brand-panel relative hidden lg:flex">
            <div class="brand-orb brand-orb-a"></div>
            <div class="brand-orb brand-orb-b"></div>
            <div class="brand-grid"></div>

            <div
                class="relative flex w-full flex-col justify-between p-12 text-white"
            >
                <Link :href="home()" class="group flex items-center gap-3">
                    <CodeSparkLogo class="brand-logo h-9 w-9 shrink-0" />
                    <span class="flex items-center gap-2">
                        <span class="text-lg font-extrabold tracking-tight"
                            >CODESPARK</span
                        >
                        <span class="h-4 w-px bg-white/30"></span>
                        <span
                            class="text-xs font-semibold tracking-[0.12em] text-white/70"
                            >PH</span
                        >
                    </span>
                </Link>

                <!-- Floating 3D stack -->
                <div class="stage my-10">
                    <div
                        class="stack"
                        :style="
                            tiltEnabled
                                ? {
                                      transform: `rotateX(${10 - pointer.y * 12}deg) rotateY(${-16 + pointer.x * 16}deg)`,
                                  }
                                : undefined
                        "
                    >
                        <div class="panel panel-back">
                            <div class="panel-bar">
                                <span></span><span></span><span></span>
                            </div>
                            <div class="space-y-2 p-5">
                                <div class="line line-cyan w-2/3"></div>
                                <div class="line w-5/6"></div>
                                <div class="line w-1/2"></div>
                                <div class="line line-violet w-3/4"></div>
                            </div>
                        </div>

                        <div class="panel panel-mid">
                            <p
                                class="text-[0.65rem] tracking-widest text-cyan-300/90 uppercase"
                            >
                                New requests
                            </p>
                            <p class="mt-1 text-2xl font-bold">3</p>
                            <div class="meter mt-3">
                                <div class="meter-fill"></div>
                            </div>
                        </div>

                        <div class="panel panel-front">
                            <div class="flex items-center gap-2.5">
                                <span class="dot"></span>
                                <div>
                                    <p class="text-xs font-semibold">
                                        Consultation booked
                                    </p>
                                    <p class="text-[0.65rem] text-white/60">
                                        Today, 2:30 PM
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h2
                        class="max-w-sm text-3xl leading-tight font-bold tracking-tight text-balance"
                    >
                        Where ideas become digital reality.
                    </h2>

                    <ul class="mt-6 space-y-3">
                        <li
                            v-for="point in points"
                            :key="point.text"
                            class="flex items-center gap-3 text-sm text-white/80"
                        >
                            <span
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/10 ring-1 ring-white/20"
                            >
                                <component :is="point.Icon" class="h-4 w-4" />
                            </span>
                            {{ point.text }}
                        </li>
                    </ul>

                    <p class="mt-8 text-xs text-white/50">
                        Zamboanga City, Philippines · Web Development &amp;
                        Digital Solutions
                    </p>
                </div>
            </div>
        </aside>

        <!-- Form panel -->
        <main class="form-panel relative flex flex-col p-6 sm:p-10">
            <div class="form-orb"></div>

            <Link
                :href="home()"
                class="text-muted-foreground hover:text-foreground relative inline-flex items-center gap-1.5 self-start text-sm transition-colors"
            >
                <ArrowLeft class="h-4 w-4" />
                Back to website
            </Link>

            <div class="relative flex flex-1 items-center justify-center py-10">
                <div class="form-card w-full max-w-sm">
                    <div class="flex flex-col items-center gap-3 lg:hidden">
                        <CodeSparkLogo class="h-10 w-10" />
                        <span class="flex items-center gap-2">
                            <span class="font-extrabold tracking-tight"
                                >CODESPARK</span
                            >
                            <span class="bg-border h-4 w-px"></span>
                            <span
                                class="text-muted-foreground text-xs font-semibold tracking-[0.12em]"
                                >PH</span
                            >
                        </span>
                    </div>

                    <div class="mt-8 space-y-2 lg:mt-0">
                        <h1 class="text-2xl font-bold tracking-tight">
                            {{ title }}
                        </h1>
                        <p class="text-muted-foreground text-sm">
                            {{ description }}
                        </p>
                    </div>

                    <div class="mt-8">
                        <slot />
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
/* The panel is a saturated brand surface in both themes, so its colors are
   fixed rather than theme-tokenised — white text stays legible either way. */
.brand-panel {
    overflow: hidden;
    background: linear-gradient(145deg, #0b1020 0%, #14123a 55%, #2a1145 100%);
}

.brand-logo {
    filter: drop-shadow(0 0 12px rgba(56, 232, 255, 0.5));
    transition: transform 500ms cubic-bezier(0.22, 1, 0.36, 1);
}
.group:hover .brand-logo {
    transform: rotate(-8deg) scale(1.08);
}

.brand-orb {
    position: absolute;
    border-radius: 9999px;
    filter: blur(90px);
    opacity: 0.55;
    animation: drift 20s ease-in-out infinite;
}
.brand-orb-a {
    top: -6rem;
    left: -4rem;
    height: 24rem;
    width: 24rem;
    background: #06b6d4;
}
.brand-orb-b {
    right: -6rem;
    bottom: -6rem;
    height: 26rem;
    width: 26rem;
    background: #7c3aed;
    animation-direction: reverse;
}

.brand-grid {
    position: absolute;
    inset: auto 0 0 0;
    height: 60%;
    background-image:
        linear-gradient(rgba(255, 255, 255, 0.07) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.07) 1px, transparent 1px);
    background-size: 48px 48px;
    transform: perspective(500px) rotateX(70deg);
    transform-origin: bottom center;
    mask-image: linear-gradient(to top, black, transparent 75%);
}

@keyframes drift {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }
    50% {
        transform: translate3d(2rem, -1.5rem, 0) scale(1.1);
    }
}

/* Floating 3D stack */
.stage {
    perspective: 1100px;
    display: flex;
    justify-content: center;
}
.stack {
    position: relative;
    height: 13rem;
    width: 100%;
    max-width: 20rem;
    transform-style: preserve-3d;
    transition: transform 400ms cubic-bezier(0.22, 1, 0.36, 1);
}
.panel {
    position: absolute;
    border-radius: 0.9rem;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: rgba(12, 16, 34, 0.68);
    backdrop-filter: blur(14px);
    box-shadow: 0 30px 60px -25px rgba(0, 0, 0, 0.85);
}
.panel-back {
    inset: 0 0 2rem 0;
    animation: float 7s ease-in-out infinite;
}
.panel-mid {
    right: -1rem;
    bottom: 1.5rem;
    width: 9.5rem;
    padding: 0.9rem;
    transform: translateZ(60px);
    animation: float 6s ease-in-out infinite 0.6s;
}
.panel-front {
    bottom: -1rem;
    left: -0.75rem;
    padding: 0.7rem 0.9rem;
    transform: translateZ(110px);
    animation: float 8s ease-in-out infinite 1.2s;
}
.panel-bar {
    display: flex;
    gap: 0.35rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    padding: 0.6rem 1rem;
}
.panel-bar span {
    height: 0.5rem;
    width: 0.5rem;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.2);
}
.line {
    height: 0.45rem;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.14);
}
.line-cyan {
    background: rgba(56, 232, 255, 0.45);
}
.line-violet {
    background: rgba(167, 139, 250, 0.45);
}

.meter {
    height: 0.3rem;
    width: 100%;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.14);
}
.meter-fill {
    height: 100%;
    width: 72%;
    border-radius: 9999px;
    background: linear-gradient(90deg, #38e8ff, #7c6bff);
}

.dot {
    height: 0.5rem;
    width: 0.5rem;
    flex-shrink: 0;
    border-radius: 9999px;
    background: #34d399;
    box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.2);
}

@keyframes float {
    0%,
    100% {
        translate: 0 0;
    }
    50% {
        translate: 0 -10px;
    }
}

/* Form side gets a faint echo of the brand glow so the two halves relate. */
.form-panel {
    overflow: hidden;
}
.form-orb {
    position: absolute;
    top: -10rem;
    right: -10rem;
    height: 22rem;
    width: 22rem;
    border-radius: 9999px;
    background: #7c3aed;
    opacity: 0.07;
    filter: blur(80px);
}

.form-card {
    animation: rise 600ms cubic-bezier(0.22, 1, 0.36, 1) both;
}

@keyframes rise {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
}

@media (prefers-reduced-motion: reduce) {
    .brand-orb,
    .panel,
    .form-card {
        animation: none;
    }
    .stack {
        transition: none;
    }
}
</style>
