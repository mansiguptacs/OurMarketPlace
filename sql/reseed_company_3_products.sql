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
(3, 'Classic Chocolate Chip', 'Our signature cookie is baked with brown sugar, vanilla, and generous semisweet chocolate chips for crisp edges and a soft, gooey center. It is the crowd favorite for gift boxes, office trays, and late-night dessert cravings.', 14.99, 'https://www.yukta-padgaonkar.com/CMPE-272-project/cookie-business/product_image.php?slug=classic-chocolate-chip', 'Cookies'),
(3, 'Oatmeal Raisin Deluxe', 'This home-style oatmeal cookie layers toasted oats, plump raisins, and a gentle cinnamon finish for a hearty bite that feels classic and comforting. It is slightly less sweet than our chocolate varieties, which makes it popular for breakfast meetings and afternoon coffee breaks.', 13.99, 'https://www.yukta-padgaonkar.com/CMPE-272-project/cookie-business/product_image.php?slug=oatmeal-raisin-deluxe', 'Cookies'),
(3, 'Decorated Sugar Cookies', 'Our decorated sugar cookies are buttery vanilla cut-outs topped with hand-piped royal icing in themed colors and simple event designs. They work especially well for birthdays, baby showers, holiday gifting, and branded dessert tables with advance notice.', 29.99, 'https://www.yukta-padgaonkar.com/CMPE-272-project/cookie-business/product_image.php?slug=decorated-sugar-cookies', 'Cookies'),
(3, 'Snickerdoodle', 'Cream of tartar gives this cookie its classic tang while a cinnamon-sugar coating bakes into a lightly crisp shell. The center stays soft and pillowy, making it a great choice for anyone who wants something cozy and not overly rich.', 12.49, 'https://www.yukta-padgaonkar.com/CMPE-272-project/cookie-business/product_image.php?slug=snickerdoodle', 'Cookies'),
(3, 'Double Chocolate Fudge', 'Built for serious chocolate lovers, this deep cocoa cookie bakes up like a soft brownie with extra dark chocolate folded into every batch. It is dense, fudgy, and bold enough to pair well with espresso, cold brew, or vanilla ice cream.', 15.49, 'https://www.yukta-padgaonkar.com/CMPE-272-project/cookie-business/product_image.php?slug=double-chocolate-fudge', 'Cookies'),
(3, 'Lemon Glazed Shortbread', 'This crisp shortbread cookie is made with fresh lemon zest and finished with a smooth citrus glaze for a bright, clean flavor. It is one of our lightest cookies and works especially well for spring menus, brunches, and tea-time assortments.', 13.49, 'https://www.yukta-padgaonkar.com/CMPE-272-project/cookie-business/product_image.php?slug=lemon-glazed-shortbread', 'Cookies'),
(3, 'Peanut Butter Blossom', 'The peanut butter blossom starts with a soft peanut butter dough rolled in sugar and ends with a chocolate center pressed in while warm. It is sweet, nutty, and nostalgic, with the perfect mix of peanut butter richness and milk chocolate on top.', 14.49, 'https://www.yukta-padgaonkar.com/CMPE-272-project/cookie-business/product_image.php?slug=peanut-butter-blossom', 'Cookies'),
(3, 'Custom Catering Tray', 'Our catering tray includes a mix of bestsellers such as chocolate chip, snickerdoodle, decorated sugar cookies, and seasonal specials, arranged for easy sharing. It is designed for office meetings, birthdays, and celebrations, and we can label common allergens on request.', 89.99, 'https://www.yukta-padgaonkar.com/CMPE-272-project/cookie-business/product_image.php?slug=custom-catering-tray', 'Catering'),
(3, 'Gift Box Subscription', 'Each monthly subscription box features a rotating mix of seasonal cookies, limited-run flavors, and a baker selection chosen for that month. It is a simple gift option for students, remote teams, and families who want fresh cookie variety without placing a new order every time.', 34.99, 'https://www.yukta-padgaonkar.com/CMPE-272-project/cookie-business/product_image.php?slug=gift-box-subscription', 'Subscriptions'),
(3, 'Gluten-Friendly Almond Cookie', 'This almond-forward cookie uses almond flour and dark chocolate for a chewy texture and a rich roasted flavor without wheat flour in the recipe. It is made in a shared kitchen, so while it is gluten-friendly for many guests, it is not certified gluten-free.', 17.49, 'https://www.yukta-padgaonkar.com/CMPE-272-project/cookie-business/product_image.php?slug=gluten-friendly-almond-cookie', 'Cookies');

COMMIT;
