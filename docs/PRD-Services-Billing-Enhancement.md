# Product Requirements Document: Services & Billing Enhancement

**Document Version:** 1.0
**Date:** November 8, 2025
**Status:** Draft
**Product:** ITFlow - IT Documentation & Management Platform

---

## Executive Summary

This PRD outlines enhancements to ITFlow's Services and Billing system to create a more flexible, scalable service catalog and billing model. The enhancement will introduce a master service catalog with customizable rates, client-level service overrides, agreement-based service restrictions, and improved billing contact management.

### Key Capabilities
- **Master Service Catalog**: Company-wide service definitions with default rates in Admin section
- **Client-Level Service Customization**: Override default services and rates per client, add client-specific services
- **Agreement Service Restrictions**: Define which services are allowed on specific agreements
- **Service-Based Billing**: Invoice generation uses service-specific rates instead of flat hourly rates
- **Block Hours Per Service**: Allocate prepaid hours to specific services on block hour agreements
- **Enhanced Billing Contacts**: Dedicated billing contact separate from primary support contact, with CC email list
- **International Support**: Added support for Cayman Islands (country and currency KYD)
- **Improved Ticket Workflow**: Primary Support Contact auto-assigned to new tickets

---

## Background & Problem Statement

### Current State
ITFlow currently has:
- **Basic Services Module**: Services are defined per client with Name, Description, Category, and Importance
- **Flat Rate Billing**: Single `client_rate` field applies to all billable work for a client
- **Manual Service Setup**: Each client requires manual service entry with no templates or defaults
- **Limited Billing Contacts**: Primary Contact serves multiple roles (support and billing)
- **Single Email for Billing**: No way to CC multiple stakeholders on invoices
- **Limited Geography Support**: Missing Cayman Islands country and KYD currency

### Pain Points
1. **Inefficient Service Setup**: Technicians manually recreate common services for each new client
2. **Inconsistent Service Rates**: No standardized pricing across clients for the same service
3. **Billing Complexity**: Cannot bill different rates for different types of work (e.g., support vs. project work)
4. **Poor Agreement Control**: No way to restrict which services are billable under specific agreements
5. **Block Hours Limitations**: Cannot allocate prepaid hours to specific service types
6. **Billing Contact Confusion**: Support contact receives all billing emails, causing confusion
7. **Limited Email Distribution**: Cannot CC accounting team, managers, or other stakeholders on invoices
8. **Geographic Gaps**: Cannot properly serve Cayman Islands clients

### User Impact
- **MSP Administrators**: Spend excessive time setting up services for new clients
- **Billing Teams**: Cannot accurately bill for different service types at different rates
- **Account Managers**: Cannot configure agreements to restrict services to only covered work
- **Clients**: Receive billing communications at wrong email addresses
- **Accounting Departments**: Not included in invoice distribution automatically

---

## Goals & Objectives

### Primary Goals
1. **Standardize Service Catalog**: Create master service definitions reusable across all clients
2. **Flexible Pricing**: Support different rates for different services and service types
3. **Agreement Service Control**: Restrict which services can be billed under specific agreements
4. **Accurate Block Hours Tracking**: Track prepaid hours consumption by service type
5. **Improved Billing Communications**: Separate billing contacts from support contacts with multi-recipient support
6. **Global Expansion**: Support Cayman Islands clients properly

### Success Criteria
- [ ] Master service catalog with at least 10 default services defined
- [ ] New clients automatically inherit default services with ability to customize
- [ ] Service rates used in invoice generation instead of flat client rate
- [ ] Agreements can specify allowed services
- [ ] Block hour agreements track hours consumed per service
- [ ] Billing contacts separated from support contacts on 100% of clients
- [ ] Cayman Islands country and KYD currency available in system
- [ ] Primary Support Contact auto-assigned to new tickets

### Non-Goals (Out of Scope for v1.0)
- Service level agreement (SLA) enforcement per service
- Time-of-day rate variations (after-hours, weekend multipliers)
- Automatic service recommendations based on client industry
- Service bundling or package pricing
- Multi-currency billing on single invoice

---

## User Stories

### Service Catalog Management

**As an MSP Administrator, I want to:**
- Define a master list of services in the Admin section with Name, Description, and Rate
- Set standard rates for common services (Remote Support, Onsite Support, Project Work, etc.)
- Categorize services by type (Support, Project, Consulting, Monitoring, etc.)
- Edit master service definitions and have changes available to new clients
- Archive obsolete services while preserving historical data

**As an Account Manager, I want to:**
- See default services automatically added when creating a new client
- Override service rates at the client level to reflect negotiated pricing
- Add custom services specific to a client that don't exist in the master catalog
- Remove services from a client that they don't use
- View which clients use custom rates vs. default rates

### Agreement & Service Integration

**As an Account Manager, I want to:**
- Select which services are allowed when creating an agreement
- Restrict block hour agreements to only consume hours for specific services
- See which services are covered vs. excluded when viewing an agreement
- Configure different hour allocations per service on block hour agreements
- Prevent technicians from logging billable time against excluded services

**As a Technician, I want to:**
- See which services are available under the client's agreement when creating a ticket
- Have the system warn me if I select a service not covered by the agreement
- Log time against specific services to ensure proper billing and hour tracking
- View how many hours remain for each service on block hour agreements

### Billing & Invoicing

**As a Billing Administrator, I want to:**
- Generate invoices that use service-specific rates instead of flat client rates
- See line items on invoices broken down by service
- Track revenue by service type across all clients
- Have invoice line items show: Service Name, Hours, Rate, Total
- Configure billing contacts separate from support contacts
- Send invoices to primary billing contact with CC to additional emails

**As an Accountant, I want to:**
- Receive all invoices automatically via CC email
- Have invoices sent to dedicated billing email addresses
- See clear service descriptions on invoice line items
- Track which services generate the most revenue

### Client Setup & Contacts

**As an Account Manager, I want to:**
- Set up a "Primary Support Contact" who is the default assignee for new tickets
- Set up a separate "Billing Contact" with email address
- Add multiple CC email addresses for billing (comma-separated list)
- Have billing emails automatically sent to Billing Contact + CC list
- Have support tickets automatically assigned to Primary Support Contact

