# QUICK REFERENCE GUIDE - BEMS

**Business Event Management System (BEMS)**  
**Quick Setup & Usage Guide**

---

## ⚡ 5-Minute Quick Start

### 1. Verify System is Running
```
Open: http://localhost/BusinessEventManagementSystem/verify.php
Expected: All checks pass ✓
```

### 2. Initialize Database (First Time Only)
```
Open: http://localhost/BusinessEventManagementSystem/config/init-db.php
Click: Initialize Database
Expected: All tables created successfully ✓
```

### 3. Open Application
```
Open: http://localhost/BusinessEventManagementSystem/public/index.html
```

### 4. Create Account or Login
```
Register a new account OR
Use default credentials (if available)
```

### 5. Start Using
```
Create events → Register attendees → Check-in → View reports
```

---

## 🔑 API Quick Reference

### Authentication
```bash
# Register
POST /api/auth.php?action=register
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "phone": "1234567890",
  "company": "ABC Corp"
}

# Login
POST /api/auth.php?action=login
{
  "email": "john@example.com",
  "password": "password123"
}
Response: { "token": "eyJ..." }

# Get Profile
GET /api/auth.php?action=profile
Headers: Authorization: Bearer {token}
```

### Events
```bash
# List Events
GET /api/events.php?action=list&page=1&limit=20

# Create Event
POST /api/events.php?action=create
{
  "title": "Annual Conference",
  "description": "...",
  "location": "...",
  "start_date": "2026-06-15 09:00:00",
  "max_attendees": 100
}

# Get Event
GET /api/events.php?action=detail&id=1

# Update Event
PUT /api/events.php?action=update&id=1
{
  "title": "Updated Title",
  "status": "ongoing"
}

# Delete Event
DELETE /api/events.php?action=delete&id=1

# Stats
GET /api/events.php?action=stats
```

### Attendees
```bash
# Register for Event
POST /api/attendees.php?action=register
{
  "event_id": 1
}

# Check-in
POST /api/attendees.php?action=checkin
{
  "event_id": 1
}

# List Attendees
GET /api/attendees.php?action=list&event_id=1

# Unregister
POST /api/attendees.php?action=unregister
{
  "event_id": 1
}
```

### Reports
```bash
# Event Report
GET /api/reports.php?action=event&event_id=1

# Attendee Report
GET /api/reports.php?action=attendees

# Summary
GET /api/reports.php?action=summary

# Export CSV
GET /api/reports.php?action=export&format=csv
```

---

## 📁 Directory Structure

```
BusinessEventManagementSystem/
├── api/              # API endpoints
├── config/           # Configuration & DB init
├── src/              # Core classes
├── public/           # Frontend (index.html)
├── assets/           # CSS, JavaScript
├── storage/logs/     # Application logs
├── *.md              # Documentation files
└── verify.php        # System verification
```

---

## 🔐 Default Credentials

**Note:** Use these only for initial setup. Create new admin account afterward.

```
Email: admin@example.com
Password: admin123
Role: Admin
```

---

## 📝 Configuration

**File:** `config/config.php`

```php
// Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'business_event_system');

// JWT Secret (CHANGE THIS!)
define('JWT_SECRET', 'your_unique_secret_key');

// Environment
define('APP_ENV', 'production');  // or 'development'
define('APP_DEBUG', false);       // Enable logging
```

---

## 🧪 Testing Endpoints

### 1. Test Database Connection
```
GET http://localhost/BusinessEventManagementSystem/verify.php
```
Should show: ✓ All checks passed

### 2. Test Registration
```bash
curl -X POST http://localhost/BusinessEventManagementSystem/api/auth.php?action=register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@test.com",
    "password": "test123",
    "phone": "1234567890",
    "company": "Test Co"
  }'
```

### 3. Test Login
```bash
curl -X POST http://localhost/BusinessEventManagementSystem/api/auth.php?action=login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@test.com",
    "password": "test123"
  }'
```

