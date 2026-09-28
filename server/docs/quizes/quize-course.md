# Admin API Course - Practice Quiz

## Section 1: Laravel API Authentication & Security

### Question 1: Authentication Flow
**Which authentication package is being used in this admin-api project?**
- A) Laravel Sanctum
- B) Laravel Passport
- C) JWT Auth
- D) Custom Token System

**Answer:** B) Laravel Passport

**Explanation:** The project uses `Laravel\Passport\HasApiTokens` trait in the User model and the `auth:api` middleware in routes, indicating Passport is the authentication system.

---

### Question 2: Password Security
**In the AuthController's register method, what function is used to hash passwords?**
```php
$data['password'] = ______($request->input('password'));
```
- A) hash()
- B) encrypt()
- C) bcrypt()
- D) Hash::make()

**Answer:** C) bcrypt()

**Explanation:** The `bcrypt()` helper function is Laravel's recommended way to hash passwords using the Bcrypt algorithm, which is secure and includes automatic salting.

---

### Question 3: Token Generation
**After successful registration, what method is called to generate an access token?**
- A) `$user->generateToken('admin')`
- B) `$user->createToken('admin')->accessToken`
- C) `$user->newToken('admin')`
- D) `Auth::token('admin')`

**Answer:** B) `$user->createToken('admin')->accessToken`

**Explanation:** Passport's `createToken()` method creates a personal access token, and we access the `accessToken` property to get the actual token string.

---

## Section 2: RESTful API Design & HTTP Methods

### Question 4: Resource Routes
**What does `Route::apiResource('users', UserController::class)` automatically create?**
- A) Only GET and POST routes
- B) GET (index, show), POST, PUT, DELETE routes
- C) Custom admin routes
- D) Only authenticated routes

**Answer:** B) GET (index, show), POST, PUT, DELETE routes

**Explanation:** `apiResource` creates 5 RESTful routes: index (GET), show (GET), store (POST), update (PUT/PATCH), and destroy (DELETE), excluding create and edit which are for web forms.

---

### Question 5: HTTP Status Codes
**What HTTP status code is returned when a new user is successfully created in the store method?**
- A) 200 OK
- B) 201 Created
- C) 202 Accepted
- D) 204 No Content

**Answer:** B) 201 Created

**Explanation:** The store method returns `201` status code, which is the standard HTTP code for successful resource creation.

---

### Question 6: Partial Routes
**What does the `only(['index','show'])` method do in this route definition?**
```php
Route::apiResource('orders', OrderController::class)->only(['index','show']);
```
- A) Creates all routes except index and show
- B) Creates only the index and show routes
- C) Makes only these routes public
- D) Applies middleware only to these routes

**Answer:** B) Creates only the index and show routes

**Explanation:** The `only()` method restricts the resource routes to just the specified actions, creating only GET /orders (index) and GET /orders/{id} (show).

---

## Section 3: Request Validation & Form Requests

### Question 7: Custom Form Requests
**Why does the store method use `CreateUserRequest` instead of the regular `Request` class?**
- A) For better performance
- B) To separate validation logic from controller
- C) It's required by Laravel
- D) To enable async validation

**Answer:** B) To separate validation logic from controller

**Explanation:** Form Request classes allow you to encapsulate validation rules, keeping controllers clean and following the Single Responsibility Principle.

---

### Question 8: Request Data Filtering
**What does the `only()` method do in this code?**
```php
$data = $request->only(['f_name', 'l_name', 'email']);
```
- A) Validates only these fields
- B) Returns only these fields from the request
- C) Makes only these fields required
- D) Encrypts only these fields

**Answer:** B) Returns only these fields from the request

**Explanation:** The `only()` method filters the request data to include just the specified fields, preventing mass assignment vulnerabilities.

---

### Question 9: Conditional Data Inclusion
**What does `$request->filled('password')` check for?**
- A) If password is hashed
- B) If password exists and is not empty
- C) If password meets requirements
- D) If password is validated

**Answer:** B) If password exists and is not empty

**Explanation:** The `filled()` method returns true if the field exists in the request and has a non-empty value, useful for optional updates.

---

## Section 4: Eloquent ORM & Database Operations

### Question 10: Model Property Protection
**What does `protected $guarded = ['id'];` mean in the User model?**
- A) The id field is encrypted
- B) All fields except id are mass assignable
- C) The id field cannot be updated
- D) Only id can be mass assigned

**Answer:** B) All fields except id are mass assignable

**Explanation:** `$guarded` specifies which attributes should NOT be mass assignable. With only 'id' guarded, all other fields can be mass assigned via create() or update().

---

### Question 11: Eloquent Relationships
**What type of relationship is defined in the User model?**
```php
public function role()
{
    return $this->belongsTo(Role::class);
}
```
- A) One-to-Many
- B) Many-to-One
- C) Many-to-Many
- D) One-to-One

