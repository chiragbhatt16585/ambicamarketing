// Admin Dashboard JavaScript
class AdminDashboard {
    constructor() {
        this.currentSection = 'overview';
        this.businessCategories = [];
        this.productCategories = [];
        this.products = [];
        this.contacts = [];
        this.settings = {};
        
        this.init();
    }
    
    async init() {
        await this.loadDashboardData();
        this.setupEventListeners();
        this.showSection('overview');
    }
    
    async loadDashboardData() {
        try {
            // Load all data in parallel
            const [stats, businessCategories, productCategories, products, contacts, settings] = await Promise.all([
                this.fetchAPI('../api/products.php?action=stats'),
                this.fetchAPI('../api/products.php?action=business-categories'),
                this.fetchAPI('../api/products.php?action=product-categories'),
                this.fetchAPI('../api/products.php?action=products'),
                this.fetchAPI('../api/contacts.php'),
                this.fetchAPI('../api/settings.php')
            ]);
            
            this.businessCategories = businessCategories.data || [];
            this.productCategories = productCategories.data || [];
            this.products = products.data || [];
            this.contacts = contacts.data || [];
            this.settings = settings.data || {};
            
            // Update dashboard stats
            this.updateDashboardStats(stats.data);
            this.updateRecentContacts();
            
            // Populate tables
            this.populateBusinessCategoriesTable();
            this.populateProductCategoriesTable();
            this.populateProductsTable();
            this.populateContactsTable();
            this.populateSettingsForm();
            
            // Populate filters
            this.populateFilters();
            
        } catch (error) {
            console.error('Error loading dashboard data:', error);
            this.showMessage('Error loading dashboard data', 'error');
        }
    }
    
