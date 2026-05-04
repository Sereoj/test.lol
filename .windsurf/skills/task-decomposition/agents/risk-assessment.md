# Risk Assessment Agent

**When to use:** Before implementation starts

## Quick Script

Run `risk-assessment.ps1` for automatic risk checks:
```powershell
.\agents\risk-assessment.ps1
```

## Actions

1. **Affected Files Analysis**
   - Which files will be changed?
   - Are there critical files?
   - Can changes be rolled back?

2. **Backward Compatibility Check**
   - Will solution change existing API?
   - Are migrations needed?
   - How will this affect existing code?

3. **Impact Assessment**
   - Which functions may be affected?
   - Are there dependencies on changed code?
   - Do other developers need to be notified?

4. **Failure Point Identification**
   - Where can things go wrong?
   - How to handle errors?
   - Is there a rollback plan?

## Result

Risk report with mitigation recommendations and rollback plan.

**Note:** Use cost-effective model for this agent.
