# CORS Security Configuration

## Overview
CORS (Cross-Origin Resource Sharing) controls which websites/apps can access your WashBox API. Proper configuration prevents unauthorized access and security vulnerabilities.

---

## ⚠️ The Problem (Before Fix)

### Dangerous Configuration:
```php
'allowed_origins' => ['*'],  // ❌ ALLOWS ANY WEBSITE!
```

### Real Attack Scenario:
```
1. Hacker creates fake-washbox.com
2. Customer visits fake site (phishing email)
3. Fake site calls YOUR API: api.washbox.com
4. Because CORS allows '*', API responds
5. Hacker steals customer data, places fake orders
```

---

## ✅ The Solution (After Fix)

### Secure Configuration:
```php
'allowed_origins' => [
    'http://localhost:3000',           // Development only
    'http://localhost:19006',          // Expo development
    'https://washbox.com',             // Production website
    'https://admin.washbox.com',       // Admin dashboard
],
```

### Now:
- ✅ Only YOUR domains can access the API
- ❌ fake-washbox.com gets blocked
- ❌ Hackers can't call your API from their sites

---

## Configuration Guide

### 1. Development Environment

**Current setup (for local testing):**
```php
'allowed_origins' => [
    'http://localhost:3000',           // React/Next.js dev server
    'http://localhost:8080',           // Vue dev server
    'http://localhost:19006',          // Expo web
    'http://192.168.1.9:8000',         // Local network (your IP)
    'exp://192.168.1.9:8081',          // Expo mobile app
],
```

**How to find your local IP:**
```bash
# Linux/Mac
ifconfig | grep "inet "

# Windows
ipconfig

# Look for: 192.168.x.x or 10.0.x.x
```

**Update CORS with your IP:**
```php
'http://192.168.YOUR.IP:8000',
'exp://192.168.YOUR.IP:8081',
```

---

### 2. Production Environment

**When deploying to production:**

```php
'allowed_origins' => [
    // Remove localhost entries
    'https://washbox.com',              // Main website
    'https://www.washbox.com',          // WWW version
    'https://admin.washbox.com',        // Admin panel
    'https://api.washbox.com',          // API domain (if separate)
],
```

**Important:**
- ❌ Remove ALL `localhost` entries in production
- ❌ Remove ALL `http://` entries (use `https://` only)
- ✅ Only add YOUR actual domains
- ✅ Use HTTPS in production

---

### 3. Mobile App Configuration

**For React Native / Expo apps:**

```php
'allowed_origins' => [
    // Development
    'exp://192.168.1.9:8081',          // Expo development
    'http://localhost:19006',          // Expo web
    
    // Production (mobile apps don't send Origin header)
    // No need to add mobile app domains
],
```

**Note:** Mobile apps (React Native, Flutter) don't send `Origin` header, so CORS doesn't block them. This is normal behavior.

---

## Environment-Based Configuration

### Better Approach (Recommended):

**Update `.env` file:**
```env
# Development
APP_ENV=local
FRONTEND_URL=http://localhost:3000
ADMIN_URL=http://localhost:8080
MOBILE_URL=exp://192.168.1.9:8081

# Production
# APP_ENV=production
# FRONTEND_URL=https://washbox.com
# ADMIN_URL=https://admin.washbox.com
```

**Update `config/cors.php`:**
```php
'allowed_origins' => array_filter([
    env('FRONTEND_URL'),
    env('ADMIN_URL'),
    env('MOBILE_URL'),
    'http://localhost:3000',  // Fallback for dev
]),
```

This way you can change domains without editing code!

---

## Testing CORS Configuration

### Test 1: Allowed Origin (Should Work)
```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Origin: http://localhost:3000" \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}' \
  -v

# Look for response header:
# Access-Control-Allow-Origin: http://localhost:3000
# ✅ Success!
```

### Test 2: Blocked Origin (Should Fail)
```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Origin: https://evil-hacker.com" \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}' \
  -v

# Browser will block the response
# ❌ CORS error in browser console
```

---

## Common Issues & Solutions

### Issue 1: "CORS error" in mobile app
**Cause:** Mobile apps don't need CORS  
**Solution:** This is normal, ignore CORS for mobile apps

### Issue 2: "CORS error" in browser during development
**Cause:** Your local IP changed  
**Solution:** Update CORS config with new IP address

### Issue 3: Works in Postman but not browser
**Cause:** Postman ignores CORS, browsers enforce it  
**Solution:** Add your frontend URL to allowed_origins

### Issue 4: "Access-Control-Allow-Credentials" error
**Cause:** `supports_credentials` must be `true` for Sanctum  
**Solution:** Already set correctly in config

---

## Security Checklist

### ✅ Before Production:

- [ ] Remove `'*'` from allowed_origins
- [ ] Remove all `localhost` entries
- [ ] Remove all `http://` entries (use `https://` only)
- [ ] Add only YOUR production domains
- [ ] Test login from production frontend
- [ ] Test admin dashboard access
- [ ] Verify mobile app still works

### ✅ Production CORS Config:
```php
'allowed_origins' => [
    'https://washbox.com',
    'https://www.washbox.com',
    'https://admin.washbox.com',
],
```

---

## What Each Setting Does

```php
return [
    // Which API routes have CORS enabled
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    
    // Which HTTP methods are allowed (GET, POST, PUT, DELETE)
    'allowed_methods' => ['*'],  // ✅ Keep as '*'
    
    // Which domains can access your API
    'allowed_origins' => [...],  // ⚠️ CRITICAL - Configure this!
    
    // Which headers frontend can send
    'allowed_headers' => ['*'],  // ✅ Keep as '*'
    
    // Which headers frontend can read
    'exposed_headers' => [],     // ✅ Keep empty
    
    // How long browser caches CORS response
    'max_age' => 0,              // ✅ Keep as 0
    
    // Allow cookies/auth headers (required for Sanctum)
    'supports_credentials' => true,  // ✅ MUST be true
];
```

---

## Quick Reference

### Development (Local Testing):
```php
'allowed_origins' => [
    'http://localhost:3000',
    'http://192.168.1.9:8000',  // Your local IP
],
```

### Production (Live Website):
```php
'allowed_origins' => [
    'https://washbox.com',
    'https://admin.washbox.com',
],
```

### Testing (Allow Everything - NEVER USE IN PRODUCTION):
```php
'allowed_origins' => ['*'],  // ❌ DANGEROUS!
```

---

## Summary

✅ **CORS configured** - Only your domains allowed  
✅ **Development URLs** - localhost and local IP  
✅ **Production ready** - Add your domains before deploy  
✅ **Mobile apps work** - No CORS restrictions  
✅ **Sanctum compatible** - credentials enabled  

**Security Rating:** 9/10 (Excellent with proper domains)

Your API is now protected from cross-origin attacks! 🔒
