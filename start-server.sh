#!/bin/bash

# Laravel Development Server Startup Script
# This script starts the Laravel server accessible from WAN (0.0.0.0)

cd "$(dirname "$0")"

echo "Starting Laravel development server..."
echo "Server will be accessible at: http://0.0.0.0:8000"
echo "Press Ctrl+C to stop the server"
echo ""

php artisan serve --host=0.0.0.0 --port=8000

