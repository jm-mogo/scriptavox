<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

// Shadcn Components
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

// Type definition for the data coming from our controller
interface VersesByBook {
    book_title: string;
    verse_count: number;
}
interface Stats {
    totalLearned: number;
    masteredCount: number;
    reviewingCount: number;
    currentStreak: number;
    versesByBook: VersesByBook[];
}
const props = defineProps<{
    stats: Stats;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    {
        title: 'Estadísticas',
        href: '',
    },
];
</script>

<template>
    <Head title="Mis Estadísticas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto max-w-4xl p-4 sm:p-6 lg:p-8">
            <h1 class="mb-6 text-3xl font-bold tracking-tight">Mis Estadísticas</h1>

            <!-- Stat Cards Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Racha Actual</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.currentStreak }} días 🔥</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Aprendidos</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalLearned }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">En Repaso</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.reviewingCount }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Dominados</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.masteredCount }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Verses by Book Table -->
            <div class="mt-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Progreso por Libro</CardTitle>
                        <CardDescription> Un desglose de cuántos versículos has aprendido de cada libro de la Biblia. </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-[300px]">Libro</TableHead>
                                    <TableHead class="text-right">Versículos Aprendidos</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="stats.versesByBook.length === 0">
                                    <TableCell colspan="2" class="text-center text-muted-foreground">
                                        Aún no has aprendido ningún versículo.
                                    </TableCell>
                                </TableRow>
                                <TableRow v-for="book in stats.versesByBook" :key="book.book_title">
                                    <TableCell class="font-medium">{{ book.book_title }}</TableCell>
                                    <TableCell class="text-right">{{ book.verse_count }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
