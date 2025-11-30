# CampMart Installation Guide

## Quick Start

Follow these steps to get CampMart up and running on your server.

### Prerequisites

Before you begin, ensure you have:
- PHP 7.4 or higher
- MySQL 5.7 or higher  
- Apache or Nginx web server
- mod_rewrite enabled (Apache)
- At least 1GB disk space
- SSL certificate (recommended for production)

### Step 1: Download and Extract

1. Clone or download the CampMart repository
2. Extract to your web server directory (e.g., `/var/www/html/campmart` or `htdocs/campmart`)

### Step 2: Database Setup

1. Create a new MySQL database:
   ```sql
   CREATE DATABASE campmart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Create a database user (replace with your own credentials):
   ```sql
   CREATE USER 'campmart_user'@'localhost' IDENTIFIED BY 'your_secure_password';
   GRANT ALL PRIVILEGES ON campmart.* TO 'campmart_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

3. Import the database schema:
   ```bash
   mysql -u campmart_user -p campmart < database/schema.sql
   ```

### Step 3: Configuration

1. Open `config/config.php` in a text editor

2. Update the database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'campmart_user');
   define('DB_PASS', 'your_secure_password');
   define('DB_NAME', 'campmart');
   ```

3. Update the site URL:
   ```php
   define('SITE_URL', 'http://yourdomain.com/campmart');
   ```

4. For production, disable error display:
   ```php
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

### Step 4: File Permissions

Set appropriate permissions for upload directories:

```bash
chmod 755 assets/uploads/
chmod 755 assets/uploads/listings/
chmod 755 assets/uploads/profiles/
chmod 755 assets/uploads/lost_found/
```

For Linux/Unix systems:
```bash
chown -R www-data:www-data assets/uploads/
```

### Step 5: Apache Configuration (if using Apache)

1. Create or edit `.htaccess` in the root directory:
   ```apache
   RewriteEngine On
   RewriteBase /campmart/
   
   # Force HTTPS (uncomment in production)
   # RewriteCond %{HTTPS} off
   # RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   
   # Protect sensitive files
   <FilesMatch "(config\.php|\.sql)$">
       Order allow,deny
       Deny from all
   </FilesMatch>
   ```

### Step 6: Security Hardening

1. **Change default configuration:**
   - Update all default passwords
   - Generate new CSRF tokens
   - Configure session security

2. **Create default avatar:**
   - Place a default avatar image at `assets/images/default-avatar.png`

3. **SSL Certificate (Production):**
   - Install an SSL certificate
   - Update `SITE_URL` to use `https://`
   - Enable HTTPS redirect in `.htaccess`

### Step 7: Testing

1. Open your browser and navigate to your installation URL
2. You should see the CampMart homepage
3. Try registering a new account
4. Test creating a listing
5. Test searching and filtering

### Creating Admin Account

To create an admin account:

1. Register normally through the website
2. Update the user in the database:
   ```sql
   UPDATE users SET is_admin = 1 WHERE email = 'your@email.com';
   ```

### Common Issues

**Database Connection Fails:**
- Check database credentials in `config/config.php`
- Verify MySQL is running
- Check firewall settings

**Upload Errors:**
- Check file permissions on upload directories
- Verify `upload_max_filesize` in `php.ini`
- Check `MAX_FILE_SIZE` in `config/config.php`

**Session Issues:**
- Ensure session directory is writable
- Check `session.save_path` in `php.ini`
- Verify cookies are enabled

**Images Not Showing:**
- Check file paths in `config/config.php`
- Verify upload directory permissions
- Check Apache/Nginx configuration

### Performance Optimization

For production with 10,000+ MAU:

1. **Enable PHP OpCache:**
   ```ini
   opcache.enable=1
   opcache.memory_consumption=128
   opcache.max_accelerated_files=10000
   ```

2. **MySQL Optimization:**
   - Enable query cache
   - Optimize table indexes
   - Regular database maintenance

3. **Caching:**
   - Implement Redis/Memcached
   - Enable browser caching
   - Use CDN for static assets

4. **Server:**
   - Use PHP-FPM
   - Enable Gzip compression
   - Configure load balancing

### Backup

Regular backups are essential:

```bash
# Database backup
mysqldump -u campmart_user -p campmart > backup_$(date +%Y%m%d).sql

# Files backup
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz assets/uploads/
```

### Maintenance Mode

To enable maintenance mode, create `maintenance.php` in the root:

```php
<?php
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    include 'maintenance.html';
    exit();
}
?>
```

### Support

For issues or questions:
- Email: support@campmart.ng
- Documentation: Check README.md
- GitHub Issues: Report bugs

### Security Updates

Stay updated:
1. Subscribe to security bulletins
2. Regularly update PHP and MySQL
3. Monitor error logs
4. Review access logs
5. Keep dependencies updated

---

**Congratulations!** Your CampMart installation is complete.

For advanced configuration and features, refer to the main README.md file.
