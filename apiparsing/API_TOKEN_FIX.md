# 🔧 Fix Access Token Issues

## 🚨 Problem
You're getting "Failed to get access token" errors when opening ticket details.

## 🔍 Root Cause
The Zoho API access token has expired. This is normal behavior - access tokens are temporary and need to be refreshed regularly.

## 🛠️ Solution Steps

### Step 1: Generate New Authorization URL
```bash
python generate_auth_url.py
```

### Step 2: Get New Authorization Code
1. Copy the generated URL
2. Open it in your browser
3. Login to your Zoho account
4. Grant permissions to the application
5. You'll be redirected to Google with a `code` parameter
6. Copy the code value from the URL (looks like: `1000.abc123...`)

### Step 3: Update Config File
1. Open `config.py`
2. Replace the `AUTHORIZATION_CODE` with the new code you copied
3. Save the file

### Step 4: Refresh Tokens
```bash
python refresh_token.py
```

This will:
- Use the new authorization code to get fresh tokens
- Automatically update your `config.py` with the new refresh token

### Step 5: Test Connection
```bash
python test_api_connection.py
```

### Step 6: Restart Web Application
```bash
python launch_ticket_viewer.py
```

## 🎭 Alternative: Use Demo Version

If you want to test the interface without fixing the API:

```bash
python launch_demo.py
```

This runs a demo version with mock data on port 5001.

## 📋 Complete Fix Script

I've created several helper scripts:

1. **`generate_auth_url.py`** - Creates the authorization URL
2. **`refresh_token.py`** - Updates tokens using authorization code
3. **`test_api_connection.py`** - Tests if everything is working
4. **`debug_token_refresh.py`** - Debugs token refresh issues
5. **`launch_demo.py`** - Runs demo version with mock data

## 🔄 Automatic Token Refresh

The web application now includes:
- **Retry logic** (3 attempts) for getting access tokens
- **Better error messages** that guide users to fix the issue
- **Graceful fallback** when tokens fail

## 🚀 Quick Start After Fix

Once tokens are refreshed:
1. Run: `python launch_ticket_viewer.py`
2. Open browser to: `http://localhost:5000`
3. Click on any ticket card to see details
4. Processing time calculation will work perfectly!

## 🆘 Still Having Issues?

If you continue having problems:

1. **Check your Zoho account permissions**
2. **Verify your CLIENT_ID and CLIENT_SECRET** in config.py
3. **Ensure your Zoho Desk API is enabled**
4. **Try the demo version** to test the interface: `python launch_demo.py`

The demo version works on port 5001 and shows all features with mock data.
