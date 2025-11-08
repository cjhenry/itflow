# Product Requirements Document: Development Acceleration Initiative

**Version:** 1.0
**Date:** November 2025
**Status:** Draft - Ready for Implementation
**Document Owner:** Engineering Team

---

## Executive Summary

### Problem Statement

ITFlow's current development workflow suffers from significant inefficiencies that slow feature delivery and increase developer frustration:

1. **Repetitive CRUD Development**: Each new entity (categories, services, templates, etc.) requires 200-300 lines of boilerplate code across 5+ files (list page, add modal, edit modal, delete handler, post processor)
2. **Manual Asset Management**: No hot reload or build tooling—developers must manually refresh browsers and manage CSS/JS minification
3. **Raw SQL Queries**: Direct mysqli queries throughout codebase create security risks (SQL injection), maintenance burden, and no IDE autocomplete
4. **No Code Reusability**: Forms, modals, and tables built from scratch each time with duplicated HTML/PHP code
5. **Poor Developer Experience**: No modern tooling (type checking, linting, hot reload) leads to slower iteration cycles

**Impact**: A simple CRUD feature that could take 30 minutes with modern tooling currently takes 3-4 hours to develop and test.

### Solution Overview

Implement a **Development Acceleration Stack** that modernizes the development workflow without requiring a complete rewrite:

1. **Vite Build System** - Hot module reload and modern asset pipeline
2. **PHP Component Library** - Reusable FormBuilder, ModalBuilder, TableBuilder classes
3. **Laravel Query Builder** - Type-safe, fluent database queries replacing raw SQL
4. **CRUD Scaffolding Generator** - Command-line tool to generate complete CRUD modules in seconds
5. **Modern PHP Tooling** - PHPStan for type checking, PHP CS Fixer for code standards
6. **Blade Templating** - Clean, reusable templates replacing mixed PHP/HTML

### Strategic Benefits

- ✓ **3-5x faster development** for new features and CRUD modules
- ✓ **Reduced bugs** through type checking and query builder protection
- ✓ **Instant feedback loop** with hot reload (no manual refreshes)
- ✓ **Better code quality** with automated standards and reusable components
- ✓ **Easier onboarding** for new developers with modern, familiar tools
- ✓ **Foundation for future modernization** (API layer, SPA frontend, mobile apps)
- ✓ **Improved UX** through faster iteration on frontend changes

### Development Speed Comparison

| Task | Current Time | After Improvements | Time Saved |
|------|--------------|-------------------|------------|
| New CRUD module | 3-4 hours | 15-30 min | **85% faster** |
| Form creation | 30-45 min | 5 min | **90% faster** |
| Database query | 10-15 min | 2-3 min | **80% faster** |
| CSS/JS changes | 5 min (manual refresh) | Instant | **Instant feedback** |
| Bug fix deployment | 20-30 min | 5-10 min | **66% faster** |

---

## Functional Requirements

### 1. Vite Build System & Hot Module Reload

#### 1.1 Asset Pipeline Setup

**Objective**: Replace manual CSS/JS file management with automated build system and instant hot reload

**Components**:
- `package.json` - Node.js dependency management
- `vite.config.js` - Build configuration
- Development server with hot reload
- Production asset optimization (minification, tree-shaking, code splitting)

#### 1.2 File Structure

```
/itflow
├── package.json
├── vite.config.js
├── assets/
│   ├── css/
│   │   ├── app.css (main stylesheet)
│   │   └── components/ (component styles)
│   └── js/
│       ├── app.js (main entry)
│       └── modules/ (feature modules)
├── public/
│   └── build/ (generated assets - gitignored)
```

#### 1.3 Development Workflow

**Development Mode**:
```bash
npm run dev
# Starts Vite dev server on localhost:5173
# Hot reload: changes appear instantly without refresh
# Source maps: easy debugging
```

**Production Build**:
```bash
npm run build
# Minifies CSS/JS
# Generates versioned filenames (cache busting)
# Outputs to /public/build/
```

#### 1.4 Integration with PHP

**Header Include**:
```php
<?php if (DEV_MODE): ?>
    <!-- Development: Vite dev server -->
    <script type="module" src="http://localhost:5173/@vite/client"></script>
    <script type="module" src="http://localhost:5173/assets/js/app.js"></script>
<?php else: ?>
    <!-- Production: Built assets -->
    <link rel="stylesheet" href="/public/build/assets/app.css">
    <script type="module" src="/public/build/assets/app.js"></script>
<?php endif; ?>
```

