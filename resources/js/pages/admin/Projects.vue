<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import ProjectController from '@/actions/App/Http/Controllers/Admin/ProjectController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/admin/projects';

type Project = {
    id: number;
    name: string;
    type: string;
    summary: string;
    url: string;
    domain: string;
    cover_url: string | null;
    position: number;
    is_published: boolean;
};

defineProps<{ projects: Project[] }>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Projects', href: index() }],
    },
});

const editing = ref<Project | null>(null);
const dialogOpen = ref(false);

const openCreate = () => {
    editing.value = null;
    dialogOpen.value = true;
};

const openEdit = (project: Project) => {
    editing.value = project;
    dialogOpen.value = true;
};

const confirmDelete = (event: Event, name: string) => {
    if (!window.confirm(`Delete "${name}"? This cannot be undone.`)) {
        event.preventDefault();
    }
};
</script>

<template>
    <Head title="Projects" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <Heading
                variant="small"
                title="Finished projects"
                description="Manage what appears in the Work section of the landing page"
            />
            <Button size="sm" @click="openCreate">
                <Plus class="h-4 w-4" />
                Add project
            </Button>
        </div>

        <div
            v-if="projects.length === 0"
            class="rounded-xl border border-dashed p-12 text-center"
        >
            <p class="text-muted-foreground text-sm">
                No projects yet. Add your first one and it will show up on the
                landing page.
            </p>
        </div>

        <div v-else class="grid gap-4 md:grid-cols-2">
            <article
                v-for="project in projects"
                :key="project.id"
                class="bg-card flex flex-col rounded-xl border p-5"
            >
                <div
                    v-if="project.cover_url"
                    class="bg-muted mb-4 aspect-video overflow-hidden rounded-lg border"
                >
                    <img
                        :src="project.cover_url"
                        :alt="`${project.name} cover`"
                        class="h-full w-full object-cover object-top"
                    />
                </div>

                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p
                            class="text-muted-foreground text-xs tracking-widest uppercase"
                        >
                            {{ project.type }}
                        </p>
                        <h3 class="mt-1 truncate text-base font-semibold">
                            {{ project.name }}
                        </h3>
                    </div>
                    <span
                        :class="[
                            'shrink-0 rounded-full px-2 py-0.5 text-xs font-medium',
                            project.is_published
                                ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                : 'bg-muted text-muted-foreground',
                        ]"
                    >
                        {{ project.is_published ? 'Live' : 'Hidden' }}
                    </span>
                </div>

                <p class="text-muted-foreground mt-3 line-clamp-3 text-sm">
                    {{ project.summary }}
                </p>

                <a
                    :href="project.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-muted-foreground mt-3 truncate text-xs underline underline-offset-4"
                >
                    {{ project.domain }}
                </a>

                <div class="mt-5 flex items-center gap-2">
                    <Button
                        size="sm"
                        variant="outline"
                        @click="openEdit(project)"
                    >
                        <Pencil class="h-3.5 w-3.5" />
                        Edit
                    </Button>

                    <Form
                        v-bind="ProjectController.destroy.form(project.id)"
                        :options="{ preserveScroll: true }"
                        @submit="confirmDelete($event, project.name)"
                    >
                        <Button size="sm" variant="ghost" type="submit">
                            <Trash2 class="h-3.5 w-3.5" />
                            Delete
                        </Button>
                    </Form>

                    <span class="text-muted-foreground ml-auto text-xs"
                        >#{{ project.position }}</span
                    >
                </div>
            </article>
        </div>
    </div>

    <Dialog v-model:open="dialogOpen">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>{{
                    editing ? 'Edit project' : 'Add project'
                }}</DialogTitle>
                <DialogDescription>
                    Shown in the Work section of the landing page.
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="
                    editing
                        ? ProjectController.update.form(editing.id)
                        : ProjectController.store.form()
                "
                :options="{ preserveScroll: true }"
                class="space-y-4"
                v-slot="{ errors, processing }"
                @success="dialogOpen = false"
            >
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        name="name"
                        required
                        :default-value="editing?.name"
                        placeholder="Honda Cars Philippines"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="type">Type</Label>
                    <Input
                        id="type"
                        name="type"
                        required
                        :default-value="editing?.type"
                        placeholder="Business Website & Booking System"
                    />
                    <InputError :message="errors.type" />
                </div>

                <div class="grid gap-2">
                    <Label for="url">URL</Label>
                    <Input
                        id="url"
                        name="url"
                        type="url"
                        required
                        :default-value="editing?.url"
                        placeholder="https://example.com"
                    />
                    <InputError :message="errors.url" />
                </div>

                <div class="grid gap-2">
                    <Label for="summary">Summary</Label>
                    <textarea
                        id="summary"
                        name="summary"
                        rows="3"
                        required
                        :value="editing?.summary"
                        placeholder="What you built for them, in one or two sentences."
                        class="border-input bg-background focus-visible:ring-ring w-full resize-y rounded-md border px-3 py-2 text-sm focus-visible:ring-2 focus-visible:outline-none"
                    ></textarea>
                    <InputError :message="errors.summary" />
                </div>

                <div class="grid gap-2">
                    <Label for="cover">Cover image</Label>
                    <div class="flex items-center gap-4">
                        <div
                            class="bg-muted h-16 w-28 shrink-0 overflow-hidden rounded-md border"
                        >
                            <img
                                v-if="editing?.cover_url"
                                :src="editing.cover_url"
                                alt=""
                                class="h-full w-full object-cover object-top"
                            />
                            <div
                                v-else
                                class="text-muted-foreground flex h-full w-full items-center justify-center text-xs"
                            >
                                None
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <input
                                id="cover"
                                name="cover"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                class="text-muted-foreground file:bg-muted file:text-foreground w-full text-sm file:mr-3 file:rounded-md file:border-0 file:px-3 file:py-1.5 file:text-sm"
                            />
                            <p class="text-muted-foreground mt-1 text-xs">
                                JPG, PNG, or WebP up to 3 MB. A 16:9 screenshot
                                works best.
                            </p>
                            <label
                                v-if="editing?.cover_url"
                                class="mt-2 flex items-center gap-2 text-xs"
                            >
                                <input
                                    type="checkbox"
                                    name="remove_cover"
                                    value="1"
                                    class="border-input size-3.5 rounded border"
                                />
                                Remove current cover
                            </label>
                        </div>
                    </div>
                    <InputError :message="errors.cover" />
                </div>

                <div class="grid grid-cols-2 items-end gap-4">
                    <div class="grid gap-2">
                        <Label for="position">Order</Label>
                        <Input
                            id="position"
                            name="position"
                            type="number"
                            min="0"
                            :default-value="editing?.position"
                            placeholder="1"
                        />
                        <InputError :message="errors.position" />
                    </div>

                    <label class="flex items-center gap-2 pb-2 text-sm">
                        <input
                            type="checkbox"
                            name="is_published"
                            value="1"
                            :checked="editing ? editing.is_published : true"
                            class="border-input size-4 rounded border"
                        />
                        Show on landing page
                    </label>
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="dialogOpen = false"
                        >Cancel</Button
                    >
                    <Button type="submit" :disabled="processing">
                        {{ editing ? 'Save changes' : 'Add project' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
