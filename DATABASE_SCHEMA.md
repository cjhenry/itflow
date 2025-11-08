# ITFlow Database Schema Reference

**Database:** itflow_dev
**Server:** MariaDB 10.11.14
**Current Version:** 2.3.6
**Last Updated:** November 2025

---

## Quick Reference: Key Tables

### Products & Services (Currently Mixed)
| Field | Type | Purpose |
|-------|------|---------|
| `product_id` | INT | Primary key |
| `product_name` | VARCHAR(200) | Name of product or service |
| `product_type` | ENUM('service','product') | **IMPORTANT: Distinguishes products from services** |
| `product_description` | TEXT | Description |
| `product_price` | DECIMAL(15,2) | Price per unit |
| `product_currency_code` | VARCHAR(200) | Currency code |
| `product_tax_id` | INT | FK to taxes table |
| `product_category_id` | INT | FK to product_categories |
| `product_archived_at` | DATETIME | Soft delete timestamp |

**⚠️ CRITICAL:** When querying products, ALWAYS filter by `product_type = 'product'` to exclude services!

---

### Future: Separate Service Catalog (Planned v2.4)
*(Not yet implemented - see PRD-Service-Catalog-Enhancement.md)*

```
service_catalog
├── service_id (PK)
├── service_name
├── service_description
├── service_default_rate
├── service_category
├── service_default_unit (Hour/Month/Incident)
├── service_tax_id (FK)
├── service_minimum_hours
├── service_sort_order
├── service_status (Active/Archived)
└── service_created_at, service_updated_at
```

---

## Complete Table Inventory

### Core Business Tables

#### `clients`
- **Purpose:** Customer/MSP client information
- **Key Fields:** client_name, client_rate, client_currency_code, client_net_terms
- **Pending Additions:** client_billing_contact_name, client_billing_contact_email, client_billing_cc_emails

#### `contacts`
- **Purpose:** People associated with clients
- **Key Fields:** contact_name, contact_email, contact_phone, contact_primary, contact_technical
- **Pending Rename:** contact_primary → contact_primary_support, contact_technical → contact_technical_support

#### `tickets`
- **Purpose:** Support tickets/issues
- **Key Fields:** ticket_number, ticket_status, ticket_priority, ticket_client_id
- **Linked To:** clients, assets, agreements (pending)

#### `ticket_replies`
- **Purpose:** Time tracking and notes on tickets
- **Key Fields:** ticket_reply_title, ticket_reply_description, ticket_reply_read_by_client, time logged
- **Pending Addition:** ticket_reply_service_id (FK to service_catalog or product type='service')

#### `invoices`
- **Purpose:** Billing documents
- **Key Fields:** invoice_number, invoice_amount, invoice_status, invoice_date, invoice_due_date
- **Linked To:** clients, quotes (as source)
- **Pending Addition:** invoice_agreement_id (FK to agreements)

#### `invoice_items`
- **Purpose:** Line items on invoices
- **Key Fields:** item_name, item_description, item_quantity, item_price, item_total, item_tax
- **Pending Addition:** item_service_id (FK to service_catalog or product type='service')

#### `quotes`
- **Purpose:** Quotations/estimates for clients
- **Key Fields:** quote_number, quote_status (Draft/Sent/Viewed/Accepted/Declined/Invoiced), quote_amount, quote_date, quote_expire
- **Linked To:** clients, invoice_items (shared table with invoices)

#### `products`
- **Purpose:** Sellable items (currently mixed: products AND services)
- **Key Fields:** product_name, product_type (**ENUM: 'product' or 'service'**), product_price, product_category_id
- **Note:** See warning above - always filter by type!

#### `product_categories`
- **Purpose:** Categorize products
- **Key Fields:** product_category_name

#### `assets`
- **Purpose:** Client IT assets (servers, workstations, software, domains, etc.)
- **Types:** server, workstation, software, domain, license
- **Key Fields:** asset_name, asset_type, asset_status

#### `locations`
- **Purpose:** Physical locations for clients
- **Key Fields:** location_address, location_city, location_state, location_zip, location_country

#### `companies`
- **Purpose:** Your company/MSP information
- **Key Fields:** company_name, company_address, company_city, company_state, company_zip, company_phone, company_email, company_logo

#### `settings`
- **Purpose:** Company settings and configuration
- **Key Fields:** settings_company_id, and many individual setting keys
- **Example Settings:** config_smtp_host, config_base_url, config_default_net_terms, config_hide_tax_fields

#### `taxes`
- **Purpose:** Tax codes and rates
- **Key Fields:** tax_name, tax_percent, tax_archived_at

#### `users`
- **Purpose:** Application users
- **Key Fields:** user_name, user_email, user_role_id, user_password_hash, user_archived_at

#### `roles`
- **Purpose:** User permission roles
- **Key Fields:** role_name, module_X (permissions bitmask for each module)

---

## Agreement Tables (Recent Addition)

### `agreements` (Main)
```sql
agreement_id (PK)
agreement_number (unique)
agreement_client_id (FK → clients)
agreement_type (Fixed Price / Block Hours Prepaid / Block Hours Monthly / Time & Materials)
agreement_status (Draft / Active / Expired / Cancelled / Renewed)
agreement_start_date
agreement_end_date
agreement_renewal_date
agreement_value (total fixed price or monthly recurring)
agreement_recurring_amount (for recurring agreements)
agreement_billing_frequency (monthly / quarterly / annually)
agreement_hours_included (for block hour agreements)
agreement_hours_used
agreement_overage_rate (additional hours beyond allocation)
agreement_auto_renew (boolean)
agreement_auto_invoice (boolean)
agreement_email_notifications (boolean)
agreement_notes
agreement_created_at
agreement_updated_at
```

