# 📡 WashBox API Documentation

Base URL: `https://yourdomain.com/api`

## Authentication

All authenticated endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

### Register Customer
```http
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "09123456789",
  "password": "password123",
  "password_confirmation": "password123",
  "fcm_token": "firebase_token_here" // Optional
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "customer": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "09123456789"
    },
    "token": "1|abc123..."
  }
}
```

### Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123",
  "fcm_token": "firebase_token_here" // Optional
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "customer": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "token": "2|xyz789..."
  }
}
```

### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### Get Current User
```http
GET /api/user
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "09123456789",
    "created_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

---

## Laundry Orders

### Get All Orders
```http
GET /api/laundries
Authorization: Bearer {token}
```

**Query Parameters:**
- `status` - Filter by status (received, processing, ready, paid, completed, cancelled)
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15)

**Response (200):**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "tracking_number": "WB-2024-0001",
        "status": "ready",
        "total_amount": 250.00,
        "created_at": "2024-01-01T10:00:00.000000Z",
        "ready_at": "2024-01-02T15:00:00.000000Z",
        "branch": {
          "id": 1,
          "name": "WashBox Main Branch"
        }
      }
    ],
    "total": 10,
    "per_page": 15
  }
}
```

### Create Order
```http
POST /api/laundries
Authorization: Bearer {token}
Content-Type: application/json

{
  "branch_id": 1,
  "service_id": 1,
  "weight": 5.5,
  "pickup_address": "123 Main St",
  "pickup_latitude": "9.3068",
  "pickup_longitude": "123.3007",
  "delivery_address": "123 Main St",
  "delivery_latitude": "9.3068",
  "delivery_longitude": "123.3007",
  "preferred_pickup_date": "2024-01-15",
  "preferred_pickup_time": "10:00",
  "special_instructions": "Handle with care",
  "addons": [1, 2], // Array of addon IDs
  "promotion_code": "SAVE20" // Optional
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "id": 1,
    "tracking_number": "WB-2024-0001",
    "status": "received",
    "total_amount": 250.00,
    "estimated_completion": "2024-01-16T15:00:00.000000Z"
  }
}
```

### Get Order Details
```http
GET /api/laundries/{id}
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "tracking_number": "WB-2024-0001",
    "status": "ready",
    "weight": 5.5,
    "total_amount": 250.00,
    "service": {
      "id": 1,
      "name": "Wash & Fold",
      "price_per_kilo": 40.00
    },
    "addons": [
      {
        "id": 1,
        "name": "Fabric Softener",
        "price": 20.00
      }
    ],
    "branch": {
      "id": 1,
      "name": "WashBox Main Branch",
      "address": "123 Business St"
    },
    "status_history": [
      {
        "status": "received",
        "changed_at": "2024-01-01T10:00:00.000000Z"
      },
      {
        "status": "processing",
        "changed_at": "2024-01-01T11:00:00.000000Z"
      }
    ]
  }
}
```

### Cancel Order
```http
DELETE /api/laundries/{id}
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Order cancelled successfully"
}
```

---

## Pickup Requests

### Get All Pickup Requests
```http
GET /api/pickup-requests
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "status": "pending",
      "pickup_address": "123 Main St",
      "pickup_latitude": "9.3068",
      "pickup_longitude": "123.3007",
      "preferred_date": "2024-01-15",
      "preferred_time": "10:00",
      "created_at": "2024-01-01T10:00:00.000000Z"
    }
  ]
}
```

### Create Pickup Request
```http
POST /api/pickup-requests
Authorization: Bearer {token}
Content-Type: application/json

