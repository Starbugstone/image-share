# ImageShare Deployment Guide

## Pre-Deployment Checklist

### 1. Environment Configuration

#### Backend Configuration (.env)
Copy the production template and update for your environment:

```bash
# Copy production template
cp .env.production.example .env

# Edit with your production values:
# - APP_SECRET: Generate a secure 32+ character secret
# - DATABASE_URL: Your production database connection
# - MAILER_DSN: Your email service configuration
# - FRONTEND_URL: Your production domain (CRITICAL for email verification)
```

Key variables to update:
- `FRONTEND_URL=https://yourdomain.com` (CRITICAL for email verification)
- `DATABASE_URL="mysql://username:password@host:port/database_name"`
- `MAILER_DSN=smtp://username:password@smtp.example.com:587`
- `APP_SECRET=your-production-secret-key`

#### Frontend URL Configuration
Update `config/packages/routing.yaml`:

```yaml
framework:
    router:
        utf8: true
        # IMPORTANT: Update this URL for production deployment
        default_uri: https://yourdomain.com

when@prod:
    framework:
        router:
            strict_requirements: null
```

#### Frontend Environment
Copy the production template and update for your environment:

```bash
# Copy production template
cp frontend/.env.production.example frontend/.env.production

# Edit with your production values:
# - VITE_API_BASE_URL: Your backend API URL
```

Key variables to update:
- `VITE_API_BASE_URL=https://yourdomain.com/api`

### 2. Database Setup

```bash
# Run database migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Setup required directories and permissions
php bin/console app:setup-profile-images
php bin/console app:make-usernames-unique

# Create admin user (optional)
php bin/console app:user-admin create admin@yourdomain.com
php bin/console app:user-admin promote admin@yourdomain.com ROLE_ADMIN
```

### 3. File Permissions & Directories

Ensure the following directories exist and are writable:

```bash
# Image storage (outside public directory)
mkdir -p images/profile_images
chmod 755 images
chmod 755 images/profile_images

# Symfony cache and logs
chmod -R 755 var/cache
chmod -R 755 var/log

# Public assets
chmod -R 755 public
```

### 4. Frontend Build

```bash
# Build production frontend
cd frontend
npm install
npm run build

# Copy built assets to public directory
# (This should be automated in your deployment pipeline)
```

### 5. Security Considerations

#### SSL/HTTPS
- Ensure SSL certificate is properly configured
- Update all URLs to use HTTPS
- Configure proper security headers

#### File Upload Security
- Verify image upload directory is outside web root
- Ensure proper file type validation
- Set appropriate file size limits

#### Email Security
- Use authenticated SMTP for email delivery
- Configure SPF/DKIM records for your domain
- Test email delivery in production environment

### 6. Performance Optimization

#### Symfony Optimization
```bash
# Clear and warm up cache
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# Install optimized autoloader
composer install --no-dev --optimize-autoloader
```

#### Frontend Optimization
- Ensure Vite build optimization is enabled
- Configure CDN for static assets (optional)
- Enable gzip compression on web server

### 7. Monitoring & Logging

#### Application Logs
- Configure log rotation for `var/log/`
- Set up log monitoring/alerting
- Monitor email delivery success/failures

#### Database Monitoring
- Monitor database performance
- Set up automated backups
- Monitor disk space usage

#### Email Monitoring
- Test email verification flow
- Monitor email delivery rates
- Set up bounce handling

## Deployment Steps

### 1. Code Deployment
```bash
# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
cd frontend && npm install && npm run build
```

### 2. Configuration Updates
```bash
# Update environment variables
cp .env.example .env
# Edit .env with production values

# Update routing configuration
# Edit config/packages/routing.yaml with production URL
```

### 3. Database Migration
```bash
# Backup database first!
php bin/console doctrine:migrations:migrate --no-interaction
```

### 4. Cache & Permissions
```bash
# Clear cache
php bin/console cache:clear --env=prod

# Set permissions
chmod -R 755 var/
chmod -R 755 images/
```

### 5. Verification Tests
```bash
# Test email functionality with a real user ID
php bin/console debug:test-email [USER_ID]

# List users to get valid IDs
php bin/console app:user:admin --list

# Test image upload/serving
# Verify user registration flow
# Test email verification links
```

## Post-Deployment Verification

### 1. Functional Tests
- [ ] User registration with email verification
- [ ] Login/logout functionality
- [ ] Image upload and viewing
- [ ] Album creation and management
- [ ] Image sharing functionality
- [ ] Email delivery and verification links

### 2. Performance Tests
- [ ] Page load times
- [ ] Image serving performance
- [ ] Database query performance
- [ ] Email delivery speed

### 3. Security Tests
- [ ] HTTPS enforcement
- [ ] File upload security
- [ ] Authentication/authorization
- [ ] CSRF protection
- [ ] SQL injection protection

## Rollback Plan

### Quick Rollback
```bash
# Revert to previous code version
git checkout previous-stable-tag

# Restore database backup (if needed)
# Restore previous configuration files
```

### Database Rollback
```bash
# If migrations need to be reverted
php bin/console doctrine:migrations:migrate prev --no-interaction
```

## Environment-Specific Notes

### Development → Staging
- Use staging database and email service
- Test with staging domain
- Verify all integrations work

### Staging → Production
- Update all URLs and domains
- Switch to production email service
- Enable production monitoring
- Update DNS records if needed

## Troubleshooting

### Common Issues

#### Email Verification Not Working
1. Check MAILER_DSN configuration
2. Verify FRONTEND_URL environment variable is set correctly
3. Confirm routing.yaml uses %env(FRONTEND_URL)% (not hardcoded)
4. Test email delivery with debug command: `php bin/console debug:test-email [USER_ID]`
5. Check email template rendering in Mailpit or email service logs
6. Verify the generated URLs point to your correct domain

#### Image Upload/Serving Issues
1. Verify directory permissions
2. Check file size limits
3. Verify secure image serving endpoint
4. Check storage directory configuration

#### Frontend Not Loading
1. Verify frontend build completed successfully
2. Check API base URL configuration
3. Verify web server configuration
4. Check for JavaScript errors in browser console

### Debug Commands
```bash
# Test email functionality
php bin/console app:test-email user@example.com

# Check user status
php bin/console app:user-admin list

# Clear all caches
php bin/console cache:clear --env=prod
```

## Maintenance

### Regular Tasks
- Monitor log files for errors
- Check email delivery success rates
- Monitor disk space usage
- Update dependencies regularly
- Backup database regularly

### Security Updates
- Keep Symfony and dependencies updated
- Monitor security advisories
- Update SSL certificates before expiration
- Review and rotate secrets regularly