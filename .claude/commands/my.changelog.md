---
description: Update CHANGELOG.md with a summary of the completed specification
---

## User Input

```text
$ARGUMENTS
```

The user input above is the spec directory name (e.g., `001-phase-0-foundation`).

## Instructions

1. **Validate input**: If `$ARGUMENTS` is empty, ask the user to provide the spec directory name.

2. **Read the completed spec files:**
   - `/specs/$ARGUMENTS/spec.md` - What was specified (user stories, requirements)
   - `/specs/$ARGUMENTS/plan.md` - How it was implemented (technical details)
   - `/specs/$ARGUMENTS/tasks.md` - What tasks were completed

3. **Determine the changelog categories** based on spec content:
   - `Added` - New features, capabilities, endpoints
   - `Changed` - Modifications to existing functionality
   - `Fixed` - Bug fixes
   - `Technical` - Infrastructure, tooling, refactoring
   - `Security` - Security improvements
   - `Deprecated` - Features marked for removal
   - `Removed` - Removed features

4. **Generate a changelog entry** following Keep a Changelog format:
   - Use today's date in ISO format (YYYY-MM-DD)
   - Extract the human-readable phase/feature name from the spec
   - Summarize key deliverables (not implementation details)
   - Keep entries concise but meaningful
   - Group items under appropriate categories

5. **Update CHANGELOG.md:**
   - Insert the new entry below the `## [Unreleased]` section
   - Maintain proper markdown formatting

## Entry Format

```markdown
## [Phase X - Feature Name] - YYYY-MM-DD

### Added

- Feature or capability added (user-facing description)

### Technical

- Infrastructure or tooling change
```

## Rules

- Do NOT include implementation details (file paths, function names)
- DO focus on deliverables and capabilities
- Keep each bullet to one line
- Use imperative mood in descriptions ("Add feature" not "Added feature")
- Section headers use past tense ("Added", "Changed")

```

---

**Usage:**
```

/changelog 001-phase-0-foundation
