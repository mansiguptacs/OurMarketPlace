-- ============================================
-- OurMarketplace Database Schema
-- Cross Domain Enterprise Online Market Place
-- ============================================



-- ============================================
-- USERS TABLE (marketplace-wide single login)
-- ============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- COMPANIES TABLE (the 4 member businesses)
-- ============================================
CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    owner_name VARCHAR(100),
    website_url VARCHAR(255),
    logo_url VARCHAR(255),
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- PRODUCTS / SERVICES TABLE
-- ============================================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2),
    image_url VARCHAR(255),
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

-- ============================================
-- REVIEWS TABLE (ratings + text)
-- ============================================
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- ============================================
-- USER VISITS TABLE (tracking)
-- ============================================
CREATE TABLE user_visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    company_id INT NOT NULL,
    product_id INT DEFAULT NULL,
    page_url VARCHAR(255),
    visited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- ============================================
-- SEED DATA: Insert the 4 companies
-- ============================================
INSERT INTO companies (name, slug, description, owner_name, website_url, category) VALUES
('Komal Gupta Makeup Studio', 'kg-makeup-studio', 'Professional makeup services and beauty products for all occasions.', 'Mansi Gupta', 'https://mansiguptacs.com/kgmakeupstudio/', 'Makeup & Beauty'),
('Artisan Jewelry by Megha', 'megha-artisans', 'Handcrafted artificial jewellery blending tradition with modern elegance.', 'Megha Gangal', 'https://mgcodes.com/', 'Artificial Jewellery'),
('Sweet Crumb Homemade Cookies', 'cookie-business', 'Freshly baked cookies and treats made with love and premium ingredients.', 'Yukta Padgaonkar', 'http://yukta-padgaonkar.com/CMPE-272-project/cookie-business/', 'Cookies & Bakery'),
('GeekyHub', 'geekyhub', 'IT consulting and staffing services connecting top tech talent with businesses.', 'Gayathri Rukmadhavan', 'http://geekyhub.me/', 'IT & Staffing Services');

-- ============================================
-- SEED DATA: Products for KG Makeup Studio
-- ============================================
INSERT INTO products (company_id, name, description, price, image_url, category) VALUES
(1, 'Bridal Makeup Package', 'Complete bridal makeup with airbrush finish, hairstyling, and draping.', 299.99, 'images/makeup_bridal.jpg', 'Services'),
(1, 'Party Makeup', 'Glamorous party look with smokey eyes and contouring.', 89.99, 'images/makeup_party.jpg', 'Services'),
(1, 'Matte Lipstick Collection', 'Set of 6 long-lasting matte lipsticks in trending shades.', 45.00, 'images/makeup_lipstick.jpg', 'Products'),
(1, 'Eyeshadow Palette - Sunset', '12-shade eyeshadow palette with warm sunset tones.', 38.00, 'images/makeup_eyeshadow.jpg', 'Products'),
(1, 'Makeup Masterclass Workshop', '3-hour hands-on workshop to learn professional makeup techniques.', 150.00, 'images/makeup_workshop.jpg', 'Services');

-- ============================================
-- SEED DATA: Products for Megha Artisans
-- ============================================
INSERT INTO products (company_id, name, description, price, image_url, category) VALUES
(2, 'Kundan Necklace Set', 'Elegant kundan necklace with matching earrings for festive occasions.', 65.00, 'images/jewel_kundan.jpg', 'Necklaces'),
(2, 'Pearl Drop Earrings', 'Delicate pearl drop earrings with gold-plated hooks.', 25.00, 'images/jewel_pearl.jpg', 'Earrings'),
(2, 'Oxidized Silver Bracelet', 'Handcrafted oxidized silver bracelet with traditional motifs.', 30.00, 'images/jewel_bracelet.jpg', 'Bracelets'),
(2, 'Statement Ring - Emerald', 'Bold statement ring featuring emerald-colored stone in antique setting.', 20.00, 'images/jewel_ring.jpg', 'Rings'),
(2, 'Maang Tikka - Bridal', 'Beautiful bridal maang tikka with crystal and pearl detailing.', 35.00, 'images/jewel_tikka.jpg', 'Hair Accessories');

-- ============================================
-- SEED DATA: Products for Cookie Business
-- ============================================
INSERT INTO products (company_id, name, description, price, image_url, category) VALUES
(3, 'Classic Chocolate Chip Cookies', 'Freshly baked chocolate chip cookies with premium Belgian chocolate.', 12.99, 'images/cookie_chocchip.jpg', 'Cookies'),
(3, 'Red Velvet Cookie Box', 'Box of 12 soft red velvet cookies with cream cheese filling.', 18.99, 'images/cookie_redvelvet.jpg', 'Cookies'),
(3, 'Assorted Macaron Set', 'Set of 12 French macarons in assorted flavors.', 24.99, 'images/cookie_macaron.jpg', 'Macarons'),
(3, 'Custom Birthday Cookie Cake', 'Personalized giant cookie cake for birthdays and celebrations.', 35.00, 'images/cookie_cake.jpg', 'Custom Orders'),
(3, 'Brownie Bliss Box', '6 fudgy brownies with walnut and caramel swirl.', 15.99, 'images/cookie_brownie.jpg', 'Brownies');

