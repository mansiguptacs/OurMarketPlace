# OurMarketplace - Cross Domain Enterprise Online Market Place

**CMPE 272 - Enterprise Software Platforms**

A unified online marketplace connecting 4 independent web companies, built with PHP + MySQL.

## Team Members

| Member | Company | Category | Website |
|--------|---------|----------|---------|
| Mansi Gupta | KG Makeup Studio | Makeup & Beauty | [mansiguptacs.com/kgmakeupstudio](https://mansiguptacs.com/kgmakeupstudio/) |
| Megha Gangal| Megha Artisans | Artificial Jewellery | [mgcodes.com](https://mgcodes.com/) |
| Yukta Padgaonkar | Cookie Business | Cookies & Bakery | [yukta-padgaonkar.com](http://yukta-padgaonkar.com/CMPE-272-project/cookie-business/) |
| Gayathri Rukmadhavan | GeekyHub | IT & Staffing Services | [geekyhub.me](http://geekyhub.me/) |

## Live Demo

**URL:** https://mansiguptacs.com/ourmarketplace/

**Demo Login:**
- Username: `demo_user`
- Password: `password`

## Features

### Core Features (Required)
1. **User Registration & Login** - Single account for the entire marketplace
2. **Visit Tracking** - Automatic logging of user visits across all companies
3. **Reviews & Ratings** - 1-5 star rating + text reviews for any product/service
4. **Top 5 Per Company** - Best rated / most visited / most reviewed per company
5. **Top 5 Marketplace-wide** - Overall rankings across all 4 companies

### Bonus Features
6. **Cross-Marketplace Search** - Search products by name, description, category, or company
7. **User Dashboard** - Personal stats, visit breakdown, reviews written
8. **Wishlist** - Save products from any company to a personal wishlist
9. **Product Comparison** - Compare 2-3 products side by side in a table
10. **Multiple Ranking Methods** - Toggle between Best Rated, Most Visited, Most Reviewed
11. **Responsive UI** - Mobile-friendly design with Bootstrap 5
12. **External Site Links** - Direct links to each member's actual company website

## Tech Stack

- **Backend:** PHP 7.4+ (pure PHP, no frameworks)
- **Database:** MySQL 5.7+
- **Frontend:** Bootstrap 5, Font Awesome 6, vanilla JavaScript
- **Hosting:** InfinityFree shared hosting
- **Security:** Prepared statements (SQL injection prevention), password_hash/password_verify (bcrypt)

## Demo Test Cases

1. Register a new user account
2. Login with credentials (SSO)
3. Browse a company storefront (e.g., KG Makeup Studio)
4. View a product detail page
5. Submit a star rating + text review
6. View visit tracking history (across multiple companies)
7. View Top 5 within a company (Best Rated)
8. View Top 5 marketplace-wide (toggle Most Visited)
9. Search across marketplace (e.g., "bridal" finds results from Makeup + Jewellery)
10. User dashboard showing stats, visits breakdown, reviews list
11. Add products to wishlist from different companies
12. Compare 2-3 products side by side
13. Teams Page

Details information to check test cases can be found on google doc: https://docs.google.com/document/d/10GqmC1MvlfgmM86uEI-kkCmC4omgsqoscxgQYWZh-oI/edit?tab=t.kheh5gb14o08