#### 1.5 Success Criteria

- ✓ CSS changes appear instantly without browser refresh
- ✓ JavaScript changes reflect immediately with module reload
- ✓ Production builds 50%+ smaller than current minified files
- ✓ Build time < 5 seconds for production deployment

---

### 2. PHP Component Library

#### 2.1 FormBuilder Component

**Objective**: Eliminate repetitive form HTML with fluent PHP API

**Example Usage**:
```php
use ITFlow\Components\FormBuilder;

$form = FormBuilder::create('post.php', 'POST')
    ->hidden('type', 'category')
    ->text('name', 'Category Name', required: true, maxlength: 200)
    ->color('color', 'Color', required: true)
    ->select('type', 'Type', ['Expense', 'Income', 'Referral'], required: true)
    ->textarea('description', 'Description', rows: 4)
    ->submit('add_category', 'Create', icon: 'fa-check')
    ->cancel('Cancel', icon: 'fa-times')
    ->render();
```

**Generated Output**:
- Properly structured Bootstrap 4 form HTML
- Input group wrappers with icons
- Validation attributes (required, maxlength, pattern)
- CSRF token injection
- Consistent styling and spacing

**Supported Field Types**:
- `text()` - Text input with optional icon, placeholder, maxlength
- `email()` - Email input with validation
- `password()` - Password with show/hide toggle
- `textarea()` - Multi-line text with configurable rows
- `select()` - Dropdown with options array or database query
- `checkbox()` - Single checkbox or checkbox group
- `radio()` - Radio button group
- `color()` - Color picker input
- `date()` - Date picker with Tempusdominus integration
- `file()` - File upload with Dropzone integration
- `hidden()` - Hidden input field
- `submit()` - Submit button with icon
- `cancel()` - Cancel button (modal dismiss)

**Validation Support**:
```php
->text('email', 'Email')
    ->validate('email', 'Please enter valid email')
    ->validate('unique:clients,client_email', 'Email already exists')
```

#### 2.2 ModalBuilder Component

**Objective**: Standardize modal creation across application

**Example Usage**:
```php
use ITFlow\Components\ModalBuilder;

echo ModalBuilder::create('add_category_modal', 'New Category')
    ->size('lg') // sm, md, lg, xl
    ->theme('dark') // dark header
    ->form(FormBuilder::create(...))
    ->footer([
        'submit' => 'Create Category',
        'cancel' => 'Cancel'
    ])
    ->render();
```

**Features**:
- Consistent modal structure (header, body, footer)
- AJAX loading support (via ajax-modal class)
- Auto-dismiss on submit success
- Error handling and display
- Nested form integration
- Custom footer button configurations

#### 2.3 TableBuilder Component

**Objective**: Auto-generate data tables with sorting, filtering, pagination

**Example Usage**:
```php
use ITFlow\Components\TableBuilder;

$table = TableBuilder::create()
    ->query("SELECT * FROM categories WHERE category_type = ?", [$type])
    ->columns([
        'category_name' => ['label' => 'Name', 'sortable' => true],
        'category_color' => ['label' => 'Color', 'type' => 'color-badge'],
        'category_created_at' => ['label' => 'Created', 'type' => 'date']
    ])
    ->actions(['edit', 'delete', 'archive'])
    ->searchable(['category_name'])
    ->pagination(50)
    ->render();
```

**Generated Features**:
- DataTables.js integration
- Custom column rendering (color badges, dates, links, badges)
- Action button dropdowns
- Responsive design
- Export functionality (CSV, PDF)
- Bulk selection and actions

#### 2.4 CardComponent Class

**Objective**: Consistent card layouts throughout application

**Example Usage**:
```php
use ITFlow\Components\CardComponent;

echo CardComponent::create()
    ->title('Categories', icon: 'fa-list-ul')
    ->tools([
        'button' => ['New Category', 'ajax-modal', 'modals/category/category_add.php']
    ])
    ->body($table->render())
    ->footer('Showing 25 of 100 categories')
    ->theme('card-dark')
    ->render();
```

