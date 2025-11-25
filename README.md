# LeadFlow CRM 📊

A modern, feature-rich Customer Relationship Management (CRM) system designed for lead tracking, call management, and team collaboration.

## ✨ Features

- **Lead Management** - Track and manage leads through their entire lifecycle
- **Call Tracking** - Log and monitor all customer interactions
- **Kanban Board** - Visual pipeline management with drag-and-drop
- **Team Management** - Role-based access control (Admin/Team)
- **CSV Import** - Bulk import leads from CSV files
- **Webhook Integrations** - Connect with Google Ads, Facebook Lead Ads, and custom sources
- **Dashboard Analytics** - Real-time insights and performance metrics
- **Responsive Design** - Works seamlessly on desktop and mobile devices

## 🚀 Quick Start

### For Development (XAMPP/Local)

1. **Clone or download the project**
   ```bash
   cd C:\xampp\htdocs
   git clone <your-repo-url> crm
   ```

2. **Start XAMPP**
   - Start Apache and MySQL

3. **Create Database**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Import `setup.sql`

4. **Access the Application**
   - Open: `http://localhost/crm/login.html`
   - Default credentials:
     - Email: `admin@example.com`
     - Password: `admin123`

### For Production Deployment

📖 **See [ROADMAP.md](ROADMAP.md) for complete deployment instructions**

Quick overview:
1. Set up your server (PHP 7.4+, MySQL 5.7+)
2. Create `config.php` from `config.example.php`
3. Upload files via FTP/SSH
4. Import `database_setup.sql`
5. Configure `.htaccess` for HTTPS
6. Change default admin password

## 📁 Project Structure

```
crm/
├── api/                    # Backend API endpoints
│   ├── auth.php           # Authentication
│   ├── calls.php          # Call management
│   ├── db.php             # Database connection
│   ├── import.php         # CSV import
│   ├── integrations.php   # Webhook integrations
│   ├── leads.php          # Lead management
│   ├── reports.php        # Analytics & reports
│   ├── users.php          # User management
│   └── webhook.php        # Webhook receiver
├── assets/
│   ├── css/
│   │   └── style.css      # Main stylesheet
│   └── js/
│       ├── api.js         # API client
│       └── app.js         # Main application logic
├── admin.html             # Team management page
├── import.html            # CSV import page
├── index.html             # Main dashboard
├── integrations.html      # Webhook integrations
├── login.html             # Login page
├── config.example.php     # Configuration template
├── database_setup.sql     # Production database schema
├── setup.sql              # Development database schema
├── .htaccess              # Apache configuration
├── .gitignore             # Git ignore rules
└── ROADMAP.md             # Deployment guide
```

## 🔧 Configuration

### Database Configuration

Edit `config.php` (create from `config.example.php`):

```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'crm_system');
define('DB_USER', 'your_user');
define('DB_PASS', 'your_password');
```

### Environment Settings

```php
define('APP_ENV', 'production');  // production, staging, development
define('APP_DEBUG', false);       // Set to false in production
define('APP_URL', 'https://yourdomain.com');
```

## 🔐 Security

- **HTTPS Required** - Always use SSL in production
- **Password Hashing** - Uses PHP's `password_hash()` with bcrypt
- **Session Security** - HTTP-only, secure cookies
- **SQL Injection Protection** - Prepared statements throughout
- **XSS Protection** - Security headers configured
- **CSRF Protection** - Recommended to implement for forms
- **File Upload Validation** - CSV files only

### Important Security Steps

1. ⚠️ **Change default admin password immediately**
2. ✅ Enable HTTPS and force redirect
3. ✅ Set proper file permissions (644 for files, 755 for directories)
4. ✅ Protect `config.php` (chmod 600)
5. ✅ Keep `logs/` directory outside web root or protected
6. ✅ Regularly update PHP and MySQL

## 📊 Database Schema

### Tables

- **users** - System users (admin/team members)
- **leads** - Lead information and status
- **calls** - Call history and notes
- **integrations** - Webhook integration configurations

### Relationships

```
users (1) ──── (many) leads
leads (1) ──── (many) calls
integrations (1) ──── (many) leads (via webhook)
```

## 🔌 Webhook Integration

### Supported Platforms

- Google Ads Lead Form Extensions
- Facebook Lead Ads
- Custom webhooks

### Webhook URL Format

```
https://yourdomain.com/api/webhook.php?token=YOUR_WEBHOOK_TOKEN
```

### Example Webhook Payload

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "1234567890"
}
```

## 🛠️ Development

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite
- Modern web browser

### Local Setup

1. Install XAMPP or similar (Apache + MySQL + PHP)
2. Clone repository to `htdocs/crm`
3. Import `setup.sql` via phpMyAdmin
4. Access `http://localhost/crm/login.html`

### Making Changes

- **Frontend**: Edit HTML files and `assets/css/style.css`
- **JavaScript**: Modify `assets/js/app.js` and `assets/js/api.js`
- **Backend**: Update PHP files in `api/` directory
- **Database**: Modify schema in `setup.sql` or `database_setup.sql`

## 📈 Features in Detail

### Lead Management
- Create, edit, and delete leads
- Assign leads to team members
- Track lead status (New → Contacted → Interested → Qualified → Converted/Lost)
- Filter and search capabilities
- Bulk import via CSV

### Call Tracking
- Log calls with outcome and duration
- Add detailed notes
- View complete call history
- Track call outcomes (Connected, No Answer, Busy, etc.)

### Kanban Board
- Visual pipeline management
- Drag-and-drop lead status updates
- Real-time status tracking
- Organized by lead status columns

### Team Management (Admin Only)
- Create and manage team members
- Role-based access control
- Assign leads to team members
- View team performance

### Analytics Dashboard
- Lead status distribution
- Call outcome statistics
- Source tracking
- Time-based trends

## 🐛 Troubleshooting

### Common Issues

**Database Connection Failed**
- Check credentials in `config.php`
- Verify MySQL is running
- Ensure database exists

**Login Not Working**
- Clear browser cache and cookies
- Check session configuration
- Verify user exists in database

**Webhook Not Receiving Data**
- Verify token is correct
- Check integration is active
- Review server firewall settings
- Test with curl command

**500 Internal Server Error**
- Check PHP error logs
- Verify file permissions
- Ensure `.htaccess` is compatible
- Check PHP version compatibility

## 📝 License

This project is proprietary software. All rights reserved.

## 🤝 Support

For issues and questions:
1. Check [ROADMAP.md](ROADMAP.md) for deployment help
2. Review troubleshooting section above
3. Check server error logs
4. Contact your system administrator

## 🔄 Updates & Maintenance

### Regular Tasks

**Daily**
- Monitor error logs
- Check webhook integrations

**Weekly**
- Review lead pipeline
- Database backup verification

**Monthly**
- Update PHP/MySQL if needed
- Security audit
- Performance optimization

### Backup Strategy

```bash
# Database backup
mysqldump -u user -p crm_system | gzip > backup_$(date +%Y%m%d).sql.gz

# File backup
tar -czf crm_files_$(date +%Y%m%d).tar.gz /path/to/crm
```

## 🎯 Roadmap

- [ ] Email notifications
- [ ] SMS integration
- [ ] Advanced reporting
- [ ] Mobile app
- [ ] API documentation
- [ ] Two-factor authentication
- [ ] Activity timeline
- [ ] Document attachments

---

**Built with ❤️ for efficient lead management**
