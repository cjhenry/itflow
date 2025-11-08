# Product Requirements Document: Agreements Feature

**Document Version:** 1.0
**Date:** November 7, 2025
**Status:** Draft
**Product:** ITFlow - IT Documentation & Management Platform

---

## Executive Summary

This PRD outlines the addition of an **Agreements** module to ITFlow's billing system. The Agreements feature will transform ITFlow from a simple time-and-materials billing system into a comprehensive managed services platform that supports multiple contract types including fixed-price agreements, block hour retainers, and time-and-materials contracts.

### Key Capabilities
- Service contract management with defined start/end dates
- Multiple billing models: Fixed Price, Block Hours, Time & Materials
- Asset coverage tracking (which devices/services are covered)
- Mandatory ticket-to-agreement assignment for better tracking
- Automated recurring invoice generation
- Hour consumption tracking with low-balance notifications
- Rate tier management based on ticket types
- Client portal visibility for agreement details and usage

---

## Background & Problem Statement

### Current State
ITFlow currently operates with:
- **Manual billing process**: Each ticket must be manually converted to an invoice
- **Flat hourly rates**: Single `client_rate` per client with no service tier differentiation
- **No contract tracking**: No way to track prepaid hours, included services, or contract terms
- **Disconnected recurring invoices**: Recurring invoices exist but aren't tied to service agreements
- **No hour consumption tracking**: Cannot track remaining hours on block-hour retainers
- **Limited ticket categorization**: No structured way to determine what work is covered vs billable

### Pain Points
1. **MSP workflow doesn't match current capabilities**: Many IT service providers operate on managed service agreements with monthly recurring fees and included support hours
2. **No prepaid hour tracking**: Block hour packages are sold but tracked manually in spreadsheets
3. **Revenue leakage**: Work performed under agreements is sometimes billed incorrectly or not tracked
4. **Poor visibility**: Clients can't see their agreement status, remaining hours, or coverage
5. **Administrative overhead**: Manual tracking of contract terms, renewal dates, and billing
6. **Inconsistent pricing**: No systematic way to apply different rates based on service type or contract tier

### User Impact
- **MSP Technicians**: Need to know if ticket work is covered before starting
- **Billing Administrators**: Waste time manually tracking agreements and calculating usage
- **Account Managers**: Cannot easily identify contracts nearing renewal or hour exhaustion
- **Clients**: Have no transparency into their agreement usage or remaining hours

---

## Goals & Objectives

### Primary Goals
1. **Enable MSP Business Model**: Support recurring revenue contracts with included services
2. **Automate Billing**: Automatically generate invoices based on agreement terms
3. **Track Hour Consumption**: Real-time visibility into used vs. remaining hours
4. **Improve Accuracy**: Ensure all work is properly categorized and billed per agreement terms
5. **Increase Transparency**: Give clients visibility into their agreements and usage

### Success Criteria
- [ ] 100% of tickets must be assigned to an agreement
- [ ] Recurring invoices automatically generated for active fixed-price agreements
- [ ] Hour consumption tracked in real-time for block-hour agreements
- [ ] Notifications sent when agreement hours reach 15% remaining threshold
- [ ] Overage work properly billed at defined rates
- [ ] Agreement details visible in client portal
- [ ] Average time to invoice reduced by 70%

### Non-Goals (Out of Scope for v1.0)
- SLA response time tracking and enforcement
- Multi-tier escalation workflows
- Profitability analysis by agreement
- Automated agreement renewals (auto-renew will create draft, not auto-send)
- Integration with third-party PSA tools

---

## User Stories

### Agreement Management

**As an Account Manager, I want to:**
- Create a new agreement for a client specifying the type, duration, and terms
- Define which assets/services are covered under the agreement
- Set up monthly recurring billing for fixed-price MSP contracts
- Configure block hour packages with a defined number of hours
- View all agreements with their status (Active, Expired, Pending Renewal)
- Receive notifications 60 days before agreement expiration
- Clone an expiring agreement to create a renewal - nice to have but i can just extend the end date as well

**As a Technician, I want to:**
- See which agreements a client has when viewing/creating tickets
- Know if my ticket work is covered under an agreement or billable
- See remaining hours on block-hour agreements before starting work
- Select an agreement when creating a ticket (required field). One agreement is set as default.
- View agreement coverage details (what assets/services are included)

**As a Billing Administrator, I want to:**
- View agreement consumption reports (hours used, hours remaining)
- See all tickets associated with an agreement
- Identify agreements that have exceeded their included hours
- Generate invoices for overage work automatically
- Review monthly recurring invoices before they're sent to clients
- Track revenue by agreement type and status

### Ticket-to-Agreement Workflow

**As a Technician, I want to:**
- Be required to select an agreement when creating any ticket
- Have the system suggest the appropriate agreement based on the asset or ticket type
- Log time against a ticket and have it automatically deducted from agreement hours (for block-hour agreements)
- See a warning if an agreement has low hours before logging time
- Override billable status for special circumstances (with permission)

**As a Client Portal User, I want to:**
- View all my active agreements with start/end dates
- See how many hours I've used and how many remain on block-hour agreements
- View which assets are covered under each agreement
- See a history of tickets logged against each agreement
- Download monthly usage reports
- Receive email notifications when my hours are running low (15% threshold)

### Billing Automation

**As a Billing Administrator, I want to:**
- Have fixed-price agreement invoices automatically generated monthly/quarterly/annually
- Have overage hours on block-hour agreements automatically invoiced at the defined rate
- Apply different rates based on ticket type (e.g., emergency after-hours = 1.5x rate)
- Have invoices include agreement reference and hour consumption details
- See draft invoices before they're sent for review
- Track which agreements are most profitable

**As an Account Manager, I want to:**
- Be notified when a client's block-hour agreement reaches 85% utilization
- Be notified again at 100% utilization (hours exhausted)
- See projected exhaustion date based on historical usage patterns
- View agreement renewal pipeline with revenue projections

---

## Functional Requirements

### 1. Agreement Core Entity

#### 1.1 Agreement Types
The system must support the following agreement types:

