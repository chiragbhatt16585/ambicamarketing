<?php
require_once 'api/config.php';

try {
    $db = Database::getInstance();
    // Fetch company settings
    $settings = $db->fetchAll("SELECT setting_key, setting_value FROM settings");
    $companyData = [];
    foreach ($settings as $setting) {
        $companyData[$setting['setting_key']] = $setting['setting_value'];
    }
    // Fetch CNC Machine Spare Parts, Oil & Grease (business_category_id = 1)
    $products = $db->fetchAll("
        SELECT p.*, bc.name as business_category_name, pc.name as product_category_name
        FROM products p
        JOIN business_categories bc ON p.business_category_id = bc.id
        LEFT JOIN product_categories pc ON p.product_category_id = pc.id
        WHERE p.is_active = 1 AND p.business_category_id = 1
        ORDER BY p.display_order, p.name
    ");
} catch (Exception $e) {
    $companyData = [
        'company_name' => 'Ambica Marketing',
    ];
    $products = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CNC Machine Spare Parts, Oil & Grease | <?php echo htmlspecialchars($companyData['company_name'] ?? 'Ambica Marketing'); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <img src="assets/images/logo.png" alt="<?php echo htmlspecialchars($companyData['company_name'] ?? 'Ambica Marketing'); ?>" class="logo-img">
                    <h2 style="display: none;">Ambica Marketing</h2>
                </div>
                <ul class="nav-menu">
                    <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="cnc.php" class="nav-link active">CNC Machine Spare Parts</a></li>
                    <li class="nav-item"><a href="automation.php" class="nav-link">Automation & Road Safety</a></li>
                    <li class="nav-item"><a href="#contact" class="nav-link">Contact</a></li>
                </ul>
                <div class="hamburger">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>
        </nav>
    </header>
    <section class="products" style="padding-top:100px;">
        <div class="container">
            <div class="section-header">
                <h1>CNC Machine Spare Parts, Oil & Grease</h1>
                <p>Explore our range of CNC machine parts, oil, and grease</p>
            </div>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php 
                        $imgPath = !empty($product['image_url']) ? $product['image_url'] : '';
                        $imgFile = $imgPath && strpos($imgPath, 'http') !== 0 ? __DIR__ . '/' . $imgPath : '';
                        if (!empty($imgPath) && ($imgFile && file_exists($imgFile) || strpos($imgPath, 'http') === 0)) : ?>
                            <img src="<?php echo htmlspecialchars($imgPath); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#ccc;font-size:2rem;background:#f8fafc;">No Image</div>
                        <?php endif; ?>
                    </div>
                    <div class="product-content">
                        <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="product-category"><?php echo htmlspecialchars($product['product_category_name']); ?></p>
                        <p class="product-description"><?php echo htmlspecialchars($product['short_description'] ?? $product['description']); ?></p>
                        <a href="#contact" class="btn btn-primary get-quote-btn" data-product="<?php echo htmlspecialchars($product['name']); ?>" data-category="<?php echo htmlspecialchars($product['product_category_name']); ?>">Get Quote</a>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                <p>No products found in this category.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <section id="contact" class="contact">
        <div class="container">
            <div class="section-header">
                <h2>Contact Us</h2>
                <p>Get in touch for professional CNC solutions</p>
            </div>
            <div class="contact-content">
                <div class="contact-info">
                    <h3><?php echo htmlspecialchars($companyData['company_name'] ?? 'Ambica Marketing'); ?></h3>
                    <p>Your trusted partner for CNC machine parts and lubricants.</p>
                </div>
                <div class="contact-form">
                    <form id="contactForm">
                        <input type="hidden" id="productInterest" name="product_interest">
                        <div class="form-group">
                            <input type="text" id="name" name="name" placeholder="Your Name" required>
                        </div>
                        <div class="form-group">
                            <input type="email" id="email" name="email" placeholder="Your Email" required>
                        </div>
                        <div class="form-group">
                            <input type="tel" id="phone" name="phone" placeholder="Your Phone">
                        </div>
                        <div class="form-group">
                            <textarea id="message" name="message" placeholder="Your Message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><?php echo htmlspecialchars($companyData['company_name'] ?? 'Ambica Marketing'); ?></h3>
                    <p>Your trusted partner for home automation and security solutions.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="cnc.php">CNC Parts</a></li>
                        <li><a href="automation.php">Automation</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <div class="footer-contact-item">
                        <i class="fas fa-phone"></i>
                        <a href="tel:<?php echo htmlspecialchars($companyData['company_phone'] ?? '+91 9924278593'); ?>"><?php echo htmlspecialchars($companyData['company_phone'] ?? '+91 9924278593'); ?></a>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fas fa-envelope"></i>
                        <a href="mailto:<?php echo htmlspecialchars($companyData['company_email'] ?? 'automation.ambica@gmail.com'); ?>"><?php echo htmlspecialchars($companyData['company_email'] ?? 'automation.ambica@gmail.com'); ?></a>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fas fa-clock"></i>
                        <span><?php echo htmlspecialchars($companyData['working_hours'] ?? 'Mon - Sat: 9:00 AM - 7:00 PM'); ?></span>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($companyData['company_name'] ?? 'Ambica Marketing'); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <script src="assets/js/main.js"></script>
    <script>
    function scrollToContactAndFill(product, category) {
        var info = '';
        if (category && product) {
            info = 'Product: ' + category + ' - ' + product;
        } else if (product) {
            info = 'Product: ' + product;
        } else {
            info = '';
        }
        document.getElementById('productInterest').value = info.replace(/^Product: /, ''); // hidden field: just category - product
        var message = document.getElementById('message');
        if (message) {
            if (!message.value.startsWith(info)) {
                message.value = info + '\n' + (message.value.replace(/^Product:.*\n/, ''));
            }
        }
        const contactSection = document.getElementById('contact');
        if (contactSection) {
            contactSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        document.getElementById('name').focus();
    }
    document.querySelectorAll('.get-quote-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const product = this.getAttribute('data-product');
            const category = this.getAttribute('data-category');
            scrollToContactAndFill(product, category);
        });
    });
    </script>
</body>
</html> 