-- ============================================
-- SEED DATA: Products for GeekyHub
-- ============================================
INSERT INTO products (company_id, name, description, price, image_url, category) VALUES
(4, 'Frontend Development', 'Responsive UI development with modern design, accessibility, and performance optimization.', 1200.00, 'images/frontend.jpg', 'Development'),
(4, 'Backend Development', 'Secure and scalable server-side development including APIs, authentication, and database integration.', 1800.00, 'images/backend.jpg', 'Development'),
(4, 'Test Automation', 'Automated testing solutions including unit, integration, and regression testing to improve software quality.', 900.00, 'images/test_automation.jpg', 'QA'),
(4, 'Infrastructure Setup', 'Server and environment setup including LAMP stack, deployment configuration, backups, and monitoring.', 1500.00, 'images/infrastructure.jpg', 'DevOps'),
(4, 'CMS Setup', 'Installation and customization of CMS platforms like WordPress with themes, plugins, and content structuring.', 800.00, 'images/cms.jpg', 'Development'),
(4, 'API Integration', 'Integration of third-party services such as payment gateways, email systems, and external APIs.', 1100.00, 'images/api_integration.jpg', 'Development'),
(4, 'Database Design', 'Design of efficient database schemas with proper relationships, indexing, and data integrity constraints.', 1300.00, 'images/database.jpg', 'Development'),
(4, 'Performance Optimization', 'Enhancing application speed through code optimization, caching, and database query improvements.', 1000.00, 'images/performance.jpg', 'Optimization'),
(4, 'Security Hardening', 'Improving application security with input validation, secure authentication, and best practices.', 1400.00, 'images/security.jpg', 'Security'),
(4, 'Maintenance & Support', 'Ongoing updates, bug fixes, monitoring, and support to ensure system reliability and performance.', 700.00, 'images/maintenance.jpg', 'Support');



-- ============================================
-- WISHLIST TABLE (bonus feature)
-- ============================================
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, product_id)
);

-- ============================================
-- SEED DATA: Sample users for demo
-- ============================================
INSERT INTO users (username, email, password_hash, full_name) VALUES
('demo_user', 'demo@ourmarketplace.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo User'),
('mansi', 'mansi@ourmarketplace.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mansi Gupta'),
('megha', 'megha@ourmarketplace.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Megha'),
('yukta', 'yukta@ourmarketplace.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Yukta Padgaonkar'),
('gayathri', 'gayathri@ourmarketplace.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Gayathri');

-- Password for all seed users: "password"

-- ============================================
-- SEED DATA: Sample reviews for demo
-- ============================================
INSERT INTO reviews (user_id, product_id, rating, review_text) VALUES
(1, 1, 5, 'Amazing bridal makeup! Made me look stunning on my wedding day.'),
(1, 6, 4, 'Beautiful kundan set, great value for the price.'),
(1, 11, 5, 'Best chocolate chip cookies I have ever tasted!'),
(1, 16, 4, 'Professional web development team, delivered on time.'),
(2, 2, 4, 'Great party makeup, lasted all night without any touch-ups.'),
(2, 7, 5, 'These pearl earrings are absolutely gorgeous. Highly recommend!'),
(2, 12, 5, 'Red velvet cookies are to die for. Ordering again!'),
(2, 17, 5, 'Their mobile app development service is top-notch.'),
(3, 3, 5, 'Love this lipstick collection! Colors are vibrant and long-lasting.'),
(3, 8, 4, 'Stylish oxidized bracelet. Gets compliments everywhere I go.'),
(3, 13, 4, 'Macarons were fresh and flavourful. Nice packaging too.'),
(3, 18, 3, 'Good consulting but could be faster with responses.'),
(4, 4, 4, 'Gorgeous eyeshadow palette. Pigmentation is excellent.'),
(4, 9, 5, 'This emerald ring is my favorite piece of jewelry now!'),
(4, 14, 5, 'Custom cookie cake was a hit at the birthday party!'),
(4, 19, 4, 'Found great candidates through their staffing service.'),
(5, 5, 5, 'Learned so much in the masterclass. Totally worth it!'),
(5, 10, 4, 'Beautiful maang tikka for my engagement. Looked elegant.'),
(5, 15, 4, 'Brownies were fudgy and delicious. Perfect sweetness.'),
(5, 20, 5, 'QA team found bugs we never would have caught. Excellent service!');

-- ============================================
-- SEED DATA: Sample visits for demo
-- ============================================
INSERT INTO user_visits (user_id, company_id, product_id, page_url) VALUES
(1, 1, 1, '/companies/view.php?id=1'),
(1, 1, 2, '/products/view.php?id=2'),
(1, 2, 6, '/products/view.php?id=6'),
(1, 2, 7, '/products/view.php?id=7'),
(1, 3, 11, '/products/view.php?id=11'),
(1, 4, 16, '/products/view.php?id=16'),
(2, 1, 2, '/products/view.php?id=2'),
(2, 2, 7, '/products/view.php?id=7'),
(2, 3, 12, '/products/view.php?id=12'),
(2, 4, 17, '/products/view.php?id=17'),
(3, 1, 3, '/products/view.php?id=3'),
(3, 2, 8, '/products/view.php?id=8'),
(3, 3, 13, '/products/view.php?id=13'),
(3, 4, 18, '/products/view.php?id=18'),
(4, 1, 4, '/products/view.php?id=4'),
(4, 2, 9, '/products/view.php?id=9'),
(4, 3, 14, '/products/view.php?id=14'),
(4, 4, 19, '/products/view.php?id=19'),
(5, 1, 5, '/products/view.php?id=5'),
(5, 2, 10, '/products/view.php?id=10'),
(5, 3, 15, '/products/view.php?id=15'),
(5, 4, 20, '/products/view.php?id=20'),
(1, 1, NULL, '/companies/view.php?id=1'),
(1, 2, NULL, '/companies/view.php?id=2'),
(1, 3, NULL, '/companies/view.php?id=3'),
(1, 4, NULL, '/companies/view.php?id=4'),
(2, 1, NULL, '/companies/view.php?id=1'),
(2, 3, NULL, '/companies/view.php?id=3'),
(3, 2, NULL, '/companies/view.php?id=2'),
(3, 4, NULL, '/companies/view.php?id=4');
