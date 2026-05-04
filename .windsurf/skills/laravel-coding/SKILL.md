---
name: laravel-coding
description: Laravel coding guidelines for AI programmer - project-specific patterns, architecture, and standards
---

# Laravel Coding Guidelines for AI Programmer

## Project Architecture

This Laravel project uses a layered architecture with clear separation of concerns:

### 1. Repository Pattern (Data Access Layer)
- **Location:** `app/Repositories/`
- **Purpose:** Database queries and data access logic
- **Naming:** `{Entity}Repository.php` (e.g., `UserRepository.php`)
- **Methods:** `create()`, `getAll()`, `findById()`, `update()`, `delete()`, `findBy*()`

### 2. Service Layer (Business Logic)
- **Location:** `app/Services/`
- **Purpose:** Business logic and orchestration
- **Base Class:** `BaseService` with abstract methods
- **Organization:** Grouped by domain (e.g., `Services/Users/`, `Services/Authentication/`)
- **Naming:** `{Entity}Service.php` or domain-specific names

### 3. Controllers (HTTP Layer)
- **Location:** `app/Http/Controllers/`
- **Purpose:** Handle HTTP requests, call services
- **Organization:** Grouped by domain (e.g., `Controllers/Users/`, `Controllers/Posts/`)
- **Naming:** `{Entity}Controller.php`

### 4. Models (Eloquent)
- **Location:** `app/Models/`
- **Purpose:** Eloquent models and relationships
- **Organization:** Grouped by domain (e.g., `Models/Users/`, `Models/Posts/`)
- **Naming:** `{Entity}.php`

### 5. Form Requests (Validation)
- **Location:** `app/Http/Requests/`
- **Purpose:** Request validation
- **Naming:** `{Action}{Entity}Request.php` (e.g., `StoreUserRequest.php`)

### 6. API Resources
- **Location:** `app/Http/Resources/`
- **Purpose:** API response formatting
- **Naming:** `{Entity}Resource.php`

## Main Functional Domains

### Authentication
- **Services:** `app/Services/Authentication/`
- **Controllers:** `app/Http/Controllers/Authentication/`
- **Models:** `app/Models/Authentication/`
- **Features:** Login, register, password reset, email verification, account recovery

### Users
- **Services:** `app/Services/Users/`
- **Controllers:** `app/Http/Controllers/Users/`
- **Models:** `app/Models/Users/`
- **Repository:** `app/Repositories/UserRepository.php`
- **Features:** User profiles, settings, skills, employment status, work experience

### Posts
- **Services:** `app/Services/Posts/`
- **Controllers:** `app/Http/Controllers/Posts/`
- **Models:** `app/Models/Posts/`
- **Repository:** `app/Repositories/PostRepository.php`
- **Features:** Create, update, delete, statistics, reports

### Comments
- **Services:** `app/Services/Comments/`
- **Controllers:** `app/Http/Controllers/CommentController.php`
- **Models:** `app/Models/Comments/`
- **Repository:** `app/Repositories/CommentRepository.php`
- **Features:** Comment CRUD, reactions, reports

### Challenges
- **Services:** `ChallengeService.php`
- **Controllers:** `app/Http/Controllers/ChallengeController.php`
- **Models:** `app/Models/Challenge.php`, `ChallengePrize.php`, `ChallengeVote.php`
- **Repository:** `app/Repositories/ChallengeRepository.php`
- **Features:** Challenge creation, participation, voting, winners selection

### Billing
- **Services:** `app/Services/Billing/`
- **Controllers:** `app/Http/Controllers/Billing/`
- **Models:** `app/Models/Billing/`
- **Repositories:** `app/Repositories/PurchaseRepository.php`, `TransactionRepository.php`
- **Features:** Purchases, subscriptions, transactions, media purchases

### Media
- **Services:** `app/Services/Media/`
- **Controllers:** `app/Http/Controllers/Media/`
- **Models:** `app/Models/Media/`
- **Repository:** `app/Repositories/MediaRepository.php`
- **Features:** Media upload, processing, purchase

## Coding Standards

### 1. Repository Pattern
Location: `app/Repositories/`
- Extend base repository patterns
- Implement standard CRUD methods
- Use Model queries with filters
- Return Collections or Models

### 2. Service Pattern
Location: `app/Services/`
- Extend `BaseService`
- Inject repositories in constructor
- Implement abstract methods: `getAll()`, `create()`, `getById()`, `update()`, `delete()`
- Orchestrate business logic

### 3. Controller Pattern
Location: `app/Http/Controllers/`
- Inject services in constructor
- Use Form Requests for validation
- Call service methods
- Return API Resources

### 4. Model Relations
Location: `app/Store/`
- Create relation classes (e.g., `UserRelations.php`)
- Define static methods returning relation arrays
- Use in repository `with()` calls

## Code Quality Rules

### 1. No Code Duplication
- Reuse existing services and repositories
- Check for similar functionality before creating new code
- Use inheritance and composition appropriately

