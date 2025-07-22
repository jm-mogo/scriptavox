<script setup lang="ts">
import RecitationEngine from '@/components/app/RecitationEngine.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Verse as VerseType } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

// Shadcn Components
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';

// --- Type Definition for Word Bank items ---
interface WordBankItem {
    id: number;
    word: string;
}

const props = defineProps<{
    verse: VerseType;
}>();

// --- State Management ---
const currentStage = ref<'study' | 'word-bank' | 'recite'>('study');
const form = useForm({ verse_id: props.verse.id });

// Word Bank State
const availableWords = ref<WordBankItem[]>([]);
const selectedWords = ref<WordBankItem[]>([]);
const isWordBankCorrect = ref<boolean | null>(null);

// Result State
const isPracticeComplete = ref(false);
const finalAccuracy = ref(0);

// --- Breadcrumbs ---
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Mis Versículos', href: '/my-verses' },
    {
        title: 'Practicar',
        href: '',
    },
];

// --- Computed Properties ---
const verseCitation = computed(() => {
    if (!props.verse || !props.verse.book) return 'Cargando...';
    return `${props.verse.book.title} ${props.verse.chapter}:${props.verse.verse_number}`;
});

const firstLetters = computed(() => {
    if (!props.verse.text) return '';
    const words = props.verse.text.split(/\s+/);
    return (
        words
            .map((word) => {
                if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ]/.test(word)) return word;
                return word.charAt(0);
            })
            .join(' ') + '.'
    );
});

const constructedSentenceText = computed(() => {
    return selectedWords.value.map((item) => item.word).join(' ');
});