{
  "pickup_address": "123 Main St",
  "pickup_latitude": "9.3068",
  "pickup_longitude": "123.3007",
  "preferred_date": "2024-01-15",
  "preferred_time": "10:00",
  "notes": "Please call before arriving"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Pickup request created successfully",
  "data": {
    "id": 1,
    "status": "pending",
    "pickup_address": "123 Main St",
    "preferred_date": "2024-01-15",
    "preferred_time": "10:00"
  }
}
```

### Update Pickup Status
```http
PUT /api/pickup-requests/{id}/status
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "cancelled",
  "reason": "Changed my mind"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Pickup request updated successfully"
}
```

---

## Services & Pricing

### Get All Services
```http
GET /api/services
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Wash & Fold",
      "description": "Basic wash and fold service",
      "price_per_kilo": 40.00,
      "price_per_item": null,
      "pricing_type": "per_kilo",
      "category": "basic",
      "image_url": "https://..."
    }
  ]
}
```

### Get Service Details
```http
GET /api/services/{id}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Wash & Fold",
    "description": "Basic wash and fold service",
    "price_per_kilo": 40.00,
    "pricing_type": "per_kilo",
    "category": "basic",
    "estimated_duration": "24 hours",
    "available_addons": [
      {
        "id": 1,
        "name": "Fabric Softener",
        "price": 20.00
      }
    ]
  }
}
```

---

## Branches

### Get All Branches
```http
GET /api/branches
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "WashBox Main Branch",
      "address": "123 Business St, Dumaguete City",
      "phone": "09123456789",
      "latitude": "9.3068",
      "longitude": "123.3007",
      "operating_hours": {
        "monday": "8:00 AM - 6:00 PM",
        "tuesday": "8:00 AM - 6:00 PM"
      }
    }
  ]
}
```

---

## Notifications

### Get Notifications
```http
GET /api/notifications
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Order Ready",
      "message": "Your laundry WB-2024-0001 is ready for pickup",
      "type": "order_ready",
      "read_at": null,
      "created_at": "2024-01-01T10:00:00.000000Z"
    }
  ]
}
```

### Mark as Read
```http
PUT /api/notifications/{id}/read
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Notification marked as read"
}
```

### Update FCM Token
```http
POST /api/fcm-token
Authorization: Bearer {token}
Content-Type: application/json

{
  "fcm_token": "new_firebase_token_here"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "FCM token updated successfully"
}
```

---

## Promotions

### Get Active Promotions
```http
GET /api/promotions
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "code": "SAVE20",
      "description": "20% off on all services",
      "discount_type": "percentage",
      "discount_value": 20,
      "valid_from": "2024-01-01",
      "valid_until": "2024-01-31",
      "min_order_amount": 100.00
    }
  ]
}
```

### Validate Promotion Code
```http
POST /api/promotions/validate
Content-Type: application/json

{
  "code": "SAVE20",
  "order_amount": 250.00
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "valid": true,
    "discount_amount": 50.00,
    "final_amount": 200.00
  }
}
```

---

## Ratings

### Submit Rating
```http
POST /api/ratings
Authorization: Bearer {token}
Content-Type: application/json

{
  "laundry_id": 1,
  "rating": 5,
  "comment": "Excellent service!"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Rating submitted successfully"
}
```

---

## Error Responses

### 400 Bad Request
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Resource not found"
}
```

### 500 Server Error
```json
{
  "success": false,
  "message": "Internal server error"
}
```

---

## Rate Limiting

- **Default:** 60 requests per minute per IP
- **Authenticated:** 100 requests per minute per user

Rate limit headers:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1640000000
```

---

## Webhooks (Optional)

### Order Status Changed
```http
POST {your_webhook_url}
Content-Type: application/json

{
  "event": "order.status_changed",
  "data": {
    "order_id": 1,
    "tracking_number": "WB-2024-0001",
    "old_status": "processing",
    "new_status": "ready",
    "timestamp": "2024-01-01T10:00:00.000000Z"
  }
}
```

---

## Testing

### Test Credentials
```
Email: test@washbox.com
Password: password123
```

### Postman Collection
Download: [WashBox API.postman_collection.json](./postman/WashBox_API.postman_collection.json)

---

## Support

For API support, contact:
- Email: api@washbox.com
- Documentation: https://docs.washbox.com
