<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import { Trash2 } from '@lucide/vue';
import { ref, watch } from 'vue';
import BookingController from '@/actions/App/Http/Controllers/Admin/BookingController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/admin/bookings';

type Booking = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    service: string;
    budget: string | null;
    details: string;
    status: string;
    scheduled_at: string | null;
    notes: string | null;
    created_at: string;
};

type Paginated = {
    data: Booking[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
    from: number | null;
    to: number | null;
};

const props = defineProps<{
    bookings: Paginated;
    statuses: string[];
    filters: { status: string | null; search: string | null };
    counts: Record<string, number>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Bookings', href: index() }],
    },
});

const search = ref(props.filters.search ?? '');
const expanded = ref<number | null>(null);

let searchTimer: ReturnType<typeof setTimeout> | undefined;

watch(search, (value) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(
            index().url,
            { search: value || undefined, status: props.filters.status },
            { preserveState: true, replace: true },
        );
    }, 300);
});

const statusClass = (status: string) =>
    ({
        new: 'bg-sky-500/10 text-sky-600 dark:text-sky-400',
        contacted: 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
        scheduled: 'bg-violet-500/10 text-violet-600 dark:text-violet-400',
        completed: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
        declined: 'bg-muted text-muted-foreground',
    })[status] ?? 'bg-muted text-muted-foreground';

const confirmDelete = (event: Event, name: string) => {
    if (!window.confirm(`Delete the request from ${name}?`)) {
        event.preventDefault();
    }
};

const formatDate = (value: string | null) =>
    value ? new Date(value).toLocaleString() : '—';

const toDateTimeLocal = (value: string | null) =>
    value ? new Date(value).toISOString().slice(0, 16) : '';
</script>

