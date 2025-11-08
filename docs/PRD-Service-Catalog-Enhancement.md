# Product Requirements Document: Service Catalog Enhancement

**Version:** 1.0
**Date:** November 2025
**Status:** Draft - Ready for Implementation
**Document Owner:** MSP Billing Team

---

## Executive Summary

### Problem Statement
ITFlow's current billing model uses a single `client_rate` per client, inadequate for modern MSPs that offer multiple service types with different pricing tiers. Services like 24/7 Support, Project Work, and Security Monitoring require distinct rates. Agreements cannot specify which services are included, leading to manual invoice adjustments and billing errors.

### Solution Overview
Implement a **Master Service Catalog** in the Admin section, enabling:
1. **Centralized service definitions** with standard rates
2. **Client-level service overrides** for custom pricing
3. **Agreement-service relationships** specifying allowed services and hour allocations
4. **Intelligent invoice generation** pulling from service hierarchy
5. **Per-service hour tracking** for Block Hour agreements
6. **Enhanced billing contact management** for invoicing workflows

### Strategic Benefits
- ✓ Accurate, service-specific billing
- ✓ Flexible rate management at multiple levels
- ✓ Per-service hour tracking and utilization reporting
- ✓ Reduced manual invoice adjustments
- ✓ Improved agreement clarity and compliance
- ✓ Better MSP revenue recognition

---

## Functional Requirements

### 1. Admin Section - Master Service Catalog

#### 1.1 Service Management Page
**Access:** Admin → Services (new menu item)
**Permission:** Admin-only access

#### 1.2 Service Attributes
Each service in the master catalog must have:

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| Service Name | Text (100 chars) | Yes | e.g., "24/7 Managed Support", "Project Development" |
| Description | Text (500 chars) | Yes | Service scope and what's included |
| Default Rate | Decimal(15,2) | Yes | Standard rate per hour or unit |
| Service Category | Dropdown | No | Suggested: Support, Projects, Consulting, Premium, Basic |
| Default Unit | Text | No | "Hour", "Month", "Incident", etc. |
| Status | Dropdown | Yes | Active / Archived (soft delete) |
| Tax Treatment | FK to Tax Codes | No | Link to tax category if applicable |
| Minimum Hours | Decimal(5,2) | No | For subscriptions - minimum billable hours |
| Sorting Order | Integer | No | Display order in dropdowns |

#### 1.3 Master Service CRUD Operations

**Create Service**
- Form with all fields above
- Validation: Name unique, Rate > 0, Description filled
- On create: Set creation timestamp, log admin action
- Default status: "Active"

**View Services**
- Sortable table: Name, Category, Default Rate, Status, Last Modified
- Filters: By Category, By Status, Search by Name
- Bulk actions: Archive/Restore, Export CSV
- Display count of clients using each service

**Edit Service**
- Update any field except Service ID
- Track modification timestamp
- Log changes for audit trail
- Warning: "Editing rate affects future invoices, not historical ones"

**Archive Service**
- Soft delete: Set archived_at timestamp
- Archived services hidden from dropdowns (show with filter option)
- Existing client/agreement references preserved
- Can restore archived services

**Delete Service**
- Hard delete only if never referenced by client/agreement
- Otherwise block with message: "Service is in use by X clients and Y agreements"

#### 1.4 Bulk Operations
- **Import from CSV:** Name, Description, Default Rate, Category
- **Export CSV:** All service data for backup
- **Clone Service:** Create copy with different name and rate

---

### 2. Client Management Enhancements

#### 2.1 New Services Tab in Client Setup/Edit

**Tab Location:** Client Detail Page
**When It Appears:** Always visible for all clients
**Data Loaded:** All active master services + any client-custom services

#### 2.2 Service Display & Overrides

**Layout: Two-Column Interface**

**Column A: Master Services**
- List all active master services
- Show: [Service Name] - [Default Rate] [Default Unit]
- State: Radio button or toggle to "Include / Exclude / Override"
- Status indicators:
  - ✓ Default Rate (green)
  - ⚠ Custom Rate (orange)
  - ✗ Not Included (gray)

**Column B: Service Details (on selection)**
- Service Description (read-only)
- Default Rate (read-only)
- **Custom Rate [OPTIONAL]:** Decimal input field
  - Placeholder: "Leave blank to use default rate"
  - Shows "Using default rate of $X" below field
  - Updates to "Custom rate of $X" when filled
- **Custom Service Name [OPTIONAL]:** Text input
  - Placeholder: "Leave blank to use default name"
