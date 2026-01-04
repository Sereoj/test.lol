This file provides guidance to Claude Code (claude.ai/code) when working in this repository.

Use the information below to understand the project structure, coding standards, architectural rules, and development principles.
All rules in this document are **mandatory** unless explicitly overridden by the user.

---

## Global Restrictions (Strict)

* Do not create or modify markdown files unless explicitly instructed
* Do not create new files unless absolutely necessary
* Do not add unnecessary code, commands, dependencies, or abstractions
* Do not deviate from established architecture and development principles
* Do not make assumptions — ask clarifying questions first
* Do not write code in files you have not fully read
* Do not write placeholder, example, stub, or unused code
* Do not write code without fully understanding the task
* Do not write code without planning the logic first
* Do not write code that will not be used
* Do not run Docker
* Do not modify production environment settings
* Do not suggest changes to this document unless instructed
* Do not suggest adding new tools, libraries, or frameworks unless explicitly requested
* Do not suggest architectural changes unless explicitly requested
* Do not remove database migrations or seeders, and base your code on their existence
* Do not suggest changes that violate established coding standards or architectural rules

---

## PHP Environment (Windows)

* PHP path: `e/Programs/OpenServer/modules/php/PHP_8.2/php.exe`

---

## Git Workflow

**After completing each logical unit of work, ALWAYS make a commit**

* Commit after features, refactoring, or bug fixes
* Commit messages must be clear and written in Russian
* No detailed analysis in commits

Example:

```
git commit -m "Добавлен Stylelint в проект"
```

---

## Project Overview

**Wallone (wallone.app)** — Laravel-based social platform for artists.

### Features

* User profiles and portfolios
* Social interactions (follows, likes, comments)
* Payments and monetization
* Gamification system
* Real-time messaging and notifications

### Tech Stack

* Laravel 10, PHP 8.2+
* MySQL
* Laravel Passport (OAuth)
* Laravel Reverb (WebSockets)
* Redis (cache, queues, sessions)
* Docker (production only)

---

## Core Development Principles (Non-Negotiable)

### Before Writing Any Code

1. Clarify requirements

   * Never assume behavior or edge cases
   * Ask questions if anything is unclear
   * Confirm understanding before coding

2. Plan first

   * Think through logic, data flow, and error cases
   * Consider existing patterns and architecture
   * Do not improvise during implementation

3. Read existing code

   * Fully read files before modifying them
   * Look for similar existing solutions
   * Respect current structure and conventions

---

### While Writing Code

4. Write only real, production code

   * No placeholders, examples, or stubs
   * Every method, class, and line must be used

5. Keep it minimal

   * No overengineering
   * No “nice-to-have” features unless requested
   * Solve exactly the stated problem

6. Prevent errors

   * Plan validation and error handling in advance
   * Mentally test logic before writing code

7. Keep the codebase clean

   * No unused imports
   * No commented-out code
   * No debug output
   * No unnecessary files

---

## Architecture & Code Structure

### Mandatory Layered Architecture

Controller → Service → Repository → Model

Skipping layers is strictly forbidden.

---

### Controllers (`app/Http/Controllers`)

* Handle HTTP only
* Always wrap logic in try-catch
* Call Services only
* Use `successResponse()` / `errorResponse()`
* Strict typing required

---

### Services (`app/Services`)

* Contain all business logic
* Must extend `BaseService`
* Use `LoggableTrait`
* Dispatch events for side effects
* Coordinate multiple repositories

---

### Repositories (`app/Repositories`)

* Data access only
* Eloquent queries and simple CRUD
* No business logic
* Use Store relation classes

---

### Models (`app/Models`)

* Eloquent structure
* Relations, casts, accessors
* Organized by domain

---

### Events & Listeners

* Events dispatched from Services
* Listeners handle side effects
* Used for notifications, gamification, async work

---

## Relations Pattern (Mandatory)

Use Store classes for relations:

```
User::with(UserRelations::getUserRelations())->find($id);
```

Hardcoded relation arrays are forbidden.

---

## Error Handling & Logging

* Controllers must use try-catch
* Log errors before returning responses
* Use `LoggableTrait` methods:

  * logInfo
  * logWarning
  * logError
  * logDebug
  * logCritical

Always include relevant context (user_id, entity_id, etc.).

---

## Authentication & Realtime

* OAuth2 via Laravel Passport
* Social auth: Google, Yandex, Apple
* WebSockets via Laravel Reverb
* Channels defined in `routes/channels.php`

---

## Messaging System

* Welcome message from User ID = 1
* Notifications via Events & Listeners
* Messaging logic handled in Services

---

## Code Style & Quality

* PSR-12
* Strict typing everywhere
* PHP CS Fixer, PHPStan, CodeSniffer
* Husky pre-commit hooks enabled

---

## Media Processing

* Images: Intervention Image
* Video: PHP-FFmpeg
* Storage: Local or S3
* Services located in `app/Services/Media`

---

## Testing

* PHPUnit
* Unit and Feature test suites
* Simplified testing environment
* Sync queues, array cache/session

---

## Common Mistakes (Avoid)

* Business logic in Controllers
* Repository calls from Controllers
* Skipping Service layer
* Hardcoded relations
* Unused classes or methods
* Overengineering
* Missing logging or error handling
* Creating markdown files without permission
* Adding unnecessary dependencies or abstractions
