
# Laravel + React REST API (Decoupled Architecture)

A fully **decoupled** full-stack admin application:

- **`server/`** — Production-ready Laravel REST API (Passport auth, RBAC, products, orders)
- **`client/`** — Next.js (React) admin dashboard

Each side runs independently with its own Docker setup and can be developed, deployed, and scaled separately.

[![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?logo=laravel)](https://laravel.com)
[![Next.js](https://img.shields.io/badge/Next.js-16-black?logo=next.js)](https://nextjs.org)
[![React](https://img.shields.io/badge/React-19-61DAFB?logo=react)](https://react.dev)
[![Docker](https://img.shields.io/badge/Docker-ready-2496ED?logo=docker)](https://docker.com)
[![License](https://img.shields.io/badge/license-MIT-green)](server/LICENSE.md)

---

## Architecture Overview

```mermaid
graph TB
    subgraph Client["client/ — Next.js 16 + React 19"]
        UI["Admin Dashboard UI"]
        AuthClient["Auth / Token Storage"]
        APIClient["API Client (fetch/axios)"]
    end

    subgraph Server["server/ — Laravel 10 REST API"]
        Router["Router<br/>/api/v1"]
        Middleware["Middleware<br/>Auth · CORS · CheckRole · Rate Limit"]
        Controllers["Controllers"]
        Requests["FormRequests"]
        Resources["API Resources"]
        Services["Services<br/>Auth · Order · CSV"]
        Models["Eloquent Models"]
    end

    subgraph Data["Data Layer"]
        MySQL[(MySQL)]
        Redis[(Redis Cache)]
        Storage["Laravel Storage<br/>(Images)"]
    end

    UI --> AuthClient
    AuthClient --> APIClient
    APIClient -->|"HTTP + Bearer Token"| Router
    Router --> Middleware
    Middleware --> Controllers
    Controllers --> Requests
    Controllers --> Services
    Services --> Models
    Controllers --> Resources
    Models --> MySQL
    Services --> Redis
    Services --> Storage

    style Client fill:#e3f2fd
    style Server fill:#f3e5f5
    style Data fill:#e8f5e9
```

### Why Decoupled?

| Benefit              | Description                                      |
|----------------------|--------------------------------------------------|
| Independent deploy   | Frontend & backend can be released separately    |
| Tech flexibility     | Swap React/Next.js or Laravel without rewriting  |
| Parallel development | Frontend & backend teams work in isolation       |
| Scalability          | Scale API and UI independently                   |
| Clear contracts      | API is the single source of truth                |

---

## Docker Architecture

```mermaid
graph TB
    subgraph Docker["Docker Environment"]
        subgraph Frontend["Frontend Container"]
            Next["Next.js 16<br/>React 19<br/>port 3000"]
        end

        subgraph Backend["Backend Container"]
            PHP["PHP 8.2+ FPM"]
            Apache["Apache / Nginx"]
            Laravel["Laravel 10 + Passport"]
        end

        subgraph Database["Database Container"]
            MySQL["MySQL 5.7 / 8.x<br/>port 33066→3306"]
        end

        Network["Docker Network: app"]
    end

    Next -->|"REST API calls<br/>Bearer Token"| Laravel
    Laravel --> MySQL
    PHP --> Laravel
    Apache --> PHP

    style Frontend fill:#e3f2fd
    style Backend fill:#f3e5f5
    style Database fill:#e8f5e9
```

---

## Project Structure

```
laravel-react-rest-api/
├── client/                 # Next.js admin dashboard
│   ├── app/                # App Router pages & layouts
│   ├── public/
│   ├── package.json
│   ├── next.config.ts
│   └── README.md
│
└── server/                 # Laravel REST API
    ├── app/
    │   ├── Http/
    │   │   ├── Controllers/
    │   │   ├── Middleware/     # CheckRole, etc.
    │   │   ├── Requests/       # FormRequest validation
    │   │   └── Resources/      # API transformers
    │   ├── Models/
    │   └── Services/
    ├── routes/api.php          # /api/v1/*
    ├── Dockerfile
    ├── docker-compose.yaml
    └── README.md
```

---

## Features

### Backend (`server/`)

- **JWT authentication** via Laravel Passport (OAuth2)
- **Role-based access control** — `admin` / `editor` + permissions
- **User management** — CRUD, profile, password change
- **Product management** — full CRUD + image upload (Laravel Storage)
- **Order management** — list, show, streaming CSV export
- **API versioning** — all routes under `/api/v1`
- **FormRequest validation** + **API Resources** (no raw model leakage)
- **Swagger / OpenAPI** documentation (l5-swagger)
- Docker-ready (PHP-FPM + Apache + MySQL)

### Frontend (`client/`)

- **Next.js 16** (App Router) + **React 19**
- **TypeScript** + **Tailwind CSS 4**
- Modern UI components (shadcn/ui style)
- Bun as package manager (also works with npm/yarn/pnpm)

---

## Authentication & Authorization Flow

```mermaid
sequenceDiagram
    participant C as Client (Next.js)
    participant API as Laravel API
    participant Auth as AuthController
    participant Passport as Passport OAuth2
    participant DB as MySQL
    participant MW as Auth + CheckRole Middleware

    rect rgb(200, 150, 255)
        Note over C,DB: Login
        C->>API: POST /api/v1/login<br/>{email, password}
        API->>Auth: Handle login
        Auth->>DB: Verify credentials
        DB-->>Auth: User found
        Auth->>Passport: Create access token
        Passport->>DB: Store oauth_access_tokens
        Passport-->>Auth: JWT / access_token
        Auth-->>C: 200 + access_token
    end

    rect rgb(150, 200, 255)
        Note over C,DB: Protected Request
        C->>API: GET /api/v1/profile<br/>Authorization: Bearer {token}
        API->>MW: Validate token
        MW->>Passport: Verify signature & expiry
        Passport->>DB: Check token not revoked
        DB-->>Passport: Valid
        Passport-->>MW: Authenticated user
        MW-->>API: User + Role
        API-->>C: 200 + Profile
    end

    rect rgb(200, 200, 150)
        Note over C,DB: Role-Based Access
        C->>API: POST /api/v1/users<br/>Bearer {token}
        API->>MW: CheckRole: admin
        MW->>DB: Load user role
        DB-->>MW: role = admin
        MW-->>API: Allowed
        API-->>C: 200 + result
    end
```

---

## API Route Hierarchy & Access Control

```mermaid
graph TD
    API["/api/v1"]

    subgraph Public["PUBLIC (No Auth)"]
        Login["POST /login"]
        Register["POST /register"]
    end

    subgraph Authenticated["AUTHENTICATED (Bearer Token)"]
        Logout["POST /logout"]
        Profile["GET /profile"]
        Orders["GET /orders"]
        Export["GET /orders/export"]
        Chart["GET /chart"]
    end

    subgraph AdminOnly["ADMIN ONLY"]
        Users["GET|POST /users"]
        Roles["GET|POST /roles"]
    end

    subgraph AdminEditor["ADMIN + EDITOR"]
        Products["GET|POST /products"]
        Upload["POST /uploads"]
    end

    API --> Public
    API --> Authenticated
    API --> AdminOnly
    API --> AdminEditor

    style Public fill:#c8e6c9
    style Authenticated fill:#bbdefb
    style AdminOnly fill:#ffccbc
    style AdminEditor fill:#ffe0b2
```

---

## Database Schema

```mermaid
erDiagram
    USERS ||--o{ ORDERS : creates
    USERS }o--|| ROLES : has
    ROLES ||--o{ PERMISSIONS : has_many
    PRODUCTS ||--o{ ORDER_ITEMS : "in"
    ORDERS ||--o{ ORDER_ITEMS : contains
    USERS ||--o{ OAUTH_ACCESS_TOKENS : owns

    USERS {
        bigint id PK
        string name
        string email UK
        string password
        bigint role_id FK
        timestamp created_at
        timestamp updated_at
    }

    ROLES {
        bigint id PK
        string name UK
        text description
    }

    PERMISSIONS {
        bigint id PK
        string name UK
        text description
    }

    PRODUCTS {
        bigint id PK
        string title
        text description
        decimal price
        string image_path
    }

    ORDERS {
        bigint id PK
        bigint user_id FK
        string first_name
        string last_name
        string email
        decimal total_price
        string status
    }

    ORDER_ITEMS {
        bigint id PK
        bigint order_id FK
        bigint product_id FK
        int quantity
        decimal price
    }

    OAUTH_ACCESS_TOKENS {
        string id PK
        bigint user_id FK
        string client_id FK
        boolean revoked
        timestamp expires_at
    }
```

---

## Quick Start

### Prerequisites

- Docker & Docker Compose (recommended)
- **or**
    - PHP 8.2+, Composer, MySQL 8
    - Node.js 20+ (or Bun)

### 1. Clone the repository

```bash
git clone https://github.com/Ahmed-Hamdy101/laravel-react-rest-api.git
cd laravel-react-rest-api
git checkout decouple-architecture
```

### 2. Start the Backend (Laravel API)

```bash
cd server
cp .env.example .env
docker compose up --build -d

# Inside the container (or locally):
php artisan key:generate
php artisan migrate
php artisan passport:install
php artisan storage:link
```

API available at: `http://localhost:8000/api/v1`

### 3. Start the Frontend (Next.js)

```bash
cd client
bun install          # or: npm install
bun dev              # or: npm run dev
```

Dashboard available at: `http://localhost:3000`

> Set `NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1` in `client/.env.local`.

---

## API Reference

Protected routes require:

```
Authorization: Bearer <access_token>
```

| Method | Endpoint                | Auth | Role          | Description           |
|--------|-------------------------|------|---------------|-----------------------|
| POST   | `/api/v1/login`         | —    | —             | Login → returns token |
| POST   | `/api/v1/register`      | —    | —             | Register new user     |
| POST   | `/api/v1/logout`        | ✅   | —             | Revoke current token  |
| GET    | `/api/v1/profile`       | ✅   | —             | Current user profile  |
| GET    | `/api/v1/users`         | ✅   | admin         | List users            |
| POST   | `/api/v1/users`         | ✅   | admin         | Create user           |
| GET    | `/api/v1/products`      | ✅   | admin\|editor | List products         |
| POST   | `/api/v1/products`      | ✅   | admin\|editor | Create product        |
| GET    | `/api/v1/orders`        | ✅   | —             | List orders           |
| GET    | `/api/v1/orders/export` | ✅   | —             | Stream CSV export     |
| POST   | `/api/v1/uploads`       | ✅   | admin\|editor | Upload image          |

Full docs & Swagger: see `server/README.md` and `/api/documentation`.

---

## Tech Stack

| Layer     | Technology                                      |
|-----------|-------------------------------------------------|
| Frontend  | Next.js 16, React 19, TypeScript, Tailwind CSS 4, Bun |
| Backend   | Laravel 10, PHP 8.2+, Passport, Spatie Permission |
| Database  | MySQL 5.7 / 8.x                                 |
| Auth      | Laravel Passport (OAuth2 / JWT)                 |
| Docs      | l5-swagger (OpenAPI)                            |
| Container | Docker + Docker Compose                         |

---

## Development Tips

- **CORS** — Match `FRONTEND_URL` / allowed origins in Laravel with your client URL.
- **Token storage** — Store the Passport access token securely (httpOnly cookie or secure storage).
- **API contract** — Treat `/api/v1` as the contract between `client` and `server`.
- **Environment** — Never commit real `.env` files. Use `.env.example` as the template.

---

## Documentation

- Backend deep-dive: [`server/README.md`](server/README.md)
- Architecture diagrams: [`server/docs/`](server/docs/)
- Client getting started: [`client/README.md`](client/README.md)

---

## License

MIT — see [`server/LICENSE.md`](server/LICENSE.md)

---

⭐ **Star this repo if you find it useful!**

