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
(1, 'Bridal Makeup Package', 'Complete bridal makeup with airbrush finish, hairstyling, and draping.', 299.99, 'assets/img/bridal_makeup.jpg', 'Services'),
(1, 'Party Makeup', 'Glamorous party look with smokey eyes and contouring.', 89.99, 'assets/img/party_makeup.jpg', 'Services'),
(1, 'Matte Lipstick Collection', 'Set of 6 long-lasting matte lipsticks in trending shades.', 45.00, 'assets/img/makeup_lipstick.jpg', 'Products'),
(1, 'Eyeshadow Palette - Sunset', '12-shade eyeshadow palette with warm sunset tones.', 38.00, 'assets/img/makeup_eyeshadow.jpg', 'Products'),
(1, 'Makeup Masterclass Workshop', '3-hour hands-on workshop to learn professional makeup techniques.', 150.00, 'assets/img/makeup_workshop.jpg', 'Services');

INSERT INTO products (company_id, name, description, price, image_url, category) VALUES
(1, 'Party Makeup', 'Party makeup service', 25.00, 'assets/img/party_makeup.jpg', 'Services'),
(1, 'Bridal Makeup', 'Bridal makeup service', 150.00, 'assets/img/bridal_makeup.jpg', 'Services'),
(1, 'Engagement Makeup', 'Engagement makeup service', 80.00, 'assets/img/engagement_makeup.jpg', 'Services'),

(1, 'Eyebrow Threading', 'Eyebrow threading service', 0.50, 'assets/img/eyebrow_threading.jpg', 'Services'),
(1, 'Upper Lip Threading', 'Upper lip threading service', 0.30, 'assets/img/upper_lip_threading.jpg', 'Services'),

(1, 'Basic Haircut', 'Basic haircut service', 3.00, 'assets/img/basic_haircut.jpg', 'Services'),
(1, 'Layered Haircut', 'Layered haircut service', 6.00, 'assets/img/layered_haircut.jpg', 'Services'),
(1, 'Step Cut', 'Step cut service', 5.00, 'assets/img/step_cut.jpg', 'Services'),

(1, 'Hair Spa', 'Hair spa service', 12.00, 'assets/img/hair_spa.jpg', 'Services'),
(1, 'Keratin Treatment', 'Keratin treatment service', 40.00, 'assets/img/keratin_treatment.jpg', 'Services'),
(1, 'Hair Coloring (Global)', 'Hair coloring service', 30.00, 'assets/img/hair_coloring_global.jpg', 'Services'),

(1, 'Clean Up', 'Skin clean up service', 5.00, 'assets/img/clean_up.jpg', 'Services'),
(1, 'Fruit Facial', 'Fruit facial service', 8.00, 'assets/img/fruit_facial.jpg', 'Services'),
(1, 'O3+ Facial', 'O3 facial service', 20.00, 'assets/img/o3_facial.jpg', 'Services'),
(1, 'De-Tan Pack', 'De-tan service', 4.00, 'assets/img/detan_pack.jpg', 'Services'),

(1, 'Basic Manicure', 'Manicure service', 4.00, 'assets/img/basic_manicure.jpg', 'Services'),
(1, 'Basic Pedicure', 'Pedicure service', 5.00, 'assets/img/basic_pedicure.jpg', 'Services'),
(1, 'Nail Art (Per Finger)', 'Nail art service', 1.00, 'assets/img/nail_art_per_finger.jpg', 'Services'),
(1, 'Gel Nail Polish', 'Gel polish service', 8.00, 'assets/img/gel_nail_polish.jpg', 'Services'),

(1, 'Basic Mehndi', 'Mehndi service', 5.00, 'assets/img/basic_mehndi.jpg', 'Services'),
(1, 'Bridal Mehndi', 'Bridal mehndi service', 50.00, 'assets/img/bridal_mehndi.jpg', 'Services'),

