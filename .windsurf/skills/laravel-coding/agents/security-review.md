# Security Review Agent

**When to use:** Before implementing code changes

## Actions

1. **SQL Injection Check**
   - Verify Eloquent is used instead of raw SQL
   - Check for user input concatenation
   - Verify parameterized bindings if raw SQL is used

2. **Transaction Safety Check**
   - Identify operations modifying multiple tables
   - Check if `DB::transaction()` is used
   - Verify exception handling and rollback

3. **Authentication/Authorization Check**
   - Check if routes have proper middleware
   - Verify user permissions are checked
   - Ensure sensitive data is protected

4. **Input Validation Check**
   - Verify Form Requests are used
   - Check validation rules are comprehensive
   - Ensure sanitization of user input

## Result

Security report with vulnerabilities and recommendations.

**Note:** Use cost-effective model for this agent.
