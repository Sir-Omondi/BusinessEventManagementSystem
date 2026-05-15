# DEPLOYMENT GUIDE - Business Event Management System (BEMS)

**Status:** ✅ **PRODUCTION READY**  
**Version:** 1.0.0  
**Last Updated:** May 15, 2026

---

## Table of Contents
1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [System Requirements](#system-requirements)
3. [Installation & Setup](#installation--setup)
4. [Database Initialization](#database-initialization)
5. [Configuration](#configuration)
6. [Testing & Verification](#testing--verification)
7. [First-Time Usage](#first-time-usage)
8. [Security Best Practices](#security-best-practices)
9. [Troubleshooting](#troubleshooting)
10. [Performance Optimization](#performance-optimization)

---

## Pre-Deployment Checklist

Before deploying BEMS to production, verify the following:

### Server Environment
- ✅ PHP 7.4 or higher installed
- ✅ MySQL 5.7 or higher installed
- ✅ Apache with mod_rewrite enabled
- ✅ PHP extensions: MySQLi, JSON, PDO
- ✅ Sufficient disk space (minimum 500MB)
- ✅ XAMPP or equivalent web server running

### Application Files
- ✅ All files in correct directory structure
- ✅ File permissions set correctly (755 for directories, 644 for files)
- ✅ No sensitive files exposed (config files not in web root is preferred)

### Database
- ✅ MySQL service running
- ✅ Database user created with proper privileges
- ✅ Connection from application server to database verified

### Browser Compatibility
- ✅ Chrome/Edge (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

---

## System Requirements

### Minimum Requirements
| Component | Requirement |
|-----------|-------------|
| **OS** | Windows/Linux/macOS |
| **PHP** | 7.4 or higher |
| **MySQL** | 5.7 or higher |
| **RAM** | 2GB |
| **Disk Space** | 500MB |
| **Browser** | Modern browser (ES6 support) |

### Recommended Requirements
| Component | Recommendation |
|-----------|---|
| **PHP** | 8.0 or higher |
| **MySQL** | 8.0 or higher |
| **RAM** | 4GB or more |
| **Disk Space** | 1GB or more |
| **Connection** | Stable internet (100 Mbps) |

### Required PHP Extensions
```
✓ MySQLi (Database connectivity)
✓ JSON (API communication)
✓ OpenSSL (HTTPS support)
✓ Curl (HTTP requests)
✓ Filter (Input validation)
✓ Hash (Password hashing)
```

---

## Installation & Setup

### Step 1: Install XAMPP (If Not Already Installed)

**Windows:**
1. Download from https://www.apachefriends.org/
2. Run installer and follow wizard
3. Install to `C:\xampp` (default)
4. Start Apache and MySQL from control panel

**macOS/Linux:**
1. Download appropriate version
2. Run installer
3. Start services from application menu

### Step 2: Extract Application Files

```bash
# Navigate to htdocs
cd C:\xampp\htdocs

# Verify application exists
dir BusinessEventManagementSystem
```

### Step 3: Create MySQL Database

```sql
-- Create database
CREATE DATABASE IF NOT EXISTS business_event_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user (optional - only if not using root)
CREATE USER 'bems_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON business_event_system.* TO 'bems_user'@'localhost';
FLUSH PRIVILEGES;
```

### Step 4: Update Configuration

Edit `config/config.php`:

```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Or your database user
define('DB_PASS', '');              // Or your database password
define('DB_NAME', 'business_event_system');
define('DB_PORT', 3306);

// Change JWT secret to something unique
define('JWT_SECRET', 'YOUR_UNIQUE_SECRET_KEY_HERE');

// Update environment if needed
define('APP_ENV', 'production');    // 'production' or 'development'
```

### Step 5: Set File Permissions

```bash
# Windows (usually not needed)
# Use File Explorer: Properties → Security → Edit Permissions

# Linux/macOS
chmod 755 config
chmod 755 storage
chmod 755 storage/logs
chmod 644 config/config.php
chmod 644 storage/logs/*
```

---

## Database Initialization

### Option 1: Automatic Initialization (Recommended)

1. Open browser and navigate to:
   ```
   http://localhost/BusinessEventManagementSystem/config/init-db.php
   ```

2. Click the initialization button to create all tables and default admin user

3. You should see: ✅ All tables created successfully

### Option 2: Manual Initialization

1. Open MySQL command line or phpMyAdmin
2. Select the `business_event_system` database
3. Run the SQL statements from `config/init-db.php`

### Option 3: Run via Terminal

```bash
php config/init-db.php
```

---

## Configuration

### Environment-Based Configuration

For production, create `.env` file in root directory:

```env
APP_ENV=production
APP_DEBUG=false

DB_HOST=localhost
DB_USER=bems_user
DB_PASS=secure_password
DB_NAME=business_event_system

JWT_SECRET=your_production_secret_key_here
SESSION_TIMEOUT=3600
```

### Update config.php to Use .env

```php
// At top of config/config.php
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    define('APP_ENV', $env['APP_ENV'] ?? 'production');
    define('DB_HOST', $env['DB_HOST'] ?? 'localhost');
    // ... etc
}
```

---

## Testing & Verification

### 1. Run System Verification

Navigate to: `http://localhost/BusinessEventManagementSystem/verify.php`

Expected output:
```
✓ PHP Version OK
✓ MySQLi Extension
✓ JSON Extension
✓ Database Connection OK
✓ Table: users
✓ Table: events
✓ Table: attendees
✓ Table: audit_logs
✓ All required files found
```

### 2. Test API Endpoints

#### Health Check (No Auth Required)
```bash
curl http://localhost/BusinessEventManagementSystem/api/auth.php?action=list
```

#### User Registration
```bash
curl -X POST http://localhost/BusinessEventManagementSystem/api/auth.php?action=register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "phone": "1234567890",
    "company": "Test Company"
  }'
```

#### User Login
```bash
curl -X POST http://localhost/BusinessEventManagementSystem/api/auth.php?action=login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

#### List Events
```bash
curl http://localhost/BusinessEventManagementSystem/api/events.php?action=list
```

### 3. Test Frontend Application

1. Open browser to: `http://localhost/BusinessEventManagementSystem/public/index.html`
2. Verify:
   - ✓ Page loads without errors
   - ✓ Navigation bar displays
   - ✓ Login/Register forms visible
   - ✓ Bootstrap styling applied
   - ✓ Font Awesome icons visible

### 4. Test User Workflows

#### Registration Flow
1. Click "Login"
2. Select "Register" tab
3. Fill in form with test data
4. Click Register
5. Verify redirect to dashboard

#### Login Flow
1. Enter credentials
2. Click Login
3. Verify dashboard displays
4. Check user profile in navbar

#### Event Creation
1. Login as organizer/admin
2. Click "Create Event"
3. Fill in event details
4. Click Create
5. Verify event appears in list

#### Event Registration
1. Browse events as regular user
2. Click event detail
3. Click "Register"
4. Verify registration confirmed

#### Check-In/Out
1. As organizer, view event attendees
2. Click check-in button for attendee
3. Verify check-in time recorded
4. Test check-out similarly

---

## First-Time Usage

### Default Admin Account

**Note:** Create a new admin after first login

```
Email: admin@bems.local
Password: Generated during init-db.php
```

### Initial Setup Steps

1. **Create Admin Account** (if not auto-created)
   - Login to application
   - Navigate to Settings
   - Create admin user

2. **Create Test Events**
   - Login as organizer
   - Create sample events
   - Test event management

3. **Register Test Attendees**
   - Create regular user accounts
   - Register for events
   - Test check-in functionality

4. **Generate Reports**
   - Navigate to Reports
   - Generate event statistics
   - Export sample data to CSV

5. **Configure System Settings**
   - Update event capacity
   - Set check-in time windows
   - Configure notifications

---

## Security Best Practices

### 1. Change Default Credentials

✅ **Always change:**
- JWT_SECRET in config.php
- Database password
- Admin account password

### 2. Implement HTTPS

In production:
```apache
# .htaccess
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

### 3. Set Proper File Permissions

```bash
# Protect sensitive files
chmod 600 config/config.php
chmod 600 .env

# Protect directories
chmod 750 storage/logs
chmod 750 config
```

### 4. Enable CORS for Trusted Domains Only

In `src/Core.php`, update CORS headers:
```php
header('Access-Control-Allow-Origin: https://yourdomain.com');
// Not '*' in production
```

### 5. Update Database Credentials

Replace default credentials in `config/config.php`:
```php
define('DB_USER', 'unique_username');
define('DB_PASS', 'complex_password_here');
```

### 6. Enable Logging

Set `APP_DEBUG` to `false` in production:
```php
define('APP_ENV', 'production');
define('APP_DEBUG', false);
```

### 7. Regular Backups

```bash
# Weekly database backup
mysqldump -u root business_event_system > backup_$(date +%Y%m%d).sql

# Backup application files
tar -czf bems_backup_$(date +%Y%m%d).tar.gz /path/to/BusinessEventManagementSystem
```

### 8. Input Validation

All user inputs are validated using the Validator class:
- Email validation
- Phone number validation
- Date/time validation
- SQL injection prevention
- XSS prevention

---

## Troubleshooting

### Issue: "Database Connection Error"

**Solution:**
1. Verify MySQL is running
2. Check credentials in `config/config.php`
3. Ensure database exists: `CREATE DATABASE business_event_system`
4. Test connection: `php verify.php`

### Issue: "Table Not Found"

**Solution:**
1. Run: `http://localhost/BusinessEventManagementSystem/config/init-db.php`
2. Or manually import SQL from `config/init-db.php`

### Issue: "401 Unauthorized" API Response

**Solution:**
1. Verify JWT token is being sent in Authorization header
2. Check token expiration in `config/config.php`
3. Login again to get new token

### Issue: "CORS Error"

**Solution:**
1. Update CORS headers in API files
2. Check browser console for detailed error
3. Verify API and frontend are on same domain

### Issue: "File Upload Failed"

**Solution:**
1. Check storage permissions: `chmod 755 storage`
2. Verify file size under MAX_UPLOAD_SIZE in config
3. Check file type is in ALLOWED_EXTENSIONS

### Issue: "Pagination Not Working"

**Solution:**
1. Verify ITEMS_PER_PAGE is set in config
2. Check page parameter is being passed
3. Ensure database has sufficient records

### Issue: "Login Always Redirects"

**Solution:**
1. Clear browser cookies
2. Verify JWT_SECRET is consistent
3. Check server time synchronization
4. Look for errors in logs: `storage/logs/`

---

## Performance Optimization

### Database Optimization

1. **Add Indexes** (already included):
   - `email` on users table
   - `role` on users table
   - `status` on events table
   - `start_date` on events table

2. **Archive Old Data**:
   ```sql
   -- Move completed events to archive table
   INSERT INTO events_archive SELECT * FROM events 
   WHERE status = 'completed' AND end_date < DATE_SUB(NOW(), INTERVAL 6 MONTH);
   ```

### Frontend Optimization

1. **Minify CSS/JS** (in production):
   - Use asset minification tools
   - Enable gzip compression in Apache

2. **Caching**:
   ```apache
   # Add to .htaccess
   <FilesMatch "\.(jpg|jpeg|png|gif|ico|css|js)$">
       Header set Cache-Control "max-age=31536000, public"
   </FilesMatch>
   ```

### API Optimization

1. **Implement Pagination** (already done)
   - Default: 20 items per page
   - Configurable via ITEMS_PER_PAGE

2. **Use Prepared Statements** (already implemented)
   - Prevents SQL injection
   - Improves performance

3. **Enable Query Caching**:
   ```sql
   -- In my.cnf
   query_cache_size = 256M
   query_cache_type = 1
   ```

---

## Production Deployment Checklist

- ✅ All system requirements verified
- ✅ Database created and initialized
- ✅ Configuration updated (JWT secret, DB credentials)
- ✅ File permissions set correctly
- ✅ HTTPS configured
- ✅ Verification script passes all checks
- ✅ API endpoints tested
- ✅ Frontend application tested
- ✅ User workflows verified
- ✅ Logging enabled
- ✅ Backups scheduled
- ✅ Security hardened
- ✅ Admin account created
- ✅ Test data loaded
- ✅ Performance baseline established

---

## Support & Maintenance

### Regular Maintenance Tasks

**Daily:**
- Monitor error logs
- Check database integrity

**Weekly:**
- Backup database
- Review security logs
- Test critical workflows

**Monthly:**
- Analyze performance metrics
- Update dependencies
- Archive old audit logs

**Quarterly:**
- Security audit
- Performance optimization
- Feature enhancement review

### Log Files Location

```
storage/logs/
├── error.log        # Application errors
├── database.log     # Database queries (debug mode)
├── audit.log        # User actions
└── access.log       # HTTP access
```

### Monitoring Commands

```bash
# Check error logs
tail -f storage/logs/error.log

# Monitor database
mysql -u root -e "SHOW PROCESSLIST;"

# Check disk usage
du -sh /path/to/BusinessEventManagementSystem
```

---

## Contact & Support

For issues or questions:
1. Check Troubleshooting section above
2. Review logs in `storage/logs/`
3. Run `verify.php` for system diagnostics
4. Refer to README.md for additional information

---

**Deployment Guide Version:** 1.0.0  
**Last Updated:** May 15, 2026  
**Status:** ✅ Production Ready
