# Impact Analysis Agent

**When to use:** Before implementing code changes

## Actions

1. **Dependency Analysis**
   - Identify which services/controllers use affected code
   - Check for cascading dependencies
   - Map data flow through the system

2. **Breaking Change Check**
   - Identify if API contracts will change
   - Check if database schema changes are needed
   - Verify backward compatibility

3. **Regression Risk Assessment**
   - Identify existing tests that may fail
   - Check for hardcoded references
   - Assess configuration file impacts

4. **Performance Impact**
   - Estimate query performance changes
   - Check for N+1 query issues
   - Assess memory usage impact

## Result

Impact report with risks and mitigation strategies.

**Note:** Use cost-effective model for this agent.
