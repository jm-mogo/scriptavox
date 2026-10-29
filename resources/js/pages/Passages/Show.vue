<script setup lang="ts">
import MemorizeVerses, { type MemorizeVerse } from '@/components/app/MemorizeVerses.vue';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Passage {
    id: number;
    book: string;
    chapter: number;
    verse_from: number;
    verse_to: number;
    hide_level: number;
}

const props = defineProps<{
    passage: Passage;
    verses: MemorizeVerse[];
}>();

const hideLevel = ref<number>(props.passage.hide_level);
const citation = computed(() => {
    const range =
        props.passage.verse_from === props.passage.verse_to
            ? `${props.passage.verse_from}`
            : `${props.passage.verse_from}-${props.passage.verse_to}`;
    return `${props.passage.book} ${props.passage.chapter}:${range}`;
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Mis pasajes', href: '/passages' },
    { title: citation.value, href: '' },
];

// Save progress each time the user changes the hide level, so they can resume later.
const onLevelChange = (level: number) => {
    hideLevel.value = level;
    router.patch(`/passages/${props.passage.id}`, { hide_level: level }, { preserveScroll: true, preserveState: true });
};
</script>

<template>
    <Head :title="citation" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-4 p-4">
            <Card>
                <CardHeader class="text-center">
                    <CardTitle>{{ citation }}</CardTitle>
                    <CardDescription>Oculta palabras poco a poco y repite de memoria. Tu avance se guarda.</CardDescription>
                </CardHeader>
            </Card>
            <MemorizeVerses :verses="verses" :hide-level="hideLevel" @update:hide-level="onLevelChange" />
        </div>
    </AppLayout>
</template>
