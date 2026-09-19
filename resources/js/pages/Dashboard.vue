<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { index as bookingsIndex } from '@/routes/admin/bookings';
import { index as projectsIndex } from '@/routes/admin/projects';

type Booking = {
    id: number;
    name: string;
    service: string;
    status: string;
    scheduled_at: string | null;
    created_at: string;
};

const props = defineProps<{
    stats: {
        bookings: number;
        newBookings: number;
        openBookings: number;
        projects: number;
        publishedProjects: number;
    };
    latestBookings: Booking[];
    upcoming: Booking[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});

const tiles = [
    {
        label: 'New requests',
        value: props.stats.newBookings,
        hint: 'waiting for a first reply',
    },
    {
        label: 'Open requests',
        value: props.stats.openBookings,
        hint: 'not yet completed or declined',
    },
    {
        label: 'Total requests',
        value: props.stats.bookings,
        hint: 'all time',
    },
    {
        label: 'Projects live',
        value: `${props.stats.publishedProjects}/${props.stats.projects}`,
        hint: 'shown on the landing page',
    },
];

const formatDate = (value: string | null) =>
    value ? new Date(value).toLocaleString() : '—';
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col gap-4 p-4">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="tile in tiles"
                :key="tile.label"
                class="bg-card rounded-xl border p-5"
            >
                <p class="text-muted-foreground text-sm">{{ tile.label }}</p>
                <p class="mt-2 text-3xl font-bold">{{ tile.value }}</p>
                <p class="text-muted-foreground mt-1 text-xs">
                    {{ tile.hint }}
                </p>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <section class="bg-card rounded-xl border">
                <header class="flex items-center justify-between border-b p-4">
                    <h2 class="font-semibold">Latest requests</h2>
                    <Link
                        :href="bookingsIndex()"
                        class="text-muted-foreground hover:text-foreground text-sm underline underline-offset-4"
                        >View all</Link
                    >
                </header>
                <ul v-if="latestBookings.length" class="divide-y">
                    <li
                        v-for="booking in latestBookings"
                        :key="booking.id"
                        class="flex items-center gap-3 p-4 text-sm"
                    >
                        <span class="font-medium">{{ booking.name }}</span>
                        <span class="text-muted-foreground truncate">{{
                            booking.service
                        }}</span>
                        <span
                            class="text-muted-foreground ml-auto shrink-0 text-xs"
                            >{{ formatDate(booking.created_at) }}</span
                        >
                    </li>
                </ul>
                <p v-else class="text-muted-foreground p-6 text-sm">
                    No requests yet.
                </p>
            </section>

            <section class="bg-card rounded-xl border">
                <header class="flex items-center justify-between border-b p-4">
                    <h2 class="font-semibold">Upcoming consultations</h2>
                    <Link
                        :href="projectsIndex()"
                        class="text-muted-foreground hover:text-foreground text-sm underline underline-offset-4"
                        >Projects</Link
                    >
                </header>
                <ul v-if="upcoming.length" class="divide-y">
                    <li
                        v-for="booking in upcoming"
                        :key="booking.id"
                        class="flex items-center gap-3 p-4 text-sm"
                    >
                        <span class="font-medium">{{ booking.name }}</span>
                        <span class="text-muted-foreground truncate">{{
                            booking.service
                        }}</span>
                        <span
                            class="text-muted-foreground ml-auto shrink-0 text-xs"
                            >{{ formatDate(booking.scheduled_at) }}</span
                        >
                    </li>
                </ul>
                <p v-else class="text-muted-foreground p-6 text-sm">
                    Nothing scheduled. Set a consultation date on a request to
                    see it here.
                </p>
            </section>
        </div>
    </div>
</template>
