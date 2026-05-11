# OurMarketplace — Presentation Outline

Consolidated structure for slides, aligned with the **current** codebase: PHP + MySQL hub, four companies in `companies`, catalog and reviews in the marketplace database.

---

## Slide 1 — Title

**OurMarketplace** — Cross-domain enterprise marketplace (CMPE 272)

**Team:** Four member businesses, one unified hub

---

## Slide 2 — Problem & approach

- **Problem:** Four independent businesses, each with its own web presence; users want one place to discover, compare, and give feedback.
- **Approach:** **Single marketplace application** with **one account** across all storefronts, plus **links** to each company’s official site.

---

## Slide 3 — High-level architecture (main message)

**Hub-and-spoke**

| Layer | What it is |
|--------|------------|
| **Hub** | **OurMarketplace** — one PHP app, one MySQL database |
| **Spokes** | **Four company websites** (separate domains) — linked from the hub; not required to run the same stack |

**One-liner:** *Unified discovery, identity, and engagement in the hub; each company keeps its own public site.*

---

## Slide 4 — What lives in the marketplace (current behavior)

All of this is in **marketplace MySQL** (`config/database.php`):

| Area | Tables / behavior |
|------|-------------------|
| **Identity** | `users` — register, login (`password_hash` / `password_verify`) |
| **Catalog** | `companies` (4 rows) + `products` (rows per `company_id`) |
| **Social proof** | `reviews` — one review per user per product (`UNIQUE(user_id, product_id)`) |
| **Engagement** | `wishlist`, `user_visits` |
| **Session** | PHP sessions after login (server-side session store, not a second “login DB”) |

**Product listing on company pages:** Loaded with SQL from **`products` WHERE `company_id` = …** (same pattern for all four companies).

**Product images:** Stored as paths under the project **or** full `https://…` URLs (resolved in UI via `includes/product_image.php`).

---

## Slide 5 — How the four businesses appear

- **`companies`** row per business: name, description, owner, **category**, **`website_url`** (outbound link: “Visit official website”).
- **`products`** rows reference **`company_id`** so rankings, search, compare, and product detail URLs are consistent.

**Speaker note (optional):** Member sites may maintain their own catalog or APIs on their domain (e.g. JSON catalog on mgcodes). The marketplace **UI and reviews** are driven by **`products`** in this database unless you add an explicit sync job.

---

## Slide 6 — Tech stack

- **Backend:** PHP (no framework), prepared statements
- **Data:** MySQL (schema + seed in `sql/schema.sql`)
- **Frontend:** Bootstrap 5, Font Awesome, vanilla JS
- **Deploy:** Document root = project root; `BASE_URL` in `config/database.php` if hosted in a subdirectory

---

## Slide 7 — Application map (folder → responsibility)

| Area | Path (high level) |
|------|-------------------|
| Entry / home | `index.php` |
| Auth | `auth/` — register, login, logout |
| Companies | `companies/` — list + storefront |
| Products | `products/` — catalog, detail, search |
| Reviews | `reviews/add.php` |
| Wishlist | `wishlist/` |
| Rankings | `rankings/` — per-company & marketplace top 5 |
| Visits | `tracking/`, `includes/tracking.php` |
| Compare | `compare/` |
| Dashboard | `dashboard.php` |
| Shared UI / DB / images | `includes/` (`header`, `footer`, `session`, `product_image.php`), `config/database.php` |
| Assets | `assets/` |

---

## Slide 8 — User journey (demo script)

1. Register / login once on the hub
2. Open **Companies** → pick any of the four → see **products from DB**
3. **View Details** → product page → **Submit review** (saved in **`reviews`**)
4. **Wishlist**, **search**, **rankings**, **visit history** / dashboard as time allows
5. Click **official website** → leave hub to member’s domain

---

## Slide 9 — Cross-enterprise / “cross domain” angle (for grading language)

- **Logical integration:** Shared **schema** (`company_id` on products, FKs to users/companies).
- **Physical integration:** **HTTP** to four **external origins** (`website_url`); optional **static assets or URLs** for images.
- **Single sign-on** within the marketplace boundary (not federated OAuth across the four sites unless you add it).

---

## Slide 10 — Risks / honesty (optional)

- Catalog on the hub is **as maintained in `products`**; keeping it aligned with each member’s live site is an **operational** concern (manual updates or a future sync from an external catalog API).
- Reviews and users **do not** automatically replicate to member databases.

---

## Slide 11 — Close

**Takeaway:** One **hub** database and app deliver **multi-tenant marketplace behavior** for four enterprises; external sites are **first-class links** and optional **source catalogs**, while **trust data** (users, reviews, visits) stays **centralized**.

---

## Trim guide

- **Short talk:** Slides 1–3, 4–5 (compact), 7–8, 11.
- **Technical deep dive:** Add 6, 9, 10.
