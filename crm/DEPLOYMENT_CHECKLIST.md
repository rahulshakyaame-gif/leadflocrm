# 🚀 Pre-Deployment Checklist

Use this checklist before deploying to your live server.

## 📋 Files to Prepare

- [ ] Copy `config.example.php` to `config.php` and update with production values
- [ ] Replace `api/db.php` with `api/db.production.php` content
- [ ] Review `.htaccess` and uncomment HTTPS redirect
- [ ] Ensure `.gitignore` is configured correctly
- [ ] Create `logs/` directory on server

## 🔐 Security Checklist

- [ ] SSL certificate installed and configured
- [ ] HTTPS redirect enabled in `.htaccess`
- [ ] `config.php` has 600 permissions
- [ ] `APP_DEBUG` set to `false` in `config.php`
- [ ] `APP_ENV` set to `'production'` in `config.php`
- [ ] Default admin password changed from `admin123`
- [ ] Database user has minimal required privileges (not root)
- [ ] Error display turned off (`display_errors = Off`)
- [ ] Error logging enabled and configured
- [ ] Session security settings enabled (secure, httponly)

## 💾 Database Checklist

- [ ] Database created with utf8mb4 charset
- [ ] Database user created with strong password
- [ ] User granted only necessary privileges (SELECT, INSERT, UPDATE, DELETE)
- [ ] `database_setup.sql` imported successfully
- [ ] Default admin user exists and accessible
- [ ] All tables created with proper indexes
- [ ] Test database connection from application

## 📁 File Upload Checklist

- [ ] All application files uploaded to server
- [ ] File permissions set correctly (644 for files, 755 for directories)
- [ ] `config.php` created on server (not uploaded from local)
- [ ] `logs/` directory created with write permissions
- [ ] No development files uploaded (.git, node_modules, etc.)
- [ ] Static assets accessible (CSS, JS, images)

## 🌐 Server Configuration

- [ ] PHP version 7.4 or higher
- [ ] MySQL/MariaDB 5.7 or higher
- [ ] Required PHP extensions installed (PDO, pdo_mysql, json, mbstring)
- [ ] Apache mod_rewrite enabled
- [ ] Apache mod_headers enabled (for security headers)
- [ ] `.htaccess` file is being processed
- [ ] Domain pointing to correct directory
- [ ] Web server restart (if configuration changed)

## ✅ Testing Checklist

### Basic Functionality
- [ ] Login page loads: `https://yourdomain.com/login.html`
- [ ] Can log in with admin credentials
- [ ] Dashboard displays correctly
- [ ] Can create a new lead
- [ ] Can view leads list
- [ ] Can log a call
- [ ] Kanban board works
- [ ] Team management accessible (admin)
- [ ] CSV import works
- [ ] Integrations page loads

### Security Tests
- [ ] HTTP redirects to HTTPS
- [ ] Security headers present (check with curl or browser dev tools)
- [ ] `config.php` not accessible via browser
- [ ] `logs/` directory not accessible via browser
- [ ] `.git` directory not accessible (if exists)
- [ ] Session cookies have secure and httponly flags
- [ ] XSS protection headers present
- [ ] Clickjacking protection (X-Frame-Options) present

### Webhook Tests
- [ ] Can create new integration
- [ ] Webhook token generated
- [ ] Webhook endpoint accessible
- [ ] Test webhook with curl command
- [ ] Lead created from webhook data
- [ ] Integration stats updated

## 📊 Post-Deployment

- [ ] Change default admin password immediately
- [ ] Create additional admin/team users
- [ ] Set up automated database backups
- [ ] Configure error log monitoring
- [ ] Set up uptime monitoring (optional)
- [ ] Document custom configuration changes
- [ ] Test all critical user workflows
- [ ] Verify email notifications (if configured)
- [ ] Set up cron jobs (if needed)

## 🔄 Backup Verification

- [ ] Database backup script configured
- [ ] Test database backup/restore
- [ ] File backup strategy in place
- [ ] Backup retention policy defined
- [ ] Backup storage location secured
- [ ] Test restore procedure

## 📞 Emergency Contacts

**Hosting Provider:** ___________________________
**Support Phone:** ___________________________
**Support Email:** ___________________________

**Database Admin:** ___________________________
**Server Admin:** ___________________________

## 📝 Deployment Notes

**Deployment Date:** ___________________________
**Deployed By:** ___________________________
**Server Details:** ___________________________
**Database Name:** ___________________________
**Domain:** ___________________________

**Issues Encountered:**
- 
- 
- 

**Resolutions:**
- 
- 
- 

---

## Quick Commands Reference

### Test Database Connection
```bash
mysql -u your_user -p -h localhost your_database
```

### Test HTTPS Redirect
```bash
curl -I http://yourdomain.com
```

### Check Security Headers
```bash
curl -I https://yourdomain.com
```

### Test Webhook
```bash
curl -X POST "https://yourdomain.com/api/webhook.php?token=YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","phone":"1234567890","email":"test@test.com"}'
```

### View Error Logs
```bash
tail -f /path/to/crm/logs/php-error.log
```

### Database Backup
```bash
mysqldump -u user -p database_name | gzip > backup_$(date +%Y%m%d).sql.gz
```

---

**Once all items are checked, your CRM is ready for production! 🎉**
