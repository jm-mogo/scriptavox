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

const tryAgain = () => {
    showResult.value = false;
};
</script>

<template>
    <!-- Browser Not Supported View -->
    <div v-if="!isSpeechRecognitionSupported" class="w-full">
        <Alert variant="destructive">
            <AlertTitle class="font-bold">Navegador no compatible</AlertTitle>
            <AlertDescription>
                La función de reconocimiento de voz no es compatible con tu navegador actual. Por favor, intenta usar Google Chrome o Microsoft Edge
                para una experiencia completa.
            </AlertDescription>
        </Alert>
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
        <div class="w-full rounded-md bg-muted p-4 text-lg leading-relaxed">
            <p class="mb-2 text-sm font-semibold text-muted-foreground">Comparación visual:</p>
            <span
                v-for="(part, index) in comparisonResult"
                :key="index"
                :class="{
                    'text-green-600': !part.added && !part.removed,
                    'bg-red-100 text-red-600 line-through': part.removed,
                    'rounded bg-green-100 px-1 text-green-800': part.added,
                }"
            >
                {{ part.value }}
            </span>
        </div>
        <!-- The "Recite Again" button has been moved to the parent component -->
    </div>
</template>
