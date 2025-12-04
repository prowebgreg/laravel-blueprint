---
description: Generate an actionable, dependency-ordered tasks.md for the feature based on available design artifacts.
handoffs:
  - label: Analyze For Consistency
    agent: speckit.analyze
    prompt: Run a project analysis for consistency
    send: true
  - label: Implement Project
    agent: speckit.implement
    prompt: Start the implementation in phases
    send: true
---

## User Input

```text
$ARGUMENTS
```

You **MUST** consider the user input before proceeding (if not empty).

## Outline

1. **Setup**: Run `.specify/scripts/bash/check-prerequisites.sh --json` from repo root and parse FEATURE_DIR and AVAILABLE_DOCS list. All paths must be absolute. For single quotes in args like "I'm Groot", use escape syntax: e.g 'I'\''m Groot' (or double-quote if possible: "I'm Groot").

2. **Load design documents**: Read from FEATURE_DIR:
   - **Required**: plan.md (tech stack, libraries, structure), spec.md (user stories with priorities)
   - **Optional**: data-model.md (entities), contracts/ (API endpoints), research.md (decisions), quickstart.md (test scenarios)
   - Note: Not all projects have all documents. Generate tasks based on what's available.

3. **Load subagent reference**: Read `docs/subagents/claude-subagents.md` for the complete list of available subagents with their descriptions and use cases. This is the authoritative reference for subagent selection in step 4.

4. **Execute task generation workflow**:
   - Load plan.md and extract tech stack, libraries, project structure
   - Load spec.md and extract user stories with their priorities (P1, P2, P3, etc.)
   - If data-model.md exists: Extract entities and map to user stories
   - If contracts/ exists: Map endpoints to user stories
   - If research.md exists: Extract decisions for setup tasks
   - Generate tasks organized by user story (see Task Generation Rules below)
   - **Assign subagent to each task** (see Subagent Assignment Rules below)
   - Generate dependency graph showing user story completion order
   - Create parallel execution examples per user story
   - Validate task completeness (each user story has all needed tasks, independently testable)

5. **Generate tasks.md**: Use `.specify/templates/tasks-template.md` as structure, fill with:
   - Correct feature name from plan.md
   - Phase 1: Setup tasks (project initialization)
   - Phase 2: Foundational tasks (blocking prerequisites for all user stories)
   - Phase 3+: One phase per user story (in priority order from spec.md)
   - Each phase includes: story goal, independent test criteria, tests (if requested), implementation tasks
   - Final Phase: Polish & cross-cutting concerns
   - **Each task MUST include subagent assignment** (see format below)
   - All tasks must follow the strict checklist format (see Task Generation Rules below)
   - Clear file paths for each task
   - Dependencies section showing story completion order
   - Parallel execution examples per story
   - Implementation strategy section (MVP first, incremental delivery)

6. **Report**: Output path to generated tasks.md and summary:
   - Total task count
   - Task count per user story
   - Parallel opportunities identified
   - Independent test criteria for each story
   - Suggested MVP scope (typically just User Story 1)
   - Format validation: Confirm ALL tasks follow the checklist format (checkbox, ID, labels, file paths)
   - **Subagent assignment summary**: List unique subagents used and task counts per subagent

Context for task generation: $ARGUMENTS

The tasks.md should be immediately executable - each task must be specific enough that an LLM can complete it without additional context.

## Task Generation Rules

**CRITICAL**: Tasks MUST be organized by user story to enable independent implementation and testing.

**Tests are OPTIONAL**: Only generate test tasks if explicitly requested in the feature specification or if user requests TDD approach.

### Checklist Format (REQUIRED)

Every task MUST strictly follow this format:

```text
- [ ] [TaskID] [P?] [Story?] Description with file path → @subagent-name [, @subagent-name...]
```

**Format Components**:

1. **Checkbox**: ALWAYS start with `- [ ]` (markdown checkbox)
2. **Task ID**: Sequential number (T001, T002, T003...) in execution order
3. **[P] marker**: Include ONLY if task is parallelizable (different files, no dependencies on incomplete tasks)
4. **[Story] label**: REQUIRED for user story phase tasks only
   - Format: [US1], [US2], [US3], etc. (maps to user stories from spec.md)
   - Setup phase: NO story label
   - Foundational phase: NO story label
   - User Story phases: MUST have story label
   - Polish phase: NO story label
5. **Description**: Clear action with exact file path
6. **Subagent assignment**: `→ @subagent-name` at the end of each task (**MANDATORY**)
   - Single agent: `→ @laravel-specialist`
   - Multiple agents: `→ @laravel-specialist, @postgres-pro` (comma-separated)
   - Order matters: First agent is primary, others collaborate in sequence
   - Use multiple agents when task requires different expertise areas

**Examples**:

- ✅ CORRECT: `- [ ] T001 Create project structure per implementation plan → @devops-engineer`
- ✅ CORRECT: `- [ ] T005 [P] Implement authentication middleware in app/Http/Middleware/Auth.php → @laravel-specialist`
- ✅ CORRECT: `- [ ] T012 [P] [US1] Create User model with complex JSONB queries → @laravel-specialist, @postgres-pro`
- ✅ CORRECT: `- [ ] T014 [US1] Implement payment service with Stripe integration → @laravel-specialist, @payment-integration`
- ✅ CORRECT: `- [ ] T020 [US2] Build API endpoint with OpenAPI docs → @api-designer, @laravel-specialist, @api-documenter`
- ❌ WRONG: `- [ ] T001 Create project structure` (missing subagent assignment)
- ❌ WRONG: `- [ ] Create User model` (missing ID, Story label, and subagent)
- ❌ WRONG: `T001 [US1] Create model → @backend-developer` (missing checkbox)
- ❌ WRONG: `- [ ] T001 [US1] Create model → @backend-developer` (missing file path)

