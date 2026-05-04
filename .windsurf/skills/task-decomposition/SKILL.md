---
name: task-decomposition
description: Task decomposition - breaking down projects into subtasks with complexity assessment and optimal implementation path selection
---

# Task Decomposition

## 1. Receive Task from User

- Carefully read the task from the user
- Determine task type: superficial or detailed
- Document the original task formulation

## 2. Analysis and Questions

### Phase 1: General Questions (if essence is unclear)
- What is the main goal of the task?
- Who will use the result?
- What constraints exist (time, resources, technologies)?
- Are there examples or references?

### Phase 2: Specification (after understanding essence)
- What specific functions are needed?
- How should this integrate with current code?
- What data is used/modified?
- What edge cases need to be considered?
- **Check existing code:** Search for similar functions/mechanics in the project. If already implemented, reuse them instead of writing new code.
- **Project structure clarification:** Ask where specific components should be placed (directories, files, configuration)
- **Environment variables:** Do NOT immediately suggest creating .env variables. Clarify if they are needed and where they should be defined
- **Route design:** Do NOT create excessive routes. Consider if existing routes can handle the requirement
- **API considerations:** For REST API, assess potential timeout issues and performance impact
- **Research when needed:** If clarification requires up-to-date information, search the web for authoritative sources
- **Research strategy:** Prefer thematic forums, official documentation, and specialized resources over popular articles

**Important:** Do not invent new features without user permission. Analyze only the current project structure.

## 3. Information Gathering

- Study screenshots, examples, documentation
- Analyze current project structure
- Check existing code for similar solutions
- Follow code writing and testing standards in the project

## 4. Break Down into Subtasks

Create a task tree to the final point:
- Divide task into logical blocks
- Each subtask should be atomic and testable
- Specify dependencies between subtasks
- Assess complexity of each subtask

## 5. Save Task Context

Remember the task in a structured form:
- Use todo_list to track subtasks
- Create visual representation (task tree)
- Document key decisions and constraints
- Save link to commit or branch for context return

## 6. Approach Assessment

### If project is in MVP stage:
- Propose a quick and simple solution
- Indicate limitations of current approach
- Explain what can be done safer/better in the future
- Get user confirmation

### If mature project:
- Propose architecturally correct solution
- Consider existing patterns and standards
- Ensure backward compatibility if needed
- Add comprehensive tests

## 7. Task Lifecycle

### Planning
- Document requirements
- Agree on approach with user
- Create implementation plan

### Implementation
- Execute subtasks in order
- Follow project code style
- Write tests in parallel with code
- Commit with atomic changes

### Verification
- Run tests
- Check edge cases
- Ensure compliance with requirements
- Conduct code review if needed

### Acceptance
- Demonstrate result to user
- Get final confirmation
- Document changes
- Close task

## 8. Complexity Assessment Criteria

**Easy task:**
- Change in one file
- No complex logic
- Does not affect architecture
- Can be implemented in 1-2 hours

**Medium task:**
- Changes in 2-5 files
- Small business logic
- Requires understanding of several modules
- Can be implemented in 4-8 hours

**Complex task:**
- Multiple changes across project
- Complex business logic or algorithms
- Affects architecture
- Requires deep analysis
- Requires detailed planning and testing

## 9. Principles

- **Minimal changes:** Prefer minimal upstream fixes over downstream workarounds
- **Security:** Do not make breaking changes without confirmation
- **Testability:** Each subtask should be verifiable
- **Transparency:** Keep user informed of progress
- **Flexibility:** Be ready to adjust plan with new information

## 10. Mini-Agents for Decomposition Scenarios

See supporting files in skill directory:
- `agents/code-analysis.md` - Code analysis agent
- `agents/task-breakdown.md` - Task breakdown agent
- `agents/risk-assessment.md` - Risk assessment agent
- `agents/approach-selection.md` - Approach selection agent
- `agents/verification.md` - Solution verification agent
- `agents/documentation.md` - Documentation agent

## Agent Scripts

Quick executable scripts for fast analysis:
- `agents/code-analysis.ps1` - Fast code search (console output only)
- `agents/task-breakdown.ps1` - Create task breakdown template
- `agents/risk-assessment.ps1` - Automatic risk check
- `agents/verification.ps1` - Basic verification checks

**Output Files (single files, overwritten on each run):**
- `outputs/breakdown.md` - Task breakdown report
- `outputs/risk-assessment.md` - Risk assessment report
- `outputs/verification.md` - Verification report

**Note:** All agents should use cost-effective models for efficiency.