- Minimum Hours (display-only from master)
- Notes: Text area for client-specific notes

#### 2.3 Add Custom Services (Client-Specific)

**"Add Custom Service" Button** (bottom of services tab)
- Modal form: Service Name, Description, Rate, Category
- Create new entry in `client_services` with `is_custom = true`
- Not added to master catalog (client-only)
- Status: Always "Active" for custom services

#### 2.4 Service Selection Logic

**Default Behavior (New Client):**
1. Load all active master services
2. All checked by default = "Include with default rate"
3. User can override rates before saving

**Existing Client (Edit):**
1. Show current selections with existing overrides
2. Allow adding/removing services
3. Allow modifying existing overrides
4. Show count of services in use

#### 2.5 Services Billing Configuration

**Billing Tab Enhancements** (new fields added)

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| Billing Contact Name | Text(100) | No | Who to bill (may differ from Primary Support Contact) |
| Billing Contact Email | Email | No | Primary invoice recipient |
| CC Billing Emails | Text(500) | No | Comma-separated list of additional email recipients |
| Primary Support Contact | FK | No | NEW TERMINOLOGY (renamed from "Primary Contact") |
| Net Terms | Integer | Yes | Days to payment (existing) |
| Currency | Dropdown | Yes | (existing) |
| Tax Code | FK | No | (existing) |

**Validation:**
- Billing Email required if any services with rates > 0
- CC Emails: Validate each comma-separated email
- Support Contact: Auto-selects primary contact on new client, can override

---

### 3. Agreement Enhancements

#### 3.1 New "Services" Tab in Agreement Form

**Location:** Agreement Create/Edit page, new tab: "Services"
**Visibility:** After basic agreement fields (Name, Type, Dates)

#### 3.2 Service Selection Interface

**For All Agreement Types:**

**Service List View**
- Checkbox grid: All client services (master + client overrides)
- Show: [Service Name] [Default Rate] [Units]
- Selection: Check services allowed on this agreement
- At least one service must be selected
- Count: "X of Y services selected"

**Service Rate Configuration (per agreement)**
- For each selected service, show:
  - Service Name
  - Rate Source: "Client Rate ($X)" [✓] | "Agreement Override:"
  - Override Rate: [Text input] (optional)
  - Negotiated hours/units: [For display, informational]
- Visual: "Using client rate of $X/hr" (default) or "Using agreement rate of $X/hr" (if overridden)

#### 3.3 Block Hour Agreements - Special Handling

**For "Block Hours - Prepaid" or "Block Hours - Monthly Drawdown":**

**Per-Service Hour Allocation**

Each selected service shows:
```
[Service Name] _________ hours allocated
    (of _______ total hours)
```

- Input: Number of hours to allocate to this service
- Validation:
  - All inputs must be integers or decimals ≥ 0
  - Sum of all service hours = Agreement total hours
  - Alert if sum doesn't match: "Total allocated hours (25) ≠ Agreement hours (30)"
  - Allow auto-distribute: Button "Distribute equally" if user prefers

**Hour Allocation Example:**
```
Agreement: Block Hours - Prepaid
Total Hours: 30

Services:
☑ 24/7 Support        15 hours allocated  (50%)
☑ Project Development 10 hours allocated  (33%)
☑ On-Demand Consulting 5 hours allocated  (17%)
                        ─────────────────
                        30 hours total
```

#### 3.4 Fixed Price & Time & Materials Agreements

**Services Selection:** Services allowed (for invoice filtering), but no hour allocation needed
**Service Rates:** Can override per-agreement if desired
**Recurring Amount:** Can be split by service (optional, informational only)

---

### 4. Ticket & Work Time Tracking Integration

#### 4.1 Service Assignment on Ticket Creation

**New Field: Service (optional)**
- Dropdown: All client services (from agreement if one selected)
- Placeholder: "Select service for time tracking"
- Pre-populated if ticket linked to agreement with single service
- Allows switching later if work spans multiple services

#### 4.2 Time Entry Enhancement

**When logging time/replies:**
- Option to assign service to this time entry
- Shown on time logs and invoice details
- Used for per-service hour tracking and billing

#### 4.3 Contact Terminology Update

**Throughout application, update:**
- "Primary Contact" → "Primary Support Contact"
- "Technical Contact" → "Technical Support Contact" (optional)
- Update database field labels, UI labels, form fields
- Maintain auto-selection behavior: Primary Support Contact auto-selected for new tickets
- New tickets use "Primary Support Contact" by default checkbox