(1, 'Pre-Bridal Package (Basic)', 'Basic pre-bridal service', 50.00, 'assets/img/prebridal_basic.jpg', 'Services'),
(1, 'Pre-Bridal Package (Premium)', 'Premium pre-bridal service', 100.00, 'assets/img/prebridal_premium.jpg', 'Services'),

(1, 'Saree Draping', 'Saree draping service', 3.00, 'assets/img/saree_draping.jpg', 'Services'),
(1, 'Waxing (Full Arms)', 'Full arms waxing', 2.50, 'assets/img/waxing_full_arms.jpg', 'Services'),
(1, 'Waxing (Full Legs)', 'Full legs waxing', 4.00, 'assets/img/waxing_full_legs.jpg', 'Services');

INSERT INTO products (company_id, name, description, price, image_url, category) VALUES
(1, 'Vitamin C Face Wash', 'Face wash product', 3.50, 'assets/img/vitamin_c_facewash.jpg', 'Products'),
(1, 'Hydrating Moisturizer', 'Moisturizer product', 5.50, 'assets/img/hydrating_moisturizer.jpg', 'Products'),
(1, 'Sunscreen SPF 50', 'Sunscreen product', 4.50, 'assets/img/sunscreen_spf50.jpg', 'Products'),
(1, 'Rose Water Toner', 'Toner product', 2.00, 'assets/img/rose_water_toner.jpg', 'Products'),
(1, 'Under Eye Cream', 'Eye cream product', 4.00, 'assets/img/under_eye_cream.jpg', 'Products'),

(1, 'Base Coat Polish', 'Nail base coat', 2.50, 'assets/img/base_coat_polish.jpg', 'Products'),
(1, 'Top Coat Polish', 'Nail top coat', 2.50, 'assets/img/top_coat_polish.jpg', 'Products'),
(1, 'Nail Polish Remover', 'Nail remover product', 1.50, 'assets/img/nail_polish_remover.jpg', 'Products');


-- ============================================
-- SEED DATA: Products for Megha Artisans (company_id = 2)
-- Source: mgcodes.com catalog / get_products.php
-- ============================================
INSERT INTO products (company_id, name, description, price, image_url, category) VALUES
(2, 'Rose Gold Filigree Necklace', 'Handcrafted rose gold-plated filigree pendant on a delicate chain, perfect for everyday elegance.', 79.00, 'https://mgcodes.com/images/rose-gold-filigree-necklace.jpeg', 'Necklace'),
(2, 'Moonstone Drop Earrings', 'Sterling silver earrings featuring iridescent moonstone drops that catch the light with every movement.', 49.00, 'https://mgcodes.com/images/moonstone-drop-earrings.jpeg', 'Earrings'),
(2, 'Stackable Gemstone Rings', 'Mix-and-match bands with tiny natural gemstones, designed to be stacked or worn solo.', 69.00, 'https://mgcodes.com/images/stackable-gemstone-rings.jpeg', 'Ring'),
(2, 'Pearl Horizon Bracelet', 'Freshwater pearls and tiny gold beads on a dainty bracelet that layers beautifully.', 59.00, 'https://mgcodes.com/images/pearl-horizon-bracelet.jpeg', 'Bracelet'),
(2, 'Boho Charm Anklet', 'Beaded anklet with tiny charms inspired by summer festivals and beach days.', 39.00, 'https://mgcodes.com/images/boho-charm-anklet.jpeg', 'Anklet'),
(2, 'Crystal Cluster Hair Comb', 'Wire-wrapped crystals arranged on a comb, ideal for bridal and special occasion hairstyles.', 45.00, 'https://mgcodes.com/images/crystal-cluster-hair-comb.jpeg', 'Hair Accessory'),
(2, 'Minimalist Bar Necklace', 'Sleek horizontal bar necklace for a clean, modern look that layers with anything.', 55.00, 'https://mgcodes.com/images/minimalist-bar-necklace.jpeg', 'Necklace'),
(2, 'Hammered Cuff Bracelet', 'Adjustable hand-hammered cuff with subtle texture that reflects light beautifully.', 62.00, 'https://mgcodes.com/images/hammered-cuff-bracelet.jpeg', 'Bracelet'),
(2, 'Custom Birthstone Necklace', 'Personalized pendant featuring hand-selected birthstones for you or your loved ones.', 85.00, 'https://mgcodes.com/images/custom-birthstone-necklace.jpeg', 'Necklace'),
(2, 'Bridal Jewelry Set', 'Coordinated necklace, earrings, and bracelet set custom-designed for your wedding day.', 149.00, 'https://mgcodes.com/images/bridal-jewelry-set.jpeg', 'Bridal Set');

