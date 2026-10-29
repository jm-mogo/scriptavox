<script setup lang="ts">
import * as Diff from 'diff';
import levenshtein from 'fast-levenshtein';
import { computed, onMounted, ref } from 'vue';

// --- Shadcn/Vue Component Imports ---
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';

// --- Type Definitions ---
import type { Verse as VerseType } from '@/types';
type DiffResult = Diff.Change[];
declare global {
    interface Window {
        SpeechRecognition: any;
        webkitSpeechRecognition: any;
    }
}

// --- Props & Emits ---
const props = defineProps<{
    verse: VerseType;
    prompt?: string; // Optional prompt, like first-letters
    // Optional per-verse breakdown for a passage; when present, results are shown per verse.
    verses?: { verse_number: number; text: string }[];
}>();

const emit = defineEmits<{
    (e: 'recitation-complete', payload: { accuracy: number; transcribedText: string }): void;
}>();

// --- State Management ---
const transcribedText = ref<string>('');
const isListening = ref<boolean>(false);
const comparisonResult = ref<DiffResult>([]);
const showResult = ref<boolean>(false);
const accuracy = ref<number>(0);
const errorMessage = ref<string>('');
const isSpeechRecognitionSupported = ref<boolean>(true);

// --- Computed Properties ---
const accuracyVariant = computed(() => {
    if (accuracy.value >= 90) return 'default';
    if (accuracy.value >= 70) return 'default';
    return 'destructive';
});

// --- Web Speech API Logic ---
let recognition: any;

onMounted(() => {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
        isSpeechRecognitionSupported.value = false;
        return;
    }

    recognition = new SpeechRecognition();
    recognition.continuous = true;
    recognition.interimResults = true;
    recognition.lang = 'es-ES';

    recognition.onresult = (event: any) => {
        let finalTranscript = '';
        for (let i = 0; i < event.results.length; ++i) {
            if (event.results[i].isFinal) {
                finalTranscript += event.results[i][0].transcript;
            }
        }

        const currentSpokenText = finalTranscript.trim();
        transcribedText.value = currentSpokenText;

        if (isListening.value && props.verse.text) {
            const originalNormalized = normalizeText(props.verse.text);
            const spokenNormalized = normalizeText(currentSpokenText);

            const isLengthSufficient = spokenNormalized.length >= originalNormalized.length * 0.9;
            const originalWords = originalNormalized.split(/\s+/);
            const tailToMatch = originalWords.slice(-3).join(' ');

            if (tailToMatch && spokenNormalized.endsWith(tailToMatch) && isLengthSufficient) {
                stopListening();
            }
        }
    };

    recognition.onend = () => {
        isListening.value = false;
        if (transcribedText.value) {
            compareVerses();
        }
    };

    recognition.onerror = (event: any) => {
        console.error('Speech recognition error:', event.error);
        isListening.value = false;
        switch (event.error) {
            case 'not-allowed':
                errorMessage.value = 'Permiso para el micrófono denegado. Por favor, habilita el acceso en la barra de direcciones de tu navegador.';
                break;
            case 'no-speech':
                errorMessage.value = 'No se detectó ninguna voz. Por favor, intenta hablar más claro y cerca del micrófono.';
                break;
            case 'network':
                errorMessage.value = (navigator as any).brave
                    ? 'Brave Browser está bloqueando el servicio. Por favor, deshabilita los "Shields" (icono de león) para este sitio.'
                    : 'Error de red. No se pudo conectar al servicio de reconocimiento de voz. Revisa tu conexión a internet.';
                break;
            default:
                errorMessage.value = `Ocurrió un error inesperado (${event.error}). Por favor, intenta de nuevo.`;
        }
    };
});

// --- Core Functions ---
const startListening = () => {
    if (!isSpeechRecognitionSupported.value || isListening.value) return;
    transcribedText.value = '';
    comparisonResult.value = [];
    showResult.value = false;
    errorMessage.value = '';
    accuracy.value = 0;
    isListening.value = true;
    recognition.start();
};

const stopListening = () => {
    if (isListening.value) {
        isListening.value = false;
        recognition.stop();
    }
};

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

const compareVerses = () => {
    const originalNormalized = normalizeText(props.verse.text);
    const spokenNormalized = normalizeText(transcribedText.value);

    const distance = levenshtein.get(originalNormalized, spokenNormalized);
    const accuracyDenominator = originalNormalized.length || 1;
    const calculatedAccuracy = Math.max(0, (1 - distance / accuracyDenominator) * 100);
    accuracy.value = Math.round(calculatedAccuracy);

    const diff = Diff.diffWords(originalNormalized, spokenNormalized, { ignoreCase: true });
    comparisonResult.value = diff;
    showResult.value = true;

    emit('recitation-complete', {
        accuracy: accuracy.value,
        transcribedText: transcribedText.value,
    });
};

