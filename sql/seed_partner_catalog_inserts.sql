-- ============================================
-- Supplemental seed rows for integrated partner businesses
-- (Run after schema.sql on a fresh DB.)
-- Use case: richer marketplace demos when not all catalogs are loaded via live API.
-- ============================================

-- KG Makeup Studio (company_id = 1)
INSERT INTO products (company_id, name, description, price, image_url, category) VALUES
(1, 'HD Airbrush Foundation Session', 'Flawless HD-ready base with custom shade matching and setting spray.', 75.00, 'assets/img/makeup_hd.jpg', 'Services'),
(1, 'Lash Extension Consultation', 'Consultation plus natural or volume lash mapping for events.', 55.00, 'assets/img/makeup_lashes.jpg', 'Services'),
(1, 'Skincare Prep Kit', 'Travel-size cleanser, toner, and primer bundle for pre-makeup prep.', 42.00, 'assets/img/makeup_skincare.jpg', 'Products');

-- Artisan Jewelry by Megha (company_id = 2)
INSERT INTO products (company_id, name, description, price, image_url, category) VALUES
(2, 'Chandbali Earrings - Gold Tone', 'Statement chandbali earrings with mirror and bead work.', 28.00, 'assets/img/jewel_chandbali.jpg', 'Earrings'),
(2, 'Layered Temple Necklace', 'Multi-layer temple necklace inspired by South Indian craft.', 72.00, 'assets/img/jewel_temple.jpg', 'Necklaces'),
(2, 'Adjustable Finger Ring Set', 'Set of 3 stackable rings with antique finish.', 18.00, 'assets/img/jewel_stack.jpg', 'Rings');

-- Sweet Crumb Homemade Cookies (company_id = 3) — fallback rows if live get_products.php is unreachable
INSERT INTO products (company_id, name, description, price, image_url, category) VALUES
(3, 'Classic Chocolate Chip (API mirror)', 'Matches the cookie-business signature chocolate chip cookie and uses the same stable product artwork.', 14.99, 'https://www.yukta-padgaonkar.com/CMPE-272-project/cookie-business/product_image.php?slug=classic-chocolate-chip', 'Cookies'),
(3, 'Custom Catering Tray (API mirror)', 'Assorted catering platter mirroring the cookie-business event tray with updated artwork.', 89.99, 'https://www.yukta-padgaonkar.com/CMPE-272-project/cookie-business/product_image.php?slug=custom-catering-tray', 'Catering');

-- GeekyHub (company_id = 4)
INSERT INTO products (company_id, name, description, price, image_url, category) VALUES
(4, 'Cloud Migration Assessment', 'Readiness assessment, cost estimate, and phased migration roadmap.', 950.00, 'assets/img/cloud.svg', 'Consulting'),
(4, 'Code Review Sprint', 'Targeted senior review focused on security, performance, and maintainability.', 600.00, 'assets/img/code-review.svg', 'Consulting'),
(4, '24/7 On-Call Retainer', 'Incident response coverage with SLA-backed response windows.', 2500.00, 'assets/img/oncall.svg', 'Support');
