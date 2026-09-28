# Baseline and Security Tasks

This checklist is the practical starting point for the Laravel API. Complete the tasks from top to bottom.

## Testing Basics

A test sends a request to the application and checks the result automatically.

Example:

```php
$response = $this->postJson('/api/v1/login', [
    'email' => 'user@example.com',
    'password' => 'password',
]);

$response->assertStatus(200);
```

The test passes when the application behaves as expected. A feature test checks a complete HTTP request. A unit test checks one small class or rule. Start with feature tests because this project is an API.

Run the tests with:

```bash
php artisan test
```

Run one test file with:

```bash
php artisan test tests/Feature/AuthTest.php
```

## Phase 0: Baseline

### Task 1: Run Existing Tests

- [x] Run `php artisan test`.
- [x] Record the number of passing and failing tests.
- [ ] Do not hide or skip failures.
- [ ] Fix only failures that belong to the current application behavior.

Current result: 21 tests passed with 60 assertions.

Done when the current test result is recorded, understood, and no unexplained failures remain.

### Task 2: Inventory API Routes

- [ ] Run `php artisan route:list`.
- [ ] List every route from `routes/api.php`.
- [ ] Record its HTTP method and URL.
- [ ] Record whether authentication is required.
- [ ] Record the required role, if any.
- [ ] Record the expected success status and response.

Done when every API route is documented.

### Task 3: Review Authentication

Understand these existing flows:

- [ ] Login creates a Passport access token.
- [ ] Registration creates a Passport access token.
- [ ] Login creates the `jwt` cookie.
- [ ] Logout forgets the `jwt` cookie.
- [ ] Logout revokes the current Passport token.
- [ ] A revoked token cannot call a protected endpoint.
- [ ] Passport token lifetime is known and documented.
- [ ] Refresh-token behavior is known, or confirmed unused.

Done when logout is tested for real token invalidation, not only for a successful response.

### Task 4: Document the Database

- [ ] List the tables.
- [ ] List each model and its relationships.
- [ ] Identify user ownership of orders.
- [ ] Identify order ownership of order items.
- [ ] Identify product relationships.
- [ ] Record important foreign keys and indexes.

Done when the current database structure is understandable without reading every model.

### Task 5: Define Response Conventions

Decide and document:

- [ ] Successful list response format.
- [ ] Successful single-resource response format.
- [ ] Create response status, normally `201`.
- [ ] Update response status, normally `200`.
- [ ] Delete response status.
- [ ] Validation error format, normally `422`.
- [ ] Unauthenticated response, normally `401`.
- [ ] Forbidden response, normally `403`.
- [ ] Not-found response, normally `404`.

Done when similar endpoints return errors and success responses consistently.

## Phase 1: Security Tests

### Task 6: Test Registration

Existing tests cover some of this. Confirm these cases:

- [ ] Valid registration returns `201`.
- [ ] Missing required fields returns `422`.
- [ ] Invalid email returns `422`.
- [ ] Duplicate email returns `422`.
- [ ] Mismatched password confirmation returns `422`.
- [ ] Password is stored hashed, never as plain text.
- [ ] Response does not expose the password.

### Task 7: Test Login

- [ ] Valid credentials return `200`.
- [ ] Valid credentials return an access token.
- [ ] Valid credentials create the expected cookie.
- [ ] Wrong password returns `401`.
- [ ] Unknown email returns `401`.
- [ ] Missing fields return `422`.
- [ ] Invalid email format returns `422`.
- [ ] Response does not expose the password.

### Task 7A: Test Logout Token Revocation

- [ ] Log in and save the Passport token.
- [ ] Call logout using that token.
- [ ] Call `/api/v1/profile` using the same token.
- [ ] Confirm the old token returns `401`.
- [ ] Confirm logout does not revoke another user's token.

### Task 8: Test Logout

This is the most important current security task.

- [ ] Unauthenticated logout returns `401`.
- [ ] Authenticated logout returns the chosen success status.
- [ ] Logout forgets the `jwt` cookie.
- [ ] Logout revokes the current Passport access token.
- [ ] The old token fails on a protected endpoint after logout.
- [ ] Logout does not revoke another user's token.
- [ ] Logout works safely if the cookie is already missing.

The key test is:

1. Log in and save the token.
2. Call logout using that token.
3. Call a protected endpoint using the same token.
4. Expect `401`.

### Task 9: Test Authorization

For each protected area, test both allowed and denied users:

- [ ] Guest receives `401`.
- [ ] Normal user cannot use admin endpoints.
- [ ] Editor can use editor-authorized product endpoints.
- [ ] Admin can use admin endpoints.
- [ ] A user cannot change another user's protected data.
- [ ] A user cannot assign themselves an admin role.
- [ ] Forbidden access returns `403`.

### Task 10: Test Validation

For each FormRequest:

- [ ] Test valid input.
- [ ] Test missing required fields.
- [ ] Test invalid data types.
- [ ] Test maximum lengths.
- [ ] Test minimum numeric values.
- [ ] Test password confirmation.
- [ ] Test invalid image types and sizes.
- [ ] Test whether update fields are required or optional.

### Task 11: Review Sensitive Data

- [ ] Passwords are hidden from API resources.
- [ ] Password hashes are never returned.
- [ ] Access tokens are not written to logs.
- [ ] Refresh tokens are not written to logs.
- [ ] Provider secrets are not returned.
- [ ] Payment credentials are never stored.
- [ ] Unexpected exceptions do not reveal secrets.

### Task 12: Review Mass Assignment

Inspect each model's `$fillable` or `$guarded` fields.

- [ ] Users cannot submit a protected `role_id` without authorization.
- [ ] Users cannot submit a password hash.
- [ ] Users cannot change ownership fields.
- [ ] Users cannot change payment status or amount.
- [ ] Add a negative test for each sensitive field.

## Feature Endpoint Tests

### Task 13: Test Products

- [ ] Authenticated admin/editor can list products.
- [ ] Unauthenticated users cannot list products.
- [ ] Authorized user can show one product.
- [ ] Missing product returns `404`.
- [ ] Authorized user can create a product.
- [ ] Invalid product data returns `422`.
- [ ] Authorized user can update a product.
- [ ] Authorized user can delete a product.
- [ ] Normal users cannot create, update, or delete products.

### Task 14: Test Roles and Permissions

- [ ] Admin can list roles.
- [ ] Admin can create a role.
- [ ] Admin can assign permissions to a role.
- [ ] Admin can update a role.
- [ ] Admin can delete a role.
- [ ] Non-admin users receive `403`.
- [ ] Admin can list permissions.
- [ ] Admin can create a permission.
- [ ] Invalid role and permission data returns `422`.

### Task 15: Test Orders

- [ ] Authenticated user can list orders.
- [ ] Authenticated user can show an order.
- [ ] Missing order returns `404`.
- [ ] Users cannot access another user's orders.
- [ ] Authorized user can export orders as CSV.
- [ ] Unauthenticated users cannot access orders or exports.

### Task 16: Test Image Uploads

- [ ] Authorized admin/editor can upload a valid image.
- [ ] Upload response contains filename and URL.
- [ ] Invalid file type returns `422`.
- [ ] Oversized image returns `422`.
- [ ] Normal users cannot upload images.

### Task 17: Test Dashboard Chart

- [ ] Authorized admin/editor can retrieve chart data.
- [ ] Chart response contains date and total values.
- [ ] Normal users cannot retrieve chart data.
- [ ] Unauthenticated users cannot retrieve chart data.

## Beginner Workflow

For every test:

1. Give the test a name describing one behavior.
2. Arrange the data, such as creating a user.
3. Act by calling the API endpoint.
4. Assert the status code.
5. Assert important JSON fields or database changes.
6. Run the test.
7. Fix the application or test when the result is unexpected.

Example structure:

```php
public function test_guest_cannot_view_protected_data(): void
{
    $response = $this->getJson('/api/v1/profile');

    $response->assertStatus(401);
}
```

## Useful Commands

```bash
php artisan test
php artisan test tests/Feature/AuthTest.php
php artisan route:list
php artisan migrate:fresh --env=testing
php artisan l5-swagger:generate
```

Do not start payment implementation until the authentication, authorization, validation, and baseline tests are understood.

## Phase 0 Definition of Done

Phase 0 is complete when:

- [ ] Existing tests have been run and understood.
- [ ] All API routes are documented.
- [ ] Authentication behavior is documented.
- [ ] Logout revocation is proven by a test.
- [ ] Authorization behavior has positive and negative tests.
- [ ] Validation behavior has tests.
- [ ] Sensitive data exposure has been reviewed.
- [ ] Database relationships are documented.
- [ ] API response conventions are documented.

Only then move to payment-domain design.