### Task Organization

1. **From User Stories (spec.md)** - PRIMARY ORGANIZATION:
   - Each user story (P1, P2, P3...) gets its own phase
   - Map all related components to their story:
     - Models needed for that story
     - Services needed for that story
     - Endpoints/UI needed for that story
     - If tests requested: Tests specific to that story
   - Mark story dependencies (most stories should be independent)

2. **From Contracts**:
   - Map each contract/endpoint → to the user story it serves
   - If tests requested: Each contract → contract test task [P] before implementation in that story's phase

3. **From Data Model**:
   - Map each entity to the user story(ies) that need it
   - If entity serves multiple stories: Put in earliest story or Setup phase
   - Relationships → service layer tasks in appropriate story phase

4. **From Setup/Infrastructure**:
   - Shared infrastructure → Setup phase (Phase 1)
   - Foundational/blocking tasks → Foundational phase (Phase 2)
   - Story-specific setup → within that story's phase

### Phase Structure

- **Phase 1**: Setup (project initialization)
- **Phase 2**: Foundational (blocking prerequisites - MUST complete before user stories)
- **Phase 3+**: User Stories in priority order (P1, P2, P3...)
  - Within each story: Tests (if requested) → Models → Services → Endpoints → Integration
  - Each phase should be a complete, independently testable increment
- **Final Phase**: Polish & Cross-Cutting Concerns

## Subagent Assignment Rules

**MANDATORY**: Every task MUST have a subagent assigned. The subagent is responsible for implementing the task.

### How to Select Subagents

1. **Read the subagent reference**: Consult `docs/subagents/claude-subagents.md` for the complete list of available subagents organized by category
2. **Match tech stack from plan.md**: Use the "Common Technology Stacks" and project-specific sections in the reference
3. **Match task to expertise**: Select the best-fit agent based on:
   - Tech stack from plan.md (e.g., Laravel → `laravel-specialist`, PHP → `php-pro`)
   - Task domain (e.g., database work → `postgres-pro` or `sql-pro`)
   - Task type (e.g., API design → `api-designer`, frontend → `frontend-developer`)

### Subagent Selection Priority

When multiple agents could fit a task, use this priority order:

1. **Framework-specific specialist** (highest priority)
   - Laravel tasks → `@laravel-specialist`
   - Next.js tasks → `@nextjs-developer`
   - React tasks → `@react-specialist`

2. **Language-specific pro**
   - PHP code → `@php-pro`
   - Python code → `@python-pro`
   - TypeScript code → `@typescript-pro`
   - JavaScript code → `@javascript-pro`
   - SQL queries → `@sql-pro`

3. **Domain specialist**
   - Database schema/models → `@postgres-pro`, `@database-administrator`
   - API endpoints → `@api-designer`, `@backend-developer`
   - Frontend UI → `@frontend-developer`, `@ui-designer`
   - DevOps/setup → `@devops-engineer`
   - Security → `@security-engineer`, `@security-auditor`
   - Performance → `@performance-engineer`
   - Testing → `@test-automator`, `@qa-expert`
   - Documentation → `@documentation-engineer`, `@technical-writer`
   - AI/LLM features → `@ai-engineer`, `@llm-architect`, `@prompt-engineer`
   - Payments → `@payment-integration`
   - GraphQL → `@graphql-architect`

4. **Generalist fallback** (if no specialist fits)
   - Full-stack work → `@fullstack-developer`
   - Backend work → `@backend-developer`
   - Code quality → `@refactoring-specialist`

### Special Task Types

| Task Type | Recommended Subagent(s) |
|-----------|------------------------|
| Project setup/config | `@devops-engineer` |
| Database migrations | `@laravel-specialist, @postgres-pro` |
| API contracts/OpenAPI | `@api-designer, @api-documenter` |
| Code review tasks | `@code-reviewer` |
| Refactoring tasks | `@refactoring-specialist` |
| Performance optimization | `@performance-engineer, @database-optimizer` |
| Security hardening | `@security-engineer, @penetration-tester` |
| Debugging/fixes | `@debugger` |
| Research tasks | `@research-analyst` |

### When to Use Multiple Agents

Assign multiple agents when a task spans different expertise areas:

| Task Characteristic | Agent Combination |
|--------------------|-------------------|
| Laravel + Complex SQL/JSONB | `@laravel-specialist, @postgres-pro` |
| API design + Implementation | `@api-designer, @laravel-specialist` |
| Feature + Documentation | `@laravel-specialist, @api-documenter` |
| Implementation + Security review | `@laravel-specialist, @security-auditor` |
| Database + Performance tuning | `@postgres-pro, @database-optimizer` |
| UI + Accessibility | `@frontend-developer, @accessibility-tester` |
| AI feature + Prompt design | `@ai-engineer, @prompt-engineer` |
| Payment + Security | `@payment-integration, @security-engineer` |

**Execution order**: Agents execute sequentially in the order listed. First agent does primary implementation, subsequent agents refine/enhance/review.

**Single agent is fine** when:
- Task is straightforward within one domain
- No cross-cutting concerns
- Simple CRUD operations

### Validation

Before finalizing tasks.md, verify:
- [ ] Every task has `→ @subagent-name` suffix
- [ ] Every assigned subagent exists in `docs/subagents/claude-subagents.md`
- [ ] Subagent selection matches task domain and tech stack

### Reference

See `docs/subagents/claude-subagents.md` for:
- Complete list of all available subagents
- Subagent categories and descriptions
- Common technology stack combinations
- Project-specific recommendations
