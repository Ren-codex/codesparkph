<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import {
    ChevronLeft,
    ChevronRight,
    Menu,
    Monitor,
    Moon,
    Sun,
    X,
} from '@lucide/vue';
import type { CSSProperties } from 'vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import BookingController from '@/actions/App/Http/Controllers/BookingController';
import CodeSparkLogo from '@/components/CodeSparkLogo.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useAppearance } from '@/composables/useAppearance';

type Project = {
    id: number;
    name: string;
    type: string;
    summary: string;
    url: string;
    domain: string;
    cover_url: string | null;
};

const props = defineProps<{
    services: string[];
    budgets: string[];
    projects: Project[];
}>();

const { appearance, updateAppearance } = useAppearance();

const menuOpen = ref(false);

const navLinks = computed(() => [
    { href: '#services', label: 'Services' },
    ...(props.projects.length ? [{ href: '#work', label: 'Work' }] : []),
    { href: '#why-us', label: 'Why Us' },
    { href: '#booking', label: 'Book a Project' },
]);

const contact = {
    phone: '0976 375 2654',
    phoneHref: 'tel:+639763752654',
    facebook: 'https://www.facebook.com/profile.php?id=61594725682251',
};

const themes = [
    { value: 'light', Icon: Sun, label: 'Light' },
    { value: 'dark', Icon: Moon, label: 'Dark' },
    { value: 'system', Icon: Monitor, label: 'System' },
] as const;

const serviceCards = [
    {
        title: 'Modern & Responsive Websites',
        description:
            'Clean, fast, mobile-first sites that look sharp on every screen and load without the wait.',
        icon: 'M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Zm0 3h18M7 7.5h.01M10 7.5h.01',
    },
    {
        title: 'Business Websites & Landing Pages',
        description:
            'Focused pages built to introduce your brand and turn visitors into paying customers.',
        icon: 'M4 20V10m6 10V4m6 16v-7m-12 7h18',
    },
    {
        title: 'Custom Web Applications',
        description:
            'Tailor-made tools and systems built around how your business actually works.',
        icon: 'm8 16-4-4 4-4m8 0 4 4-4 4M14 4l-4 16',
    },
    {
        title: 'Mobile App Development',
        description:
            'Android and iOS apps that put your business in your customers’ pockets.',
        icon: 'M7 2h10a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Zm3 1.5h4M12 18.5h.01',
    },
    {
        title: 'Reservation & Booking Systems',
        description:
            'Let customers book, reserve, and pay online — no more endless back-and-forth messages.',
        icon: 'M8 2v4m8-4v4M3 10h18M5 6h14a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Zm11 9-3.5 3.5L11 17',
    },
    {
        title: 'Business Digital Solutions',
        description:
            'From automation to dashboards, the digital backbone your business needs to grow.',
        icon: 'M12 2 3 7l9 5 9-5-9-5Zm-9 10 9 5 9-5M3 17l9 5 9-5',
    },
];

const highlights = [
    {
        title: 'Built For You',
        description:
            'No copy-pasted templates. Every project is planned around your goals and your customers.',
    },
    {
        title: 'Local & Reachable',
        description:
            'Based in Zamboanga City. Real conversations from first message to launch day.',
    },
    {
        title: 'Launch Ready',
        description:
            'We handle the code so you can focus on running the business — idea to live site.',
    },
];

const tiltStyle = (event: MouseEvent) => {
    const card = event.currentTarget as HTMLElement;
    const rect = card.getBoundingClientRect();
    const x = (event.clientX - rect.left) / rect.width - 0.5;
    const y = (event.clientY - rect.top) / rect.height - 0.5;

    card.style.transform = `perspective(900px) rotateX(${-y * 10}deg) rotateY(${x * 12}deg) translateZ(18px)`;
};

const resetTilt = (event: MouseEvent) => {
    (event.currentTarget as HTMLElement).style.transform = '';
};

// Sections fade and rise as they scroll into view. Registered as a local
// directive so each element manages its own observer.
const revealObservers = new WeakMap<HTMLElement, IntersectionObserver>();

const vReveal = {
    mounted(el: HTMLElement) {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        el.classList.add('reveal');

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-revealed');
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.1, rootMargin: '0px 0px -6% 0px' },
        );

        observer.observe(el);
        revealObservers.set(el, observer);
    },
    unmounted(el: HTMLElement) {
        revealObservers.get(el)?.disconnect();
        revealObservers.delete(el);
    },
};

// 3D coverflow carousel for the finished projects.
const activeProject = ref(0);

/** Distance from the active card, wrapped so the ring takes the short way round. */
const projectOffset = (index: number) => {
    const count = props.projects.length;

    if (count === 0) {
        return 0;
    }

    let diff = index - activeProject.value;

    if (diff > count / 2) {
        diff -= count;
    } else if (diff < -count / 2) {
        diff += count;
    }

    return diff;
};

const projectStyle = (index: number): CSSProperties => {
    const offset = projectOffset(index);
    const depth = Math.abs(offset);

    return {
        transform: [
            `translateX(${offset * 54}%)`,
            `translateZ(${depth === 0 ? 0 : -140 - depth * 70}px)`,
            `rotateY(${offset * -26}deg)`,
        ].join(' '),
        opacity: depth > 2 ? 0 : 1,
        zIndex: 10 - depth,
        pointerEvents: depth === 0 ? 'auto' : 'none',
    };
};

const goProject = (direction: number) => {
    const count = props.projects.length;

    if (count > 0) {
        activeProject.value = (activeProject.value + direction + count) % count;
    }
};

const dragStartX = ref<number | null>(null);

