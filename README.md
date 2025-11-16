# Parf Edhellen

This is the source code for [elfdict.com](http://www.elfdict.com), a non-profit, free dictionary online for Tolkien's languages. Maintained by Leonard. Follow me on twitter at [@parmaeldo](https://twitter.com/parmaeldo).

## Technology Stack

### Backend
- **Framework**: Laravel 12.x
- **PHP**: 8.2+
- **Database**: MariaDB
- **Testing**: PHPUnit, PHPStan (level 1)

### Frontend
- **Framework**: React 18.3+
- **Language**: TypeScript 5.5+
- **State Management**: Redux Toolkit
- **Build Tools**: Webpack 5, Babel 7
- **Testing**: Jest, React Testing Library
- **Styling**: SCSS, Bootstrap 5

### Key Dependencies
- AWS SDK (for Comprehend services)
- Laravel Socialite (OAuth authentication)
- Server-side rendering (SSR) support
- Intervention Image (image processing)

## Service Configuration

Ensure that the following PHP dependencies are installed:

```
php8.2-curl php8.2-gd php8.2-intl php8.2-mbstring php8.2-mysql php8.2-readline php8.2-xml php8.2-zip
```

Configure the database using the model files in the `model/` directory. Execute the script files in ascending order as documented in `model/0. RUN IN ORDER.md`. You can apply the migrations once you've got Laravel configured.

To configure Laravel, run the following commands sequentially:

```bash
gh repo clone galadhremmin/Parf-Edhellen
cd Parf-Edhellen/src
npm install
mkdir bootstrap/cache
chmod 755 bootstrap/cache
mkdir -p storage/framework/{sessions,views,cache}
chmod -R 775 storage/framework
cp .env.example .env
vim .env # configure appropriately
composer install
php artisan key:generate
php artisan storage:link
```

> Always make sure to [follow Laravel's guidelines and best practices](https://laravel.com/docs/12.x/deployment) before moving the app into production.

## Development

Run *Parf Edhellen* locally by executing `php artisan serve`. You need to compile the TypeScript application by running `npm run watch` in a separate terminal.

### Available NPM Scripts
- `npm run watch` - Watch mode for development
- `npm run dev` - Single build in development mode
- `npm run production` - Production build (includes linting and testing)
- `npm run test` - Run Jest tests
- `npm run lint` - Run ESLint

### Node.js Version
Make sure to run Node v16. Parf Edhellen isn't currently compatible with Node 17+.

## Architecture

### Design Patterns

The application follows several established design patterns:

#### Repository Pattern
- **Purpose**: Abstracts data access logic from business logic
- **Location**: `app/Repositories/`
- **Interfaces**: Repository interfaces are defined in `app/Repositories/Interfaces/`
- **Usage**: Controllers and services depend on repository interfaces, not concrete implementations
- **Example**: `LexicalEntryRepository`, `DiscussRepository`, `SearchIndexRepository`

#### Adapter Pattern
- **Purpose**: Transforms data between different representations (models ↔ view models)
- **Location**: `app/Adapters/`
- **Usage**: Converts Eloquent models and database results into API-friendly formats
- **Example**: `BookAdapter`, `DiscussAdapter`, `SentenceAdapter`

#### Dependency Injection
- **Purpose**: Loose coupling and testability
- **Usage**: All dependencies are injected via constructor injection
- **Convention**: Protected properties prefixed with underscore (e.g., `$_repository`)

### Directory Structure

```
app/
├── Adapters/          # Data transformation adapters
├── Console/           # Artisan commands
├── Events/            # Event classes
├── Exceptions/        # Custom exception handlers
├── Factories/         # Factory classes
├── Helpers/           # Helper utility classes
├── Http/
│   ├── Controllers/   # Request handlers
│   ├── Discuss/       # Forum-specific controllers
│   └── Middleware/    # HTTP middleware
├── Interfaces/        # Interface definitions
├── Jobs/              # Queue jobs
├── Mail/              # Email templates
├── Models/            # Eloquent models
├── Repositories/      # Data access layer
├── Resolvers/         # Service resolvers
├── Security/          # Security utilities
└── Subscribers/       # Event subscribers
```

## Documentation

### API Documentation
Additional API documentation is available in the `src/docs/` directory:
- `BOOK_API_CONTROLLER.md` - Book/lexical entry API endpoints
- `LEXICAL_ENTRY_API_CONTROLLER.md` - Lexical entry operations
- `DISCUSS_API_CONTROLLER.md` - Forum/discussion API
- `EXPENSIVE_REQUESTS_MIDDLEWARE.md` - Performance monitoring
- `QUEUE_JOB_STATISTICS.md` - Queue job tracking

### Audit Trail
The audit trail consists of activities. Activities are specified as constants within the `App\Models\AuditTrail` class, and utilised throughout the application. The `App\Repositories\AuditTrailRepository` contains the necessary functionality for converting activities (which are integers) into human-readable strings.

_Note_: audit trail model objects with the property `is_admin` set to 1 (= true) can only be seen by administrators.

### Cookies
The following cookie names are used by the application:

| Cookie name | Description |
|-------------|-------------|
| ed-usermode | Administrators can give a cookie with this name the value _incognito_ to hide their activity. |

### System Errors
The schema `system_errors` contains information about client-side as well as server-side exceptions. Common exceptions (404 Page not found, 401 Unauthorized, etc.) are separated from the rest by the `is_common` column.

Uncaught client-side exceptions are caught by the `onerror` event, passed to a web API, and logged. Refer to the API documentation for more information.

## Coding Style

All contributors are expected to follow the coding style guidelines outlined below. Consistency is crucial for maintainability.

### General Principles

- **Indentation**: Tabs must be replaced with spaces
  - **PHP & JavaScript**: 4 spaces
  - **TypeScript/TSX & SCSS**: 2 spaces
- **Spacing**: Always use a single space between statements, brackets, and parentheses
- **Line Length**: Aim for reasonable line lengths; split long lines when they become hard to read

### PHP Style Guide

#### Naming Conventions
- **Classes & Interfaces**: PascalCase (e.g., `LexicalEntryRepository`, `IAuditTrailRepository`)
- **Methods & Variables**: camelCase (e.g., `getLexicalEntry()`, `$lexicalEntryId`)
- **Constants**: UPPER_SNAKE_CASE (e.g., `DEFAULT_SORT_BY_DATE_ORDER`)
- **Protected/Private Properties**: camelCase with underscore prefix (e.g., `$_repository`, `$_bookAdapter`)

#### Brace Placement
- **Classes, Interfaces, Methods**: Opening brace on a new line
- **Control Structures** (if, else, for, while, etc.): Opening brace on the same line

**Example:**
```php
class LexicalEntryRepository
{
    protected LexicalEntryRepository $_repository;

    public function getLexicalEntry(int $id)
    {
        if (empty($id)) {
            return null;
        } else {
            return $this->_repository->find($id);
        }
    }
}
```

#### Conditional Operators (Ternary)
- **Single line**: Use when the operation is short and readable
- **Multi-line**: Split when the operation is long enough to warrant readability improvement
  - First new line positioned **before** the question mark (`?`)
  - Second new line positioned **before** the colon (`:`)

**Example:**
```php
// Single line
$fruitName = $fruit instanceof Apple ? 'apple' : 'fruit';

// Multi-line
$customer = $fruit instanceof Apple
    ? FruitVendor::sell($fruit, $appleAdvert, true)
    : FruitVendor::discard($fruit);
```

#### Type Declarations
- Always use type hints for method parameters and return types
- Use PHPDoc comments for complex types and additional documentation
- Leverage PHP 8.2+ features (readonly properties, enums, etc.) where appropriate

#### Dependency Injection
- All dependencies must be injected via constructor
- Use interface types when available (e.g., `IAuditTrailRepository` instead of `AuditTrailRepository`)
- Store dependencies as protected properties with underscore prefix

### TypeScript/React Style Guide

#### Naming Conventions
- **Components**: PascalCase (e.g., `LexicalEntry`, `Panel`)
- **Functions & Variables**: camelCase (e.g., `getLexicalEntry`, `lexicalEntryId`)
- **Types & Interfaces**: PascalCase with `I` prefix for interfaces (e.g., `IProps`, `ILexicalEntry`)
- **Constants**: UPPER_SNAKE_CASE or camelCase depending on context
- **Files**: Match component/class name (e.g., `LexicalEntry.tsx`, `LexicalEntry._types.ts`)

#### Component Structure
- **Prefer functional components** over class components
- Use TypeScript interfaces for props (defined in `._types.ts` files)
- Destructure props at the top of the component
- Use default parameter values for optional props

**Example:**
```typescript
import type { IProps } from './Panel._types';

function Panel(props: IProps) {
    const {
        children,
        className,
        title = null,
        shadow,
    } = props;

    return <div className={classNames("card", className ?? '')}>
        {children ?? ''}
    </div>;
}

export default Panel;
```

#### TypeScript Configuration
- **Strict Mode**: Enabled (`noImplicitAny: true`)
- **Module System**: ESNext modules
- **JSX**: React 18 JSX transform (`react-jsx`)
- **Path Aliases**: Use `@root/*` for imports from `resources/assets/ts`

#### Code Organization
- **Component Files**: `ComponentName.tsx`
- **Type Files**: `ComponentName._types.ts`
- **Test Files**: `ComponentName._spec.tsx`
- **Style Files**: `ComponentName.scss` (co-located with component)

#### Import Order
1. External dependencies (React, libraries)
2. Internal utilities and helpers
3. Types (with `type` keyword)
4. Component-specific imports
5. Styles

**Example:**
```typescript
import classNames from '@root/utilities/ClassNames';
import type { IProps } from './LexicalEntry._types';
import LexicalEntryDetails from './LexicalEntryDetails';
import LexicalEntryTitle from './LexicalEntryTitle';

import './LexicalEntry.scss';
```

#### ESLint Configuration
- Follow the rules defined in `.eslintrc.js`
- Some TypeScript strict rules are disabled for pragmatic reasons (documented in config)
- Always fix linting errors before committing

### SQL Style Guide

- **Keywords**: UPPER CASE (e.g., `SELECT`, `FROM`, `WHERE`, `JOIN`)
- **Table/Column Names**: snake_case (e.g., `lexical_entries`, `account_id`)
- **Indentation**: 4 spaces
- **Line Breaks**: Use line breaks for readability in complex queries

**Example:**
```sql
SELECT g.id, g.word_id, g.language_id
FROM lexical_entries g
WHERE g.sense_id IN (1, 2, 3)
    AND g.is_deleted = 0
ORDER BY g.id ASC;
```

### SCSS Style Guide

- **Indentation**: 2 spaces
- **Naming**: kebab-case for class names (e.g., `.lexical-entry`, `.panel-title`)
- **Nesting**: Maximum 3-4 levels deep
- **Variables**: Use SCSS variables defined in `_scss/_variables.scss`

### File Organization

- **One class/component per file**: Each file should contain a single class, component, or related functionality
- **Co-location**: Keep related files together (component, types, styles, tests)
- **Barrel Exports**: Use index files for clean imports when appropriate

### Testing

- **PHP**: Write unit tests in `tests/Unit/` using PHPUnit
- **TypeScript**: Write tests in `._spec.tsx` files using Jest and React Testing Library
- **Coverage**: Aim for meaningful test coverage, especially for critical business logic

### Code Quality Tools

- **PHP**: PHPStan (level 1) for static analysis
- **TypeScript**: ESLint with TypeScript plugin
- **Formatting**: Follow the style guide; consider using Laravel Pint for PHP formatting

### Git Commit Messages

- Use clear, descriptive commit messages
- Reference issue numbers when applicable
- Follow conventional commit format when possible

## Want to Help Out?

If you are interested in helping out, please get in touch with [galadhremmin](https://github.com/galadhremmin).
You can also help us by donating. Please visit [elfdict.com](http://www.elfdict.com) for more information.

## License

ElfDict (Parf Edhellen; elfdict.com) is licensed in accordance with [AGPL](https://tldrlegal.com/license/gnu-affero-general-public-license-v3-(agpl-3.0)).
