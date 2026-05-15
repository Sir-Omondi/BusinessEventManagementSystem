# Business Event Management System (BEMS)

## Overview

**Business Event Management System (BEMS)** is a professional full-stack web application designed for managing business events with comprehensive features including event creation, attendee registration, check-in/out management, and advanced reporting.

**Developed For:** TVET Fair Competition 2026
**Theme:** Transforming Tomorrow Today: TVET Powering Bottom-Up Transformation Through Skilling, Innovation, Robotics and Research
**Sub-Theme:** Innovation, Digitization and Commercialization
**Track:** 09 - IT Software Solutions for Business Infrastructure

---

## Features

### ✨ Core Features

1. **User Management**
   - User registration and authentication
   - Role-based access control (Admin, Organizer, User)
   - JWT-based token authentication
   - Password hashing with bcrypt

2. **Event Management**
   - Create, read, update, delete (CRUD) events
   - Event status tracking (Scheduled, Ongoing, Completed, Cancelled)
   - Event capacity management
   - Event search and filtering
   - Full-text search capabilities

3. **Attendee Management**
   - Event registration and cancellation
   - Check-in/Check-out functionality
   - Attendee tracking and status management
   - Capacity limits enforcement

4. **Reporting & Analytics**
   - Event statistics and summaries
   - Attendee reports with details
   - CSV export functionality
   - Dashboard with key metrics

5. **Database Design**
   - Normalized MySQL database
   - Referential integrity with foreign keys
   - Indexed queries for performance
   - Full-text search indexes

6. **Security**
   - Input validation and sanitization
   - SQL injection prevention (prepared statements)
   - CORS support for API requests
   - Environment-based configuration

---

## Technology Stack

- **Backend:** PHP 7.4+
- **Database:** MySQL 8.0
- **Frontend:** HTML5, CSS3, JavaScript (ES6+)
- **Framework:** Bootstrap 5
- **Icons:** Font Awesome 6.4
- **Server:** Apache (XAMPP)

---

## Installation & Setup

### Prerequisites

- XAMPP installed and running
- MySQL service running
- Modern web browser

### Step 1: Copy Project Files

The project is already located at: `C:\xampp\htdocs\BusinessEventManagementSystem\`

### Step 2: Initialize Database

1. Open your browser and navigate to:
   ```
   http://localhost/BusinessEventManagementSystem/config/init-db.php
   ```

2. This will create all necessary database tables and a default admin user.

### Step 3: Access the Application

Open your browser and navigate to:
```
http://localhost/BusinessEventManagementSystem/public/index.html
```

### Step 4: Login with Demo Credentials

**Default Admin Account:**
- Email: `admin@bems.local`
- Password: `admin@123`

---

## Project Structure

```
BusinessEventManagementSystem/
├── api/                          # Backend API endpoints
│   ├── auth.php                 # Authentication endpoints
│   ├── events.php               # Events management endpoints
│   ├── attendees.php            # Attendees management endpoints
│   └── reports.php              # Reports generation endpoints
├── config/
│   ├── config.php               # Application configuration
│   └── init-db.php              # Database initialization script
├── src/
│   ├── Core.php                 # Core classes (Database, Logger, Auth, Validator, Response)
│   └── Models.php               # Data models (User, Event, Attendee, Report)
├── public/
│   └── index.html               # Main application entry point
├── assets/
│   ├── css/
│   │   └── style.css            # Application stylesheet
│   ├── js/
│   │   ├── api.js               # API communication module
│   │   └── app.js               # Main application logic
│   └── images/                  # Image assets
├── views/                        # Dynamic view templates (if needed)
├── storage/
│   └── logs/                    # Application logs
└── README.md                     # This file
```

---

## API Documentation

### Authentication Endpoints

#### Register User
```
POST /api/auth.php?action=register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "phone": "0712345678",
  "company": "Tech Corp"
}
```

#### Login
```
POST /api/auth.php?action=login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

#### Get Profile
```
GET /api/auth.php?action=profile
Authorization: Bearer {token}
```

### Events Endpoints

#### List Events
```
GET /api/events.php?action=list&page=1&limit=20&status=scheduled&search=term
Authorization: Bearer {token}
```

#### Get Event Detail
```
GET /api/events.php?action=detail&id={event_id}
Authorization: Bearer {token}
```

#### Create Event
```
POST /api/events.php?action=create
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Annual Conference",
  "description": "Company annual conference",
  "location": "Convention Center",
  "start_date": "2026-06-15 09:00:00",
  "end_date": "2026-06-15 17:00:00",
  "max_attendees": 200
}
```

