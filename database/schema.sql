-- Ambica Marketing Database Schema
-- Create this database in your MySQL server

-- Create database
CREATE DATABASE IF NOT EXISTS ambica_marketing;
USE ambica_marketing;

-- Business Categories table
CREATE TABLE business_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    icon_class VARCHAR(100),
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Product Categories table (subcategories within each business)
CREATE TABLE product_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    description TEXT,
    icon_class VARCHAR(100),
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (business_category_id) REFERENCES business_categories(id) ON DELETE CASCADE
);

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_category_id INT NOT NULL,
    product_category_id INT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    image_url VARCHAR(500),
    price DECIMAL(10,2),
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (business_category_id) REFERENCES business_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (product_category_id) REFERENCES product_categories(id) ON DELETE SET NULL
);

-- Product Images table (for multiple images per product)
CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    alt_text VARCHAR(255),
    is_primary BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Contacts table
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    message TEXT NOT NULL,
    product_interest VARCHAR(255),
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admin users table
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    full_name VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Settings table
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO admin_users (username, password_hash, email, full_name) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'automation.ambica@gmail.com', 'Administrator');

-- Insert default settings
INSERT INTO settings (setting_key, setting_value) VALUES
('company_name', 'Ambica Marketing'),
('company_email', 'automation.ambica@gmail.com'),
('company_phone', '+91 9924278593'),
('company_address', 'C-73, Arihantnagar Society, Nikol Road, Naroda, Ahmedabad, Gujarat-382330.'),
('company_description', 'We are a glorious trader, manufacturer and supplier of high quality CNC Machines parts, Oil & Grease since 2009 and now we also supply smart gate automation system, road safety products, mechanical car parking, home automation system and much much more since 2021.'),
('whatsapp_number', '+919924278593'),
('working_hours', 'Mon - Sat: 9:00 AM - 7:00 PM'),
('facebook_url', ''),
('twitter_url', ''),
('instagram_url', ''),
('linkedin_url', '');

-- Insert Business Categories
INSERT INTO business_categories (name, slug, description, icon_class, display_order) VALUES
('CNC Machine Spare Parts, Oil & Grease', 'cnc-machine-spare-parts', 'High quality CNC Machines parts, Oil & Grease since 2009', 'fas fa-cogs', 1),
('Automation & Road Safety Products', 'automation-road-safety', 'Smart gate automation system, road safety products, mechanical car parking, home automation system and much more since 2021', 'fas fa-robot', 2);

-- Insert Product Categories for CNC Machine Spare Parts
INSERT INTO product_categories (business_category_id, name, slug, description, icon_class, display_order) VALUES
(1, 'Machine Parts', 'machine-parts', 'Essential CNC machine spare parts', 'fas fa-cog', 1),
(1, 'Oil & Grease', 'oil-grease', 'High quality lubricants and greases', 'fas fa-oil-can', 2);

-- Insert Product Categories for Automation & Road Safety
INSERT INTO product_categories (business_category_id, name, slug, description, icon_class, display_order) VALUES
(2, 'Gate Automation', 'gate-automation', 'Advanced gate automation solutions', 'fas fa-door-open', 1),
(2, 'Security System', 'security-system', 'Professional security systems', 'fas fa-shield-alt', 2),
(2, 'Road Safety', 'road-safety', 'Essential road safety products', 'fas fa-road', 3),
(2, 'Accessories', 'accessories', 'Essential accessories and components', 'fas fa-tools', 4);