#### 2.5 Component File Structure

```
/includes/components/
├── BaseComponent.php       # Abstract base class
├── FormBuilder.php         # Form generation
├── ModalBuilder.php        # Modal generation
├── TableBuilder.php        # Table generation
├── CardComponent.php       # Card layout
├── AlertComponent.php      # Toast/alert messages
├── BadgeComponent.php      # Status badges
└── helpers/
    ├── ValidationRules.php # Form validation
    └── ColumnRenderers.php # Table column formatters
```

---

### 3. Laravel Query Builder Integration

#### 3.1 Database Connection Setup

**Objective**: Replace raw mysqli queries with fluent, type-safe query builder

**Installation**:
```bash
composer require illuminate/database illuminate/events
```

**Configuration** (`/includes/database/connection.php`):
```php
use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $config['db_host'],
    'database'  => $config['db_name'],
    'username'  => $config['db_user'],
    'password'  => $config['db_pass'],
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();
```

#### 3.2 Query Builder Usage Patterns

**Before (Raw SQL)**:
```php
$sql = mysqli_query(
    $mysqli,
    "SELECT SQL_CALC_FOUND_ROWS * FROM categories
     WHERE category_name LIKE '%$q%'
     AND category_type = '$category'
     AND category_archived_at IS NULL
     ORDER BY $sort $order
     LIMIT $record_from, $record_to"
);
```

**After (Query Builder)**:
```php
use Illuminate\Database\Capsule\Manager as DB;

$categories = DB::table('categories')
    ->where('category_name', 'like', "%{$q}%")
    ->where('category_type', $category)
    ->whereNull('category_archived_at')
    ->orderBy($sort, $order)
    ->skip($record_from)
    ->take($record_to)
    ->get();
```

**Benefits**:
- ✓ Automatic SQL injection protection (parameterized queries)
- ✓ IDE autocomplete for methods
- ✓ Chainable, readable syntax
- ✓ Easier to test and maintain
- ✓ Database agnostic (MySQL, PostgreSQL, SQLite support)

#### 3.3 Common Query Patterns

**Select with Joins**:
```php
$tickets = DB::table('tickets')
    ->join('clients', 'tickets.ticket_client_id', '=', 'clients.client_id')
    ->join('users', 'tickets.ticket_assigned_to', '=', 'users.user_id')
    ->select('tickets.*', 'clients.client_name', 'users.user_name')
    ->where('tickets.ticket_status', 'Open')
    ->get();
```

**Insert**:
```php
$category_id = DB::table('categories')->insertGetId([
    'category_name' => $name,
    'category_type' => $type,
    'category_color' => $color,
    'category_created_at' => now()
]);
```

**Update**:
```php
DB::table('categories')
    ->where('category_id', $id)
    ->update([
        'category_name' => $name,
        'category_updated_at' => now()
    ]);
```

**Delete/Archive**:
```php
// Soft delete
DB::table('categories')
    ->where('category_id', $id)
    ->update(['category_archived_at' => now()]);

// Hard delete
DB::table('categories')->where('category_id', $id)->delete();
```

**Transactions**:
```php
DB::transaction(function () use ($invoice_data, $items) {
    $invoice_id = DB::table('invoices')->insertGetId($invoice_data);

    foreach ($items as $item) {
        $item['invoice_id'] = $invoice_id;
        DB::table('invoice_items')->insert($item);
    }
});
```

#### 3.4 Migration Path

**Phase 1**: New features use Query Builder exclusively
**Phase 2**: Refactor high-risk queries (user input, complex conditions)
**Phase 3**: Gradual migration of remaining queries (low priority)

**Compatibility**: Keep existing mysqli connection for gradual migration

---

### 4. CRUD Scaffolding Generator

#### 4.1 Generator Command

**Objective**: Auto-generate complete CRUD modules in seconds

**Usage**:
```bash
php scaffold.php --model=Category --fields=name:string,color:color,type:select
```

**Generated Files**:
```
/admin/category.php              # List view
/admin/modals/category/
    ├── category_add.php         # Create modal
    ├── category_edit.php        # Edit modal
/admin/post/category.php         # POST handler (CRUD operations)
```

#### 4.2 Scaffold Configuration File

**File**: `/scaffold_config.yaml` or array in PHP

