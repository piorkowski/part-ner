-- MySQL initialization script for Part-ner.pl

-- Set charset
SET NAMES utf8mb4;
SET CHARACTER_SET_CLIENT = utf8mb4;

-- Grant all privileges to sulu user
GRANT ALL PRIVILEGES ON sulu.* TO 'sulu'@'%';
FLUSH PRIVILEGES;

-- Create default database settings
USE sulu;

-- Set default charset for database
ALTER DATABASE sulu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
