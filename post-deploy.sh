#!/bin/bash
# === post-deploy.sh ===
# Run this after every git pull to fix permissions

echo "🔧 Setting upload folder permissions..."
sudo mkdir -p uploads
sudo chown -R www-data:www-data uploads
sudo chmod -R 755 uploads

echo "✅ Upload folder permissions fixed!"
sudo chmod +x post-deploy.sh
git pull origin main
./post-deploy.sh