```yaml
model: Category
table: categories
primary_key: category_id
fields:
  - name: name
    type: text
    label: Category Name
    required: true
    maxlength: 200

  - name: color
    type: color
    label: Color
    required: true

  - name: type
    type: select
    label: Type
    options: [Expense, Income, Referral, Ticket]
    required: true

  - name: description
    type: textarea
    label: Description
    rows: 4

timestamps: true
soft_deletes: true
```

#### 4.3 Generated Code Example

**List View** (`/admin/category.php`):
```php
<?php
require_once "includes/inc_all_admin.php";

use Illuminate\Database\Capsule\Manager as DB;
use ITFlow\Components\{CardComponent, TableBuilder};

$categories = DB::table('categories')
    ->where('category_name', 'like', "%{$q}%")
    ->whereNull('category_archived_at')
    ->orderBy($sort, $order)
    ->paginate(50);

echo CardComponent::create()
    ->title('Categories', icon: 'fa-list-ul')
    ->tools(['button' => ['New Category', 'ajax-modal', 'modals/category/category_add.php']])
    ->body(
        TableBuilder::fromCollection($categories)
            ->columns(['name' => 'Name', 'color' => 'Color', 'type' => 'Type'])
            ->actions(['edit', 'delete'])
            ->render()
    )
    ->render();
```

**Add Modal** (`/admin/modals/category/category_add.php`):
```php
<?php
require_once '../../../includes/modal_header.php';

use ITFlow\Components\{ModalBuilder, FormBuilder};

echo ModalBuilder::create('add_category_modal', 'New Category')
    ->form(
        FormBuilder::create('../../post/category.php', 'POST')
            ->hidden('action', 'add_category')
            ->text('name', 'Category Name', required: true, maxlength: 200)
            ->color('color', 'Color', required: true)
            ->select('type', 'Type', ['Expense', 'Income', 'Referral'], required: true)
            ->textarea('description', 'Description', rows: 4)
            ->submit('add_category', 'Create')
            ->cancel()
    )
    ->render();
```

**POST Handler** (`/admin/post/category.php`):
```php
<?php
require_once "../includes/inc_all_admin.php";

use Illuminate\Database\Capsule\Manager as DB;

if (isset($_POST['add_category'])) {
    $name = sanitizeInput($_POST['name']);
    $color = sanitizeInput($_POST['color']);
    $type = sanitizeInput($_POST['type']);

    $category_id = DB::table('categories')->insertGetId([
        'category_name' => $name,
        'category_color' => $color,
        'category_type' => $type,
        'category_created_at' => date('Y-m-d H:i:s')
    ]);

    logAction("Category Created", $session_user_id, $category_id);

    $_SESSION['alert_message'] = "Category created successfully";
    header("Location: ../category.php");
}
// ... edit, delete handlers ...
```

#### 4.4 Customization After Generation

Developers can:
- Modify generated templates to add custom fields
- Add custom validation logic
- Extend with relationships or computed fields
- Add custom actions or bulk operations

**Goal**: 80% code coverage from generator, 20% customization

---

### 5. Modern PHP Tooling & Standards

#### 5.1 PHPStan - Static Analysis

**Objective**: Catch bugs before runtime through type checking

**Installation**:
```bash
composer require --dev phpstan/phpstan
```

**Configuration** (`phpstan.neon`):
```neon
parameters:
    level: 5  # Start at level 5, increase to 8 over time
    paths:
        - includes/
        - admin/
        - agent/
    excludePaths:
        - vendor/
```

**Usage**:
```bash
vendor/bin/phpstan analyse
```

**Benefits**:
- Detects undefined variables, functions, methods
- Type mismatches (string passed where int expected)
- Dead code detection
- Ensures PHPDoc accuracy

#### 5.2 PHP CS Fixer - Code Style

**Objective**: Automated code formatting for consistency

**Installation**:
```bash
composer require --dev friendsofphp/php-cs-fixer
```

**Configuration** (`.php-cs-fixer.php`):
```php
return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => true,
        'no_unused_imports' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
            ->exclude('vendor')
    );
```

**Usage**:
```bash
vendor/bin/php-cs-fixer fix
```

#### 5.3 PHPDoc Standards

**Requirement**: All functions must have PHPDoc blocks