-- ============================================
-- SEED DATA: Products for Cookie Business
-- ============================================
INSERT INTO products (company_id, name, description, price, image_url, category) VALUES
(3, 'Classic Chocolate Chip Cookies', 'Freshly baked chocolate chip cookies with premium Belgian chocolate.', 12.99, 'assets/img/cookie_chocchip.jpg', 'Cookies'),
(3, 'Red Velvet Cookie Box', 'Box of 12 soft red velvet cookies with cream cheese filling.', 18.99, 'assets/img/cookie_redvelvet.jpg', 'Cookies'),
(3, 'Assorted Macaron Set', 'Set of 12 French macarons in assorted flavors.', 24.99, 'assets/img/cookie_macaron.jpg', 'Macarons'),
(3, 'Custom Birthday Cookie Cake', 'Personalized giant cookie cake for birthdays and celebrations.', 35.00, 'assets/img/cookie_cake.jpg', 'Custom Orders'),
(3, 'Brownie Bliss Box', '6 fudgy brownies with walnut and caramel swirl.', 15.99, 'assets/img/cookie_brownie.jpg', 'Brownies');

-- ============================================
-- SEED DATA: Products for GeekyHub
-- ============================================
INSERT INTO products (company_id, name, description, price, image_url, category) VALUES
(4, 'Frontend Development', 'Responsive UI development with modern design, accessibility, and performance optimization.', 1200.00, 'assets/img/frontend.svg', 'Development'),
(4, 'Backend Development', 'Secure and scalable server-side development including APIs, authentication, and database integration.', 1800.00, 'assets/img/backend.svg', 'Development'),
(4, 'Test Automation', 'Automated testing solutions including unit, integration, and regression testing to improve software quality.', 900.00, 'assets/img/test-automation.svg', 'QA'),
(4, 'Infrastructure Setup', 'Server and environment setup including LAMP stack, deployment configuration, backups, and monitoring.', 1500.00, 'assets/img/infrastructure.svg', 'DevOps'),
(4, 'CMS Setup', 'Installation and customization of CMS platforms like WordPress with themes, plugins, and content structuring.', 800.00, 'assets/img/cms.svg', 'Development'),
(4, 'API Integration', 'Integration of third-party services such as payment gateways, email systems, and external APIs.', 1100.00, 'assets/img/api-integration.svg', 'Development'),
(4, 'Database Design', 'Design of efficient database schemas with proper relationships, indexing, and data integrity constraints.', 1300.00, 'assets/img/database-design.svg', 'Development'),
(4, 'Performance Optimization', 'Enhancing application speed through code optimization, caching, and database query improvements.', 1000.00, 'assets/img/performance.svg', 'Optimization'),
(4, 'Security Hardening', 'Improving application security with input validation, secure authentication, and best practices.', 1400.00, 'assets/img/security.svg', 'Security'),
(4, 'Maintenance & Support', 'Ongoing updates, bug fixes, monitoring, and support to ensure system reliability and performance.', 700.00, 'assets/img/maintenance.svg', 'Support');



-- ============================================
-- API TOKENS TABLE (cross-site authentication)
-- ============================================
CREATE TABLE user_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

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