**As a Client (Billing Contact), I want to:**
- Receive all invoices and billing communications at my billing email
- Not receive support ticket notifications (those go to Support Contact)
- Have my accounting team automatically CC'd on all invoices

### International Operations

**As an Administrator, I want to:**
- Select "Cayman Islands" as a country when setting up client locations
- Select "KYD - Cayman Islands Dollar" as the currency for Cayman Islands clients
- Have invoices properly formatted with KYD currency symbol
- Ensure all geographic and currency features work for international clients

---

## Functional Requirements

### 1. Master Service Catalog

#### 1.1 Admin Service Management

**Location:** `/admin/admin_services.php` (new page)

**Features:**
- Create, Read, Update, Delete (CRUD) master service definitions
- Table view showing all master services
- Search and filter by category, status

**Master Service Fields:**
- **Service Name** (required): E.g., "Remote Support", "Onsite Visit", "Project Work"
- **Service Description** (required): Detailed description of what's included
- **Service Category** (optional): Support, Project, Consulting, Monitoring, Emergency, etc.
- **Default Rate** (required): Default hourly rate charged (e.g., $150.00)
- **Service Status** (required): Active, Archived
- **Internal Notes** (optional): Notes for internal staff only

**Master Service Examples:**
```
1. Remote Support | $125/hr | Troubleshooting and support via remote connection
2. Onsite Support | $175/hr | On-location technical support and service
3. Project Work | $150/hr | Planned project implementation and upgrades
4. Emergency Support | $225/hr | After-hours emergency response
5. Consulting | $200/hr | Strategic planning and consultation services
6. Network Monitoring | $50/hr | Proactive network monitoring and alerting
7. Security Patching | $75/hr | Routine security updates and patch management
8. Backup Management | $40/hr | Backup configuration and verification
9. User Training | $100/hr | End user training and documentation
10. Server Maintenance | $150/hr | Server updates, optimization, and maintenance
```

**Business Rules:**
- Cannot delete master service if used by any client (archive instead)
- Default rate must be greater than $0.00
- Service names must be unique in master catalog
- Archived services don't appear in new client setup but remain in existing clients

#### 1.2 Database Schema: Master Services

**New Table: `service_templates`**
```sql
CREATE TABLE service_templates (
    service_template_id INT AUTO_INCREMENT PRIMARY KEY,
    service_template_name VARCHAR(200) NOT NULL,
    service_template_description TEXT,
    service_template_category VARCHAR(100),
    service_template_rate DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    service_template_status ENUM('Active', 'Archived') DEFAULT 'Active',
    service_template_notes TEXT,
    service_template_created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    service_template_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_service_name (service_template_name),
    INDEX (service_template_category),
    INDEX (service_template_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2. Client-Level Service Management

#### 2.1 Client Service Inheritance

**Automatic Service Setup:**
- When a new client is created, automatically copy all active master services to client
- Client services start with default rates from master catalog
- Client can then customize rates, descriptions, or remove services

**Manual Override Process:**
- Edit client service to change rate (overrides master default)
- Add custom service to client (not in master catalog)
- Remove service from client (soft delete, retains history)

#### 2.2 Client Service Customization

**Client Services Page:** `/agent/services.php?client_id={id}`

**Features:**
- View all services for this client
- Badge indicating "Default Rate" vs. "Custom Rate"
- Ability to add new client-specific services
- Ability to edit rates and descriptions
- Ability to remove/archive services

**Modified Fields in `services` Table:**
```sql
ALTER TABLE services ADD COLUMN service_template_id INT AFTER service_id;
ALTER TABLE services ADD COLUMN service_rate DECIMAL(15,2) DEFAULT 0.00 AFTER service_description;
ALTER TABLE services ADD COLUMN service_is_custom_rate TINYINT(1) DEFAULT 0 AFTER service_rate;
ALTER TABLE services ADD INDEX (service_template_id);
ALTER TABLE services ADD FOREIGN KEY (service_template_id) REFERENCES service_templates(service_template_id) ON DELETE SET NULL;
```

**Field Descriptions:**
- `service_template_id`: Links to master service (NULL if client-specific custom service)
- `service_rate`: Hourly rate for this service for this client
- `service_is_custom_rate`: 1 if rate differs from master template, 0 if using default

#### 2.3 Client Service UI Changes

**Service Add Modal Updates:**
- Add "Select from Master Catalog" option (default)
- Add "Create Custom Service" option
- When selecting from master catalog, pre-populate name, description, category, rate
- Allow editing fields before saving

**Service List Display:**
- Show service rate in list view
- Badge: "Default Rate" (green) or "Custom Rate" (blue) or "Custom Service" (orange)
- Sortable by rate, category, name

### 3. Agreement Service Restrictions

#### 3.1 Agreement Service Configuration

**Agreement Service Selection:**
- When creating/editing an agreement, select which services are allowed
- Multi-select checkbox list of all client services
- Option: "All Services" or "Selected Services Only"

**Agreement Service Fields:**

Extend the `agreement_services` table from the Agreements PRD:
```sql
-- Modify agreement_services table to link to actual services
ALTER TABLE agreement_services ADD COLUMN agreement_service_service_id INT AFTER agreement_service_agreement_id;
ALTER TABLE agreement_services ADD COLUMN agreement_service_hours_allocated DECIMAL(10,2) DEFAULT 0.00;
ALTER TABLE agreement_services ADD COLUMN agreement_service_hours_used DECIMAL(10,2) DEFAULT 0.00;
ALTER TABLE agreement_services ADD INDEX (agreement_service_service_id);
ALTER TABLE agreement_services ADD FOREIGN KEY (agreement_service_service_id) REFERENCES services(service_id) ON DELETE CASCADE;
```

**Field Descriptions:**
- `agreement_service_service_id`: Links to client's service
- `agreement_service_hours_allocated`: For block hour agreements, hours allocated to this service
- `agreement_service_hours_used`: Hours consumed for this service

#### 3.2 Block Hours Per Service

**Block Hour Allocation:**
For Block Hours agreements, allocate prepaid hours across services:

**Example:**
```
Agreement: Gold Support Package - 100 hours prepaid
  - Remote Support: 60 hours allocated
  - Onsite Support: 20 hours allocated
  - Project Work: 15 hours allocated
  - Emergency Support: 5 hours allocated
