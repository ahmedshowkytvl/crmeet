#!/bin/bash

# Test Runner Script for Zoho Sync Service

echo "🧪 Running Zoho Sync Service Tests..."

# Check if .env.test file exists
if [ ! -f .env.test ]; then
    echo "❌ .env.test file not found. Creating from template..."
    cp .env.example .env.test
fi

# Set test environment
export NODE_ENV=test

# Check if node_modules exists
if [ ! -d "node_modules" ]; then
    echo "📦 Installing dependencies..."
    npm install
fi

# Create logs directory
mkdir -p logs

# Run tests
echo "🎯 Running tests..."
npm test

# Check test results
if [ $? -eq 0 ]; then
    echo "✅ All tests passed!"
else
    echo "❌ Some tests failed!"
    exit 1
fi







