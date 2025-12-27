# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Git Workflow

**IMPORTANT: After completing each major procedure (feature implementation, refactoring, bug fix, etc.), ALWAYS make a commit.**

- ✅ Commit after completing a logical unit of work
- ✅ Use clear, descriptive commit messages in Russian
- ✅ No need for detailed analysis - just commit the changes
- ✅ Example: `git commit -m "Добавлен Stylelint в проект"`

## Project Overview

Art Platform (wallone.app) - A Laravel-based social platform for artists to showcase portfolios, interact with community, and monetize their work. Features include user profiles, portfolio management, social interactions (follows, likes, comments), billing/payments, gamification, real-time messaging, and notifications.

**Tech Stack:**
- Laravel 10, PHP 8.2+
- MySQL database
- Laravel Passport (OAuth authentication)
- Laravel Reverb (WebSockets for real-time features)
- Redis (cache, sessions, queues)
- Docker (production deployment)

## CRITICAL: Development Principles for Claude Code

**These rules MUST be followed at all times when working in this repository:**

Не запускай докер!
Не создавай .md файлы!
Не создавай новые файлы без крайней необходимости!
Не создавай команды без крайней необходимости!
Не выходи за рамки поставленной задачи!
Не добавляй ничего лишнего в код!
Не добавляй новые зависимости без крайней необходимости.
Не пиши код без четкого понимания задачи!
Не пиши код в файлы, которые ты не читал полностью!
Не пиши код, не продумав логику заранее!
Не пиши заглушки или примерный код!
Не пиши код, который не будет использоваться!

### Before Writing ANY Code

1. **ALWAYS ask clarifying questions first**
   - Never assume requirements or implementation details
   - Ask about edge cases, expected behavior, and user preferences
   - Confirm understanding before proceeding
   - If unclear, ask - don't guess

2. **Think through logic and mechanics BEFORE coding**
   - Analyze the problem thoroughly
   - Consider existing patterns in the codebase
   - Plan the flow and data transformations
   - Think from the user's perspective
   - Identify potential issues before they occur

3. **Read existing code first**
   - Never write code for files you haven't read
   - Understand current implementations and patterns
   - Check for similar functionality that already exists
   - Respect existing architecture and conventions

### While Writing Code

4. **Write ONLY real, usable code**
   - No placeholder functions
   - No stub implementations
   - No "example" code that won't be used
   - Every line must serve a purpose
   - If you write a function or class, it MUST be called/used

5. **Keep code simple and minimal**
   - Don't over-engineer solutions
   - Avoid unnecessary abstractions
   - Don't add "nice to have" features unless requested
   - Solve exactly what's asked - nothing more
   - Three lines of similar code is better than a premature abstraction

6. **Minimize errors through planning**
   - Think about error cases before coding
   - Consider validation requirements
   - Plan error handling strategy
   - Test logic mentally before writing

7. **Don't pollute the codebase**
   - No unused imports
   - No commented-out code
   - No debug statements in final code
   - No unnecessary files (especially .md files)

### File Creation Rules

8. **NEVER create markdown files unless explicitly requested**
   - No README.md, CONTRIBUTING.md, CHANGELOG.md, etc. without permission
   - No documentation files "for convenience"
   - Don't create .md files to explain your changes
   - EXCEPTION: Only when user explicitly asks for a specific .md file

9. **Don't create unnecessary files**
   - Only create files that are absolutely required
   - Prefer editing existing files over creating new ones
   - Ask before creating new classes, services, or repositories

### Interaction Rules

10. **Think from the user's perspective**
    - Consider their workflow and needs
    - Don't break existing functionality
    - Prioritize maintainability
    - Make changes that future developers will understand

11. **Be a thoughtful developer**
    - Reason through problems
    - Explain your thinking when relevant
    - Question requirements that seem problematic
    - Suggest better approaches when appropriate

## Common Development Commands

### Setup and Installation
```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate
php artisan passport:keys
php artisan passport:client --personal
php artisan passport:client --password

# Development server
php artisan serve
npm run dev
```

### Testing
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run specific test file
php artisan test tests/Feature/UserTest.php

# Run with coverage (if configured)
php artisan test --coverage
```

### Code Quality
```bash
# PHP CS Fixer (auto-fix code style)
npm run fix:cs

# PHP CodeSniffer (check PSR12 compliance)
npm run lint