---

### 5. Invoice Generation & Service Integration

#### 5.1 Invoice Creation Enhancements

**Service Selection During Invoice Creation**

When creating manual invoice:
- **Filter Options Dropdown:**
  - ◎ All Client Services (show all services available to client)
  - ◎ Agreement Services Only (if agreement selected, show only allowed services)

- **Selection of Services:**
  - If "Agreement Services" selected: Filter line items dropdown to agreement services
  - If "All Services" selected: Show all client services with warning badge "Not in agreement"
  - Display service rate: Agreement rate > Client rate > Master rate

#### 5.2 Invoice Line Item Structure

Each line item now links to service:
| Field | Type | Source |
|-------|------|--------|
| Service Name | Text | service_catalog or client_services |
| Description | Text | Service description or custom |
| Quantity | Decimal | Hours/units (user entry) |
| Unit Type | Text | From service (Hour/Month/Incident) |
| Unit Rate | Decimal | Agreement > Client > Master (hierarchy) |
| Extended Amount | Decimal | Quantity × Unit Rate |
| Line Total | Decimal | Extended amount + tax |

#### 5.3 Recurring Invoice Service Integration

**Recurring invoices** created from agreements should:
- Auto-populate services from agreement
- Use agreement service rates
- For Block Hour agreements: Allocate hours proportionally by service
- Support regeneration with updated service rates

#### 5.4 Invoice Detail Display (Client Portal)

Invoice detail view shows:
- Service name and description
- Hours/units and rate per unit
- Rate source transparency: "Negotiated rate" vs "Standard rate"

---

### 6. Configuration & Localization Updates

#### 6.1 Country List Enhancement

**File:** `/includes/settings_localization_array.php`

**Addition:**
- Add "Cayman Islands" to `$countries_array`
- Position: Alphabetical (between "Canada" and "Central African Republic")

#### 6.2 Currency Enhancement

**File:** `/includes/settings_localization_array.php`

**Addition:**
- Add to `$currencies_array`: `'KYD' => 'Cayman Islands Dollar'`
- Enable selection in:
  - Company settings
  - Client setup (Currency dropdown)
  - Invoice generation
  - Product/Service rates

#### 6.3 Localization Impact

- Company can set KYD as default currency
- Clients can select KYD for their invoices
- Services/Products display rates in selected currency
- No currency conversion logic (user responsibility)

---

## Database Schema

### New Tables

#### Table: `service_catalog`
```sql
CREATE TABLE service_catalog (
    service_id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(100) NOT NULL,
    service_description VARCHAR(500),
    service_default_rate DECIMAL(15,2) NOT NULL,
    service_category VARCHAR(50),
    service_default_unit VARCHAR(20),
    service_tax_id INT,
    service_minimum_hours DECIMAL(5,2),
    service_sort_order INT DEFAULT 0,
    service_status ENUM('Active', 'Archived') DEFAULT 'Active',
    service_created_by INT,
    service_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    service_updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (service_tax_id) REFERENCES tax_codes(tax_id),
    FOREIGN KEY (service_created_by) REFERENCES users(user_id),
    INDEX idx_service_status (service_status),
    INDEX idx_service_category (service_category)
);
```

#### Table: `client_services`
```sql
CREATE TABLE client_services (
    client_service_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    service_id INT,
    client_service_custom_name VARCHAR(100),
    client_service_custom_rate DECIMAL(15,2),
    client_service_is_custom BOOLEAN DEFAULT FALSE,
    client_service_custom_description VARCHAR(500),
    client_service_custom_notes TEXT,
    client_service_included BOOLEAN DEFAULT TRUE,
    client_service_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    client_service_updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES service_catalog(service_id) ON DELETE SET NULL,
    UNIQUE KEY unique_client_service (client_id, service_id),
    INDEX idx_client_id (client_id),
    INDEX idx_included (client_service_included)
);
```

#### Table: `agreement_services`
```sql
CREATE TABLE agreement_services (
    agreement_service_id INT AUTO_INCREMENT PRIMARY KEY,
    agreement_id INT NOT NULL,
    service_id INT,
    agreement_service_custom_rate DECIMAL(15,2),
    agreement_service_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (agreement_id) REFERENCES agreements(agreement_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES service_catalog(service_id) ON DELETE CASCADE,
    UNIQUE KEY unique_agreement_service (agreement_id, service_id),
    INDEX idx_agreement_id (agreement_id)
);
```

