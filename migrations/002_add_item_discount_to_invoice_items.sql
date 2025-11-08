-- Add item-level discount column to invoice_items table
ALTER TABLE `invoice_items` ADD COLUMN `item_discount` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `item_tax`;
