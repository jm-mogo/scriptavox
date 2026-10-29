---
name: laravel-inertia-vue
description: "Trigger: Inertia, Vue, Laravel routes, Ziggy, forms. Conventions for ScriptaVox (Laravel 11 + Vue 3 + Inertia + TypeScript)."
license: Apache-2.0
metadata:
  author: team
  version: "1.0"
---

## Activation Contract
Load for frontend/backend bridge work involving Inertia, Vue components, or routes.

## Conventions
- Use Vue 3 `<script setup lang="ts">`.
- Use Inertia's `useForm`, `router`, and `Link` as appropriate.
- Keep pages in `resources/js/Pages/`, components in `resources/js/components/` following Shadcn/Vue patterns.
- Use TypeScript types where reasonable; follow existing patterns.
- Respect Laravel routes via Ziggy if used, or named routes.
- Keep server-side validation and authorization in Laravel.

## Tips
- Check `routes/web.php` for existing routes before adding new ones.
- Use existing UI components from `components/ui/` when possible.
- Follow the project's styling with Tailwind CSS.
