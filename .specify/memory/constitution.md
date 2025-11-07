<!--
SYNC IMPACT REPORT
==================
Version Change: INITIAL → 1.0.0
Change Type: MINOR (Initial constitution creation)
Date: 2025-11-07

Principles Added:
- I. Security-First Development
- II. Code Quality Standards
- III. Modern Tech Stack
- IV. Test-Driven Development
- V. Performance & Scalability
- VI. Documentation Standards
- VII. Maintainability & Refactoring

Sections Added:
- Security Requirements
- Development Workflow
- Governance

Templates Status:
✅ plan-template.md - Compatible with constitution principles
✅ spec-template.md - Compatible with constitution principles
✅ tasks-template.md - Compatible with constitution principles

Follow-up Actions:
- None - All principles defined and ready for use
-->

# ITFlow Constitution

## Core Principles

### I. Security-First Development

Security is NON-NEGOTIABLE and must be considered at every stage of development:

- **Input Validation**: All user input MUST be validated, sanitized, and escaped before use. Use
  prepared statements for ALL database queries - no exceptions.
- **Authentication & Authorization**: Every endpoint MUST verify user authentication and
  authorization. Role-based access control (RBAC) is mandatory.
- **Sensitive Data Protection**: Passwords MUST use bcrypt/Argon2. API keys, tokens, and secrets
  MUST be stored encrypted. Never log sensitive data.
- **Security Headers**: All responses MUST include appropriate security headers (CSP,
  X-Frame-Options, X-Content-Type-Options, HSTS).
- **Dependency Security**: Dependencies MUST be regularly scanned for vulnerabilities. Security
  updates have priority over feature development.
- **OWASP Compliance**: All code MUST address OWASP Top 10 vulnerabilities. Regular security
  audits are required.

**Rationale**: ITFlow handles sensitive MSP client data including passwords, financial records,
and confidential documentation. A security breach would be catastrophic for both the MSP and
their clients.

### II. Code Quality Standards

Code quality is enforced through automated tooling and peer review:

- **Coding Standards**: Follow PSR-12 for PHP code. Use consistent naming conventions across the
  codebase.
- **Static Analysis**: Code MUST pass PHPStan level 5+ or equivalent static analysis before merge.
- **Code Review**: All changes require peer review. Security-critical changes require two
  reviewers.
- **No Dead Code**: Remove commented-out code, unused functions, and deprecated features. Keep
  the codebase clean.
- **Complexity Limits**: Functions SHOULD NOT exceed 50 lines. Cyclomatic complexity SHOULD NOT
  exceed 10.
- **DRY Principle**: Duplicate code blocks (>3 occurrences) MUST be refactored into reusable
  functions.

**Rationale**: High-quality code is easier to maintain, debug, and extend. Consistent standards
reduce cognitive load for contributors.

### III. Modern Tech Stack

Adopt modern, well-supported technologies while maintaining backward compatibility:

- **PHP Version**: Target the latest stable PHP version (currently 8.x+). Deprecate support for
  EOL PHP versions within 6 months of their end-of-life.
- **Database**: Use MySQL 8.0+ or MariaDB 10.5+ features where beneficial (JSON columns, CTEs,
  window functions).
- **Frontend**: Progressively enhance with modern JavaScript (ES6+). Use build tools (webpack,
  vite) for asset optimization.
- **Dependencies**: Prefer well-maintained packages with active communities. Evaluate
  alternatives before adding new dependencies.
- **APIs**: REST APIs MUST follow OpenAPI 3.0 specifications. Consider GraphQL for complex
  data queries.
- **Progressive Enhancement**: Core functionality MUST work without JavaScript. Enhanced features
  degrade gracefully.

**Rationale**: Modern technologies improve performance, security, and developer productivity while
reducing technical debt.

### IV. Test-Driven Development

Testing is MANDATORY for all new features and bug fixes:

- **Unit Tests**: All business logic MUST have unit tests with minimum 80% code coverage.
- **Integration Tests**: API endpoints and database interactions MUST have integration tests.
- **Test-First Approach**: For new features - write tests FIRST, verify they fail, then
  implement.
- **Continuous Testing**: Tests MUST run automatically on every commit. Failed tests block
  merging.
- **Test Quality**: Tests MUST be readable, maintainable, and fast. Avoid flaky tests.
- **Edge Cases**: Tests MUST cover error conditions, boundary values, and edge cases - not just
  happy paths.

**Rationale**: Tests prevent regressions, document expected behavior, and enable confident
refactoring. TDD improves design quality.

### V. Performance & Scalability

Performance is a feature, not an afterthought:

- **Database Optimization**: All queries MUST use proper indexes. N+1 queries are prohibited.
  Use EXPLAIN to analyze query plans.
- **Caching Strategy**: Implement appropriate caching (query cache, page cache, object cache) for
  expensive operations.
- **Lazy Loading**: Load data and assets only when needed. Implement pagination for large
  datasets.
- **Response Times**: API endpoints SHOULD respond in <200ms (p95). Page loads SHOULD complete
  in <2s.
- **Scalability**: Design for growth. Consider multi-tenant isolation, horizontal scaling, and
  resource limits.