```

**Business Rules:**
- Sum of allocated hours must equal or be less than total agreement hours
- Hours are consumed from service-specific allocation
- If service hours exhausted but total hours remain, allow with warning
- Track hours at service level for detailed reporting

**UI Component:**
Agreement edit modal shows service allocation table:
```
Service Name          | Rate    | Hours Allocated | Hours Used | Remaining
--------------------  | ------- | --------------- | ---------- | ---------
Remote Support        | $125/hr | 60              | 45         | 15
Onsite Support        | $175/hr | 20              | 12         | 8
Project Work          | $150/hr | 15              | 3          | 12
Emergency Support     | $225/hr | 5               | 0          | 5
--------------------  | ------- | --------------- | ---------- | ---------
TOTAL                 |         | 100             | 60         | 40
```

#### 3.3 Service Validation on Tickets

**Ticket Creation:**
- When creating ticket, select service from allowed services under agreement
- Dropdown shows only services allowed by selected agreement
- Show rate and available hours next to each service
- Warn if service has low hours remaining

**Time Logging:**
- When logging time on ticket, deduct from service-specific hours (if block hours)
- Use service rate for billing calculation
- Block time logging if service not allowed under agreement (configurable)

### 4. Invoice Generation with Service Rates

#### 4.1 Service-Based Line Items

**Invoice Line Item Changes:**

Modify `invoice_items` table:
```sql
ALTER TABLE invoice_items ADD COLUMN item_service_id INT AFTER item_id;
ALTER TABLE invoice_items ADD COLUMN item_service_rate DECIMAL(15,2) DEFAULT 0.00 AFTER item_price;
ALTER TABLE invoice_items ADD INDEX (item_service_id);
ALTER TABLE invoice_items ADD FOREIGN KEY (item_service_id) REFERENCES services(service_id) ON DELETE SET NULL;
```

**Invoice Generation Logic:**
When generating invoice from tickets:
1. Group billable time by service
2. Calculate: Service Hours × Service Rate = Line Item Total
3. Create invoice line items:
   - Description: "{Service Name} - {Hours} hours"
   - Quantity: Hours worked
   - Price: Service rate
   - Total: Hours × Rate

**Example Invoice:**
```
Invoice #INV-2025-001
Client: Acme Corporation
Date: November 8, 2025

Description                        | Qty   | Rate     | Total
---------------------------------- | ----- | -------- | ---------
Remote Support - 12.5 hours        | 12.5  | $125.00  | $1,562.50
Onsite Support - 4.0 hours         | 4.0   | $175.00  | $700.00
Project Work - 8.0 hours           | 8.0   | $150.00  | $1,200.00
---------------------------------- | ----- | -------- | ---------
                                   |       | SUBTOTAL | $3,462.50
                                   |       | TAX      | $276.20
                                   |       | TOTAL    | $3,738.70
```

#### 4.2 Billing Rate Priority

**Rate Determination Logic:**
```
When creating invoice line item:
  1. Use service_rate from services table (client-specific rate)
  2. If service_rate is 0.00, fall back to client_rate
  3. If both are 0.00, use $0.00 and log warning
```

This ensures backward compatibility with existing clients using flat rates.

### 5. Enhanced Billing Contacts

#### 5.1 Primary Support Contact Rename

**Changes:**
- Rename "Primary Contact" to "Primary Support Contact" everywhere in UI
- Label update in client add/edit modals
- Database field remains `contact_primary` (no schema change needed)
- Documentation updates

**Auto-Assignment to Tickets:**
- When creating new ticket, auto-populate "Assigned To" with Primary Support Contact
- User can override if needed
- Reduces friction in ticket creation workflow

**UI Changes:**
```
Before: "Primary Contact: John Smith"
After:  "Primary Support Contact: John Smith (Auto-assigned to new tickets)"
```

#### 5.2 Billing Contact Fields

**New Client Fields:**

Add to `clients` table:
```sql
ALTER TABLE clients ADD COLUMN client_billing_contact_name VARCHAR(200) AFTER client_rate;
ALTER TABLE clients ADD COLUMN client_billing_email VARCHAR(200) AFTER client_billing_contact_name;
ALTER TABLE clients ADD COLUMN client_billing_cc_emails TEXT AFTER client_billing_email;
ALTER TABLE clients ADD INDEX (client_billing_email);
```

**Field Descriptions:**
- `client_billing_contact_name`: Full name of billing contact person
- `client_billing_email`: Primary email for invoices and billing communications
- `client_billing_cc_emails`: Comma-separated list of additional emails to CC on invoices

**Example Data:**
```
client_billing_contact_name: "Sarah Johnson"
client_billing_email: "billing@acmecorp.com"
client_billing_cc_emails: "accounting@acmecorp.com, cfo@acmecorp.com, sarah.johnson@acmecorp.com"
```

#### 5.3 Client Add/Edit Modal Updates

**Billing Tab Enhancements:**

In `/agent/modals/client/client_add.php` and `client_edit.php`, update Billing tab:

**Add after Hourly Rate field:**
```html
<div class="form-group">
    <label>Billing Contact Name</label>
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fa fa-fw fa-file-invoice-dollar"></i></span>
        </div>
        <input type="text" class="form-control" name="billing_contact_name"
               placeholder="Billing Contact Person" maxlength="200">
    </div>
</div>

<div class="form-group">
    <label>Billing Contact Email <strong class="text-danger">*</strong></label>
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fa fa-fw fa-envelope"></i></span>
        </div>
        <input type="email" class="form-control" name="billing_email"
               placeholder="Primary billing email address" maxlength="200" required>
    </div>
    <small class="form-text text-muted">Invoices will be sent to this email</small>
</div>

<div class="form-group">
    <label>Additional Billing Emails (CC)</label>
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fa fa-fw fa-envelope"></i></span>
        </div>
        <textarea class="form-control" name="billing_cc_emails" rows="2"
                  placeholder="Separate multiple emails with commas"></textarea>
    </div>
    <small class="form-text text-muted">Example: accounting@client.com, cfo@client.com</small>
