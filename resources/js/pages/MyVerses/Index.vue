<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

// Shadcn Components
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

// Define the shape of the data coming from Laravel
interface Verse {
    id: number;
    text: string;
    chapter: number;
    verse_number: number;
    book: {
        title: string;
    };
}
interface VerseProgress {
    id: number;
    status: string;
    review_at: string;
    verse: Verse;
}
interface PaginatedResponse {
    data: VerseProgress[];
    // Include other pagination properties like links, current_page, etc.
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    current_page: number;
    last_page: number;
}

const props = defineProps<{
    versesProgress: PaginatedResponse;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    {
        title: 'Mis Versículos',
        href: '',
    },
];

// Helper to format the status for display
const formatStatus = (status: string) => {
    const map: { [key: string]: { text: string; variant: 'default' | 'secondary' | 'destructive' | 'outline' } } = {
        new: { text: 'Nuevo', variant: 'secondary' },
        learning: { text: 'Aprendiendo', variant: 'secondary' },
        reviewing: { text: 'Repasando', variant: 'default' },
        mastered: { text: 'Dominado', variant: 'outline' },
    };
    return map[status] || { text: status, variant: 'secondary' };
};
</script>

<template>
    <Head title="Mis Versículos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto max-w-4xl p-4 sm:p-6 lg:p-8">
            <div class="mb-6 flex flex-col items-start gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-3xl font-bold tracking-tight">Mi Lista de Versículos</h1>
                <Link :href="route('explore.show')">
                    <Button>Añadir Nuevo Versículo</Button>
                </Link>
            </div>

            <!-- Empty State -->
            <div v-if="!versesProgress.data.length" class="text-center">
                <Card class="p-8">
                    <CardTitle>Tu lista está vacía</CardTitle>
                    <CardDescription class="mt-2"> ¡Comienza tu viaje de memorización añadiendo tu primer versículo! </CardDescription>
                    <Link :href="route('explore.show')" class="mt-4 inline-block">
                        <Button>Explorar la Biblia</Button>
                    </Link>
                </Card>
            </div>

            <!-- Verse List -->
            <div v-else class="space-y-4">
                <Card v-for="progress in versesProgress.data" :key="progress.id">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-xl">
                                {{ progress.verse.book.title }} {{ progress.verse.chapter }}:{{ progress.verse.verse_number }}
                            </CardTitle>
                            <Badge :variant="formatStatus(progress.status).variant">
                                {{ formatStatus(progress.status).text }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <blockquote class="prose max-w-none border-l-4 pl-4 text-muted-foreground italic">
                            {{ progress.verse.text }}
                        </blockquote>
                        <div class="mt-4 flex justify-end gap-2">
                            <!-- ===================================================================== -->
                            <!-- THE FIX IS HERE: Changed 'recite.show' to 'practice.show' -->
                            <!-- ===================================================================== -->
                            <Link :href="route('practice.show', { verse: progress.verse.id })">
                                <Button variant="secondary">Practicar</Button>
                            </Link>
                            <!-- You can add a delete button here later -->
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Pagination Controls -->
            <div v-if="versesProgress.data.length && versesProgress.last_page > 1" class="mt-6 flex justify-between">
                <Link v-if="versesProgress.links.prev" :href="versesProgress.links.prev" preserve-scroll>
                    <Button variant="outline">Anterior</Button>
                </Link>
                <div v-else></div>
                <!-- Placeholder for spacing -->

                <Link v-if="versesProgress.links.next" :href="versesProgress.links.next" preserve-scroll>
                    <Button variant="outline">Siguiente</Button>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
