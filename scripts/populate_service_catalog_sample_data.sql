-- ============================================
-- Service Catalog Sample Data
-- ============================================
-- This script populates the service catalog with realistic MSP service examples
-- Review before executing in production

-- ============================================
-- 1. MASTER SERVICE CATALOG ENTRIES
-- ============================================

INSERT INTO service_catalog (
    service_name,
    service_description,
    service_default_rate,
    service_category,
    service_default_unit,
    service_status,
    service_sort_order
) VALUES

-- Support Services
('24/7 Managed Support',
 'Round-the-clock managed IT support with 24/7 availability and 1-hour response time',
 100.00,
 'Support',
 'Hour',
 'Active',
 10),

('Business Hours Support',
 'Standard support during business hours (8AM-6PM weekdays)',
 75.00,
 'Support',
 'Hour',
 'Active',
 20),

('Remote Assistance',
 'Remote desktop support and troubleshooting',
 60.00,
 'Support',
 'Hour',
 'Active',
 30),

('Priority Support',
 'Priority queue with guaranteed 30-minute response time',
 150.00,
 'Support',
 'Hour',
 'Active',
 40),

('Phone Support',
 'Telephone-based technical support',
 45.00,
 'Support',
 'Hour',
 'Active',
 50),

-- Project Services
('Project Development',
 'Custom development projects (NET/PHP/Python)',
 125.00,
 'Projects',
 'Hour',
 'Active',
 100),

('Onsite Consultation',
 'Onsite technical assessment and consulting',
 150.00,
 'Projects',
 'Hour',
 'Active',
 110),

('Data Migration',
 'Data migration and consolidation services',
 100.00,
 'Projects',
 'Hour',
 'Active',
 120),

('Network Design & Implementation',
 'Network planning, design, and deployment',
 175.00,
 'Projects',
 'Hour',
 'Active',
 130),

('Infrastructure Upgrade',
 'Server and infrastructure upgrade services',
 150.00,
 'Projects',
 'Hour',
 'Active',
 140),

-- Monitoring & Management
('24/7 System Monitoring',
 'Continuous system monitoring and alerting',
 80.00,
 'Monitoring',
 'Month',
 'Active',
 200),

('Security Patch Management',
 'Automated security updates and patch management',
 50.00,
 'Monitoring',
 'Month',
 'Active',
 210),

('Backup Management',
 'Backup solution deployment and management',
 65.00,
 'Monitoring',
 'Month',
 'Active',
 220),

('Antivirus & Malware Protection',
 'Enterprise antivirus and malware protection',
 40.00,
 'Monitoring',
 'Month',
 'Active',
 230),

-- Consulting Services
('Security Audit',
 'Comprehensive security vulnerability assessment',
 200.00,
 'Consulting',
 'Hour',
 'Active',
 300),

('Disaster Recovery Planning',
 'DR plan creation and implementation',
 175.00,
 'Consulting',
 'Hour',
 'Active',
 310),

('IT Strategy Consulting',
 'Strategic IT planning and roadmap development',
 175.00,
 'Consulting',
 'Hour',
 'Active',
 320),

('Cloud Migration Consulting',
 'Cloud architecture and migration planning',
 150.00,
 'Consulting',
 'Hour',
 'Active',
 330),

-- Premium Services
('Emergency After-Hours Support',
 'After-hours emergency support (6PM-8AM)',
 200.00,
 'Premium',
 'Hour',
 'Active',
 400),

('Weekend/Holiday Support',
 'Support services on weekends and holidays',
 250.00,
 'Premium',
 'Hour',
 'Active',
 410),

('Dedicated Account Manager',
 'Dedicated technical account manager',
 3000.00,
 'Premium',
 'Month',
 'Active',
 420),

('Executive Briefing',
 'Executive-level IT strategy and briefing',
 250.00,
 'Premium',
 'Hour',
 'Active',
 430),

-- Training Services
('End-User Training',
 'Staff training on new systems and software',
 85.00,
 'Training',
 'Hour',
 'Active',
 500),

('Administrator Training',
 'Technical training for IT administrators',
 120.00,
 'Training',
 'Hour',
 'Active',
 510),

('Software License Management Training',
 'Training on license tracking and compliance',
 100.00,
 'Training',
 'Hour',
 'Active',
 520);

-- ============================================
-- 2. COMMENTARY ON SERVICE HIERARCHY
-- ============================================
-- These sample services demonstrate the rate hierarchy:
--
-- Master Default Rate (shown above)
--   ↓
-- Client Custom Rate (can override per-client)
--   ↓
-- Agreement Custom Rate (most specific, applies to ticket)
--
-- Example: If Master = $100/hr, Client = $85/hr, Agreement = $75/hr
-- The ticket will be billed at $75/hr (agreement rate wins)

-- ============================================
-- 3. OPTIONAL: CLIENT SERVICES WITH OVERRIDES
-- ============================================
-- Uncomment below to add sample client overrides
-- This shows "preferred partner" pricing for specific clients

/*
-- Example: For Client ID 1 (assuming it exists)
INSERT INTO client_services (
    client_id,
    service_id,
    client_service_custom_rate,
    client_service_included
) VALUES
-- Client gets preferred pricing on support
((SELECT client_id FROM clients WHERE client_name = 'Acme Corp' LIMIT 1),
 (SELECT service_id FROM service_catalog WHERE service_name = '24/7 Managed Support' LIMIT 1),
 85.00,  -- Preferred rate instead of master $100/hr
 TRUE),

((SELECT client_id FROM clients WHERE client_name = 'Acme Corp' LIMIT 1),
 (SELECT service_id FROM service_catalog WHERE service_name = 'Remote Assistance' LIMIT 1),
 50.00,  -- Preferred rate instead of master $60/hr
 TRUE),

((SELECT client_id FROM clients WHERE client_name = 'Acme Corp' LIMIT 1),
 (SELECT service_id FROM service_catalog WHERE service_name = 'Project Development' LIMIT 1),
 110.00,  -- Preferred rate instead of master $125/hr
 TRUE);
*/

-- ============================================
-- 4. VERIFICATION QUERIES
-- ============================================
-- Run these to verify data was inserted correctly:
/*
SELECT COUNT(*) as total_services FROM service_catalog;
-- Expected: 24 services

SELECT service_category, COUNT(*) as count FROM service_catalog GROUP BY service_category;
-- Shows breakdown by category

SELECT * FROM service_catalog WHERE service_status = 'Active' ORDER BY service_sort_order;
-- Lists all services ordered for display
*/
