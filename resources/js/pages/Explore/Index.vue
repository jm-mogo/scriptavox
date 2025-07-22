<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type Book as BookType, type BreadcrumbItem, type Verse as VerseType } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, watch } from 'vue';

// Shadcn Components
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

// --- Props & Types ---
interface Props {
    books: BookType[];
}
const props = defineProps<Props>();
interface FlashProps {
    success?: string;
    [key: string]: any;
}
interface PageProps {
    flash: FlashProps;
    name: string;
    quote: { message: string; author: string };
    auth: any;
    ziggy: any;
    sidebarOpen: boolean;
    [key: string]: any;
}
const page = usePage<PageProps>();

// --- State Management ---
const selectedBookId = ref<number | null>(null);
const selectedChapter = ref<number | null>(null);
const selectedVerseId = ref<number | null>(null);

const chapters = ref<number[]>([]);
const verses = ref<VerseType[]>([]);
const selectedVerse = ref<VerseType | null>(null);
const isLoadingChapters = ref(false);
const isLoadingVerses = ref(false);

// Inertia form helper for the POST request
const form = useForm({
    verse_id: null as number | null,
});

// --- Logic ---
watch(selectedBookId, async (newBookId) => {
    // Reset subsequent fields when book changes
    selectedChapter.value = null;
    selectedVerseId.value = null;
    verses.value = [];
    selectedVerse.value = null;
    chapters.value = [];

    if (newBookId) {
        isLoadingChapters.value = true;
        try {
            // This is a placeholder for chapter count.
            // For a real app, you'd add a `chapters_count` to your books table/model
            // and pass it with the props to avoid this extra API call.
            // For now, we'll just simulate a quick way. Let's assume a max for simplicity.
            // TODO: A more robust solution is needed here.
            const bookInfo = await axios.get(`/api/books/${newBookId}/chapters/1/verses`); // A bit of a hack to get chapter info indirectly
            // This is a simplified example. A dedicated endpoint for chapter counts would be better.
            const bookWithChapters = props.books.find((b) => b.id === newBookId);
            // Let's create a placeholder for chapter numbers, as we don't have the count.
            // In a real app, you would pass chapter_count with each book from the controller.
            // For now, let's just create a list from 1 to 50 as a demo.
            chapters.value = Array.from({ length: 50 }, (_, i) => i + 1); // Replace 50 with actual chapter count
        } finally {
            isLoadingChapters.value = false;
        }
    }
});

watch(selectedChapter, async (newChapter) => {
    selectedVerseId.value = null;
    selectedVerse.value = null;
    verses.value = [];

    if (newChapter && selectedBookId.value) {
        isLoadingVerses.value = true;
        try {
            const response = await axios.get(`/api/books/${selectedBookId.value}/chapters/${newChapter}/verses`);
            verses.value = response.data;
        } catch (error) {
            console.error('Failed to fetch verses:', error);
        } finally {
            isLoadingVerses.value = false;
        }
    }
});

watch(selectedVerseId, (newVerseId) => {
    if (newVerseId) {
        selectedVerse.value = verses.value.find((v) => v.id === newVerseId) || null;
    } else {
        selectedVerse.value = null;
    }
});

const addVerseToMyList = () => {
    if (selectedVerse.value) {
        form.verse_id = selectedVerse.value.id;
        form.post(route('my-verses.store'), {
            preserveScroll: true,
            onSuccess: () => {
                // You can add a toast notification here
                console.log('Verse added!');
                // Reset to allow adding another
                selectedVerse.value = null;
                selectedVerseId.value = null;
            },
        });
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Explorar', href: '/explore' },
];
</script>

<template>
    <Head title="Explorar Versículos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <Card class="mx-auto max-w-3xl">
                <CardHeader>
                    <CardTitle>Explorador de Versículos</CardTitle>
                </CardHeader>
                <CardContent class="space-y-6">
                    <!-- Success Message -->
                    <Alert v-if="page.props.flash.success" variant="default">
                        <AlertTitle>¡Éxito!</AlertTitle>
                        <AlertDescription>{{ page.props.flash.success }}</AlertDescription>
                    </Alert>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <!-- Book Select -->
                        <Select v-model="selectedBookId">
                            <SelectTrigger><SelectValue placeholder="Selecciona un Libro" /></SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem v-for="book in props.books" :key="book.id" :value="book.id">
                                        {{ book.title }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>

                        <!-- Chapter Select -->
                        <Select v-model="selectedChapter" :disabled="!selectedBookId || isLoadingChapters">
                            <SelectTrigger><SelectValue placeholder="Selecciona un Capítulo" /></SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem v-for="chapterNum in chapters" :key="chapterNum" :value="chapterNum">
                                        Capítulo {{ chapterNum }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>

                        <!-- Verse Select -->
                        <Select v-model="selectedVerseId" :disabled="!selectedChapter || isLoadingVerses">
                            <SelectTrigger><SelectValue placeholder="Selecciona un Versículo" /></SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem v-for="verse in verses" :key="verse.id" :value="verse.id">
                                        Versículo {{ verse.verse_number }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Verse Display -->
                    <div v-if="selectedVerse" class="rounded-lg border bg-card p-6 text-card-foreground shadow-sm">
                        <blockquote class="prose border-l-4 pl-4 text-lg italic">
                            {{ selectedVerse.text }}
                        </blockquote>
                        <div class="mt-4 flex justify-end">
                            <Button @click="addVerseToMyList" :disabled="form.processing"> Añadir a Mi Lista </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