### 4. Test Event List
```bash
curl http://localhost/BusinessEventManagementSystem/api/events.php?action=list
```

---

## 🐛 Troubleshooting

### Issue: Database Connection Error
**Solution:**
1. Verify MySQL is running
2. Check credentials in `config/config.php`
3. Run `verify.php` to diagnose

### Issue: Tables Not Found
**Solution:**
1. Run: `config/init-db.php`
2. Or check database with `SELECT * FROM users;`

### Issue: 401 Unauthorized
**Solution:**
1. Login first to get token
2. Include token in Authorization header
3. Check token hasn't expired

### Issue: CORS Error
**Solution:**
1. Check browser console for details
2. Verify API and frontend are on same domain
3. Check CORS headers in API files

### Issue: Page Not Loading
**Solution:**
1. Clear browser cache
2. Check browser console (F12)
3. Verify correct URL
4. Check `storage/logs/` for errors

---

## 📊 Dashboard Metrics

The dashboard shows:
- **Total Events:** Count of all events
- **My Registrations:** Events user registered for
- **Ongoing Events:** Events currently active
- **Completed Events:** Events that finished

---

## 👥 User Roles

### Admin
- Full access to all features
- Can manage all users
- Can view all events
- Can generate reports

### Organizer
- Can create/edit/delete own events
- Can view event attendees
- Can manage check-ins
- Can generate event reports

### User
- Can view all events
- Can register/unregister for events
- Can check-in at events
- Can view personal registrations

---

## 🔒 Security Quick Tips

1. **Change JWT Secret:** Update `JWT_SECRET` in `config/config.php`
2. **Update Admin:** Create new admin account after setup
3. **Enable HTTPS:** Configure SSL certificate for production
4. **Backup Database:** Regular database backups
5. **Monitor Logs:** Check `storage/logs/` for issues
6. **Update PHP:** Keep PHP version current
7. **File Permissions:** Set correct permissions on files
8. **Database Password:** Use strong password for DB user

---

## 📚 Documentation Files

- **README.md** - Full project overview
- **QUICKSTART.md** - 5-minute setup
- **INSTALLATION.md** - Detailed installation
- **FEATURES.md** - Complete feature list
- **DEPLOYMENT_GUIDE.md** - Production deployment
- **COMPLETION_REPORT.md** - Completion report
- **COMPLETION_CHECKLIST.md** - Feature checklist

---

## 🚀 Production Deployment

1. Update configuration:
   ```php
   define('APP_ENV', 'production');
   define('APP_DEBUG', false);
   define('JWT_SECRET', 'YOUR_PRODUCTION_SECRET');
   ```

2. Update database credentials:
   ```php
   define('DB_USER', 'production_user');
   define('DB_PASS', 'strong_password');
   ```

3. Enable HTTPS

4. Set file permissions:
   ```bash
   chmod 755 storage/logs
   chmod 600 config/config.php
   ```

5. Run verification:
   ```
   http://yourdomain.com/BusinessEventManagementSystem/verify.php
   ```

---

## 📞 Support Resources

1. **System Verification:** Run `verify.php`
2. **Logs:** Check `storage/logs/` for errors
3. **Documentation:** Read README.md, DEPLOYMENT_GUIDE.md
4. **API Docs:** See FEATURES.md for endpoint list
5. **Database:** Check schema in `config/init-db.php`

---

## ✅ Verification Checklist

- [ ] PHP 7.4+ installed
- [ ] MySQL running
- [ ] Database `business_event_system` created
- [ ] `verify.php` passes all checks
- [ ] Database initialized via `init-db.php`
- [ ] Frontend accessible at `public/index.html`
- [ ] Can register new account
- [ ] Can login successfully
- [ ] Can create events (as organizer)
- [ ] Can register for events
- [ ] Can generate reports

---

**Version:** 1.0.0  
**Status:** ✅ Production Ready  
**Updated:** May 15, 2026