-- Insert sample CNC products
INSERT INTO products (business_category_id, product_category_id, name, slug, description, short_description, image_url, price, is_featured, display_order) VALUES
(1, 1, 'Soft Jaws', 'soft-jaws', 'High quality soft jaws for CNC machines with precise gripping and durability', 'Precision soft jaws for secure workpiece holding', 'assets/images/products/cnc/soft-jaws.jpg', 2500.00, TRUE, 1),
(1, 1, 'T Nut', 't-nut', 'Standard T nuts for CNC machine tables with excellent strength and compatibility', 'Standard T nuts for secure table mounting', 'assets/images/products/cnc/t-nut.jpg', 150.00, FALSE, 2),
(1, 2, 'Oil & Grease', 'oil-grease', 'Premium quality lubricating oils and greases for CNC machines', 'High performance lubricants for optimal machine operation', 'assets/images/products/cnc/oil-grease.jpg', 850.00, TRUE, 3),
(1, 1, 'Boring Block', 'boring-block', 'Precision boring blocks for accurate hole boring operations', 'Precision boring blocks for accurate machining', 'assets/images/products/cnc/boring-block.jpg', 3200.00, FALSE, 4),
(1, 1, 'Sleeve', 'sleeve', 'High quality sleeves for CNC machine components', 'Durable sleeves for machine component protection', 'assets/images/products/cnc/sleeve.jpg', 450.00, FALSE, 5);

-- Insert sample Automation products
INSERT INTO products (business_category_id, product_category_id, name, slug, description, short_description, image_url, price, is_featured, display_order) VALUES
(2, 3, 'Boom Barrier', 'boom-barrier', 'Automatic boom barriers for parking lots, toll plazas, and restricted areas with reliable operation and safety features', 'Automatic boom barriers for secure access control', 'assets/images/products/automation/boom-barrier.jpg', 25999.00, TRUE, 1),
(2, 3, 'Flap Barrier', 'flap-barrier', 'High-speed flap barriers for pedestrian access control with smooth operation and durable construction', 'High-speed pedestrian access control', 'assets/images/products/automation/flap-barrier.jpg', 18999.00, FALSE, 2),
(2, 3, 'Tripod Turnstile', 'tripod-turnstile', 'Three-arm turnstiles for controlled pedestrian access with anti-backflow and emergency release features', 'Controlled pedestrian access system', 'assets/images/products/automation/tripod-turnstile.jpg', 15999.00, FALSE, 3),
(2, 3, 'Shutter Motor', 'shutter-motor', 'High-performance shutter motors for automatic door and gate operation with remote control capabilities', 'High-performance automatic door motors', 'assets/images/products/automation/shutter-motor.jpg', 12999.00, TRUE, 4),
(2, 3, 'Access Control', 'access-control', 'Comprehensive access control systems with card readers, biometric scanners, and management software', 'Comprehensive access control solutions', 'assets/images/products/automation/access-control.jpg', 22999.00, FALSE, 5),
(2, 4, 'AJAX', 'ajax', 'Professional wireless security systems with real-time monitoring, mobile app control, and reliable protection', 'Professional wireless security systems', 'assets/images/products/automation/ajax.jpg', 45999.00, TRUE, 6),
(2, 5, 'Speed Breakers', 'speed-breakers', 'Rubber and concrete speed breakers for traffic calming and speed control in residential and commercial areas', 'Traffic calming speed control solutions', 'assets/images/products/automation/speed-breakers.jpg', 3500.00, FALSE, 7),
(2, 5, 'Convex Mirror', 'convex-mirror', 'High-quality convex mirrors for blind spot visibility and traffic safety at intersections and parking areas', 'Blind spot visibility enhancement', 'assets/images/products/automation/convex-mirror.jpg', 1200.00, FALSE, 8),
(2, 5, 'CAT Eye', 'cat-eye', 'Reflective road markers and cat eyes for lane marking and road visibility enhancement', 'Road visibility enhancement markers', 'assets/images/products/automation/cat-eye.jpg', 85.00, FALSE, 9),
(2, 5, 'Corner Guard', 'corner-guard', 'Protective corner guards for buildings, walls, and structures to prevent damage from vehicles', 'Building protection from vehicle damage', 'assets/images/products/automation/corner-guard.jpg', 2800.00, FALSE, 10),
(2, 5, 'Tyre Killer', 'tyre-killer', 'Security tyre killers for restricted areas and high-security zones with automatic deployment systems', 'High-security vehicle deterrent system', 'assets/images/products/automation/tyre-killer.jpg', 89999.00, TRUE, 11),
(2, 6, 'RFID Readers', 'rfid-readers', 'High-frequency RFID card readers for access control systems with fast response and reliable operation', 'Fast response RFID access control', 'assets/images/products/automation/rfid-readers.jpg', 4500.00, FALSE, 12),
(2, 6, 'UHF Tags', 'uhf-tags', 'Ultra-high frequency RFID tags for long-range identification and tracking applications', 'Long-range RFID identification tags', 'assets/images/products/automation/uhf-tags.jpg', 250.00, FALSE, 13),
(2, 6, 'Jewellery Tags', 'jewellery-tags', 'Specialized RFID tags for jewellery security and inventory management systems', 'Jewellery security and inventory tags', 'assets/images/products/automation/jewellery-tags.jpg', 180.00, FALSE, 14),
(2, 6, 'Loop Detector', 'loop-detector', 'Inductive loop detectors for vehicle detection in parking systems and traffic management', 'Vehicle detection for parking systems', 'assets/images/products/automation/loop-detector.jpg', 3200.00, FALSE, 15),
(2, 6, 'Photo Sensor', 'photo-sensor', 'Photoelectric sensors for automatic door operation and presence detection systems', 'Automatic door presence detection', 'assets/images/products/automation/photo-sensor.jpg', 1800.00, FALSE, 16),
(2, 6, 'Push Button', 'push-button', 'Durable push buttons for manual control of automation systems and emergency operations', 'Manual control for automation systems', 'assets/images/products/automation/push-button.jpg', 450.00, FALSE, 17);

