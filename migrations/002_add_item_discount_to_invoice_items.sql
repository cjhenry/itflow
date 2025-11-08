-- Add item-level discount column to invoice_items table
-- decimal(5,2) stores percentages 0-999.99%
ALTER TABLE `invoice_items` ADD COLUMN `item_discount` decimal(5,2) NOT NULL DEFAULT 0.00 AFTER `item_subtotal`;
