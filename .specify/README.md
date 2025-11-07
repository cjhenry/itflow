# Spec Kit Installation

This project has been set up with [GitHub Spec Kit](https://github.com/github/spec-kit), a toolkit for Spec-Driven Development with AI.

## Directory Structure

```
.specify/
├── memory/
│   └── constitution.md          # Project governing principles
├── scripts/
│   └── bash/                    # Automation scripts
│       ├── check-prerequisites.sh
│       ├── common.sh
│       ├── create-new-feature.sh
│       ├── setup-plan.sh
│       └── update-agent-context.sh
├── specs/                       # Feature specifications (created per feature)
└── templates/                   # Templates for specs, plans, and tasks
    ├── spec-template.md
    ├── plan-template.md
    └── tasks-template.md

.claude/
└── commands/                    # Slash commands for Claude
    ├── speckit.constitution.md
    ├── speckit.specify.md
    ├── speckit.clarify.md
    ├── speckit.plan.md
    ├── speckit.tasks.md
    ├── speckit.implement.md
    ├── speckit.analyze.md
    └── speckit.checklist.md
```

## Available Commands

Use these slash commands in Claude to follow the Spec-Driven Development workflow:

### Core Commands

- `/speckit.constitution` - Create or update project governing principles
- `/speckit.specify` - Define what you want to build (requirements and user stories)
- `/speckit.plan` - Create technical implementation plans with your tech stack
- `/speckit.tasks` - Generate actionable task lists for implementation
- `/speckit.implement` - Execute all tasks to build the feature

### Optional Commands

- `/speckit.clarify` - Clarify underspecified areas (recommended before planning)
- `/speckit.analyze` - Cross-artifact consistency & coverage analysis
- `/speckit.checklist` - Generate custom quality checklists

## Getting Started

1. **Establish project principles**:
   ```
   /speckit.constitution Create principles focused on code quality, testing standards, and user experience
   ```

2. **Create a specification**:
   ```
   /speckit.specify Build a feature that allows users to...
   ```

3. **Create a technical plan**:
   ```
   /speckit.plan Use PHP and MySQL for the backend...
   ```

4. **Break down into tasks**:
   ```
   /speckit.tasks
   ```

5. **Execute implementation**:
   ```
   /speckit.implement
   ```

## Resources

- [Spec Kit Documentation](https://github.github.io/spec-kit/)
- [GitHub Repository](https://github.com/github/spec-kit)
- [Spec-Driven Development Guide](https://github.com/github/spec-kit/blob/main/spec-driven.md)
