-- Replace all products for company_id = 3 (Sweet Crumb Homemade Cookies) with the current 10-product catalog.
-- Run after schema.sql: mysql -u root -p ourmarketplace < sql/reseed_company_3_products.sql

START TRANSACTION;

DELETE rv FROM reviews rv
INNER JOIN products p ON p.id = rv.product_id
WHERE p.company_id = 3;

DELETE w FROM wishlist w
INNER JOIN products p ON p.id = w.product_id
WHERE p.company_id = 3;

DELETE uv FROM user_visits uv
INNER JOIN products p ON p.id = uv.product_id
WHERE p.company_id = 3;

DELETE FROM products WHERE company_id = 3;

INSERT INTO products (company_id, name, description, price, image_url, category) VALUES
(3, 'Classic Chocolate Chip', 'Our signature cookie: real butter, brown sugar, and premium semisweet chocolate chips. Baked until the edges are golden and the center stays soft. Perfect with milk or coffee. Available by the dozen or half-dozen.', 14.99, 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=800&q=80', 'Cookies'),
(3, 'Oatmeal Raisin Deluxe', 'Old-fashioned rolled oats, California raisins, and a hint of cinnamon and vanilla. Chewy, filling, and not too sweet-great for breakfast or an afternoon snack.', 13.99, 'https://images.unsplash.com/photo-1590080876410-2c2a8e7b3c5f?w=800&q=80', 'Cookies'),
(3, 'Decorated Sugar Cookies', 'Soft cut-out sugar cookies with royal icing in your choice of colors and simple designs. Ideal for birthdays, holidays, and corporate gifts. Minimum order applies.', 29.99, 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=800&q=80', 'Cookies'),
(3, 'Snickerdoodle', 'Cream of tartar gives these their classic tang; the outside is rolled in cinnamon sugar for a crisp, spicy shell and a tender middle.', 12.49, 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=800&q=80', 'Cookies'),
(3, 'Double Chocolate Fudge', 'For chocolate lovers: rich cocoa batter folded with dark chocolate chunks. Dense, fudgy, and intense. Pairs well with espresso.', 15.49, 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=800&q=80', 'Cookies'),
(3, 'Lemon Glazed Shortbread', 'Crisp shortbread base with fresh lemon zest in the dough and a thin tangy glaze on top. Light and refreshing for spring and summer events.', 13.49, 'https://images.unsplash.com/photo-1621303837174-89787a7d4729?w=800&q=80', 'Cookies'),
(3, 'Peanut Butter Blossom', 'Classic peanut butter cookies rolled in sugar, baked, and topped with a milk chocolate center. Nostalgic and crowd-pleasing.', 14.49, 'https://images.unsplash.com/photo-1599735219378-2b5c8a3b6c5f?w=800&q=80', 'Cookies'),
(3, 'Custom Catering Tray', 'Large platters with a mix of our bestsellers (minimum 48 pieces). We label allergens on request and deliver within our service area. Perfect for office meetings and celebrations.', 89.99, 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=800&q=80', 'Catering'),
(3, 'Gift Box Subscription', 'Subscribe for a monthly box of seasonal flavors plus a rotating baker''s choice. Skip or cancel anytime. Great gift for students and remote teams.', 34.99, 'https://images.unsplash.com/photo-1548365328-9c3a75d7f2f0?w=800&q=80', 'Subscriptions'),
(3, 'Gluten-Friendly Almond Cookie', 'Chewy cookies made with almond flour and dark chocolate. Not certified gluten-free (shared kitchen) but no wheat flour in the recipe. Ask for ingredient list.', 17.49, 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=800&q=80', 'Cookies');

COMMIT;
