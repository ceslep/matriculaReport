# AGENTS.md - Development Guide for Agentic Coding

This document provides guidelines for agents working on this codebase.

## Project Overview

This is a **Svelte 5 + TypeScript + Vite + TailwindCSS v4** web application. The project is a student enrollment/registration report system.

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

### Deploy
```bash
npm run deploy       # Build and deploy to GitHub Pages
```

### Running Tests
This project currently has no test files. When tests are added:
- Install Vitest: `npm install -D vitest`
- Run a single test file: `npx vitest run src/path/to/test.spec.ts`
- Run tests matching a pattern: `npx vitest run -t "test name"`

## Code Style Guidelines

### Language & Framework
- **Svelte 5** (using runes: `$state`, `$derived`, `$effect`, `$props`)
- **TypeScript** with strict mode enabled
- **TailwindCSS v4** for styling (CSS-first configuration)
- **Vite** for bundling

### TypeScript Configuration
- Target: ES2022 (app), ES2023 (node)
- `strict: true` - all strict type checking enabled
- `checkJs: true` - typecheck JavaScript files
- `noUnusedLocals: true` - error on unused locals
- `noUnusedParameters: true` - error on unused parameters

### Imports & Path Resolution
- Use relative imports for local modules: `import Component from './components/Component.svelte'`
- Use package imports for dependencies: `import { mount } from 'svelte'`
- Import types explicitly: `import type { Estudiante, ApiError } from '../types/student'`
- Svelte mount pattern uses `mount()` from 'svelte'

### Naming Conventions
- **Files**: PascalCase for Svelte components, camelCase for TypeScript modules
- **Variables/functions**: camelCase
- **Classes/interfaces**: PascalCase
- **Constants**: UPPER_SNAKE_CASE for compile-time constants, camelCase otherwise
- **Directories**: kebab-case (`lib/components`, `lib/api`, `lib/types`)

### Svelte 5 Patterns
```svelte
<script lang="ts">
  // Props with defaults using $props
  let { onSearch, loading = false }: { 
    onSearch: (criterio: string) => void; 
    loading?: boolean;
  } = $props();

  // Reactive state with $state
  let searchValue = $state('');

  // Derived state with $derived
  let isValid = $derived(searchValue.trim().length > 0);

  // Effects with $effect
  $effect(() => {
    console.log('Search value changed:', searchValue);
  });
</script>

<!-- Use {#snippet} for named slots -->
{#snippet button()}
  <button>Click</button>
{/snippet}
```

### Error Handling
- Use try/catch for async operations
- Never use `any` type - use `unknown` and type narrowing
- Always narrow caught errors with type assertions: `const err = e as ApiError`
- Use Svelte's error boundary component pattern when appropriate

### API Client Pattern
The project uses a singleton API client pattern:
```typescript
export class ApiClient {
  private baseUrl: string;
  constructor(baseUrl: string = API_BASE_URL) {
    this.baseUrl = baseUrl.replace(/\/$/, '');
  }
  private async request<T>(endpoint: string, options: RequestInit = {}): Promise<T> {
    const response = await fetch(`${this.baseUrl}${endpoint}`, options);
    if (!response.ok) {
      const error: unknown = await response.json().catch(() => ({}));
      throw error;
    }
    return response.json();
  }
}
export const apiClient = new ApiClient();
```
- Use class-based API clients for organized API calls
- Export a singleton instance for global use
- Use `import.meta.env.VITE_*` for environment variables

### CSS & Styling
- Use TailwindCSS utility classes directly in templates
- TailwindCSS v4 uses CSS-first configuration (no tailwind.config.js)
- Custom styles in `app.css` or component `<style>` blocks sparingly

### File Organization
```
src/
├── main.ts              # Entry point
├── App.svelte           # Root component
├── app.css              # Global styles
├── lib/                 # Shared code
│   ├── api/             # API client
│   ├── components/      # Svelte components
│   └── types/           # TypeScript types
```

### TypeScript Best Practices
- Always define return types for functions when possible
- Use interfaces for object shapes, types for unions/aliases
- Avoid `!` non-null assertion unless absolutely certain

### Environment Variables
- Prefix with `VITE_` to expose to client-side code
- Example: `VITE_API_URL=https://tu-dominio.com`

### Pre-commit & Build Verification
Before submitting any code changes, run:
```bash
npm run check           # Must pass type checking
npm run build           # Must build successfully
```

## Editor Configuration

Recommended VS Code extensions:
- `svelte.svelte-vscode` - Svelte language support

## Backend APIs

### PHP API (public/pyni/api/)
- `api/config.php` - MySQL connection with PDO
- `api/buscar.php` - Search students endpoint (GET `/api/buscar.php?criterio=...`)
- `api/pdf.php` - Generate PDF endpoint (GET `/api/pdf.php?codigo=...`)
- Setup: `composer require tecnickcom/tcpdf`

### Python Backend (legacy - public/pyni/)
- `server.py` - Flask application
- Run: `cd public/pyni && pip install -r requirements.txt && python server.py`
