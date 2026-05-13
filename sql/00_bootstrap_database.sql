-- Run this FIRST in phpMyAdmin (Import), or run before schema.sql from the command line.
-- Then import schema.sql while ourmarketplace is selected (or import schema right after — USE applies to the session).

CREATE DATABASE IF NOT EXISTS ourmarketplace
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ourmarketplace;
