# TCCMS Login System Documentation

## Overview
The Tortoise Conservation Center Management System (TCCMS) now includes a comprehensive role-based authentication system that automatically creates login credentials for all staff members and redirects them to appropriate dashboards based on their roles.

## 🔒 **Security Features (UPDATED)**

### ✅ **Strict Authentication**
- **Password validation is enforced** - Wrong passwords will NOT allow access
- **Session validation on every page** - Users must be logged in for each visit
- **Role-based access control** - Only authorized roles can access specific pages
- **Automatic session timeout** - Sessions expire after 1 hour of inactivity

### ✅ **Session Security**
- **Complete session destruction** on logout
- **Cookie cleanup** to prevent session hijacking
- **Automatic redirect** to login for unauthorized access
- **No persistent sessions** without proper authentication

## Features

### 🔐 **Automatic Credential Generation**
- System automatically creates login credentials table on first login
- Default passwords are set for all existing staff members
- Secure session management with automatic logout

### 🎯 **Role-Based Access Control**
- Each staff member is redirected to their role-specific dashboard
- Session validation prevents unauthorized access
- Clean logout functionality

### 🚀 **Automatic Dashboard Routing**
- Manager → homepage.php
- Tortoise Caretaker → caretaker_dashboard.php
- Veterinarian → vetdashboard.php
- Breeding Specialist → BSDashboard.php
- Inventory Manager → inventory_dashboard.php
- Environment Monitor → Environment_Monitor.php

## Staff Member Credentials

| Staff ID | Name | Email | Password | Role | Dashboard |
|----------|------|-------|----------|------|-----------|
| SM001 | Rahim Khan | rahimk@tcc.org | manager123 | Manager | homepage.php |
| SM002 | Anika Sultana | anika@tcc.org | caretaker123 | Tortoise Caretaker | caretaker_dashboard.php |
| SM003 | Jamil Hossain | jamil@tcc.org | breeding123 | Breeding Specialist | BSDashboard.php |
| SM004 | Farhana Rahman | farhana@tcc.org | vet123 | Veterinarian | vetdashboard.php |
| SM005 | Tanvir Alam | tanvir@tcc.org | inventory123 | Inventory Manager | inventory_dashboard.php |
| SM006 | Mehedi Hasan | mehedi@tcc.org | environment123 | Environment Monitor | Environment_Monitor.php |

## Database Structure

### Login Credentials Table (`tbllogincredentials`)
```sql
CREATE TABLE tbllogincredentials (
    cstaffid varchar(8) NOT NULL,
    cemail varchar(50) NOT NULL,
    cpassword varchar(255) NOT NULL,
    crole varchar(40) NOT NULL,
    cstatus varchar(20) DEFAULT 'Active',
    dcreated_date timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (cstaffid),
    UNIQUE KEY (cemail)
);
```

### Session Variables
- `$_SESSION['staff_id']` - Staff member ID
- `$_SESSION['staff_name']` - Staff member name
- `$_SESSION['staff_email']` - Staff member email
- `$_SESSION['role']` - Staff member role
- `$_SESSION['login_time']` - Login timestamp

## How to Use

### 1. **First Time Setup**
1. Navigate to `login.php`
2. System automatically creates credentials table
3. Use any of the demo credentials above

### 2. **Login Process**
1. Enter email and password
2. System validates credentials **strictly**
3. User is redirected to role-appropriate dashboard

### 3. **Session Management**
- Sessions are maintained across page visits
- **Automatic role validation on every page**
- **Clean logout with complete session destruction**
- **1-hour session timeout** for security

## Security Features

### ✅ **Session Protection**
- **All dashboard pages check for valid session**
- **Role-based access validation on every page**
- **Automatic redirect to login if unauthorized**
- **Session timeout after 1 hour of inactivity**

### ✅ **Input Validation**
- Email format validation
- Password requirement enforcement
- SQL injection prevention with prepared statements

### ✅ **Session Security**
- **Secure session handling**
- **Complete session destruction on logout**
- **Cookie security settings**
- **No persistent unauthorized access**

## File Structure

```
├── login.php              # Main login page with authentication
├── logout.php             # Complete session destruction and logout
├── caretaker_dashboard.php # Protected dashboard with strict session check
├── feeding.php            # Protected feeding page with strict session check
├── test_auth.php          # Authentication testing script
├── config/database.php    # Database connection
└── README_LOGIN_SYSTEM.md # This documentation
```

## Implementation Details

### **Strict Authentication**
The system now enforces strict password validation:
```php
if ($user && $password === $user['cpassword']) {
    // Only allow access if password matches exactly
    $_SESSION['staff_id'] = $user['cstaffid'];
    // ... set other session variables
} else {
    $error_message = "Invalid email or password. Please try again.";
}
```

### **Session Validation**
Each protected page includes strict validation:
```php
session_start();
if (!isset($_SESSION['staff_id']) || !in_array($_SESSION['role'], ['Allowed Role 1', 'Allowed Role 2'])) {
    session_destroy(); // Clear invalid session
    header('Location: login.php');
    exit();
}

// Optional: Session timeout check
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 3600) {
    session_destroy();
    header('Location: login.php?timeout=1');
    exit();
}
```

### **Dashboard Routing**
Login success triggers automatic routing based on user role:
```php
switch ($user['crole']) {
    case 'Manager':
        header('Location: homePage.php');
        break;
    case 'Tortoise Caretaker':
        header('Location: caretaker_dashboard.php');
        break;
    case 'Veterinarian':
        header('Location: vetdashboard.php');
        break;
    case 'Breeding Specialist':
        header('Location: BSDashboard.php');
        break;
    case 'Inventory Manager':
        header('Location: inventory_dashboard.php');
        break;
    case 'Environment Monitor':
        header('Location: Environment_Monitor.php');
        break;
    // ... other roles
}
```