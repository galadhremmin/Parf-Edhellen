# CLAUDE.md — PHP Backend (`app/`)

Laravel 12 + PHP 8.2. All HTTP entry points are in `Http/Controllers/`; everything else is injected.

## Layer Overview

```
Http/Controllers/   # Receive request, call adapters/repositories, return response
Adapters/           # Transform Eloquent models → API view models (presentation logic)
Repositories/       # Data access — all DB queries go here, never in controllers
Services/           # Stateless business logic that doesn't fit repositories or adapters
Jobs/               # Async queue workers (implement ShouldQueue)
Events/             # Thin data carriers for domain occurrences
Subscribers/        # React to events — one subscriber can handle many event types
Resolvers/          # Single-purpose __invoke() classes for resource resolution
Interfaces/         # App-level service contracts (IMarkdownParser, ISystemLanguageFactory, etc.)
Models/             # Eloquent ORM entities
Http/Middleware/    # Request filtering and transformation
```

## Dependency Injection

All dependencies are constructor-injected and stored as `protected` properties with an underscore prefix:

```php
class BookApiController extends Controller
{
    public function __construct(
        protected readonly IBookRepository $_bookRepository,
        protected readonly BookAdapter $_bookAdapter,
    ) {}
}
```

**Always inject the interface, not the concrete class.** Bindings are registered in `app/Providers/AppServiceProvider.php`:

```php
$this->app->singleton(IBookRepository::class, BookRepository::class);

// Conditional binding (HTTP vs console):
$this->app->singleton(IAuditTrailRepository::class, function ($app) {
    return $app->make(
        $app->runningInConsole()
            ? NoopAuditTrailRepository::class
            : AuditTrailRepository::class
    );
});
```

## Repository Pattern

Repositories abstract all DB access. Interfaces live in `Repositories/Interfaces/`.

```php
// Interface
interface IWordRepository
{
    public function findByKeyword(string $keyword): ?Word;
}

// Implementation — Eloquent queries only here, never in controllers
class WordRepository implements IWordRepository
{
    public function findByKeyword(string $keyword): ?Word
    {
        return Word::whereRaw('BINARY normalised_word = ?', [
            StringHelper::normalize($keyword),
        ])->first();
    }
}
```

Some repositories have **Noop variants** (`Repositories/Noop/`) that implement the same interface but do nothing — used in console context or tests to disable side effects without conditionals.

## Adapter Pattern

Adapters transform domain models into presentation-ready arrays/objects. They depend on repositories; controllers depend on adapters.

```php
class AuditTrailAdapter
{
    public function __construct(
        protected readonly IAuditTrailRepository $_repository,
        protected readonly LinkHelper $_linkHelper,
    ) {}

    public function adapt(Collection $actions): array
    {
        return $actions->map(fn ($action) => [
            'message' => $this->toHumanMessage($action),
            'entity'  => $this->resolveEntityLink($action->entity),
        ])->all();
    }
}
```

## Events & Subscribers

Events are thin data carriers (no logic). Subscribers map event classes to handler methods via `subscribe()`.

```php
// Event — data only
class LexicalEntryCreated
{
    public function __construct(
        public readonly LexicalEntry $lexicalEntry,
        public readonly int $accountId,
    ) {}
}

// Subscriber — handles multiple related events
class AuditTrailSubscriber
{
    public function subscribe(): array
    {
        return [
            LexicalEntryCreated::class => 'onGlossCreated',
            LexicalEntryEdited::class  => 'onGlossEdited',
        ];
    }

    public function onGlossCreated(LexicalEntryCreated $event): void
    {
        $this->_repository->store(AuditTrail::ACTION_GLOSS_CREATED, $event->lexicalEntry, $event->accountId);
    }
}
```

Heavy work triggered by events is dispatched to a **Job**, not done inline:
```php
public function onGlossCreated(LexicalEntryCreated $event): void
{
    ProcessSearchIndexCreation::dispatch($event->lexicalEntry);
}
```

## Jobs

Jobs implement `ShouldQueue`. Constructor captures data; `handle()` receives injected dependencies when the job runs.

```php
class ProcessDiscussIndex implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly ForumPost $post) {}

    public function handle(
        SearchIndexRepository $searchIndexRepository,
        ISystemLanguageFactory $systemLanguageFactory,
    ): void {
        // heavy processing here
    }
}
```

## Routes

Routes are split into modular files under `routes/api-routes/` and `routes/web-routes/`. All API routes share the `API_PATH` prefix (defined in `routes/constants.php`; resolves to `api/v{version}`).

```php
// routes/api-routes/api-public.php
Route::group(['prefix' => API_PATH], function () {
    Route::get('book/translate/{glossId}', [BookApiController::class, 'get'])
        ->where(['glossId' => REGULAR_EXPRESSION_NUMERIC])
        ->middleware('throttle:60,1')
        ->name('api.book.gloss');
});
```

Regex constants (`REGULAR_EXPRESSION_NUMERIC`, `REGULAR_EXPRESSION_SEO_STRING`) are defined in `routes/constants.php` — use them for route constraints instead of inline patterns.

Route files:
- `api-public.php` — unauthenticated API
- `api-user.php` — authenticated user API
- `api-admin.php` — admin-only API
- `api-game.php`, `api-discuss.php`, `api-discuss-feed.php` — feature-specific

## Middleware

Standard Laravel middleware signature. Role-based access uses the `CheckRole` middleware with a parameter:

```php
Route::post('...', [AdminController::class, 'store'])
    ->middleware('role:admin');
```

`LogExpensiveRequests` tracks slow requests above `ED_EXPENSIVE_REQUEST_THRESHOLD` (ms).

## Coding Style

- 4 spaces indentation
- Classes/interfaces/methods: opening brace on **new line**; control structures: brace on **same line**
- Classes & interfaces: PascalCase; methods & variables: camelCase; constants: UPPER_SNAKE_CASE
- Protected/private properties: camelCase with `_` prefix (`$_repository`, `$_bookAdapter`)
- Always declare parameter and return types; use PHP 8.2+ features (readonly, enums, etc.)
- Space before negation: `if (! $this->enabled())`
- Interfaces prefixed with `I`: `IBookRepository`, `IMarkdownParser`

Format with `./vendor/bin/pint`. Analyse with `./vendor/bin/phpstan analyse` (level 1, `app/` only).
