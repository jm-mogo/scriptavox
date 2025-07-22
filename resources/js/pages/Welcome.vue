<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/vue3';
import { BookCheck, BrainCircuit, Ear } from 'lucide-vue-next';

interface Feature {
    icon: any;
    title: string;
    description: string;
}

const features: Feature[] = [
    {
        icon: BookCheck,
        title: 'Aprende con Métodos Probados',
        description:
            'Utiliza técnicas interactivas como bancos de palabras y ejercicios de primera letra para construir una base sólida en tu memoria.',
    },
    {
        icon: Ear,
        title: 'Recita y Recibe Feedback',
        description:
            'Usa tu voz para recitar los versículos. Nuestra tecnología de reconocimiento de voz te dará una puntuación de precisión y te mostrará exactamente dónde mejorar.',
    },
    {
        icon: BrainCircuit,
        title: 'Retén a Largo Plazo',
        description:
            'Nuestro sistema de repetición espaciada (SRS) te recordará repasar los versículos en el momento justo, moviéndolos de tu memoria a corto plazo a la de largo plazo.',
    },
];
</script>

<template>
    <Head title="Bienvenido a ScriptaVox" />
    <div class="flex min-h-screen flex-col bg-slate-50 text-slate-900 dark:bg-slate-900 dark:text-slate-50">
        <!-- Header / Navigation -->
        <header class="sticky top-0 z-40 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div class="container mx-auto flex h-14 max-w-7xl items-center px-4 sm:px-6 lg:px-8">
                <div class="mr-4 flex">
                    <Link :href="route('home')" class="mr-6 flex items-center space-x-2">
                        <span class="text-xl font-bold">ScriptaVox</span>
                    </Link>
                </div>
                <div class="flex flex-1 items-center justify-end space-x-2">
                    <nav class="flex items-center space-x-2">
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')">
                            <Button variant="ghost">Dashboard</Button>
                        </Link>
                        <template v-else>
                            <Link :href="route('login')">
                                <Button variant="ghost">Iniciar Sesión</Button>
                            </Link>
                            <Link :href="route('register')">
                                <Button>Registrarse</Button>
                            </Link>
                        </template>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1">
            <!-- ========================================================================= -->
            <!-- THE FIX IS APPLIED TO EACH SECTION'S INNER DIV -->
            <!-- ========================================================================= -->

            <!-- Hero Section -->
            <section class="w-full py-12 md:py-24 lg:py-32">
                <!-- This inner div now handles max-width, centering, and padding -->
                <div class="container mx-auto max-w-7xl px-4 md:px-6">
                    <div class="grid gap-6 lg:grid-cols-[1fr_400px] lg:gap-12 xl:grid-cols-[1fr_600px]">
                        <div class="flex flex-col justify-center space-y-4">
                            <div class="space-y-2">
                                <h1 class="text-3xl font-bold tracking-tighter sm:text-5xl xl:text-6xl/none">
                                    Memoriza la Palabra de Dios, <br />
                                    <span class="text-primary">Versículo a Versículo.</span>
                                </h1>
                                <p class="max-w-[600px] text-muted-foreground md:text-xl">
                                    ScriptaVox es tu coach personal de memorización. Aprende, recita y retén las Escrituras como nunca antes con
                                    nuestra plataforma interactiva.
                                </p>
                            </div>
                            <div class="flex flex-col gap-2 min-[400px]:flex-row">
                                <Link :href="route('register')">
                                    <Button size="lg">Comienza Gratis</Button>
                                </Link>
                            </div>
                        </div>
                        <img
                            alt="Hero"
                            class="mx-auto aspect-video overflow-hidden rounded-xl object-cover sm:w-full lg:order-last lg:aspect-square"
                            height="550"
                            src="/biblesharing.webp"
                            width="550"
                        />
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section class="w-full bg-muted py-12 md:py-24 lg:py-32 dark:bg-slate-800">
                <div class="container mx-auto max-w-7xl px-4 md:px-6">
                    <div class="flex flex-col items-center justify-center space-y-4 text-center">
                        <div class="space-y-2">
                            <div class="inline-block rounded-lg bg-secondary px-3 py-1 text-sm">Características Principales</div>
                            <h2 class="text-3xl font-bold tracking-tighter sm:text-5xl">Una Manera Más Inteligente de Memorizar</h2>
                            <p class="max-w-[900px] text-muted-foreground md:text-xl/relaxed lg:text-base/relaxed xl:text-xl/relaxed">
                                Nuestra metodología de 3 pasos te guía desde el primer contacto con un versículo hasta su retención a largo plazo.
                            </p>
                        </div>
                    </div>
                    <div class="mx-auto grid max-w-5xl items-start gap-12 py-12 lg:grid-cols-3">
                        <div v-for="feature in features" :key="feature.title" class="grid gap-4 text-center">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                <component :is="feature.icon" class="h-8 w-8" />
                            </div>
                            <h3 class="text-xl font-bold">{{ feature.title }}</h3>
                            <p class="text-sm text-muted-foreground">{{ feature.description }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Final Call to Action Section -->
            <section class="w-full py-12 md:py-24 lg:py-32">
                <div class="container mx-auto grid max-w-7xl items-center justify-center gap-4 px-4 text-center md:px-6">
                    <div class="space-y-3">
                        <h2 class="text-3xl font-bold tracking-tighter md:text-4xl/tight">¿Listo para comenzar tu viaje?</h2>
                        <p class="mx-auto max-w-[600px] text-muted-foreground md:text-xl/relaxed lg:text-base/relaxed xl:text-xl/relaxed">
                            Crea tu cuenta gratuita hoy y empieza a atesorar la Palabra de Dios en tu corazón.
                        </p>
                    </div>
                    <div class="mx-auto w-full max-w-sm space-y-2">
                        <Link :href="route('register')">
                            <Button size="lg" class="w-full">Únete a ScriptaVox Ahora</Button>
                        </Link>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="w-full border-t">
            <div class="container mx-auto flex h-14 max-w-7xl items-center justify-center text-sm text-muted-foreground">
                <p>© {{ new Date().getFullYear() }} ScriptaVox. Todos los derechos reservados.</p>
            </div>
        </footer>
    </div>
</template>
