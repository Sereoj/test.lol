# Code Search Agent

**When to use:** Before writing new code to check for existing implementations

## Actions

1. **Search Repositories**
   - Search in `app/Repositories/` for similar entities
   - Check if repository already exists
   - Look for similar method names

2. **Search Services**
   - Search in `app/Services/` for similar business logic
   - Check domain-specific service folders
   - Look for similar service methods

3. **Search Controllers**
   - Search in `app/Http/Controllers/` for similar endpoints
   - Check domain-specific controller folders
   - Look for similar route handlers

4. **Search Models**
   - Search in `app/Models/` for similar entities
   - Check domain-specific model folders
   - Look for similar model relationships

## Result

Report on existing code that can be reused or conflicts to avoid.

**Note:** Use cost-effective model for this agent.