**Answer:** B) Many-to-One

**Explanation:** `belongsTo` defines a many-to-one (inverse one-to-many) relationship, meaning many users belong to one role.

---

### Question 12: Finding Records
**What's the difference between `User::find($id)` and `User::findOrFail($id)`?**
- A) No difference
- B) find() returns null if not found, findOrFail() throws exception
- C) findOrFail() is faster
- D) find() is deprecated

**Answer:** B) find() returns null if not found, findOrFail() throws exception

**Explanation:** `find()` returns null when record doesn't exist, requiring manual checking. `findOrFail()` automatically throws a 404 exception, reducing boilerplate code.

---

## Section 5: Middleware & Route Protection

### Question 13: API Middleware
**What does `Route::middleware('auth:api')->group()` do?**
- A) Creates API routes
- B) Protects routes requiring authentication via API guard
- C) Enables API rate limiting
- D) Formats responses as JSON

**Answer:** B) Protects routes requiring authentication via API guard

**Explanation:** This middleware ensures that all routes in the group require valid API authentication (Passport token) to access.

---

### Question 14: Public Routes
**Why are login and register routes defined OUTSIDE the middleware group?**
- A) For better performance
- B) Because they need to be accessible without authentication
- C) It's a Laravel convention
- D) To enable CORS

**Answer:** B) Because they need to be accessible without authentication

**Explanation:** Users need to be able to register and login without already having a token - these are public endpoints.

---

## Section 6: API Responses & Resources

### Question 15: Resource Classes
**What is the purpose of `UserResources::collection($user)`?**
- A) To validate user collection
- B) To transform and format user data for API responses
- C) To cache user data
- D) To paginate users

**Answer:** B) To transform and format user data for API responses

**Explanation:** API Resources transform Eloquent models/collections into JSON structures, controlling exactly what data is exposed and how it's formatted.

---

### Question 16: JSON Responses
**What does `response()->json()` do differently than just returning an array?**
- A) Nothing, they're the same
- B) Sets Content-Type header to application/json and encodes data
- C) Validates the response
- D) Compresses the response

**Answer:** B) Sets Content-Type header to application/json and encodes data

**Explanation:** `response()->json()` ensures proper JSON encoding, sets the correct Content-Type header, and allows you to specify status codes.

---

### Question 17: Pagination
**What does `User::paginate(10)` return?**
- A) Array of 10 users
- B) LengthAwarePaginator instance with metadata
- C) Collection of 10 users
- D) First 10 users only

**Answer:** B) LengthAwarePaginator instance with metadata

**Explanation:** `paginate()` returns a paginator instance containing the current page of results plus metadata (total count, per page, current page, etc.).

---

## Section 7: Controller Best Practices

### Question 18: Return Types
**Why do all methods in UserController specify `: JsonResponse` return type?**
- A) Required by Laravel
- B) For type safety and better IDE support
- C) For performance optimization
- D) To enable caching

**Answer:** B) For type safety and better IDE support

**Explanation:** PHP return type declarations improve code quality by ensuring methods return expected types and provide better autocomplete/error detection in IDEs.

---

### Question 19: Authentication Helper
**What does `auth()->user()` return?**
- A) User ID
- B) Currently authenticated user instance
- C) User token
- D) User credentials

**Answer:** B) Currently authenticated user instance

**Explanation:** The `auth()->user()` helper returns the full User model instance of the currently authenticated user, or null if not authenticated.

---

### Question 20: Error Handling
**What happens if you try to update a non-existent user in the update method?**
```php
$user = User::find($id);
if (!$user) {
    return response()->json(['message' => 'User not found'], 404);
}
```
- A) Server error 500
- B) Returns 404 with error message
- C) Creates new user
- D) Returns empty response

**Answer:** B) Returns 404 with error message

**Explanation:** The code explicitly checks if the user exists and returns a 404 status code with a descriptive message if not found.

---

## Section 8: Route Organization & Grouping

### Question 21: Route Prefixes
**What does `Route::prefix('user')->group()` accomplish?**
- A) Protects user routes
- B) Adds '/user' to the beginning of all routes in the group
- C) Creates user middleware
- D) Enables user validation

**Answer:** B) Adds '/user' to the beginning of all routes in the group

**Explanation:** The `prefix()` method prepends the given string to all routes within the group, so `Route::get('user')` becomes '/user/user'.

---

### Question 22: Route File Organization
**Which file contains the API routes in a Laravel project?**
- A) web.php
- B) api.php
- C) routes.php
- D) app.php

**Answer:** B) api.php

**Explanation:** Laravel separates web and API routes - `routes/api.php` contains API routes which are automatically prefixed with '/api'.

---

## Section 9: Data Manipulation & Security