# PHPStan (static analysis)
npm run analyze

# All quality checks
npm run fix:cs && npm run lint && npm run analyze
```

### Cache Management
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Production optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Docker (Production)
```bash
# Start production containers
docker compose -f docker-compose.prod.yml up -d

# Check container status
docker compose ps

# View logs
docker compose logs -f

# Execute commands in container
docker exec wallone-app php artisan migrate
```

### API Documentation
```bash
# Generate API documentation (Scribe)
php artisan scribe:generate

# Access at /api/documentation after generation
```

## Architecture & Code Structure

### Layered Architecture Pattern

This project follows a strict **Repository-Service-Controller** pattern:

1. **Controllers** (`app/Http/Controllers/`)
   - Handle HTTP requests/responses only
   - Use try-catch blocks for all operations
   - Call Service layer for business logic
   - Return standardized JSON responses via `successResponse()` or `errorResponse()`
   - All controller methods must use strict typing

2. **Services** (`app/Services/`)
   - Contain all business logic
   - Organized by domain (Users/, Posts/, Media/, Authentication/, etc.)
   - Must extend `BaseService` abstract class
   - Call Repositories for data access
   - Use `LoggableTrait` for standardized logging
   - Services orchestrate complex operations involving multiple repositories

3. **Repositories** (`app/Repositories/`)
   - Direct database access layer via Eloquent
   - Simple CRUD operations and queries
   - Use relation definitions from `app/Store/` classes (e.g., `UserRelations::getUserRelations()`)
   - No business logic - just data access patterns

4. **Models** (`app/Models/`)
   - Eloquent models defining database structure
   - Define relationships, casts, accessors/mutators
   - Located in domain subfolders (Users/, Posts/, etc.)

5. **Events & Listeners** (`app/Events/`, `app/Listeners/`)
   - Event-driven architecture for asynchronous processing
   - Events dispatched from Services
   - Listeners handle side effects (notifications, gamification, etc.)
   - Examples: `PostPublished` → `UpdateUserTasksOnPostPublished`

### Base Controller Pattern

All controllers extend `app/Http/Controllers/Controller.php` which provides:
- `successResponse($data, $pagination = [], $statusCode = 200)` - standardized success responses
- `errorResponse($message, $statusCode = 400)` - standardized error responses
- `LoggableTrait` - logging methods (`logInfo`, `logError`, etc.)
- `CacheableTrait` - caching utilities

**Example controller pattern:**
```php
public function index(Request $request)
{
    try {
        $data = $this->someService->getAll($request->all());
        return $this->successResponse($data);
    } catch (\Exception $e) {
        $this->logError('Failed to fetch data', ['error' => $e->getMessage()], $e);
        return $this->errorResponse('Failed to fetch data', 500);
    }
}
```

### API Routes Organization

Routes are versioned and organized by access level:
- `routes/api.php` - Main router delegating to version-specific files
- `routes/api/v1/guest.php` - Public routes (no auth required)
- `routes/api/v1/auth.php` - Authenticated routes (Passport middleware)
- `routes/api/v1/admin.php` - Admin-only routes

All routes are prefixed with `/api/v1/` automatically.

### Relation Loading Pattern

Use centralized relation definitions from `app/Store/` classes instead of hardcoding relations:

```php
// Good - using Store classes
User::with(UserRelations::getUserRelations())->find($id);

