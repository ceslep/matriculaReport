# AGENTS.md - Development Guide for Agentic Coding

This document provides guidelines for agents working on this codebase.

## Project Overview

This is a **Svelte 5 + TypeScript + Vite + TailwindCSS** web application. The project is a student enrollment/registration report system.

## Build, Lint, and Test Commands

### Development
```bash
npm run dev          # Start Vite dev server with hot reload
```

### Build & Preview
```bash
npm run build        # Build for production (outputs to dist/)
npm run preview      # Preview production build locally
```

### Type Checking & Linting
```bash
npm run check        # Run svelte-check + TypeScript validation
```

### Running a Single Test
There are currently no test files in this project. If tests are added:
- Use Vitest as the testing framework
- Run a single test file: `npx vitest run src/path/to/test.spec.ts`
- Run tests matching a pattern: `npx vitest run -t "test name"`

## Code Style Guidelines

### Language & Framework
- **Svelte 5** (using runes: `$state`, `$derived`, `$effect`, `$props`)
- **TypeScript** with strict mode enabled
- **TailwindCSS v4** for styling
- **Vite** for bundling

### TypeScript Configuration
- Target: ES2022 (app), ES2023 (node)
- `strict: true` - all strict type checking enabled
- `checkJs: true` - typecheck JavaScript files
- `noUnusedLocals: true` - error on unused locals
- `noUnusedParameters: true` - error on unused parameters

### Imports & Path Resolution
- Use relative imports for local modules: `./components/Component.svelte`
- Use package imports for dependencies: `import { something } from 'svelte'`
- Use `$lib` alias for library code if configured (not currently set up)

### Naming Conventions
- **Files**: kebab-case for Svelte components (`MyComponent.svelte`), PascalCase for TypeScript modules
- **Variables/functions**: camelCase
- **Classes/interfaces**: PascalCase
- **Constants**: UPPER_SNAKE_CASE for compile-time constants, camelCase otherwise
- **Components**: PascalCase (e.g., `EnrollmentForm.svelte`)

### Svelte 5 Patterns
```svelte
<script lang="ts">
  // Use runes for reactive state
  let count = $state(0);
  
  // Derived state
  let doubled = $derived(count * 2);
  
  // Effects
  $effect(() => {
    console.log('Count changed:', count);
  });
  
  // Props with defaults
  let { title = 'Default', onSubmit } = $props<{
    title?: string;
    onSubmit: () => void;
  }>();
</script>

<template>
  <!-- Use {#snippet} for named slots -->
  {#snippet button()}
    <button>Click</button>
  {/snippet}
</template>
```

### Error Handling
- Use try/catch for async operations
- Never use `any` type - use `unknown` and type narrowing
- Handle errors with proper TypeScript types
- Use Svelte's error boundary component pattern when appropriate

### CSS & Styling
- Use TailwindCSS utility classes in component templates
- Custom styles go in `app.css` or use `<style>` blocks sparingly
- TailwindCSS v4 uses CSS-first configuration (no tailwind.config.js)

### File Organization
```
src/
├── main.ts              # Entry point
├── App.svelte           # Root component
├── app.css              # Global styles
├── lib/                 # Shared components/utilities
│   └── components/
├── routes/              # Page components (if using SvelteKit)
└── assets/              # Static assets
```

### TypeScript Best Practices
- Always define return types for functions when possible
- Use interfaces for object shapes, types for unions/aliases
- Enable strict null checks (`strictNullChecks` is on via strict mode)
- Avoid `!` non-null assertion unless absolutely certain

### Git & Commits
- This repo is not initialized with git
- If git is added, use conventional commit messages

### Pre-commit & Build Verification
Before submitting any code changes, run:
```bash
npm run check           # Must pass type checking
npm run build           # Must build successfully
```

## Editor Configuration

Recommended VS Code extensions:
- `svelte.svelte-vscode` - Svelte language support

## PHP Backend API (public/pyni/api/)

The project includes a PHP API for the MySQL database (for deployment on PHP hosting):

### Files
- `api/config.php` - MySQL connection with PDO
- `api/buscar.php` - Search students endpoint (GET `/api/buscar.php?criterio=...`)
- `api/pdf.php` - Generate PDF endpoint (GET `/api/pdf.php?codigo=...`)

### Setup
1. Install TCPDF: `composer require tecnickcom/tcpdf`
2. Configure database credentials in `api/config.php`
3. Copy images folder to hosting: `public/pyni/images/`

### Configuration
Set the API URL in `.env`:
```
VITE_API_URL=https://tu-dominio.com
```

## Python Backend (legacy - public/pyni/)

The original Python Flask backend is still available in `public/pyni/`:
- `server.py` - Flask application
- `requirements.txt` - Python dependencies
- Run with: `cd public/pyni && pip install -r requirements.txt && python server.py`
