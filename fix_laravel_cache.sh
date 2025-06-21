#!/bin/bash

# Laravel Cache and Autoloader Fix Script
# Run this script to clear all Laravel caches and regenerate autoloader

echo "🔄 Clearing Laravel caches and regenerating autoloader..."

# Clear all Laravel caches
echo "📝 Clearing config cache..."
php artisan config:clear

echo "📝 Clearing route cache..."
php artisan route:clear

echo "📝 Clearing view cache..."
php artisan view:clear

echo "📝 Clearing application cache..."
php artisan cache:clear

echo "📝 Regenerating optimized autoloader..."
composer dump-autoload

echo "📝 Caching config for performance..."
php artisan config:cache

echo "✅ All caches cleared and autoloader regenerated!"
echo "🚀 Try accessing your application now."
