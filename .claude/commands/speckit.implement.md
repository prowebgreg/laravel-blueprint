---
description: Execute the implementation plan by processing and executing all tasks defined in tasks.md
---

## User Input

```text
$ARGUMENTS
```

You **MUST** consider the user input before proceeding (if not empty).

## Execution Mode

**Default: Single-Task Mode**

This command implements **ONE task at a time** by default.

- **If user specifies a task ID** (e.g., `T005`): Implement ONLY that specific task
- **If user specifies multiple tasks** (e.g., `T005-T008` or `T005, T006, T007`): Implement those tasks sequentially
- **If no task specified**: Show the current task list and ask which task to implement

**Examples**:
- `/speckit.implement T005` → Implements only T005
- `/speckit.implement T005-T010` → Implements T005 through T010 sequentially
- `/speckit.implement T005, T007, T012` → Implements those specific tasks
- `/speckit.implement` → Shows task list, asks user which to implement

## Outline

1. **Parse task specification from $ARGUMENTS**:
   - Extract task ID(s) from user input (e.g., `T005`, `T005-T010`, `T005, T006`)
   - If no task specified, show incomplete tasks from tasks.md and ask which task to implement
   - **IMPORTANT**: If only ONE task ID is specified, implement ONLY that task. Do not continue to other tasks.

2. **Prerequisites**: Run `.specify/scripts/bash/check-prerequisites.sh --json --require-tasks --include-tasks` from repo root and parse FEATURE_DIR and AVAILABLE_DOCS list. All paths must be absolute. For single quotes in args like "I'm Groot", use escape syntax: e.g 'I'\''m Groot' (or double-quote if possible: "I'm Groot").

3. **Check checklists status** (if FEATURE_DIR/checklists/ exists):
   - Scan all checklist files in the checklists/ directory
   - For each checklist, count:
     - Total items: All lines matching `- [ ]` or `- [X]` or `- [x]`
     - Completed items: Lines matching `- [X]` or `- [x]`
     - Incomplete items: Lines matching `- [ ]`
   - Create a status table:

     ```text
     | Checklist | Total | Completed | Incomplete | Status |
     |-----------|-------|-----------|------------|--------|
     | ux.md     | 12    | 12        | 0          | ✓ PASS |
     | test.md   | 8     | 5         | 3          | ✗ FAIL |
     | security.md | 6   | 6         | 0          | ✓ PASS |
     ```

   - **If any checklist is incomplete**: Ask user if they want to proceed
   - **If all checklists complete**: Proceed automatically

4. **Load implementation context**:
   - **REQUIRED**: Read tasks.md for task list and execution plan
   - **REQUIRED**: Read plan.md for tech stack, architecture, and file structure
   - **IF EXISTS**: Read data-model.md, contracts/, research.md, quickstart.md

5. **Locate the specified task(s)**:
   - Find the task(s) matching the ID(s) from step 1
   - Extract task details: description, file path, **subagent assignment** (`→ @subagent-name`)
   - If task not found, show error and list available incomplete tasks
   - **CRITICAL**: Each task MUST have a subagent assignment (format: `→ @subagent-name`)

6. **Execute task using assigned subagent(s)** (MANDATORY):

   For EACH task to implement:

   a. **Extract subagent(s) from task**: Parse the `→ @subagent-name` suffix
      - Single agent: `→ @laravel-specialist` → execute with one agent
      - Multiple agents: `→ @laravel-specialist, @postgres-pro` → execute sequentially
      - Order matters: First agent is primary, subsequent agents refine/enhance

   b. **For SINGLE agent tasks**:
      ```
      Task tool:
        subagent_type: [extracted-subagent-name]
        description: "Implement T00X: [task description]"
        prompt: |
          You are implementing task T00X from tasks.md

          ## Task
          [Full task line from tasks.md]

          ## Context
          Read these Speckit artifacts for context:
          - FEATURE_DIR/plan.md (tech stack, architecture)
          - FEATURE_DIR/spec.md (user stories, requirements)
          - FEATURE_DIR/data-model.md (entities, if relevant)
          - FEATURE_DIR/contracts/ (API specs, if relevant)

          ## Your job
          1. Implement exactly what the task specifies
          2. Follow the file path indicated in the task
          3. Write tests if task is in a test phase
          4. Verify implementation works
          5. Report back with summary

          Report back:
          - What you implemented
          - Files changed/created
          - Tests written and results (if applicable)
          - Any issues or blockers
      ```

   c. **For MULTIPLE agent tasks** (sequential execution):
      ```
      For each agent in order:
        1. First agent (@laravel-specialist): Primary implementation
           - Implement the core functionality
           - Report what was done

        2. Subsequent agents (@postgres-pro, etc.): Enhance/refine
           - Review what previous agent(s) implemented
           - Apply their expertise to improve/optimize
           - Report enhancements made

      Example for: → @laravel-specialist, @postgres-pro
        Step 1: @laravel-specialist implements the feature
        Step 2: @postgres-pro reviews and optimizes database queries/schema
      ```

   d. **After all subagent(s) complete**: Dispatch `code-reviewer` subagent to review the work

   e. **Handle review feedback**:
      - If Critical issues: Dispatch appropriate fix subagent, then re-review
      - If Important issues: Fix before marking complete
      - If Minor issues only: Note for later, proceed

   f. **Mark task complete**: Update tasks.md to change `- [ ]` to `- [X]`

7. **Single-task completion**:
   - After completing the specified task(s), STOP
   - Do NOT automatically proceed to next tasks
   - Report completion summary:
     - Task(s) completed
     - Files created/modified
     - Review results
     - Suggested next task (for user's reference only)

8. **Error handling**:
   - If subagent fails: Report error with context, do not mark task complete
   - If task is blocked: Mark with `[BLOCKED: reason]` and report
   - If subagent not found: Verify against `docs/subagents/claude-subagents.md` - task may need regeneration with `/speckit.tasks`

## Subagent Execution Rules

**MANDATORY**: Tasks MUST be executed using the assigned subagent(s). Do NOT implement tasks directly.

**Why subagents are required**:
- Fresh context per task (no pollution from previous work)
- Specialized expertise for each task type
- Code review gates between tasks
- Clear accountability and traceability

**Single-agent flow**:
```
1. Implementation subagent (→ @subagent-name)
   ↓
2. Code review subagent (@code-reviewer)
   ↓
3. Fix subagent (if issues found)
   ↓
4. Mark task [X] complete
```

**Multi-agent flow** (for tasks with multiple agents):
```
1. First agent: Primary implementation (→ @laravel-specialist)
   ↓
2. Second agent: Enhance/optimize (→ @postgres-pro)
   ↓
3. [Additional agents if assigned...]
   ↓
4. Code review subagent (@code-reviewer)
   ↓
5. Fix subagent (if issues found)
   ↓
6. Mark task [X] complete
```

**Multi-agent execution rules**:
- Execute agents in the order they appear in the task
- Each agent builds on the previous agent's work
- Pass context between agents (what was implemented, files changed)
- All agents complete before code review

**When subagent is NOT required** (rare exceptions):
- Trivial file moves or renames
- Comment-only changes
- Configuration value changes (no logic)

For all other tasks, subagent execution is **MANDATORY**.

## Notes

- This command implements ONE task per invocation by default
- If no task ID provided, it shows the task list and asks which to implement
- Subagent assignments come from tasks.md (generated by `/speckit.tasks`)
- If tasks.md missing subagent assignments, run `/speckit.tasks` to regenerate
- See `docs/subagents/claude-subagents.md` for the complete subagent reference
