// Admin Dashboard JavaScript
class AdminDashboard {
    constructor() {
        this.currentSection = 'overview';
        this.businessCategories = [];
        this.productCategories = [];
        this.products = [];
        this.contacts = [];
        this.settings = {};
        this.bannerSlides = [];
        
        this.init();
    }
    
    async init() {
        await this.loadDashboardData();
        await this.loadBannerSlides();
        this.setupEventListeners();
        this.showSection('overview');
        
        // Test settings population after a short delay
        setTimeout(() => {
            console.log('Testing settings population...');
            console.log('Settings object:', this.settings);
            console.log('Settings keys:', Object.keys(this.settings));
            this.populateSettingsForm();
        }, 1000);
    }
    
    async loadDashboardData() {
        try {
            console.log('Loading dashboard data...'); // Debug log
            
            // Load all data in parallel
            const [stats, businessCategories, productCategories, products, contacts, settings] = await Promise.all([
                this.fetchAPI('../api/products.php?action=stats'),
                this.fetchAPI('../api/products.php?action=business-categories'),
                this.fetchAPI('../api/products.php?action=product-categories'),
                this.fetchAPI('../api/products.php?action=products'),
                this.fetchAPI('../api/contacts.php'),
                this.fetchAPI('../api/settings.php')
            ]);
            
            console.log('API responses received:'); // Debug log
            console.log('Stats:', stats);
            console.log('Business Categories:', businessCategories);
            console.log('Product Categories:', productCategories);
            console.log('Products:', products);
            console.log('Contacts:', contacts);
            console.log('Settings:', settings);
            
            this.businessCategories = businessCategories.data || [];
            this.productCategories = productCategories.data || [];
            this.products = products.data || [];
            this.contacts = contacts.data || [];
            this.settings = settings.data || {};
            
            console.log('Processed settings:', this.settings); // Debug log
            
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
            this.showMessage('Error loading dashboard data: ' + error.message, 'error');
        }
    }
    
    async loadBannerSlides() {
        try {
            const response = await this.fetchAPI('../api/banner_slides.php');
            this.bannerSlides = response.data || [];
            this.populateBannerSlidesTable();
        } catch (error) {
            this.showMessage('Error loading banner slides: ' + error.message, 'error');
        }
    }
    populateBannerSlidesTable() {
        const tbody = document.getElementById('bannerSlidesTable');
        if (!tbody) return;
        tbody.innerHTML = this.bannerSlides.map(slide => `
            <tr>
                <td>${slide.image_url ? `<img src="../${slide.image_url}" alt="Banner" style="max-width:80px;max-height:50px;">` : ''}</td>
                <td>${slide.title || ''}</td>
                <td>${slide.subtitle || ''}</td>
                <td>${slide.display_order}</td>
                <td><span class="status-badge ${slide.is_active ? 'active' : 'inactive'}">${slide.is_active ? 'Active' : 'Inactive'}</span></td>
                <td>
                    <button onclick="dashboard.editBannerSlide(${slide.id})" class="btn btn-small btn-edit"><i class="fas fa-edit"></i></button>
                    <button onclick="dashboard.deleteBannerSlide(${slide.id})" class="btn btn-small btn-delete"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `).join('');
    }
    