**Example**:
```php
/**
 * Create a new category
 *
 * @param string $name Category name
 * @param string $color Hex color code
 * @param string $type Category type (Expense|Income|Referral)
 * @return int Category ID
 * @throws \Exception if validation fails
 */
function createCategory(string $name, string $color, string $type): int {
    // ...
}
```

**IDE Integration**: VSCode/PHPStorm can use PHPDoc for autocomplete

---

### 6. Blade Templating Engine

#### 6.1 Installation & Setup

**Objective**: Replace mixed PHP/HTML with clean template syntax

**Installation**:
```bash
composer require jenssegers/blade
```

**Setup** (`/includes/blade.php`):
```php
use Jenssegers\Blade\Blade;

$blade = new Blade(__DIR__ . '/../views', __DIR__ . '/../cache/views');
```

#### 6.2 Template Syntax Comparison

**Before (Mixed PHP/HTML)**:
```php
<a href="?category=Expense"
   class="btn <?php if ($category == 'Expense') {
       echo 'btn-primary';
   } else {
       echo 'btn-default';
   } ?>">
   Expense
</a>
```

**After (Blade)**:
```blade
<a href="?category=Expense"
   class="btn {{ $category == 'Expense' ? 'btn-primary' : 'btn-default' }}">
   Expense
</a>
```

#### 6.3 Blade Directives

**Conditionals**:
```blade
@if ($user->role === 'admin')
    <button>Admin Panel</button>
@elseif ($user->role === 'tech')
    <button>Tech Dashboard</button>
@else
    <p>No access</p>
@endif
```

**Loops**:
```blade
@foreach ($categories as $category)
    <tr>
        <td>{{ $category->name }}</td>
        <td><span class="badge" style="background: {{ $category->color }}"></span></td>
    </tr>
@endforeach

@forelse ($tickets as $ticket)
    <li>{{ $ticket->subject }}</li>
@empty
    <li>No tickets found</li>
@endforelse
```

**Components**:
```blade
@component('components.card')
    @slot('title')
        Categories
    @endslot

    @slot('body')
        <table>...</table>
    @endslot
@endcomponent
```

**Escaping** (automatic XSS protection):
```blade
{{ $user_input }}  <!-- Escaped by default -->
{!! $safe_html !!} <!-- Unescaped (use sparingly) -->
```

#### 6.4 Migration Strategy

**Phase 1**: Use Blade for all new pages
**Phase 2**: Convert frequently edited pages
**Phase 3**: Gradual conversion of remaining pages

**Compatibility**: Blade templates can call existing PHP includes

---

## Implementation Plan

### Phase 1: Foundation Setup (Week 1)

**Day 1-2: Vite & Asset Pipeline**
- Install Node.js dependencies (`npm init`, `npm install -D vite`)
- Create `vite.config.js` with proper PHP integration
- Setup `/assets/` directory structure
- Migrate existing `/css/itflow_custom.css` and core JS files
- Test hot reload in development mode
- Configure production build and deployment

**Day 3-4: Query Builder Integration**
- Install Illuminate Database via Composer
- Create `/includes/database/connection.php` wrapper
- Test connection and basic queries
- Document usage patterns
- Create helper functions for common operations

**Day 5: Tooling Setup**
- Install PHPStan and PHP CS Fixer
- Create configuration files
- Run initial analysis (expect many warnings)
- Fix critical issues
- Setup pre-commit hooks (optional)

**Deliverable**: Development environment with hot reload, query builder ready, code quality tools configured

---

### Phase 2: Component Library (Week 2)

**Day 1-2: FormBuilder Component**
- Create `BaseComponent.php` abstract class
- Build `FormBuilder.php` with core field types
- Implement validation rules
- Test with existing forms (convert 2-3 forms as proof of concept)
- Document API with examples

**Day 3: ModalBuilder & CardComponent**
- Build `ModalBuilder.php`
- Build `CardComponent.php`
- Create helper components (AlertComponent, BadgeComponent)
- Test integration with FormBuilder

**Day 4-5: TableBuilder Component**
- Build `TableBuilder.php` with DataTables integration
- Implement column renderers
- Add action button generation
- Test with existing list pages
- Document usage

**Deliverable**: Fully functional component library with documentation and examples

---

### Phase 3: CRUD Generator (Week 3)

