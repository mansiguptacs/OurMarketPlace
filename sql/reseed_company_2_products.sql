-- Replace all products for company_id = 2 (Megha Artisans) with current seed data.
-- Run after updating schema.sql:  mysql -u root -p ourmarketplace < sql/reseed_company_2_products.sql

START TRANSACTION;

DELETE rv FROM reviews rv
INNER JOIN products p ON p.id = rv.product_id
WHERE p.company_id = 2;

DELETE w FROM wishlist w
INNER JOIN products p ON p.id = w.product_id
WHERE p.company_id = 2;

DELETE uv FROM user_visits uv
INNER JOIN products p ON p.id = uv.product_id
WHERE p.company_id = 2;

DELETE FROM products WHERE company_id = 2;

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

COMMIT;