    async fetchAPI(url, options = {}) {
        try {
            const config = {
                method: options.method || 'GET',
                headers: {
                    ...options.headers
                }
            };
            
            // Add authentication token
            const token = localStorage.getItem('adminToken');
            if (token) {
                config.headers['Authorization'] = `Bearer ${token}`;
            }
            
            // Don't set Content-Type for FormData
            if (!(options.body instanceof FormData)) {
                config.headers['Content-Type'] = 'application/json';
            }
            
            if (options.body) {
                if (options.body instanceof FormData) {
                    config.body = options.body;
                } else {
                    config.body = JSON.stringify(options.body);
                }
            }
            
            const response = await fetch(url, config);
            
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.error || `HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }
    
    updateDashboardStats(stats) {
        if (!stats) return;
        
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
        if (!tbody) return;
        
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
        if (!tbody) return;
        
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
    
    populateProductsTable(products = null) {
        const tbody = document.getElementById('productsTable');
        if (!tbody) return;
        
        const productsToShow = products || this.products;
        
        tbody.innerHTML = productsToShow.map(product => {
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
        if (!tbody) return;
        
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
        console.log('=== SETTINGS POPULATION DEBUG ===');
        console.log('Settings data:', this.settings);
        console.log('Settings type:', typeof this.settings);
        console.log('Settings keys:', Object.keys(this.settings || {}));
        
        // Check if settings exist
        if (!this.settings || Object.keys(this.settings).length === 0) {
            console.log('No settings data available');
            return;
        }
        
        // Direct mapping - no complex logic
        const mappings = [
            { key: 'company_name', id: 'companyName' },
            { key: 'company_email', id: 'companyEmail' },
            { key: 'company_phone', id: 'companyPhone' },
            { key: 'company_address', id: 'companyAddress' },
            { key: 'company_description', id: 'companyDescription' },
            { key: 'whatsapp_number', id: 'whatsappNumber' },
            { key: 'working_hours', id: 'workingHours' }
        ];
        
        mappings.forEach(mapping => {
            const element = document.getElementById(mapping.id);
            const value = this.settings[mapping.key];
            
            console.log(`Looking for element: ${mapping.id}`);
            console.log(`Value for ${mapping.key}: ${value}`);
            
            if (element) {
                element.value = value || '';
                console.log(`✓ Set ${mapping.id} to: ${value}`);
            } else {
                console.log(`✗ Element not found: ${mapping.id}`);
            }
        });
        
        console.log('=== END SETTINGS DEBUG ===');
    }
    
    // New function to force populate settings when section is shown
    async populateSettingsOnShow() {
        console.log('Forcing settings population...'); // Debug log
        
        // If settings are not loaded yet, load them
        if (!this.settings || Object.keys(this.settings).length === 0) {
            console.log('Settings not loaded, fetching...'); // Debug log
            try {
                const response = await this.fetchAPI('../api/settings.php');
                this.settings = response.data || {};
                console.log('Settings loaded:', this.settings); // Debug log
            } catch (error) {
                console.error('Error loading settings:', error);
                return;
            }
        }
        
        // Wait a bit for DOM to be ready
        setTimeout(() => {
            this.populateSettingsForm();
        }, 100);
    }
    
    populateFilters() {
        // Business category filter
        const businessFilter = document.getElementById('businessCategoryFilter');
        if (businessFilter) {
            businessFilter.innerHTML = '<option value="">All Business Categories</option>' +
                this.businessCategories.map(category => 
                    `<option value="${category.id}">${category.name}</option>`
                ).join('');
        }
        
        // Product category filter
        const productFilter = document.getElementById('productCategoryFilter');
        if (productFilter) {
            productFilter.innerHTML = '<option value="">All Product Categories</option>' +
                this.productCategories.map(category => 
                    `<option value="${category.id}">${category.name}</option>`
                ).join('');
        }
    }
    
    setupEventListeners() {
        // Form submissions
        const businessCategoryForm = document.getElementById('businessCategoryForm');
        if (businessCategoryForm) {
            businessCategoryForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.saveBusinessCategory();
            });
        }
        
        const productCategoryForm = document.getElementById('productCategoryForm');
        if (productCategoryForm) {
            productCategoryForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.saveProductCategory();
            });
        }
        
        const productForm = document.getElementById('productForm');
        if (productForm) {
            productForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.saveProduct();
            });
        }
        
        const settingsForm = document.getElementById('settingsForm');
        if (settingsForm) {
            settingsForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.saveSettings();
            });
        }
        const bannerSlideForm = document.getElementById('bannerSlideForm');
        if (bannerSlideForm) {
            bannerSlideForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                await this.saveBannerSlide();
            });
            document.getElementById('bannerSlideImage').addEventListener('change', function() {
                const preview = document.getElementById('currentBannerSlideImage');
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.innerHTML = `<img src='${e.target.result}' style='max-width:100px;max-height:60px;'>`;
                    };
                    reader.readAsDataURL(this.files[0]);
                } else {
                    preview.innerHTML = '';
                }
            });
        }
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
        const targetSection = document.getElementById(sectionId);
        if (targetSection) {
            targetSection.classList.add('active');
        }
        
        // Add active class to corresponding nav button
        const targetButton = document.querySelector(`[onclick="showSection('${sectionId}')"]`);
        if (targetButton) {
            targetButton.classList.add('active');
        }
        
        this.currentSection = sectionId;
        
        // Populate settings form when settings section is shown
        if (sectionId === 'settings') {
            this.populateSettingsOnShow();
        }
        if (sectionId === 'banner-slides') {
            this.loadBannerSlides();
        }
    }
    
    showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            // Only reset form if adding (not editing)
            if (modalId === 'bannerSlideModal' && !document.getElementById('bannerSlideId').value) {
                this.resetBannerSlideForm();
                document.getElementById('bannerSlideModalTitle').textContent = 'Add Banner Slide';
                document.getElementById('bannerSlideImage').required = true;
            }
            modal.style.display = 'block';
            
            // Populate business categories in modals
            if (modalId === 'productCategoryModal' || modalId === 'productModal') {
                this.populateBusinessCategorySelects();
            }
            if (modalId === 'bannerSlideModal') {
                this.resetBannerSlideForm();
                document.getElementById('bannerSlideModalTitle').textContent = 'Add Banner Slide';
                document.getElementById('bannerSlideImage').required = true;
            }
        }
    }
    
    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            this.resetForm(modalId);
        }
    }
    
    resetForm(modalId) {
        const form = document.querySelector(`#${modalId} form`);
        if (form) {
            form.reset();
            const hiddenInput = form.querySelector('input[type="hidden"]');
            if (hiddenInput) {
                hiddenInput.value = '';
            }
        }
    }
    
    populateBusinessCategorySelects() {
        const selects = [
            document.getElementById('productCategoryBusiness'),
            document.getElementById('productBusinessCategory')
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
            if (productCategorySelect) {
                productCategorySelect.innerHTML = '<option value="">Select Product Category</option>';
            }
            return;
        }
        
        try {
            const response = await this.fetchAPI(`../api/products.php?action=product-categories&business_id=${businessId}`);
            const categories = response.data || [];
            
            if (productCategorySelect) {
                productCategorySelect.innerHTML = '<option value="">Select Product Category</option>' +
                    categories.map(category => 
                        `<option value="${category.id}">${category.name}</option>`
                    ).join('');
            }
        } catch (error) {
            console.error('Error loading product categories:', error);
            this.showMessage('Error loading product categories', 'error');
        }
    }
    
    async saveBusinessCategory() {
        const form = document.getElementById('businessCategoryForm');
        if (!form) return;
        
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
            this.showMessage('Error saving business category: ' + error.message, 'error');
        }
    }
    