**Day 1-2: Generator Engine**
- Create `scaffold.php` CLI script
- Build template engine for file generation
- Implement configuration parser (YAML or array)
- Create default templates for list/add/edit/post

**Day 3: Generator Templates**
- Build Blade templates for generated files
- Add customization hooks
- Implement field type handlers
- Add relationship support (foreign keys)

**Day 4-5: Testing & Refinement**
- Generate 5 test modules (Category, Tag, Status, Template, etc.)
- Compare generated code to hand-written equivalents
- Refine templates based on real-world usage
- Document generator usage and customization

**Deliverable**: Working CRUD generator that creates production-ready modules

---

### Phase 4: Blade Integration (Week 4)

**Day 1-2: Blade Setup**
- Install Jenssegers Blade
- Create `/views/` directory structure
- Setup cache directory
- Create base layout template
- Build common components (header, sidebar, footer)

**Day 3-4: Template Conversion**
- Convert 3-5 frequently used pages to Blade
- Create reusable components for forms, tables, modals
- Test rendering performance
- Document Blade patterns and best practices

**Day 5: Integration Testing**
- Ensure Blade templates work with component library
- Test with scaffolded modules
- Performance benchmarking
- Documentation updates

**Deliverable**: Blade templating integrated with examples and documentation

---

### Phase 5: Documentation & Training (Week 5)

**Day 1-2: Developer Documentation**
- Create `/docs/DEVELOPMENT.md` with setup instructions
- Document component library API
- Write CRUD generator guide
- Create query builder migration guide
- Add code examples and best practices

**Day 3: Training Materials**
- Create video walkthrough of new workflow
- Build example project using all new tools
- Write comparison guide (old vs new approach)
- Create troubleshooting guide

**Day 4-5: Migration Examples**
- Convert 3 existing modules to new stack (full examples)
- Document migration process
- Create before/after code comparisons
- Identify common pitfalls

**Deliverable**: Comprehensive documentation enabling team adoption

---

### Phase 6: Rollout & Adoption (Ongoing)

**Week 6-8: Team Adoption**
- All new features use new stack (mandatory)
- Pair programming sessions for knowledge transfer
- Weekly code reviews focusing on new patterns
- Collect feedback and iterate on tooling

**Week 9-12: Legacy Migration**
- Prioritize high-traffic pages for Blade conversion
- Refactor complex SQL queries to Query Builder
- Regenerate simple CRUD modules with scaffold
- Gradual replacement of old patterns

**Success Metrics**:
- 100% of new features use component library
- 50% of existing queries migrated to Query Builder by end of quarter
- Development time for CRUD reduced by 80%+
- Zero SQL injection vulnerabilities in new code

---

## Technical Specifications

### System Requirements

**Server Requirements**:
- PHP 7.4+ (preferably 8.0+)
- Composer installed
- Node.js 16+ and npm
- Existing ITFlow dependencies maintained

**Development Environment**:
- VSCode or PHPStorm recommended
- PHP Intelephense or similar extension
- Terminal access for CLI tools

**Browser Requirements** (for hot reload):
- Modern browser with ES6 module support
- WebSocket support for HMR (Hot Module Replacement)

### Performance Targets

| Metric | Current | Target |
|--------|---------|--------|
| Hot reload time | N/A (manual refresh) | < 200ms |
| Production build | N/A | < 10s |
| Page load (dev mode) | ~2s | ~2s (no change) |
| Page load (production) | ~2s | ~1.5s (optimized assets) |
| Query execution | Varies | 10-20% faster (indexed, optimized) |

### Security Considerations

**Query Builder**:
- All queries use parameterized statements (SQL injection protection)
- No raw user input in queries
- Validation layer before database interaction

**Component Library**:
- Automatic HTML escaping in templates
- CSRF token injection in all forms
- XSS protection through Blade escaping

**Build Process**:
- `node_modules/` never deployed (only built assets)
- Source maps disabled in production
- Asset integrity verification (SRI hashes optional)

### Backward Compatibility

**Existing Code**:
- All existing PHP code continues to work
- mysqli queries remain functional
- No breaking changes to current functionality
- Gradual migration path (not forced rewrite)

**Database**:
- No schema changes required
- Query Builder works with existing tables
- Can mix mysqli and Query Builder in same codebase

---

