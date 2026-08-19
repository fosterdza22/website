-- ============================================================
-- Hostel Agency — Full Database Schema
-- ============================================================
CREATE DATABASE IF NOT EXISTS hostel_agency CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hostel_agency;

-- ---------------------------------------------------------------
-- USERS  (students + admins share one table, differentiated by role)
-- ---------------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    student_id VARCHAR(50) DEFAULT NULL,
    date_of_birth DATE DEFAULT NULL,
    profile_picture VARCHAR(255) DEFAULT NULL,
    role ENUM('student','admin') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- HOSTELS
-- ---------------------------------------------------------------
CREATE TABLE hostels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    address VARCHAR(255),
    latitude DECIMAL(10,7),
    longitude DECIMAL(10,7),
    distance_to_campus_km DECIMAL(5,2) DEFAULT 0,
    main_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE hostel_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hostel_id INT NOT NULL,
    photo_path VARCHAR(255) NOT NULL,
    FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE hostel_videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hostel_id INT NOT NULL,
    video_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE amenities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT 'check-circle'
) ENGINE=InnoDB;

CREATE TABLE hostel_amenities (
    hostel_id INT NOT NULL,
    amenity_id INT NOT NULL,
    PRIMARY KEY (hostel_id, amenity_id),
    FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE CASCADE,
    FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE room_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hostel_id INT NOT NULL,
    type ENUM('single','shared','premium') NOT NULL,
    price_per_year DECIMAL(10,2) NOT NULL,
    size_sqm DECIMAL(6,2) DEFAULT NULL,
    furnishing VARCHAR(255) DEFAULT NULL,
    total_rooms INT NOT NULL DEFAULT 10,
    booked_rooms INT NOT NULL DEFAULT 0,
    FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- BOOKINGS + PAYMENTS (hostel rooms)
-- ---------------------------------------------------------------
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    hostel_id INT NOT NULL,
    room_type_id INT NOT NULL,
    academic_year VARCHAR(20) NOT NULL DEFAULT '2026/2027',
    status ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
    amount DECIMAL(10,2) NOT NULL,
    payment_plan ENUM('full','installment') NOT NULL DEFAULT 'full',
    payment_status ENUM('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid',
    amount_paid DECIMAL(10,2) NOT NULL DEFAULT 0,
    booked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE CASCADE,
    FOREIGN KEY (room_type_id) REFERENCES room_types(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE installment_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    installment_number INT NOT NULL,
    amount_due DECIMAL(10,2) NOT NULL,
    due_date DATE NOT NULL,
    status ENUM('unpaid','paid') NOT NULL DEFAULT 'unpaid',
    paid_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    installment_id INT NULL DEFAULT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    reference VARCHAR(100) NOT NULL UNIQUE,
    channel VARCHAR(40) DEFAULT NULL,
    status ENUM('pending','success','failed') NOT NULL DEFAULT 'pending',
    gateway_response VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    paid_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (installment_id) REFERENCES installment_plans(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- TESTIMONIALS / NEWS / BIRTHDAYS
-- ---------------------------------------------------------------
CREATE TABLE testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    hostel_id INT NULL,
    display_name VARCHAR(120) NOT NULL,
    rating TINYINT NOT NULL DEFAULT 5,
    message TEXT NOT NULL,
    is_approved TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE news_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE birthday_wishes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sent_by INT NULL,
    message TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sent_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- SHOP: PRODUCTS, ORDERS, ORDER ITEMS, ORDER PAYMENTS
-- ---------------------------------------------------------------
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    category ENUM('food','item','other') NOT NULL DEFAULT 'item',
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    delivery_address VARCHAR(255) NOT NULL,
    delivery_phone VARCHAR(30) NOT NULL,
    delivery_notes VARCHAR(255) DEFAULT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 15.00,
    total_amount DECIMAL(10,2) NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL DEFAULT 0,
    payment_status ENUM('unpaid','paid') NOT NULL DEFAULT 'unpaid',
    status ENUM('pending','processing','out_for_delivery','delivered','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NULL,
    product_name VARCHAR(150) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    line_total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE order_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    reference VARCHAR(100) NOT NULL UNIQUE,
    channel VARCHAR(40) DEFAULT NULL,
    status ENUM('pending','success','failed') NOT NULL DEFAULT 'pending',
    gateway_response VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    paid_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Default admin account (password placeholder — run reset_admin_password.php once)
INSERT INTO users (full_name, email, password, role) VALUES
('System Administrator', 'admin@hostelagency.com', '$2y$10$Q9nP2xU8x1QwYV1zjq8pXOe3Yl6qXwq1r0N8p8mM4v1s0m8xN1a5S', 'admin');

-- Amenities
INSERT INTO amenities (name, icon) VALUES
('Wi-Fi', 'wifi'),
('Laundry', 'washing-machine'),
('Study Rooms', 'book'),
('24/7 Security', 'shield'),
('Backup Power', 'bolt'),
('Water Supply', 'droplet'),
('Kitchen', 'utensils'),
('Parking', 'car'),
('CCTV', 'camera-video'),
('Common Room / TV Lounge', 'tv');

-- Hostels (7)
INSERT INTO hostels (name, description, address, latitude, longitude, distance_to_campus_km, main_image) VALUES
('Fosters Hostel', 'A vibrant hostel community close to the main campus gate, popular for its lively social atmosphere and modern furnishing.', '12 Unity Road, Campus North', 5.6500000, -0.1870000, 0.5, 'https://getrooms.co/hostels/evandy-hostel-kumasi/'),
('Patrick Hostel', 'A quiet, study-focused hostel with dedicated reading rooms and reliable backup power, ideal for focused students.', '4 Serenity Ave, Campus East', 5.6520000, -0.1830000, 1.2, 'https://www.tripadvisor.com/LocationPhotoDirectLink-g293797-d1068994-i282967677-Pink_Hostel-Accra_Greater_Accra.html'),
('Owusu Hostel', 'Premium accommodation offering en-suite rooms, air conditioning and a rooftop common area with campus views.', '9 Golden Gate St, Campus West', 5.6480000, -0.1900000, 0.8, 'https://www.booking.com/hotel/gh/the-backpacker-accra.html'),
('Fiifi Hostel', 'Affordable shared rooms with a friendly community feel and easy walking distance to lecture halls.', '21 View Street, Near Main Gate', 5.6510000, -0.1850000, 0.3, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTFU7oMdUaZGJv8H8_blybb6YzOv2fI9yOxwQZSX6hqV2lr6nwAIJzXUjM&s'),
('Jerry Hostel', 'Modern hostel with landscaped courtyards, laundry services, and a dedicated kitchen for residents.', '17 Palm Court, Campus South', 5.6470000, -0.1820000, 1.5, 'https://www.booking.com/hotel/gh/the-backpacker-accra.en-gb.html'),
('Riverside Hall', 'Budget-friendly hostel by the riverside with scenic views, communal kitchen, and secure parking.', '3 Riverside Drive, Campus Southeast', 5.6455000, -0.1795000, 2.1, 'https://ghanapropertycentre.com/for-sale/houses/greater-accra/accra-metropolitan/56502-titled-4-bedrooms-house'),
('Heritage Suites', 'Boutique-style hostel with tastefully furnished premium rooms and 24/7 concierge-style security.', '8 Heritage Lane, Campus North East', 5.6540000, -0.1810000, 1.0, 'https://ghanapropertycentre.com/for-sale/commercial/hostel/showtype');

INSERT INTO hostel_amenities (hostel_id, amenity_id) VALUES
(1,1),(1,2),(1,4),(1,9),(1,10),
(2,1),(2,3),(2,4),(2,5),(2,6),
(3,1),(3,4),(3,5),(3,8),(3,9),(3,10),
(4,1),(4,2),(4,6),(4,10),
(5,1),(5,2),(5,7),(5,6),
(6,1),(6,7),(6,8),(6,6),
(7,1),(7,4),(7,5),(7,9),(7,10);

INSERT INTO room_types (hostel_id, type, price_per_year, size_sqm, furnishing, total_rooms, booked_rooms) VALUES
(1,'single', 4500.00, 12, 'Bed, wardrobe, desk, chair', 10, 3),
(1,'shared', 2800.00, 18, 'Bunk beds, wardrobes, study desks', 20, 12),
(1,'premium', 6200.00, 16, 'AC, en-suite bathroom, wardrobe, desk', 8, 2),
(2,'single', 4200.00, 11, 'Bed, wardrobe, desk', 12, 5),
(2,'shared', 2600.00, 17, 'Bunk beds, wardrobes', 25, 20),
(3,'single', 5200.00, 14, 'Bed, wardrobe, desk, mini-fridge', 10, 4),
(3,'premium', 7800.00, 20, 'AC, en-suite, smart TV, wardrobe', 12, 6),
(4,'shared', 2200.00, 16, 'Bunk beds, wardrobes, study desks', 30, 18),
(4,'single', 3800.00, 10, 'Bed, wardrobe, desk', 10, 7),
(5,'single', 4000.00, 12, 'Bed, wardrobe, desk, bookshelf', 15, 6),
(5,'shared', 2500.00, 18, 'Bunk beds, wardrobes', 20, 9),
(5,'premium', 6000.00, 18, 'AC, en-suite, wardrobe, desk', 6, 1),
(6,'shared', 1900.00, 16, 'Bunk beds, wardrobes', 25, 10),
(6,'single', 3200.00, 10, 'Bed, wardrobe, desk', 12, 3),
(7,'premium', 8500.00, 22, 'AC, en-suite, smart TV, mini-fridge, sofa', 10, 4),
(7,'single', 5500.00, 13, 'Bed, wardrobe, desk, mini-fridge', 8, 2);

INSERT INTO hostel_photos (hostel_id, photo_path) VALUES
(1,'https://picsum.photos/seed/unity1/500/350'),(1,'https://picsum.photos/seed/unity2/500/350'),(1,'https://picsum.photos/seed/unity3/500/350'),
(2,'https://picsum.photos/seed/serene1/500/350'),(2,'https://picsum.photos/seed/serene2/500/350'),
(3,'https://picsum.photos/seed/golden1/500/350'),(3,'https://picsum.photos/seed/golden2/500/350'),(3,'https://picsum.photos/seed/golden3/500/350'),
(4,'https://picsum.photos/seed/cv1/500/350'),(4,'https://picsum.photos/seed/cv2/500/350'),
(5,'https://picsum.photos/seed/palm1/500/350'),(5,'https://picsum.photos/seed/palm2/500/350'),
(6,'https://picsum.photos/seed/river1/500/350'),(6,'https://picsum.photos/seed/river2/500/350'),
(7,'https://picsum.photos/seed/heritage1/500/350'),(7,'https://picsum.photos/seed/heritage2/500/350');

-- Sample testimonials
INSERT INTO testimonials (user_id, hostel_id, display_name, rating, message, is_approved) VALUES
(NULL, 1, 'Ama K.', 5, 'Unity Hostel made my first year so much easier. Close to campus, great community, and the admin team responded to every request fast.', 1),
(NULL, 3, 'Kwame O.', 5, 'Golden Gate Lodge is worth every cedi. The rooftop common area is my favourite place to study in the evenings.', 1),
(NULL, 4, 'Efua B.', 4, 'Affordable and convenient — Campus View is a 5 minute walk to my first lecture. Wifi could be faster but overall a great stay.', 1),
(NULL, 2, 'Yaw A.', 5, 'Serene Heights lives up to its name. Quiet, clean, and the backup power meant I never missed an online class during outages.', 1),
(NULL, NULL, 'Abena D.', 5, 'Booking through Hostel Agency was so much easier than going hostel to hostel myself. The comparison tool saved me hours.', 1);

-- Sample news posts
INSERT INTO news_posts (title, body, is_published) VALUES
('Hostel Applications Now Open for 2026/2027', 'Applications for the upcoming academic year are now open across all seven partner hostels. Early applicants get priority room selection, so browse and book before rooms fill up.', 1),
('New Payment Option: Pay in 3 Installments', 'You can now split your hostel fees into three manageable payments via Paystack — 40% to secure your room, then two follow-up payments over the semester. Look for the payment plan option when booking.', 1),
('Now Live: Order Food & Essentials for Delivery', 'You can now order food, snacks, and everyday essentials right from your dashboard, with secure delivery payments through Paystack. Check out the new Shop section!', 1);

-- Sample shop products
INSERT INTO products (name, description, category, price, image, stock_quantity, is_available) VALUES
('Jollof Rice & Chicken', 'A generous portion of jollof rice served with grilled chicken and coleslaw.', 'food', 35.00, 'https://picsum.photos/seed/jollof/500/400', 50, 1),
('Waakye Special', 'Waakye with fish, gari, spaghetti, and shito — a campus favourite.', 'food', 30.00, 'https://picsum.photos/seed/waakye/500/400', 40, 1),
('Bottled Water (Pack of 12)', 'A pack of 12 x 500ml bottled water.', 'item', 24.00, 'https://picsum.photos/seed/water/500/400', 100, 1),
('Instant Noodles (Carton)', 'A carton of 40 packs of instant noodles.', 'item', 60.00, 'https://picsum.photos/seed/noodles/500/400', 30, 1),
('A4 Printing Paper (Ream)', '500 sheets of A4 printing paper for assignments and projects.', 'item', 28.00, 'https://picsum.photos/seed/paper/500/400', 60, 1),
('Reading Lamp', 'A rechargeable LED reading lamp — perfect for late-night study sessions.', 'other', 45.00, 'https://picsum.photos/seed/lamp/500/400', 20, 1),
('Fried Rice & Sausage', 'Fried rice served with grilled sausage and fresh salad.', 'food', 32.00, 'https://picsum.photos/seed/friedrice/500/400', 45, 1),
('Toiletries Bundle', 'Soap, toothpaste, toothbrush, and sponge — a handy essentials bundle.', 'item', 38.00, 'https://picsum.photos/seed/toiletries/500/400', 35, 1);
