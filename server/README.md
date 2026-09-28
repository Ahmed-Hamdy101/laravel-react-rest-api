
## ⭐ Star this repo if you find it useful!
# Laravel Rest API

A production-ready **Laravel REST API** for admin dashboard backends, featuring role-based access control, Passport authentication, product/order management, and CSV export.

[![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php)](https://php.net)
[![Docker](https://img.shields.io/badge/Docker-ready-2496ED?logo=docker)](https://docker.com)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![Laravel](https://github.com/Ahmed-Hamdy101/laravel-rest-api/actions/workflows/laravel.yml/badge.svg)](https://github.com/Ahmed-Hamdy101/laravel-rest-api/actions/workflows/laravel.yml)
---

## Features

- **JWT authentication** via Laravel Passport
- **Role-based access control** — `admin`, `editor` roles with `CheckRole` middleware
- **User management** — CRUD, profile update, password change
- **Product management** — full CRUD with image upload via Laravel Storage
- **Order management** — list, show, CSV export with streaming cursor
- **Permissions system** — Role ↔ Permission many-to-many relationship
- **API versioning** — all routes under `/api/v1`
- **Input validation** — FormRequest classes for every endpoint
- **API Resources** — controlled serialization, no raw model leaking

---

## System Architecture

### High-Level Overview
```mermaid
graph TB
    Client["🖥️ Client Applications<br/>(Web/Mobile)"]
    
    subgraph API["API Layer"]
        Router["Router<br/>api.php"]
        Middleware["Middleware<br/>(Auth, CORS, Rate Limit)"]
        Controller["Controllers<br/>(9 Controllers)"]
        Request["Request Validation<br/>(FormRequest)"]
        Resource["Resource Layer<br/>(API Transformers)"]
    end
    
    subgraph Business["Business Logic Layer"]
        Model["Models<br/>(6 Models)"]
        Service["Services<br/>(Auth, Order, CSV)"]
        Rule["Authorization<br/>(Policies, Roles)"]
    end
    
    subgraph Data["Data Layer"]
        DB["MySQL Database"]
        Cache["Redis Cache<br/>(Sessions, Cache)"]
        Storage["Laravel Storage<br/>(Images)"]
    end
    
    External["External Services<br/>(Passport OAuth2)"]
    
    Client -->|HTTP/REST| Router
    Router --> Middleware
    Middleware --> Controller
    Controller --> Request
    Request --> Resource
    Resource --> Model
    Model --> Service
    Service --> Rule
    Rule --> DB
    Rule --> Cache
    Rule --> Storage
    Service --> External
    
    style API fill:#e1f5ff
    style Business fill:#f3e5f5
    style Data fill:#e8f5e9
    style External fill:#fff3e0
```

---

## Database Schema

### Entity-Relationship Diagram
```mermaid
erDiagram
    USERS ||--o{ ROLES : "belongs_to"
    USERS ||--o{ ORDERS : "creates"
    ROLES ||--o{ PERMISSIONS : "has_many"
    PRODUCTS ||--o{ ORDER_ITEMS : "belongs_to"
    ORDERS ||--o{ ORDER_ITEMS : "has_many"
    USERS ||--o{ OAUTH_ACCESS_TOKENS : "owns"
    
    USERS {
        bigint id PK
        string name
        string email UK
        string password
        bigint role_id FK
        timestamp email_verified_at
        string remember_token
        timestamp created_at
        timestamp updated_at
    }
    
    ROLES {
        bigint id PK
        string name UK
        text description
        timestamp created_at
        timestamp updated_at
    }
    
    PERMISSIONS {
        bigint id PK
        string name UK
        text description
        timestamp created_at
        timestamp updated_at
    }
    
    PRODUCTS {
        bigint id PK
        string title
        text description
        decimal price "decimal(10,2)"
        string image_path
        timestamp created_at
        timestamp updated_at
    }
    
    ORDERS {
        bigint id PK
        bigint user_id FK
        string first_name
        string last_name
        string email
        decimal total_price "decimal(12,2)"
        string status "pending|completed|cancelled"
        timestamp created_at
        timestamp updated_at
    }
    
    ORDER_ITEMS {
        bigint id PK
        bigint order_id FK
        bigint product_id FK
        integer quantity
        decimal price "decimal(10,2)"
        timestamp created_at
        timestamp updated_at
    }
    
    OAUTH_ACCESS_TOKENS {
        string id PK
        bigint user_id FK
        string client_id FK
        text scopes
        boolean revoked
        timestamp created_at
        timestamp updated_at
        timestamp expires_at
    }
```

---

## Authentication & Authorization Flow

### JWT Authentication with Passport
```mermaid
sequenceDiagram
    participant Client as Client Application
    participant API as API Server
    participant Auth as AuthController
    participant Passport as Passport OAuth2
    participant DB as Database
    participant Middleware as Auth Middleware
    
    rect rgb(200, 150, 255)
    Note over Client,DB: Login Process
    Client->>API: POST /api/v1/login (email, password)
    API->>Auth: Handle login request
    Auth->>DB: Verify user credentials
    DB-->>Auth: User found ✓
    Auth->>Passport: Generate OAuth2 Token
    Passport->>DB: Store access_token
    Passport-->>Auth: Return JWT token
    Auth-->>Client: HTTP 200 + access_token
    end
    
    rect rgb(150, 200, 255)
    Note over Client,DB: Protected Request
    Client->>API: GET /api/v1/profile<br/>Header: Authorization: Bearer {token}
    API->>Middleware: Validate token
    Middleware->>Passport: Verify token signature & expiry
    Passport->>DB: Check token validity
    DB-->>Passport: Token valid ✓
    Passport-->>Middleware: Token verified
    Middleware->>Auth: Extract user from token
    Auth-->>API: User ID: 1, Role: admin
    API-->>Client: HTTP 200 + User Profile
    end
    
    rect rgb(200, 200, 150)
    Note over Client,DB: Role-Based Access
    Client->>API: POST /api/v1/users<br/>Header: Authorization: Bearer {token}
    API->>Middleware: Check Role
    Middleware->>DB: Get user role
    DB-->>Middleware: Role: admin ✓
    Middleware-->>API: Access Allowed
    API-->>Client: HTTP 200 + Users List
    end
```

---

## API Endpoint Hierarchy

### Route Structure & Access Control
```mermaid
graph TD
    API["🔗 API v1<br/>/api/v1"]
    
    subgraph Public["PUBLIC ENDPOINTS<br/>(No Authentication)"]
        Login["POST /login<br/>@AuthController.login"]
        Register["POST /register<br/>@AuthController.register"]
    end
    
    subgraph Auth["AUTHENTICATED ENDPOINTS<br/>(Requires Token)"]
        Logout["POST /logout<br/>@AuthController.logout"]
        Profile["GET /profile<br/>@AuthController.profile"]
        Orders["GET /orders<br/>@OrderController.index"]
        OrdersExport["GET /orders/export<br/>@OrderController.export"]
        Charts["GET /chart<br/>@DashboardController.chart"]
    end
    
    subgraph Admin["ADMIN ONLY<br/>(Role: admin)"]
        Users["GET|POST /users<br/>@UserController"]
        Roles["GET|POST /roles<br/>@RoleController"]
    end
    
    subgraph AdminEditor["ADMIN & EDITOR<br/>(Role: admin|editor)"]
        Products["GET|POST /products<br/>@ProductController"]
        Upload["POST /uploads<br/>@ImageController.upload"]
    end
    
    API --> Public
    API --> Auth
    API --> Admin
    API --> AdminEditor
    
    style Public fill:#c8e6c9
    style Auth fill:#bbdefb
    style Admin fill:#ffccbc
    style AdminEditor fill:#ffe0b2
```

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php      # login, register, logout
│   │   ├── UserController.php      # user CRUD + profile
│   │   ├── ProductController.php   # product CRUD
│   │   ├── OrderController.php     # order list, show, CSV export
│   │   ├── RoleController.php      # role CRUD
│   │   └── ImageController.php     # image upload → Laravel Storage
│   ├── Middleware/
│   │   └── CheckRole.php           # role:admin,editor gate
│   ├── Requests/                   # FormRequest validation classes
│   └── Resources/                  # API response transformers
├── Models/
│   ├── User.php         # HasApiTokens, role(), hasPermission()
│   ├── Role.php         # permissions() BelongsToMany
│   ├── Permission.php
│   ├── Product.php
│   └── Order.php        # order_items(), getNameAttribute()
routes/
└── api.php              # versioned under /api/v1
```

### Design Decisions

- **Passport over Sanctum** — chosen for full OAuth2 support and access token revocation on logout
- **FormRequest classes** — validation separated from controllers, reusable and testable
- **API Resources** — explicit field allowlisting prevents accidentally leaking sensitive model attributes (e.g. password hash, full role object)
- **`cursor()` for CSV export** — streams orders one at a time instead of loading all into memory, safe for large datasets
- **CheckRole middleware** — role names are checked at the route level, not scattered across controller methods
- **Storage::disk('public')** — images stored via Laravel's filesystem abstraction, not directly in `public/` with `0777` permissions

---

## Deployment Architecture

### Docker Container Architecture
```mermaid
graph TB
    subgraph Docker["Docker Environment"]
        subgraph Backend["Backend Container<br/>(Laravel 10.x)"]
            PHP["PHP 8.4 FPM"]
            Apache["Apache2 Web Server"]
            Laravel["Laravel Application"]
        end
        
        subgraph Frontend["Frontend Container<br/>(Vue.js)"]
            Node["Node.js Runtime"]
            Vue["Vue.js Application"]
        end
        
        subgraph Database["Database Container<br/>(MySQL)"]
            MySQL["MySQL 8.0+"]
        end
        
        Network["Docker Network"]
    end
    
    Backend --> Database
    Frontend -.->|API Calls| Backend
    
    style Backend fill:#e3f2fd
    style Frontend fill:#f3e5f5
    style Database fill:#e8f5e9
```

---

## Technology Stack

```mermaid
graph TB
    subgraph Backend["Backend - Laravel 10.x"]
        LARAVEL["Laravel Framework"]
        PASSPORT["Passport OAuth2"]
        SPATIE["Spatie Permissions"]
        STORAGE["Laravel Storage"]
        ELOQUENT["Eloquent ORM"]
    end
    
    subgraph Database["Data Layer"]
        MYSQL["MySQL 8.0+"]
        REDIS["Redis<br/>(Sessions, Cache)"]
    end
    
    subgraph Deployment["Deployment"]
        DOCKER["Docker"]
        PHP["PHP 8.4 FPM"]
        APACHE["Apache 2.4"]
    end
    
    subgraph Tools["Developer Tools"]
        PHPUNIT["PHPUnit<br/>(Testing)"]
        PHPSTAN["PHPStan<br/>(Analysis)"]
        PINT["Pint<br/>(Style)"]
    end
    
    LARAVEL --> PASSPORT
    LARAVEL --> SPATIE
    LARAVEL --> ELOQUENT
    ELOQUENT --> MYSQL
    LARAVEL --> REDIS
    
    PHP --> LARAVEL
    APACHE --> PHP
    DOCKER --> Backend
    DOCKER --> Database
    
    Backend --> PHPUNIT
    Backend --> PHPSTAN
    Backend --> PINT
    
    style Backend fill:#e3f2fd
    style Database fill:#e8f5e9
    style Deployment fill:#fff3e0
    style Tools fill:#fce4ec
```

---

> 📖 **For detailed architecture diagrams, data flows, and system design documentation**, see [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)

---

## Getting started

### Prerequisites

- PHP 8.2+
- Composer
- MySQL 8.0+
- Docker (optional)

### Installation

```bash
git clone https://github.com/Ahmed-Hamdy101/admin-api-laravel.git
cd admin-api-laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan passport:install
php artisan storage:link
```

### Development

```bash
php artisan serve
# API at http://localhost:8000/api/v1
```

### Docker

```bash
docker compose up --build
# API at http://localhost:8000/api/v1
```

---

## API Reference

### Authentication

Protected routes require a Bearer token:
```
Authorization: Bearer <token>
```

### Endpoints

| Method | Endpoint | Auth | Role | Description |
|--------|----------|------|------|-------------|
| POST | `/api/v1/login` | — | — | Login, returns token |
| POST | `/api/v1/register` | — | — | Register new user |
| POST | `/api/v1/logout` | ✅ | — | Revoke current token |
| GET | `/api/v1/profile` | ✅ | — | Get own profile |
| PUT | `/api/v1/profile/info` | ✅ | — | Update name/email |
| PUT | `/api/v1/profile/password` | ✅ | — | Change password |
| GET/POST/PUT/DELETE | `/api/v1/users` | ✅ | admin | User CRUD |
| GET/POST/PUT/DELETE | `/api/v1/roles` | ✅ | admin | Role CRUD |
| GET/POST/PUT/DELETE | `/api/v1/products` | ✅ | admin, editor | Product CRUD |
| POST | `/api/v1/uploads` | ✅ | admin, editor | Upload image |
| GET | `/api/v1/orders` | ✅ | — | List orders (paginated) |
| GET | `/api/v1/orders/{id}` | ✅ | — | Get order detail |
| GET | `/api/v1/orders/export` | ✅ | — | Download orders CSV |

### Example: Login

```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
  "user": {
    "id": 1,
    "full_name": "Ahmed Hamdy",
    "email": "admin@example.com",
    "role": "admin"
  }
}
```

---

## Environment variables

| Variable | Description |
|---|---|
| `APP_KEY` | Laravel application key |
| `DB_HOST` | MySQL host |
| `DB_DATABASE` | Database name |
| `DB_USERNAME` | Database user |
| `DB_PASSWORD` | Database password |
| `APP_URL` | Application URL (used for storage links) |

---

## Author

**Ahmed Hamdy** — [GitHub](https://github.com/Ahmed-Hamdy101) · [LinkedIn](https://www.linkedin.com/in/ahmed-hamdy-AH)

## Contributing

Contributions are welcome! Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details.
