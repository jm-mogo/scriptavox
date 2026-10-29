# 0001 - Use TALL stack philosophy with Vue & Inertia.js

- Date: 2026-10-27
- Status: Accepted

## Context
We need a modern, reactive web app for scripture memorization with a smooth SPA experience without building a separate API from scratch.

## Decision
Use Laravel 11+ as the backend, Vue.js 3 with TypeScript and Inertia.js as the SPA bridge, Tailwind CSS for styling, and Shadcn/Vue for UI components. This adapts the TALL stack philosophy to Vue.

## Consequences
- **Gain:** Rapid development, shared routing/auth, great DX, easy to deploy as a monolith
- **Lose:** Some SPA patterns differ from a headless API; SSR requires extra setup
- **Pending:** Keep an eye on Inertia v2 patterns as the project evolves