| Type | Description | Billing Behavior |
|------|-------------|------------------|
| **Fixed Price - Monthly** | Client pays fixed monthly fee for included services | Auto-generate invoice monthly on anniversary date |
| **Fixed Price - Quarterly** | Client pays fixed quarterly fee | Auto-generate invoice quarterly |
| **Fixed Price - Annually** | Client pays fixed annual fee | Auto-generate invoice annually |
| **Block Hours - Prepaid** | Client purchases block of hours upfront | Invoice upfront; track consumption; invoice overage separately |
| **Block Hours - Monthly Drawdown** | Client gets X hours per month; unused expire | Monthly invoice for base; track usage; invoice overage |
| **Time & Materials** | Traditional hourly billing with no prepaid hours | Invoice based on time logged; use agreement rates |

#### 1.2 Agreement Properties

**Required Fields:**
- Agreement Name (e.g., "Acme Corp - Gold MSP Plan")
- Agreement Type (from list above)
- Client (link to existing client record)
- Start Date
- End Date
- Agreement Status (Draft, Active, Expired, Cancelled)

**Financial Fields:**
- Agreement Value (total contract value)
- Recurring Invoice Amount (for fixed-price types)
- Billing Frequency (Monthly, Quarterly, Annually)
- Included Hours (for block-hour and monthly drawdown types)
- Overage Rate (hourly rate for work exceeding included hours)
- Currency (inherited from client, but can override)

**Configuration Fields:**
- Auto-Renew (Yes/No)
- Auto-Renew Term (same as current, or specify new term length)
- Invoice Net Terms (payment due days, defaults from client)
- Email Notifications Enabled (Yes/No)
- Low Hour Threshold (default 15%, customizable per agreement)