-- Create indexes for better performance
CREATE INDEX idx_products_business_category ON products(business_category_id);
CREATE INDEX idx_products_product_category ON products(product_category_id);
CREATE INDEX idx_products_slug ON products(slug);
CREATE INDEX idx_products_is_active ON products(is_active);
CREATE INDEX idx_products_is_featured ON products(is_featured);
CREATE INDEX idx_products_display_order ON products(display_order);
CREATE INDEX idx_product_categories_business ON product_categories(business_category_id);
CREATE INDEX idx_product_categories_slug ON product_categories(slug);
CREATE INDEX idx_business_categories_slug ON business_categories(slug);
CREATE INDEX idx_contacts_status ON contacts(status);
CREATE INDEX idx_contacts_created_at ON contacts(created_at);
CREATE INDEX idx_admin_users_username ON admin_users(username);

-- Create views for common queries
CREATE VIEW recent_contacts AS
SELECT * FROM contacts 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY created_at DESC;

CREATE VIEW featured_products AS
SELECT p.*, bc.name as business_category_name, pc.name as product_category_name
FROM products p
JOIN business_categories bc ON p.business_category_id = bc.id
LEFT JOIN product_categories pc ON p.product_category_id = pc.id
WHERE p.is_featured = TRUE AND p.is_active = TRUE
ORDER BY p.display_order;

CREATE VIEW product_categories_summary AS
SELECT 
    bc.name as business_category,
    pc.name as product_category,
    COUNT(p.id) as product_count
FROM business_categories bc
LEFT JOIN product_categories pc ON bc.id = pc.business_category_id
LEFT JOIN products p ON pc.id = p.product_category_id AND p.is_active = TRUE
GROUP BY bc.id, pc.id;

-- Create stored procedure for contact statistics
DELIMITER //
CREATE PROCEDURE GetContactStats()
BEGIN
    SELECT 
        COUNT(*) as total_contacts,
        COUNT(CASE WHEN status = 'new' THEN 1 END) as new_contacts,
        COUNT(CASE WHEN status = 'read' THEN 1 END) as read_contacts,
        COUNT(CASE WHEN status = 'replied' THEN 1 END) as replied_contacts,
        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as recent_contacts
    FROM contacts;
END //

-- Create stored procedure for product statistics
CREATE PROCEDURE GetProductStats()
BEGIN
    SELECT 
        COUNT(*) as total_products,
        COUNT(CASE WHEN is_active = TRUE THEN 1 END) as active_products,
        COUNT(CASE WHEN is_featured = TRUE THEN 1 END) as featured_products,
        COUNT(DISTINCT business_category_id) as business_categories,
        COUNT(DISTINCT product_category_id) as product_categories
    FROM products;
END //
DELIMITER ; 