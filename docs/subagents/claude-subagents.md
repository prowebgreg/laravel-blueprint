# Claude Code Subagents Reference

This document provides a comprehensive overview of all available Claude Code subagents for The Blueprint CMS project. Subagents are specialized AI assistants that can be invoked for specific tasks, bringing deep expertise in their respective domains.

## Table of Contents

- [Core Development](#core-development)
- [Language Specialists](#language-specialists)
- [Infrastructure](#infrastructure)
- [Quality & Security](#quality--security)
- [Data & AI](#data--ai)
- [Developer Experience](#developer-experience)
- [Specialized Domains](#specialized-domains)
- [Business & Product](#business--product)
- [Meta & Orchestration](#meta--orchestration)
- [Research & Analysis](#research--analysis)

---

## Core Development

Core Development subagents cover the entire development spectrum - from backend services to frontend interfaces, APIs to distributed systems.

### Available Agents

| Agent                   | Description                              | Use When                                                              |
| ----------------------- | ---------------------------------------- | --------------------------------------------------------------------- |
| **api-designer**        | REST and GraphQL API architect           | Designing new APIs, refactoring endpoints, implementing API standards |
| **backend-developer**   | Server-side expert for scalable APIs     | Building APIs, designing databases, implementing authentication       |
| **frontend-developer**  | UI/UX specialist for React, Vue, Angular | Creating web interfaces, implementing UI components, accessibility    |
| **fullstack-developer** | End-to-end feature development           | Building complete features, prototyping, unified stack development    |
| **graphql-architect**   | GraphQL schema and federation expert     | Implementing GraphQL APIs, schema design, federation setup            |
| **ui-designer**         | Visual design and interaction specialist | Creating visual designs, design systems, interaction patterns         |

### Common Combinations

**Full-Stack Web Application:**

- `api-designer` � `backend-developer` � `frontend-developer`

**GraphQL-Powered Application:**

- `graphql-architect` � `backend-developer` � `frontend-developer`

**Design-Driven Development:**

- `ui-designer` � `frontend-developer`

---

## Language Specialists

Language Specialists bring deep knowledge of language idioms, best practices, and framework expertise.

### Available Agents

| Agent                   | Description                           | Use When                                               |
| ----------------------- | ------------------------------------- | ------------------------------------------------------ |
| **javascript-pro**      | JavaScript development expert         | Modern JavaScript, Node.js, async patterns             |
| **laravel-specialist**  | Laravel 10+ full-stack specialist     | Laravel apps, Eloquent ORM, queues, enterprise APIs    |
| **nextjs-developer**    | Next.js 14+ full-stack specialist     | Next.js apps, SSR, full-stack React, Core Web Vitals   |
| **php-pro**             | PHP 8.3+ ecosystem master             | Modern PHP, Laravel/Symfony, async patterns, strict typing |
| **python-pro**          | Python ecosystem master               | Python applications, data pipelines, automation        |
| **react-specialist**    | React 18+ modern patterns expert      | React applications, state management, performance      |
| **sql-pro**             | Database query expert                 | Complex SQL queries, optimization, schema design       |
| **typescript-pro**      | TypeScript specialist                 | Type safety, complex type definitions, migrations      |

### Common Technology Stacks

**Modern Web Application:**

- `react-specialist` + `typescript-pro` + `nextjs-developer`

**Full-Stack Development:**

- `nextjs-developer` + `typescript-pro` + `sql-pro`

**Data & Backend:**

- `python-pro` + `sql-pro`

**Laravel Application:**

- `laravel-specialist` + `php-pro` + `sql-pro`

**PHP Enterprise Development:**

- `php-pro` + `laravel-specialist` + `api-designer`

---

## Infrastructure

Infrastructure subagents handle DevOps, cloud computing, CI/CD, and system reliability.

### Available Agents

| Agent                         | Description                         | Use When                                                  |
| ----------------------------- | ----------------------------------- | --------------------------------------------------------- |
| **cloud-architect**           | AWS/GCP/Azure specialist            | Cloud architectures, migrations, cost optimization        |
| **database-administrator**    | Database management expert          | Database setup, performance tuning, backup strategies     |
| **deployment-engineer**       | Deployment automation specialist    | Deployment pipelines, blue-green, canary releases         |
| **devops-engineer**           | CI/CD and automation expert         | CI/CD pipelines, automation, DevOps practices             |
| **devops-incident-responder** | DevOps incident management          | Troubleshooting, root cause analysis, incident management |
| **incident-responder**        | System incident response expert     | Critical incidents, recovery procedures, post-mortems     |
| **platform-engineer**         | Platform architecture expert        | Internal platforms, developer portals, governance         |
| **security-engineer**         | Infrastructure security specialist  | Security hardening, compliance, threat prevention         |
| **sre-engineer**              | Site reliability engineering expert | SLIs/SLOs, error budgets, chaos engineering               |

### Common Patterns

**Cloud-Native Application:**

- `cloud-architect` � `devops-engineer` � `sre-engineer`

**Platform Engineering:**

- `platform-engineer` + `deployment-engineer` + `devops-engineer` + `cloud-architect`

**Incident Management:**

- `incident-responder` + `devops-incident-responder` + `sre-engineer` + `security-engineer`

---

## Quality & Security

Quality & Security subagents ensure applications are robust, secure, performant, and accessible.

### Available Agents

| Agent                    | Description                          | Use When                                                  |
| ------------------------ | ------------------------------------ | --------------------------------------------------------- |
| **accessibility-tester** | A11y compliance expert               | WCAG compliance, screen reader testing, inclusive design  |
| **architect-reviewer**   | Architecture review specialist       | Design evaluation, architectural debt, system validation  |
| **chaos-engineer**       | System resilience testing expert     | Resilience testing, failure injection, disaster recovery  |
| **code-reviewer**        | Code quality guardian                | Code reviews, standards enforcement, technical debt       |
| **debugger**             | Advanced debugging specialist        | Complex bugs, memory leaks, race conditions, profiling    |
| **error-detective**      | Error analysis and resolution expert | Production errors, log analysis, distributed debugging    |
| **penetration-tester**   | Ethical hacking specialist           | Security assessments, vulnerability testing, OWASP Top 10 |
| **performance-engineer** | Performance optimization expert      | Load testing, bottleneck analysis, optimization           |
| **qa-expert**            | Test automation specialist           | Test strategies, automation frameworks, CI/CD testing     |
| **security-auditor**     | Security vulnerability expert        | Security audits, vulnerability assessment, remediation    |
| **test-automator**       | Test automation framework expert     | Test frameworks, automation, CI/CD integration            |

### Common Patterns

**Comprehensive Testing:**

- `qa-expert` + `test-automator` + `performance-engineer` + `accessibility-tester`

**Security Assessment:**

- `security-auditor` + `penetration-tester` + `code-reviewer`

**Performance Optimization:**

- `performance-engineer` + `debugger` + `error-detective` + `chaos-engineer`

---

## Data & AI

Data & AI subagents handle data engineering, machine learning, and AI system deployment.

### Available Agents

| Agent                  | Description                                | Use When                                            |
| ---------------------- | ------------------------------------------ | --------------------------------------------------- |
| **ai-engineer**        | AI system design and deployment expert     | AI deployment, system architecture, AI integration  |
| **data-analyst**       | Data insights and visualization specialist | Business analysis, dashboards, statistical analysis |
| **data-engineer**      | Data pipeline architect                    | Data pipelines, ETL/ELT, data warehousing           |
| **database-optimizer** | Database performance specialist            | Query optimization, indexing, database tuning       |
| **llm-architect**      | Large language model architect             | LLM solutions, prompt engineering, fine-tuning      |
| **nlp-engineer**       | Natural language processing expert         | Text processing, chatbots, sentiment analysis       |
| **postgres-pro**       | PostgreSQL database expert                 | PostgreSQL optimization, advanced features, schemas |
| **prompt-engineer**    | Prompt optimization specialist             | Prompt design, optimization, testing                |

### Common Patterns

**AI Application:**

- `llm-architect` + `prompt-engineer` + `ai-engineer` + `nlp-engineer`

**Data Platform:**

- `data-engineer` + `database-optimizer` + `postgres-pro` + `data-analyst`

---

## Developer Experience

Developer Experience subagents focus on productivity, tooling, and development workflow optimization.

### Available Agents

| Agent                      | Description                                  | Use When                                                   |
| -------------------------- | -------------------------------------------- | ---------------------------------------------------------- |
| **build-engineer**         | Build system specialist                      | Build optimization, caching, monorepo builds               |
| **dependency-manager**     | Package and dependency specialist            | Version conflicts, security updates, package optimization  |
| **documentation-engineer** | Technical documentation expert               | API docs, developer guides, documentation sites            |
| **dx-optimizer**           | Developer experience optimization specialist | Workflow analysis, productivity, tool selection            |
| **git-workflow-manager**   | Git workflow and branching expert            | Branching strategies, merge conflicts, Git automation      |
| **legacy-modernizer**      | Legacy code modernization specialist         | Legacy refactoring, framework updates, migrations          |
| **mcp-developer**          | Model Context Protocol specialist            | MCP servers, AI tool integrations, protocol implementation |
| **refactoring-specialist** | Code refactoring expert                      | Code structure, design patterns, code smells               |
| **tooling-engineer**       | Developer tooling specialist                 | IDE setup, linters, formatters, custom tooling             |

### Common Patterns

**Legacy Modernization:**

- `legacy-modernizer` + `refactoring-specialist` + `dependency-manager` + `documentation-engineer`

**Developer Productivity:**

- `dx-optimizer` + `tooling-engineer` + `build-engineer` + `git-workflow-manager`

---

## Specialized Domains

Specialized Domains subagents bring expertise in specific technology verticals.

### Available Agents

| Agent                   | Description                           | Use When                                              |
| ----------------------- | ------------------------------------- | ----------------------------------------------------- |
| **api-documenter**      | API documentation specialist          | OpenAPI specs, developer portals, SDK generation      |
| **embedded-systems**    | Embedded and real-time systems expert | Microcontrollers, firmware, RTOS, hardware interfaces |
| **payment-integration** | Payment systems expert                | Payment gateways, PCI compliance, subscriptions       |

### Common Patterns

**Payment System:**

- `payment-integration` + `api-documenter`

**Embedded IoT Solution:**

- `embedded-systems` + `api-documenter`

---

## Business & Product

Business & Product subagents bridge technology and business value.

### Available Agents

| Agent                | Description                        | Use When                                         |
| -------------------- | ---------------------------------- | ------------------------------------------------ |
| **product-manager**  | Product strategy expert            | Product vision, feature prioritization, roadmaps |
| **project-manager**  | Project management specialist      | Project planning, Agile, timeline management     |
| **technical-writer** | Technical documentation specialist | User guides, tutorials, knowledge bases          |
| **ux-researcher**    | User research expert               | User interviews, usability testing, personas     |

### Common Patterns

**Product Development:**

- `product-manager` + `ux-researcher` + `project-manager`

**Documentation & Research:**

- `technical-writer` + `ux-researcher`

---

## Meta & Orchestration

Meta & Orchestration subagents manage complex multi-agent workflows and AI system performance.

### Available Agents

| Agent                       | Description                            | Use When                                                    |
| --------------------------- | -------------------------------------- | ----------------------------------------------------------- |
| **agent-organizer**         | Multi-agent coordinator                | Task decomposition, agent selection, result synthesis       |
| **context-manager**         | Context optimization expert            | Context windows, information prioritization, memory         |
| **error-coordinator**       | Error handling and recovery specialist | Error handling, fallback strategies, system resilience      |
| **knowledge-synthesizer**   | Knowledge aggregation expert           | Information fusion, conflict resolution, insight generation |
| **multi-agent-coordinator** | Advanced multi-agent orchestration     | Large-scale agent systems, parallel workflows               |
| **performance-monitor**     | Agent performance optimization         | Metrics, bottleneck analysis, system efficiency             |
| **task-distributor**        | Task allocation specialist             | Load balancing, capability matching, scheduling             |
| **workflow-orchestrator**   | Complex workflow automation            | Workflow patterns, state management, process automation     |

### Common Patterns

**Complex Problem Solving:**

- `agent-organizer` + `task-distributor` + `knowledge-synthesizer` + `error-coordinator`

**Large-Scale Operations:**

- `multi-agent-coordinator` + `performance-monitor` + `workflow-orchestrator` + `context-manager`

---

## Research & Analysis

Research & Analysis subagents specialize in finding, analyzing, and synthesizing information.

### Available Agents

| Agent                 | Description                           | Use When                                                  |
| --------------------- | ------------------------------------- | --------------------------------------------------------- |
| **research-analyst**  | Comprehensive research specialist     | Deep research, topic investigation, research reports      |
| **search-specialist** | Advanced information retrieval expert | Finding specific information, query optimization          |
| **data-researcher**   | Data discovery and analysis expert    | Dataset analysis, pattern discovery, statistical analysis |

### Common Patterns

**Comprehensive Research:**

- `research-analyst` + `search-specialist` + `data-researcher`

**Data-Driven Insights:**

- `data-researcher` + `research-analyst` + `search-specialist`

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

- `laravel-specialist` + `php-pro` + `cloud-architect`

**Frontend Templating (Blade/Tailwind/Alpine):**

- `frontend-developer` + `ui-designer`

**AWS Infrastructure (S3/CloudFront/Redis):**

- `cloud-architect` + `devops-engineer` + `deployment-engineer`

**SEO & Performance Optimization:**

- `performance-engineer` + `laravel-specialist`

**Code Quality & Security:**

- `code-reviewer` + `test-automator` + `security-auditor`
