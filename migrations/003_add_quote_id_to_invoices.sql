-- Add quote_id reference to invoices table
-- Tracks which quote (if any) an invoice was created from
ALTER TABLE `invoices` ADD COLUMN `invoice_quote_id` int(11) DEFAULT NULL AFTER `invoice_recurring_invoice_id`;
