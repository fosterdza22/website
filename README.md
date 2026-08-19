# Hostel Agency 🏠

A full-stack PHP + MySQL web application for admitted university students to browse,
compare, and book hostels — plus order food and everyday essentials for delivery — all
with secure Paystack payments (including installments). Includes a full admin
dashboard for hostels, rooms, media, bookings, the shop, orders, testimonials, news,
and student birthdays.

## Tech Stack
- **Frontend:** HTML5, CSS3 (Bootstrap 5), vanilla JavaScript
- **Backend:** PHP 8 (procedural, PDO, prepared statements throughout)
- **Database:** MySQL / MariaDB
- **Payments:** Paystack (Initialize + Verify API, plus a webhook for server-to-server confirmation)
- **Map:** Leaflet.js + OpenStreetMap (no API key required)
- **Charts:** Chart.js (admin analytics)
- **Auth:** PHP sessions + bcrypt password hashing, CSRF tokens on every form

## Setup Instructions

1. **Install a local server stack** (XAMPP, WAMP, MAMP, or Laragon) with PHP 8+, the
   **cURL** extension enabled, and MySQL.
2. **Copy this `hostel-agency` folder** into your web root (e.g. `htdocs/`) — it works
   whether it sits at the root or in a subfolder; routing is auto-detected.
3. **Create the database:** import `database/schema.sql` in phpMyAdmin. This creates
   everything — hostels, bookings, payments, shop, orders, testimonials, news, and
   birthdays — plus sample seed data.
4. **Configure the connection:** edit `config/db.php` if your MySQL username/password
   differ from the XAMPP defaults (`root` / empty password).
5. **Add your Paystack keys:** get free test keys from https://dashboard.paystack.com
   (Settings → API Keys & Webhooks) and paste them into `config/paystack.php`.
6. **(Recommended) Set up the webhook:** in the Paystack dashboard, set your webhook
   URL to `https://yourdomain.com/hostel-agency/webhook.php` for reliable payment
   confirmation even if a student closes their browser mid-checkout.
7. **Fix the admin password:** visit `reset_admin_password.php` once in your browser,
   then **delete that file**. Admin login: `admin@hostelagency.com` / `Admin@123`.
8. **Check folder permissions:** make sure `assets/uploads/` (and its `hostels`,
   `profiles`, `products` subfolders) are writable by your web server user.
9. Visit `index.php` — register a new student account (a profile picture is required
   at signup) or log in as admin.

**Troubleshooting "Not Found" on every link:** open `debug_base_url.php` in your
browser — it shows exactly what install path was detected and lets you test a link.
If it's wrong, `config/app.php` has a one-line manual override at the top.

## Features

### Student Dashboard
- Register (profile picture **required**), log in, manage profile & photo
- Browse 7 seeded hostels, each with its own dedicated detail page showing only that
  hostel's own rooms, prices, amenities, photos, video tour, and map pin
- Live AJAX filtering (price, distance, room type, amenities) + interactive campus map
- Book a room, choosing to **pay in full or in 3 installments** (40/30/30), via Paystack
- Price comparison tool, booking history with live payment status
- **Shop:** browse food/items/other products, add to cart, checkout with a delivery
  address, and pay via Paystack — full or later top-up payments supported
- Order history with live delivery status (pending → processing → out for delivery → delivered)
- Birthday countdown page + any wishes received from admin
- Submit testimonials (shown on homepage once approved)

### Admin Dashboard
- Manage hostel listings, room types/prices, and **upload photos & videos** per hostel
- Monitor & update room bookings and their payment status
- **Manage Shop:** add/edit/delete products (with image upload), toggle availability
- **Manage Orders:** view every order and update delivery status
- Send birthday wishes to students (shows on their dashboard instantly)
- Moderate testimonials, publish "News of the Week" posts
- Analytics: occupancy, room + shop revenue collected, pending collections, charts

### Security & Accessibility
- Bcrypt password hashing, CSRF tokens on all forms, PDO prepared statements everywhere
- Server-side Paystack transaction verification (browser is never trusted alone);
  webhook signature verified with `hash_hmac('sha512', ...)`
- Uploaded files (profile pictures, hostel media, product images) are served from
  folders with script execution disabled (`.htaccess`)
- A booking/order with any payment on it can't be silently self-cancelled by mistake
- Mobile-responsive Bootstrap layout; skip-link, labelled fields, alt text throughout

## Notes
- The shop cart is session-based (no login required to keep items while browsing, but
  checkout requires being logged in as a student).
- Delivery fee is a flat GH₵15 per order — change `$DELIVERY_FEE` in `student/cart.php`
  if you want a different amount or a distance-based calculation.
- Leaflet + OpenStreetMap is used instead of Google Maps API since it needs no API key;
  swapping providers is a small change in `assets/js/main.js` and the two map pages.