#### Table: `agreement_service_hours`
```sql
CREATE TABLE agreement_service_hours (
    agreement_service_hours_id INT AUTO_INCREMENT PRIMARY KEY,
    agreement_id INT NOT NULL,
    service_id INT NOT NULL,
    service_hours_allocated DECIMAL(10,2),
    service_hours_used DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (agreement_id) REFERENCES agreements(agreement_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES service_catalog(service_id) ON DELETE CASCADE,
    UNIQUE KEY unique_agreement_service_hours (agreement_id, service_id),
    INDEX idx_agreement_id (agreement_id)
);
```

### Modified Tables

#### Table: `clients` (add billing fields)
```sql
ALTER TABLE clients ADD COLUMN (
    client_billing_contact_name VARCHAR(100) AFTER client_rate,
    client_billing_contact_email VARCHAR(100) AFTER client_billing_contact_name,
    client_billing_cc_emails VARCHAR(500) AFTER client_billing_contact_email
);
```

#### Table: `contacts` (rename and enhance)
```sql
-- Rename existing column
ALTER TABLE contacts CHANGE COLUMN contact_primary contact_primary_support BOOLEAN;
ALTER TABLE contacts CHANGE COLUMN contact_technical contact_technical_support BOOLEAN;

-- Update default on new contacts
ALTER TABLE contacts MODIFY contact_primary_support BOOLEAN DEFAULT TRUE;
ALTER TABLE contacts MODIFY contact_technical_support BOOLEAN DEFAULT FALSE;
```

#### Table: `invoice_items` (service linkage)
```sql
-- Link line items to services
ALTER TABLE invoice_items ADD COLUMN (
    item_service_id INT AFTER item_product_id,
    FOREIGN KEY (item_service_id) REFERENCES service_catalog(service_id)
);
```

#### Table: `ticket_replies` (service tracking)
```sql
-- Track which service time was logged against
ALTER TABLE ticket_replies ADD COLUMN (
    ticket_reply_service_id INT AFTER ticket_reply_ticket_id,
    FOREIGN KEY (ticket_reply_service_id) REFERENCES service_catalog(service_id)
);
```

---

## Implementation Plan

### Phase 1: Database Foundation (Week 1)
- Create `service_catalog`, `client_services`, `agreement_services`, `agreement_service_hours` tables
- Migrate `clients` table with new billing fields
- Update `contacts` table terminology (primary_support, technical_support)
- Update `includes/settings_localization_array.php` (add Cayman Islands, KYD)
- Create database migration scripts

### Phase 2: Admin Service Catalog (Week 2)
- Create `/agent/services.php` (list view with filters)
- Create `/agent/modals/service/service_add.php` (create form)
- Create `/agent/modals/service/service_edit.php` (edit form)
- Create `/agent/post/service.php` (CRUD post handler)
- Create `/agent/includes/inc_services.php` (helper functions)
- Add "Services" to admin menu

### Phase 3: Client Services Integration (Week 2-3)
- Add "Services" tab to client setup/edit modal
- Implement client service override UI
- Add "Add Custom Service" functionality
- Update client form save/load logic
- Create `/agent/includes/inc_client_services.php` (helpers)

### Phase 4: Agreement Service Selection (Week 3)
- Add "Services" tab to agreement form
- Implement service selection checkboxes
- Implement per-service rate override UI
- Implement Block Hour per-service hour allocation
- Update agreement save logic

### Phase 5: Invoice Service Integration (Week 4)
- Add service filtering to invoice creation
- Update invoice line item form to link services
- Implement rate hierarchy lookup (agreement > client > master)
- Update invoice display to show service info

### Phase 6: Billing & Contact Enhancements (Week 4)
- Add billing contact fields to client form
- Update contact terminology throughout
- Update ticket creation to use "Primary Support Contact"
- Add service selection to time entries (optional)

### Phase 7: Testing & Refinement (Week 5)
- Integration testing across modules
- Data migration validation
- Performance testing with large service catalogs
- User acceptance testing

---

## Rate Hierarchy & Resolution Logic

### Service Rate Determination

When generating invoice line item for a service:

```
IF agreement_service_custom_rate IS NOT NULL THEN
    rate = agreement_service_custom_rate
ELSE IF client_service_custom_rate IS NOT NULL THEN
    rate = client_service_custom_rate
ELSE
    rate = service_catalog.service_default_rate
END IF
```

### Example Scenarios