    async saveProductCategory() {
        const form = document.getElementById('productCategoryForm');
        if (!form) return;
        
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
            this.showMessage('Error saving product category: ' + error.message, 'error');
        }
    }
    
    async saveProduct() {
        const form = document.getElementById('productForm');
        if (!form) return;
        const formData = new FormData(form);
        const id = formData.get('id');
        if (id) {
            formData.append('id', id);
        }
        try {
            await this.fetchAPI('../api/products.php?action=product', { method: 'POST', body: formData });
            this.showMessage('Product saved successfully', 'success');
            this.closeModal('productModal');
            await this.loadDashboardData();
        } catch (error) {
            this.showMessage('Error saving product: ' + error.message, 'error');
        }
    }
    
    async saveSettings() {
        const form = document.getElementById('settingsForm');
        if (!form) return;
        
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        try {
            await this.fetchAPI('../api/settings.php', { method: 'POST', body: data });
            this.showMessage('Settings saved successfully', 'success');
        } catch (error) {
            this.showMessage('Error saving settings: ' + error.message, 'error');
        }
    }
    
    async editBusinessCategory(id) {
        const category = this.businessCategories.find(c => c.id == id);
        if (!category) return;
        
        const elements = {
            id: document.getElementById('businessCategoryId'),
            name: document.getElementById('businessCategoryName'),
            description: document.getElementById('businessCategoryDescription'),
            icon: document.getElementById('businessCategoryIcon'),
            order: document.getElementById('businessCategoryOrder'),
            active: document.getElementById('businessCategoryActive'),
            title: document.getElementById('businessCategoryModalTitle')
        };
        
        if (elements.id) elements.id.value = category.id;
        if (elements.name) elements.name.value = category.name;
        if (elements.description) elements.description.value = category.description || '';
        if (elements.icon) elements.icon.value = category.icon_class || '';
        if (elements.order) elements.order.value = category.display_order;
        if (elements.active) elements.active.checked = category.is_active;
        if (elements.title) elements.title.textContent = 'Edit Business Category';
        
        this.showModal('businessCategoryModal');
    }
    
    async editProductCategory(id) {
        const category = this.productCategories.find(c => c.id == id);
        if (!category) return;
        
        const elements = {
            id: document.getElementById('productCategoryId'),
            business: document.getElementById('productCategoryBusiness'),
            name: document.getElementById('productCategoryName'),
            description: document.getElementById('productCategoryDescription'),
            icon: document.getElementById('productCategoryIcon'),
            order: document.getElementById('productCategoryOrder'),
            active: document.getElementById('productCategoryActive'),
            title: document.getElementById('productCategoryModalTitle')
        };
        
        if (elements.id) elements.id.value = category.id;
        if (elements.business) elements.business.value = category.business_category_id;
        if (elements.name) elements.name.value = category.name;
        if (elements.description) elements.description.value = category.description || '';
        if (elements.icon) elements.icon.value = category.icon_class || '';
        if (elements.order) elements.order.value = category.display_order;
        if (elements.active) elements.active.checked = category.is_active;
        if (elements.title) elements.title.textContent = 'Edit Product Category';
        
        this.showModal('productCategoryModal');
    }
    
    async editProduct(id) {
        const product = this.products.find(p => p.id == id);
        if (!product) return;
        
        const elements = {
            id: document.getElementById('productId'),
            business: document.getElementById('productBusinessCategory'),
            name: document.getElementById('productName'),
            shortDesc: document.getElementById('productShortDescription'),
            description: document.getElementById('productDescription'),
            price: document.getElementById('productPrice'),
            order: document.getElementById('productOrder'),
            featured: document.getElementById('productFeatured'),
            active: document.getElementById('productActive'),
            title: document.getElementById('productModalTitle'),
            currentImage: document.getElementById('currentImage')
        };
        
        if (elements.id) elements.id.value = product.id;
        if (elements.business) elements.business.value = product.business_category_id;
        if (elements.name) elements.name.value = product.name;
        if (elements.shortDesc) elements.shortDesc.value = product.short_description || '';
        if (elements.description) elements.description.value = product.description || '';
        if (elements.price) elements.price.value = product.price || '';
        if (elements.order) elements.order.value = product.display_order;
        if (elements.featured) elements.featured.checked = product.is_featured;
        if (elements.active) elements.active.checked = product.is_active;
        if (elements.title) elements.title.textContent = 'Edit Product';
        
        // Show current image if exists
        if (elements.currentImage) {
            if (product.image_url) {
                elements.currentImage.innerHTML = `<img src="../${product.image_url}" alt="${product.name}" style="max-width: 100px; height: auto;">`;
            } else {
                elements.currentImage.innerHTML = '';
            }
        }
        
        // Load product categories for this business category
        await this.loadProductCategories();
        if (elements.business) elements.business.value = product.business_category_id;
        const productCategorySelect = document.getElementById('productCategory');
        if (productCategorySelect) {
            productCategorySelect.value = product.product_category_id || '';
        }
        
        this.showModal('productModal');
    }
    
    async deleteBusinessCategory(id) {
        if (!confirm('Are you sure you want to delete this business category?')) return;
        
        try {
            await this.fetchAPI(`../api/products.php?action=business-category&id=${id}`, { method: 'DELETE' });
            this.showMessage('Business category deleted successfully', 'success');
            await this.loadDashboardData();
        } catch (error) {
            this.showMessage('Error deleting business category: ' + error.message, 'error');
        }
    }
    
    async deleteProductCategory(id) {
        if (!confirm('Are you sure you want to delete this product category?')) return;
        
        try {
            await this.fetchAPI(`../api/products.php?action=product-category&id=${id}`, { method: 'DELETE' });
            this.showMessage('Product category deleted successfully', 'success');
            await this.loadDashboardData();
        } catch (error) {
            this.showMessage('Error deleting product category: ' + error.message, 'error');
        }
    }
    
    async deleteProduct(id) {
        if (!confirm('Are you sure you want to delete this product?')) return;
        
        try {
            await this.fetchAPI(`../api/products.php?action=product&id=${id}`, { method: 'DELETE' });
            this.showMessage('Product deleted successfully', 'success');
            await this.loadDashboardData();
        } catch (error) {
            this.showMessage('Error deleting product: ' + error.message, 'error');
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
            this.showMessage('Error updating contact status: ' + error.message, 'error');
        }
    }
    
    filterProducts() {
        const businessFilter = document.getElementById('businessCategoryFilter')?.value || '';
        const productFilter = document.getElementById('productCategoryFilter')?.value || '';
        const statusFilter = document.getElementById('statusFilter')?.value || '';
        
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
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 300px;
            word-wrap: break-word;
        `;
        
        document.body.appendChild(messageDiv);
        
        setTimeout(() => {
            if (document.body.contains(messageDiv)) {
                document.body.removeChild(messageDiv);
            }
        }, 5000);
    }
    async editBannerSlide(id) {
        const slide = this.bannerSlides.find(s => s.id == id);
        if (!slide) return;
        this.resetBannerSlideForm(); // Always reset before editing
        document.getElementById('bannerSlideId').value = slide.id;
        document.getElementById('bannerSlideTitle').value = slide.title || '';
        document.getElementById('bannerSlideSubtitle').value = slide.subtitle || '';
        document.getElementById('bannerSlideOrder').value = slide.display_order || 0;
        document.getElementById('bannerSlideActive').checked = !!slide.is_active;
        document.getElementById('currentBannerSlideImage').innerHTML = slide.image_url ? `<img src="../${slide.image_url}" style="max-width:100px;max-height:60px;">` : '';
        document.getElementById('bannerSlideImage').required = false;
        document.getElementById('bannerSlideModalTitle').textContent = 'Edit Banner Slide';
        document.getElementById('bannerSlideModal').style.display = 'block';
    }
    resetBannerSlideForm() {
        document.getElementById('bannerSlideForm').reset();
        document.getElementById('bannerSlideId').value = '';
        document.getElementById('currentBannerSlideImage').innerHTML = '';
    }
    async saveBannerSlide() {
        const form = document.getElementById('bannerSlideForm');
        if (!form) return;
        const id = document.getElementById('bannerSlideId').value;
        const formData = new FormData(form);
        if (id) {
            formData.append('id', id);
        }
        try {
            await this.fetchAPI('../api/banner_slides.php', { method: 'POST', body: formData });
            this.showMessage('Banner slide saved', 'success');
            this.closeModal('bannerSlideModal');
            await this.loadBannerSlides();
        } catch (error) {
            this.showMessage('Error saving slide: ' + error.message, 'error');
        }
    }
    async deleteBannerSlide(id) {
        if (!confirm('Are you sure you want to delete this banner slide?')) return;
        try {
            await this.fetchAPI('../api/banner_slides.php', { method: 'DELETE', body: { id } });
            this.showMessage('Banner slide deleted', 'success');
            await this.loadBannerSlides();
        } catch (error) {
            this.showMessage('Error deleting slide: ' + error.message, 'error');
        }
    }
}

// Global functions for onclick handlers
function showSection(sectionId) {
    if (dashboard) {
        dashboard.showSection(sectionId);
    }
}

function showModal(modalId) {
    if (dashboard) {
        dashboard.showModal(modalId);
    }
}

function closeModal(modalId) {
    if (dashboard) {
        dashboard.closeModal(modalId);
    }
}

function loadProductCategories() {
    if (dashboard) {
        dashboard.loadProductCategories();
    }
}

function filterProducts() {
    if (dashboard) {
        dashboard.filterProducts();
    }
}

function reloadSettings() {
    if (dashboard) {
        dashboard.populateSettingsOnShow();
    }
}

// Global test function - you can call this from console
function testSettings() {
    console.log('=== MANUAL SETTINGS TEST ===');
    if (dashboard) {
        console.log('Dashboard object exists');
        console.log('Settings:', dashboard.settings);
        dashboard.populateSettingsForm();
    } else {
        console.log('Dashboard object not found');
    }
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