// --- Per-verse results ---
// Align the spoken words to the original word sequence, then score each verse.
type VerseStatus = 'ok' | 'partial' | 'missed';
interface VerseRow {
    verse_number: number;
    status: VerseStatus;
    percent: number;
}

// Shared alignment: which normalized original words were spoken.
const alignment = computed(() => {
    const sources = props.verses && props.verses.length > 0 ? props.verses : [{ verse_number: 0, text: props.verse.text }];

    const words: { verse: number; word: string }[] = [];
    sources.forEach((v) => {
        normalizeText(v.text)
            .split(/\s+/)
            .filter((w) => w.length > 0)
            .forEach((word) => words.push({ verse: v.verse_number, word }));
    });

    const spoken = normalizeText(transcribedText.value)
        .split(/\s+/)
        .filter((w) => w.length > 0);

    const matched = new Array<boolean>(words.length).fill(false);
    let position = 0;
    for (const part of Diff.diffArrays(
        words.map((w) => w.word),
        spoken,
    )) {
        const length = part.value.length;
        if (part.added) continue; // spoken but not in the verse
        if (part.removed) {
            position += length; // in the verse but not spoken
            continue;
        }
        for (let k = 0; k < length; k++) matched[position + k] = true;
        position += length;
    }

    return { words, matched };
});

// Original tokens with punctuation and capitals, each marked as spoken or missed.
const originalTokens = computed<{ text: string; state: 'match' | 'miss' | 'neutral' }[]>(() => {
    const { matched } = alignment.value;
    const sources = props.verses && props.verses.length > 0 ? props.verses.map((v) => v.text) : [props.verse.text];

    const tokens: { text: string; state: 'match' | 'miss' | 'neutral' }[] = [];
    let position = 0;
    sources.forEach((text) => {
        text.split(/\s+/)
            .filter((raw) => raw.length > 0)
            .forEach((raw) => {
                if (normalizeText(raw).length === 0) {
                    tokens.push({ text: raw, state: 'neutral' }); // pure punctuation
                    return;
                }
                tokens.push({ text: raw, state: matched[position] ? 'match' : 'miss' });
                position++;
            });
    });
    return tokens;
});

// Per-verse tokens (raw text with punctuation) colored by alignment.
type WordToken = { text: string; state: 'match' | 'miss' | 'neutral' };
const tokensByVerse = computed<Record<number, WordToken[]>>(() => {
    const { matched } = alignment.value;
    const sources = props.verses && props.verses.length > 0 ? props.verses : [{ verse_number: 0, text: props.verse.text }];

    const result: Record<number, WordToken[]> = {};
    let position = 0;
    sources.forEach((v) => {
        const tokens: WordToken[] = [];
        v.text
            .split(/\s+/)
            .filter((raw) => raw.length > 0)
            .forEach((raw) => {
                if (normalizeText(raw).length === 0) {
                    tokens.push({ text: raw, state: 'neutral' });
                    return;
                }
                tokens.push({ text: raw, state: matched[position] ? 'match' : 'miss' });
                position++;
            });
        result[v.verse_number] = tokens;
    });
    return result;
});

const verseRows = computed<VerseRow[]>(() => {
    if (!props.verses || props.verses.length === 0) return [];

    const { words, matched } = alignment.value;

    return props.verses.map((v) => {
        const indexes = words.map((w, i) => (w.verse === v.verse_number ? i : -1)).filter((i) => i >= 0);
        const total = indexes.length || 1;
        const ok = indexes.filter((i) => matched[i]).length;
        const ratio = ok / total;
        const status: VerseStatus = ratio === 0 ? 'missed' : ratio >= 0.9 ? 'ok' : 'partial';
        return { verse_number: v.verse_number, status, percent: Math.round(ratio * 100) };
    });
});

// Collapse consecutive fully missed verses into one line: "Versículo N omitido" / "Versículos N-M omitidos".
type ResultLine =
    | { kind: 'verse'; verse_number: number; status: VerseStatus; percent: number; tokens: WordToken[] }
    | { kind: 'missed-range'; from: number; to: number };