const onDragStart = (event: PointerEvent) => {
    dragStartX.value = event.clientX;
};

const onDragEnd = (event: PointerEvent) => {
    if (dragStartX.value === null) {
        return;
    }

    const travelled = event.clientX - dragStartX.value;
    dragStartX.value = null;

    if (Math.abs(travelled) > 40) {
        goProject(travelled < 0 ? 1 : -1);
    }
};

const pointer = ref({ x: 0, y: 0 });
const tiltEnabled = ref(false);

const trackPointer = (event: MouseEvent) => {
    pointer.value = {
        x: event.clientX / window.innerWidth - 0.5,
        y: event.clientY / window.innerHeight - 0.5,
    };
};

// Pointer tilt is a desktop flourish: on touch screens it never fires, and the
// stack is scaled down there to fit anyway.
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
    finePointer = window.matchMedia('(min-width: 641px) and (pointer: fine)');
    finePointer.addEventListener('change', syncTilt);
    syncTilt();
});

onBeforeUnmount(() => {
    finePointer?.removeEventListener('change', syncTilt);
    window.removeEventListener('mousemove', trackPointer);
});
</script>

<template>
    <Head title="CodeSpark PH — Web Development & Digital Solutions">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="cs-page relative min-h-screen">
        <!-- Ambient 3D backdrop -->
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div class="orb orb-a"></div>
            <div class="orb orb-b"></div>
            <div class="orb orb-c"></div>
            <div class="grid-floor"></div>
            <div class="vignette absolute inset-0"></div>
            <div
                class="spotlight absolute inset-0"
                :style="{
                    '--mx': `${(pointer.x + 0.5) * 100}%`,
                    '--my': `${(pointer.y + 0.5) * 100}%`,
                }"
            ></div>
            <div class="grain absolute inset-0"></div>
        </div>

        <!-- Nav -->
        <header class="page-header sticky top-0 z-50">
            <div
                class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-6 py-4"
            >
                <a href="#top" class="group flex items-center gap-2.5">
                    <CodeSparkLogo class="logo-mark h-8 w-8 shrink-0" />
                    <span class="wordmark">
                        <span class="wordmark-name">CODESPARK</span>
                        <span class="wordmark-divider"></span>
                        <span class="wordmark-ph">PH</span>
                    </span>
                </a>

                <nav class="hidden items-center gap-8 text-sm md:flex">
                    <a
                        v-for="link in navLinks"
                        :key="link.href"
                        :href="link.href"
                        class="nav-link"
                        >{{ link.label }}</a
                    >
                </nav>

                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="theme-switch">
                        <button
                            v-for="{ value, Icon, label } in themes"
                            :key="value"
                            type="button"
                            :title="label"
                            :aria-label="`Switch to ${label} theme`"
                            :aria-pressed="appearance === value"
                            :class="[
                                'theme-option',
                                appearance === value && 'is-active',
                            ]"
                            @click="updateAppearance(value)"
                        >
                            <component :is="Icon" class="h-4 w-4" />
                        </button>
                    </div>

                    <Button
                        as-child
                        size="sm"
                        class="glow-button hidden sm:inline-flex"
                    >
                        <a href="#booking">Start Building</a>
                    </Button>

                    <button
                        type="button"
                        class="menu-toggle"
                        :aria-expanded="menuOpen"
                        aria-controls="mobile-menu"
                        :aria-label="menuOpen ? 'Close menu' : 'Open menu'"
                        @click="menuOpen = !menuOpen"
                    >
                        <component :is="menuOpen ? X : Menu" class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <!-- Mobile menu -->
            <Transition name="menu">
                <nav v-if="menuOpen" id="mobile-menu" class="mobile-menu">
                    <a
                        v-for="link in navLinks"
                        :key="link.href"
                        :href="link.href"
                        class="mobile-link"
                        @click="menuOpen = false"
                        >{{ link.label }}</a
                    >
                    <a
                        :href="contact.phoneHref"
                        class="mobile-link accent"
                        @click="menuOpen = false"
                        >Call {{ contact.phone }}</a
                    >
                </nav>
            </Transition>
        </header>

        <main id="top" class="relative">
            <!-- Hero -->
            <section
                class="mx-auto max-w-6xl px-6 pt-12 pb-16 sm:pt-24 sm:pb-24"
            >
                <div class="grid items-center gap-12 sm:gap-16 lg:grid-cols-2">
                    <div>
                        <span class="locale-badge">
                            <span class="relative flex h-2 w-2">
                                <span class="ping"></span>
                                <span class="dot"></span>
                            </span>
                            Based in Zamboanga City, Philippines
                        </span>

                        <h1 class="mt-7">
                            <span class="headline-lead">
                                Your business deserves more than just a website.
                            </span>
                            <span class="headline-punch gradient-text">
                                It deserves a digital experience.
                            </span>
                        </h1>

                        <p class="muted mt-6 max-w-xl text-lg">
                            Your idea. Our code. Your next big digital success.
                            In a world where first impressions happen online,
                            your website should do more than just exist — it
                            should attract, impress, and convert.
                        </p>

                        <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                            <a href="#booking" class="cta cta-primary">
                                Book Your Project
                            </a>
                            <a href="#services" class="cta cta-ghost">
                                See What We Build
                            </a>
                        </div>
                    </div>

                    <!-- Floating 3D stack -->
                    <div class="stage">
                        <div
                            class="stack"
                            :style="
                                tiltEnabled
                                    ? {
                                          transform: `rotateX(${12 - pointer.y * 14}deg) rotateY(${-18 + pointer.x * 18}deg)`,
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
                                    <div class="line w-2/5"></div>
                                </div>
                            </div>

                            <div class="panel panel-mid">
                                <p
                                    class="accent text-[0.65rem] tracking-widest uppercase"
                                >
                                    Booking system
                                </p>
                                <p
                                    class="mt-2 text-sm leading-snug font-semibold"
                                >
                                    Customers book themselves in
                                </p>
                                <p class="muted mt-1.5 text-xs leading-snug">
                                    No more back-and-forth messages
                                </p>
                                <div class="meter mt-3.5">
                                    <div class="meter-fill"></div>
                                </div>
                            </div>

                            <div class="panel panel-front">
                                <div class="flex items-center gap-3">
                                    <div class="avatar">CS</div>
                                    <div>
                                        <p class="text-sm font-semibold">
                                            Project Kickoff
                                        </p>
                                        <p class="muted text-xs">
                                            Confirmed — let's build
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Services -->
            <section
                id="services"
                class="relative mx-auto max-w-6xl px-6 py-16 sm:py-24"
            >
                <div v-reveal class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">
                        What we can build for you
                    </h2>
                    <p class="muted mt-4">
                        Whether you're a startup, small business, or growing
                        company, we're here to turn your vision into a powerful
                        digital experience.
                    </p>
                </div>

                <div
                    v-reveal
                    class="reveal-stagger mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <article
                        v-for="service in serviceCards"
                        :key="service.title"
                        class="tilt-card"
                        @mousemove="tiltStyle"
                        @mouseleave="resetTilt"
                    >
                        <div class="service-icon">
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path :d="service.icon" />
                            </svg>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold">
                            {{ service.title }}
                        </h3>
                        <p class="muted mt-2 text-sm">
                            {{ service.description }}
                        </p>
                    </article>
                </div>
            </section>

            <!-- Finished projects -->
            <section
                v-if="projects.length"
                id="work"
                class="relative mx-auto max-w-6xl px-6 py-16 sm:py-24"
            >
                <div v-reveal class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">
                        Finished projects
                    </h2>
                    <p class="muted mt-4">
                        Real businesses, shipped and live. Here's a look at what
                        we've built.
                    </p>
                </div>

                <div v-reveal class="carousel mt-14">
                    <div
                        class="carousel-stage"
                        role="group"
                        aria-roledescription="carousel"
                        aria-label="Finished projects"
                        tabindex="0"
                        @keydown.left.prevent="goProject(-1)"
                        @keydown.right.prevent="goProject(1)"
                        @pointerdown="onDragStart"
                        @pointerup="onDragEnd"
                        @pointercancel="dragStartX = null"
                    >
                        <article
                            v-for="(project, i) in projects"
                            :key="project.id"
                            class="carousel-card"
                            :class="{ 'is-active': i === activeProject }"
                            :style="projectStyle(i)"
                            :aria-hidden="i !== activeProject"
                        >
                            <div class="cover">
                                <img
                                    v-if="project.cover_url"
                                    :src="project.cover_url"
                                    :alt="`${project.name} website`"
                                    class="cover-img"
                                    loading="lazy"
                                />
                                <div v-else class="cover-fallback">
                                    <span class="cover-initial">{{
                                        project.name.charAt(0)
                                    }}</span>
                                    <span class="cover-domain">{{
                                        project.domain
                                    }}</span>
                                </div>
                                <span class="cover-scan"></span>
                            </div>

                            <p
                                class="accent mt-5 text-xs tracking-widest uppercase"
                            >
                                {{ project.type }}
                            </p>
                            <h3 class="mt-2 text-xl font-semibold">
                                {{ project.name }}
                            </h3>
                            <p class="muted mt-3 text-sm">
                                {{ project.summary }}
                            </p>
                            <div
                                class="mt-6 flex flex-wrap items-center justify-between gap-2"
                            >
                                <a
                                    :href="project.url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="visit-link accent"
                                    :tabindex="i === activeProject ? 0 : -1"
                                >
                                    Visit site
                                    <svg
                                        class="h-3.5 w-3.5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path d="M7 17 17 7M8 7h9v9" />
                                    </svg>
                                </a>
                                <span class="subtle text-xs">{{
                                    project.domain
                                }}</span>
                            </div>
                        </article>
                    </div>

                    <div class="carousel-controls">
                        <button
                            type="button"
                            class="carousel-arrow"
                            aria-label="Previous project"
                            @click="goProject(-1)"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </button>

                        <div class="carousel-dots">
                            <button
                                v-for="(project, i) in projects"
                                :key="project.id"
                                type="button"
                                class="carousel-dot"
                                :class="{ 'is-active': i === activeProject }"
                                :aria-label="`Show ${project.name}`"
                                :aria-current="i === activeProject"
                                @click="activeProject = i"
                            ></button>
                        </div>

                        <button
                            type="button"
                            class="carousel-arrow"
                            aria-label="Next project"
                            @click="goProject(1)"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </section>

            <!-- Why us -->
            <section
                id="why-us"
                class="relative mx-auto max-w-6xl px-6 py-16 sm:py-24"
            >
                <div v-reveal class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">
                        Stop dreaming. Start building.
                    </h2>
                </div>

                <div
                    v-reveal
                    class="reveal-stagger mt-14 grid gap-10 sm:grid-cols-3"
                >
                    <div
                        v-for="item in highlights"
                        :key="item.title"
                        class="text-center"
                    >
                        <div class="spark-badge">
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M13 2 3 14h7l-1 8 10-12h-7l1-8Z" />
                            </svg>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold">
                            {{ item.title }}
                        </h3>
                        <p class="muted mt-2 text-sm">
                            {{ item.description }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Booking -->
            <section
                id="booking"
                class="relative mx-auto max-w-4xl px-6 py-16 sm:py-24"
            >
                <div v-reveal class="booking-shell">
                    <div class="mx-auto max-w-xl text-center">
                        <h2
                            class="text-3xl font-bold tracking-tight sm:text-4xl"
                        >
                            Book your project
                        </h2>
                        <p class="muted mt-4">
                            Tell us what you need and we'll get back to you with
                            a plan, a timeline, and a quote.
                        </p>

                        <div
                            class="mt-6 flex flex-wrap items-center justify-center gap-3"
                        >
                            <a :href="contact.phoneHref" class="contact-chip">
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z"
                                    />
                                </svg>
                                {{ contact.phone }}
                            </a>
                            <a
                                :href="contact.facebook"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="contact-chip"
                            >
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                >
                                    <path
                                        d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0 0 22 12Z"
                                    />
                                </svg>
                                Message us on Facebook
                            </a>
                        </div>
                    </div>

                    <Form
                        v-bind="BookingController.store.form()"
                        reset-on-success
                        class="mt-10 space-y-5"
                        v-slot="{ errors, processing, recentlySuccessful }"
                    >
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="name">Name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    required
                                    placeholder="Juan Dela Cruz"
                                    class="field"
                                />
                                <InputError :message="errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="email">Email</Label>
                                <Input
                                    id="email"
                                    name="email"
                                    type="email"
                                    required
                                    placeholder="you@business.com"
                                    class="field"
                                />
                                <InputError :message="errors.email" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="phone">Phone (optional)</Label>
                                <Input
                                    id="phone"
                                    name="phone"
                                    placeholder="+63 900 000 0000"
                                    class="field"
                                />
                                <InputError :message="errors.phone" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="budget">Budget (optional)</Label>
                                <select
                                    id="budget"
                                    name="budget"
                                    class="field select"
                                >
                                    <option value="">Select a range</option>
                                    <option
                                        v-for="budget in budgets"
                                        :key="budget"
                                        :value="budget"
                                    >
                                        {{ budget }}
                                    </option>
                                </select>
                                <InputError :message="errors.budget" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="service">What do you need?</Label>
                            <select
                                id="service"
                                name="service"
                                required
                                class="field select"
                            >
                                <option value="">Choose a service</option>
                                <option
                                    v-for="service in services"
                                    :key="service"
                                    :value="service"
                                >
                                    {{ service }}
                                </option>
                            </select>
                            <InputError :message="errors.service" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="details">Project details</Label>
                            <textarea
                                id="details"
                                name="details"
                                rows="5"
                                required
                                placeholder="Tell us about your business and what you want to build."
                                class="field resize-y"
                            ></textarea>
                            <InputError :message="errors.details" />
                        </div>

                        <div class="flex flex-wrap items-center gap-4">
                            <Button
                                size="lg"
                                class="glow-button"
                                :disabled="processing"
                            >
                                {{ processing ? 'Sending…' : 'Send Request' }}
                            </Button>
                            <p
                                v-if="recentlySuccessful"
                                class="accent text-sm font-medium"
                            >
                                Request received — we'll be in touch shortly.
                            </p>
                        </div>
                    </Form>
                </div>
            </section>
        </main>

        <footer class="page-footer relative">
            <div class="mx-auto max-w-6xl px-6 py-12">
                <div
                    class="flex flex-col gap-8 sm:flex-row sm:items-start sm:justify-between"
                >
                    <div>
                        <div class="flex items-center gap-2.5">
                            <CodeSparkLogo class="h-7 w-7" />
                            <span class="wordmark">
                                <span class="wordmark-name">CODESPARK</span>
                                <span class="wordmark-divider"></span>
                                <span class="wordmark-ph">PH</span>
                            </span>
                        </div>
                        <p class="muted mt-3 max-w-sm text-sm">
                            Where Ideas Become Digital Reality. Web development
                            and digital solutions from Zamboanga City,
                            Philippines.
                        </p>
                    </div>

                    <div class="text-sm">
                        <p class="font-semibold">Get in touch</p>
                        <ul class="mt-3 space-y-2">
                            <li>
                                <a
                                    :href="contact.phoneHref"
                                    class="footer-link"
                                >
                                    {{ contact.phone }}
                                </a>
                            </li>
                            <li>
                                <a
                                    :href="contact.facebook"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="footer-link"
                                >
                                    Facebook Page
                                </a>
                            </li>
                            <li class="muted">Zamboanga City, Philippines</li>
                        </ul>
                    </div>
                </div>

                <p class="muted mt-10 text-xs">
                    © {{ new Date().getFullYear() }} CodeSpark PH. All rights
                    reserved.
                </p>
            </div>
        </footer>
    </div>
</template>

<!--
  Theme tokens live in an UNSCOPED block on purpose. Vue's scoped-CSS compiler
  rewrites `:global(.dark) .cs-page` down to a bare `.dark`, which would set the
  dark values on <html> where they are merely inherited — and an inherited value
  loses to the light value set directly on .cs-page. Plain CSS avoids that.
-->
<style>
.cs-page {
    --page-bg: #f6f7fb;
    --page-fg: #0f172a;
    --muted-fg: #475569;
    --subtle-fg: #64748b;
    --surface: rgba(255, 255, 255, 0.72);
    --surface-strong: rgba(255, 255, 255, 0.85);
    --header-bg: rgba(246, 247, 251, 0.72);
    --line: rgba(15, 23, 42, 0.12);
    --line-soft: rgba(15, 23, 42, 0.07);
    --grid-line: rgba(15, 23, 42, 0.1);
    --accent-fg: #0e7490;
    --accent-dot: #0891b2;
    --accent-bg: rgba(8, 145, 178, 0.1);
    --accent-border: rgba(8, 145, 178, 0.28);
    --brand-grad: linear-gradient(110deg, #0891b2, #6d28d9);
    --brand-grad-fg: #ffffff;
    --hero-grad: linear-gradient(110deg, #0891b2, #6d28d9 55%, #be185d);
    --orb-opacity: 0.3;
    --panel-shadow: 0 30px 70px -35px rgba(15, 23, 42, 0.45);
    --field-bg: #ffffff;
    --glow: rgba(8, 145, 178, 0.35);
    --glow-hover: rgba(109, 40, 217, 0.4);
    --line-cyan: rgba(8, 145, 178, 0.45);
    --line-violet: rgba(109, 40, 217, 0.4);
    --punch-glow: none;
    --spot: rgba(8, 145, 178, 0.1);
    --shine: rgba(15, 23, 42, 0.05);
    --grain-opacity: 0.025;

    position: relative;
    /* `clip` (not `hidden`) contains the ambient orbs without turning the page
       into a scroll container, which would break the sticky header. */
    overflow-x: clip;
    background: var(--page-bg);
    color: var(--page-fg);
}

.dark .cs-page {
    --page-bg: #05060f;
    --page-fg: #f1f5f9;
    --muted-fg: #94a3b8;
    --subtle-fg: #64748b;
    --surface: rgba(15, 23, 42, 0.5);
    --surface-strong: rgba(15, 23, 42, 0.62);
    --header-bg: rgba(5, 6, 15, 0.7);
    --line: rgba(255, 255, 255, 0.12);
    --line-soft: rgba(255, 255, 255, 0.06);
    --grid-line: rgba(148, 163, 184, 0.14);
    --accent-fg: #67e8f9;
    --accent-dot: #22d3ee;
    --accent-bg: rgba(34, 211, 238, 0.1);
    --accent-border: rgba(34, 211, 238, 0.25);
    --brand-grad: linear-gradient(110deg, #22d3ee, #7c3aed);
    --brand-grad-fg: #05060f;
    --hero-grad: linear-gradient(110deg, #22d3ee, #a78bfa 55%, #f472b6);
    --orb-opacity: 0.35;
    --panel-shadow: 0 30px 70px -25px rgba(0, 0, 0, 0.9);
    --field-bg: rgba(2, 6, 23, 0.6);
    --glow: rgba(34, 211, 238, 0.7);
    --glow-hover: rgba(124, 58, 237, 0.8);
    --line-cyan: rgba(34, 211, 238, 0.4);
    --line-violet: rgba(167, 139, 250, 0.4);
    --punch-glow: drop-shadow(0 8px 32px rgba(124, 58, 237, 0.35));
    --spot: rgba(56, 232, 255, 0.09);
    --shine: rgba(255, 255, 255, 0.07);
    --grain-opacity: 0.04;
}
</style>

<style scoped>
.muted {
    color: var(--muted-fg);
}
.subtle {
    color: var(--subtle-fg);
}
.accent {
    color: var(--accent-fg);
}

.page-header {
    border-bottom: 1px solid var(--line-soft);
    background: var(--header-bg);
    backdrop-filter: blur(16px);
}

/* Keep anchor targets clear of the sticky header. */
.cs-page section[id] {
    scroll-margin-top: 5.5rem;
}

.page-footer {
    border-top: 1px solid var(--line-soft);
}

.nav-link {
    color: var(--muted-fg);
    transition: color 200ms ease;
}
.nav-link:hover {
    color: var(--page-fg);
}

.gradient-text {
    background: var(--hero-grad);
    background-size: 220% auto;
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    animation: shimmer 9s linear infinite;
}

@keyframes shimmer {
    to {
        background-position: 220% center;
    }
}

.headline-lead {
    display: block;
    max-width: 18ch;
    font-size: clamp(1.35rem, 2.4vw, 1.85rem);
    font-weight: 600;
    line-height: 1.25;
    letter-spacing: -0.015em;
    color: var(--muted-fg);
    text-wrap: balance;
}

.headline-punch {
    display: block;
    margin-top: 0.6rem;
    max-width: 14ch;
    font-size: clamp(2.5rem, 6vw, 4.25rem);
    font-weight: 800;
    line-height: 1.02;
    letter-spacing: -0.035em;
    text-wrap: balance;
    filter: var(--punch-glow);
}

/* Mobile menu.
   Visibility is handled here rather than with Tailwind's `md:hidden`: these
   scoped rules carry the scope attribute, so they outrank that utility and
   would keep the menu on screen at desktop widths. */
.menu-toggle {
    display: grid;
    height: 2.5rem;
    width: 2.5rem;
    place-items: center;
    border-radius: 0.65rem;
    border: 1px solid var(--line);
    background: var(--surface);
    color: var(--page-fg);
    cursor: pointer;
}

.mobile-menu {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    border-top: 1px solid var(--line-soft);
    background: var(--header-bg);
    padding: 0.75rem 1.5rem 1.25rem;
    backdrop-filter: blur(16px);
}

@media (min-width: 768px) {
    .menu-toggle,
    .mobile-menu {
        display: none;
    }
}
.mobile-link {
    border-radius: 0.6rem;
    padding: 0.7rem 0.75rem;
    font-size: 0.95rem;
    font-weight: 500;
    color: var(--muted-fg);
}
.mobile-link:hover,
.mobile-link:focus-visible {
    background: var(--surface);
    color: var(--page-fg);
}

.menu-enter-active,
.menu-leave-active {
    transition:
        opacity 180ms ease,
        transform 180ms ease;
}
.menu-enter-from,
.menu-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

/* Theme switch */
.theme-switch {
    display: inline-flex;
    gap: 0.125rem;
    border-radius: 9999px;
    border: 1px solid var(--line);
    background: var(--surface);
    padding: 0.2rem;
}
.theme-option {
    display: grid;
    height: 1.75rem;
    width: 1.75rem;
    place-items: center;
    border-radius: 9999px;
    color: var(--subtle-fg);
    cursor: pointer;
    transition:
        color 180ms ease,
        background 180ms ease;
}
.theme-option:hover {
    color: var(--page-fg);
}
.theme-option.is-active {
    background: var(--brand-grad);
    color: var(--brand-grad-fg);
}

/* Buttons */
.cs-page .glow-button {
    background: var(--brand-grad);
    color: var(--brand-grad-fg);
    border: none;
    box-shadow: 0 10px 30px -10px var(--glow);
    transition:
        transform 200ms ease,
        box-shadow 200ms ease;
}
.cs-page .glow-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 40px -12px var(--glow-hover);
}

.cs-page .ghost-button {
    border: 1px solid var(--line);
    background: var(--surface);
    color: var(--page-fg);
    backdrop-filter: blur(10px);
    transition:
        border-color 200ms ease,
        background 200ms ease,
        transform 200ms ease;
}
.cs-page .ghost-button:hover {
    border-color: var(--accent-border);
    background: var(--surface-strong);
    color: var(--page-fg);
    transform: translateY(-2px);
}

/* Hero CTAs — plain anchors so no component variant can fight these styles. */
.cta {
    display: inline-flex;
    height: 2.875rem;
    align-items: center;
    justify-content: center;
    border-radius: 0.6rem;
    padding-inline: 1.6rem;
    font-size: 0.95rem;
    font-weight: 600;
    white-space: nowrap;
    transition:
        transform 200ms ease,
        box-shadow 200ms ease,
        background 200ms ease,
        border-color 200ms ease;
}
.cta:hover {
    transform: translateY(-2px);
}

.cta-primary {
    background: var(--brand-grad);
    color: var(--brand-grad-fg);
    box-shadow: 0 10px 30px -10px var(--glow);
}
.cta-primary:hover {
    box-shadow: 0 18px 44px -12px var(--glow-hover);
}

.cta-ghost {
    border: 1px solid var(--line);
    background: var(--surface);
    color: var(--page-fg);
    backdrop-filter: blur(10px);
}
.cta-ghost:hover {
    border-color: var(--accent-border);
    background: var(--surface-strong);
}

/* 3D project carousel */
.carousel-stage {
    position: relative;
    height: 33rem;
    perspective: 1500px;
    transform-style: preserve-3d;
    outline: none;
    touch-action: pan-y;
}
.carousel-stage:focus-visible {
    outline: 2px solid var(--accent-border);
    outline-offset: 12px;
    border-radius: 1rem;
}

.carousel-card {
    position: absolute;
    top: 0;
    right: 0;
    left: 0;
    margin-inline: auto;
    width: min(30rem, 88%);
    border-radius: 1rem;
    border: 1px solid var(--line);
    background: var(--surface-strong);
    padding: 1.75rem;
    backdrop-filter: blur(12px);
    box-shadow: var(--panel-shadow);
    transition:
        transform 650ms cubic-bezier(0.22, 1, 0.36, 1),
        opacity 500ms ease,
        border-color 300ms ease,
        box-shadow 500ms ease;
}
.carousel-card.is-active {
    border-color: var(--accent-border);
    box-shadow:
        var(--panel-shadow),
        0 30px 70px -35px var(--glow);
}

/* Project cover */
.cover {
    position: relative;
    overflow: hidden;
    border-radius: 0.7rem;
    border: 1px solid var(--line);
    aspect-ratio: 16 / 9;
    background: var(--surface);
}
.cover-img {
    height: 100%;
    width: 100%;
    object-fit: cover;
    object-position: top center;
}
.cover-fallback {
    display: flex;
    height: 100%;
    width: 100%;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    background:
        radial-gradient(120% 100% at 50% 0%, var(--accent-bg), transparent 70%),
        var(--surface-strong);
}
.cover-initial {
    background: var(--brand-grad);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    font-size: 2.5rem;
    font-weight: 800;
    line-height: 1;
}
.cover-domain {
    font-size: 0.7rem;
    color: var(--subtle-fg);
}

/* Holographic sweep across the cover. */
.cover-scan {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        105deg,
        transparent 42%,
        var(--shine) 50%,
        transparent 58%
    );
    background-size: 260% 100%;
    animation: sweep 7s ease-in-out infinite;
    pointer-events: none;
}

@keyframes sweep {
    0%,
    100% {
        background-position: 130% 0;
    }
    50% {
        background-position: -30% 0;
    }
}

.visit-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.875rem;
    font-weight: 500;
}
.visit-link svg {
    transition: transform 200ms ease;
}
.visit-link:hover svg {
    transform: translate(2px, -2px);
}

.carousel-controls {
    margin-top: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1.25rem;
}
.carousel-arrow {
    display: grid;
    height: 2.5rem;
    width: 2.5rem;
    place-items: center;
    border-radius: 9999px;
    border: 1px solid var(--line);
    background: var(--surface);
    color: var(--page-fg);
    cursor: pointer;
    transition:
        border-color 200ms ease,
        color 200ms ease,
        transform 200ms ease;
}
.carousel-arrow:hover {
    border-color: var(--accent-border);
    color: var(--accent-fg);
    transform: translateY(-2px);
}

.carousel-dots {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.carousel-dot {
    height: 0.5rem;
    width: 0.5rem;
    border-radius: 9999px;
    border: none;
    background: var(--line);
    cursor: pointer;
    transition:
        width 300ms ease,
        background 300ms ease;
}
.carousel-dot.is-active {
    width: 1.6rem;
    background: var(--brand-grad);
}

@media (max-width: 640px) {
    .carousel-stage {
        height: 32rem;
        perspective: 1000px;
    }
    .carousel-card {
        padding: 1.25rem;
    }
}

/* Scroll reveal */
.reveal {
    opacity: 0;
    transform: translateY(20px);
    transition:
        opacity 700ms cubic-bezier(0.22, 1, 0.36, 1),
        transform 700ms cubic-bezier(0.22, 1, 0.36, 1);
}
.reveal.is-revealed {
    opacity: 1;
    transform: none;
}

/* On staggered grids the children do the animating, not the container. */
.reveal-stagger.reveal {
    opacity: 1;
    transform: none;
}

/* Children of a staggered grid cascade in one after another. */
.reveal-stagger > * {
    opacity: 0;
    transform: translateY(18px);
    transition:
        opacity 600ms cubic-bezier(0.22, 1, 0.36, 1),
        transform 600ms cubic-bezier(0.22, 1, 0.36, 1);
}
.reveal-stagger.is-revealed > * {
    opacity: 1;
    transform: none;
}
.reveal-stagger.is-revealed > *:nth-child(2) {
    transition-delay: 80ms;
}
.reveal-stagger.is-revealed > *:nth-child(3) {
    transition-delay: 160ms;
}
.reveal-stagger.is-revealed > *:nth-child(4) {
    transition-delay: 240ms;
}
.reveal-stagger.is-revealed > *:nth-child(5) {
    transition-delay: 320ms;
}
.reveal-stagger.is-revealed > *:nth-child(6) {
    transition-delay: 400ms;
}

/* Ambient backdrop */
.orb {
    position: absolute;
    border-radius: 9999px;
    filter: blur(90px);
    opacity: var(--orb-opacity);
}
.orb-a {
    top: -8rem;
    left: -6rem;
    height: 26rem;
    width: 26rem;
    background: #06b6d4;
    animation: drift 18s ease-in-out infinite;
}
.orb-b {
    top: 20%;
    right: -8rem;
    height: 30rem;
    width: 30rem;
    background: #7c3aed;
    animation: drift 22s ease-in-out infinite reverse;
}
.orb-c {
    bottom: -10rem;
    left: 35%;
    height: 24rem;
    width: 24rem;
    background: #db2777;
    animation: drift 26s ease-in-out infinite;
}

.grid-floor {
    position: absolute;
    inset: auto 0 0 0;
    height: 55vh;
    background-image:
        linear-gradient(var(--grid-line) 1px, transparent 1px),
        linear-gradient(90deg, var(--grid-line) 1px, transparent 1px);
    background-size: 56px 56px;
    transform: perspective(500px) rotateX(70deg);
    transform-origin: bottom center;
    mask-image: linear-gradient(to top, black, transparent 70%);
}

.vignette {
    background: radial-gradient(
        ellipse at 50% 0%,
        transparent 20%,
        var(--page-bg) 75%
    );
}

/* A glow that trails the cursor, for depth on large screens. */
.spotlight {
    background: radial-gradient(
        38rem circle at var(--mx, 50%) var(--my, 35%),
        var(--spot),
        transparent 65%
    );
}

/* Fine film grain keeps the large flat gradients from banding. */
.grain {
    opacity: var(--grain-opacity);
    mix-blend-mode: overlay;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3'/%3E%3C/filter%3E%3Crect width='160' height='160' filter='url(%23n)'/%3E%3C/svg%3E");
}

@keyframes drift {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }
    50% {
        transform: translate3d(3rem, -2rem, 0) scale(1.12);
    }
}

/* Hero badge */
.locale-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border-radius: 9999px;
    border: 1px solid var(--accent-border);
    background: var(--accent-bg);
    padding: 0.375rem 1rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--accent-fg);
}
.ping {
    position: absolute;
    display: inline-flex;
    height: 100%;
    width: 100%;
    border-radius: 9999px;
    background: var(--accent-dot);
    opacity: 0.75;
    animation: ping 1.4s cubic-bezier(0, 0, 0.2, 1) infinite;
}
.dot {
    position: relative;
    display: inline-flex;
    height: 0.5rem;
    width: 0.5rem;
    border-radius: 9999px;
    background: var(--accent-dot);
}

@keyframes ping {
    75%,
    100% {
        transform: scale(2);
        opacity: 0;
    }
}

/* Hero 3D stack */
.stage {
    perspective: 1200px;
    display: flex;
    justify-content: center;
}
.stack {
    position: relative;
    height: 26rem;
    width: 100%;
    max-width: 26rem;
    transform-style: preserve-3d;
    transition: transform 400ms cubic-bezier(0.22, 1, 0.36, 1);
}
.panel {
    position: absolute;
    border-radius: 1rem;
    border: 1px solid var(--line);
    background: var(--surface-strong);
    backdrop-filter: blur(14px);
    box-shadow: var(--panel-shadow);
}
.panel-back {
    inset: 0 0 4rem 0;
    transform: translateZ(0);
    animation: float 7s ease-in-out infinite;
}
.panel-mid {
    right: -1.5rem;
    bottom: 3.5rem;
    width: 15rem;
    padding: 1.25rem;
    transform: translateZ(70px);
    animation: float 6s ease-in-out infinite 0.6s;
}
.panel-front {
    bottom: -0.5rem;
    left: -1rem;
    padding: 1rem 1.25rem;
    transform: translateZ(130px);
    animation: float 8s ease-in-out infinite 1.2s;
}
.panel-bar {
    display: flex;
    gap: 0.4rem;
    border-bottom: 1px solid var(--line-soft);
    padding: 0.75rem 1.25rem;
}
.panel-bar span {
    height: 0.6rem;
    width: 0.6rem;
    border-radius: 9999px;
    background: var(--line);
}
.line {
    height: 0.55rem;
    border-radius: 9999px;
    background: var(--line);
}
.line-cyan {
    background: var(--line-cyan);
}
.line-violet {
    background: var(--line-violet);
}

.meter {
    height: 0.375rem;
    width: 100%;
    border-radius: 9999px;
    background: var(--line);
}
.meter-fill {
    height: 100%;
    width: 98%;
    border-radius: 9999px;
    background: var(--brand-grad);
}

.avatar {
    display: grid;
    height: 2.25rem;
    width: 2.25rem;
    place-items: center;
    border-radius: 0.5rem;
    background: var(--brand-grad);
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--brand-grad-fg);
}

