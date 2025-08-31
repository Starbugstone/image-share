# ImageShare Production Deployment Checklist

## Pre-Deployment Configuration

### ✅ Environment Files
- [ ] Copy `.env.production.example` to `.env`
- [ ] Copy `frontend/.env.production.example` to `frontend/.env.production`
- [ ] Update `FRONTEND_URL` in `.env` (CRITICAL for email verification)
- [ ] Update `DATABASE_URL` with production database credentials
- [ ] Update `MAILER_DSN` with production email service
- [ ] Generate secure `APP_SECRET` (32+ characters)
- [ ] Update `VITE_API_BASE_URL` in frontend environment

### ✅ Security Configuration
- [ ] Set `APP_ENV=prod` and `APP_DEBUG=false`
- [ ] Enable `SESSION_COOKIE_SECURE=true` for HTTPS
- [ ] Configure `TRUSTED_PROXIES` if using load balancer
- [ ] Verify SSL certificate is installed and working

### ✅ Database Setup
- [ ] Create production database
- [ ] Run migrations: `php bin/console doctrine:migrations:migrate`
- [ ] Setup directories: `php bin/console app:setup-profile-images`
- [ ] Make usernames unique: `php bin/console app:make-usernames-unique`
- [ ] Create admin user: `php bin/console app:user:admin create admin@yourdomain.com`

### ✅ File Permissions
- [ ] Ensure `images/` directory exists and is writable
- [ ] Ensure `var/cache/` and `var/log/` are writable
- [ ] Verify `public/` directory permissions
- [ ] Check that `images/` is outside web root for security

## Deployment Process

### ✅ Code Deployment
- [ ] Pull latest stable code from repository
- [ ] Install PHP dependencies: `composer install --no-dev --optimize-autoloader`
- [ ] Install frontend dependencies: `cd frontend && npm install`
- [ ] Build frontend: `npm run build`
- [ ] Clear Symfony cache: `php bin/console cache:clear --env=prod`

### ✅ Configuration Verification
- [ ] Verify environment variables: `php bin/console debug:container --env-vars`
- [ ] Check frontend URL parameter: `php bin/console debug:container --parameter=app.frontend_url`
- [ ] Test database connection
- [ ] Verify email configuration

## Post-Deployment Testing

### ✅ Critical Functionality Tests
- [ ] User registration works
- [ ] Email verification emails are sent
- [ ] Email verification links work and point to correct domain
- [ ] Login/logout functionality
- [ ] Image upload and viewing
- [ ] Album creation and management
- [ ] Image sharing functionality

### ✅ Email Verification Testing
- [ ] Test email sending: `php bin/console debug:test-email [USER_ID]`
- [ ] Verify email contains correct domain URLs
- [ ] Test clicking verification link
- [ ] Confirm user status changes to verified

### ✅ Performance & Security
- [ ] Page load times are acceptable
- [ ] HTTPS is enforced
- [ ] Images are served securely (not directly accessible)
- [ ] File upload security is working
- [ ] CSRF protection is active

## Rollback Plan

### ✅ Rollback Preparation
- [ ] Database backup created before deployment
- [ ] Previous code version tagged in git
- [ ] Configuration files backed up
- [ ] Rollback procedure documented and tested

## Monitoring Setup

### ✅ Post-Deployment Monitoring
- [ ] Application logs are being written
- [ ] Email delivery monitoring is active
- [ ] Database performance monitoring
- [ ] Disk space monitoring for image uploads
- [ ] SSL certificate expiration monitoring

## Environment-Specific Notes

### Development → Staging
- [ ] Use staging database and email service
- [ ] Test with staging domain
- [ ] Verify all integrations work with staging services

### Staging → Production
- [ ] Update all URLs to production domain
- [ ] Switch to production email service
- [ ] Enable production monitoring and alerting
- [ ] Update DNS records if needed
- [ ] Verify CDN configuration (if applicable)

## Common Issues & Solutions

### Email Verification URLs Wrong Domain
**Problem**: Verification emails contain localhost or wrong domain
**Solution**: 
1. Check `FRONTEND_URL` in `.env`
2. Verify `config/packages/routing.yaml` uses `%env(FRONTEND_URL)%`
3. Clear cache: `php bin/console cache:clear --env=prod`

### Images Not Loading
**Problem**: Images return 404 or permission denied
**Solution**:
1. Check `images/` directory permissions
2. Verify `ImageServeController` is working
3. Test with: `/secure-image/{image_id}`

### Frontend Not Loading
**Problem**: Vue.js app shows blank page or errors
**Solution**:
1. Check `VITE_API_BASE_URL` in frontend environment
2. Verify frontend build completed: `npm run build`
3. Check browser console for JavaScript errors
4. Verify web server serves static files correctly

## Final Verification

- [ ] All checklist items completed
- [ ] Test user can register and verify email
- [ ] Test user can upload and share images
- [ ] All URLs point to production domain
- [ ] Email delivery is working
- [ ] Performance is acceptable
- [ ] Security measures are active
- [ ] Monitoring is in place
- [ ] Rollback plan is ready