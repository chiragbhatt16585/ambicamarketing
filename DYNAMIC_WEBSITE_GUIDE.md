# Dynamic Website Features Guide

## Overview

The Ambica Marketing website has been enhanced with dynamic functionality that pulls data from a MySQL database. This allows for easy content management through the admin dashboard and real-time updates to the website.

## Dynamic Features

### 1. Company Information
- **Company Name**: Displayed in header, footer, and page titles
- **Company Description**: Shown in the About section
- **Contact Information**: Phone, email, address, WhatsApp, working hours
- **Social Media Links**: Facebook, Twitter, Instagram, LinkedIn

### 2. Business Categories
- **CNC Machine Spare Parts**: Products and services since 2009
- **Automation & Road Safety**: Smart solutions since 2021
- Each category displays with custom icons and descriptions

### 3. Products
- **Featured Products**: Highlighted on homepage
- **All Products**: Complete catalog with filtering
- **Product Categories**: Organized by business type
- **Pricing**: Displayed when available
- **Images**: Product photos with fallback placeholders

### 4. Contact Management
- **Contact Form**: Collects inquiries from visitors
- **Statistics**: Shows total contacts and recent activity
- **Admin Dashboard**: Manage and respond to inquiries

## File Structure

### Dynamic Pages
- `index.php` - Dynamic homepage with database-driven content
- `test-dynamic.php` - Test page to verify functionality

### API Endpoints
- `api/website-data.php` - Provides all website data
- `api/settings.php` - Manages company settings
- `api/products.php` - Handles product data
- `api/contacts.php` - Manages contact submissions

### Database
- `database/schema.sql` - Complete database structure
- Tables: settings, business_categories, product_categories, products, contacts, admin_users

## How to Use

### 1. Setup Database
1. Import `database/schema.sql` into your MySQL database
2. Update database credentials in `api/config.php`
3. Run the website to verify connection

### 2. Access Admin Dashboard
1. Navigate to `admin/dashboard.html`
2. Login with username: `admin`, password: `admin123`
3. Manage content through the admin interface

### 3. Update Company Information
1. Go to Settings in admin dashboard
2. Update company details, contact info, social media
3. Changes appear immediately on the website

### 4. Manage Products
1. Add/edit products in admin dashboard
2. Upload product images
3. Set featured products for homepage
4. Organize by categories

### 5. View Contact Inquiries
1. Check Contacts section in admin dashboard
2. View new inquiries and respond
3. Track contact statistics

## Dynamic Content Areas

### Homepage (`index.php`)
- Company name and description
- Business category cards (CNC Machine Spare Parts & Automation & Road Safety)
- Featured products section
- Contact information
- Footer details

### Contact Section
- Dynamic contact information
- Working hours
- Social media links
- WhatsApp integration

## API Endpoints

### GET `/api/website-data.php`
Returns all website data including:
- Company settings
- Business categories
- Featured products
- Product categories
- Statistics

### GET `/api/settings.php`
Returns company settings

### POST `/api/settings.php`
Updates company settings (admin only)

### GET `/api/products.php?action=business-categories`
Returns business categories

### GET `/api/products.php?action=products&business_id={slug}`
Returns products for specific business category

### POST `/api/contacts.php`
Submits contact form data

## Testing

### Test Page
Visit `test-dynamic.php` to:
- Verify database connection
- Test API endpoints
- View sample data
- Check functionality

### API Testing
Use the test buttons on the test page to verify:
- Website data API
- Settings API
- Products API

## Fallback Behavior

The website includes fallback mechanisms:
- If database connection fails, static content is displayed
- If API calls fail, JavaScript continues with existing content
- Error handling prevents website crashes

## Customization

### Adding New Settings
1. Add new setting to database schema
2. Update admin dashboard settings form
3. Add to JavaScript update functions

### Adding New Product Categories
1. Insert into `product_categories` table
2. Update admin dashboard
3. Add to JavaScript category mapping

### Styling
- CSS classes remain the same
- Dynamic content uses existing styling
- Responsive design maintained

## Security Features

- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- Admin authentication required for sensitive operations
- CORS headers for API access
- Error handling without exposing sensitive information

## Performance

- Database connection pooling
- Efficient queries with proper indexing
- Lazy loading for images
- Caching considerations for production

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check database credentials in `api/config.php`
   - Verify MySQL server is running
   - Check database exists and tables are created

2. **API Not Working**
   - Check browser console for errors
   - Verify API endpoints are accessible
   - Check server error logs

3. **Content Not Updating**
   - Clear browser cache
   - Check admin dashboard for changes
   - Verify database has data

4. **Images Not Loading**
   - Check image paths in database
   - Verify image files exist
   - Check file permissions

### Debug Mode
Enable debug mode by setting `error_reporting(E_ALL)` in `api/config.php` for development.

## Future Enhancements

- Product image galleries
- Advanced search functionality
- Product reviews and ratings
- Newsletter subscription
- Blog/news section
- Multi-language support
- SEO optimization
- Analytics integration

## Support

For technical support or questions about the dynamic features, refer to the admin dashboard documentation or contact the development team. 