<template>
    <Head title="Bookings" />

    <div class="flex flex-col gap-6 p-4">
        <Heading
            variant="small"
            title="Bookings & consultations"
            description="Every project request that comes in through the landing page"
        />

        <div class="flex flex-wrap items-center gap-3">
            <Input
                v-model="search"
                placeholder="Search name, email, or service…"
                class="max-w-xs"
            />

            <div class="flex flex-wrap gap-1.5">
                <Link
                    :href="index({ query: { search: filters.search } }).url"
                    :class="[
                        'rounded-full px-3 py-1 text-xs font-medium',
                        !filters.status
                            ? 'bg-primary text-primary-foreground'
                            : 'bg-muted text-muted-foreground hover:text-foreground',
                    ]"
                >
                    All ({{ bookings.total }})
                </Link>
                <Link
                    v-for="status in statuses"
                    :key="status"
                    :href="
                        index({
                            query: { status, search: filters.search },
                        }).url
                    "
                    :class="[
                        'rounded-full px-3 py-1 text-xs font-medium capitalize',
                        filters.status === status
                            ? 'bg-primary text-primary-foreground'
                            : 'bg-muted text-muted-foreground hover:text-foreground',
                    ]"
                >
                    {{ status }} ({{ counts[status] ?? 0 }})
                </Link>
            </div>
        </div>

        <div
            v-if="bookings.data.length === 0"
            class="rounded-xl border border-dashed p-12 text-center"
        >
            <p class="text-muted-foreground text-sm">No requests found.</p>
        </div>

        <div v-else class="flex flex-col gap-3">
            <article
                v-for="booking in bookings.data"
                :key="booking.id"
                class="bg-card rounded-xl border"
            >
                <button
                    type="button"
                    class="hover:bg-muted/40 flex w-full flex-wrap items-center gap-3 p-4 text-left"
                    @click="
                        expanded = expanded === booking.id ? null : booking.id
                    "
                >
                    <span
                        :class="[
                            'rounded-full px-2 py-0.5 text-xs font-medium capitalize',
                            statusClass(booking.status),
                        ]"
                    >
                        {{ booking.status }}
                    </span>

                    <span class="font-medium">{{ booking.name }}</span>
                    <span class="text-muted-foreground text-sm">{{
                        booking.service
                    }}</span>

                    <span class="text-muted-foreground ml-auto text-xs">
                        {{ formatDate(booking.created_at) }}
                    </span>
                </button>

                <div v-if="expanded === booking.id" class="border-t p-4">
                    <dl class="grid gap-4 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-muted-foreground text-xs">Email</dt>
                            <dd>
                                <a
                                    :href="`mailto:${booking.email}`"
                                    class="underline underline-offset-4"
                                    >{{ booking.email }}</a
                                >
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground text-xs">Phone</dt>
                            <dd>
                                <a
                                    v-if="booking.phone"
                                    :href="`tel:${booking.phone}`"
                                    class="underline underline-offset-4"
                                    >{{ booking.phone }}</a
                                >
                                <span v-else>—</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground text-xs">
                                Budget
                            </dt>
                            <dd>{{ booking.budget ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground text-xs">
                                Scheduled
                            </dt>
                            <dd>{{ formatDate(booking.scheduled_at) }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-muted-foreground text-xs">
                                Project details
                            </dt>
                            <dd class="whitespace-pre-line">
                                {{ booking.details }}
                            </dd>
                        </div>
                    </dl>

                    <Form
                        v-bind="BookingController.update.form(booking.id)"
                        :options="{ preserveScroll: true }"
                        class="mt-6 grid gap-4 border-t pt-4 sm:grid-cols-2"
                        v-slot="{ errors, processing }"
                    >
                        <div class="grid gap-2">
                            <Label :for="`status-${booking.id}`">Status</Label>
                            <select
                                :id="`status-${booking.id}`"
                                name="status"
                                class="border-input bg-background focus-visible:ring-ring w-full rounded-md border px-3 py-2 text-sm capitalize focus-visible:ring-2 focus-visible:outline-none"
                            >
                                <option
                                    v-for="status in statuses"
                                    :key="status"
                                    :value="status"
                                    :selected="status === booking.status"
                                    class="capitalize"
                                >
                                    {{ status }}
                                </option>
                            </select>
                            <InputError :message="errors.status" />
                        </div>

                        <div class="grid gap-2">
                            <Label :for="`scheduled-${booking.id}`"
                                >Consultation date</Label
                            >
                            <Input
                                :id="`scheduled-${booking.id}`"
                                name="scheduled_at"
                                type="datetime-local"
                                :default-value="
                                    toDateTimeLocal(booking.scheduled_at)
                                "
                            />
                            <InputError :message="errors.scheduled_at" />
                        </div>

                        <div class="grid gap-2 sm:col-span-2">
                            <Label :for="`notes-${booking.id}`"
                                >Internal notes</Label
                            >
                            <textarea
                                :id="`notes-${booking.id}`"
                                name="notes"
                                rows="3"
                                :value="booking.notes ?? ''"
                                placeholder="What was discussed, next steps, quoted price…"
                                class="border-input bg-background focus-visible:ring-ring w-full resize-y rounded-md border px-3 py-2 text-sm focus-visible:ring-2 focus-visible:outline-none"
                            ></textarea>
                            <InputError :message="errors.notes" />
                        </div>

                        <div class="flex items-center gap-2 sm:col-span-2">
                            <Button
                                type="submit"
                                size="sm"
                                :disabled="processing"
                                >Save</Button
                            >
                        </div>
                    </Form>

                    <Form
                        v-bind="BookingController.destroy.form(booking.id)"
                        :options="{ preserveScroll: true }"
                        class="mt-3 border-t pt-3"
                        @submit="confirmDelete($event, booking.name)"
                    >
                        <Button size="sm" variant="ghost" type="submit">
                            <Trash2 class="h-3.5 w-3.5" />
                            Delete request
                        </Button>
                    </Form>
                </div>
            </article>

            <div
                v-if="bookings.links.length > 3"
                class="flex flex-wrap items-center gap-1 pt-2"
            >
                <template v-for="link in bookings.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        :class="[
                            'rounded-md px-3 py-1.5 text-sm',
                            link.active
                                ? 'bg-primary text-primary-foreground'
                                : 'hover:bg-muted',
                        ]"
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="text-muted-foreground px-3 py-1.5 text-sm"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </div>
</template>
