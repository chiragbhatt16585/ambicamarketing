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
    
    // Fetch business categories
    $businessCategories = $db->fetchAll("SELECT * FROM business_categories WHERE is_active = TRUE ORDER BY display_order");
    
    // Fetch featured products
    $featuredProducts = $db->fetchAll("
        SELECT p.*, bc.name as business_category_name, pc.name as product_category_name 
        FROM products p 
        JOIN business_categories bc ON p.business_category_id = bc.id 
        LEFT JOIN product_categories pc ON p.product_category_id = pc.id 
        WHERE p.is_featured = TRUE AND p.is_active = TRUE 
        ORDER BY p.display_order 
        LIMIT 6
    ");
    
    // Fetch recent contacts count
    $recentContacts = $db->fetch("SELECT COUNT(*) as count FROM contacts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $totalContacts = $db->fetch("SELECT COUNT(*) as count FROM contacts");
    
} catch (Exception $e) {
    // Fallback to static data if database fails
    $companyData = [
        'company_name' => 'Ambica Marketing',
        'company_email' => 'automation.ambica@gmail.com',
        'company_phone' => '+91 9924278593',
        'company_address' => 'C-73, Arihantnagar Society, Nikol Road, Naroda, Ahmedabad, Gujarat-382330.',
        'company_description' => 'We are a glorious trader, manufacturer and supplier of high quality CNC Machines parts, Oil & Grease since 2009 and now we also supply smart gate automation system, road safety products, mechanical car parking, home automation system and much more since 2021.',
        'working_hours' => 'Mon - Sat: 9:00 AM - 7:00 PM'
    ];
    $businessCategories = [];
    $featuredProducts = [];
    $recentContacts = ['count' => 0];
    $totalContacts = ['count' => 0];
}

// Fetch banner slides from API if available
$bannerSlides = [];
if (file_exists('api/website-data.php')) {
    $apiResponse = @file_get_contents('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/api/website-data.php');
    if ($apiResponse) {
        $apiData = json_decode($apiResponse, true);
        if (!empty($apiData['data']['banner_slides'])) {
            $bannerSlides = $apiData['data']['banner_slides'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($companyData['company_name'] ?? 'Ambica Marketing'); ?> - Home Automation & Security Solutions</title>
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
                    <img src="assets/images/logo.png" alt="<?php echo htmlspecialchars($companyData['company_name'] ?? 'Ambica Marketing'); ?>" class="logo-img" onerror="this.onerror=null; this.src='assets/images/logo.jpg'; this.onerror=function(){this.style.display='none'; this.nextElementSibling.style.display='block';}">
                    <h2 style="display: none;"><?php echo htmlspecialchars($companyData['company_name'] ?? 'Ambica Marketing'); ?></h2>
                </div>
                <ul class="nav-menu">
                    <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="cnc.php" class="nav-link">CNC Machine Spare Parts</a></li>
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

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-slider-container">
            <div class="hero-slider" id="heroSlider">
                <?php if (!empty($bannerSlides)): ?>
                    <?php foreach ($bannerSlides as $i => $slide): ?>
                    <div class="hero-slide<?php if ($i === 0) echo ' active'; ?>">
                        <div class="hero-container">
                            <div class="hero-content">
                                <h1 class="hero-title"><?php echo htmlspecialchars($slide['title'] ?? ''); ?></h1>
                                <p class="hero-subtitle"><?php echo htmlspecialchars($slide['subtitle'] ?? ''); ?></p>
                                <div class="hero-buttons">
                                    <a href="#products" class="btn btn-primary">Explore Products</a>
                                    <a href="#contact" class="btn btn-secondary">Get Quote</a>
                                </div>
                            </div>
                            <div class="hero-image">
                                <img src="<?php echo htmlspecialchars($slide['image_url']); ?>" alt="Banner Image">
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="hero-slide active">
                        <div class="hero-container">
                            <div class="hero-content">
                                <h1 class="hero-title">Smart Security & Home Automation Solutions</h1>
                                <p class="hero-subtitle">Transform your home and business with cutting-edge home automation systems and advanced boom barriers for complete protection.</p>
                                <div class="hero-buttons">
                                    <a href="#products" class="btn btn-primary">Explore Products</a>
                                    <a href="#contact" class="btn btn-secondary">Get Quote</a>
                                </div>
                            </div>
                            <div class="hero-image">
                                <img src="assets/images/hero-security.jpg" alt="Banner Image">
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="hero-slider-dots" id="heroSliderDots"></div>
        </div>
    </section>

    <!-- Business Sections -->
    <section class="business-sections">
        <div class="container">
            <div class="business-grid">
                <?php foreach ($businessCategories as $category): ?>
                <div class="business-card">
                    <div class="business-icon"><i class="<?php echo htmlspecialchars($category['icon_class']); ?>"></i></div>
                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                    <p><?php echo htmlspecialchars($category['description']); ?></p>
                    <a href="<?php echo htmlspecialchars($category['slug']); ?>.html" class="btn btn-primary">Explore <?php echo htmlspecialchars(explode(' ', $category['name'])[0]); ?> Business</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Advanced Security</h3>
                    <p>State-of-the-art home automation and surveillance systems for complete protection.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h3>Home Automation</h3>
                    <p>Smart home solutions that make your life easier and more secure.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <h3>Boom Barriers</h3>
                    <p>Reliable access control systems for residential and commercial properties.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <?php if (!empty($featuredProducts)): ?>
    <section id="products" class="products">
        <div class="container">
            <div class="section-header">
                <h2>Featured Products</h2>
                <p>Discover our most popular and innovative solutions</p>
            </div>
            <div class="products-grid">
                <?php foreach ($featuredProducts as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.src='assets/images/placeholder.jpg'">
                    </div>
                    <div class="product-content">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="product-category"><?php echo htmlspecialchars($product['business_category_name']); ?></p>
                        <p class="product-description"><?php echo htmlspecialchars($product['short_description']); ?></p>
                        <a href="#contact" class="btn btn-primary">Get Quote</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Catalogue Section -->
    <section id="catalogue" class="catalogue">
        <div class="container">
            <div class="section-header">
                <h2>Product Catalogues</h2>
                <p>Download our comprehensive product catalogues for detailed information</p>
            </div>
            <div class="catalogue-grid">
                <div class="catalogue-card">
                    <div class="catalogue-icon">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <h3>Complete Product Catalogue</h3>
                    <p>Comprehensive catalogue featuring all our security and automation solutions</p>
                    <a href="assets/documents/product-catalogue.pdf" class="btn btn-primary" target="_blank">
                        <i class="fas fa-download"></i> Download PDF
                    </a>
                </div>
                <div class="catalogue-card">
                    <div class="catalogue-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h3>Home Automation Catalogue</h3>
                    <p>Smart home solutions and automation products catalogue</p>
                    <a href="assets/documents/automation-catalogue.pdf" class="btn btn-primary" target="_blank">
                        <i class="fas fa-download"></i> Download PDF
                    </a>
                </div>
                <div class="catalogue-card">
                    <div class="catalogue-icon">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <h3>Boom Barriers Catalogue</h3>
                    <p>Access control and boom barrier systems catalogue</p>
                    <a href="assets/documents/boom-barrier-catalogue.pdf" class="btn btn-primary" target="_blank">
                        <i class="fas fa-download"></i> Download PDF
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>About <?php echo htmlspecialchars($companyData['company_name'] ?? 'Ambica Marketing'); ?></h2>
                    <p><?php echo htmlspecialchars($companyData['company_description'] ?? 'We are a glorious trader, manufacturer and supplier of high quality CNC Machines parts, Oil & Grease since 2009 and now we also supply smart gate automation system, road safety products, mechanical car parking, home automation system and much more since 2021.'); ?></p>
                    <div class="about-stats">
                        <div class="stat">
                            <h3>500+</h3>
                            <p>Happy Customers</p>
                        </div>
                        <div class="stat">
                            <h3>1000+</h3>
                            <p>Installations</p>
                        </div>
                        <div class="stat">
                            <h3>24/7</h3>
                            <p>Support</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <div class="section-header">
                <h2>Contact Us</h2>
                <p>Get in touch for professional security solutions</p>
            </div>
            <div class="contact-content">
                <div class="contact-info">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h4>Address</h4>
                            <p><?php echo htmlspecialchars($companyData['company_address'] ?? 'C-73, Arihantnagar Society, Nikol Road, Naroda, Ahmedabad, Gujarat-382330.'); ?></p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div>
                            <h4>Phone</h4>
                            <p><a href="tel:<?php echo htmlspecialchars($companyData['company_phone'] ?? '+91 9924278593'); ?>"><?php echo htmlspecialchars($companyData['company_phone'] ?? '+91 9924278593'); ?></a></p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h4>Email</h4>
                            <p><a href="mailto:<?php echo htmlspecialchars($companyData['company_email'] ?? 'automation.ambica@gmail.com'); ?>"><?php echo htmlspecialchars($companyData['company_email'] ?? 'automation.ambica@gmail.com'); ?></a></p>
                        </div>
                    </div>
                    <?php if (!empty($companyData['whatsapp_number'])): ?>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <div>
                            <h4>WhatsApp</h4>
                            <p><a href="https://wa.me/<?php echo htmlspecialchars(str_replace(['+', ' ', '-'], '', $companyData['whatsapp_number'])); ?>" target="_blank"><?php echo htmlspecialchars($companyData['whatsapp_number']); ?></a></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($companyData['working_hours'])): ?>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h4>Working Hours</h4>
                            <p><?php echo htmlspecialchars($companyData['working_hours']); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="contact-form">
                    <form id="contactForm">
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

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><?php echo htmlspecialchars($companyData['company_name'] ?? 'Ambica Marketing'); ?></h3>
                    <p>Your trusted partner for home automation and security solutions.</p>
                    <div class="social-links">
                        <?php if (!empty($companyData['facebook_url'])): ?>
                        <a href="<?php echo htmlspecialchars($companyData['facebook_url']); ?>" target="_blank"><i class="fab fa-facebook"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($companyData['twitter_url'])): ?>
                        <a href="<?php echo htmlspecialchars($companyData['twitter_url']); ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($companyData['instagram_url'])): ?>
                        <a href="<?php echo htmlspecialchars($companyData['instagram_url']); ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($companyData['linkedin_url'])): ?>
                        <a href="<?php echo htmlspecialchars($companyData['linkedin_url']); ?>" target="_blank"><i class="fab fa-linkedin"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="automation.php">Automation</a></li>
                        <li><a href="#catalogue">Catalogue</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Services</h4>
                    <ul>
                        <li><a href="#">Home Automation</a></li>
                        <li><a href="#">Boom Barriers</a></li>
                        <li><a href="#">Installation</a></li>
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
                    <?php if (!empty($companyData['whatsapp_number'])): ?>
                    <div class="footer-contact-item">
                        <i class="fab fa-whatsapp"></i>
                        <a href="https://wa.me/<?php echo htmlspecialchars(str_replace(['+', ' ', '-'], '', $companyData['whatsapp_number'])); ?>" target="_blank"><?php echo htmlspecialchars($companyData['whatsapp_number']); ?></a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($companyData['company_name'] ?? 'Ambica Marketing'); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html> 