## Success Metrics & KPIs

### Development Velocity

**Primary Metrics**:
- ✓ CRUD module development time: 3-4 hours → 15-30 minutes (85% reduction)
- ✓ Form creation time: 30 minutes → 5 minutes (83% reduction)
- ✓ Time to see CSS changes: 5-10 seconds → instant (100% improvement)
- ✓ Lines of code for CRUD: 300 lines → 50 lines (83% reduction)

**Adoption Metrics**:
- ✓ 100% of new features use component library (target: 3 months)
- ✓ 50% of codebase using Query Builder (target: 6 months)
- ✓ 10+ modules generated via scaffold (target: 3 months)
- ✓ Zero manual CSS/JS file concatenation

### Code Quality

**Defect Reduction**:
- ✓ SQL injection vulnerabilities: 0 in new code
- ✓ XSS vulnerabilities: 90% reduction (auto-escaping)
- ✓ Type errors: 50% reduction (PHPStan)
- ✓ Code style inconsistencies: 0 (automated formatting)

**Maintainability**:
- ✓ Code duplication: 70% reduction (reusable components)
- ✓ Time to onboard new developer: 2 weeks → 3 days
- ✓ Documentation coverage: 100% for new components

### Developer Experience

**Satisfaction Indicators**:
- ✓ Reduced context switching (no manual refreshes)
- ✓ Faster iteration cycles (instant feedback)
- ✓ Less frustration with boilerplate
- ✓ More time on feature logic, less on repetitive code

**Productivity Gains**:
- ✓ 2-3x more features per sprint
- ✓ 50% reduction in bug fix time
- ✓ Faster code reviews (standardized patterns)

---

## Cost-Benefit Analysis

### Investment Required

**Time Investment**:
- Initial setup: 5 weeks (1 developer full-time)
- Training: 1 week (team of 3-5 developers)
- Migration effort: Ongoing background task (20% of sprint capacity)
- **Total initial investment**: ~8 weeks of development time

**Monetary Investment**:
- $0 - All tools are free and open source
- No licensing fees
- No infrastructure changes

### Return on Investment

**Time Savings** (per developer, per month):
- CRUD development: 20 hours saved
- Form creation: 10 hours saved
- Query debugging: 5 hours saved
- Asset management: 3 hours saved
- **Total monthly savings**: ~38 hours per developer

**ROI Calculation** (3 developers):
- Monthly savings: 38 hrs × 3 devs = 114 hours
- Quarterly savings: 342 hours
- Break-even point: Week 7 (after 5-week setup + training)
- **First quarter net gain**: 342 - 320 = 22 hours (positive ROI)**

**Long-term Benefits**:
- Year 1: 1,368 hours saved (3.5 months of development capacity)
- Compounding effect as more code is migrated
- Reduced technical debt
- Easier to attract/retain developers (modern stack)

---

## Risk Assessment & Mitigation

### Technical Risks

**Risk 1: Learning Curve**
- **Impact**: Medium - Team unfamiliar with new tools
- **Probability**: High
- **Mitigation**:
  - Comprehensive documentation
  - Pair programming sessions
  - Start with simple modules
  - Allow parallel use of old methods during transition

**Risk 2: Build System Complexity**
- **Impact**: Medium - Vite configuration issues
- **Probability**: Low
- **Mitigation**:
  - Thoroughly test in dev/staging before production
  - Keep old asset system as fallback initially
  - Document troubleshooting steps

**Risk 3: Performance Regression**
- **Impact**: High - Slower page loads
- **Probability**: Low (Vite optimizes better than manual)
- **Mitigation**:
  - Benchmark before/after
  - Use production builds for testing
  - Monitor with real user data

### Organizational Risks

**Risk 4: Resistance to Change**
- **Impact**: High - Team prefers old methods
- **Probability**: Medium
- **Mitigation**:
  - Show clear time savings with demos
  - Make adoption gradual, not forced
  - Collect and address feedback
  - Celebrate quick wins

**Risk 5: Incomplete Migration**
- **Impact**: Medium - Mixed codebase harder to maintain
- **Probability**: Medium
- **Mitigation**:
  - Document both patterns clearly
  - Set clear policies (all new code uses new stack)
  - Allocate dedicated migration time in sprints
  - Track migration progress with metrics

---