</div>
```

#### 5.4 Invoice Email Distribution

**Email Sending Logic:**

When sending invoice email:
```php
function emailInvoiceToClient($invoice_id) {
    $invoice = getInvoice($invoice_id);
    $client = getClient($invoice['invoice_client_id']);

    // Primary recipient
    $to = $client['client_billing_email'];

    // CC recipients
    $cc = [];
    if (!empty($client['client_billing_cc_emails'])) {
        $cc_raw = $client['client_billing_cc_emails'];
        $cc = array_map('trim', explode(',', $cc_raw));
        // Validate each email
        $cc = array_filter($cc, 'filter_var', FILTER_VALIDATE_EMAIL);
    }

    // Send email with CC
    sendEmail($to, $subject, $body, $cc);
}
```

**Business Rules:**
- If `client_billing_email` is empty, fall back to primary contact email
- Invalid CC emails are silently skipped (logged for admin review)
- Maximum 10 CC recipients to prevent abuse
- Each CC recipient receives full invoice (not BCC)

### 6. International Support: Cayman Islands

#### 6.1 Country Addition

**Update Countries Array:**

In `/includes/config/config.php` or equivalent countries definition file:

Add to `$countries_array`:
```php
'Cayman Islands',
```

Position alphabetically between "Canada" and "Central African Republic".

**Database Update:**
No schema change needed - countries stored as VARCHAR.

**Validation:**
Ensure country dropdown shows "Cayman Islands" in:
- Client add/edit location tab
- Location add/edit modals
- Vendor locations
- Company settings

#### 6.2 Currency Addition: KYD

**Update Currencies Array:**

In `/includes/config/config.php` or equivalent currencies definition file:

Add to `$currencies_array`:
```php
'KYD' => 'Cayman Islands Dollar',
```

**Currency Symbol:**
- Symbol: $ (same as USD)
- Code: KYD
- Position: Prefix (e.g., "$500.00 KYD" or "KYD $500.00")

**Invoice Display:**
When invoice currency is KYD, show:
```
Total: $3,500.00 KYD
```

**Validation:**
Ensure currency dropdown shows "KYD - Cayman Islands Dollar" in:
- Client add/edit billing tab
- Invoice settings
- Quote settings
- Company settings

---

## Technical Requirements

### 7. Database Schema Summary

#### 7.1 New Tables

**`service_templates` Table** (Master Service Catalog)
```sql
CREATE TABLE service_templates (
    service_template_id INT AUTO_INCREMENT PRIMARY KEY,
    service_template_name VARCHAR(200) NOT NULL,
    service_template_description TEXT,
    service_template_category VARCHAR(100),
    service_template_rate DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    service_template_status ENUM('Active', 'Archived') DEFAULT 'Active',
    service_template_notes TEXT,
    service_template_created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    service_template_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_service_name (service_template_name),
    INDEX (service_template_category),
    INDEX (service_template_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 7.2 Modified Tables

**`services` Table** (Client Services)
```sql
ALTER TABLE services ADD COLUMN service_template_id INT AFTER service_id;
ALTER TABLE services ADD COLUMN service_rate DECIMAL(15,2) DEFAULT 0.00 AFTER service_description;
ALTER TABLE services ADD COLUMN service_is_custom_rate TINYINT(1) DEFAULT 0 AFTER service_rate;
ALTER TABLE services ADD INDEX (service_template_id);
ALTER TABLE services ADD FOREIGN KEY (service_template_id)
    REFERENCES service_templates(service_template_id) ON DELETE SET NULL;
```

**`clients` Table** (Billing Contacts)
```sql
ALTER TABLE clients ADD COLUMN client_billing_contact_name VARCHAR(200) AFTER client_rate;
ALTER TABLE clients ADD COLUMN client_billing_email VARCHAR(200) AFTER client_billing_contact_name;
ALTER TABLE clients ADD COLUMN client_billing_cc_emails TEXT AFTER client_billing_email;
ALTER TABLE clients ADD INDEX (client_billing_email);
```

**`agreement_services` Table** (Service Hours Allocation)
```sql
ALTER TABLE agreement_services ADD COLUMN agreement_service_service_id INT
    AFTER agreement_service_agreement_id;
ALTER TABLE agreement_services ADD COLUMN agreement_service_hours_allocated DECIMAL(10,2) DEFAULT 0.00;
ALTER TABLE agreement_services ADD COLUMN agreement_service_hours_used DECIMAL(10,2) DEFAULT 0.00;
ALTER TABLE agreement_services ADD INDEX (agreement_service_service_id);
ALTER TABLE agreement_services ADD FOREIGN KEY (agreement_service_service_id)
    REFERENCES services(service_id) ON DELETE CASCADE;
```

**`invoice_items` Table** (Service Rate Tracking)
```sql
ALTER TABLE invoice_items ADD COLUMN item_service_id INT AFTER item_id;
ALTER TABLE invoice_items ADD COLUMN item_service_rate DECIMAL(15,2) DEFAULT 0.00 AFTER item_price;
ALTER TABLE invoice_items ADD INDEX (item_service_id);
ALTER TABLE invoice_items ADD FOREIGN KEY (item_service_id)
    REFERENCES services(service_id) ON DELETE SET NULL;
```

**`tickets` Table** (Service Assignment)
```sql
ALTER TABLE tickets ADD COLUMN ticket_service_id INT AFTER ticket_agreement_id;
ALTER TABLE tickets ADD INDEX (ticket_service_id);
ALTER TABLE tickets ADD FOREIGN KEY (ticket_service_id)
    REFERENCES services(service_id) ON DELETE SET NULL;
```

### 8. Business Logic Requirements

#### 8.1 Service Inheritance on Client Creation

**Process:**
```php
function createClientServices($client_id) {
    // Get all active master services
    $master_services = mysqli_query($mysqli,
        "SELECT * FROM service_templates
         WHERE service_template_status = 'Active'");

    while ($template = mysqli_fetch_assoc($master_services)) {
        // Create client service from template
        mysqli_query($mysqli,
            "INSERT INTO services SET
             service_client_id = $client_id,
             service_template_id = {$template['service_template_id']},
             service_name = '{$template['service_template_name']}',
             service_description = '{$template['service_template_description']}',
             service_category = '{$template['service_template_category']}',
             service_rate = {$template['service_template_rate']},
             service_is_custom_rate = 0");
    }
}
```

**Trigger:** Called automatically after successful client creation.

#### 8.2 Service Rate Calculation

**Invoice Line Item Rate Logic:**
```php
function getServiceBillingRate($service_id, $client_id) {
    $service = getService($service_id);

    // Priority 1: Client service rate
    if ($service['service_rate'] > 0) {
        return $service['service_rate'];
    }

    // Priority 2: Master template rate
    if ($service['service_template_id']) {
        $template = getServiceTemplate($service['service_template_id']);
        if ($template['service_template_rate'] > 0) {
            return $template['service_template_rate'];
        }
    }

    // Priority 3: Client hourly rate (fallback)
    $client = getClient($client_id);
    return $client['client_rate'];
}
```

#### 8.3 Block Hours Per Service Tracking

**Hour Deduction Logic:**
```php
function deductServiceHours($ticket_id, $hours_worked) {
    $ticket = getTicket($ticket_id);
    $agreement_id = $ticket['ticket_agreement_id'];
    $service_id = $ticket['ticket_service_id'];

    // Get service allocation for this agreement
    $allocation = mysqli_query($mysqli,
        "SELECT * FROM agreement_services
         WHERE agreement_service_agreement_id = $agreement_id
         AND agreement_service_service_id = $service_id");

    if ($row = mysqli_fetch_assoc($allocation)) {
        $hours_allocated = $row['agreement_service_hours_allocated'];
        $hours_used = $row['agreement_service_hours_used'];
        $hours_remaining = $hours_allocated - $hours_used;

        if ($hours_remaining > 0) {
            $to_deduct = min($hours_worked, $hours_remaining);

            // Update hours used for this service
            mysqli_query($mysqli,
                "UPDATE agreement_services SET
                 agreement_service_hours_used = agreement_service_hours_used + $to_deduct
                 WHERE agreement_service_id = {$row['agreement_service_id']}");

            // Also update total agreement hours
            updateAgreementHoursUsed($agreement_id, $to_deduct);

            // Check if service hours low
            if (($hours_remaining - $to_deduct) / $hours_allocated < 0.15) {
                notifyLowServiceHours($agreement_id, $service_id);
            }
        }
    }
}
```

#### 8.4 Billing Email Distribution

**Email Helper Function:**
```php
function sendInvoiceEmail($invoice_id) {
    $invoice = getInvoice($invoice_id);
    $client = getClient($invoice['invoice_client_id']);

    // Primary billing email
    $to = !empty($client['client_billing_email'])
        ? $client['client_billing_email']
        : getPrimaryContactEmail($invoice['invoice_client_id']);

    // CC emails
    $cc_list = [];
    if (!empty($client['client_billing_cc_emails'])) {
        $cc_raw = explode(',', $client['client_billing_cc_emails']);
        foreach ($cc_raw as $email) {
            $email = trim($email);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $cc_list[] = $email;
            }
        }
    }

    // Limit CC recipients
    $cc_list = array_slice($cc_list, 0, 10);

    // Build and send email
    $subject = "Invoice {$invoice['invoice_prefix']}{$invoice['invoice_number']} from {$company_name}";
    $body = generateInvoiceEmailBody($invoice);

    sendEmailWithCC($to, $subject, $body, $cc_list, $invoice_pdf_path);

    // Log email sent
    logInvoiceEmail($invoice_id, $to, $cc_list);
}
```

### 9. User Interface Requirements

#### 9.1 Admin Service Management Page

**New Page:** `/admin/admin_services.php`

**Layout:**
```
┌─────────────────────────────────────────────────────────────┐
│ Admin > Master Service Catalog                   [+ New Service] │
├─────────────────────────────────────────────────────────────┤
│ Search: [____________]  Category: [All ▾]  Status: [Active ▾]│
├──────────────┬────────────────┬──────────┬──────────┬────────┤
│ Service Name │ Category       │ Rate     │ Status   │ Action │
├──────────────┼────────────────┼──────────┼──────────┼────────┤
│ Remote Supp. │ Support        │ $125.00  │ Active   │ ⋮      │
│ Onsite Supp. │ Support        │ $175.00  │ Active   │ ⋮      │
│ Project Work │ Project        │ $150.00  │ Active   │ ⋮      │
│ Emergency    │ Emergency      │ $225.00  │ Active   │ ⋮      │
│ Consulting   │ Consulting     │ $200.00  │ Active   │ ⋮      │
└──────────────┴────────────────┴──────────┴──────────┴────────┘
```

**Features:**
- Sortable columns
- Inline edit for quick rate changes
- Bulk archive/activate
- Usage count (how many clients use this service)

#### 9.2 Client Service List Enhancements

**Enhanced View:** `/agent/services.php?client_id={id}`

**Service Card/Row Display:**
```
┌─────────────────────────────────────────────────────────────┐
│ Remote Support                              [Default Rate ✓] │
│ Troubleshooting via remote connection                       │
│ Category: Support | Rate: $125.00/hr | Importance: High     │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ VIP Support                                  [Custom Service] │
│ Dedicated VIP support channel                               │
│ Category: Support | Rate: $200.00/hr | Importance: High     │
└─────────────────────────────────────────────────────────────┘
```

**Badges:**
- Green badge: "Default Rate" (using master template rate)
- Blue badge: "Custom Rate" (client-specific rate override)
- Orange badge: "Custom Service" (not in master catalog)

#### 9.3 Agreement Service Configuration UI

**Agreement Edit Modal - Services Tab:**

```
┌─────────────────────────────────────────────────────────────┐
│ Agreement: Gold Support Package                             │
│ Tab: [Details] [Services] [Coverage] [Billing]              │
├─────────────────────────────────────────────────────────────┤
│ Allowed Services:                                           │
│ ☐ All Services                                              │
│ ☑ Selected Services Only                                    │
│                                                             │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ ☑ Remote Support         $125/hr   [60] hours allocated│ │
│ │ ☑ Onsite Support         $175/hr   [20] hours allocated│ │
│ │ ☑ Project Work           $150/hr   [15] hours allocated│ │
│ │ ☑ Emergency Support      $225/hr   [5 ] hours allocated│ │
│ │ ☐ Consulting             $200/hr                        │ │
│ │ ☐ Network Monitoring     $50/hr                         │ │
│ └─────────────────────────────────────────────────────────┘ │
│                                                             │
│ Total Hours Allocated: 100 / 100 (Agreement Total)          │
│ [  Save  ] [Cancel]                                         │
└─────────────────────────────────────────────────────────────┘
```

**Business Rules:**
- If "All Services" selected, all checkboxes checked and disabled
- If "Selected Services Only", user manually checks services
- For block hours agreements, show hours allocation input
- Sum of allocated hours cannot exceed agreement total hours
- Real-time validation and warning messages

#### 9.4 Ticket Service Selection

**Ticket Add/Edit Modal Update:**

Add after Agreement field:
```html
<div class="form-group">
    <label>Service <strong class="text-danger">*</strong></label>
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fa fa-fw fa-stream"></i></span>
        </div>
        <select class="form-control select2" name="service_id" required>
            <option value="">- Select Service -</option>
            <!-- Populated based on selected agreement's allowed services -->
        </select>
    </div>
    <small class="form-text text-muted">
        <span id="serviceRateDisplay"></span>
        <span id="serviceHoursDisplay"></span>
    </small>
</div>
```

**Dynamic Display:**
When service selected, show below dropdown:
```
Rate: $125.00/hr | Hours Remaining: 45 of 60 (75%)
```

Color-coded:
- Green: > 25% hours remaining
- Yellow: 10-25% hours remaining
- Red: < 10% hours remaining

#### 9.5 Invoice Template Enhancements

**Invoice Display:**

Service-based line items:
```
┌──────────────────────────────────────────────────────────────┐
│ INVOICE #INV-2025-001                                        │
│ Client: Acme Corporation                                     │
│ Date: November 8, 2025 | Due: December 8, 2025              │
├──────────────────────────────────────────────────────────────┤
│ Service Description           Qty    Rate      Total         │
├──────────────────────────────────────────────────────────────┤
│ Remote Support                12.5   $125.00   $1,562.50     │
│   Tickets: #1234, #1235, #1238                               │
│                                                              │
│ Onsite Support                 4.0   $175.00     $700.00     │
│   Tickets: #1236                                             │
│                                                              │
│ Project Work                   8.0   $150.00   $1,200.00     │
│   Tickets: #1237, #1239                                      │
├──────────────────────────────────────────────────────────────┤
│                                        SUBTOTAL  $3,462.50    │
│                                        TAX (8%)    $276.20    │
│                                        TOTAL     $3,738.70    │
└──────────────────────────────────────────────────────────────┘
```

**Features:**
- Group by service automatically
- Show related ticket numbers
- Display service-specific rates
- Calculate subtotals per service

#### 9.6 Client Billing Tab Redesign

**Client Add/Edit - Billing Tab:**

```
┌─────────────────────────────────────────────────────────────┐
│ Billing Information                                         │
├─────────────────────────────────────────────────────────────┤
│ Hourly Rate (Default)                                       │
│ $ [150.00    ]                                              │
│                                                             │
│ Currency *                                                  │
│ [USD - United States Dollar ▾]                              │
│                                                             │
│ Payment Terms                                               │
│ [Net 30 ▾]                                                  │
│                                                             │
│ Tax ID                                                      │
│ [________________]                                          │
│                                                             │
│ ─────────────── Billing Contact ───────────────             │
│                                                             │
│ Billing Contact Name                                        │
│ [Sarah Johnson          ]                                   │
│                                                             │
│ Billing Contact Email *                                     │
│ [billing@acmecorp.com   ]                                   │
│ Invoices will be sent to this email                         │
│                                                             │
│ Additional Billing Emails (CC)                              │
│ [accounting@acmecorp.com, cfo@acmecorp.com]                │
│ Separate multiple emails with commas                        │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### 10. Reporting Requirements

#### 10.1 Service Revenue Report

**New Report:** `/agent/reports/revenue_by_service.php`

**Display:**
- Revenue breakdown by service across all clients
- Date range filter
- Group by service, show total hours and revenue
- Pie chart showing revenue distribution
- Table showing top services by revenue

**Metrics:**
- Total hours billed per service
- Total revenue per service
- Average rate per service
- Client count using each service

#### 10.2 Agreement Service Utilization Report

**New Report:** `/agent/reports/agreement_service_utilization.php`

**Display:**
- For block hours agreements, show hours used per service
- Filter by agreement, client, date range
- Warning indicators for services near exhaustion
- Projected exhaustion date per service

**Example:**
```
Agreement: Gold Support - Acme Corp
Period: October 2025

Service           | Allocated | Used  | Remaining | % Used | Proj. Exhaustion
----------------- | --------- | ----- | --------- | ------ | ----------------
Remote Support    | 60        | 45    | 15        | 75%    | Nov 15, 2025
Onsite Support    | 20        | 12    | 8         | 60%    | Dec 20, 2025
Project Work      | 15        | 3     | 12        | 20%    | Feb 10, 2026
Emergency         | 5         | 0     | 5         | 0%     | N/A
```

#### 10.3 Client Service Rate Audit Report

**New Report:** `/agent/reports/client_service_rates.php`

**Purpose:** Identify clients with custom rates vs. default rates

**Display:**
- List all clients
- Show services with custom rates highlighted
- Compare custom rate to master rate
- Calculate revenue impact of custom pricing

**Use Case:** Identify clients due for rate increases or pricing standardization

---

## Implementation Phases

### Phase 1: Master Service Catalog (Week 1)
**Goal:** Create admin service management foundation

- [ ] Create `service_templates` database table
- [ ] Build `/admin/admin_services.php` page
- [ ] Create service template add/edit/delete modals
- [ ] Build service template CRUD operations
- [ ] Seed database with 10 default service templates
- [ ] Add validation and error handling

**Deliverable:** Admin can manage master service catalog

---

### Phase 2: Client Service Inheritance (Week 2)
**Goal:** Automatic service setup for new clients

- [ ] Modify `services` table schema (add template_id, rate fields)
- [ ] Build service inheritance function triggered on client creation
- [ ] Update client creation workflow to copy master services
- [ ] Modify `/agent/services.php` to show rate and template link
- [ ] Add "Custom Rate" vs. "Default Rate" badges
- [ ] Build service rate override functionality
- [ ] Test with new client creation

**Deliverable:** New clients automatically get default services; rates can be customized

---

### Phase 3: Billing Contact Enhancement (Week 3)
**Goal:** Separate billing from support contacts

- [ ] Add billing contact fields to `clients` table
- [ ] Update client add/edit modals with billing fields
- [ ] Rename "Primary Contact" to "Primary Support Contact" in UI
- [ ] Add email validation for CC list
- [ ] Update invoice email function to use billing contacts
- [ ] Build CC email parsing and validation
- [ ] Test email distribution with multiple recipients

**Deliverable:** Invoices sent to dedicated billing contacts with CC capability

---

### Phase 4: International Support (Week 3)
**Goal:** Add Cayman Islands support

- [ ] Add "Cayman Islands" to countries array
- [ ] Add "KYD - Cayman Islands Dollar" to currencies array
- [ ] Test country selection in client/location forms
- [ ] Test currency selection and display
- [ ] Verify invoice formatting with KYD currency
- [ ] Update documentation

**Deliverable:** Cayman Islands country and KYD currency fully supported

---

### Phase 5: Agreement Service Integration (Week 4-5)
**Goal:** Link services to agreements with hour allocation

- [ ] Modify `agreement_services` table schema
- [ ] Build agreement service selection UI
- [ ] Create service hours allocation interface
- [ ] Implement validation (total hours = allocation sum)
- [ ] Build service filtering logic for ticket creation
- [ ] Update ticket modal to show only allowed services
- [ ] Implement service hours deduction logic
- [ ] Create low service hours notifications

**Deliverable:** Agreements restrict which services are allowed and track hours per service

---

### Phase 6: Service-Based Billing (Week 6)
**Goal:** Invoice generation uses service rates

- [ ] Modify `invoice_items` table schema
- [ ] Modify `tickets` table to store service_id
- [ ] Update ticket add/edit to require service selection
- [ ] Build service rate calculation logic
- [ ] Update invoice generation to group by service
- [ ] Modify invoice templates to show service breakdown
- [ ] Test invoice generation with multiple services
- [ ] Ensure backward compatibility with flat-rate clients

**Deliverable:** Invoices generated with service-specific rates and line items

---

### Phase 7: Reporting & Analytics (Week 7)
**Goal:** Service revenue and utilization reports

- [ ] Build Revenue by Service report
- [ ] Build Agreement Service Utilization report
- [ ] Build Client Service Rate Audit report
- [ ] Add charts and visualizations
- [ ] Create exportable CSV/PDF versions
- [ ] Add filters (date range, client, service, agreement)
- [ ] Build dashboard widgets for service metrics

**Deliverable:** Comprehensive service and billing analytics

---

### Phase 8: Testing & Refinement (Week 8)
**Goal:** Production readiness

- [ ] End-to-end testing of all workflows
- [ ] Test service inheritance on new clients
- [ ] Test agreement service restrictions
- [ ] Test invoice generation with service rates
- [ ] Test billing contact email distribution
- [ ] Test block hours per service tracking
- [ ] UI/UX refinements
- [ ] Performance testing
- [ ] Documentation updates

**Deliverable:** Fully tested, production-ready system

---

### Phase 9: Migration & Deployment (Week 9)
**Goal:** Deploy to production

- [ ] Create migration scripts for existing data
- [ ] Backfill existing clients with default services
- [ ] Create training materials
- [ ] Train admin staff on master service catalog
- [ ] Train billing team on new workflows
- [ ] Soft launch with pilot clients
- [ ] Monitor and address issues
- [ ] Full rollout

**Deliverable:** Live production system with user training

---

## Success Metrics

### Quantitative Metrics

**Adoption:**
- [ ] 100% of new clients have services auto-populated
- [ ] 80% of active clients using service-based billing within 60 days
- [ ] 90% of invoices include service breakdown within 90 days

**Efficiency:**
- [ ] 70% reduction in time to set up services for new clients
- [ ] 50% reduction in billing errors related to incorrect rates
- [ ] 60% reduction in invoice email routing errors

**Revenue:**
- [ ] 15% increase in billing accuracy (services billed at correct rates)
- [ ] 10% increase in revenue from corrected service rates
- [ ] 100% of billing contacts receive invoices (vs. support contacts)

**Data Quality:**
- [ ] 100% of new tickets assigned to services
- [ ] 95% of invoice line items linked to services
- [ ] 90% of clients have dedicated billing contacts configured

### Qualitative Metrics

**Internal Feedback:**
- Billing team reports easier invoice generation
- Technicians appreciate service selection clarity
- Account managers report better agreement control
- Administrators save time with default service templates

**Client Feedback:**
- Clients appreciate detailed service breakdown on invoices
- Billing departments receive invoices at correct addresses
- Accounting teams appreciate CC functionality
- International clients (Cayman Islands) properly supported

---

## Risk Assessment

### High Risk

**Risk:** Service rate changes affect historical invoices or billing
**Mitigation:**
- Store `item_service_rate` on invoice line items (snapshot at invoice time)
- Changing master service rate only affects future new client services
- Historical invoices remain unchanged
- Clear documentation on rate change impacts

**Risk:** Complex service allocation confuses users
**Mitigation:**
- Start with simple "All Services" option as default
- Make service allocation optional (advanced feature)
- Provide templates for common allocation patterns
- Video tutorials and documentation

**Risk:** Email CC list abused or invalid emails cause delivery failures
**Mitigation:**
- Validate all email addresses before saving
- Limit CC recipients to 10 maximum
- Silent skip invalid emails with admin notification
- Log all email attempts for troubleshooting

### Medium Risk

**Risk:** Service inheritance creates unwanted services for clients
**Mitigation:**
- Make service inheritance optional (toggle in admin settings)
- Allow bulk delete of services during client setup
- Provide "Common Service Sets" instead of "All Services"
- Easy service removal process

**Risk:** Ticket service selection slows down ticket creation
**Mitigation:**
- Auto-select service if only one allowed under agreement
- Remember last-used service per client
- Make service optional for internal tickets
- Keyboard shortcuts for common services

**Risk:** Billing contact separation causes confusion
**Mitigation:**
- Clear labeling: "Primary Support Contact" vs. "Billing Contact"
- Help text explaining the difference
- Allow same person for both roles
- Migration guide for existing clients

### Low Risk

**Risk:** International features (Cayman Islands) insufficient
**Mitigation:**
- Validate requirements with actual Cayman Islands users
- Research currency formatting standards
- Test timezone and date formatting
- Provide localization options if needed

---

## Open Questions & Decisions Needed

1. **Service Templates vs. Service Categories:**
   - Should we create service categories as a separate entity, or keep as simple text field?
   - **Recommendation:** Keep as text field for v1.0; upgrade to category entity in v2.0

2. **Service Rate Override Inheritance:**
   - When master service rate changes, should existing client services with default rate update automatically?
   - **Recommendation:** No auto-update; show notification suggesting rate review

3. **Ticket Service Requirement:**
   - Should all tickets require service selection, or only billable tickets?
   - **Recommendation:** Only require for billable tickets; optional for internal

4. **Block Hours Service Allocation:**
   - Should unallocated hours be usable across all services, or locked per service?
   - **Recommendation:** Allow "Unallocated" pool usable for any service; configurable

5. **Billing Contact Migration:**
   - For existing clients, auto-populate billing contact from primary contact?
   - **Recommendation:** Yes, auto-populate as default; admin can customize afterward

6. **Service Archive vs. Delete:**
   - Can users permanently delete services, or only archive?
   - **Recommendation:** Archive only if used in any ticket/invoice; delete if never used

7. **Multi-Currency Support:**
   - Should invoices support multiple currencies on line items?
   - **Recommendation:** Out of scope for v1.0; single currency per invoice

8. **Service Rate History:**
   - Should system track historical service rate changes?
   - **Recommendation:** Yes, add `service_rate_history` table for audit trail

---

## Appendix

### A. Glossary

- **Master Service Catalog**: Company-wide list of services available to all clients
- **Service Template**: Master service definition used to create client services
- **Client Service**: Service instance for a specific client, can have custom rate
- **Default Rate**: Rate inherited from master service template
- **Custom Rate**: Client-specific rate that overrides the master rate
- **Service Allocation**: Designation of prepaid hours to specific services on block hour agreements
- **Billing Contact**: Person who receives invoices and billing communications
- **Primary Support Contact**: Person who receives support tickets and is default assignee
- **CC Emails**: Carbon copy recipients for invoice emails (additional stakeholders)

### B. Example Scenarios

**Scenario 1: New Client Setup**
1. Admin creates client "Acme Corp"
2. System automatically creates 10 services from master catalog
3. Admin reviews services, customizes "Remote Support" rate from $125 to $110 (negotiated rate)
4. Admin adds custom service "Executive Support - $250/hr" specific to Acme
5. Client now has 11 services (10 default + 1 custom)

**Scenario 2: Block Hours Agreement with Service Allocation**
1. Create "Gold Support" agreement for Acme Corp - 100 hours prepaid
2. Allocate hours: Remote Support (60), Onsite (20), Project (15), Emergency (5)
3. Technician creates ticket, selects "Remote Support" service
4. Technician logs 3 hours on ticket
5. System deducts 3 hours from Remote Support allocation (60 → 57)
6. System deducts 3 hours from total agreement hours (100 → 97)

**Scenario 3: Invoice with Service Rates**
1. Client has 3 billable tickets this month
2. Ticket #1: 10 hrs Remote Support @ $110/hr = $1,100
3. Ticket #2: 4 hrs Onsite Support @ $175/hr = $700
4. Ticket #3: 5 hrs Project Work @ $150/hr = $750
5. Invoice generated with 3 line items totaling $2,550
6. Invoice sent to billing@acmecorp.com
7. CC'd to accounting@acmecorp.com and cfo@acmecorp.com

**Scenario 4: Cayman Islands Client**
1. Create client "Caribbean Tech Ltd"
2. Location: Grand Cayman, Cayman Islands
3. Currency: KYD - Cayman Islands Dollar
4. Invoice displays: "Total: $5,000.00 KYD"
5. All billing properly formatted for Cayman Islands business

### C. Default Master Services (Seed Data)

```sql
INSERT INTO service_templates (service_template_name, service_template_description, service_template_category, service_template_rate) VALUES
('Remote Support', 'Technical support and troubleshooting via remote connection', 'Support', 125.00),
('Onsite Support', 'On-location technical support and service calls', 'Support', 175.00),
('Project Work', 'Planned project implementation, upgrades, and installations', 'Project', 150.00),
('Emergency Support', 'After-hours emergency response and critical issue resolution', 'Emergency', 225.00),
('Consulting', 'Strategic IT planning, consulting, and advisory services', 'Consulting', 200.00),
('Network Monitoring', 'Proactive network monitoring, alerting, and management', 'Monitoring', 50.00),
('Security Patching', 'Security updates, patch management, and vulnerability remediation', 'Maintenance', 75.00),
('Backup Management', 'Backup configuration, monitoring, testing, and restoration', 'Maintenance', 40.00),
('User Training', 'End user training, documentation, and knowledge transfer', 'Training', 100.00),
('Server Maintenance', 'Server updates, optimization, performance tuning, and maintenance', 'Maintenance', 150.00);
```

### D. Related Documentation

- **Agreements Feature PRD**: `/docs/PRD-Agreements-Feature.md` (related functionality)
- **ITFlow Database Schema**: `/db.sql`
- **API Documentation**: `/api/README.md` (if applicable)
- **Email System Documentation**: `/docs/email-system.md` (to be created)

### E. UI/UX Mockups Reference

- Master Service Catalog Admin Page: Wireframe TBD
- Client Service List with Badges: Wireframe TBD
- Agreement Service Allocation Interface: Wireframe TBD
- Client Billing Tab Redesign: Wireframe TBD
- Service-Based Invoice Template: Wireframe TBD

---

## Document Sign-off

| Role | Name | Date | Signature |
|------|------|------|-----------|
| Product Owner | | | |
| Technical Lead | | | |
| Billing Manager | | | |
| QA Lead | | | |

---

## Revision History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2025-11-08 | Claude AI | Initial draft - Services & Billing Enhancement PRD |

---

**End of Document**