### `agreement_services`
```sql
agreement_service_id (PK)
agreement_id (FK)
service_id (FK) — future link to service_catalog
agreement_service_custom_rate (override rate for this agreement)
agreement_service_created_at
```

### `agreement_service_hours` (Block Hour Allocation)
```sql
agreement_service_hours_id (PK)
agreement_id (FK)
service_id (FK)
service_hours_allocated (hours assigned to this service)
service_hours_used (hours consumed)
created_at
updated_at
```

### `agreement_assets`
```sql
agreement_asset_id (PK)
agreement_id (FK)
asset_id (FK → assets)
agreement_asset_created_at
```

### `agreement_rate_tiers`
```sql
rate_tier_id (PK)
agreement_id (FK)
rate_tier_type (by_ticket_type / by_time_of_day)
rate_tier_condition (e.g., "critical", "urgent", "after_hours")
rate_tier_multiplier (1.5 = 150% of base rate)
rate_tier_created_at
```

### `agreement_hours_history`
```sql
history_id (PK)
agreement_id (FK)
history_period (e.g., "2025-11")
history_hours_allocated
history_hours_used
history_overage_hours
history_created_at
```

---

## Important Field Patterns

### Soft Delete Pattern
Many tables use this to avoid permanent data loss:
```
table_archived_at (DATETIME NULL)
```
Check with: `WHERE table_archived_at IS NULL`

### Foreign Keys
```
FK fields: table_name_id → references other_table(other_table_id)
```

### Timestamps
```
created_at (DATETIME DEFAULT CURRENT_TIMESTAMP)
updated_at (DATETIME ON UPDATE CURRENT_TIMESTAMP)
```

### Boolean Fields
Stored as TINYINT(1) (0 = false, 1 = true)

---

## Critical Queries for Development

### Get ONLY Products (not services)
```sql
SELECT * FROM products
WHERE product_type = 'product'
AND product_archived_at IS NULL
ORDER BY product_name ASC;
```

### Get ONLY Services (until service_catalog implemented)
```sql
SELECT * FROM products
WHERE product_type = 'service'
AND product_archived_at IS NULL
ORDER BY product_name ASC;
```

### Get Client with all relationships
```sql
SELECT c.*, l.*, ct.*
FROM clients c
LEFT JOIN locations l ON c.client_id = l.location_client_id AND location_primary = 1
LEFT JOIN contacts ct ON c.client_id = ct.contact_client_id AND contact_primary = 1
WHERE c.client_id = ? AND c.client_archived_at IS NULL;
```

### Get Quote with all items
```sql
SELECT q.*, ii.*, c.*, t.*
FROM quotes q
LEFT JOIN clients c ON q.quote_client_id = c.client_id
LEFT JOIN invoice_items ii ON q.quote_id = ii.item_quote_id
LEFT JOIN taxes t ON ii.item_tax_id = t.tax_id
WHERE q.quote_id = ?
ORDER BY ii.item_order ASC;
```

---

## Rate Hierarchy (Service Pricing)

When determining price for a service on an invoice:

1. **Agreement Override Rate** (if agreement has custom rate for service)
2. **Client Override Rate** (if client has custom rate for service)
3. **Master Service Rate** (default from service_catalog or product)

```sql
-- Logic for rate lookup
IF agreement_service_custom_rate IS NOT NULL THEN
    use agreement_service_custom_rate
ELSE IF client_service_custom_rate IS NOT NULL THEN
    use client_service_custom_rate
ELSE
    use product.product_price or service_catalog.service_default_rate
END IF
```

---

## Database Version History

| Version | Date | Key Changes |
|---------|------|---|
| 2.3.6 | Nov 2025 | Agreements & Service Catalog (pending) |
| 2.3.4-2.3.5 | Earlier | OAuth email, Software keys |
| 2.3.0 | Earlier | Base system |
| 0.2.0 | Early | Initial schema |

See `admin/database_updates.php` for sequential migrations.

---

## Files to Reference

- **Schema Definition:** `/home/chenry/projects/ITFlow/db.sql` (complete dump)
- **Migrations:** `/home/chenry/projects/ITFlow/admin/database_updates.php` (v0.2.0 → 2.3.6)
- **Version Control:** `/home/chenry/projects/ITFlow/includes/database_version.php`
- **Agreement Tables:** `/home/chenry/projects/ITFlow/migrations/001_create_agreements_tables.sql`
- **Service Catalog PRD:** `/home/chenry/projects/ITFlow/docs/PRD-Service-Catalog-Enhancement.md`

---

## Current Issues & Notes

### ⚠️ Products vs Services
Currently both stored in `products` table with `product_type` enum.
Future plan: Move services to separate `service_catalog` table (v2.4).
**Until then: Always filter by `product_type = 'product'` for products-only queries.**

### Pending Migrations
- Service catalog tables (PRD approved, implementation pending)
- Client billing contact fields
- Contact terminology updates (primary_support, technical_support)
- Invoice items service linking

---

**Last Reviewed:** November 2025
**Maintained By:** Development Team
**Source of Truth:** `/home/chenry/projects/ITFlow/db.sql`