const resultLines = computed<ResultLine[]>(() => {
    const lines: ResultLine[] = [];
    for (const row of verseRows.value) {
        const last = lines[lines.length - 1];
        if (row.status === 'missed') {
            if (last && last.kind === 'missed-range' && last.to === row.verse_number - 1) {
                last.to = row.verse_number;
            } else {
                lines.push({ kind: 'missed-range', from: row.verse_number, to: row.verse_number });
            }
        } else {
            lines.push({
                kind: 'verse',
                verse_number: row.verse_number,
                status: row.status,
                percent: row.percent,
                tokens: tokensByVerse.value[row.verse_number] ?? [],
            });
        }
    }
    return lines;
});

const tryAgain = () => {
    showResult.value = false;
};
</script>

<template>
    <!-- Browser Not Supported View -->
    <!-- Typed fallback when voice recognition is unavailable -->
    <div v-if="!isSpeechRecognitionSupported && !showResult" class="flex w-full flex-col gap-4">
        <Alert>
            <AlertTitle class="font-bold">Voz no disponible en este navegador</AlertTitle>
            <AlertDescription>
                El reconocimiento de voz funciona mejor en Google Chrome o Microsoft Edge. Puedes escribir el versículo de memoria.
            </AlertDescription>
        </Alert>
        <textarea
            v-model="transcribedText"
            rows="4"
            class="w-full rounded-md border bg-background p-3 text-base"
            placeholder="Escribe el versículo de memoria..."
        ></textarea>
        <Button @click="compareVerses" :disabled="!transcribedText.trim()" class="w-full">Comprobar</Button>
    </div>

    <!-- Main Engine View (before showing results) -->
    <div v-else-if="!showResult" class="flex w-full flex-col items-center gap-6">
        <div v-if="prompt" class="w-full rounded-md bg-muted p-4 font-mono text-lg text-muted-foreground">
            {{ prompt }}
        </div>

        <Alert v-if="errorMessage" variant="destructive">
            <AlertDescription class="font-semibold">{{ errorMessage }}</AlertDescription>
        </Alert>

        <p v-if="isListening" class="animate-pulse text-lg font-semibold text-destructive">Escuchando...</p>

        <Button
            @click="isListening ? stopListening() : startListening()"
            size="icon"
            class="h-20 w-20 rounded-full text-4xl transition-all duration-300 hover:scale-110"
            :class="isListening ? 'animate-pulse bg-destructive hover:bg-destructive/90' : ''"
        >
            <span v-if="isListening">⏹️</span>
            <span v-else>🎤</span>
        </Button>
    </div>

    <!-- Engine Results View -->
    <div v-if="showResult" class="flex w-full flex-col items-center gap-4">
        <Alert :variant="accuracyVariant">
            <AlertTitle class="text-xl font-bold"> Resultado: {{ accuracy }}% de Precisión </AlertTitle>
            <AlertDescription> <strong>Tú dijiste:</strong> "{{ transcribedText }}" </AlertDescription>
        </Alert>
        <Progress :model-value="accuracy" class="w-full" />
        <div v-if="verses && verses.length > 0" class="flex w-full flex-col gap-2">
            <template v-for="(line, index) in resultLines" :key="index">
                <div v-if="line.kind === 'missed-range' && line.from === line.to" class="rounded-md bg-red-100 p-3 text-red-700">
                    Versículo {{ line.from }} omitido
                </div>
                <div v-else-if="line.kind === 'missed-range'" class="rounded-md bg-red-100 p-3 text-red-700">
                    Versículos {{ line.from }}-{{ line.to }} omitidos
                </div>
                <div v-else class="flex flex-col gap-2 rounded-md p-3" :class="line.status === 'ok' ? 'bg-green-50' : 'bg-amber-50'">
                    <div class="flex items-center justify-between text-sm font-semibold">
                        <span>Versículo {{ line.verse_number }}</span>
                        <span>{{ line.percent }}%</span>
                    </div>
                    <p class="text-lg leading-relaxed">
                        <span
                            v-for="(token, t) in line.tokens"
                            :key="t"
                            class="mr-1 inline-block"
                            :class="{
                                'text-green-700': token.state === 'match',
                                'rounded bg-red-100 px-1 text-red-600 line-through': token.state === 'miss',
                            }"
                        >
                            {{ token.text }}
                        </span>
                    </p>
                </div>
            </template>
        </div>

        <div v-else class="w-full rounded-md bg-muted p-4 text-lg leading-relaxed">
            <p class="mb-2 text-sm font-semibold text-muted-foreground">Comparación visual:</p>
            <span v-for="(token, index) in originalTokens" :key="index" class="mr-1 inline-block" :class="{
                'text-green-600': token.state === 'match',
                'bg-red-100 text-red-600 line-through rounded px-1': token.state === 'miss',
            }">
                {{ token.text }}
            </span>
        </div>
        <!-- The "Recite Again" button has been moved to the parent component -->
    </div>
</template>
