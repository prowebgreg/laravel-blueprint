User Prompt:

We need to change and update our Admin pages template. 
1. Remove the account badge/icon from the header. Put there light/dark mode switcher (only light/dark not system).
2. THe searchbar in the header needs restyle, make the iwhole field transparent, no need for bg color in light mode, in dark mode make contrast dark color as search field background
2. For light mode th whole background must be white, for dark version, keep it as is.
3. Left navbar:
  - Navigation Groups - remove icons from group names, and expanding icons make +/- not chevron. Make group name a liitle more font weight (600 for example)
  - Gaps between navigation groups should be lower than we have now
  - add dividers between navigation groups, it must have equal spacing betweet divider and nav groups
  - add icons for each navigation list item (find appropriate icons in our icons file)
  - navigation list items font make it smaller than group names
4. In the end of Navbar add account link with icon and user email. This block must have margin-block-start: auto, so it always will be in the bottom. If navbar is vertically scrilable, I wnat to have more modern look for scrollbar, now it inhertis brawser styles, I need custom modern scrollbar, and as thick as possible.
5. Navbar must have divider from main part of pages. add a divider.

You are freee to ask me clarifying questions if you have such.

```

Instructions:

## Non-negotiable context loading (do this BEFORE planning or coding)

- Always read the project context files:
  - @docs/prd/PRD.md
  - @CLAUDE.md
- Always load the current implementation/specs context:
  - Scan the @specs/ folder and read the relevant spec artifacts for the feature you’re touching (e.g. feature `spec.md`, `data-model.md`, `contracts/`, `quickstart.md`, relevant checklists).
  - If the request spans multiple specs, read the relevant parts from each.

## Mandatory subagent delegation

- Always read @docs/subagents/claude-subagents.md.
- Select the most appropriate subagent(s) for the job type (you may use several subagents at the same time when the work can be parallelized safely).
- When delegating work to subagents, follow the **subagent-driven-development** skill prompt patterns:
  - Use the prompt templates and role handoff style from @.claude/skills/subagent-driven-development/SKILL.md.
  - Give each subagent a crisp task, required files to read (include `@` paths), constraints, and a concrete “report back” checklist.
  - If multiple subagents run, consolidate their outputs and run a review pass (e.g., code-reviewer) before finalizing.
 - Treat every non-trivial implementation request as a mini-project:
   - Decompose the user’s request into a small set of concrete steps (e.g. data model changes, backend APIs, admin UI, tests, docs).
   - For each step, choose the best-fit subagent(s) from `claude-subagents.md` (e.g. `laravel-specialist`, `frontend-developer`, `sql-pro`, `code-reviewer`, `security-auditor`).
   - Prefer **sequential chains** like `laravel-specialist → frontend-developer → code-reviewer` over doing everything in a single agent.
 - For each subagent you dispatch, use a structured prompt:
   - Start with a one-sentence role: “You are the [subagent-name] helping implement a feature in a Laravel 12 + Filament + PostgreSQL project.”
   - Add a `## Feature` section: 2–3 bullet points restating what the user wants in plain language.
   - Add a `## Your step` section: describe exactly what this subagent must accomplish (for this step only).
   - Add a `## Context to read` section: list the most relevant docs and code using `@` paths (e.g. `@docs/prd/PRD.md`, relevant `@specs/...` files, `@app/Models/...`, `@app/Filament/...`, `@resources/views/...`, `@tests/...`).
   - Add a `## Constraints & conventions` section: note important requirements (performance, UX, SEO, etc.) and remind them to follow existing Laravel/Filament patterns and use Laravel Boost MCP tools and other MCP servers when they need up-to-date docs or examples.
   - Add a `## Report back` section asking explicitly for: summary of what they did, files changed/created, tests added or updated and their results, and any open questions or blockers.
 - For quality and safety:
   - After implementation-focused subagents finish, always dispatch a `code-reviewer` subagent (and `security-auditor` or `performance-engineer` when relevant) with a summary of the feature, your expectations, and the list of changed files.
   - If the review finds issues, dispatch a follow-up implementation subagent (usually the same specialist that implemented the feature) with a short list of concrete fixes to apply before considering the work complete.

## Execution expectations

- Validate assumptions against the PRD and current `specs/` artifacts; don’t invent requirements.
- Keep changes aligned with existing project conventions (Filament admin patterns, Laravel best practices, existing UI/theme conventions. Any laravel based workflow must use laravel-boost MCP, to find docs, best practices, coding techniques and methods).
- Avoid exposing secrets; never print sensitive config values in logs or error messages.

## Admin credentials
When you will need to check admin pages and test, always use this creadentials:
User: info@proweb.ai
Password: Levonik2007@

IMPORTANT! Never change this credentials.