- **Profiling**: Profile performance bottlenecks before optimizing. Measure improvements
  objectively.

**Rationale**: MSPs manage hundreds of clients with thousands of assets. Poor performance
directly impacts user productivity.

### VI. Documentation Standards

Code without documentation is incomplete:

- **Code Comments**: Complex logic MUST have explanatory comments. Use PHPDoc blocks for
  functions and classes.
- **API Documentation**: All API endpoints MUST be documented with request/response examples and
  error codes.
- **User Documentation**: User-facing features MUST include end-user documentation and
  screenshots.
- **Architecture Decisions**: Significant architectural decisions MUST be documented (ADRs).
- **Changelog**: All changes MUST be documented in CHANGELOG.md following Keep a Changelog
  format.
- **Inline Updates**: Update documentation in the SAME commit as code changes.

**Rationale**: Documentation enables contributors to understand and extend the system. It reduces
onboarding time and support burden.

### VII. Maintainability & Refactoring

Write code for the next developer:

- **Clear Naming**: Variable and function names MUST be descriptive and unambiguous. Avoid
  abbreviations.
- **Small Functions**: Functions SHOULD do one thing well. Extract complex logic into helper
  functions.
- **Separation of Concerns**: Business logic, data access, and presentation MUST be separated.
- **Refactoring Cycles**: Dedicate 20% of development time to refactoring and technical debt
  reduction.
- **Legacy Migration**: When touching legacy code, improve it. Leave code better than you found
  it.
- **Deprecation Process**: Features MUST be deprecated for one major version before removal with
  clear migration paths.

**Rationale**: Maintainable code has lower long-term costs and enables faster feature
development.

## Security Requirements

### Vulnerability Management

- **Dependency Scanning**: Automated scanning for vulnerable dependencies on every build
- **Security Patches**: Critical vulnerabilities MUST be patched within 48 hours
- **Disclosure Policy**: Follow responsible disclosure practices for security issues
- **Penetration Testing**: Annual security audits by external parties recommended

### Secure Development Lifecycle

- **Threat Modeling**: Required for new features handling authentication, authorization, or
  sensitive data
- **Security Review**: Required before releasing features to production
- **Secrets Management**: No hardcoded secrets. Use environment variables or secret management
  systems
- **Audit Logging**: Security-relevant actions MUST be logged (auth attempts, permission changes,
  data access)

### Data Protection

- **Encryption at Rest**: Sensitive data MUST be encrypted in the database
- **Encryption in Transit**: All connections MUST use TLS 1.2+
- **Data Retention**: Implement data retention and deletion policies
- **Privacy Compliance**: Follow GDPR, CCPA, and applicable privacy regulations

## Development Workflow

### Branch Strategy

- **Main Branch**: Always deployable. Protected with required reviews and passing tests.
- **Feature Branches**: Use descriptive names (e.g., `feature/add-asset-scanning`,
  `fix/ticket-assignment-bug`).
- **Spec-Driven Development**: Use `/speckit.*` commands for structured feature development.

### Code Review Process

- **Pull Request Template**: Use template including description, testing notes, and checklist
- **Review Criteria**: Security, correctness, tests, documentation, performance
- **Approval Requirements**: One approval for minor changes, two for security/architecture changes
- **Automated Checks**: All CI checks MUST pass before merge

### Quality Gates

- **Pre-Commit**: Linting, formatting checks
- **Pre-Push**: Unit tests, static analysis
- **Pull Request**: Integration tests, code coverage, security scanning
- **Pre-Release**: Performance benchmarks, security audit, documentation review

### Release Process

- **Semantic Versioning**: MAJOR.MINOR.PATCH format
- **Release Notes**: Detailed changelog with upgrade instructions
- **Database Migrations**: Reversible migrations with rollback procedures
- **Staged Rollout**: Test in staging environment before production deployment

## Governance

### Constitution Compliance

- This constitution supersedes all other development practices and guidelines
- All pull requests MUST be evaluated for constitutional compliance during code review
- Violations MUST be justified with documented rationale and project lead approval
- Complexity introduced MUST provide proportional value and be thoroughly documented

### Amendment Process

- **Proposal**: Amendments require written proposal with rationale and impact analysis
- **Discussion**: Open discussion period for all contributors (minimum 1 week)
- **Approval**: Requires consensus from project maintainers
- **Migration Plan**: Breaking changes require migration guide and deprecation period
- **Documentation**: Amendments MUST update the Sync Impact Report at the top of this document

### Version Management

- **Semantic Versioning**:
  - MAJOR: Backward-incompatible principle changes or removals
  - MINOR: New principles or sections added
  - PATCH: Clarifications, wording improvements, non-semantic changes
- **Review Cycle**: Constitution reviewed annually for relevance and effectiveness
- **Guidance File**: For runtime development guidance, refer to `.specify/README.md`

### Enforcement

- All code reviews MUST verify constitutional compliance
- Automated tooling SHOULD enforce rules where possible (linters, CI checks)
- Technical debt that violates principles MUST be tracked and prioritized for remediation
- New contributors MUST review and acknowledge this constitution

**Version**: 1.0.0 | **Ratified**: 2025-11-07 | **Last Amended**: 2025-11-07
