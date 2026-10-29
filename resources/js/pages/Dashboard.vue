<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface BookOption {
    id: number;
    title: string;
    chapter_count: number;
}

interface PassageRow {
    id: number;
    book: string;
    chapter: number;
    verse_from: number;
    verse_to: number;
    hide_level: number;
}

const props = defineProps<{
    books: BookOption[];
    passages: PassageRow[];
    review_count: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Inicio', href: '/dashboard' }];

const bookId = ref<number | null>(props.books[0]?.id ?? null);
const chapter = ref<number>(1);

const selectedBook = computed(() => props.books.find((b) => b.id === bookId.value) ?? null);
const chapterOptions = computed(() => Array.from({ length: selectedBook.value?.chapter_count ?? 0 }, (_, i) => i + 1));

// Keep the chapter inside the selected book's range.
watch(bookId, () => (chapter.value = 1));

const memorizeHref = computed(() => (bookId.value ? `/memorize/${bookId.value}/${chapter.value}` : '#'));

const range = (p: PassageRow) => (p.verse_from === p.verse_to ? `${p.verse_from}` : `${p.verse_from}-${p.verse_to}`);
</script>

<template>
    <Head title="Inicio" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <CardTitle>Elige qué memorizar</CardTitle>
                    <CardDescription>Selecciona un libro y un capítulo, y luego guarda el rango de versículos que quieras.</CardDescription>
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
                    <Button as-child :disabled="!bookId">
                        <Link :href="memorizeHref">Memorizar</Link>
                    </Button>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Mis pasajes</CardTitle>
                    <CardDescription>Continúa donde lo dejaste.</CardDescription>
                </CardHeader>
                <CardContent class="flex flex-col gap-2">
                    <p v-if="passages.length === 0" class="text-muted-foreground">Todavía no tienes pasajes guardados.</p>
                    <Button v-for="p in passages" :key="p.id" as-child variant="outline" class="justify-between">
                        <Link :href="`/passages/${p.id}`">
                            <span>{{ p.book }} {{ p.chapter }}:{{ range(p) }}</span>
                            <span class="text-muted-foreground">{{ p.hide_level }}% oculto</span>
                        </Link>
                    </Button>
                    <Link href="/passages" class="text-sm underline">Ver todos mis pasajes</Link>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="flex items-center justify-between py-4">
                    <span>Para repasar hoy: {{ review_count }}</span>
                    <Button as-child variant="secondary">
                        <Link href="/review/start">Repasar</Link>
                    </Button>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
