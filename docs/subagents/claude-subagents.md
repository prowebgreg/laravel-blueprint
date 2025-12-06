# Claude Code Subagents Reference

This document provides a comprehensive overview of all available Claude Code subagents for The Blueprint CMS project. Subagents are specialized AI assistants that can be invoked for specific tasks, bringing deep expertise in their respective domains.

## Table of Contents

- [Core Development](#core-development)
- [Language Specialists](#language-specialists)
- [Database](#database)
- [Infrastructure & DevOps](#infrastructure--devops)
- [Quality & Security](#quality--security)
- [Developer Experience](#developer-experience)
- [Documentation & Research](#documentation--research)

---

## Core Development

Core Development subagents cover the entire development spectrum - from backend services to frontend interfaces and APIs.

### Available Agents

| Agent                   | Description                              | Use When                                                              |
| ----------------------- | ---------------------------------------- | --------------------------------------------------------------------- |
| **api-designer**        | REST API architect                       | Designing new APIs, refactoring endpoints, implementing API standards |
| **backend-developer**   | Server-side expert for scalable APIs     | Building APIs, designing databases, implementing authentication       |
| **frontend-developer**  | UI specialist for Blade/Alpine/Tailwind  | Creating web interfaces, implementing UI components, accessibility    |
| **fullstack-developer** | End-to-end feature development           | Building complete features, prototyping, unified stack development    |
| **ui-designer**         | Visual design and interaction specialist | Creating visual designs, design systems, interaction patterns         |

### Common Combinations

**Full-Stack Web Application:**

- `api-designer` + `backend-developer` + `frontend-developer`

**Design-Driven Development:**

- `ui-designer` + `frontend-developer`

---

## Language Specialists

Language Specialists bring deep knowledge of language idioms, best practices, and framework expertise.

### Available Agents

| Agent                   | Description                           | Use When                                               |
| ----------------------- | ------------------------------------- | ------------------------------------------------------ |
| **javascript-pro**      | JavaScript development expert         | Alpine.js interactions, Vite configuration, async patterns |
| **laravel-specialist**  | Laravel 12+ full-stack specialist     | Laravel apps, Eloquent ORM, queues, enterprise APIs    |
| **php-pro**             | PHP 8.3+ ecosystem master             | Modern PHP, Laravel/Symfony, async patterns, strict typing |
| **sql-pro**             | Database query expert                 | Complex SQL queries, optimization, schema design       |

### Common Technology Stacks

**Laravel Application:**

- `laravel-specialist` + `php-pro` + `sql-pro`

**PHP Enterprise Development:**

- `php-pro` + `laravel-specialist` + `api-designer`

---

## Database

Database subagents handle PostgreSQL optimization, schema design, and performance tuning.

### Available Agents

| Agent                    | Description                         | Use When                                                  |
| ------------------------ | ----------------------------------- | --------------------------------------------------------- |
| **database-administrator** | Database management expert        | Database setup, performance tuning, backup strategies     |
| **database-optimizer**   | Database performance specialist     | Query optimization, indexing, database tuning             |
| **postgres-pro**         | PostgreSQL database expert          | PostgreSQL optimization, advanced features, schemas       |

### Common Patterns

**PostgreSQL & Performance:**

- `postgres-pro` + `sql-pro` + `database-optimizer`

**Database Administration:**

- `database-administrator` + `postgres-pro` + `database-optimizer`

---

## Infrastructure & DevOps

Infrastructure subagents handle DevOps, CI/CD, and deployment automation.

### Available Agents

| Agent                   | Description                         | Use When                                                  |
| ----------------------- | ----------------------------------- | --------------------------------------------------------- |
| **deployment-engineer** | Deployment automation specialist    | Deployment pipelines, blue-green, canary releases         |
| **devops-engineer**     | CI/CD and automation expert         | CI/CD pipelines, automation, DevOps practices             |
| **security-engineer**   | Infrastructure security specialist  | Security hardening, compliance, threat prevention         |

### Common Patterns

**CI/CD Pipeline:**

- `devops-engineer` + `deployment-engineer`

**Secure Deployment:**

- `security-engineer` + `devops-engineer` + `deployment-engineer`

---

## Quality & Security

Quality & Security subagents ensure applications are robust, secure, performant, and accessible.

### Available Agents

| Agent                    | Description                          | Use When                                                  |
| ------------------------ | ------------------------------------ | --------------------------------------------------------- |
| **accessibility-tester** | A11y compliance expert               | WCAG compliance, screen reader testing, inclusive design  |
| **architect-reviewer**   | Architecture review specialist       | Design evaluation, architectural debt, system validation  |
| **code-reviewer**        | Code quality guardian                | Code reviews, standards enforcement, technical debt       |
| **debugger**             | Advanced debugging specialist        | Complex bugs, memory leaks, race conditions, profiling    |
| **error-detective**      | Error analysis and resolution expert | Production errors, log analysis, debugging                |
| **performance-engineer** | Performance optimization expert      | Load testing, bottleneck analysis, optimization           |
| **performance-monitor**  | Performance metrics specialist       | Metrics collection, anomaly detection, system monitoring  |
| **qa-expert**            | Test automation specialist           | Test strategies, automation frameworks, CI/CD testing     |
| **security-auditor**     | Security vulnerability expert        | Security audits, vulnerability assessment, remediation    |
| **test-automator**       | Test automation framework expert     | Test frameworks, automation, CI/CD integration            |

### Common Patterns

**Comprehensive Testing:**

- `qa-expert` + `test-automator` + `performance-engineer` + `accessibility-tester`

**Security Assessment:**

- `security-auditor` + `code-reviewer`

**Performance Optimization:**

- `performance-engineer` + `debugger` + `error-detective`

---

## Developer Experience

Developer Experience subagents focus on productivity, tooling, and development workflow optimization.

### Available Agents

| Agent                      | Description                                  | Use When                                                   |
| -------------------------- | -------------------------------------------- | ---------------------------------------------------------- |
| **build-engineer**         | Build system specialist                      | Build optimization, caching, asset compilation             |
| **dependency-manager**     | Package and dependency specialist            | Version conflicts, security updates, package optimization  |
| **documentation-engineer** | Technical documentation expert               | API docs, developer guides, documentation sites            |
| **dx-optimizer**           | Developer experience optimization specialist | Workflow analysis, productivity, tool selection            |
| **git-workflow-manager**   | Git workflow and branching expert            | Branching strategies, merge conflicts, Git automation      |
| **legacy-modernizer**      | Legacy code modernization specialist         | Legacy refactoring, framework updates, migrations          |
| **refactoring-specialist** | Code refactoring expert                      | Code structure, design patterns, code smells               |

### Common Patterns

**Legacy Modernization:**

- `legacy-modernizer` + `refactoring-specialist` + `dependency-manager`

**Developer Productivity:**

- `dx-optimizer` + `build-engineer` + `git-workflow-manager`

---

## Documentation & Research

Documentation & Research subagents specialize in documentation, finding information, and analyzing data.

### Available Agents

| Agent                 | Description                           | Use When                                                  |
| --------------------- | ------------------------------------- | --------------------------------------------------------- |
| **api-documenter**    | API documentation specialist          | OpenAPI specs, developer portals, SDK generation          |
| **research-analyst**  | Comprehensive research specialist     | Deep research, topic investigation, research reports      |
| **search-specialist** | Advanced information retrieval expert | Finding specific information, query optimization          |
| **technical-writer**  | Technical documentation specialist    | User guides, tutorials, knowledge bases                   |

### Common Patterns

**API Documentation:**

- `api-documenter` + `technical-writer`

**Comprehensive Research:**

- `research-analyst` + `search-specialist`

---

## Usage Guidelines

### Best Practices

1. **Choose the right specialist** based on your specific needs
2. **Provide clear context** about your project requirements
3. **Specify your tech stack** preferences if any
4. **Describe your constraints** (performance, scalability, timeline)
5. **Combine specialists** for complex projects

### General Principles

- **Start with architecture:** Use architects before implementation
- **Iterate frequently:** Work in short cycles for better results
- **Security throughout:** Security isn't an afterthought
- **Document everything:** Future developers will thank you
- **Test continuously:** Quality is ongoing
- **Measure impact:** Track productivity gains

### For Blueprint CMS Project

When working on The Blueprint CMS (Laravel 12 + Filament V3 + PostgreSQL), consider these common combinations:

**Laravel Core Development:**

- `laravel-specialist` + `php-pro` + `sql-pro`

**Filament Admin Panel:**

- `laravel-specialist` + `php-pro`

**Content Models & Eloquent ORM:**

- `laravel-specialist` + `postgres-pro` + `database-optimizer`

**PostgreSQL & JSONB:**

- `postgres-pro` + `sql-pro` + `database-optimizer`

**API Layer (Sanctum):**

- `laravel-specialist` + `api-designer` + `php-pro`

**Media Library & Queue Processing:**

- `laravel-specialist` + `php-pro`

**Frontend Templating (Blade/Tailwind/Alpine):**

- `frontend-developer` + `ui-designer`

**SEO & Performance Optimization:**

- `performance-engineer` + `laravel-specialist`

**Code Quality & Security:**

- `code-reviewer` + `test-automator` + `security-auditor`
