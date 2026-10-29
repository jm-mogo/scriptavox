<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface PassageRow {
    id: number;
    book: string;
    chapter: number;
    verse_from: number;
    verse_to: number;
    hide_level: number;
}

interface BookOption {
    id: number;
    title: string;
    chapter_count: number;
    verses: Record<number, number>;
}

const props = defineProps<{
    passages: PassageRow[];
    books: BookOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inicio', href: '/dashboard' },
    { title: 'Mis pasajes', href: '' },
];

const range = (p: PassageRow) => (p.verse_from === p.verse_to ? `${p.verse_from}` : `${p.verse_from}-${p.verse_to}`);

// --- Add a new passage ---
const bookId = ref<number | null>(props.books[0]?.id ?? null);
const chapter = ref<number>(1);

const selectedBook = computed(() => props.books.find((b) => b.id === bookId.value) ?? null);
const chapterOptions = computed(() => Array.from({ length: selectedBook.value?.chapter_count ?? 0 }, (_, i) => i + 1));
const verseCount = computed(() => selectedBook.value?.verses[chapter.value] ?? 0);
const verseOptions = computed(() => Array.from({ length: verseCount.value }, (_, i) => i + 1));

// Keep the chapter inside the selected book's range.
watch(bookId, () => (chapter.value = 1));

const form = useForm({
    book_id: bookId.value,
    chapter: chapter.value,
    verse_from: 1,
    verse_to: 1,
});

watch([bookId, chapter], ([book, ch]) => {
    form.book_id = book;
    form.chapter = ch;
    form.verse_from = 1;
    form.verse_to = 1;
});

const canSave = computed(() => !!bookId.value && verseCount.value > 0 && form.verse_from <= form.verse_to);

const savePassage = () => {
    form.post('/passages', { preserveScroll: true });
};
</script>

<template>
    <Head title="Mis pasajes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <CardTitle>Añadir pasaje</CardTitle>
                    <CardDescription>Elige un libro, capítulo y rango de versículos para memorizar.</CardDescription>
                </CardHeader>
                <CardContent class="flex flex-wrap items-end gap-3">
                    <label class="flex min-w-40 flex-1 flex-col text-sm">
                        Libro
                        <select v-model.number="bookId" class="rounded-md border bg-background p-2">
                            <option v-for="book in books" :key="book.id" :value="book.id">{{ book.title }}</option>
                        </select>
                    </label>
                    <label class="flex flex-col text-sm">
                        Capítulo
                        <select v-model.number="chapter" class="rounded-md border bg-background p-2">
                            <option v-for="c in chapterOptions" :key="c" :value="c">{{ c }}</option>
                        </select>
                    </label>
                    <label class="flex flex-col text-sm">
                        Desde versículo
                        <select v-model.number="form.verse_from" class="rounded-md border bg-background p-2">
                            <option v-for="n in verseOptions" :key="n" :value="n">{{ n }}</option>
                        </select>
                    </label>
                    <label class="flex flex-col text-sm">
                        Hasta versículo
                        <select v-model.number="form.verse_to" class="rounded-md border bg-background p-2">
                            <option v-for="n in verseOptions" :key="n" :value="n">{{ n }}</option>
                        </select>
                    </label>
                    <Button :disabled="!canSave || form.processing" @click="savePassage">Guardar pasaje</Button>
                    <p v-if="form.errors.verse_to" class="w-full text-sm text-destructive">{{ form.errors.verse_to }}</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Mis pasajes</CardTitle>
                </CardHeader>
                <CardContent class="flex flex-col gap-2">
                    <p v-if="passages.length === 0" class="text-muted-foreground">Todavía no tienes pasajes guardados.</p>
                    <Button v-for="p in passages" :key="p.id" as-child variant="outline" class="justify-between">
                        <Link :href="`/passages/${p.id}`">
                            <span>{{ p.book }} {{ p.chapter }}:{{ range(p) }}</span>
                            <span class="text-muted-foreground">{{ p.hide_level }}% oculto</span>
                        </Link>
                    </Button>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
