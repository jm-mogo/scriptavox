# Architecture Overview

## Stack
- **Backend:** Laravel 11+
- **Frontend:** Vue.js 3 (TypeScript, `<script setup>`)
- **Routing/Bridge:** Inertia.js
- **Styling:** Tailwind CSS
- **UI Components:** [Shadcn/Vue](https://www.shadcn-vue.com/)
- **Database:** MySQL (default) / SQLite supported
- **Build:** Vite

## High-level flow
ScriptaVox is a single-page app powered by Inertia. Laravel handles routes/controllers, models, and business logic. Vue components render the UI, with shared UI built from Shadcn/Vue components.

## Core pillars
- **Learn:** Study mode and word-bank exercises
- **Recite:** Practice recitation with Web Speech API feedback and diff
- **Retain:** Spaced repetition system (SRS) for daily reviews

## Key directories
- `app/` - Laravel application code (Models, Http/Controllers, etc.)
- `resources/js/` - Vue/TypeScript frontend
- `routes/web.php` - Web routes
- `database/` - Migrations, seeders, and Bible data cache
