-- Initial database setup for ImageShare
CREATE DATABASE IF NOT EXISTS imageshare_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON imageshare_db.* TO 'imageshare_user'@'%';
FLUSH PRIVILEGES;