### Question 23: Mass Assignment Protection
**Why is it important to use `only()` when creating/updating users?**
- A) For validation
- B) To prevent mass assignment vulnerabilities
- C) For better performance
- D) Required by Laravel

**Answer:** B) To prevent mass assignment vulnerabilities

**Explanation:** Using `only()` prevents attackers from injecting unexpected fields (like is_admin, role_id) in requests, protecting against mass assignment attacks.

---

### Question 24: Password Updates
**In the updatePassword method, why isn't the password validated before hashing?**
```php
$data['password'] = bcrypt($request->input('password'));
```
- A) Validation should happen in a Form Request
- B) Passwords don't need validation
- C) bcrypt() validates automatically
- D) It's a bug

**Answer:** A) Validation should happen in a Form Request

**Explanation:** Best practice is to create a custom Form Request class for password validation rules, keeping the controller focused on business logic.

---

## Section 10: Advanced Concepts

### Question 25: Update vs Create
**What's the key difference between `User::create()` and `$user->update()`?**
- A) No difference
- B) create() makes new record, update() modifies existing
- C) update() is faster
- D) create() returns boolean

**Answer:** B) create() makes new record, update() modifies existing

**Explanation:** `create()` is a static method that inserts a new database record and returns the model instance. `update()` is an instance method that modifies an existing record.

---

### Question 26: API Token Scope
**What does the 'admin' string represent in `createToken('admin')`?**
- A) User role
- B) Token name/scope identifier
- C) Permission level
- D) Token expiration

**Answer:** B) Token name/scope identifier

**Explanation:** The string is a custom name for the token, useful for identifying and managing multiple tokens per user (e.g., 'admin-panel', 'mobile-app').

---

### Question 27: Route Method Limitation
**Why might you want to use `->only(['index','show'])` on the orders resource?**
- A) Performance optimization
- B) To prevent users from creating/updating/deleting orders via API
- C) Required for read-only models
- D) To enable caching

**Answer:** B) To prevent users from creating/updating/deleting orders via API

**Explanation:** Limiting routes to index and show creates a read-only API endpoint, useful when you want to display data but not allow modifications through the API.

---

### Question 28: Response Status Codes
**Why does the update method return 202 Accepted instead of 200 OK?**
- A) It's a Laravel convention
- B) To indicate the request was accepted but processing may not be complete
- C) It's likely a design choice to distinguish from GET requests
- D) Required by REST standards

**Answer:** C) It's likely a design choice to distinguish from GET requests

**Explanation:** While 200 OK is standard for successful updates, 202 Accepted can indicate asynchronous processing or simply be used to differentiate update operations from retrieval operations. The project uses it consistently for updates.

---

### Question 29: Authentication Attempt
**What does `Auth::attempt($request->only('email','password'))` do?**
- A) Creates a new user
- B) Validates credentials and logs user in if valid
- C) Returns user object
- D) Generates token

**Answer:** B) Validates credentials and logs user in if valid

**Explanation:** `attempt()` tries to authenticate using the provided credentials, returns true if successful and sets the authenticated user in the session/guard.

---

### Question 30: Model Hidden Attributes
**What is the purpose of the `$hidden` property in the User model?**
```php
protected $hidden = ['password', 'remember_token'];
```
- A) Hides fields from database
- B) Excludes fields from JSON serialization
- C) Encrypts the fields
- D) Makes fields non-queryable

**Answer:** B) Excludes fields from JSON serialization

**Explanation:** The `$hidden` property prevents specified attributes from being included when the model is converted to an array or JSON, protecting sensitive data like passwords from being exposed in API responses.

---

## Scoring Guide
- **27-30 correct:** Expert Level - Ready for production!
- **23-26 correct:** Advanced - Strong understanding
- **18-22 correct:** Intermediate - Good foundation
- **13-17 correct:** Beginner - Keep practicing
- **Below 13:** Review the fundamentals

---

## Additional Practice Challenges

### Challenge 1: Implement Role-Based Authorization
Add a middleware that checks if a user has permission to perform certain actions based on their role.

### Challenge 2: Add Request Validation
Create proper Form Request classes for all controller methods that are missing them (like updateInfo and updatePassword).

### Challenge 3: Improve Error Handling
Refactor the controllers to use `findOrFail()` instead of manual 404 checks to reduce code duplication.

### Challenge 4: Add API Versioning
Implement API versioning (e.g., /api/v1/users) to support multiple API versions.

### Challenge 5: Implement Rate Limiting
Add rate limiting to prevent API abuse on authentication endpoints.

---

## Resources for Further Learning
- Laravel Official Documentation: https://laravel.com/docs
- Laravel Passport Documentation: https://laravel.com/docs/passport
- RESTful API Design Best Practices
- PHP Type Declarations and Strict Typing
- API Resource Transformation Patterns

**Happy Learning! 🚀**
