# Task Breakdown Agent

**When to use:** After understanding task and gathering information

## Quick Script

Run `task-breakdown.ps1 -TaskName "Task Description"` to create breakdown template:
```powershell
.\agents\task-breakdown.ps1 -TaskName "Implement user authentication"
```

## Actions

1. **Logical Block Identification**
   - Divide task into independent components
   - Define boundaries between blocks
   - Ensure each block has clear purpose

2. **Dependency Determination**
   - Which blocks depend on others?
   - Can some blocks be executed in parallel?
   - Create dependency graph

3. **todo_list Creation**
   - Use `todo_list` to create subtasks
   - Set priorities (high/medium/low)
   - Add statuses (pending/in_progress/completed)

4. **Complexity Assessment**
   - Assess each subtask (easy/medium/complex)
   - Consider execution time
   - Consider risks and uncertainties

## Result

Structured plan with priorities, dependencies, and complexity assessment.

**Note:** Use cost-effective model for this agent.
