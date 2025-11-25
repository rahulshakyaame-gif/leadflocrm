# 🚀 LeadFlow CRM - Production Deployment Roadmap

This comprehensive guide will walk you through deploying the LeadFlow CRM system to a live server.

---

## 📋 Table of Contents

1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [Server Requirements](#server-requirements)
3. [Step-by-Step Deployment](#step-by-step-deployment)
4. [Post-Deployment Configuration](#post-deployment-configuration)
5. [Security Hardening](#security-hardening)
6. [Testing & Verification](#testing--verification)
7. [Troubleshooting](#troubleshooting)
8. [Maintenance & Backups](#maintenance--backups)

---

## 📝 Pre-Deployment Checklist

Before deploying to production, ensure you have:

- [ ] Access to your web hosting control panel (cPanel, Plesk, etc.)
- [ ] MySQL/MariaDB database credentials
- [ ] FTP/SFTP or SSH access to your server
- [ ] SSL certificate installed (for HTTPS)
- [ ] Domain name configured and pointing to your server
- [ ] Backup of any existing data

---

## 🖥️ Server Requirements

### Minimum Requirements

| Component | Requirement |
|-----------|-------------|
| **PHP Version** | 7.4 or higher (8.0+ recommended) |
| **MySQL/MariaDB** | 5.7+ / 10.2+ |
| **Web Server** | Apache 2.4+ with mod_rewrite |
| **Disk Space** | 100 MB minimum |
| **Memory** | 256 MB minimum |
| **SSL Certificate** | Required for production |

### Required PHP Extensions

```bash
- PDO
- pdo_mysql
- json
- mbstring
- session
- openssl
```

### Apache Modules Required

```bash
- mod_rewrite
- mod_headers
- mod_deflate (optional, for compression)
- mod_expires (optional, for caching)
```

---

## 🚀 Step-by-Step Deployment

### Step 1: Prepare Your Files

#### 1.1 Create Production Configuration

```bash
# Copy the example configuration file
cp config.example.php config.php
```

#### 1.2 Edit `config.php` with your production settings

```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_secure_password');

// Application Settings
define('APP_ENV', 'production');
define('APP_DEBUG', false);
define('APP_URL', 'https://yourdomain.com');

// Security Settings
define('SESSION_SECURE', true); // HTTPS only
```

#### 1.3 Update Database Connection

Replace the contents of `api/db.php` with `api/db.production.php`:

```bash
# Backup original
mv api/db.php api/db.development.php

# Use production version
cp api/db.production.php api/db.php
```

### Step 2: Upload Files to Server

#### Option A: Using FTP/SFTP (FileZilla, WinSCP, etc.)

1. Connect to your server using FTP/SFTP credentials
2. Navigate to your web root directory (usually `public_html` or `www`)
3. Upload all project files **except**:
   - `config.php` (create this on server)
   - `logs/` directory (create on server)
   - Development files (`.git`, `node_modules`, etc.)

#### Option B: Using SSH/Terminal

```bash
# From your local machine
scp -r /path/to/crm user@yourserver.com:/path/to/public_html/

# Or using rsync (recommended)
rsync -avz --exclude 'config.php' --exclude 'logs/' \
  /path/to/crm/ user@yourserver.com:/path/to/public_html/
```

### Step 3: Set Up Database

#### 3.1 Create Database

**Via cPanel:**
1. Go to MySQL Databases
2. Create a new database (e.g., `yourusername_crm`)
3. Create a database user with a strong password
4. Add user to database with ALL PRIVILEGES

**Via Command Line:**

```bash
mysql -u root -p
```

```sql
CREATE DATABASE crm_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'crm_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT SELECT, INSERT, UPDATE, DELETE ON crm_system.* TO 'crm_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 3.2 Import Database Schema

```bash
mysql -u crm_user -p crm_system < database_setup.sql
```

**Or via phpMyAdmin:**
1. Select your database
2. Click "Import" tab
3. Choose `database_setup.sql`
4. Click "Go"

### Step 4: Set File Permissions

```bash
# Navigate to your project directory
cd /path/to/public_html/crm

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Create logs directory with write permissions
mkdir -p logs
chmod 755 logs

# Protect sensitive files
chmod 600 config.php
```

### Step 5: Configure Web Server

#### Apache (.htaccess is already included)

The `.htaccess` file is already configured with:
- HTTPS enforcement
- Security headers
- File protection
- Compression and caching

**To enable HTTPS redirect**, edit `.htaccess` and uncomment:

```apache
# Force HTTPS (uncomment in production)
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

#### Nginx Configuration (if using Nginx)

Create a configuration file:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    
    root /path/to/public_html/crm;
    index index.html login.html;
    
    # SSL Configuration
    ssl_certificate /path/to/ssl/cert.pem;
    ssl_certificate_key /path/to/ssl/key.pem;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # PHP Configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
    
    # Protect sensitive files
    location ~ /(config\.php|\.git|logs|backups) {
        deny all;
        return 404;
    }
    
    # Static file caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

---

## ⚙️ Post-Deployment Configuration

### 1. Create Admin Account

The database setup creates a default admin account:
- **Email:** `admin@example.com`
- **Password:** `admin123`

> ⚠️ **CRITICAL:** Change this password immediately!

**To change the admin password:**

1. Log in with default credentials
2. Go to Team Management
3. Edit the admin user
4. Set a strong password

**Or via database:**

```bash
# Generate password hash
php -r "echo password_hash('your_new_password', PASSWORD_DEFAULT);"

# Update in database
mysql -u crm_user -p crm_system
```

```sql
UPDATE users 
SET password = '$2y$10$...' -- paste the hash here
WHERE email = 'admin@example.com';
```

### 2. Configure Email (Optional)

If you want email notifications, you'll need to add email functionality. Consider using:
- PHPMailer
- SendGrid API
- Mailgun API
- SMTP configuration

### 3. Set Up Cron Jobs (Optional)

For automated tasks like cleanup or reports:

```bash
# Edit crontab
crontab -e

# Add cron jobs (examples)
# Daily cleanup at 2 AM
0 2 * * * php /path/to/crm/cron/cleanup.php

# Weekly reports on Monday at 9 AM
0 9 * * 1 php /path/to/crm/cron/weekly_report.php
```

---

## 🔒 Security Hardening

### 1. SSL/HTTPS Configuration

Ensure SSL is properly configured:

```bash
# Test SSL configuration
curl -I https://yourdomain.com
```

### 2. Database Security

```sql
-- Remove test databases
DROP DATABASE IF EXISTS test;

-- Ensure user has minimal privileges
REVOKE ALL PRIVILEGES ON *.* FROM 'crm_user'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON crm_system.* TO 'crm_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. File Security Checklist

- [ ] `config.php` has 600 permissions
- [ ] `.htaccess` is active and working
- [ ] Directory listing is disabled
- [ ] Error display is turned off in production
- [ ] Logs directory is not web-accessible

### 4. PHP Security Settings

Add to `php.ini` or `.htaccess`:

```ini
expose_php = Off
display_errors = Off
log_errors = On
error_log = /path/to/crm/logs/php-error.log
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_only_cookies = 1
```

### 5. Regular Updates

- Keep PHP updated
- Monitor security advisories
- Update dependencies regularly
- Review access logs for suspicious activity

---

## ✅ Testing & Verification

### 1. Basic Functionality Tests

- [ ] Can access login page: `https://yourdomain.com/login.html`
- [ ] Can log in with admin credentials
- [ ] Dashboard loads correctly
- [ ] Can create a new lead
- [ ] Can view leads list
- [ ] Can log a call
- [ ] Kanban board displays correctly
- [ ] Team management works (admin only)
- [ ] CSV import works
- [ ] Webhook integrations can be created

### 2. Security Tests

```bash
# Test HTTPS redirect
curl -I http://yourdomain.com

# Test security headers
curl -I https://yourdomain.com

# Test protected files (should return 403/404)
curl https://yourdomain.com/config.php
curl https://yourdomain.com/logs/
```

### 3. Performance Tests

- [ ] Page load time < 3 seconds
- [ ] Database queries are optimized
- [ ] Static assets are cached
- [ ] Compression is enabled

### 4. Webhook Testing

Test webhook endpoint:

```bash
curl -X POST "https://yourdomain.com/api/webhook.php?token=YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Lead",
    "phone": "1234567890",
    "email": "test@example.com"
  }'
```

---

## 🔧 Troubleshooting

### Common Issues

#### 1. Database Connection Failed

**Error:** "Connection failed: Access denied"

**Solution:**
- Verify database credentials in `config.php`
- Check database user has correct privileges
- Ensure database exists
- Check MySQL is running

```bash
# Test database connection
mysql -u crm_user -p -h localhost crm_system
```

#### 2. 500 Internal Server Error

**Solution:**
- Check PHP error logs: `logs/php-error.log`
- Verify file permissions
- Ensure `.htaccess` is compatible with server
- Check PHP version compatibility

#### 3. .htaccess Not Working

**Solution:**
- Verify `mod_rewrite` is enabled
- Check `AllowOverride All` in Apache config
- Test with simple .htaccess rule

```bash
# Enable mod_rewrite (Ubuntu/Debian)
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### 4. Session Issues

**Solution:**
- Check session directory permissions
- Verify session settings in `php.ini`
- Clear browser cookies
- Check `session.save_path` is writable

#### 5. Webhook Not Receiving Data

**Solution:**
- Verify webhook token is correct
- Check integration is marked as "active"
- Review webhook logs
- Test with curl command
- Verify firewall allows incoming connections

---

## 💾 Maintenance & Backups

### 1. Database Backups

**Automated Daily Backup:**

```bash
#!/bin/bash
# Save as: /path/to/crm/backups/backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/path/to/crm/backups"
DB_NAME="crm_system"
DB_USER="crm_user"
DB_PASS="your_password"

# Create backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/backup_$DATE.sql.gz

# Keep only last 30 days
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +30 -delete
```

**Add to crontab:**

```bash
0 3 * * * /path/to/crm/backups/backup.sh
```

### 2. File Backups

```bash
# Backup entire application
tar -czf crm_backup_$(date +%Y%m%d).tar.gz /path/to/crm \
  --exclude='logs/*' --exclude='backups/*'
```

### 3. Monitoring

**Monitor disk space:**

```bash
df -h
```

**Monitor database size:**

```sql
SELECT 
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.tables
WHERE table_schema = 'crm_system'
GROUP BY table_schema;
```

**Monitor error logs:**

```bash
tail -f /path/to/crm/logs/php-error.log
```

### 4. Regular Maintenance Tasks

**Weekly:**
- Review error logs
- Check disk space
- Verify backups are running

**Monthly:**
- Update PHP and MySQL if needed
- Review and archive old leads
- Optimize database tables

```sql
OPTIMIZE TABLE leads, calls, users, integrations;
```

**Quarterly:**
- Security audit
- Performance review
- User access review

---

## 📞 Support & Additional Resources

### Documentation

- [PHP Manual](https://www.php.net/manual/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Apache Documentation](https://httpd.apache.org/docs/)

### Security Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)

### Webhook Integration Guides

- [Google Ads Lead Form Extensions](https://developers.google.com/google-ads/api/docs/conversions/upload-leads)
- [Facebook Lead Ads](https://developers.facebook.com/docs/marketing-api/guides/lead-ads)

---

## 🎉 Deployment Complete!

Once you've completed all steps:

1. ✅ Access your CRM at `https://yourdomain.com/login.html`
2. ✅ Log in with admin credentials
3. ✅ Change default password
4. ✅ Create team members
5. ✅ Start managing leads!

---

## 📝 Quick Reference

### Important URLs

- **Login:** `https://yourdomain.com/login.html`
- **Dashboard:** `https://yourdomain.com/index.html`
- **Admin Panel:** `https://yourdomain.com/admin.html`
- **Import Leads:** `https://yourdomain.com/import.html`
- **Integrations:** `https://yourdomain.com/integrations.html`
- **Webhook Endpoint:** `https://yourdomain.com/api/webhook.php?token=YOUR_TOKEN`

### Important Files

- **Configuration:** `config.php`
- **Database Connection:** `api/db.php`
- **Security:** `.htaccess`
- **Database Schema:** `database_setup.sql`
- **Error Logs:** `logs/php-error.log`

### Default Credentials

- **Email:** `admin@example.com`
- **Password:** `admin123` (⚠️ CHANGE IMMEDIATELY!)

---

**Good luck with your deployment! 🚀**
