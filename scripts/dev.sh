#!/bin/bash
# Development script for running all services concurrently

npx concurrently \
  -c "#93c5fd,#c4b5fd,#fb7185,#fdba74" \
  --names "server,queue,logs,vite" \
  --kill-others \
  "php artisan serve" \
  "php artisan queue:listen --tries=1" \
  "php -r \"if (function_exists('pcntl_fork')) { passthru('php artisan pail --timeout=0'); } else { fwrite(STDOUT, 'Pail skipped: pcntl extension not available' . PHP_EOL); while (true) { sleep(3600); } }\"" \
  "npm run dev"
