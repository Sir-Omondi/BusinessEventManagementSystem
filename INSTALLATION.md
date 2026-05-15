# COMPLETE INSTALLATION GUIDE

## Business Event Management System (BEMS) - Full Setup Instructions

---

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Installation Steps](#installation-steps)
3. [Database Setup](#database-setup)
4. [Verification](#verification)
5. [First Use](#first-use)
6. [Troubleshooting](#troubleshooting)

---

## Prerequisites

### System Requirements
- **Operating System:** Windows, macOS, or Linux
- **XAMPP:** 7.4.0 or higher
- **PHP:** 7.4 or higher
- **MySQL:** 5.7 or higher
- **Browser:** Modern browser (Chrome, Firefox, Safari, Edge)

### Required PHP Extensions
- MySQLi (Database connection)
- JSON (API communication)

### Disk Space
- Minimum: 500 MB
- Recommended: 1 GB

---

## Installation Steps

### Step 1: Install XAMPP

**Windows:**
1. Download XAMPP from https://www.apachefriends.org/
2. Run the installer (.exe file)
3. Follow the installation wizard
4. Default installation folder: `C:\xampp`
5. Install Apache and MySQL components

**macOS:**
1. Download XAMPP for macOS
2. Run the installer (.dmg file)
3. Follow the installation wizard
4. Default location: `/Applications/XAMPP`

**Linux:**
```bash
# Download and extract XAMPP
wget https://sourceforge.net/projects/xampp/files/XAMPP%20Linux/7.4.0/xampp-linux-x64-7.4.0-installer.run/download
chmod +x xampp-linux-x64-7.4.0-installer.run
sudo ./xampp-linux-x64-7.4.0-installer.run
```

### Step 2: Start XAMPP Services

**Windows:**
1. Open XAMPP Control Panel
2. Click "Start" next to Apache
3. Click "Start" next to MySQL
4. Both should show green status

**macOS/Linux:**
```bash
# Start services
sudo /Applications/XAMPP/xamppfiles/bin/mysql.server start
sudo /Applications/XAMPP/xamppfiles/bin/apache2ctl start
```

### Step 3: Verify XAMPP Installation

1. Open browser and go to: `http://localhost`
2. You should see the XAMPP dashboard
3. Click on "phpinfo()" to verify PHP is running
4. Look for PHP version and installed extensions

---

## Database Setup

### Step 1: Verify MySQL is Running

**Windows:**
- XAMPP Control Panel should show MySQL with green status

**macOS/Linux:**
```bash
mysql -u root
# Should connect without asking for password
```

### Step 2: Create Database

**Option A: Using phpMyAdmin (Recommended for beginners)**

1. Open browser: `http://localhost/phpmyadmin`
2. Login with default credentials:
   - Username: `root`
   - Password: (leave blank)
3. Click on "New" button
4. Create new database: `business_event_system`
5. Click "Create"

**Option B: Using Command Line**

```bash
# Connect to MySQL
mysql -u root -p

# If no password, press Enter when prompted
# Then run these commands:

CREATE DATABASE business_event_system;
SHOW DATABASES;
EXIT;
```

### Step 3: Initialize BEMS Database

1. Ensure XAMPP is running
2. Navigate to: `http://localhost/BusinessEventManagementSystem/config/init-db.php`
3. You should see:
   ```
   ✓ Table created/verified successfully
   ✓ Database initialization completed successfully!
   ```

### Step 4: Verify Database Setup

1. Go to phpMyAdmin: `http://localhost/phpmyadmin`
2. Select database: `business_event_system`
3. Verify these tables exist:
   - `users`
   - `events`
   - `attendees`
   - `audit_logs`

---

## Configuration

### Default Configuration

The application comes with default configuration. To customize:

1. Open `config/config.php`
2. Modify these settings as needed:

```php
// Database (usually no changes needed)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Add password if MySQL has one
define('DB_NAME', 'business_event_system');

// Application
define('APP_ENV', 'production'); // 'development' for debugging
define('APP_DEBUG', false); // Set to true for debugging

// Security (IMPORTANT for production!)
define('JWT_SECRET', 'your_super_secret_jwt_key_change_this_in_production_2026');
```

### For Production Deployment

1. Change `JWT_SECRET` to a strong random value:
   ```php
   // Use a random string like this:
   define('JWT_SECRET', 'aB#xY$mK9@lP&qW2$vR4!tQ7*uL1^aS3%dF5&gH8(jK0)zX2~cV4<bN6>mL8?pQ');
   ```

2. Set `APP_ENV` to `'production'`

3. Set `APP_DEBUG` to `false`

4. Use a strong MySQL password

---

## Verification

### Quick Verification Script

1. Open browser: `http://localhost/BusinessEventManagementSystem/verify.php`
2. This will check:
   - PHP version
   - Required extensions
   - Database connection
   - File structure
   - Directory permissions

### Manual Verification

**Check 1: Application Access**
- URL: `http://localhost/BusinessEventManagementSystem/public/index.html`
- Should load the login page without errors

**Check 2: Database Connection**
- URL: `http://localhost/BusinessEventManagementSystem/config/init-db.php`
- Should show success messages

**Check 3: API Endpoints**
- URL: `http://localhost/BusinessEventManagementSystem/api/auth.php?action=login`
- Should return JSON response (error about missing credentials is expected)

---

## First Use

### Step 1: Access Application

1. Open browser: `http://localhost/BusinessEventManagementSystem/public/index.html`
2. You should see the login page

### Step 2: Login with Default Admin Account

```
Email: admin@bems.local
Password: admin@123
```

### Step 3: Explore Dashboard

1. You should see:
   - Dashboard with statistics
   - Upcoming events section
   - Quick action buttons

### Step 4: Create Your First Event

1. Click "Create Event" button
2. Fill in event details:
   - Title: "My First Event"
   - Location: "Convention Center"
   - Start Date: Tomorrow's date
   - Max Attendees: 100
3. Click "Create Event"

### Step 5: Register for Event

1. Click "Browse Events"
2. Click on your newly created event
3. Click "Register" button
4. Confirm registration

---

## Troubleshooting

### Issue: Cannot Connect to Database

**Solution:**
1. Verify MySQL is running in XAMPP
2. Check database credentials in `config/config.php`
3. Create database `business_event_system` if it doesn't exist
4. Run `config/init-db.php` to create tables

### Issue: "mysqli not loaded" Error

**Solution:**
1. Open `php.ini` file (usually in `C:\xampp\php\php.ini`)
2. Find line: `;extension=mysqli`
3. Remove the semicolon: `extension=mysqli`
4. Restart Apache in XAMPP

### Issue: "Call to undefined function json_encode"

**Solution:**
1. Open `php.ini`
2. Find and uncomment: `extension=json`
3. Restart Apache

### Issue: Application Loads but No CSS/JS

**Solution:**
1. Clear browser cache: `Ctrl+F5` or `Cmd+Shift+R`
2. Check browser console (F12) for errors
3. Verify file paths are correct
4. Check .htaccess file if using URL rewrite

### Issue: Database Tables Not Created

**Solution:**
1. Go to: `http://localhost/BusinessEventManagementSystem/config/init-db.php`
2. Check for error messages
3. Verify MySQL user has CREATE TABLE permissions
4. Try running commands in phpMyAdmin directly

### Issue: Cannot Login

**Solution:**
1. Check default credentials: `admin@bems.local` / `admin@123`
2. Verify database has users table
3. Check browser console (F12) for API errors
4. Verify API endpoints are accessible

### Issue: API Requests Return 404

**Solution:**
1. Verify .htaccess file is in root directory
2. Check mod_rewrite is enabled in Apache
3. Restart Apache service
4. Use direct URLs to API files (e.g., `/api/auth.php`)

---

## Security Checklist for Production

- [ ] Change `JWT_SECRET` in `config/config.php`
- [ ] Set `APP_ENV` to `'production'`
- [ ] Set `APP_DEBUG` to `false`
- [ ] Use strong MySQL password
- [ ] Set up HTTPS (SSL certificate)
- [ ] Configure firewall rules
- [ ] Set up regular backups
- [ ] Create admin user with strong password
- [ ] Remove demo accounts
- [ ] Enable error logging
- [ ] Monitor logs regularly

---

## Performance Optimization

1. **Enable Caching:**
   - Set appropriate cache headers in .htaccess

2. **Database Optimization:**
   - Ensure indexes are created (automatic during init)
   - Regular database maintenance

3. **Frontend Optimization:**
   - Browser caching enabled
   - CSS and JS are already optimized

4. **Server Configuration:**
   - Increase PHP memory limit if needed
   - Optimize MySQL settings

---

## Getting Help

1. **Check Documentation:**
   - README.md - Comprehensive documentation
   - QUICKSTART.md - Quick reference guide
   - This installation guide

2. **Check Logs:**
   - Application logs: `storage/logs/app.log`
   - PHP errors: Check XAMPP error logs
   - MySQL logs: Check MySQL error logs

3. **Verify Installation:**
   - Run verification script: `verify.php`
   - Check all requirements are met
   - Test each component

---

## Next Steps

After successful installation:

1. Review the [README.md](README.md) for detailed documentation
2. Check [QUICKSTART.md](QUICKSTART.md) for quick reference
3. Explore the application features
4. Create test events and register attendees
5. Generate reports
6. Customize for your needs

---

**Version:** 1.0.0 | **Last Updated:** May 15, 2026

For detailed API documentation, see: [API_DOCUMENTATION.md](api/documentation.md)