// --- Logic ---
const normalizeText = (text: string | null | undefined): string => {
    if (!text) return '';
    return text
        .toLowerCase()
        .replace(/á/g, 'a')
        .replace(/é/g, 'e')
        .replace(/í/g, 'i')
        .replace(/ó/g, 'o')
        .replace(/ú/g, 'u')
        .replace(/ü/g, 'u')
        .replace(/[.,¡!¿;:"“”]/g, '')
        .trim();
};

const setupWordBank = () => {
    if (!props.verse.text) return;
    const words = props.verse.text
        .replace(/[.,¡!¿;:"“”]/g, '')
        .trim()
        .split(/\s+/);
    const initialWords = words.map((word, index) => ({ id: index, word }));
    availableWords.value = initialWords.sort(() => Math.random() - 0.5);
    selectedWords.value = [];
};

const selectWord = (wordItem: WordBankItem) => {
    isWordBankCorrect.value = null;
    selectedWords.value.push(wordItem);
    availableWords.value = availableWords.value.filter((item) => item.id !== wordItem.id);
};

const deselectWord = (wordItem: WordBankItem) => {
    availableWords.value.push(wordItem);
    selectedWords.value = selectedWords.value.filter((item) => item.id !== wordItem.id);
    availableWords.value.sort(() => Math.random() - 0.5);
};

const checkWordBank = () => {
    const original = props.verse.text.replace(/[.,¡!¿;:"“”]/g, '').trim();
    const constructed = selectedWords.value.map((item) => item.word).join(' ');

    if (normalizeText(original) === normalizeText(constructed)) {
        isWordBankCorrect.value = true;
        setTimeout(() => {
            currentStage.value = 'recite';
        }, 1500);
    } else {
        isWordBankCorrect.value = false;
    }
};

watch(currentStage, (newStage, oldStage) => {
    if (newStage === 'word-bank') {
        setupWordBank();
    }
    if (oldStage === 'recite' && newStage !== 'recite') {
        isPracticeComplete.value = false;
        finalAccuracy.value = 0;
    }
});

const handleRecitationComplete = (payload: { accuracy: number; transcribedText: string }) => {
    isPracticeComplete.value = true;
    finalAccuracy.value = payload.accuracy;
    if (payload.accuracy >= 90) {
        saveProgress();
    }
};

const saveProgress = () => {
    form.post(route('my-verses.store'), {
        preserveScroll: true,
        onSuccess: () => console.log('Progress saved!'),
        onError: (errors) => console.error('Failed to save progress:', errors),
    });
};

const resetPractice = () => {
    currentStage.value = 'study';
};
</script>

<template>
    <Head title="Practicar Versículo" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <Card class="mx-auto w-full max-w-2xl">
            <!-- Stage 1: Study -->
            <template v-if="currentStage === 'study'">
                <CardHeader class="text-center">
                    <CardTitle>Paso 1: Estudiar el Versículo</CardTitle>
                    <CardDescription>{{ verseCitation }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <blockquote class="prose w-full border-l-4 pl-4 text-muted-foreground italic">
                        {{ verse.text }}
                    </blockquote>
                </CardContent>
                <CardFooter>
                    <Button @click="currentStage = 'word-bank'" class="w-full"> ¡Entendido! Siguiente paso </Button>
                </CardFooter>
            </template>

            <!-- Stage 2: Word Bank -->
            <template v-if="currentStage === 'word-bank'">
                <CardHeader class="text-center">
                    <CardTitle>Paso 2: Construir el Versículo</CardTitle>
                    <CardDescription>Toca las palabras en el orden correcto.</CardDescription>
                </CardHeader>
                <CardContent class="flex flex-col gap-4">
                    <!-- The area where the sentence is constructed -->
                    <div class="flex min-h-[100px] w-full flex-wrap content-start items-start gap-2 rounded-md border bg-muted p-4">
                        <!-- Words selected by the user (clickable to deselect) -->
                        <Button v-for="item in selectedWords" :key="item.id" variant="secondary" class="h-auto py-1" @click="deselectWord(item)">
                            {{ item.word }}
                        </Button>
                    </div>

                    <!-- The bank of available word buttons -->
                    <div class="flex flex-wrap justify-center gap-2">
                        <Button v-for="item in availableWords" :key="item.id" variant="outline" class="h-auto py-1" @click="selectWord(item)">
                            {{ item.word }}
                        </Button>
                    </div>

                    <!-- Action buttons for the word bank -->
                    <div class="mt-4 flex w-full justify-end">
                        <Button @click="checkWordBank" :disabled="availableWords.length > 0"> Comprobar </Button>
                    </div>

                    <!-- Feedback Alerts -->
                    <Alert v-if="isWordBankCorrect === true" class="mt-2" variant="default">
                        <AlertTitle>¡Correcto! Preparándote para recitar...</AlertTitle>
                    </Alert>
                    <Alert v-if="isWordBankCorrect === false" class="mt-2" variant="destructive">
                        <AlertTitle>No es correcto todavía.</AlertTitle>
                        <AlertDescription>Puedes tocar las palabras de arriba para corregir.</AlertDescription>
                    </Alert>
                </CardContent>
            </template>

            <!-- Stage 3: Recite (and its results) -->
            <template v-if="currentStage === 'recite'">
                <CardHeader class="text-center">
                    <CardTitle>Paso 3: Práctica de Recitación</CardTitle>
                    <CardDescription v-if="!isPracticeComplete"> Usa las primeras letras como guía y habla claro. </CardDescription>
                    <CardDescription v-else-if="finalAccuracy >= 90"> ¡Felicidades! Has aprendido este versículo. </CardDescription>
                    <CardDescription v-else> ¡Casi lo tienes! Sigue practicando. </CardDescription>
                </CardHeader>
                <CardContent>
                    <RecitationEngine :verse="verse" :prompt="firstLetters" @recitation-complete="handleRecitationComplete" />
                </CardContent>
                <CardFooter v-if="isPracticeComplete" class="flex flex-col gap-2 pt-6">
                    <Button v-if="finalAccuracy >= 90" as-child class="w-full">
                        <Link :href="route('my-verses.index')"> ¡Genial! Ir a mi lista de versículos </Link>
                    </Button>
                    <Button @click="resetPractice" class="w-full" variant="secondary"> Practicar de Nuevo </Button>
                </CardFooter>
            </template>
        </Card>
    </AppLayout>
</template>
