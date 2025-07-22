<script setup lang="ts">
import RecitationEngine from '@/components/app/RecitationEngine.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, UserVerseProgress as ProgressType } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

// Shadcn Components
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';

// --- Props ---
const props = defineProps<{
    progress: ProgressType;
    nextIntervals: {
        again: string;
        hard: string;
        good: string;
    };
}>();

const page = usePage();

const form = useForm({
    assessment: '' as 'again' | 'hard' | 'good' | '',
});

const isRecitationComplete = ref(false);
const recitationEngineKey = ref(0);

// --- Breadcrumbs ---
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    {
        title: 'Repaso Diario',
        href: '',
    },
];

// --- Computed Properties ---
const verseCitation = computed(() => {
    if (!props.progress.verse || !props.progress.verse.book) return 'Cargando...';
    return `${props.progress.verse.book.title} ${props.progress.verse.chapter}:${props.progress.verse.verse_number}`;
});

// --- Logic ---
const handleRecitationComplete = () => {
    isRecitationComplete.value = true;
};

const submitAssessment = (assessment: 'again' | 'hard' | 'good') => {
    form.assessment = assessment;
    form.patch(route('review.update', { userVerseProgress: props.progress.id }), {
        preserveScroll: false,
    });
};

// Watch for changes to the `progress` prop (when a new verse is loaded).
watch(
    () => props.progress.id,
    () => {
        isRecitationComplete.value = false;
        recitationEngineKey.value++;
    },
    { immediate: true },
);
</script>

<template>
    <Head :title="`Repasar: ${verseCitation}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto max-w-4xl p-4 sm:p-6 lg:p-8">
            <Alert v-if="page.props.flash.review_success" class="mb-4">
                <AlertTitle>¡Progreso Guardado!</AlertTitle>
                <AlertDescription>{{ page.props.flash.review_success }}</AlertDescription>
            </Alert>

            <Card class="mx-auto w-full">
                <CardHeader class="text-center">
                    <CardTitle>Repaso Diario</CardTitle>
                    <CardDescription v-if="!isRecitationComplete">
                        Recita el siguiente versículo de memoria:
                        <span class="mt-1 block font-bold text-foreground">{{ verseCitation }}</span>
                    </CardDescription>
                    <CardDescription v-else> El versículo correcto es: </CardDescription>
                </CardHeader>
                <CardContent>
                    <blockquote v-if="isRecitationComplete" class="prose mb-6 max-w-none border-l-4 pl-4 italic">
                        {{ progress.verse.text }}
                    </blockquote>

                    <RecitationEngine :key="recitationEngineKey" :verse="progress.verse" @recitation-complete="handleRecitationComplete" />

                    <!-- ========================================================================= -->
                    <!-- NEW "PRACTICE" LINK IS HERE -->
                    <!-- ========================================================================= -->
                    <div v-if="!isRecitationComplete" class="mt-4 text-center">
                        <Link
                            :href="route('practice.show', { verse: progress.verse.id })"
                            class="text-sm text-muted-foreground underline-offset-4 hover:underline"
                        >
                            ¿No lo recuerdas? Ir a la página de práctica.
                        </Link>
                    </div>
                    <!-- ========================================================================= -->
                </CardContent>

                <CardFooter v-if="isRecitationComplete" class="flex flex-col gap-4 pt-6">
                    <div class="text-center">
                        <p class="font-semibold">¿Cómo te sentiste al recordarlo?</p>
                        <p class="text-sm text-muted-foreground">Tu respuesta ajustará la próxima fecha de repaso.</p>
                    </div>
                    <TooltipProvider :delay-duration="100">
                        <div class="grid w-full grid-cols-3 gap-2">
                            <Tooltip>
                                <TooltipTrigger as-child>
                                    <Button
                                        @click="submitAssessment('again')"
                                        variant="destructive"
                                        :disabled="form.processing"
                                        class="flex h-auto flex-col py-2"
                                    >
                                        <span>Otra vez</span>
                                        <span class="text-xs font-normal opacity-75">{{ nextIntervals.again }}</span>
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>No lo recordé. Repasar de nuevo en unos minutos.</p>
                                </TooltipContent>
                            </Tooltip>

                            <Tooltip>
                                <TooltipTrigger as-child>
                                    <Button
                                        @click="submitAssessment('hard')"
                                        variant="outline"
                                        :disabled="form.processing"
                                        class="flex h-auto flex-col py-2"
                                    >
                                        <span>Difícil</span>
                                        <span class="text-xs font-normal opacity-75">{{ nextIntervals.hard }}</span>
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>Lo recordé, pero con dificultad.</p>
                                </TooltipContent>
                            </Tooltip>

                            <Tooltip>
                                <TooltipTrigger as-child>
                                    <Button @click="submitAssessment('good')" :disabled="form.processing" class="flex h-auto flex-col py-2">
                                        <span>Bien</span>
                                        <span class="text-xs font-normal opacity-75">{{ nextIntervals.good }}</span>
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>Lo recordé fácilmente.</p>
                                </TooltipContent>
                            </Tooltip>
                        </div>
                    </TooltipProvider>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
