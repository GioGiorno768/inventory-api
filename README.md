# 📦 Smart Inventory Management System - Backend API

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![JWT](https://img.shields.io/badge/JWT-Auth-000000?style=for-the-badge&logo=jsonwebtokens&logoColor=white)

RESTful API untuk sistem manajemen inventori barang dengan fitur autentikasi multi-role, CRUD lengkap, transaksi real-time, dan export laporan PDF.

## 🚀 Features

- ✅ **JWT Authentication** - Secure token-based authentication
- ✅ **Multi-Role Authorization** - Admin & Staff dengan hak akses berbeda
- ✅ **CRUD Operations** - Manajemen Barang, Transaksi, User
- ✅ **Auto Stock Update** - Stok otomatis update saat transaksi
- ✅ **PDF Reports** - Export laporan dalam format PDF
- ✅ **Real-time Dashboard** - Statistik dan visualisasi data
- ✅ **Low Stock Alert** - Notifikasi barang stok rendah
- ✅ **Activity Logging** - Catat semua aktivitas user
- ✅ **API Documentation** - Dokumentasi lengkap endpoint

## 📋 Tech Stack

| Technology | Version | Purpose |
|------------|---------|---------|
| Laravel | 11.x | PHP Framework |
| MySQL | 8.0+ | Database |
| JWT Auth | tymon/jwt-auth | Authentication |
| DOMPDF | barryvdh/laravel-dompdf | PDF Generator |
| Eloquent ORM | Built-in | Database ORM |

---

## 🛠️ Installation

### Prerequisites

- PHP >= 8.2
- Composer
- MySQL >= 8.0
- Node.js & NPM (optional, for Laravel Mix)

### Step 1: Clone Repository

```bash
git clone https://github.com/GioGiorno768/inventory-api.git
cd inventory-api
```

### Step 2: Install Dependencies

```bash
composer install
```

### Step 3: Environment Configuration

```bash
cp .env.example .env
```

Edit `.env` file:

```env
APP_NAME="Smart Inventory API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_db
DB_USERNAME=root
DB_PASSWORD=

JWT_SECRET=

```

### Step 4: Generate Application Key

```bash
php artisan key:generate
```

### Step 5: Generate JWT Secret

```bash
php artisan jwt:secret
```

### Step 6: Database Setup

```bash
# Create database
mysql -u root -p
CREATE DATABASE inventory_db;
exit;

# Run migrations
php artisan migrate

# Seed initial data
php artisan db:seed
```

### Step 7: Storage Link

```bash
php artisan storage:link
```

### Step 8: Run Server

```bash
php artisan serve
```

API akan berjalan di `http://127.0.0.1:8000`

## 🗄️ Database Schema

### Users Table
```sql
- id (PK)
- name
- email (unique)
- password (hashed)
- role (enum: admin, staff)
- created_at
- updated_at
```

### Items Table
```sql
- id (PK)
- name
- category
- stock
- unit
- threshold
- created_at
- updated_at
```

### Transactions Table
```sql
- id (PK)
- item_id (FK)
- user_id (FK)
- type (enum: in, out)
- quantity
- date
- file_path (nullable)
- file_public_id (nullable)
- description (nullable)
- created_at
- updated_at
```

## 🔐 Authentication

### Login

**POST** `/api/login`

```json
{
  "email": "admin@inventory.com",
  "password": "admin123"
}
```

**Response:**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 3600,
  "user": {
    "id": 1,
    "name": "Admin",
    "email": "admin@inventory.com",
    "role": "admin"
  }
}
```

### Default Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@inventory.com | admin123 |
| Staff | staff@inventory.com | staff123 |

## 📡 API Endpoints

### Authentication
```
POST   /api/login              # Login user
POST   /api/logout             # Logout user
POST   /api/refresh            # Refresh token
GET    /api/me                 # Get current user
```

### Items (Barang)
```
GET    /api/items              # Get all items (with pagination, search, filter)
GET    /api/items/{id}         # Get item detail
POST   /api/items              # Create item (Admin only)
PUT    /api/items/{id}         # Update item (Admin only)
DELETE /api/items/{id}         # Delete item (Admin only)
GET    /api/items/low-stock    # Get low stock items
GET    /api/items/categories   # Get all categories
```

### Transactions
```
GET    /api/transactions       # Get all transactions (with filter)
GET    /api/transactions/{id}  # Get transaction detail
POST   /api/transactions       # Create transaction
DELETE /api/transactions/{id}  # Delete transaction (Admin only)
```

### Dashboard
```
GET    /api/dashboard?days=30  # Get dashboard statistics
GET    /api/activity-log       # Get activity log (Admin only)
```

### Reports (Admin only)
```
GET    /api/reports/items/pdf              # Export items report
GET    /api/reports/transactions/pdf       # Export transactions report
GET    /api/reports/stock/pdf              # Export stock report
```

### Users (Admin only)
```
GET    /api/users              # Get all users
GET    /api/users/{id}         # Get user detail
POST   /api/users              # Create user
PUT    /api/users/{id}         # Update user
DELETE /api/users/{id}         # Delete user
```

## 🔒 Authorization

### Middleware

**auth:api** - Require authentication
```php
Route::middleware('auth:api')->group(function () {
    // Protected routes
});
```

**role:admin** - Admin only
```php
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    // Admin only routes
});
```

### Permission Matrix

| Endpoint | Admin | Staff |
|----------|-------|-------|
| GET /items | ✅ | ✅ |
| POST /items | ✅ | ❌ |
| PUT /items | ✅ | ❌ |
| DELETE /items | ✅ | ❌ |
| POST /transactions | ✅ | ✅ |
| DELETE /transactions | ✅ | ❌ |
| GET /reports/* | ✅ | ❌ |
| /users/* | ✅ | ❌ |

## 📊 Business Logic

### Stock Update Logic

**Barang Masuk (IN):**
```php
$item->stock += $transaction->quantity;
```

**Barang Keluar (OUT):**
```php
// Validation
if ($item->stock < $transaction->quantity) {
    throw new Exception('Stok tidak mencukupi');
}
$item->stock -= $transaction->quantity;
```

### Low Stock Detection

```php
$lowStockItems = Item::whereColumn('stock', '<=', 'threshold')->get();
```

## 🧪 Testing

### Manual Testing with Postman

1. Import Postman collection (coming soon)
2. Set environment variables:
   - `base_url`: http://127.0.0.1:8000/api
   - `token`: (akan diisi otomatis setelah login)

### Test Scenarios

```bash
# Test authentication
POST /api/login

# Test get items
GET /api/items

# Test create transaction
POST /api/transactions
- item_id: 1
- type: in
- quantity: 10
- date: 2025-10-20

# Test PDF export
GET /api/reports/items/pdf
```


---
