# ImageShare - Product Overview

ImageShare is a secure, private image sharing platform that prioritizes user privacy and granular access control. The platform allows users to upload, organize, and share images with specific users while maintaining complete security.

## Core Features

- **Secure Image Storage**: Images stored outside public directory (`/images`) with permission-controlled access
- **Private Sharing**: Share images/albums with specific verified users only
- **Album Organization**: Create public or private albums to organize content
- **Private Comments**: Contextual discussions visible only to owner and shared users
- **User Profiles**: Comprehensive profile system with status, bio, profile images, and privacy controls
- **User Management**: Email verification, role-based access, and unique username system
- **Admin Interface**: EasyAdmin bundle for administrative tasks

## Migration Status

The project is currently undergoing a **Twig to Vue.js migration**:
- **Backend**: Symfony 6.4 with RESTful APIs (complete)
- **Frontend**: Vue.js 3 with modern UI/UX (in progress)
- **Current Phase**: Core infrastructure and image management completed
- **Next Phase**: Advanced sharing features and user profiles

## Security-First Design

The platform's architecture ensures that no image can be accessed without explicit permission:
- All image requests go through `/secure-image/{id}` endpoint
- Permission verification before serving content
- No direct file access to storage directory
- CSRF protection on all forms
- Secure file upload with validation

## User Profile System

- **Rich Profiles**: Display name, bio, location, website, social links
- **Profile Images**: Upload and manage profile pictures (2MB limit)
- **Online Status**: Real-time status tracking (Online, Away, Do Not Disturb, Invisible, Offline)
- **Privacy Controls**: Public/private profile visibility
- **Last Seen**: Activity tracking with relative time display

## Target Users

- Individuals who need secure image sharing
- Families sharing private photos
- Professionals requiring controlled access to visual content
- Anyone prioritizing privacy over public social media sharing