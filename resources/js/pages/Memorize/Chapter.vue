<script setup lang="ts">
import MemorizeVerses, { type MemorizeVerse } from '@/components/app/MemorizeVerses.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    book: { id: number; title: string };
    chapter: number;
    verses: MemorizeVerse[];
}>();

const hideLevel = ref<number>(0);
const onLevelChange = (level: number) => (hideLevel.value = level);

const first = computed(() => (props.verses.length ? props.verses[0].verse_number : 1));
const last = computed(() => (props.verses.length ? props.verses[props.verses.length - 1].verse_number : 1));

// Save a verse range to the user's account so they can resume it later.
const form = useForm({
    book_id: props.book.id,
    chapter: props.chapter,
    verse_from: first.value,
    verse_to: last.value,
});

const savePassage = () => {
    form.post('/passages', { preserveScroll: true });
};

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Dashboard', href: '/dashboard' },
    { title: `${props.book.title} ${props.chapter}`, href: '' },
]);
</script>

<template>
    <Head :title="`${book.title} ${chapter}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-4 p-4">
            <Card>
                <CardHeader class="text-center">
                    <CardTitle>{{ book.title }} {{ chapter }}</CardTitle>
                    <CardDescription>Oculta palabras poco a poco y repite de memoria. Toca una palabra oculta para verla.</CardDescription>
                </CardHeader>
                <CardContent class="flex flex-col gap-3">
                    <div class="flex flex-wrap items-end justify-center gap-3">
                        <label class="flex flex-col text-sm">
                            Desde versículo
                            <Input v-model.number="form.verse_from" type="number" :min="first" :max="last" class="w-24" />
                        </label>
                        <label class="flex flex-col text-sm">
                            Hasta versículo
                            <Input v-model.number="form.verse_to" type="number" :min="first" :max="last" class="w-24" />
                        </label>
                        <Button :disabled="form.processing || form.verse_from > form.verse_to" @click="savePassage">Guardar pasaje</Button>
                    </div>
                    <p v-if="form.errors.verse_to" class="text-center text-sm text-destructive">{{ form.errors.verse_to }}</p>
                </CardContent>
            </Card>

            <MemorizeVerses :verses="verses" :hide-level="hideLevel" @update:hide-level="onLevelChange" />
        </div>
    </AppLayout>
</template>
