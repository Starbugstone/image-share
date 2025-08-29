# User Profile System

## Overview
The ImageShare application now includes a comprehensive user profile system that allows users to create detailed profiles, upload profile images, set online/offline status, and manage their public visibility.

## Features

### 1. Profile Management
- **Display Name**: Custom display name separate from username
- **Bio**: Personal description (up to 1000 characters)
- **Location**: City, country, or any location information
- **Website**: Personal website URL
- **Profile Image**: Upload and manage profile pictures
- **Privacy Settings**: Control profile visibility

### 2. Online Status System
- **Status Types**:
  - Online: Active and available
  - Away: Temporarily unavailable
  - Do Not Disturb: Busy, please don't interrupt
  - Invisible: Hidden from other users
  - Offline: Not currently active

- **Automatic Status Updates**:
  - Status automatically updates based on user activity
  - "Last seen" tracking with relative time display
  - 5-minute timeout for online status

### 3. Profile Images
- **Supported Formats**: JPEG, PNG, GIF, WebP
- **File Size Limit**: 2MB maximum
- **Automatic Cleanup**: Old images are removed when new ones are uploaded
- **Responsive Design**: Images are displayed in various sizes throughout the interface

## Technical Implementation

### Entities

#### UserProfile
```php
class UserProfile
{
    private User $user;                    // OneToOne relationship with User
    private ?string $displayName;          // Custom display name
    private ?string $bio;                  // Personal bio
    private ?string $profileImageName;     // Stored image filename
    private ?int $profileImageSize;        // File size in bytes
    private ?DateTimeImmutable $profileImageUpdatedAt;
    private string $status;                // Current status
    private ?DateTimeImmutable $lastSeen;  // Last activity timestamp
    private ?string $location;             // User location
    private ?string $website;              // Personal website
    private array $socialLinks;            // JSON array of social media links
    private bool $isPublic;                // Profile visibility
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;
}
```

#### User Entity Updates
- Added `profile` property with OneToOne relationship
- Profile is automatically created when User is instantiated
- Cascade operations for profile management

### Controllers

#### ProfileController
- **`/profile/me`**: View own profile
- **`/profile/me/edit`**: Edit own profile
- **`/profile/{username}`**: View another user's profile
- **`/profile/status/online`**: Set status to online
- **`/profile/status/offline`**: Set status to offline
- **`/profile/online`**: View online users

#### UserController
- **`/user/search`**: Search users for sharing
- **`/user/available`**: Get available users for sharing
- **`/user/profile/{username}`**: Redirects to new profile system

### Forms

#### ProfileType
- Comprehensive form for profile editing
- File upload handling for profile images
- Validation constraints for all fields
- Bootstrap styling integration

### Repositories

#### UserProfileRepository
- Status-based queries
- Online user detection
- Bulk status updates
- Public profile filtering

### Twig Extensions

#### TimeExtension
- `ago` filter for relative time display
- Human-readable time differences
- Supports years, months, days, hours, minutes

## Database Schema

### UserProfile Table
```sql
CREATE TABLE user_profile (
    user_id INT NOT NULL,
    display_name VARCHAR(255) DEFAULT NULL,
    bio LONGTEXT DEFAULT NULL,
    profile_image_name VARCHAR(255) DEFAULT NULL,
    profile_image_size INT DEFAULT NULL,
    profile_image_updated_at DATETIME DEFAULT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'offline',
    last_seen DATETIME DEFAULT NULL,
    location VARCHAR(100) DEFAULT NULL,
    website VARCHAR(255) DEFAULT NULL,
    social_links JSON DEFAULT '[]',
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY(user_id),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);
```

### Indexes
- `display_name`: For user search functionality
- `status`: For status-based queries
- `created_at`: For chronological sorting

## Installation & Setup

### 1. Run Migrations
```bash
# Apply the new migration
php bin/console doctrine:migrations:migrate

# Or manually run the migration
php bin/console app:make-usernames-unique
```

### 2. Create Profile Images Directory
```bash
# Create the uploads directory
php bin/console app:setup-profile-images
```

### 3. Clear Cache
```bash
php bin/console cache:clear
```

### 4. Verify Configuration
Check that the following parameters are set in `config/services.yaml`:
```yaml
parameters:
    profile_images_directory: '%kernel.project_dir%/public/uploads/profile_images'
```

