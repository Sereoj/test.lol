# Code Analysis Agent

**When to use:** During task specification to find reusable solutions

## Quick Script

Run `code-analysis.ps1 -SearchPattern "pattern"` for fast code search:
```powershell
.\agents\code-analysis.ps1 -SearchPattern "functionName"
```

**Search types:**
- `pattern` (default) - general pattern search
- `function` - search for specific function
- `class` - search for specific class
- `todo` - search for TODO/FIXME/XXX/HACK comments
- `logs` - search in log files (storage/logs/, .windsurf/hooks/)

**Examples:**
```powershell
.\agents\code-analysis.ps1 -SearchPattern "login" -SearchType "function"
.\agents\code-analysis.ps1 -SearchPattern "User" -SearchType "class"
.\agents\code-analysis.ps1 -SearchPattern "fix" -SearchType "todo"
.\agents\code-analysis.ps1 -SearchPattern "error" -SearchType "logs"
```

## Actions

1. **Code Search**
   - Use `Grep` to search for similar functions, classes, patterns
   - Search by keywords related to the task
   - Check different naming variations

2. **Structure Analysis**
   - Use `find_by_name` to find related files
   - Study project structure to understand code organization

3. **Implementation Reading**
   - Use `read_file` to study found solutions
   - Analyze logic and patterns
   - Check tests to understand expected behavior

4. **Applicability Assessment**
   - Does existing solution fit current task?
   - Does code need adaptation?
   - Are there dependencies to consider?

## Result

List of existing solutions that can be reused with applicability assessment.

**Note:** Use cost-effective model for this agent.