    async fetchAPI(url, options = {}) {
        try {
            const response = await fetch(url, {
                method: options.method || 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                },
                body: options.body ? JSON.stringify(options.body) : undefined
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }
    
    updateDashboardStats(stats) {
        document.getElementById('totalProducts').textContent = stats.total_products || 0;
        document.getElementById('totalBusinessCategories').textContent = stats.business_categories || 0;
        document.getElementById('totalProductCategories').textContent = stats.product_categories || 0;
        document.getElementById('totalContacts').textContent = stats.total_contacts || 0;
    }
    
    updateRecentContacts() {
        const recentContacts = this.contacts.slice(0, 5);
        const container = document.getElementById('recentContacts');
        
        if (recentContacts.length === 0) {
            container.innerHTML = '<p>No recent contacts</p>';
            return;
        }
        
        container.innerHTML = recentContacts.map(contact => `
            <div class="recent-item">
                <div class="recent-item-header">
                    <strong>${contact.name}</strong>
                    <span class="recent-date">${new Date(contact.created_at).toLocaleDateString()}</span>
                </div>
                <div class="recent-item-content">
                    <p>${contact.email}</p>
                    <p class="recent-message">${contact.message.substring(0, 100)}${contact.message.length > 100 ? '...' : ''}</p>
                </div>
            </div>
        `).join('');
    }
    
    populateBusinessCategoriesTable() {
        const tbody = document.getElementById('businessCategoriesTable');
        tbody.innerHTML = this.businessCategories.map(category => `
            <tr>
                <td>${category.name}</td>
                <td>${category.slug}</td>
                <td><i class="${category.icon_class || 'fas fa-building'}"></i></td>
                <td>${category.display_order}</td>
                <td>
                    <span class="status-badge ${category.is_active ? 'active' : 'inactive'}">
                        ${category.is_active ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td>
                    <button onclick="dashboard.editBusinessCategory(${category.id})" class="btn btn-small btn-edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="dashboard.deleteBusinessCategory(${category.id})" class="btn btn-small btn-delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }
    
    populateProductCategoriesTable() {
        const tbody = document.getElementById('productCategoriesTable');
        tbody.innerHTML = this.productCategories.map(category => {
            const businessCategory = this.businessCategories.find(bc => bc.id == category.business_category_id);
            return `
                <tr>
                    <td>${category.name}</td>
                    <td>${businessCategory ? businessCategory.name : 'N/A'}</td>
                    <td>${category.slug}</td>
                    <td><i class="${category.icon_class || 'fas fa-tag'}"></i></td>
                    <td>${category.display_order}</td>
                    <td>
                        <span class="status-badge ${category.is_active ? 'active' : 'inactive'}">
                            ${category.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td>
                        <button onclick="dashboard.editProductCategory(${category.id})" class="btn btn-small btn-edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="dashboard.deleteProductCategory(${category.id})" class="btn btn-small btn-delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }
    
    populateProductsTable() {
        const tbody = document.getElementById('productsTable');
        tbody.innerHTML = this.products.map(product => {
            const businessCategory = this.businessCategories.find(bc => bc.id == product.business_category_id);
            const productCategory = this.productCategories.find(pc => pc.id == product.product_category_id);
            
            return `
                <tr>
                    <td>
                        ${product.image_url ? 
                            `<img src="../${product.image_url}" alt="${product.name}" class="product-thumbnail">` : 
                            '<div class="no-image">No Image</div>'
                        }
                    </td>
                    <td>${product.name}</td>
                    <td>${businessCategory ? businessCategory.name : 'N/A'}</td>
                    <td>${productCategory ? productCategory.name : 'N/A'}</td>
                    <td>₹${product.price ? product.price.toLocaleString() : '0'}</td>
                    <td>
                        <span class="status-badge ${product.is_featured ? 'featured' : 'normal'}">
                            ${product.is_featured ? 'Featured' : 'Normal'}
                        </span>
                    </td>
                    <td>
                        <span class="status-badge ${product.is_active ? 'active' : 'inactive'}">
                            ${product.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td>
                        <button onclick="dashboard.editProduct(${product.id})" class="btn btn-small btn-edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="dashboard.deleteProduct(${product.id})" class="btn btn-small btn-delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }
    
    populateContactsTable() {
        const tbody = document.getElementById('contactsTable');
        tbody.innerHTML = this.contacts.map(contact => `
            <tr>
                <td>${contact.name}</td>
                <td>${contact.email}</td>
                <td>${contact.phone || 'N/A'}</td>
                <td>${contact.product_interest || 'N/A'}</td>
                <td>${contact.message.substring(0, 50)}${contact.message.length > 50 ? '...' : ''}</td>
                <td>
                    <span class="status-badge ${contact.status}">
                        ${contact.status.charAt(0).toUpperCase() + contact.status.slice(1)}
                    </span>
                </td>
                <td>${new Date(contact.created_at).toLocaleDateString()}</td>
                <td>
                    <button onclick="dashboard.viewContact(${contact.id})" class="btn btn-small btn-view">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="dashboard.updateContactStatus(${contact.id})" class="btn btn-small btn-edit">
                        <i class="fas fa-check"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }
    
    populateSettingsForm() {
        Object.keys(this.settings).forEach(key => {
            const element = document.getElementById(key);
            if (element) {
                element.value = this.settings[key];
            }
        });
    }
    
    populateFilters() {
        // Business category filter
        const businessFilter = document.getElementById('businessCategoryFilter');
        businessFilter.innerHTML = '<option value="">All Business Categories</option>' +
            this.businessCategories.map(category => 
                `<option value="${category.id}">${category.name}</option>`
            ).join('');
        
        // Product category filter
        const productFilter = document.getElementById('productCategoryFilter');
        productFilter.innerHTML = '<option value="">All Product Categories</option>' +
            this.productCategories.map(category => 
                `<option value="${category.id}">${category.name}</option>`
            ).join('');
    }
    
    setupEventListeners() {
        // Form submissions
        document.getElementById('businessCategoryForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveBusinessCategory();
        });
        
        document.getElementById('productCategoryForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveProductCategory();
        });
        
        document.getElementById('productForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveProduct();
        });
        
        document.getElementById('settingsForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveSettings();
        });
    }
    
    showSection(sectionId) {
        // Hide all sections
        document.querySelectorAll('.admin-section').forEach(section => {
            section.classList.remove('active');
        });
        
        // Remove active class from all nav buttons
        document.querySelectorAll('.nav-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Show selected section
        document.getElementById(sectionId).classList.add('active');
        
        // Add active class to corresponding nav button
        document.querySelector(`[onclick="showSection('${sectionId}')"]`).classList.add('active');
        
        this.currentSection = sectionId;
    }
    
    showModal(modalId) {
        document.getElementById(modalId).style.display = 'block';
        
        // Populate business categories in modals
        if (modalId === 'productCategoryModal' || modalId === 'productModal') {
            this.populateBusinessCategorySelects();
        }
    }
    
    closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
        this.resetForm(modalId);
    }
    
    resetForm(modalId) {
        const form = document.querySelector(`#${modalId} form`);
        if (form) {
            form.reset();
            form.querySelector('input[type="hidden"]').value = '';
        }
    }
    
    populateBusinessCategorySelects() {
        const selects = [
            document.getElementById('productCategoryBusiness'),
            document.getElementById('productCategoryBusiness')
        ];
        
        selects.forEach(select => {
            if (select) {
                select.innerHTML = '<option value="">Select Business Category</option>' +
                    this.businessCategories.map(category => 
                        `<option value="${category.id}">${category.name}</option>`
                    ).join('');
            }
        });
    }
    
    async loadProductCategories() {
        const businessId = document.getElementById('productBusinessCategory').value;
        const productCategorySelect = document.getElementById('productCategory');
        
        if (!businessId) {
            productCategorySelect.innerHTML = '<option value="">Select Product Category</option>';
            return;
        }
        
        try {
            const response = await this.fetchAPI(`../api/products.php?action=product-categories&business_id=${businessId}`);
            const categories = response.data || [];
            
            productCategorySelect.innerHTML = '<option value="">Select Product Category</option>' +
                categories.map(category => 
                    `<option value="${category.id}">${category.name}</option>`
                ).join('');
        } catch (error) {
            console.error('Error loading product categories:', error);
        }
    }
    
    async saveBusinessCategory() {
        const form = document.getElementById('businessCategoryForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        const id = data.id;
        const method = id ? 'PUT' : 'POST';
        const url = id ? `../api/products.php?action=business-category&id=${id}` : '../api/products.php?action=business-category';
        
        try {
            await this.fetchAPI(url, { method, body: data });
            this.showMessage('Business category saved successfully', 'success');
            this.closeModal('businessCategoryModal');
            await this.loadDashboardData();
        } catch (error) {
            this.showMessage('Error saving business category', 'error');
        }
    }
    
    async saveProductCategory() {
        const form = document.getElementById('productCategoryForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        const id = data.id;
        const method = id ? 'PUT' : 'POST';
        const url = id ? `../api/products.php?action=product-category&id=${id}` : '../api/products.php?action=product-category';
        
        try {
            await this.fetchAPI(url, { method, body: data });
            this.showMessage('Product category saved successfully', 'success');
            this.closeModal('productCategoryModal');
            await this.loadDashboardData();
        } catch (error) {
            this.showMessage('Error saving product category', 'error');
        }
    }
    
    async saveProduct() {
        const form = document.getElementById('productForm');
        const formData = new FormData(form);
        
        const id = formData.get('id');
        const method = id ? 'PUT' : 'POST';
        const url = id ? `../api/products.php?action=product&id=${id}` : '../api/products.php?action=product';
        
        try {
            await this.fetchAPI(url, { 
                method, 
                body: formData,
                headers: {} // Don't set Content-Type for FormData
            });
            this.showMessage('Product saved successfully', 'success');
            this.closeModal('productModal');
            await this.loadDashboardData();
        } catch (error) {
            this.showMessage('Error saving product', 'error');
        }
    }
    
    async saveSettings() {
        const form = document.getElementById('settingsForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        try {
            await this.fetchAPI('../api/settings.php', { method: 'POST', body: data });
            this.showMessage('Settings saved successfully', 'success');
        } catch (error) {
            this.showMessage('Error saving settings', 'error');
        }
    }
    
    async editBusinessCategory(id) {
        const category = this.businessCategories.find(c => c.id == id);
        if (!category) return;
        
        document.getElementById('businessCategoryId').value = category.id;
        document.getElementById('businessCategoryName').value = category.name;
        document.getElementById('businessCategoryDescription').value = category.description || '';
        document.getElementById('businessCategoryIcon').value = category.icon_class || '';
        document.getElementById('businessCategoryOrder').value = category.display_order;
        document.getElementById('businessCategoryActive').checked = category.is_active;
        
        document.getElementById('businessCategoryModalTitle').textContent = 'Edit Business Category';
        this.showModal('businessCategoryModal');
    }
    
    async editProductCategory(id) {
        const category = this.productCategories.find(c => c.id == id);
        if (!category) return;
        
        document.getElementById('productCategoryId').value = category.id;
        document.getElementById('productCategoryBusiness').value = category.business_category_id;
        document.getElementById('productCategoryName').value = category.name;
        document.getElementById('productCategoryDescription').value = category.description || '';
        document.getElementById('productCategoryIcon').value = category.icon_class || '';
        document.getElementById('productCategoryOrder').value = category.display_order;
        document.getElementById('productCategoryActive').checked = category.is_active;
        
        document.getElementById('productCategoryModalTitle').textContent = 'Edit Product Category';
        this.showModal('productCategoryModal');
    }
    
    async editProduct(id) {
        const product = this.products.find(p => p.id == id);
        if (!product) return;
        
        document.getElementById('productId').value = product.id;
        document.getElementById('productBusinessCategory').value = product.business_category_id;
        document.getElementById('productName').value = product.name;
        document.getElementById('productShortDescription').value = product.short_description || '';
        document.getElementById('productDescription').value = product.description || '';
        document.getElementById('productPrice').value = product.price || '';
        document.getElementById('productOrder').value = product.display_order;
        document.getElementById('productFeatured').checked = product.is_featured;
        document.getElementById('productActive').checked = product.is_active;
        
        // Show current image if exists
        const currentImage = document.getElementById('currentImage');
        if (product.image_url) {
            currentImage.innerHTML = `<img src="../${product.image_url}" alt="${product.name}" style="max-width: 100px; height: auto;">`;
        } else {
            currentImage.innerHTML = '';
        }
        
        // Load product categories for this business category
        await this.loadProductCategories();
        document.getElementById('productCategory').value = product.product_category_id || '';
        
        document.getElementById('productModalTitle').textContent = 'Edit Product';
        this.showModal('productModal');
    }
    
    async deleteBusinessCategory(id) {
        if (!confirm('Are you sure you want to delete this business category?')) return;
        
        try {
            await this.fetchAPI(`../api/products.php?action=business-category&id=${id}`, { method: 'DELETE' });
            this.showMessage('Business category deleted successfully', 'success');
            await this.loadDashboardData();
        } catch (error) {
            this.showMessage('Error deleting business category', 'error');
        }
    }
    
    async deleteProductCategory(id) {
        if (!confirm('Are you sure you want to delete this product category?')) return;
        
        try {
            await this.fetchAPI(`../api/products.php?action=product-category&id=${id}`, { method: 'DELETE' });
            this.showMessage('Product category deleted successfully', 'success');
            await this.loadDashboardData();
        } catch (error) {
            this.showMessage('Error deleting product category', 'error');
        }
    }
    
    async deleteProduct(id) {
        if (!confirm('Are you sure you want to delete this product?')) return;
        
        try {
            await this.fetchAPI(`../api/products.php?action=product&id=${id}`, { method: 'DELETE' });
            this.showMessage('Product deleted successfully', 'success');
            await this.loadDashboardData();
        } catch (error) {
            this.showMessage('Error deleting product', 'error');
        }
    }
    
    async viewContact(id) {
        const contact = this.contacts.find(c => c.id == id);
        if (!contact) return;
        
        // Show contact details in a modal or alert
        alert(`Contact Details:\n\nName: ${contact.name}\nEmail: ${contact.email}\nPhone: ${contact.phone || 'N/A'}\nProduct Interest: ${contact.product_interest || 'N/A'}\n\nMessage:\n${contact.message}`);
    }
    
    async updateContactStatus(id) {
        try {
            await this.fetchAPI(`../api/contacts.php?id=${id}`, { 
                method: 'PUT', 
                body: { status: 'read' } 
            });
            this.showMessage('Contact status updated', 'success');
            await this.loadDashboardData();
        } catch (error) {
            this.showMessage('Error updating contact status', 'error');
        }
    }
    
    filterProducts() {
        const businessFilter = document.getElementById('businessCategoryFilter').value;
        const productFilter = document.getElementById('productCategoryFilter').value;
        const statusFilter = document.getElementById('statusFilter').value;
        
        const filteredProducts = this.products.filter(product => {
            if (businessFilter && product.business_category_id != businessFilter) return false;
            if (productFilter && product.product_category_id != productFilter) return false;
            if (statusFilter !== '' && product.is_active != statusFilter) return false;
            return true;
        });
        
        this.populateProductsTable(filteredProducts);
    }
    
    showMessage(message, type = 'info') {
        // Create a simple message display
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
        `;
        
        document.body.appendChild(messageDiv);
        
        setTimeout(() => {
            document.body.removeChild(messageDiv);
        }, 3000);
    }
}

// Global functions for onclick handlers
function showSection(sectionId) {
    dashboard.showSection(sectionId);
}

function showModal(modalId) {
    dashboard.showModal(modalId);
}

function closeModal(modalId) {
    dashboard.closeModal(modalId);
}

function loadProductCategories() {
    dashboard.loadProductCategories();
}

function filterProducts() {
    dashboard.filterProducts();
}

// Initialize dashboard only on dashboard page
let dashboard;
document.addEventListener('DOMContentLoaded', () => {
    // Only initialize AdminDashboard on dashboard.html
    if (window.location.pathname.includes('dashboard.html')) {
        dashboard = new AdminDashboard();
    }
});

// Admin authentication class (existing code)
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

// Login functionality
function handleLogin(event) {
    event.preventDefault();
    
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    // Simple demo authentication
    if (username === 'admin' && password === 'admin123') {
        localStorage.setItem('adminToken', 'demo-token');
        window.location.href = 'dashboard.html';
    } else {
        alert('Invalid username or password. Use admin/admin123');
    }
}

// Add login form handler if on login page
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('adminLoginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
});

function logout() {
    localStorage.removeItem('adminToken');
    window.location.href = 'login.html';
} 