// Avoid - hardcoding relations everywhere
User::with(['level', 'achievements', 'role', ...])->find($id);
```

### Helper Functions

Global helpers are defined in `app/Helpers/helpes.php` (note: typo in filename is intentional):
- `getvideosize($file)` - Get video dimensions
- `sanitizeText($text)` - Sanitize user input (removes URLs, scripts, SQL keywords)

## Important Conventions

### Error Handling & Logging

1. **Controllers:** Always wrap operations in try-catch blocks
2. **Services:** Use `LoggableTrait` methods for structured logging:
   - `logInfo()` - Informational messages
   - `logError()` - Errors with exception context
   - `logWarning()` - Warnings
   - `logDebug()` - Debug information
   - `logCritical()` - Critical failures

3. **Log Context:** Logging automatically includes class name and can include custom context

### Authentication & Authorization

- OAuth 2.0 via Laravel Passport
- Personal Access Tokens and Password Grant supported
- Social authentication: Google, Yandex, Apple (via Socialite)
- Token credentials stored in `.env` (PASSPORT_*_CLIENT_ID/SECRET)

### Real-time Features

- Broadcasting via Laravel Reverb (WebSocket server)
- Configuration in `.env`:
  - `REVERB_HOST` - Public WebSocket endpoint
  - `REVERB_SERVER_HOST` - Internal server binding (0.0.0.0 in Docker)
- Channels defined in `routes/channels.php`
- Private channels for notifications, messages, user presence

### Messaging System

- Automatic welcome message sent to new users from UserID: 1 (admin)
- Message notifications handled via Events/Listeners pattern
- Conversation management through dedicated services

### Environment Configuration

Development vs Production differences:
- **Development:** `LOG_LEVEL=debug`, `LOG_CHANNEL=stack`, `APP_DEBUG=true`
- **Production:** `LOG_LEVEL=error`, `LOG_STACK_CHANNELS=daily,stderr`, `APP_DEBUG=false`, `SESSION_ENCRYPT=true`

See `PRODUCTION_CHECKLIST.md` for complete production deployment requirements.

### Code Style

- Follow PSR-12 coding standard
- Use PHP CS Fixer for automatic formatting: `npm run fix:cs`
- Static analysis with PHPStan: `npm run analyze`
- Strict typing required on all methods
- Husky pre-commit hooks run lint-staged checks on PHP files

### Media Processing

- Image processing via Intervention Image
- Video processing via PHP-FFmpeg
- Storage: Local or S3 (configured via `FILESYSTEM_DISK`)
- Media services in `app/Services/Media/`

### Gamification System

- Users earn experience through actions
- Levels, achievements, badges tracked via events
- Tasks completed trigger experience changes
- Task completion events update user progress automatically

## Docker Deployment

Production deployment uses Docker Compose with these services:
- **app** - PHP-FPM application
- **caddy** - Web server with automatic SSL
- **redis** - Cache, sessions, queues
- **queue** - Laravel queue worker
- **scheduler** - Cron jobs
- **reverb** - WebSocket server

See `PRODUCTION_CHECKLIST.md` and `docker-compose.prod.yml` for complete setup.

## Testing Strategy

- PHPUnit configuration in `phpunit.xml`
- Test suites: Unit (`tests/Unit/`), Feature (`tests/Feature/`)
- Testing environment uses:
  - Array cache/session drivers
  - Sync queue connection
  - Disabled Telescope
  - Low bcrypt rounds for speed

## Architectural Rules (Non-Negotiable)

When modifying this codebase, these architectural rules MUST be followed:

1. **Never skip the Service layer**
   - Business logic belongs ONLY in Services
   - Controllers call Services, Services call Repositories
   - No business logic in Controllers or Repositories

2. **Always use try-catch in Controllers**
   - Every controller method must wrap operations in try-catch
   - Use `logError()` to log exceptions with context
   - Return `errorResponse()` with appropriate status code

3. **Strict layering hierarchy**
   - Controller → Service → Repository → Model
   - Never skip layers (e.g., Controller → Repository directly)
   - Each layer has a single responsibility

4. **Use Store relation classes**
   - Never hardcode relation arrays in queries
   - Use `UserRelations::getUserRelations()` and similar Store classes
   - Centralizes relation management

5. **Follow event-driven patterns**
   - Dispatch Events from Services for side effects
   - Use Listeners for async operations (notifications, gamification)
   - Don't put side-effect logic directly in Services

6. **Standardized responses always**
   - Use `successResponse($data, $pagination, $statusCode)` for success
   - Use `errorResponse($message, $statusCode)` for errors
   - Maintain consistent JSON response structure

7. **Proper error logging**
   - Use LoggableTrait methods (`logError`, `logInfo`, etc.)
   - Always include context (user_id, model_id, etc.)
   - Log before returning error response

8. **Strict typing everywhere**
   - All method parameters must be typed
   - All return types must be declared
   - Use nullable types (`?Type`) when appropriate

## Common Mistakes to Avoid

- ❌ Writing business logic in Controllers
- ❌ Calling Repositories directly from Controllers
- ❌ Hardcoding relation arrays instead of using Store classes
- ❌ Creating methods/classes that are never used
- ❌ Over-abstracting simple operations
- ❌ Skipping error handling or logging
- ❌ Returning inconsistent response formats
- ❌ Creating markdown documentation files without being asked