**Descriptive Fields:**
- Agreement Scope (rich text description of covered services)
- Exclusions (what's NOT covered)
- Internal Notes (not visible to client)
- Contract Document (upload PDF of signed agreement)

#### 1.3 Agreement Status Workflow

```
Draft → Active → Renewed/Expired/Cancelled
```

- **Draft**: Agreement created but not yet active
- **Active**: Within start/end date range and billable
- **Expired**: End date has passed; system suggests renewal
- **Cancelled**: Terminated early
- **Renewed**: Replaced by a new agreement (maintain historical link)

**Status Rules:**
- Only Active agreements can have tickets assigned
- Expiring agreements (within 60 days) show warning banner
- Expired agreements automatically set to Expired status via daily cron job
- Cannot delete agreements with ticket history (can only cancel)

### 2. Agreement Coverage & Assets

#### 2.1 Covered Assets
- Agreements can specify which client assets are covered
- Multi-select asset picker (servers, workstations, network devices, etc.)
- Option for "All Assets" coverage
- When creating ticket for a covered asset, automatically suggest the agreement

#### 2.2 Covered Services
- Define which services/ticket categories are included
- Use existing ticket categories as basis
- Example covered services:
  - Remote Support
  - Email Support
  - Phone Support
  - Software Updates
  - Security Patch Management
  - Monitoring & Alerting
  - Documentation Updates
- Example excluded/billable services:
  - Onsite Visits (unless explicitly included)
  - After-Hours Emergency (premium rate)
  - Project Work
  - Hardware Procurement
  - Training

#### 2.3 Service Rate Tiers
Different ticket types can have different billing rates:

| Ticket Type | Rate Multiplier | Example |
|-------------|-----------------|---------|
| Standard Remote Support | 1.0x base rate | $150/hr |
| Phone Support | 0.5x base rate | $75/hr |
| Onsite Visit | 1.5x base rate | $225/hr |
| Emergency After-Hours | 2.0x base rate | $300/hr |
| Weekend/Holiday | 2.5x base rate | $375/hr |

- Define custom rate tiers per agreement
- Rate tiers stored in `agreement_rate_tiers` table
- Ticket type selection automatically applies correct rate
- Time logged at specific rate stored with ticket reply

### 3. Ticket-to-Agreement Assignment

#### 3.1 Mandatory Agreement Assignment
- **All new tickets MUST have an agreement assigned**
- Agreement dropdown required field on ticket creation form
- System suggests agreement based on:
  1. Asset selected (if covered by an agreement)
  2. Client's active agreements (if only one active)
  3. Most recently used agreement for this client
- Existing tickets without agreements grandfathered in (nullable field)

#### 3.2 Agreement Validation
When assigning ticket to agreement, system validates:
- Agreement is Active status
- Agreement has not expired
- If block hours: Check if hours available or warn about overage
- Show agreement details (hours remaining, covered services) in sidebar

#### 3.3 Ticket Billable Logic
New logic for ticket billable status:

```
IF ticket.agreement.type == "Fixed Price":
    ticket.billable = FALSE  // Included in monthly fee

ELIF ticket.agreement.type == "Block Hours":
    IF agreement.hours_remaining > 0:
        ticket.billable = FALSE  // Deducted from block
        ticket.deduct_from_agreement = TRUE
    ELSE:
        ticket.billable = TRUE  // Overage billing
        ticket.billable_rate = agreement.overage_rate

ELIF ticket.agreement.type == "Time & Materials":
    ticket.billable = TRUE
    ticket.billable_rate = agreement_rate_tier.rate
```

#### 3.4 Time Tracking Integration
- Existing `ticket_reply_time_worked` field continues to track time
- New logic: When time is logged on ticket:
  - Deduct from `agreement_hours_used` if block hours agreement
  - Apply rate tier based on ticket type
  - Update `agreement_hours_remaining` in real-time
  - Trigger notification if threshold breached

### 4. Hour Consumption Tracking

#### 4.1 Real-Time Hour Tracking
For Block Hours agreements:
- `agreement_hours_included`: Total hours in the agreement
- `agreement_hours_used`: Sum of all `ticket_reply_time_worked` for tickets assigned to this agreement
- `agreement_hours_remaining`: Calculated field (`included - used`)
- `agreement_hours_overage`: Hours logged beyond included hours

**Display:**
- Progress bar showing hour consumption
- Color coding: Green (>25% remaining), Yellow (15-25%), Red (<15%)
- Estimated exhaustion date based on 30-day rolling average usage

#### 4.2 Monthly Drawdown Logic
For "Block Hours - Monthly Drawdown" type:
- Hours reset on the monthly anniversary date
- Unused hours DO NOT roll over (configurable option)
- Hour tracking resets via cron job on anniversary date
- Logs historical usage to `agreement_hours_history` table

#### 4.3 Hour Consumption Reports
- Weekly email digest showing top agreement consumers
- Client-facing usage report showing hours used by category
- Technician-facing report showing hours logged per agreement
- Exportable to CSV/PDF

### 5. Automated Billing & Invoicing

#### 5.1 Recurring Invoice Generation
For Fixed Price agreements:

**Process:**
1. Daily cron job checks for agreements with `next_invoice_date == today`
2. Generates invoice with:
   - Agreement name in invoice description
   - Fixed recurring amount
   - Due date based on agreement net terms
   - Links invoice to agreement (`invoice_agreement_id`)
3. Updates `agreement_last_invoice_date` and `agreement_next_invoice_date`
4. Sets invoice status to "Draft" for review OR "Sent" if auto-send enabled
5. Sends email notification to client if configured

**Frequencies:**
- Monthly: Invoice generated on same day each month
- Quarterly: Every 3 months from start date
- Annually: On anniversary of start date

**Edge Cases:**
- If day doesn't exist in month (e.g., Jan 31 → Feb), use last day of month
- If agreement expires before next invoice date, do not generate

#### 5.2 Overage Billing
For Block Hours agreements:

**Process:**
1. When agreement hours exhausted (`hours_remaining <= 0`):
   - Continue allowing ticket creation but flag as overage
   - Log all additional time as overage hours
2. Monthly billing cycle:
   - Generate invoice for overage hours at defined overage rate
   - Include breakdown: "X hours @ $Y/hr = $Z"
   - Link to tickets that contributed to overage
   - Reset overage counter after invoicing

**Overage Invoice Detail:**
```
Agreement: Acme Corp - Silver Support Plan
Overage Hours for October 2025

Remote Support: 5.5 hours @ $150/hr = $825.00
Onsite Visit: 2.0 hours @ $225/hr = $450.00
Emergency Support: 1.0 hour @ $300/hr = $300.00

Total Overage: 8.5 hours = $1,575.00
```

#### 5.3 Invoice Linking
- New field: `invoice_agreement_id` in invoices table
- Recurring invoices automatically linked
- Overage invoices automatically linked
- Manual invoices can be linked to agreements
- Agreement detail page shows all invoices (recurring + overage)

### 6. Notifications & Alerts

#### 6.1 Low Hour Threshold Alerts

**Internal Notification (to Account Manager):**
- Triggered when `hours_remaining / hours_included <= 0.15` (15% threshold)
- Email subject: "⚠️ Low Hours Alert: [Client Name] - [Agreement Name]"
- Email body includes:
  - Hours remaining
  - Estimated exhaustion date
  - Link to agreement details
  - Suggested actions (sell more hours, discuss needs)

**Client Notification:**
- Triggered at 25% remaining and 10% remaining
- Email subject: "Your Support Hours are Running Low"
- Client portal notification badge
- Email body includes:
  - Hours remaining
  - Usage trend chart
  - Options to purchase additional hours
  - Link to view usage details

#### 6.2 Hours Exhausted Alert
- Triggered when `hours_remaining <= 0`
- Immediate notification to account manager and client
- Warning banner on ticket creation: "This agreement has no remaining hours. Additional work will be billed at $X/hr."
- Option to pause ticket creation until more hours purchased

#### 6.3 Agreement Expiration Reminders

**60 Days Before Expiration:**
- Email to account manager with renewal reminder
- Client notification via portal and email
- Dashboard widget showing expiring agreements

**30 Days Before Expiration:**
- Second reminder with urgency flag
- Suggested action: Schedule renewal call

**7 Days Before Expiration:**
- Final reminder
- Auto-create draft renewal agreement for review

**On Expiration:**
- Agreement status set to "Expired"
- Cannot create new tickets against expired agreement
- Email sent with renewal options

### 7. Client Portal Integration

#### 7.1 Agreements Page
New section in client portal: "My Agreements"

**List View:**
- All active agreements with status
- Start and end dates
- Agreement type and value
- Quick stats (hours remaining for block hours)

**Detail View:**
- Full agreement details (scope, covered assets, exclusions)
- Hour consumption chart (if applicable)
- List of tickets logged against this agreement
- Invoice history related to agreement
- Download usage reports (PDF/CSV)

#### 7.2 Hour Usage Dashboard
For block hour agreements:
- Visual progress bar
- Month-over-month usage comparison
- Breakdown by ticket category
- Top consuming assets/services
- Historical usage trends (6-month view)

#### 7.3 Purchase Additional Hours
- "Buy More Hours" button on agreement detail page
- Generates quote for additional hours
- Quote can be accepted online with saved payment method
- Upon payment, hours added to agreement immediately

---

## Technical Requirements

### 8. Database Schema

#### 8.1 New Tables

**`agreements` Table**
```sql
CREATE TABLE agreements (
    agreement_id INT AUTO_INCREMENT PRIMARY KEY,
    agreement_prefix VARCHAR(200) DEFAULT 'AGR',
    agreement_number INT NOT NULL,
    agreement_name VARCHAR(200) NOT NULL,
    agreement_reference VARCHAR(200), -- External contract reference
    agreement_type ENUM('Fixed Price - Monthly', 'Fixed Price - Quarterly', 'Fixed Price - Annually', 'Block Hours - Prepaid', 'Block Hours - Monthly Drawdown', 'Time & Materials') NOT NULL,
    agreement_status ENUM('Draft', 'Active', 'Expired', 'Cancelled', 'Renewed') DEFAULT 'Draft',
    agreement_scope TEXT, -- Description of covered services
    agreement_exclusions TEXT, -- What's NOT covered

    -- Date fields
    agreement_start_date DATE NOT NULL,
    agreement_end_date DATE NOT NULL,
    agreement_next_invoice_date DATE,
    agreement_last_invoice_date DATE,

    -- Financial fields
    agreement_value DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    agreement_recurring_amount DECIMAL(15,2) DEFAULT 0.00, -- Monthly/quarterly/annual amount for fixed price
    agreement_billing_frequency ENUM('Monthly', 'Quarterly', 'Annually', 'One-Time') DEFAULT 'Monthly',
    agreement_currency_code VARCHAR(200) DEFAULT 'USD',
    agreement_net_terms INT DEFAULT 30, -- Payment terms in days

    -- Hour tracking (for block hours types)
    agreement_hours_included DECIMAL(10,2) DEFAULT 0.00,
    agreement_hours_used DECIMAL(10,2) DEFAULT 0.00,
    agreement_hours_remaining DECIMAL(10,2) GENERATED ALWAYS AS (agreement_hours_included - agreement_hours_used) STORED,
    agreement_hours_overage DECIMAL(10,2) DEFAULT 0.00, -- Hours beyond included
    agreement_overage_rate DECIMAL(15,2) DEFAULT 0.00, -- Hourly rate for overage
    agreement_hours_rollover TINYINT(1) DEFAULT 0, -- Unused hours roll over?

    -- Configuration
    agreement_auto_renew TINYINT(1) DEFAULT 0,
    agreement_auto_renew_term INT DEFAULT 12, -- Months
    agreement_auto_invoice TINYINT(1) DEFAULT 1, -- Auto-generate recurring invoices?
    agreement_email_notifications TINYINT(1) DEFAULT 1,
    agreement_low_hour_threshold DECIMAL(5,2) DEFAULT 15.00, -- Notification threshold percentage
    agreement_all_assets_covered TINYINT(1) DEFAULT 0, -- Cover all client assets?

    -- Relationships
    agreement_client_id INT NOT NULL,
    agreement_recurring_invoice_id INT, -- Link to recurring invoice template
    agreement_template_id INT, -- Link to agreement template
    agreement_parent_agreement_id INT, -- Link to previous agreement if renewed

    -- File attachments
    agreement_contract_file VARCHAR(200), -- Uploaded signed contract

    -- Notes
    agreement_notes TEXT, -- Internal notes

    -- Timestamps
    agreement_created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    agreement_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    agreement_archived_at DATETIME,

    -- Indexes
    INDEX (agreement_client_id),
    INDEX (agreement_status),
    INDEX (agreement_start_date),
    INDEX (agreement_end_date),
    INDEX (agreement_next_invoice_date),

    FOREIGN KEY (agreement_client_id) REFERENCES clients(client_id) ON DELETE CASCADE,
    FOREIGN KEY (agreement_recurring_invoice_id) REFERENCES recurring_invoices(recurring_invoice_id) ON DELETE SET NULL,
    FOREIGN KEY (agreement_parent_agreement_id) REFERENCES agreements(agreement_id) ON DELETE SET NULL
);
```

**`agreement_assets` Table** (Many-to-Many)
```sql
CREATE TABLE agreement_assets (
    agreement_asset_id INT AUTO_INCREMENT PRIMARY KEY,
    agreement_asset_agreement_id INT NOT NULL,
    agreement_asset_asset_id INT NOT NULL,
    agreement_asset_created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unique_agreement_asset (agreement_asset_agreement_id, agreement_asset_asset_id),
    FOREIGN KEY (agreement_asset_agreement_id) REFERENCES agreements(agreement_id) ON DELETE CASCADE,
    FOREIGN KEY (agreement_asset_asset_id) REFERENCES assets(asset_id) ON DELETE CASCADE
);
```

**`agreement_services` Table** (Many-to-Many)
```sql
CREATE TABLE agreement_services (
    agreement_service_id INT AUTO_INCREMENT PRIMARY KEY,
    agreement_service_agreement_id INT NOT NULL,
    agreement_service_category VARCHAR(200) NOT NULL, -- Ticket category name
    agreement_service_included TINYINT(1) DEFAULT 1, -- Included or excluded?
    agreement_service_notes TEXT,

    UNIQUE KEY unique_agreement_service (agreement_service_agreement_id, agreement_service_category),
    FOREIGN KEY (agreement_service_agreement_id) REFERENCES agreements(agreement_id) ON DELETE CASCADE
);
```

**`agreement_rate_tiers` Table**
```sql
CREATE TABLE agreement_rate_tiers (
    rate_tier_id INT AUTO_INCREMENT PRIMARY KEY,
    rate_tier_agreement_id INT NOT NULL,
    rate_tier_name VARCHAR(100) NOT NULL, -- e.g., "Standard Support", "Emergency After-Hours"
    rate_tier_rate DECIMAL(15,2) NOT NULL, -- Hourly rate
    rate_tier_rate_multiplier DECIMAL(5,2) DEFAULT 1.00, -- Alternative to flat rate
    rate_tier_ticket_type VARCHAR(100), -- Link to ticket type/category
    rate_tier_applies_after_hours TINYINT(1) DEFAULT 0,
    rate_tier_applies_weekends TINYINT(1) DEFAULT 0,
    rate_tier_notes TEXT,

    FOREIGN KEY (rate_tier_agreement_id) REFERENCES agreements(agreement_id) ON DELETE CASCADE,
    INDEX (rate_tier_agreement_id)
);
```

**`agreement_hours_history` Table**
```sql
CREATE TABLE agreement_hours_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    history_agreement_id INT NOT NULL,
    history_period_start DATE NOT NULL,
    history_period_end DATE NOT NULL,
    history_hours_included DECIMAL(10,2),
    history_hours_used DECIMAL(10,2),
    history_hours_overage DECIMAL(10,2),
    history_tickets_logged INT DEFAULT 0,
    history_notes TEXT,
    history_created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (history_agreement_id) REFERENCES agreements(agreement_id) ON DELETE CASCADE,
    INDEX (history_agreement_id),
    INDEX (history_period_start)
);
```

#### 8.2 Modified Tables

**`tickets` Table - Add Field**
```sql
ALTER TABLE tickets ADD COLUMN ticket_agreement_id INT AFTER ticket_client_id;
ALTER TABLE tickets ADD INDEX (ticket_agreement_id);
ALTER TABLE tickets ADD FOREIGN KEY (ticket_agreement_id) REFERENCES agreements(agreement_id) ON DELETE SET NULL;
```

**`ticket_replies` Table - Add Fields**
```sql
ALTER TABLE ticket_replies ADD COLUMN ticket_reply_rate DECIMAL(15,2) DEFAULT 0.00 AFTER ticket_reply_time_worked;
ALTER TABLE ticket_replies ADD COLUMN ticket_reply_rate_tier_id INT AFTER ticket_reply_rate;
ALTER TABLE ticket_replies ADD COLUMN ticket_reply_deduct_from_agreement TINYINT(1) DEFAULT 0;
```

**`invoices` Table - Add Field**
```sql
ALTER TABLE invoices ADD COLUMN invoice_agreement_id INT AFTER invoice_recurring_invoice_id;
ALTER TABLE invoices ADD INDEX (invoice_agreement_id);
ALTER TABLE invoices ADD FOREIGN KEY (invoice_agreement_id) REFERENCES agreements(agreement_id) ON DELETE SET NULL;
```

**`clients` Table - Add Fields** (Optional)
```sql
ALTER TABLE clients ADD COLUMN client_default_agreement_type VARCHAR(50) AFTER client_rate;
ALTER TABLE clients ADD COLUMN client_preferred_agreement_term INT DEFAULT 12 AFTER client_default_agreement_type;
```

#### 8.3 Views for Reporting

**`view_agreement_utilization`**
```sql
CREATE VIEW view_agreement_utilization AS
SELECT
    a.agreement_id,
    a.agreement_name,
    a.agreement_type,
    a.agreement_status,
    c.client_name,
    a.agreement_hours_included,
    a.agreement_hours_used,
    a.agreement_hours_remaining,
    ROUND((a.agreement_hours_used / a.agreement_hours_included * 100), 2) AS utilization_percentage,
    COUNT(t.ticket_id) AS total_tickets,
    DATEDIFF(a.agreement_end_date, CURDATE()) AS days_until_expiration
FROM agreements a
LEFT JOIN clients c ON a.agreement_client_id = c.client_id
LEFT JOIN tickets t ON t.ticket_agreement_id = a.agreement_id
WHERE a.agreement_type LIKE 'Block Hours%'
GROUP BY a.agreement_id;
```

### 9. Business Logic & Calculations

#### 9.1 Hour Deduction Logic
When time is logged on a ticket reply:

```php
function deductAgreementHours($ticket_id, $hours_worked) {
    $ticket = getTicket($ticket_id);
    $agreement = getAgreement($ticket['ticket_agreement_id']);

    if (!$agreement || $agreement['agreement_type'] == 'Fixed Price - Monthly') {
        // Fixed price agreements don't deduct hours
        return;
    }

    if ($agreement['agreement_type'] == 'Block Hours - Prepaid' ||
        $agreement['agreement_type'] == 'Block Hours - Monthly Drawdown') {

        // Calculate current remaining hours
        $hours_remaining = $agreement['agreement_hours_remaining'];

        if ($hours_remaining > 0) {
            // Deduct from included hours
            $to_deduct = min($hours_worked, $hours_remaining);
            updateAgreementHoursUsed($agreement['agreement_id'], $to_deduct);

            // Check threshold after deduction
            checkLowHourThreshold($agreement['agreement_id']);

            // If work exceeds remaining, remainder is overage
            if ($hours_worked > $to_deduct) {
                $overage = $hours_worked - $to_deduct;
                addOverageHours($agreement['agreement_id'], $overage);
                notifyOverageHours($agreement['agreement_id'], $overage);
            }
        } else {
            // All hours are overage
            addOverageHours($agreement['agreement_id'], $hours_worked);
        }
    }
}
```

#### 9.2 Rate Calculation Logic
Determine billable rate based on ticket and agreement:

```php
function calculateBillableRate($ticket, $time_logged_at) {
    $agreement = getAgreement($ticket['ticket_agreement_id']);

    // Get applicable rate tier
    $rate_tier = determineRateTier(
        $agreement['agreement_id'],
        $ticket['ticket_category'],
        $time_logged_at
    );

    if ($rate_tier) {
        return $rate_tier['rate_tier_rate'];
    } else {
        // Fall back to agreement overage rate or client rate
        return $agreement['agreement_overage_rate'] ?: getClient($ticket['ticket_client_id'])['client_rate'];
    }
}

function determineRateTier($agreement_id, $ticket_category, $timestamp) {
    $is_after_hours = isAfterHours($timestamp); // After 5pm or before 8am
    $is_weekend = isWeekend($timestamp);

    $query = "SELECT * FROM agreement_rate_tiers
              WHERE rate_tier_agreement_id = ?
              AND (rate_tier_ticket_type = ? OR rate_tier_ticket_type IS NULL)";

    if ($is_after_hours) {
        $query .= " AND rate_tier_applies_after_hours = 1";
    }

    if ($is_weekend) {
        $query .= " AND rate_tier_applies_weekends = 1";
    }

    $query .= " ORDER BY rate_tier_rate DESC LIMIT 1"; // Highest rate wins

    return executeQuery($query, [$agreement_id, $ticket_category]);
}
```

#### 9.3 Invoice Generation Logic
Automated recurring invoice generation:

```php
function generateAgreementRecurringInvoices() {
    // Run daily via cron
    $agreements = mysqli_query($mysqli,
        "SELECT * FROM agreements
         WHERE agreement_status = 'Active'
         AND agreement_auto_invoice = 1
         AND agreement_next_invoice_date = CURDATE()
         AND agreement_type LIKE 'Fixed Price%'"
    );

    while ($agreement = mysqli_fetch_assoc($agreements)) {
        // Create invoice
        $invoice_id = createInvoiceFromAgreement($agreement);

        // Update agreement
        updateAgreementInvoiceDate($agreement['agreement_id'], $agreement['agreement_billing_frequency']);

        // Send notification
        if ($agreement['agreement_email_notifications']) {
            emailInvoiceToClient($invoice_id);
        }

        // Log activity
        logAgreementActivity($agreement['agreement_id'], 'Recurring invoice generated', $invoice_id);
    }
}

function generateAgreementOverageInvoices() {
    // Run monthly (e.g., 1st of month)
    $agreements = mysqli_query($mysqli,
        "SELECT * FROM agreements
         WHERE agreement_status = 'Active'
         AND agreement_hours_overage > 0
         AND agreement_type LIKE 'Block Hours%'"
    );

    while ($agreement = mysqli_fetch_assoc($agreements)) {
        // Get all overage hours with ticket details
        $overage_details = getOverageHoursBreakdown($agreement['agreement_id']);

        // Create invoice
        $invoice_id = createOverageInvoice($agreement, $overage_details);

        // Reset overage counter
        resetAgreementOverage($agreement['agreement_id']);

        // Log activity
        logAgreementActivity($agreement['agreement_id'], 'Overage invoice generated', $invoice_id);
    }
}
```

### 10. User Interface Requirements

#### 10.1 Agreement Management Interface

**Agreements List Page** (`/agent/agreements.php`)
- Table view with columns:
  - Agreement # (clickable to detail)
  - Client Name
  - Agreement Name
  - Type
  - Status (badge with color coding)
  - Start/End Date
  - Hours (remaining/included for block hours)
  - Next Invoice Date
  - Actions (View, Edit, Renew, Cancel)
- Filters:
  - Status (Active, Expiring Soon, Expired, All)
  - Type (all types)
  - Client (dropdown)
- Search by agreement name or number
- Bulk actions: Export, Archive
- Stats cards at top:
  - Total Active Agreements
  - Total MRR (Monthly Recurring Revenue)
  - Agreements Expiring This Month
  - Low Hour Alerts

**Agreement Detail Page** (`/agent/agreement.php`)
- Header section:
  - Agreement name and number
  - Status badge
  - Client name (link)
  - Quick actions: Edit, Renew, Download PDF, Cancel

- Overview Tab:
  - Agreement details (type, dates, value)
  - Covered assets (list with links)
  - Covered services (categorized)
  - Contract document (if uploaded)

- Hours Tab (for block hours agreements):
  - Progress bar with utilization percentage
  - Hours included, used, remaining
  - Overage hours count
  - Month-over-month usage chart
  - Estimated exhaustion date
  - Hour history table

- Tickets Tab:
  - List of all tickets assigned to this agreement
  - Filterable by status, date, technician
  - Shows time logged per ticket
  - Export to CSV

- Invoices Tab:
  - All invoices linked to agreement (recurring + overage)
  - Total billed to date
  - Outstanding balance
  - Payment history

- Activity Log Tab:
  - Timestamped log of all agreement changes
  - Invoice generations
  - Notifications sent
  - Status changes

**Agreement Add/Edit Modal** (`/agent/modals/agreement_add.php`)
- Multi-step form:

  **Step 1: Basic Info**
  - Agreement Name (required)
  - Client (required, searchable dropdown)
  - Agreement Type (required, radio buttons with descriptions)
  - Start Date (required, date picker)
  - End Date (required, date picker, defaults to +12 months)
  - Agreement Reference (optional)

  **Step 2: Financial Details**
  - Agreement Value (total contract value)
  - Billing Frequency (if fixed price)
  - Recurring Amount (if fixed price)
  - Included Hours (if block hours)
  - Overage Rate (if block hours)
  - Net Terms (defaults from client)

  **Step 3: Coverage**
  - All Assets Covered? (checkbox)
  - Select Specific Assets (multi-select, disabled if all assets checked)
  - Covered Services (checkboxes of ticket categories)
  - Excluded Services (text area)

  **Step 4: Rate Tiers** (optional)
  - Add custom rate tiers
  - Name, Rate, Multiplier, Conditions
  - Table view with add/remove rows

  **Step 5: Configuration**
  - Auto-Renew (checkbox)
  - Auto-Invoice (checkbox)
  - Email Notifications (checkbox)
  - Low Hour Threshold (percentage)
  - Scope & Notes (rich text editor)

  **Step 6: Review & Create**
  - Summary of all settings
  - Option to activate immediately or save as draft

#### 10.2 Ticket Interface Modifications

**Ticket Add/Edit Modal** (`/agent/modals/ticket_add.php`)
- Add new field: **Agreement** (required dropdown)
  - Position: After Client field
  - Behavior:
    - Auto-populated if only one active agreement for client
    - Shows agreement name, type, and hours remaining (if applicable)
    - Warning if agreement has low hours
    - Error if no active agreement (force user to create one or use default "No Agreement" option for legacy)
  - Visual indicator:
    - Green: Agreement has plenty of hours
    - Yellow: Agreement low on hours
    - Red: Agreement out of hours (will be overage)

**Ticket Detail Page** (`/agent/ticket.php`)
- Agreement section added to sidebar:
  - Agreement name (link to agreement detail)
  - Agreement type badge
  - Hours remaining (if applicable)
  - "This ticket is covered under agreement" or "This ticket is billable overage"
  - Link to view all tickets on this agreement

**Ticket Reply Time Logging**
- When logging time on ticket reply:
  - Show current agreement hours remaining
  - Calculate and display: "This will use X hours from agreement"
  - Show resulting remaining hours after save
  - If overage: "This will create X hours of overage at $Y/hr"

#### 10.3 Client Portal Modifications

**New Page: My Agreements** (`/portal/agreements.php`)
- Card-based layout for each active agreement
- Each card shows:
  - Agreement name
  - Status and dates
  - Visual hour gauge (for block hours)
  - Quick stats (tickets logged, invoices)
  - "View Details" button

**Agreement Detail Page** (`/portal/agreement_detail.php`)
- Simplified version of agent detail page
- Tabs:
  - Overview (scope, covered assets)
  - Usage (hours consumed, tickets)
  - Invoices (payment history)
- "Purchase Additional Hours" CTA button (if block hours)

#### 10.4 Dashboard Widgets

**Agent Dashboard:**
- New widget: "Agreements Overview"
  - Total active agreements
  - MRR total
  - Agreements expiring this month (count + list)
  - Low hour alerts (count + list)

- Modified widget: "Invoices"
  - Add filter for "Agreement Recurring" vs. "Manual" vs. "Overage"

**Reports:**
- New report: Agreement Utilization Report
- New report: Agreement Revenue Report
- New report: Agreement Renewal Pipeline

---

## Integration Points

### 11.1 Existing ITFlow Integrations

**Recurring Invoices:**
- When creating fixed-price agreement, automatically create or link recurring invoice
- Agreement acts as "master record" with recurring invoice as "execution mechanism"
- Update recurring invoice if agreement modified

**Tickets:**
- Ticket assignment to agreement mandatory
- Time tracking automatically updates agreement hours
- Ticket status changes can trigger agreement notifications

**Invoicing:**
- Manual invoices can be linked to agreements
- Overage invoices auto-generated monthly
- Invoice line items reference agreement

**Client Portal:**
- Agreements visible to clients
- Hour usage transparency
- Purchase additional hours workflow

**Cron Jobs:**
- Daily: Generate recurring invoices for agreements
- Daily: Check agreement expirations and send notifications
- Daily: Check low hour thresholds
- Monthly: Generate overage invoices
- Monthly: Archive hours history for drawdown agreements

**Email Notifications:**
- Reuse existing notification infrastructure
- New templates for agreement-specific notifications

### 11.2 Third-Party Integrations (Future)

**Accounting Systems:**
- QuickBooks: Sync agreements as "service contracts"
- Xero: Sync recurring invoices tied to agreements

**Payment Processors:**
- Stripe: Auto-charge for recurring invoices
- Link agreements to saved payment methods

**CRM Systems:**
- Sync agreement data to CRM for sales pipeline visibility

---

## Implementation Phases

### Phase 1: Foundation (Weeks 1-2)
**Goal:** Database schema and core data models

- [ ] Create all database tables and relationships
- [ ] Write migration scripts for existing data
- [ ] Create PHP classes for Agreement entity
- [ ] Implement basic CRUD operations (Create, Read, Update, Delete)
- [ ] Build agreement list page (basic table view)
- [ ] Build agreement detail page (basic info display)

**Deliverable:** Can create and view agreements (no automation yet)

---

### Phase 2: Ticket Integration (Weeks 3-4)
**Goal:** Connect tickets to agreements with hour tracking

- [ ] Add agreement field to tickets table
- [ ] Modify ticket add/edit modals to include agreement selection
- [ ] Implement agreement suggestion logic based on asset/client
- [ ] Display agreement info in ticket detail sidebar
- [ ] Build hour deduction logic when time is logged
- [ ] Update ticket billable status calculation based on agreement type
- [ ] Create validation rules (e.g., active agreement required)

**Deliverable:** All new tickets must have agreements; hour tracking functional

---

### Phase 3: Automated Billing (Weeks 5-6)
**Goal:** Recurring invoices and overage billing

- [ ] Build cron job for recurring invoice generation from fixed-price agreements
- [ ] Build cron job for overage invoice generation from block-hour agreements
- [ ] Link invoices to agreements (invoice_agreement_id)
- [ ] Implement invoice line item formatting for agreement invoices
- [ ] Create invoice templates specific to agreement types
- [ ] Test monthly/quarterly/annual billing cycles
- [ ] Build agreement-invoice relationship views

**Deliverable:** Invoices automatically generated based on agreements

---

### Phase 4: Notifications & Alerts (Week 7)
**Goal:** Proactive notifications for hour thresholds and expirations

- [ ] Build low hour threshold detection logic
- [ ] Create email templates for low hour alerts (internal & client)
- [ ] Build hours exhausted notification
- [ ] Build agreement expiration reminder system (60/30/7 days)
- [ ] Create cron jobs for daily notification checks
- [ ] Add notification preferences to agreement settings
- [ ] Build notification log/history

**Deliverable:** Automated alerts for critical agreement events

---

### Phase 5: Client Portal (Week 8)
**Goal:** Client visibility and self-service

- [ ] Build "My Agreements" page in client portal
- [ ] Build agreement detail page for clients
- [ ] Create hour usage charts and visualizations
- [ ] Build usage history reports (downloadable PDF/CSV)
- [ ] Create "Purchase Additional Hours" workflow
- [ ] Add agreement widgets to portal dashboard

**Deliverable:** Clients can view and manage their agreements

---

### Phase 6: Reporting & Analytics (Week 9)
**Goal:** Business intelligence and insights

- [ ] Build Agreement Utilization Report
- [ ] Build Agreement Revenue Report (MRR, ARR)
- [ ] Build Agreement Renewal Pipeline Report
- [ ] Create agreement dashboard widgets
- [ ] Build custom agreement views (e.g., view_agreement_utilization)
- [ ] Create exportable reports (CSV, PDF)
- [ ] Add agreement filtering to existing ticket/invoice reports

**Deliverable:** Comprehensive reporting for agreement management

---

### Phase 7: Advanced Features (Week 10)
**Goal:** Rate tiers, asset coverage, service definitions

- [ ] Build rate tier management interface
- [ ] Implement rate tier calculation logic
- [ ] Build asset coverage selection UI
- [ ] Build covered services definition UI
- [ ] Implement agreement templates (clone common agreement types)
- [ ] Build agreement renewal workflow (create new from existing)
- [ ] Add contract document upload functionality

**Deliverable:** Full-featured agreement management with flexible configurations

---

### Phase 8: Testing & Refinement (Week 11)
**Goal:** Bug fixes, edge cases, performance

- [ ] End-to-end testing of all agreement types
- [ ] Test edge cases (month-end dates, leap years, etc.)
- [ ] Load testing with large client databases
- [ ] UI/UX refinements based on testing feedback
- [ ] Documentation updates
- [ ] Permission system audit (who can create/edit/delete agreements)
- [ ] Backup/restore procedures for agreement data

**Deliverable:** Production-ready, tested system

---

### Phase 9: Migration & Training (Week 12)
**Goal:** Deploy to production and train users

- [ ] Data migration plan for existing recurring invoices → agreements
- [ ] Backfill existing tickets with agreements (or create legacy "No Agreement" option)
- [ ] Create user training documentation
- [ ] Create video tutorials for agreement management
- [ ] Admin training session
- [ ] Soft launch with pilot clients
- [ ] Monitor and address issues
- [ ] Full rollout

**Deliverable:** Live production system with trained users

---

## Success Metrics

### Quantitative Metrics

**Adoption:**
- [ ] 100% of new tickets assigned to agreements within 30 days of launch
- [ ] 80% of existing recurring invoices converted to agreements within 60 days
- [ ] 50% of clients on block-hour agreements within 6 months

**Efficiency:**
- [ ] 70% reduction in time to create recurring invoices (automated)
- [ ] 90% reduction in manual hour tracking (automated from tickets)
- [ ] 50% reduction in billing errors related to agreement terms

**Revenue:**
- [ ] 20% increase in MRR within 6 months (better contract management)
- [ ] 15% increase in overage revenue capture (previously unbilled work)
- [ ] 10% increase in average contract value (upselling block hours)

**User Satisfaction:**
- [ ] 90% user satisfaction rating from internal staff (survey)
- [ ] 85% client satisfaction with agreement transparency (portal survey)
- [ ] 50% reduction in billing-related support tickets

### Qualitative Metrics

**Internal Feedback:**
- Technicians report better clarity on billable vs. covered work
- Billing administrators report significant time savings
- Account managers report improved renewal conversations
- Management reports better forecasting accuracy

**Client Feedback:**
- Clients appreciate transparency into hour usage
- Clients report fewer billing surprises
- Clients value ability to purchase additional hours online
- Clients renew at higher rates

---

## Risk Assessment

### High Risk

**Risk:** User resistance to mandatory ticket-agreement assignment
**Mitigation:**
- Phased rollout with training
- Create simple "default agreement" for quick selection
- Show clear value proposition (better tracking, automated billing)
- Monitor ticket creation friction and adjust UX

**Risk:** Data migration complexity (existing tickets/invoices)
**Mitigation:**
- Thorough testing in staging environment
- Backfill plan with clear rules (e.g., tickets from last 12 months only)
- Option to leave legacy tickets unassigned
- Detailed migration documentation and rollback plan

**Risk:** Billing automation errors leading to incorrect invoices
**Mitigation:**
- Default to "Draft" status for auto-generated invoices (requires approval)
- Extensive testing of edge cases (month-end, leap years, daylight savings)
- Audit log of all automated billing actions
- Manual override capabilities
- 30-day parallel run with manual verification

### Medium Risk

**Risk:** Performance degradation with hour tracking calculations
**Mitigation:**
- Use database triggers for real-time updates
- Implement caching for frequently accessed agreement data
- Load testing with realistic data volumes
- Optimize queries with proper indexing

**Risk:** Notification fatigue (too many alerts)
**Mitigation:**
- Configurable notification preferences per agreement
- Digest-style notifications (daily/weekly summary)
- Threshold customization
- Snooze/dismiss options

**Risk:** Complex rate tier logic confuses users
**Mitigation:**
- Start with simple flat rates, introduce tiers as "advanced" feature
- Visual rate tier calculator in UI
- Preset templates for common tier structures
- Comprehensive documentation and examples

### Low Risk

**Risk:** Client portal adoption for agreement viewing
**Mitigation:**
- Email notifications with direct links to portal
- Mobile-responsive design
- Simple, intuitive UI
- Training resources and videos

---

## Open Questions & Decisions Needed

1. **Legacy Ticket Assignment:** Should all existing tickets be backfilled with agreements, or can we have a "No Agreement" option for historical tickets?
   - **Recommendation:** Allow NULL for existing tickets; require agreement for new tickets starting on go-live date

2. **Overage Approval Workflow:** Should technicians be required to get approval before logging overage hours, or just warn and allow?
   - **Recommendation:** Warn but allow; add permission level for "Require Approval for Overage"

3. **Hour Rollover Policy:** Should unused hours automatically roll over on monthly drawdown agreements, or always expire?
   - **Recommendation:** Make it configurable per agreement with default to "expire"

4. **Partial Hour Tracking:** Should system support 0.25 hour increments, or minimum 0.10 (6 minutes)?
   - **Recommendation:** Support 0.01 (1 minute) increments; display in decimal or HH:MM format

5. **Multiple Agreements per Client:** Can a client have multiple active agreements simultaneously (e.g., one for servers, one for desktops)?
   - **Recommendation:** Yes, allow multiple active agreements; ticket UI shows all and user selects

6. **Agreement Pause/Suspension:** Should there be a "Paused" status for temporary suspension (e.g., client request)?
   - **Recommendation:** Add "Suspended" status; retains hours but stops recurring invoices and ticket assignment

7. **Historical Reporting:** How far back should agreement reporting go?
   - **Recommendation:** Unlimited history; archive old agreements but maintain data

8. **Rate Tier Conflicts:** What happens if multiple rate tiers match (e.g., after-hours AND weekend)?
   - **Recommendation:** Highest rate wins; document clearly

---

## Appendix

### A. Glossary

- **Agreement:** A contract between service provider and client defining services, terms, and billing
- **Block Hours:** A prepaid package of support hours purchased in advance
- **Drawdown:** The act of using/consuming hours from a block hours agreement
- **Fixed Price Agreement:** Client pays a set recurring fee regardless of usage
- **MSP (Managed Service Provider):** IT service provider offering ongoing managed services typically under contract
- **MRR (Monthly Recurring Revenue):** Predictable monthly revenue from agreements
- **Overage:** Work performed beyond included hours, billed separately
- **Rate Tier:** Different billing rates based on time of day, service type, or urgency
- **Retainer:** Another term for block hours agreement
- **SLA (Service Level Agreement):** Defined service quality commitments (future scope)
- **Time & Materials:** Traditional billing model charging for actual time worked
- **Utilization:** Percentage of included hours that have been consumed

### B. Example Agreement Scenarios

**Scenario 1: Gold MSP Plan**
- Type: Fixed Price - Monthly
- Amount: $2,500/month
- Included Hours: 20 hours
- Overage Rate: $150/hr
- Covered Services: Remote support, email support, monitoring, patching
- Excluded: Onsite visits, after-hours emergency, project work
- **Billing:** Auto-invoice $2,500 on 1st of each month; track hours for usage reporting; invoice overage separately at month-end

**Scenario 2: Block Hours Retainer**
- Type: Block Hours - Prepaid
- Amount: $10,000 (one-time payment)
- Included Hours: 100 hours
- Overage Rate: $125/hr
- Covered Services: All services
- **Billing:** Invoice $10,000 upfront; deduct hours as work is performed; invoice overage monthly; notify at 85% and 100% utilization

**Scenario 3: Monthly Drawdown**
- Type: Block Hours - Monthly Drawdown
- Amount: $1,000/month
- Included Hours: 10 hours/month (resets monthly)
- Overage Rate: $150/hr
- Covered Services: Phone and remote support only
- **Billing:** Auto-invoice $1,000 monthly; deduct hours during month; reset to 10 hours on anniversary; invoice overage separately

**Scenario 4: Time & Materials**
- Type: Time & Materials
- Amount: N/A (no recurring fee)
- Included Hours: 0
- Standard Rate: $125/hr
- Rate Tiers:
  - Standard: $125/hr
  - Onsite: $175/hr
  - After-Hours: $200/hr
  - Emergency Weekend: $250/hr
- **Billing:** Invoice all hours worked monthly at applicable rate tier

### C. Related Documentation
- ITFlow Developer Documentation: `/docs/README.md`
- Database Schema Reference: `/docs/db.sql`
- Billing System Overview: `/docs/billing.md` (to be created)
- Cron Job Documentation: `/docs/cron.md` (to be created)

---

**Document Sign-off:**

| Role | Name | Date | Signature |
|------|------|------|-----------|
| Product Owner | | | |
| Technical Lead | | | |
| UX Designer | | | |
| QA Lead | | | |

---

**Revision History:**

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2025-11-07 | Claude AI | Initial draft |

