# Dynamic Dashboard Setup Guide

## Overview
The admin dashboard is now fully dynamic and connects to a MySQL database to display real-time data for:
- Products and categories
- Contact messages
- Website settings
- Business statistics

## Database Setup

### 1. Create Database
1. Open your MySQL client (phpMyAdmin, MySQL Workbench, or command line)
2. Create a new database named `ambica_marketing`
3. Import the database schema from `database/schema.sql`

### 2. Database Configuration
Update the database connection settings in `api/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ambica_marketing');
define('DB_USER', 'root');  // Your MySQL username
define('DB_PASS', '');      // Your MySQL password
```

## Features

### Dashboard Overview
- **Real-time Statistics**: Shows total products, categories, and contacts
- **Recent Contacts**: Displays the latest 5 contact form submissions
- **Quick Actions**: Easy access to add new items

### Business Categories Management
- Add, edit, and delete business categories
- Set display order and active status
- Configure FontAwesome icons
- Automatic slug generation

### Product Categories Management
- Create subcategories within business categories
- Hierarchical organization
- Bulk operations support

### Products Management
- Full CRUD operations for products
- Image upload functionality
- Price and featured product settings
- Category filtering and search
- Bulk status updates

### Contact Management
- View all contact form submissions
- Update contact status (new/read/replied)
- Detailed contact information
- Export capabilities

### Settings Management
- Company information
- Contact details
- Social media links
- Working hours
- WhatsApp integration

## API Endpoints

### Products API (`api/products.php`)
- `GET ?action=stats` - Get dashboard statistics
- `GET ?action=business-categories` - List business categories
- `GET ?action=product-categories` - List product categories
- `GET ?action=products` - List products
- `POST ?action=business-category` - Create business category
- `PUT ?action=business-category&id=X` - Update business category
- `DELETE ?action=business-category&id=X` - Delete business category

### Contacts API (`api/contacts.php`)
- `GET` - List all contacts
- `GET ?id=X` - Get specific contact
- `POST` - Create new contact (from website form)
- `PUT ?id=X` - Update contact status
- `DELETE ?id=X` - Delete contact

### Settings API (`api/settings.php`)
- `GET` - Get all settings
- `POST` - Update settings

## Authentication

### Demo Login
- **Username**: `admin`
- **Password**: `admin123`
- Uses localStorage for session management
- Token-based API authentication

### Security Features
- Input sanitization
- SQL injection prevention
- File upload validation
- CORS headers
- Error handling

## File Structure

```
admin/
├── dashboard.html      # Main dashboard interface
├── admin.css          # Dashboard styles
├── admin.js           # Dashboard functionality
└── login.html         # Admin login page

api/
├── config.php         # Database and utility functions
├── products.php       # Products and categories API
├── contacts.php       # Contacts API
└── settings.php       # Settings API

database/
└── schema.sql         # Database structure and sample data
```

## Testing

### 1. Database Connection
Visit `test-dashboard.php` to verify:
- Database connection
- Table structure
- Sample data
- API endpoints

### 2. Admin Login
1. Go to `admin/login.html`
2. Use credentials: admin/admin123
3. Verify redirect to dashboard

### 3. Dashboard Functionality
1. Check overview statistics
2. Test adding a business category
3. Test adding a product category
4. Test adding a product with image
5. Test contact management
6. Test settings update

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify database credentials in `api/config.php`
   - Ensure MySQL service is running
   - Check database exists

2. **Permission Denied**
   - Ensure web server has read/write permissions
   - Check file upload directory permissions

3. **API Errors**
   - Check browser console for JavaScript errors
   - Verify API endpoints are accessible
   - Check authentication token

4. **Image Upload Issues**
   - Verify upload directory exists
   - Check file size limits
   - Ensure proper file permissions

### Debug Mode
Enable error reporting in `api/config.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Performance Optimization

### Database Indexes
The schema includes optimized indexes for:
- Product filtering
- Category lookups
- Contact queries
- Search operations

### Caching
Consider implementing:
- Redis for session storage
- Memcached for query caching
- CDN for image delivery

## Security Recommendations

1. **Change Default Credentials**
   - Update admin username/password
   - Use strong password hashing
   - Implement rate limiting

2. **File Upload Security**
   - Validate file types
   - Scan for malware
   - Use secure file names

3. **API Security**
   - Implement proper authentication
   - Add request rate limiting
   - Use HTTPS in production

## Production Deployment

1. **Environment Setup**
   - Use production database
   - Configure proper file permissions
   - Set up SSL certificate

2. **Backup Strategy**
   - Regular database backups
   - File system backups
   - Version control

3. **Monitoring**
   - Error logging
   - Performance monitoring
   - Security auditing

## Support

For technical support or questions:
- Check the troubleshooting section
- Review browser console errors
- Verify database connectivity
- Test API endpoints individually 