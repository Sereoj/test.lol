# Solution Verification Agent

**When to use:** During verification phase before acceptance

## Quick Script

Run `verification.ps1` for basic checks:
```powershell
.\agents\verification.ps1
```

## Actions

1. **Test Execution**
   - Unit tests
   - Integration tests
   - E2E tests if applicable

2. **Edge Case Checking**
   - Boundary values
   - Null values
   - Erroneous inputs
   - Concurrent access

3. **Manual Testing**
   - User scenario testing
   - UI check if applicable
   - Performance check

4. **Requirements Comparison**
   - Are all requirements met?
   - Does result match expectations?
   - Are there deviations from plan?

## Result

Compliance report, list of found issues, and fix recommendations.

**Note:** Use cost-effective model for this agent.