@keyframes float {
    0%,
    100% {
        translate: 0 0;
    }
    50% {
        translate: 0 -12px;
    }
}

/* Logo */
.logo-mark {
    filter: drop-shadow(0 0 10px var(--glow));
    transition: transform 500ms cubic-bezier(0.22, 1, 0.36, 1);
}
.group:hover .logo-mark {
    transform: rotate(-8deg) scale(1.08);
}

.wordmark {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.wordmark-name {
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: 0.02em;
}
.wordmark-divider {
    height: 1rem;
    width: 1px;
    background: var(--line);
}
.wordmark-ph {
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.12em;
    color: var(--muted-fg);
}

/* Tilt cards */
.tilt-card {
    border-radius: 1rem;
    border: 1px solid var(--line);
    background: var(--surface);
    padding: 1.5rem;
    backdrop-filter: blur(10px);
    transform-style: preserve-3d;
    transition:
        transform 250ms cubic-bezier(0.22, 1, 0.36, 1),
        opacity 600ms cubic-bezier(0.22, 1, 0.36, 1),
        border-color 250ms ease,
        box-shadow 250ms ease;
}
.tilt-card:hover {
    border-color: var(--accent-border);
    box-shadow: 0 30px 60px -30px var(--glow);
}

.service-icon {
    display: grid;
    height: 2.75rem;
    width: 2.75rem;
    place-items: center;
    border-radius: 0.75rem;
    border: 1px solid var(--line);
    background: var(--accent-bg);
    color: var(--accent-fg);
}

.spark-badge {
    margin-inline: auto;
    display: grid;
    height: 3rem;
    width: 3rem;
    place-items: center;
    border-radius: 9999px;
    background: var(--brand-grad);
    color: var(--brand-grad-fg);
    box-shadow: 0 18px 40px -18px var(--glow-hover);
}

/* Contact */
.contact-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border-radius: 9999px;
    border: 1px solid var(--line);
    background: var(--surface);
    padding: 0.45rem 0.95rem;
    font-size: 0.8125rem;
    font-weight: 500;
    color: var(--page-fg);
    transition:
        border-color 200ms ease,
        color 200ms ease,
        transform 200ms ease;
}
.contact-chip:hover {
    border-color: var(--accent-border);
    color: var(--accent-fg);
    transform: translateY(-1px);
}

