#!/bin/bash
# Simple development script

echo "Starting Laravel Development Environment..."
echo ""

# Start services in background
php artisan serve &
PHP_SERVE_PID=$!

php artisan queue:listen --tries=1 &
QUEUE_PID=$!

npm run dev &
VITE_PID=$!

echo "All services started!"
echo "Laravel Server PID: $PHP_SERVE_PID"
echo "Queue Worker PID: $QUEUE_PID"
echo "Vite Dev Server PID: $VITE_PID"
echo ""
echo "Press Ctrl+C to stop all services..."

# Wait for user interrupt
trap "kill $PHP_SERVE_PID $QUEUE_PID $VITE_PID; exit" INT TERM
wait
