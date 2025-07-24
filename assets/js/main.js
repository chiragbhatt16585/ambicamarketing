// Main JavaScript for Ambica Marketing Website
document.addEventListener('DOMContentLoaded', function() {
    initializeWebsite();
});

async function initializeWebsite() {
    try {
        // Initialize mobile menu
        initializeMobileMenu();
        
        // Load products dynamically
        await loadProductsFromAPI();
        
        // Initialize contact forms
        initializeContactForms();
        
        // Initialize smooth scrolling
        initializeSmoothScrolling();
        
        // Initialize lazy loading
        initializeLazyLoading();
        
    } catch (error) {
        console.error('Error initializing website:', error);
    }
}

function initializeMobileMenu() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
        
        // Close menu when clicking on a link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });
    }
}

async function loadProductsFromAPI() {
    try {
        // Load business categories
        const businessCategoriesResponse = await fetch('api/products.php?action=business-categories');
        const businessCategories = await businessCategoriesResponse.json();
        
        if (businessCategories.success && businessCategories.data) {
            // Update homepage business sections if they exist
            updateHomepageBusinessSections(businessCategories.data);
        }
        
        // Load products for current page
        const currentPage = getCurrentPage();
        if (currentPage === 'automation' || currentPage === 'cnc') {
            await loadProductsForPage(currentPage);
        }
        
    } catch (error) {
        console.error('Error loading products from API:', error);
        // Fallback to static content if API fails
        loadStaticProducts();
    }
}

function getCurrentPage() {
    const path = window.location.pathname;
    if (path.includes('automation.html')) return 'automation';
    if (path.includes('cnc.html')) return 'cnc';
    return 'home';
}

function updateHomepageBusinessSections(categories) {
    const businessGrid = document.querySelector('.business-grid');
    if (!businessGrid) return;
    
    businessGrid.innerHTML = categories.map(category => `
        <div class="business-card">
            <div class="business-icon">
                <i class="${category.icon_class || 'fas fa-building'}"></i>
            </div>
            <h3>${category.name}</h3>
            <p>${category.description || ''}</p>
            <a href="${category.slug === 'cnc-machine-spare-parts' ? 'cnc.html' : 'automation.html'}" class="btn btn-primary">
                Explore Products
            </a>
        </div>
    `).join('');
}

async function loadProductsForPage(pageType) {
    try {
        // Get business category ID based on page
        const businessCategorySlug = pageType === 'cnc' ? 'cnc-machine-spare-parts' : 'automation-road-safety';
        
        // Load products for this business category
        const productsResponse = await fetch(`api/products.php?action=products&business_id=${businessCategorySlug}`);
        const products = await productsResponse.json();
        
        if (products.success && products.data) {
            displayProductsByCategory(products.data, pageType);
        }
        
    } catch (error) {
        console.error('Error loading products for page:', error);
        // Fallback to static content
        loadStaticProducts();
    }
}

function displayProductsByCategory(products, pageType) {
    const productsGrid = document.getElementById('productsGrid');
    if (!productsGrid) return;
    
    // Group products by category
    const productsByCategory = groupProductsByCategory(products);
    
    let html = '';
    
    // Create sections for each category
    Object.keys(productsByCategory).forEach((categoryName, index) => {
        const categoryProducts = productsByCategory[categoryName];
        const isAlternate = index % 2 === 1;
        
        html += `
            <section class="products" ${isAlternate ? 'style="background: #f8fafc;"' : ''}>
                <div class="container">
                    <div class="section-header">
                        <h2><i class="fas fa-${getCategoryIcon(categoryName)}"></i> ${categoryName}</h2>
                        <p>${getCategoryDescription(categoryName)}</p>
                    </div>
                    <div class="products-grid">
                        ${categoryProducts.map(product => createProductCard(product)).join('')}
                    </div>
                </div>
            </section>
        `;
    });
    
    // Replace the entire products section
    const productsSection = document.querySelector('.products');
    if (productsSection) {
        productsSection.outerHTML = html;
    }
}

function groupProductsByCategory(products) {
    const grouped = {};
    
    products.forEach(product => {
        const categoryName = product.product_category_name || 'Other Products';
        if (!grouped[categoryName]) {
            grouped[categoryName] = [];
        }
        grouped[categoryName].push(product);
    });
    
    return grouped;
}

