# QUICK START GUIDE

## Getting Started in 5 Minutes

### 1. Start XAMPP
- Open XAMPP Control Panel
- Click **Start** on Apache and MySQL modules

### 2. Initialize Database
- Open browser: `http://localhost/BusinessEventManagementSystem/config/init-db.php`
- You should see: ✓ Database initialization completed successfully!

### 3. Access Application
- Open browser: `http://localhost/BusinessEventManagementSystem/public/index.html`

### 4. Login (First Time)
- Email: `admin@bems.local`
- Password: `admin@123`

---

## First Time Setup Checklist

- [ ] XAMPP running (Apache + MySQL)
- [ ] Database initialized
- [ ] Application loads without errors
- [ ] Can login with demo credentials
- [ ] Dashboard shows statistics

---

## Common Tasks

### Create an Event
1. Login as Admin or Organizer
2. Click "Create Event" button
3. Fill in event details
4. Click "Create Event"

### Register for Event
1. Browse Events
2. Click on event card
3. Click "Register" button
4. Confirm registration

### View Attendees
1. Login as Event Organizer
2. Open your event
3. Click "View Attendees"
4. Download CSV report if needed

### Check-in Attendees
1. Open event attendees list
2. Click check-in for each attendee
3. Attendee status updates to "checked_in"

---

## Role Types

### Admin
- Access all features
- Manage users
- View all events and attendees
- Generate reports

### Organizer
- Create and manage own events
- View attendee lists
- Check-in/out attendees
- Generate reports for own events

### User
- Register for events
- View event details
- Attend events
- Check-in/out

---

## API Testing (curl commands)

### Register
```bash
curl -X POST http://localhost/BusinessEventManagementSystem/api/auth.php?action=register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"pass123","phone":"0712345678","company":"Test Corp"}'
```

### Login
```bash
curl -X POST http://localhost/BusinessEventManagementSystem/api/auth.php?action=login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@bems.local","password":"admin@123"}'
```

### List Events
```bash
curl -X GET "http://localhost/BusinessEventManagementSystem/api/events.php?action=list&page=1&limit=20" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## File Locations

| Component | Location |
|-----------|----------|
| Application Entry | `/public/index.html` |
| API Endpoints | `/api/*.php` |
| Database Config | `/config/config.php` |
| Application Logs | `/storage/logs/app.log` |
| CSS Stylesheet | `/assets/css/style.css` |
| JavaScript Files | `/assets/js/*.js` |
| Database Schema | `/config/init-db.php` |

---

## Debugging Tips

1. **Enable Debug Mode**
   - Edit `config/config.php`
   - Set `APP_ENV` to `'development'`

2. **Check Logs**
   - Open `/storage/logs/app.log`

3. **Browser Console**
   - Press F12 in browser
   - Check Console tab for JavaScript errors

4. **Network Tab**
   - Check Network tab to see API responses

---

## Default Accounts

After initialization, these accounts are available:

| Email | Password | Role |
|-------|----------|------|
| admin@bems.local | admin@123 | Admin |

You can create additional accounts by registering new users.

---

## Need Help?

1. Check the main README.md for detailed documentation
2. Review error messages in browser console
3. Check application logs at `/storage/logs/app.log`
4. Verify database connection in config files
5. Ensure all services (Apache, MySQL) are running

---

**Version:** 1.0.0 | **Updated:** May 15, 2026