### 2. Keep It Simple
- Avoid over-engineering
- Use Laravel's built-in features when possible
- Don't create unnecessary abstractions

### 3. Follow Existing Patterns
- Use the Repository → Service → Controller flow
- Follow naming conventions
- Use existing base classes

### 4. Validation
- Use Form Requests for validation
- Keep validation rules in `app/Http/Requests/`
- Return validation errors with proper HTTP status codes

### 5. API Responses
- Use API Resources for response formatting
- Keep resources in `app/Http/Resources/`
- Use proper HTTP status codes

### 6. Security First
- **SQL Injection Protection:** Always use Eloquent or parameterized queries, never raw SQL with user input
- **Transaction Safety:** Use database transactions for multi-step operations
- **Validation:** Verify the feature is genuinely useful before implementation
- **Impact Analysis:** Ensure changes won't break existing logic or introduce bugs
- **Code Reuse Check:** Verify similar code doesn't already exist before writing new code
- **Write Precisely:** Code exactly what's needed, no more, no less

## Security Guidelines

### SQL Query Safety
- Use Eloquent ORM for all database operations
- Use parameterized bindings if raw SQL is necessary
- Never concatenate user input into SQL queries
- Use Laravel's query builder for complex queries

### Transaction Management
- Use transactions for operations that modify multiple tables
- Wrap critical operations in `DB::transaction()`
- Handle exceptions and rollback on failure
- Consider isolation levels for concurrent operations

### Pre-Implementation Verification
1. **Verify Necessity:** Is this feature genuinely useful? Not a false requirement?
2. **Check Existing Code:** Search for similar functionality before writing new code
3. **Impact Analysis:** Will this break existing logic or introduce bugs?
4. **Security Review:** Does this introduce new vulnerabilities?
5. **Test Coverage:** How will this be tested?

## Git Workflow and Branching Strategy

### Branch Structure
- **main** - production branch, stable releases only
- **develop** - integration branch for features
- **feature/{name}** - feature branches from develop
- **fix/{name}** - bug fix branches from develop
- **hotfix/{name}** - urgent fixes from main

### Commit Message Format
Follow conventional commits:
- `feat:` - new feature
- `fix:` - bug fix
- `refactor:` - code refactoring
- `docs:` - documentation changes
- `style:` - formatting changes
- `test:` - adding tests
- `chore:` - maintenance tasks

### Commit Process
1. **Stage changes** - `git add` specific files
2. **Write message** - descriptive, concise, present tense
3. **Commit** - atomic changes, one logical unit per commit
4. **Push** - push to feature branch
5. **PR/MR** - create pull request for review

**Note:** This project uses Husky for git hooks. Commit messages are automatically validated via `.husky/commit-msg` hook.

### Pre-Code Writing Process

Before writing any code, follow this process:

1. **Understand the Requirement**
   - Read task description carefully
   - Ask clarifying questions if unclear
   - Verify requirement is not a false requirement

2. **Check Existing Code**
   - Search for similar functionality
   - Check repositories, services, controllers
   - Verify code can be reused

3. **Analyze Security Impact**
   - Check for SQL injection risks
   - Verify transaction safety needed
   - Review authentication/authorization

4. **Analyze System Impact**
   - Identify dependencies
   - Check for breaking changes
   - Assess performance impact

5. **Plan Architecture**
   - Determine component placement
   - Plan model relationships
   - Design API structure

6. **Create Branch**
   - Create feature branch from develop
   - Use descriptive branch name
   - Ensure clean starting point

7. **Write Code**
   - Follow Repository → Service → Controller pattern
   - Write tests alongside code
   - Commit atomic changes
   - **Search for solutions:** When stuck, search internet, Stack Overflow, Laravel documentation for best practices and solutions

8. **Test and Verify**
   - Run tests
   - Manual testing if needed
   - Check for regressions

9. **Create PR/MR**
   - Describe changes
   - Link to issue/task
   - Request review

## When Adding New Features

1. **Check for existing code** - Search repositories, services, controllers
2. **Create Model** - Add to appropriate domain folder in `app/Models/`
3. **Create Repository** - Add to `app/Repositories/`
4. **Create Service** - Extend `BaseService`, add to appropriate domain folder
5. **Create Controller** - Add to appropriate domain folder in `app/Http/Controllers/`
6. **Create Form Request** - Add to `app/Http/Requests/`
7. **Create API Resource** - Add to `app/Http/Resources/`
8. **Add Routes** - Update appropriate route file
9. **Write Tests** - Add to `tests/`
10. **Format Code** - Use Laravel Pint

## Mini-Agents for Coding Assistance

See supporting files in skill directory:
- `agents/code-search.md` - Code search agent (check for existing implementations)
- `agents/security-review.md` - Security review agent (SQL injection, transaction safety)
- `agents/impact-analysis.md` - Impact analysis agent (breaking changes, dependencies)
- `agents/architecture-advisor.md` - Architecture advisor agent (component placement, structure)
- `agents/necessity-checker.md` - Necessity checker agent (validate feature usefulness)

**Note:** All agents should use cost-effective models for efficiency.