.footer-link {
    color: var(--muted-fg);
    transition: color 200ms ease;
}
.footer-link:hover {
    color: var(--accent-fg);
}

/* Booking */
.booking-shell {
    border-radius: 1.5rem;
    border: 1px solid var(--line);
    background: var(--surface-strong);
    padding: 2.5rem 1.75rem;
    backdrop-filter: blur(18px);
    box-shadow: 0 40px 90px -40px var(--glow);
}
@media (min-width: 640px) {
    .booking-shell {
        padding: 3rem;
    }
}

.cs-page .field {
    width: 100%;
    border-radius: 0.6rem;
    border: 1px solid var(--line);
    background: var(--field-bg);
    padding: 0.55rem 0.75rem;
    font-size: 0.875rem;
    color: var(--page-fg);
    outline: none;
    transition:
        border-color 180ms ease,
        box-shadow 180ms ease;
}
.cs-page .field:focus {
    border-color: var(--accent-border);
    box-shadow: 0 0 0 3px var(--accent-bg);
}
.select option {
    background: var(--page-bg);
    color: var(--page-fg);
}

/* Mobile refinements */
@media (max-width: 480px) {
    .wordmark-name {
        font-size: 0.9rem;
    }
    .wordmark-divider,
    .wordmark-ph {
        display: none;
    }
    .theme-option {
        height: 2rem;
        width: 2rem;
    }
    .booking-shell {
        border-radius: 1.25rem;
        padding: 1.75rem 1.25rem;
    }
    .headline-lead,
    .headline-punch {
        max-width: none;
    }
}

/* The floating stack is sized for desktop; scale it to fit narrow screens. */
@media (max-width: 640px) {
    .stage {
        overflow: hidden;
        padding-block: 1rem;
    }
    .stack {
        height: 20rem;
        max-width: 19rem;
    }
    .panel-mid {
        right: 0;
        width: 12.5rem;
        padding: 1rem;
    }
    .panel-front {
        left: 0;
        padding: 0.85rem 1rem;
    }
}

@media (prefers-reduced-motion: reduce) {
    .orb,
    .panel,
    .ping,
    .cover-scan,
    .gradient-text {
        animation: none;
    }
    .spotlight {
        display: none;
    }
    .stack,
    .tilt-card {
        transition: none;
    }
}
</style>