### Attendees Endpoints

#### Register for Event
```
POST /api/attendees.php?action=register&event_id={event_id}
Authorization: Bearer {token}
```

#### Unregister from Event
```
DELETE /api/attendees.php?action=unregister&event_id={event_id}
Authorization: Bearer {token}
```

#### Check-in
```
POST /api/attendees.php?action=checkin&attendee_id={attendee_id}
Authorization: Bearer {token}
```

### Reports Endpoints

#### Event Report
```
GET /api/reports.php?action=event_report&event_id={event_id}
Authorization: Bearer {token}
```

#### Export Attendees as CSV
```
GET /api/reports.php?action=export&event_id={event_id}
Authorization: Bearer {token}
```

---

## Database Schema

### Users Table
```sql
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(20),
  company VARCHAR(255),
  role ENUM('admin', 'organizer', 'user') DEFAULT 'user',
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

### Events Table
```sql
CREATE TABLE events (
  id INT PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  description LONGTEXT,
  location VARCHAR(255),
  start_date DATETIME NOT NULL,
  end_date DATETIME,
  max_attendees INT DEFAULT 100,
  organizer_id INT NOT NULL,
  status ENUM('scheduled', 'ongoing', 'completed', 'cancelled'),
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (organizer_id) REFERENCES users(id)
);
```

### Attendees Table
```sql
CREATE TABLE attendees (
  id INT PRIMARY KEY AUTO_INCREMENT,
  event_id INT NOT NULL,
  user_id INT NOT NULL,
  registration_date TIMESTAMP,
  check_in_time DATETIME,
  check_out_time DATETIME,
  status ENUM('registered', 'checked_in', 'checked_out', 'cancelled'),
  FOREIGN KEY (event_id) REFERENCES events(id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  UNIQUE KEY (event_id, user_id)
);
```

---

## Security Measures

1. **Authentication:** JWT tokens for stateless authentication
2. **Password Security:** Bcrypt hashing algorithm
3. **SQL Injection Prevention:** Prepared statements with parameterized queries
4. **Input Validation:** Server-side validation for all inputs
5. **CORS:** Properly configured CORS headers
6. **Environment Configuration:** Sensitive data in config files

---

## Code Quality

### Best Practices Implemented

1. **MVC Architecture:** Separation of concerns with Models, Views, and Controllers
2. **Singleton Pattern:** Database connection management
3. **DRY Principle:** Code reusability and reduced duplication
4. **Error Handling:** Comprehensive error handling and logging
5. **Documentation:** Inline comments and comprehensive README
6. **Type Safety:** Input validation and type checking
7. **RESTful API:** Standard HTTP methods and status codes
8. **Pagination:** Efficient data retrieval with pagination support
9. **Indexing:** Optimized database queries with proper indexes

---

## Configuration

Edit `config/config.php` to configure:

```php
// Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'business_event_system');

// JWT
define('JWT_SECRET', 'your_secret_key');
define('JWT_EXPIRATION', 86400);

// Application
define('APP_ENV', 'production'); // or 'development'
define('BASE_URL', 'http://localhost/BusinessEventManagementSystem/');
```

---

## Troubleshooting

### Database Connection Error
- Ensure MySQL is running
- Check database credentials in `config/config.php`
- Run the initialization script: `config/init-db.php`

### API Requests Failing
- Check browser console for errors
- Verify JWT token is valid
- Ensure CORS headers are properly configured

### CSS/JS Not Loading
- Clear browser cache (Ctrl+F5)
- Check file paths in HTML
- Verify all files are in correct directories

---

## Testing

### User Flow Test

1. Register a new user account
2. Login with credentials
3. Browse available events
4. Register for an event
5. View event details
6. Generate reports (if organizer)

### Admin Test

1. Login with admin account
2. Create new event
3. Manage event details
4. View attendee reports
5. Export attendee data

---

## Future Enhancements

- Email notifications for event updates
- Payment integration for paid events
- Advanced analytics dashboard
- Mobile application
- Calendar integration
- Event reminders
- Social media sharing

---

## Support & Maintenance

For issues or questions:
1. Check the README documentation
2. Review error logs in `storage/logs/`
3. Verify database connection
4. Check browser console for frontend errors

---

## License

This project is created for TVET Fair Competition 2026.

---

## Author

Senior Developer Team | TVET Competition 2026

---

**Version:** 1.0.0  
**Last Updated:** May 15, 2026  
**Status:** Production Ready