## Usage Examples

### Viewing Profiles
```twig
{# View own profile #}
<a href="{{ path('app_profile_me') }}">My Profile</a>

{# View another user's profile #}
<a href="{{ path('app_profile_view', {username: 'john_doe'}) }}">John's Profile</a>
```

### Profile Status Updates
```javascript
// Set status to online
fetch('/profile/status/online', {method: 'POST'})
    .then(response => response.json())
    .then(data => console.log('Status updated:', data.status));

// Set status to offline
fetch('/profile/status/offline', {method: 'POST'})
    .then(response => response.json())
    .then(data => console.log('Status updated:', data.status));
```

### Displaying User Status
```twig
{# Show status with color coding #}
<span class="badge bg-{{ user.profile.getStatusColor() }}">
    {{ user.profile.getStatusDisplay()|title }}
</span>

{# Show last seen time #}
<small class="text-muted">
    Last seen: {{ user.profile.lastSeen|ago }}
</small>
```

## Security Features

### Access Control
- Profile editing restricted to profile owner
- Public/private profile visibility control
- Unverified users cannot have public profiles
- Profile viewing requires authentication

### File Upload Security
- File type validation (images only)
- File size limits (2MB max)
- Secure filename generation
- Automatic cleanup of old files

### Data Validation
- Input sanitization and validation
- XSS protection through Twig escaping
- SQL injection protection via Doctrine ORM

## Performance Considerations

### Database Optimization
- Indexed fields for common queries
- Efficient status-based queries
- Lazy loading of profile relationships

### Image Handling
- Automatic image cleanup
- Efficient file storage
- Responsive image sizing

### Caching Strategy
- Profile data caching
- Status update optimization
- Query result caching

## Future Enhancements

### Planned Features
1. **Social Media Integration**
   - Direct social media profile linking
   - Social media feed integration
   - Cross-platform sharing

2. **Advanced Privacy Controls**
   - Granular visibility settings
   - Friend-only profiles
   - Temporary profile hiding

3. **Profile Analytics**
   - Profile view tracking
   - Popularity metrics
   - Activity insights

4. **Profile Templates**
   - Pre-designed profile layouts
   - Customizable themes
   - Professional vs. casual styles

### Technical Improvements
1. **Real-time Status Updates**
   - WebSocket integration
   - Live status broadcasting
   - Push notifications

2. **Advanced Image Processing**
   - Automatic image optimization
   - Multiple image sizes
   - Image cropping tools

3. **Profile Backup/Restore**
   - Profile data export
   - Import functionality
   - Version history

## Troubleshooting

### Common Issues

#### Profile Not Loading
- Check database migration status
- Verify UserProfile entity creation
- Check for missing profile relationships

#### Image Upload Failures
- Verify upload directory permissions
- Check file size limits
- Validate file format support

#### Status Not Updating
- Check JavaScript console for errors
- Verify route accessibility
- Check database constraints

### Debug Commands
```bash
# Check database schema
php bin/console doctrine:schema:validate

# Verify profile entities
php bin/console doctrine:query:sql "SELECT * FROM user_profile LIMIT 5"

# Check routes
php bin/console debug:router | grep profile

# Clear cache
php bin/console cache:clear
```

## API Endpoints

### Profile Management
- `GET /profile/me` - Get own profile
- `GET /profile/{username}` - Get user profile
- `GET /profile/me/edit` - Edit profile form
- `POST /profile/me/edit` - Update profile

### Status Management
- `POST /profile/status/online` - Set online status
- `POST /profile/status/offline` - Set offline status

### User Discovery
- `GET /user/search?q={query}` - Search users
- `GET /user/available` - Get available users
- `GET /profile/online` - Get online users

## Conclusion

The User Profile System transforms ImageShare from a basic image sharing platform into a comprehensive social media application. Users can now create rich profiles, maintain online presence, and interact with each other in meaningful ways.

The system is designed with scalability in mind, following SOLID principles and modern web development best practices. The modular architecture makes it easy to add new features and maintain existing functionality.

For developers, the system provides clear APIs, comprehensive documentation, and extensible architecture. For users, it offers an intuitive interface for profile management and social interaction.
