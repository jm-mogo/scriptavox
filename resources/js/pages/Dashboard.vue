<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

// Shadcn Components
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

// This is the correct props definition, matching what the controller sends.
interface Stats {
    total_learned: number;
    mastered_count: number;
    review_count: number;
}
const props = defineProps<{
    stats: Stats;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto max-w-4xl p-4 sm:p-6 lg:p-8">
            <!-- Stat Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Repasos Pendientes</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.review_count }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Versículos Aprendidos</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_learned }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Versículos Dominados</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.mastered_count }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Daily Review Card -->
            <div class="mt-6">
                <Card class="mx-auto max-w-3xl text-center">
                    <CardHeader>
                        <CardTitle class="text-2xl">Repaso Diario</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="stats.review_count > 0">
                            <p class="text-lg text-muted-foreground">
                                Tienes <span class="font-bold text-primary">{{ stats.review_count }}</span> versículos listos para repasar hoy.
                            </p>
                            <!-- This route now correctly links to the start of the review flow -->
                            <Link :href="route('review.start')">
                                <Button size="lg" class="mt-6">Comenzar Repaso</Button>
                            </Link>
                        </div>
                        <div v-else>
                            <p class="text-lg text-muted-foreground">¡Excelente! No tienes repasos pendientes por hoy.</p>
                            <Link :href="route('explore.show')">
                                <Button variant="secondary" class="mt-6">Aprender un Nuevo Versículo</Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