function getCategoryIcon(categoryName) {
    const iconMap = {
        'Gate Automation': 'door-open',
        'Security System': 'shield-alt',
        'Road Safety': 'road',
        'Accessories': 'tools',
        'Machine Parts': 'cog',
        'Oil & Grease': 'oil-can'
    };
    
    return iconMap[categoryName] || 'box';
}

function getCategoryDescription(categoryName) {
    const descriptionMap = {
        'Gate Automation': 'Advanced gate automation solutions for enhanced security and convenience',
        'Security System': 'Professional security systems for comprehensive protection',
        'Road Safety': 'Essential road safety products for traffic management and accident prevention',
        'Accessories': 'Essential accessories and components for automation and security systems',
        'Machine Parts': 'Essential CNC machine spare parts',
        'Oil & Grease': 'High quality lubricants and greases'
    };
    
    return descriptionMap[categoryName] || 'Quality products for your needs';
}

function createProductCard(product) {
    const imageHtml = product.image_url ? 
        `<img src="${product.image_url}" alt="${product.name}" class="product-image" onerror="this.style.display='none'">` : 
        `<div class="product-icon"><i class="fas fa-box"></i></div>`;
    
    return `
        <div class="product-card">
            ${imageHtml}
            <div class="product-content">
                <h3 class="product-title">${product.name}</h3>
                <p class="product-description">${product.short_description || product.description || ''}</p>
                ${product.price ? `<div class="product-price">₹${product.price.toLocaleString()}</div>` : ''}
                <button class="btn btn-primary" onclick="enquireProduct('${product.name}')">Enquire Now</button>
            </div>
        </div>
    `;
}

function loadStaticProducts() {
    // Fallback static products if API fails
    console.log('Loading static products as fallback');
    
    // This will use the existing static HTML content
    // No additional action needed as the HTML is already in place
}

function initializeContactForms() {
    // Handle contact form submissions
    const contactForms = document.querySelectorAll('form[id*="contactForm"]');
    
    contactForms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            try {
                submitButton.disabled = true;
                submitButton.textContent = 'Sending...';
                
                const response = await fetch('api/contacts.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage('Message sent successfully! We will get back to you soon.', 'success');
                    form.reset();
                } else {
                    showMessage(result.error || 'Error sending message. Please try again.', 'error');
                }
                
            } catch (error) {
                console.error('Error submitting form:', error);
                showMessage('Error sending message. Please try again.', 'error');
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        });
    });
}

function initializeSmoothScrolling() {
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

function initializeLazyLoading() {
    // Lazy loading for images
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

function enquireProduct(productName) {
    // Open WhatsApp with product enquiry
    const phoneNumber = '+919924278593';
    const message = `Hi, I'm interested in ${productName}. Please provide more details.`;
    const whatsappUrl = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
    
    window.open(whatsappUrl, '_blank');
}

function showMessage(message, type = 'info') {
    // Create message element
    const messageDiv = document.createElement('div');
    messageDiv.className = `message message-${type}`;
    messageDiv.textContent = message;
    messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        z-index: 1000;
        background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8'};
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(messageDiv);
    
    // Remove message after 5 seconds
    setTimeout(() => {
        if (messageDiv.parentNode) {
            messageDiv.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                if (messageDiv.parentNode) {
                    document.body.removeChild(messageDiv);
                }
            }, 300);
        }
    }, 5000);
}

// Add CSS animations for messages
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Handle contact form submissions (legacy support)
function handleContactForm(formData) {
    // This function is called by the contact form
    // The actual submission is now handled by the form event listener
    console.log('Contact form submitted:', formData);
}

// Admin authentication (existing code)
class AdminAuth {
    constructor() {
        this.isAuthenticated = this.checkAuth();
        this.redirectToDashboard();
    }
    
    checkAuth() {
        return localStorage.getItem('adminToken') === 'demo-token';
    }
    
    redirectToDashboard() {
        if (this.isAuthenticated && window.location.pathname.includes('login.html')) {
            window.location.href = 'dashboard.html';
        }
    }
}

// Initialize admin authentication only on admin pages
let adminAuth;
if (window.location.pathname.includes('admin/')) {
    adminAuth = new AdminAuth();
} 