## Open Questions for Stakeholder Review

1. **Budget for Training**: Should we allocate dedicated time for team training, or learn as we go?
2. **Migration Priority**: Which modules should be migrated first? (High-traffic vs. frequently edited)
3. **Code Review Standards**: Should we require new patterns for all new code immediately, or gradual adoption?
4. **Documentation Format**: Prefer video tutorials, written docs, or live training sessions?
5. **External Help**: Should we bring in consultant for initial setup and training?
6. **Timeline Flexibility**: Is 5-week setup time acceptable, or need faster rollout?

---

## Dependencies & Prerequisites

### Before Starting Phase 1

- [x] Team buy-in and commitment
- [ ] Server access for installing Node.js/Composer packages
- [ ] Development environment setup for all developers
- [ ] Backup of current codebase
- [ ] Staging environment for testing

### External Dependencies

- **Composer packages**: illuminate/database, jenssegers/blade, phpstan, php-cs-fixer
- **npm packages**: vite, @vitejs/plugin-legacy (for older browser support)
- **Server software**: PHP 7.4+, Node.js 16+

---

## Glossary

| Term | Definition |
|------|-----------|
| **Hot Reload (HMR)** | Development feature where code changes appear instantly without browser refresh |
| **Component Library** | Reusable PHP classes that generate HTML (forms, modals, tables) |
| **Query Builder** | Fluent API for building SQL queries programmatically (safer than raw SQL) |
| **Scaffolding** | Auto-generating code boilerplate from templates |
| **CRUD** | Create, Read, Update, Delete - basic database operations |
| **Blade** | PHP templating engine with cleaner syntax than mixed PHP/HTML |
| **Static Analysis** | Automated code checking without running the program (PHPStan) |
| **Fluent API** | Method chaining pattern (e.g., `$form->text()->email()->submit()`) |

---

## Appendix A: Code Examples

### Example 1: Full CRUD Module with New Stack

**Before** (300+ lines across 5 files):
```php
// admin/category.php (100 lines of SQL, HTML, PHP mixed)
// admin/modals/category/category_add.php (45 lines)
// admin/modals/category/category_edit.php (50 lines)
// admin/post/category.php (100+ lines)
```

**After** (50 lines total using components):
```php
// admin/category.php (20 lines)
<?php
require_once "includes/inc_all_admin.php";
use Illuminate\Database\Capsule\Manager as DB;
use ITFlow\Components\{CardComponent, TableBuilder};

$categories = DB::table('categories')
    ->whereNull('category_archived_at')
    ->orderBy('category_name')
    ->get();

echo CardComponent::create()
    ->title('Categories')
    ->tools(['button' => ['New', 'ajax-modal', 'modals/category/category_add.php']])
    ->body(TableBuilder::fromCollection($categories)->render())
    ->render();

// Modals generated by scaffold (15 lines each)
// POST handler generated by scaffold (15 lines)
```

### Example 2: Form Creation Comparison

**Before** (45 lines):
```php
<form action="post.php" method="post">
    <input type="hidden" name="type" value="category">

    <div class="form-group">
        <label>Name <strong class="text-danger">*</strong></label>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fa fa-list-ul"></i></span>
            </div>
            <input type="text" class="form-control" name="name" required>
        </div>
    </div>

    <div class="form-group">
        <label>Color <strong class="text-danger">*</strong></label>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fa fa-paint-brush"></i></span>
            </div>
            <input type="color" class="form-control" name="color" required>
        </div>
    </div>

    <button type="submit" name="add_category" class="btn btn-primary">
        <i class="fa fa-check mr-2"></i>Create
    </button>
</form>
```

**After** (5 lines):
```php
echo FormBuilder::create('post.php')
    ->text('name', 'Name', required: true, icon: 'fa-list-ul')
    ->color('color', 'Color', required: true, icon: 'fa-paint-brush')
    ->submit('add_category', 'Create')
    ->render();
```

---

## Document History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 0.1 | Nov 2025 | Engineering Team | Initial draft |
| 1.0 | Nov 2025 | Engineering Team | Ready for stakeholder review |

---

**Next Steps**:
1. Stakeholder review and approval
2. Assign development resources (1 developer for Phase 1)
3. Setup development environment
4. Begin Phase 1: Foundation Setup (Week 1)
