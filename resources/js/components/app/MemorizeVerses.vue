<script setup lang="ts">
import RecitationEngine from '@/components/app/RecitationEngine.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { Verse as VerseType } from '@/types';
import { computed, ref, watch } from 'vue';

export interface MemorizeVerse {
    id: number;
    verse_number: number;
    text: string;
}

const props = defineProps<{
    verses: MemorizeVerse[];
    hideLevel: number;
}>();

const emit = defineEmits<{
    (e: 'update:hideLevel', level: number): void;
}>();

const levels = [0, 25, 50, 75];

// Words the user has tapped to reveal, keyed by "verseId:wordIndex".
const revealed = ref<Set<string>>(new Set());

// Word under the pointer; hovering shows it without changing the saved reveals.
const hovered = ref<string | null>(null);

// Changing the level clears the reveals.
watch(
    () => props.hideLevel,
    () => {
        revealed.value = new Set();
    },
);

const setLevel = (level: number) => emit('update:hideLevel', level);

// Deterministic pseudo-random check so the same level always hides the same words.
const isHidden = (verse: MemorizeVerse, wordIndex: number): boolean => {
    if (props.hideLevel === 0) return false;
    const key = `${verse.id}:${wordIndex}`;
    if (revealed.value.has(key) || hovered.value === key) return false;
    const score = (wordIndex * 37 + verse.verse_number * 11) % 100;
    return score < props.hideLevel;
};

const toggleReveal = (verse: MemorizeVerse, wordIndex: number) => {
    const key = `${verse.id}:${wordIndex}`;
    const next = new Set(revealed.value);
    if (next.has(key)) next.delete(key);
    else next.add(key);
    revealed.value = next;
};

// Presentation only: the stored verse text is never modified.
const wordsFor = (verse: MemorizeVerse) => verse.text.split(/\s+/).filter((w) => w.length > 0);

// Keep punctuation and length; only letters and digits become placeholders.
const maskWord = (word: string): string => word.replace(/[\p{L}\p{N}]/gu, '_');

const hasVerses = computed(() => props.verses.length > 0);
const verseNumbers = computed(() => props.verses.map((v) => v.verse_number));

// Recitation: the user picks a sub-range of the displayed verses to recite.
const reciting = ref<boolean>(false);
// Changing the key remounts the recitation, clearing its previous result.
const reciteKey = ref<number>(0);
// Repetir appears only after a first attempt has been scored.
const hasAttempt = ref<boolean>(false);
const repeatRecitation = () => {
    reciteKey.value += 1;
    hasAttempt.value = false;
};
const firstVerse = computed(() => (props.verses.length ? props.verses[0].verse_number : 1));
const lastVerse = computed(() => (props.verses.length ? props.verses[props.verses.length - 1].verse_number : 1));
const reciteFrom = ref<number>(1);
const reciteTo = ref<number>(1);

// Reset the selection when the displayed verses change (another chapter or passage).
watch(
    () => props.verses,
    () => {
        reciteFrom.value = firstVerse.value;
        reciteTo.value = lastVerse.value;
    },
    { immediate: true },
);

const recitableVerses = computed(() =>
    props.verses.filter((v) => v.verse_number >= reciteFrom.value && v.verse_number <= reciteTo.value),
);

const canRecite = computed(() => recitableVerses.value.length > 0 && reciteFrom.value <= reciteTo.value);

const passageForRecitation = computed(
    () =>
        ({
            id: 0,
            text: recitableVerses.value.map((v) => v.text).join(' '),
            chapter: 0,
            verse_number: 0,
            book: { id: 0, title: '' },
        }) as VerseType,
);
</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex flex-wrap justify-center gap-2">
            <Button
                v-for="level in levels"
                :key="level"
                :variant="hideLevel === level ? 'default' : 'outline'"
                size="sm"
                @click="setLevel(level)"
            >
                {{ level === 0 ? 'Todo visible' : `${level}% oculto` }}
            </Button>
        </div>

        <div v-if="hasVerses" class="flex justify-center">
            <Button :variant="reciting ? 'secondary' : 'default'" :disabled="!reciting && !canRecite" @click="reciting = !reciting">
                {{ reciting ? 'Volver al texto' : 'Recitar' }}
            </Button>
        </div>

        <Card v-if="reciting && canRecite">
            <CardHeader>
                <CardTitle class="text-base">Recitación</CardTitle>
            </CardHeader>
            <CardContent>
                <RecitationEngine
                    :key="reciteKey"
                    :verse="passageForRecitation"
                    :verses="recitableVerses"
                    @recitation-complete="hasAttempt = true"
                />
                <div v-if="hasAttempt" class="mt-2 flex justify-center">
                    <Button variant="outline" @click="repeatRecitation">Repetir</Button>
                </div>
                <div class="mt-4 flex flex-wrap items-end justify-center gap-3">
                    <label class="flex flex-col text-sm">
                        Desde versículo
                        <select v-model.number="reciteFrom" class="rounded-md border bg-background p-2">
                            <option v-for="n in verseNumbers" :key="n" :value="n">{{ n }}</option>
                        </select>
                    </label>
                    <label class="flex flex-col text-sm">
                        Hasta versículo
                        <select v-model.number="reciteTo" class="rounded-md border bg-background p-2">
                            <option v-for="n in verseNumbers" :key="n" :value="n">{{ n }}</option>
                        </select>
                    </label>
                </div>
            </CardContent>
        </Card>

        <Card v-if="!hasVerses">
            <CardContent class="py-8 text-center text-muted-foreground">No hay versículos para este rango todavía.</CardContent>
        </Card>

        <Card v-for="verse in verses" v-show="!reciting" :key="verse.id">
            <CardHeader>
                <CardTitle class="text-base">Versículo {{ verse.verse_number }}</CardTitle>
            </CardHeader>
            <CardContent>
                <p class="flex flex-wrap gap-x-2 gap-y-1 text-lg leading-relaxed">
                    <template v-for="(word, index) in wordsFor(verse)" :key="index">
                        <button
                            v-if="isHidden(verse, index)"
                            type="button"
                            class="rounded bg-muted px-1 font-mono text-muted-foreground"
                            aria-label="Palabra oculta, toca para ver"
                            @click="toggleReveal(verse, index)"
                            @mouseenter="hovered = `${verse.id}:${index}`"
                            @mouseleave="hovered = null"
                        >
                            {{ maskWord(word) }}
                        </button>
                        <button
                            v-else-if="hideLevel > 0 && (revealed.has(`${verse.id}:${index}`) || hovered === `${verse.id}:${index}`)"
                            type="button"
                            class="rounded underline decoration-dotted"
                            @click="toggleReveal(verse, index)"
                            @mouseenter="hovered = `${verse.id}:${index}`"
                            @mouseleave="hovered = null"
                        >
                            {{ word }}
                        </button>
                        <span v-else>{{ word }}</span>
                    </template>
                </p>
            </CardContent>
        </Card>
    </div>
</template>
