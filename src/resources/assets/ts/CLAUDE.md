# CLAUDE.md — TypeScript Frontend

This directory contains the React/TypeScript frontend for Parf Edhellen, compiled by Webpack 5 with swc-loader. Type checking is NOT part of the build — run `npx tsc --noEmit` separately.

## Directory Layout

```
ts/
├── apps/           # 19 independent React SPAs, each with its own entry point
├── components/     # Shared reusable components (48+), including Form/ subdir
├── connectors/     # API communication layer — fetch-based, typed per endpoint
│   └── backend/    # One connector + interface per API resource
├── di/             # Lightweight DI container (config, keys, types)
├── utilities/      # Shared helpers: func/, hooks/, redux/, caching, dates, etc.
├── _types/         # Global TS type helpers (Redux, DI, module declarations)
├── _scss/          # SCSS partials — variables, theme tokens, mixins
├── security/       # Auth/authorization utilities
├── config.ts       # Global constants (API path, timeouts, event names)
├── index.tsx       # Bootstrap: sets up DI, hydrates SSR, injects apps
└── index.scss      # Root stylesheet
```

## App Patterns

Each app lives in `apps/<name>/` and exports a component wrapped with `registerApp()`.

**Pattern A — No Redux** (display-only apps):
```tsx
import registerApp from '../app';
import Orchestrator from './containers/Orchestrator';
export default registerApp(Orchestrator);
```

**Pattern B — Per-app Redux** (games, complex forms):
```tsx
const store = configureStore({ reducer: rootReducer, middleware: ... });
const Inject = (props: IProps) => <Provider store={store}><MyApp /></Provider>;
export default registerApp(Inject);
```

**Important:** There is no global Redux store. Each app creates its own store at module level. Apps do not share state through Redux — use `GlobalEventConnector` for cross-app communication.

## File & Naming Conventions

Every component lives in its own folder with co-located files:
```
ComponentName.tsx          # Component
ComponentName._types.ts    # Interfaces/types for this component only
ComponentName._spec.tsx    # Jest tests
ComponentName.scss         # Scoped styles
```

- TypeScript interfaces: prefix with `I` (`IProps`, `ILexicalEntry`, `IGameAction`)
- CSS classes: BEM-like double-dash (`Avatar--picture`, `Dialog--open`)
- Constants: SCREAMING_SNAKE_CASE
- Reducers follow the same co-location: `reducers/GlossesReducer.ts` + `IGlossesReducer.ts` + `index.ts`

## Dependency Injection

Services are resolved from a DI container — do not import connectors directly.

```ts
import { resolve } from '@root/di';
import { DI } from '@root/di/keys';

const api = resolve(DI.BookApi);         // returns singleton IBookApi
const events = resolve(DI.GlobalEvents); // returns new instance
```

Use `withPropInjection` to auto-resolve DI services as props on a component — useful when the component receives the service as a prop but you don't want to wire it at the call site:
```ts
export default withPropInjection(Orchestrator, {
    globalEvents: DI.GlobalEvents,  // injected if not passed by parent
});
```
The HOC resolves missing props from the DI container on first render and holds them in a `useRef` so non-singleton instances (created via `setInstance`) aren't reconstructed on every render. Props passed by the parent always take precedence.

All singletons are registered in `di/config.ts`. When adding a new service:
1. Add a key to `di/keys.ts` (string enum `DI`)
2. Register via `setSingleton(DI.MyService, MyConcreteClass)` in `di/config.ts`
3. Add to `DIContainerType` in `di/config._types.ts`

## API Connectors

All HTTP goes through `ApiConnector` (native Fetch, no axios). Connectors live in `connectors/backend/`, each implementing a typed interface:

```ts
// IBookApi.ts — interface
export default interface IBookApi {
    find(args: IFindRequest): Promise<IFindResponse>;
}

// BookApiConnector.ts — implementation
export default class BookApiConnector implements IBookApi {
    constructor(private _api = resolve(DI.BackendApi)) {}

    find(args: IFindRequest) {
        return this._api.post<IFindResponse>('book/find', args);
    }
}
```

**Automatic case conversion:** API responses (snake_case) are automatically converted to camelCase on receipt. Outgoing payloads are sent as-is — convert to snake_case if the backend expects it.

**Error handling:** 422 responses throw `ValidationError`. Other non-OK responses throw a structured `IConnectorError` with `response.status` and `response.data`.

## State Management (Redux)

```ts
// Action class pattern (thunk-based):
export default class GameActions {
    constructor(private _api = resolve(DI.WordFinderApi)) {}

    public loadGame(languageId: number) {
        return async (dispatch: ReduxThunkDispatch) => {
            const game = await this._api.newGame(languageId);
            dispatch(this.initializeGame(game));
        };
    }
}
```

Use `ReduxThunkDispatch` and `ReduxThunk` from `@root/_types/redux`.

## Form Components

Form controls extend the abstract `FormComponent` base class in `components/Form/FormComponent.tsx`. It provides:
- `pickComponentProps()` — extracts relevant props, passes remainder to the DOM element
- `convertValue()` — override to coerce the raw input value to the typed `V`
- `onBackingComponentChange` — pre-wired change handler that fires the `onChange` prop

Component events use `IComponentEvent<V>` and the `fireEvent()` helper:
```ts
fireEvent(this, this.props.onChange, value);
```

## Cross-App Communication

Use `GlobalEventConnector` (resolved via `resolve(DI.GlobalEvents)`) or raw `CustomEvent` for communication between independent apps. Event names are constants in `config.ts` (e.g., `GlobalEventLoadReference`, `GlobalEventLoadGlossary`).

## SCSS & Theming

Custom color tokens are CSS variables on `:root` — use these, not hardcoded hex:
- `var(--ed-bg)`, `var(--ed-bg-surface)` — backgrounds
- `var(--ed-text)`, `var(--ed-text-muted)` — text
- `var(--ed-border)`, `var(--ed-shadow)` — borders and shadows
- `var(--bs-*)` — Bootstrap tokens (spacing, border-radius, etc.)

Dark mode is automatic via `@media (prefers-color-scheme: dark)` and `[data-bs-theme="dark"]` on `:root`. Never write duplicate dark-mode selectors in component SCSS — use `--ed-*` variables and they adapt automatically.

Bootstrap is imported selectively in `_scss/_variables.scss`. Do not import Bootstrap components that aren't already there.

## Path Alias

`@root/*` maps to `resources/assets/ts/*`. Use it for all non-relative imports:
```ts
import { resolve } from '@root/di';
import type { ILexicalEntry } from '@root/_types/book';
```

## Testing

Tests use Jest + React Testing Library. Test file suffix: `._spec.tsx` (or `._spec.ts`).

```ts
import { render } from '@testing-library/react';
import { describe, expect, test } from '@jest/globals';

describe('components/MyComponent', () => {
    test('renders correctly', () => {
        const { container } = render(<MyComponent />);
        expect(container.querySelector('.MyComponent--root')).toBeTruthy();
    });
});
```

Run a single test: `npx jest path/to/File._spec.tsx`