**Scenario 1: Master Rate Only**
```
Master: 24/7 Support @ $100/hr
Client: No override
Agreement: No override
Result: $100/hr
```

**Scenario 2: Client Override**
```
Master: 24/7 Support @ $100/hr
Client: Custom rate $85/hr (preferred partner)
Agreement: No override
Result: $85/hr
```

**Scenario 3: Agreement Override**
```
Master: 24/7 Support @ $100/hr
Client: Custom rate $85/hr
Agreement: Negotiated rate $75/hr
Result: $75/hr
```

**Scenario 4: Block Hours with Multiple Services**
```
Agreement: Block Hours - 30 hours total
  - Support: 15 hours @ $75/hr (agreement rate)
  - Development: 10 hours @ $120/hr (client rate)
  - Consulting: 5 hours @ $150/hr (master rate)

Utilization:
  Support: 12 hours used (80% of 15)
  Development: 10 hours used (100% of 10)
  Consulting: 2 hours used (40% of 5)

Overage: 1 hour (24 used vs 30 allocated)
```

---

## API Endpoints (Future REST Integration)

Future endpoints for service integration:

```
GET    /api/services                      - List master services
POST   /api/services                      - Create service
PUT    /api/services/{id}                 - Update service
DELETE /api/services/{id}                 - Archive service

GET    /api/clients/{id}/services         - List client services with overrides
POST   /api/clients/{id}/services         - Add/override service for client

GET    /api/agreements/{id}/services      - List services in agreement
POST   /api/agreements/{id}/services      - Add service to agreement
PUT    /api/agreements/{id}/services/{id} - Update service rate in agreement

GET    /api/invoices/{id}/services        - List services available for invoice
```

---

## Success Metrics & KPIs

### Billing Accuracy
- ✓ 100% of invoices match service rates defined
- ✓ Per-service hour tracking matches time entries
- ✓ Zero manual invoice adjustments due to rate mismatches

### Operational Efficiency
- ✓ Invoice creation time reduced by 50% (no manual rate lookups)
- ✓ Agreement clarity: All parties understand service rates
- ✓ Rate management centralized: One master catalog for all clients

### User Adoption
- ✓ Admin sets up 10+ services in < 1 hour
- ✓ Client override UI intuitive (no training needed)
- ✓ Agreement service selection reduces confusion

### Financial Impact
- ✓ Enables service-level profitability analysis
- ✓ Supports dynamic pricing strategies per client
- ✓ Reduces billing disputes by 80%

---

## Assumptions & Constraints

### Assumptions
1. Cayman Islands and KYD currency will be selectable options (no conversion logic needed)
2. Master service catalog maintained by admin (not clients)
3. Historical invoices don't need service linkage (applies going forward)
4. Block Hour agreements always allocate to at least one service

### Constraints
1. Service names must be unique in master catalog
2. Archived services remain usable in existing agreements (immutability)
3. Deleting a service only allowed if never referenced
4. Cannot modify service rates retroactively on generated invoices

### Performance Considerations
1. Service catalog typically < 50 items (minimal indexing needed)
2. Client services typically < 100 per client (cached in session for perf)
3. Agreement services < 20 per agreement
4. Indexes on (client_id, agreement_id) for fast lookup

---

## Open Questions for Stakeholder Review

1. Should service catalog be multi-currency (same service different rates per currency)?
2. Should there be service-level discount rules or always at agreement level?
3. Should service availability be date-based (e.g., seasonal services)?
4. What reporting is needed on per-service profitability?
5. Should clients see master catalog in their portal?

---

## Glossary

| Term | Definition |
|------|-----------|
| **Master Service Catalog** | Centralized list of all services company offers |
| **Service Override** | Custom rate or name for a specific client |
| **Agreement Service** | Service allowed on a specific agreement with optional rate override |
| **Block Hour Agreement** | Agreement with fixed hours allocated per service |
| **Rate Hierarchy** | Precedence: Agreement Rate > Client Rate > Master Rate |
| **Primary Support Contact** | Main contact for technical support (replaces "Primary Contact") |
| **Billing Contact** | Designated person for invoicing communications |
---

## Document History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 0.1 | Nov 2025 | Product Team | Initial draft |
| 1.0 | Nov 2025 | Product Team | Ready for implementation |

---

**Next Step:** Stakeholder review and sign-off. Upon approval, proceed to Phase 1 database implementation.

**Status:** ✓ Available on GitHub at https://github.com/cjhenry/itflow/blob/master/docs/PRD-Service-Catalog-Enhancement.md
