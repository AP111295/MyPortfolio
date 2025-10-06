# Portfolio Contact Form with PHP & MySQL

This portfolio includes a fully functional contact form that saves messages to a MySQL database with an admin panel to manage messages.

## Features

- ✅ Contact form validation (client-side and server-side)
- ✅ MySQL database storage
- ✅ Admin panel to view and manage messages
- ✅ Mark messages as read/unread
- ✅ Delete messages
- ✅ Responsive design
- ✅ Security measures (input sanitization, SQL injection prevention)

## Setup Instructions

### Prerequisites
- XAMPP installed and running
- Apache and MySQL services started in XAMPP

### Step 1: Setup the Project
1. Copy your portfolio files to `C:\xampp\htdocs\portfolio` (or your preferred directory)
2. Make sure Apache and MySQL are running in XAMPP Control Panel

### Step 2: Create Database
1. Open your browser and go to: `http://localhost/portfolio/setup_database.php`
2. This will automatically create the database and table for you
3. You should see success messages

### Step 3: Test the Contact Form
1. Go to: `http://localhost/portfolio/index.html`
2. Scroll down to the contact form
3. Fill out and submit the form
4. You should be redirected to a success page

### Step 4: Access Admin Panel
1. Go to: `http://localhost/portfolio/admin/`
2. Login with:
   - Username: `admin`
   - Password: `admin123`
3. View and manage all contact form submissions

## File Structure

```
portfolio/
├── config/
│   └── database.php          # Database configuration
├── admin/
│   └── index.php            # Admin panel
├── setup_database.php       # Database setup script
├── process_contact.php      # Form handler
├── index.html              # Main portfolio page
├── style.css               # Styles
├── script.js               # JavaScript
└── .htaccess               # Server configuration
```

## Database Schema

### Table: `contact_messages`
- `id` (INT, Primary Key, Auto Increment)
- `first_name` (VARCHAR 100)
- `last_name` (VARCHAR 100)  
- `email` (VARCHAR 255)
- `message` (TEXT)
- `created_at` (TIMESTAMP)
- `is_read` (BOOLEAN)

## Security Features

1. **Input Validation**: All form inputs are validated and sanitized
2. **SQL Injection Prevention**: Using PDO prepared statements
3. **XSS Protection**: All output is escaped using htmlspecialchars()
4. **Session Management**: Admin authentication with sessions
5. **Error Handling**: Proper error handling without exposing sensitive info

## Customization

### Change Admin Credentials
Edit `admin/index.php` lines 6-7:
```php
$admin_username = 'your_username';
$admin_password = 'your_secure_password';
```

### Database Configuration
Edit `config/database.php` if you need different database settings:
```php
$host = 'localhost';
$dbname = 'portfolio_db';
$username = 'root';
$password = '';
```

### Form Styling
The form uses your existing CSS classes. You can customize the response page styling in `process_contact.php`.

## Testing

1. **Form Submission**: Try submitting the form with valid and invalid data
2. **Admin Panel**: Test viewing, marking as read, and deleting messages
3. **Validation**: Test form validation by submitting empty or invalid data
4. **Security**: Test with HTML/JavaScript in form fields to ensure they're sanitized

## Troubleshooting

### Common Issues:

1. **Database Connection Error**
   - Make sure MySQL is running in XAMPP
   - Check database credentials in `config/database.php`

2. **Form Not Submitting**
   - Ensure Apache is running
   - Check that form action points to correct path

3. **Admin Panel Not Loading**
   - Clear browser cache
   - Check PHP error logs in XAMPP

4. **Permission Errors**
   - Make sure XAMPP has proper file permissions
   - Run XAMPP as administrator if needed

## Production Deployment

Before deploying to a live server:

1. Change admin credentials
2. Update database configuration
3. Remove or secure `setup_database.php`
4. Enable proper error handling (disable error display)
5. Add HTTPS configuration
6. Implement proper session security
7. Add rate limiting for form submissions

## Support

If you encounter any issues:
1. Check XAMPP error logs
2. Ensure all files are in the correct directory
3. Verify database connection
4. Test each component individually

---

**Note**: This is a development setup. For production use, implement additional security measures and proper server configuration.