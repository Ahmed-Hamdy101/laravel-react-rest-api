# Laravel REST API - Architecture & System Design

## Table of Contents
1. [System Architecture](#system-architecture)
2. [Entity-Relationship Diagram](#entity-relationship-diagram)
3. [Authentication & Authorization Flow](#authentication--authorization-flow)
4. [API Endpoint Hierarchy](#api-endpoint-hierarchy)
5. [Request-Response Flow](#request-response-flow)
6. [Deployment Architecture](#deployment-architecture)
7. [Data Flow Diagrams](#data-flow-diagrams)
8. [Component Interaction](#component-interaction)
9. [Implementation Roadmap](#implementation-roadmap)

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

## Entity-Relationship Diagram

### Database Schema
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
    
    ROLE_PERMISSION {
        bigint role_id FK
        bigint permission_id FK
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

### JWT Authentication Flow with Laravel Passport
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
    
    rect rgb(255, 150, 150)
    Note over Client,DB: Logout Process
    Client->>API: POST /api/v1/logout<br/>Header: Authorization: Bearer {token}
    API->>Auth: Handle logout
    Auth->>DB: Revoke access token
    DB-->>Auth: Token revoked
    Auth-->>Client: HTTP 200 Logged out
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
        ProfileInfo["PUT /profile/info<br/>@AuthController.updateProfile"]
        ProfilePass["PUT /profile/password<br/>@AuthController.updatePassword"]
        Orders["GET /orders<br/>@OrderController.index"]
        OrdersExport["GET /orders/export<br/>@OrderController.export"]
        Charts["GET /chart<br/>@DashboardController.chart"]
        Permissions["GET /permissions<br/>@PermissionController.index"]
    end
    
    subgraph Admin["ADMIN ONLY<br/>(Role: admin)"]
        Users["GET|POST /users<br/>@UserController"]
        UsersDetail["GET|PUT|DELETE /users/:id<br/>@UserController"]
        Roles["GET|POST /roles<br/>@RoleController"]
        RolesDetail["GET|PUT|DELETE /roles/:id<br/>@RoleController"]
    end
    
    subgraph AdminEditor["ADMIN & EDITOR<br/>(Role: admin|editor)"]
        Products["GET|POST /products<br/>@ProductController"]
        ProductsDetail["GET|PUT|DELETE /products/:id<br/>@ProductController"]
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

## Request-Response Flow

### Typical Order Creation Flow
```mermaid
sequenceDiagram
    participant Client as Client
    participant Router as Router
    participant Middleware as Middleware Stack
    participant Controller as OrderController
    participant Request as FormRequest
    participant Model as Order Model
    participant Resource as OrderResource
    participant DB as Database
    
    rect rgb(230, 230, 250)
    Note over Client,DB: HTTP Request Processing
    Client->>Router: POST /api/v1/orders<br/>{items, customer_info}
    
    Router->>Middleware: Route Request
    Middleware->>Middleware: ✓ Auth Check
    Middleware->>Middleware: ✓ Role Check (admin|editor)
    Middleware->>Middleware: ✓ CORS Validation
    Middleware-->>Router: Middleware Passed
    
    Router->>Controller: Dispatch to store()
    Controller->>Request: Validate Input
    
    alt Validation Fails
        Request-->>Controller: ValidationException
        Controller-->>Client: HTTP 422 Unprocessable Entity<br/>{errors: {...}}
    else Validation Passes
        Request-->>Controller: Validated Data
        Controller->>Model: Create Order
        Model->>DB: INSERT INTO orders
        DB-->>Model: Order Created (id: 1)
        
        Controller->>Model: Attach OrderItems
        Model->>DB: INSERT INTO order_items
        DB-->>Model: OrderItems Created
        
        Controller->>Resource: Transform Response
        Resource-->>Controller: OrderResource Instance
        Controller-->>Client: HTTP 201 Created<br/>{id: 1, first_name: ..., items: [...]}
    end
    end
```

### Data Transformation Pipeline
```mermaid
graph LR
    Request["Raw Request<br/>{first_name, last_name,<br/>items: [{...}]}"]
    
    Validate["FormRequest<br/>Validation<br/>- Required fields<br/>- Email format<br/>- Numeric prices"]
    
    Process["Controller<br/>Business Logic<br/>- Create order<br/>- Calculate total<br/>- Attach items"]
    
    Store["Model<br/>Database Write<br/>- INSERT order<br/>- INSERT order_items<br/>- FOREIGN KEYS"]
    
    Transform["Resource Layer<br/>API Response<br/>- Hide sensitive fields<br/>- Format timestamps<br/>- Include relationships"]
    
    Response["HTTP Response<br/>{id, first_name,<br/>created_at, items: [...]}"]
    
    Request --> Validate
    Validate --> Process
    Process --> Store
    Store --> Transform
    Transform --> Response
    
    style Validate fill:#fff9c4
    style Process fill:#c8e6c9
    style Store fill:#bbdefb
    style Transform fill:#f8bbd0
    style Response fill:#d1c4e9
```

---

## Deployment Architecture

### Docker Container Architecture
```mermaid
graph TB
    subgraph Docker["Docker Environment"]
        subgraph Backend["Backend Container<br/>(laravel-rest-api)"]
            PHP["PHP 8.4 FPM"]
            Apache["Apache2 Web Server"]
            Laravel["Laravel 10.x Application"]
            Passport["Passport OAuth2"]
        end
        
        subgraph Frontend["Frontend Container<br/>(Vue.js)"]
            Node["Node.js Runtime"]
            Vite["Vite Dev Server"]
            Vue["Vue.js Application"]
        end
        
        subgraph Database["Database Container<br/>(MySQL)"]
            MySQL["MySQL 8.0+"]
            InnoDB["InnoDB Engine<br/>(Transactions)"]
        end
        
        Network["Docker Network<br/>(Overlay)"]
    end
    
    PHP -->|Executes| Laravel
    Apache -->|Serves| PHP
    Passport -->|Auth Provider| Laravel
    Node -->|Runs| Vite
    Vite -->|Dev Server| Vue
    Laravel -->|Queries| MySQL
    InnoDB -->|Stores| MySQL
    
    Backend -.->|HTTP| Frontend
    Backend -.->|TCP 3306| Database
    Frontend -.->|API Calls| Backend
    
    All["All connected via<br/>Docker Network"]
    
    Backend --> Network
    Frontend --> Network
    Database --> Network
    
    style Docker fill:#f5f5f5,stroke:#333,stroke-width:2px
    style Backend fill:#e3f2fd
    style Frontend fill:#f3e5f5
    style Database fill:#e8f5e9
```

### Development vs Production Deployment
```mermaid
graph TB
    subgraph Dev["Development Environment"]
        DevDocker["Docker Compose<br/>- Backend (Laravel)<br/>- Frontend (Vue)<br/>- MySQL"]
        DevFile["File Volumes"]
        DevHotReload["Hot Reload Enabled"]
        DevDebug["Debug Mode: ON"]
    end
    
    subgraph Prod["Production Environment"]
        ProdK8S["Kubernetes Cluster<br/>(or Docker Swarm)"]
        ProdLB["Load Balancer<br/>(nginx/HAProxy)"]
        ProdAPI["API Pods x3<br/>(Replicas)"]
        ProdDB["Managed Database<br/>(AWS RDS/Azure)"]
        ProdCache["Redis Cluster<br/>(Session & Cache)"]
        ProdStorage["Cloud Storage<br/>(S3/Azure Blob)"]
        CDN["CDN<br/>(Static Assets)"]
    end
    
    Dev --> DevFile
    Dev --> DevHotReload
    Dev --> DevDebug
    
    Prod --> ProdLB
    ProdLB --> ProdAPI
    ProdAPI --> ProdDB
    ProdAPI --> ProdCache
    ProdAPI --> ProdStorage
    ProdStorage --> CDN
    
    style Dev fill:#fff9c4
    style Prod fill:#ffccbc
```

---

## Data Flow Diagrams

### Authentication Token Flow
```mermaid
flowchart TD
    A["User Credentials<br/>(email, password)"] --> B["POST /api/v1/login"]
    B --> C["Hash Verification"]
    
    alt C1["Invalid"]
        C -->|❌| D["HTTP 401 Unauthorized"]
    else C2["Valid"]
        C -->|✓| E["Passport OAuth2<br/>Token Generator"]
        E --> F["Generate JWT Token<br/>(Header.Payload.Signature)"]
        F --> G["Store in oauth_access_tokens"]
        G --> H["Return Token to Client"]
        H --> I["Client Stores Token<br/>(localStorage/cookie)"]
        I --> J["Include in Request Header<br/>Authorization: Bearer {token}"]
        J --> K["Server Validates Token"]
        K --> L["Extract User & Role Data"]
        L --> M["Process Authenticated Request"]
    end
```

### Role-Based Access Control Flow
```mermaid
flowchart TD
    A["Authenticated Request<br/>/api/v1/users"] --> B["Extract User Role<br/>from Token"]
    
    B --> C["Check Required Role<br/>for Endpoint"]
    
    C -->|Route: admin only| D{"User Role = admin?"}
    C -->|Route: admin|editor| E{"User Role = admin or editor?"}
    
    D -->|Yes ✓| F["Check Permissions<br/>via Spatie"]
    D -->|No ❌| G["HTTP 403 Forbidden"]
    
    E -->|Yes ✓| F
    E -->|No ❌| G
    
    F --> H{"Has Permission<br/>to resource?"}
    H -->|Yes ✓| I["Process Request<br/>Execute Controller"]
    H -->|No ❌| G
    
    I --> J["Return Authorized<br/>Response"]
    G --> K["Return Error Response"]
    
    style D fill:#ffe0b2
    style E fill:#ffe0b2
    style H fill:#ffe0b2
    style I fill:#c8e6c9
    style J fill:#c8e6c9
    style G fill:#ffcdd2
```

### CSV Export Data Flow
```mermaid
flowchart TD
    A["GET /api/v1/orders/export<br/>Query: user_id, date_range"] --> B["Database Query<br/>Paginated Cursor"]
    
    B --> C["Stream Processing<br/>Memory Efficient"]
    
    C --> D["CSV Header"]
    D --> E["Iterate Orders"]
    
    E --> F["Chunk 1<br/>1000 records"]
    F --> G["Format CSV Row"]
    G --> H["Stream to Response"]
    
    E --> I["Chunk 2<br/>1000 records"]
    I --> J["Format CSV Row"]
    J --> H
    
    E --> K["Chunk N<br/>Last batch"]
    K --> L["Format CSV Row"]
    L --> H
    
    H --> M["Client Downloads<br/>orders_export.csv"]
    
    style C fill:#bbdefb
    style H fill:#c8e6c9
    style M fill:#d1c4e9
```

---

## Component Interaction

### MVC Request Lifecycle
```mermaid
graph LR
    Route["Routes<br/>api.php<br/>(Requests)"]
    
    Middleware1["Middleware Chain"]
    
    Controller["Controller<br/>(Request Handling)"]
    
    Service["Service Layer<br/>(Business Logic)"]
    
    Model["Model<br/>(Data)"]
    
    DB[(Database)]
    
    Resource["Resource<br/>(Response)"]
    
    Response["HTTP Response"]
    
    Route -->|1. Match Route| Middleware1
    Middleware1 -->|2. Pass| Controller
    Controller -->|3. Call| Service
    Service -->|4. Use| Model
    Model -->|5. Query| DB
    DB -->|6. Return| Model
    Model -->|7. Return| Service
    Service -->|8. Return| Controller
    Controller -->|9. Transform| Resource
    Resource -->|10. Return| Response
    
    style Route fill:#c8e6c9
    style Controller fill:#bbdefb
    style Service fill:#f8bbd0
    style Model fill:#f5f5f5
    style DB fill:#ffccbc
    style Resource fill:#e1bee7
    style Response fill:#d1c4e9
```

### Authorization System Architecture
```mermaid
graph TB
    User["👤 User"]
    Token["🔐 JWT Token<br/>(Contains user_id, role)"]
    
    subgraph Auth["Authorization Layer"]
        RoleMiddleware["CheckRole Middleware<br/>(Route Protection)"]
        RoleDB["Roles Table<br/>(admin, editor, user)"]
        PermissionDB["Permissions Table<br/>(create, read, update, delete)"]
        RolePermPivot["role_permission Pivot Table<br/>(Many-to-Many)"]
    end
    
    Resource["Protected Resource<br/>(User, Product, Order)"]
    
    User -->|Authenticate| Token
    Token -->|Extract Role| RoleMiddleware
    RoleMiddleware -->|Check Role| RoleDB
    RoleDB -->|Get Permissions| RolePermPivot
    RolePermPivot -->|Verify| PermissionDB
    PermissionDB -->|Authorize| Resource
    
    style Auth fill:#fff3e0
    style RoleMiddleware fill:#ffe0b2
    style Token fill:#e0f2f1
```

---

## Technology Stack Summary

```mermaid
graph TB
    subgraph Frontend["Frontend"]
        VUE["Vue.js 3.x"]
        VITE["Vite Build Tool"]
        AXIOS["Axios HTTP Client"]
    end
    
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
    
    subgraph Deployment["Deployment & DevOps"]
        DOCKER["Docker"]
        COMPOSE["Docker Compose"]
        PHP["PHP 8.4 FPM"]
        APACHE["Apache 2.4"]
    end
    
    subgraph Tools["Developer Tools"]
        PHPUNIT["PHPUnit<br/>(Testing)"]
        PHPSTAN["PHPStan<br/>(Static Analysis)"]
        PINT["Pint<br/>(Code Style)"]
    end
    
    VUE --> AXIOS
    VITE --> VUE
    AXIOS --> Backend
    
    LARAVEL --> PASSPORT
    LARAVEL --> SPATIE
    LARAVEL --> STORAGE
    LARAVEL --> ELOQUENT
    
    ELOQUENT --> MYSQL
    LARAVEL --> REDIS
    
    PHP --> LARAVEL
    APACHE --> PHP
    DOCKER --> COMPOSE
    COMPOSE --> Backend
    COMPOSE --> Frontend
    COMPOSE --> Database
    
    Backend --> PHPUNIT
    Backend --> PHPSTAN
    Backend --> PINT
    
    style Frontend fill:#f3e5f5
    style Backend fill:#e3f2fd
    style Database fill:#e8f5e9
    style Deployment fill:#fff3e0
    style Tools fill:#fce4ec
```

---

## Key Architectural Patterns Used

### 1. **MVC Pattern**
- **Model**: Eloquent ORM models for data representation
- **View**: API Resources for response formatting
- **Controller**: HTTP controllers handling requests

### 2. **Repository Pattern (Implicit)**
- Controllers delegate to Models
- Models encapsulate database logic
- Separation of concerns

### 3. **Resource Pattern**
- API Resources transform models into API responses
- Consistent field allowlisting for security
- Relationship eager-loading optimization

### 4. **Middleware Pattern**
- Authentication middleware (Passport)
- Authorization middleware (CheckRole)
- CORS middleware for cross-origin requests

### 5. **OAuth2 Pattern**
- Laravel Passport implements OAuth2 token-based auth
- JWT tokens with expiration
- Refresh token flow supported

### 6. **Role-Based Access Control (RBAC)**
- Spatie Laravel Permission for role/permission management
- Database-driven authorization
- Flexible permission assignment per role

---

## Security Considerations

```mermaid
graph TB
    Request["HTTP Request"]
    
    CORS["CORS Middleware<br/>- Validate Origin<br/>- Allow Headers"]
    
    AUTH["Authentication<br/>- JWT Validation<br/>- Token Expiry<br/>- User Identification"]
    
    AUTHZ["Authorization<br/>- Role Check<br/>- Permission Check<br/>- Resource Ownership"]
    
    INPUT["Input Validation<br/>- FormRequest<br/>- Type Casting<br/>- SQL Injection Prevention"]
    
    BUSINESS["Business Logic<br/>- Data Integrity<br/>- State Validation"]
    
    OUTPUT["Output Encoding<br/>- JSON Encoding<br/>- Resource Transformation"]
    
    Response["HTTP Response"]
    
    Request --> CORS
    CORS --> AUTH
    AUTH --> AUTHZ
    AUTHZ --> INPUT
    INPUT --> BUSINESS
    BUSINESS --> OUTPUT
    OUTPUT --> Response
    
    style CORS fill:#ffe0b2
    style AUTH fill:#bbdefb
    style AUTHZ fill:#c8e6c9
    style INPUT fill:#f8bbd0
    style BUSINESS fill:#e1bee7
    style OUTPUT fill:#b2dfdb
```

---

## API Response Structure

### Success Response
```json
{
  "success": true,
  "status_code": 200,
  "message": "Operation completed successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "admin"
  }
}
```

### Paginated Response
```json
{
  "success": true,
  "status_code": 200,
  "data": [
    { "id": 1, "name": "Product 1", "price": 100 },
    { "id": 2, "name": "Product 2", "price": 150 }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 42,
    "last_page": 3
  }
}
```

### Error Response
```json
{
  "success": false,
  "status_code": 422,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required"],
    "password": ["The password must be at least 8 characters"]
  }
}
```

---

## Performance Optimization Strategies

| Strategy | Implementation | Benefit |
|----------|-----------------|---------|
| **Eager Loading** | Using `with()` in Eloquent | Prevents N+1 queries |
| **Pagination** | `paginate()` method | Limits data per request |
| **Cursor Pagination** | Used in CSV export | Memory efficient for large datasets |
| **Caching** | Redis for sessions | Faster auth token lookup |
| **Database Indexing** | On foreign keys, emails | Faster queries |
| **Lazy Loading Prevention** | Resource layer control | Prevents over-fetching |
| **API Resource Filtering** | Explicit field selection | Reduces response size |
| **Compression** | gzip middleware (Apache) | Smaller transfer size |

---

## Future Enhancement Recommendations

### High Priority
- [ ] API Rate Limiting (throttle middleware)
- [ ] Request Logging & Monitoring
- [ ] Webhook System for Events
- [ ] Batch Operations (bulk create/update)
- [ ] Advanced Filtering & Search

### Medium Priority
- [ ] GraphQL API Alternative
- [ ] Soft Deletes for all resources
- [ ] Audit Trail / Change Log
- [ ] Email Notifications
- [ ] Two-Factor Authentication (2FA)

### Low Priority
- [ ] API Versioning (v2)
- [ ] Subscription/Billing System
- [ ] Advanced Analytics
- [ ] Multi-tenancy Support

---

## Testing Strategy

### Test Coverage Structure
```mermaid
graph TB
    Tests["Test Suite"]
    
    Unit["Unit Tests<br/>- Model Tests<br/>- Helper Functions<br/>- Individual Methods"]
    
    Feature["Feature Tests<br/>- API Endpoints<br/>- Authentication Flow<br/>- Authorization"]
    
    Integration["Integration Tests<br/>- Database Transactions<br/>- External Services<br/>- Cache Behavior"]
    
    E2E["E2E Tests<br/>(Playwright)<br/>- Full User Flows<br/>- UI Interactions"]
    
    Tests --> Unit
    Tests --> Feature
    Tests --> Integration
    Tests --> E2E
    
    style Unit fill:#c8e6c9
    style Feature fill:#bbdefb
    style Integration fill:#f8bbd0
    style E2E fill:#e1bee7
```

---

## Documentation & Resources

- **Postman Collection**: Import API endpoints for testing
- **Database Schema**: Run migrations for complete schema
- **API Response Examples**: See `docs/` folder
- **Environment Setup**: Check `.env.example` for configuration

---

## Implementation Roadmap

This roadmap evolves the current Laravel API into a reliable payment platform without introducing microservices before the domain boundaries and operational needs are proven.

### Current Baseline

- Laravel 10 with PHP 8.1 support.
- Laravel Passport authentication.
- Spatie roles and permissions.
- MySQL persistence.
- Synchronous queues by default.
- File-based cache by default.
- Orders and order items exist, but payment transactions and payment states do not yet exist.

### Milestone 1: Stabilize the Existing API

- Establish a passing baseline for authentication, users, products, orders, roles, and permissions.
- Standardize validation, authorization, API responses, and error handling.
- Review API resources so passwords, tokens, and internal fields are never exposed.
- Add indexes for user email, order status, ownership, and foreign keys.
- Add rate limits to login, registration, exports, and future payment endpoints.
- Record architecture decisions before introducing infrastructure dependencies.

**Exit criteria:** existing behavior is covered by tests and regressions are visible before payment work begins.

### Milestone 2: Create the Payment Domain

Add a payment module inside the monolith with clear ownership of payment behavior.

Core concepts:

- Payment
- Payment attempt
- Refund
- Payment webhook
- Payment method
- Payment provider

Payment data should include the order, amount, currency, provider, provider transaction ID, internal status, provider status, idempotency key, failure reason, and timestamps.

Keep order status and payment status separate. Use controlled payment states such as `created`, `pending`, `requires_action`, `authorized`, `paid`, `failed`, `cancelled`, `refunded`, and `partially_refunded`.

**Exit criteria:** payment state transitions and database ownership are documented before provider integrations are added.

### Milestone 3: Integrate One Payment Provider

Implement one provider end to end in sandbox mode before adding PayPal, cards, Vodafone Cash, and InstaPay.

Each provider must implement the same application-facing capabilities:

- Create payment.
- Confirm payment.
- Query payment status.
- Refund payment.
- Verify webhook signatures.
- Translate provider statuses into internal statuses.

Use adapters so controllers and order logic do not contain provider-specific code.

**Exit criteria:** successful, failed, cancelled, duplicate, timeout, and webhook-retry scenarios are tested for the first provider.

### Milestone 4: Secure Payment and Password Workflows

- Never store raw card numbers, CVV, or full card credentials.
- Use hosted checkout or provider tokenization for card payments.
- Verify webhook signatures and compare webhook amounts with the original order.
- Require idempotency keys to prevent duplicate charges.
- Require current password and confirmation when a user changes their own password.
- Restrict role changes, refunds, and administrative payment actions with explicit permissions.
- Add audit records for payment, password, email, role, refund, and authentication changes.

**Exit criteria:** sensitive operations have authorization tests, audit coverage, and no secret or payment data in logs or API responses.

### Milestone 5: Connect Payments to Orders

Implement the workflow:

```text
Order created
    -> Payment created
    -> Payment pending
    -> Provider webhook received
    -> Payment verified
    -> Payment marked paid
    -> Order marked paid
    -> Notification dispatched
```

The API must never mark a payment as successful based only on a frontend redirect. Provider responses and signed webhooks must be verified server-side.

**Exit criteria:** order/payment integration tests prove that payment creation, confirmation, failure, refund, and duplicate callbacks are handled safely.

### Milestone 6: Introduce Asynchronous Processing

Move from the default synchronous queue to Redis queues or RabbitMQ for:

- Webhook processing.
- Provider retries.
- Notifications.
- Refund processing.
- Payment reconciliation.
- Report and export work.

Add domain events such as `PaymentCreated`, `PaymentSucceeded`, `PaymentFailed`, `PaymentRefunded`, and `PaymentWebhookReceived`.

Add retry policies, backoff, failed-job handling, dead-letter behavior, idempotent consumers, distributed locks, and a transactional outbox.

RabbitMQ is the recommended first broker for business jobs and routing. Introduce Kafka only when event volume, replay requirements, analytics, or multiple independent consumers justify it.

**Exit criteria:** asynchronous operations can be retried without duplicate charges or inconsistent order state.

### Milestone 7: Refactor Toward Clean Architecture

Organize new code by business capability:

```text
Domain
    User, Order, Payment, Catalog

Application
    Use cases and commands

Infrastructure
    Database, providers, queues, cache, external APIs

Presentation
    Controllers, requests, resources, webhooks
```

Controllers should coordinate HTTP requests only. Application services should execute use cases. Domain code should own business rules. Provider adapters should own external API behavior.

Use Strategy/Adapter for providers, State for payment transitions, commands for use cases, events for completed facts, and the Outbox pattern for reliable event publication. Add repositories only where they protect a meaningful boundary or improve testing.

**Exit criteria:** payment behavior can be tested without calling a real provider, and provider changes do not require rewriting controllers or order rules.

### Milestone 8: Cache and Performance

- Move shared production cache from file storage to Redis.
- Cache product reads, roles/permissions, dashboard summaries, and short-lived payment status reads where safe.
- Prevent N+1 queries and add missing indexes.
- Keep exports and long-running work asynchronous.
- Measure API latency, database time, queue latency, provider latency, cache hit rate, and error rate.
- Establish a load-test baseline before claiming performance improvements.

Do not cache payment authorization decisions or sensitive data without an explicit expiry and invalidation strategy.

**Exit criteria:** the slowest endpoints have measured before/after results and defined p95 latency targets.

### Milestone 9: Observability and Production Readiness

Add structured logs, request IDs, payment correlation IDs, health checks, queue monitoring, database monitoring, provider metrics, and alerts for payment failures, webhook backlog, retry storms, and unusual refund activity.

Track at least:

- Payment success and failure rates.
- Webhook processing time.
- Duplicate payment attempts.
- Refund rate.
- Queue depth.
- API p95 latency.
- Slow database queries.

**Exit criteria:** a failed payment or delayed webhook can be traced from API request to provider response and database state.

### Milestone 10: Selective Service Extraction

Only extract services after the modular monolith has stable boundaries and measured scaling pressure.

Possible future services are Identity, Order, Payment, Catalog, Notification, and Reporting. Payment is the strongest first extraction candidate because it has external providers, security boundaries, and independent asynchronous workloads.

An extracted service must own its database, expose an explicit API or event contract, define authentication and retry behavior, and never share direct table writes with another service.

AWS Lambda can be evaluated later for isolated webhook, reconciliation, notification, or scheduled workloads. It should not be adopted as a general performance solution without measuring cold starts, database connections, cost, and operational complexity.

### Recommended Delivery Order

```text
Baseline tests and security
    -> Payment domain and migrations
    -> One provider in sandbox
    -> Webhooks, idempotency, refunds
    -> Redis and asynchronous jobs
    -> Additional providers
    -> Performance and observability
    -> Selective service extraction
```

---

**Last Updated**: 2026-08-14  
**Architecture Version**: 1.0  
**Framework Version**: Laravel 10.x
