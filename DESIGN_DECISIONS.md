# Design Decisions - OurMarketplace

## 1. Static Product Data vs. Web Scraping

**Decision:** Store product data statically in our own MySQL database rather than scraping from live member websites.

**Reasons:**
- InfinityFree (our hosting) blocks outbound HTTP requests (`file_get_contents` for URLs and `cURL` are restricted), making live scraping impossible.
- Each member's website has a completely different HTML structure — would require 4 separate parsers that break if any site layout changes.
- Scraping adds 2-5 seconds of load time per page (fetching 4 external sites on every request).
- Reviews, ratings, and visit tracking require stable product IDs. Scraped data has no guaranteed stability.
- If any teammate's site goes down during the demo, the marketplace would show errors.
- For a class project demo, reliability and speed are more important than live data sync.

**Compromise:** Each company and product page includes a "Visit Official Website" link that opens the real external site. This demonstrates cross-domain awareness without the fragility.

---

## 2. Shared Database Architecture

**Decision:** Single MySQL database for all 4 companies instead of separate databases per company.

**Reasons:**
- Simplifies cross-company queries (Top 5 marketplace-wide, search across all companies).
- One user account works across the entire marketplace (single registration).
- Visit tracking can span all companies without cross-database joins.
- Easier to deploy and maintain on shared hosting with limited DB allocations.

---

## 3. Session-Based Authentication (No OAuth/JWT)

**Decision:** Use PHP native sessions with `password_hash()`/`password_verify()`.

**Reasons:**
- No external dependencies (no Google/Facebook OAuth SDK needed).
- Works reliably on InfinityFree without special server configuration.
- Simple to understand and demo — fits the scope of a class project.
- Secure enough: bcrypt hashing, prepared statements for SQL injection prevention.

---

## 4. Bootstrap 5 CDN (No npm/build tools)

**Decision:** Load Bootstrap and Font Awesome from CDN rather than using npm/webpack.

**Reasons:**
- No build step needed — just upload PHP files to hosting.
- InfinityFree doesn't support Node.js or build pipelines.
- CDN-loaded assets are cached by browsers, improving load times.
- Keeps the project simple and accessible for all team members.

---

## 5. BASE_URL Configuration

**Decision:** Use a `BASE_URL` constant for all internal links instead of hardcoded absolute paths.

**Reasons:**
- Site is deployed at `mansiguptacs.com/ourmarketplace/` (subdirectory, not root).
- Hardcoded `/auth/login.php` resolves to server root, causing 404s.
- `baseUrl('/auth/login.php')` generates `/ourmarketplace/auth/login.php` correctly.
- Makes the project portable — change one constant to deploy anywhere.

---

## 6. MySQLi over PDO

**Decision:** Use MySQLi with prepared statements.

**Reasons:**
- Slightly simpler syntax for a MySQL-only project.
- Full support on InfinityFree hosting.
- Prepared statements provide SQL injection protection equivalent to PDO.

---

## 7. Multiple Ranking Methods (Bonus)

**Decision:** Provide 3 ranking methods (Best Rated, Most Visited, Most Reviewed) via URL parameter toggle, not a single fixed algorithm.

**Reasons:**
- Demonstrates more technical depth for grading.
- "Best" is subjective — different users value different metrics.
- Simple to implement with same query structure, just changing ORDER BY.
- Shows awareness that a marketplace needs flexibility in how it surfaces products.

---

## 8. Wishlist with UNIQUE Constraint (Bonus)

**Decision:** Use `INSERT IGNORE` with a `UNIQUE(user_id, product_id)` constraint instead of checking existence first.

**Reasons:**
- Atomic operation — no race conditions.
- Simpler code (one query instead of SELECT + conditional INSERT).
- User can click "Add to Wishlist" multiple times without errors.

---

## 9. Seed Data for Demo (Bonus)

**Decision:** Include pre-seeded users, reviews, and visits in schema.sql.

**Reasons:**
- Demo starts with meaningful data (rankings show real results immediately).
- No need to manually create test data before the presentation.
- All seed user passwords are "password" (bcrypt hash) for easy demo access.
- Shows cross-company visit patterns that highlight the tracking feature.

---

## 10. Product Comparison Table (Bonus)

**Decision:** URL-based comparison (`?ids=1,2,3`) instead of session-based cart approach.

**Reasons:**
- Shareable URLs — can link directly to a comparison.
- No session dependency — works for logged-out users too.
- Simpler implementation (no add/remove state management).
- Limited to 3 products max to keep the table readable on all screen sizes.

---
