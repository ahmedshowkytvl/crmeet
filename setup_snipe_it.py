#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Snipe-IT Quick Setup Guide
Guide to quickly set up Snipe-IT for testing
"""

import subprocess
import requests
import time
import os


def check_docker():
    """Check if Docker is installed and running"""
    try:
        result = subprocess.run(['docker', '--version'], capture_output=True, text=True)
        if result.returncode == 0:
            print(f"✅ Docker is installed: {result.stdout.strip()}")
            return True
        else:
            print("❌ Docker is not installed")
            return False
    except FileNotFoundError:
        print("❌ Docker is not installed")
        return False


def check_docker_running():
    """Check if Docker daemon is running"""
    try:
        result = subprocess.run(['docker', 'ps'], capture_output=True, text=True)
        if result.returncode == 0:
            print("✅ Docker daemon is running")
            return True
        else:
            print("❌ Docker daemon is not running")
            return False
    except Exception as e:
        print(f"❌ Error checking Docker: {e}")
        return False


def setup_snipe_it_docker():
    """Set up Snipe-IT using Docker"""
    print("🐳 Setting up Snipe-IT with Docker...")
    
    # Create docker-compose.yml
    docker_compose_content = """version: '3.8'

services:
  snipeit:
    image: snipeit/snipe-it:latest
    container_name: snipe-it
    ports:
      - "8080:80"
    environment:
      - DB_CONNECTION=mysql
      - DB_HOST=mysql
      - DB_DATABASE=snipeit
      - DB_USERNAME=snipeit
      - DB_PASSWORD=snipeit123
      - APP_KEY=base64:YourAppKeyHere123456789012345678901234567890=
      - APP_URL=http://localhost:8080
    depends_on:
      - mysql
    volumes:
      - snipeit_data:/var/www/html/storage/app
      - snipeit_public:/var/www/html/public/uploads

  mysql:
    image: mysql:8.0
    container_name: snipe-it-mysql
    environment:
      - MYSQL_ROOT_PASSWORD=root123
      - MYSQL_DATABASE=snipeit
      - MYSQL_USER=snipeit
      - MYSQL_PASSWORD=snipeit123
    volumes:
      - mysql_data:/var/lib/mysql

volumes:
  snipeit_data:
  snipeit_public:
  mysql_data:
"""
    
    with open('docker-compose.yml', 'w') as f:
        f.write(docker_compose_content)
    
    print("✅ Created docker-compose.yml")
    
    # Start containers
    print("🚀 Starting Snipe-IT containers...")
    try:
        result = subprocess.run(['docker-compose', 'up', '-d'], capture_output=True, text=True)
        if result.returncode == 0:
            print("✅ Containers started successfully")
            return True
        else:
            print(f"❌ Failed to start containers: {result.stderr}")
            return False
    except FileNotFoundError:
        print("❌ docker-compose not found. Trying with docker run...")
        return setup_snipe_it_docker_run()


def setup_snipe_it_docker_run():
    """Set up Snipe-IT using docker run commands"""
    print("🐳 Setting up Snipe-IT with docker run...")
    
    # Start MySQL container
    print("📦 Starting MySQL container...")
    mysql_cmd = [
        'docker', 'run', '-d',
        '--name', 'snipe-it-mysql',
        '-e', 'MYSQL_ROOT_PASSWORD=root123',
        '-e', 'MYSQL_DATABASE=snipeit',
        '-e', 'MYSQL_USER=snipeit',
        '-e', 'MYSQL_PASSWORD=snipeit123',
        '-p', '3306:3306',
        'mysql:8.0'
    ]
    
    try:
        result = subprocess.run(mysql_cmd, capture_output=True, text=True)
        if result.returncode == 0:
            print("✅ MySQL container started")
        else:
            print(f"❌ Failed to start MySQL: {result.stderr}")
            return False
    except Exception as e:
        print(f"❌ Error starting MySQL: {e}")
        return False
    
    # Wait for MySQL to be ready
    print("⏳ Waiting for MySQL to be ready...")
    time.sleep(10)
    
    # Start Snipe-IT container
    print("📦 Starting Snipe-IT container...")
    snipe_cmd = [
        'docker', 'run', '-d',
        '--name', 'snipe-it',
        '--link', 'snipe-it-mysql:mysql',
        '-e', 'DB_CONNECTION=mysql',
        '-e', 'DB_HOST=mysql',
        '-e', 'DB_DATABASE=snipeit',
        '-e', 'DB_USERNAME=snipeit',
        '-e', 'DB_PASSWORD=snipeit123',
        '-e', 'APP_KEY=base64:YourAppKeyHere123456789012345678901234567890=',
        '-e', 'APP_URL=http://localhost:8080',
        '-p', '8080:80',
        'snipeit/snipe-it:latest'
    ]
    
    try:
        result = subprocess.run(snipe_cmd, capture_output=True, text=True)
        if result.returncode == 0:
            print("✅ Snipe-IT container started")
            return True
        else:
            print(f"❌ Failed to start Snipe-IT: {result.stderr}")
            return False
    except Exception as e:
        print(f"❌ Error starting Snipe-IT: {e}")
        return False


def wait_for_snipe_it():
    """Wait for Snipe-IT to be ready"""
    print("⏳ Waiting for Snipe-IT to be ready...")
    
    max_attempts = 30
    for attempt in range(max_attempts):
        try:
            response = requests.get("http://localhost:8080", timeout=5)
            if response.status_code == 200:
                print("✅ Snipe-IT is ready!")
                return True
        except:
            pass
        
        print(f"   Attempt {attempt + 1}/{max_attempts}...")
        time.sleep(10)
    
    print("❌ Snipe-IT did not become ready in time")
    return False


def create_api_token():
    """Instructions for creating API token"""
    print("\n🔑 Creating API Token")
    print("="*50)
    print("1. Open your browser and go to: http://localhost:8080")
    print("2. Login with default credentials:")
    print("   Username: admin")
    print("   Password: password")
    print("3. Go to Admin > API Tokens")
    print("4. Click 'Create Token'")
    print("5. Give it a name (e.g., 'Python API')")
    print("6. Copy the generated token")
    print("7. Update your Python scripts with the new token")


def test_setup():
    """Test the Snipe-IT setup"""
    print("\n🧪 Testing Snipe-IT Setup")
    print("="*50)
    
    # Test basic connectivity
    try:
        response = requests.get("http://localhost:8080", timeout=10)
        if response.status_code == 200:
            print("✅ Snipe-IT is accessible")
            
            # Check if it's the setup page
            if 'setup' in response.text.lower():
                print("ℹ️  Snipe-IT setup page detected")
                print("   Complete the setup in your browser")
            else:
                print("✅ Snipe-IT appears to be fully configured")
                
        else:
            print(f"❌ Snipe-IT returned status code: {response.status_code}")
            
    except Exception as e:
        print(f"❌ Error testing Snipe-IT: {e}")


def main():
    """Main setup function"""
    print("🚀 Snipe-IT Quick Setup")
    print("="*50)
    
    # Check Docker
    if not check_docker():
        print("\n📋 Docker Installation Instructions:")
        print("1. Visit: https://docs.docker.com/get-docker/")
        print("2. Download and install Docker Desktop")
        print("3. Start Docker Desktop")
        print("4. Run this script again")
        return
    
    if not check_docker_running():
        print("\n📋 Docker Startup Instructions:")
        print("1. Start Docker Desktop")
        print("2. Wait for it to be ready")
        print("3. Run this script again")
        return
    
    # Setup Snipe-IT
    if setup_snipe_it_docker():
        print("✅ Snipe-IT setup initiated")
        
        # Wait for it to be ready
        if wait_for_snipe_it():
            test_setup()
            create_api_token()
            
            print("\n🎉 Setup Complete!")
            print("="*50)
            print("Snipe-IT is now running at: http://localhost:8080")
            print("Default login: admin / password")
            print("Create an API token and update your Python scripts")
            
        else:
            print("❌ Setup failed - Snipe-IT did not start properly")
    else:
        print("❌ Setup failed - Could not start containers")


if __name__ == "__main__":
    main()

