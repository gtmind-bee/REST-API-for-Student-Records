# Student Records REST API

A token-authenticated REST API for managing student records. Built with **Laravel 13**, **Laravel Sanctum**, and **MySQL**.

## Features

- User registration and login that issue Sanctum personal access tokens.
- Bearer-token-protected student endpoints.
- Student create, read, update, and delete (CRUD) operations.
- Form Request validation and consistent JSON API resources.
- Paginated student listing (`per_page`, maximum 100), search, and filters.
- Per-account data isolation: users can only view and manage the student records they own.
- Current-token logout/revocation.
- Feature tests and an importable Postman collection.

## Requirements

- PHP 8.3 or newer with PDO MySQL enabled
- Composer 2
- MySQL 8.0+ (or a compatible MySQL server)

Laravel 13 requires PHP 8.3+. Sanctum 4 provides personal access tokens through the `Authorization: Bearer <token>` header. See the official [Laravel 13 release notes](https://laravel.com/docs/13.x/releases) and [Sanctum documentation](https://laravel.com/docs/13.x/sanctum).

## Installation

1. Create a MySQL database:

   ```sql
   CREATE DATABASE student_records CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Install PHP dependencies from the project root:

   ```bash
   composer install
   ```

3. Create your environment file and application key:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Update the database settings in `.env`:

   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=student_records
   DB_USERNAME=root
   DB_PASSWORD=your_mysql_password
   ```

5. Run the database migrations:

   ```bash
   php artisan migrate
   ```

6. Start the development server:

   ```bash
   php artisan serve
   ```

The API base URL is `http://127.0.0.1:8000/api`. The root URL (`http://127.0.0.1:8000/`) returns a small health/status response.

## Authentication

Register or log in to receive a personal access token. Include that token on every protected request:

```http
Authorization: Bearer YOUR_TOKEN
Accept: application/json
Content-Type: application/json
```

Tokens are stored hashed by Sanctum in `personal_access_tokens`. `POST /api/auth/logout` revokes only the token used for that request. Registering or logging in again issues an additional token; it does not automatically revoke existing tokens.

### Register

`POST /api/auth/register`

```json
{
  "name": "Ada Lovelace",
  "email": "ada@example.com",
  "password": "Password123!",
  "password_confirmation": "Password123!",
  "device_name": "Postman"
}
```

Successful response: `201 Created`, containing `data.user`, `data.token`, and `data.token_type`.

### Login

`POST /api/auth/login`

```json
{
  "email": "ada@example.com",
  "password": "Password123!",
  "device_name": "Postman"
}
```

Successful response: `200 OK`, containing a new bearer token.

### Current user and logout

- `GET /api/auth/user` — returns the authenticated user's profile.
- `POST /api/auth/logout` — revokes the current bearer token.

## Student endpoints

All student endpoints require `auth:sanctum`.

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/students` | List the authenticated user's students (paginated) |
| `POST` | `/api/students` | Create a student |
| `GET` | `/api/students/{id}` | View one student |
| `PUT` / `PATCH` | `/api/students/{id}` | Update a student |
| `DELETE` | `/api/students/{id}` | Delete a student |

### Create a student

`POST /api/students`

```json
{
  "admission_number": "STU-2026-001",
  "first_name": "Grace",
  "last_name": "Hopper",
  "email": "grace.hopper@example.com",
  "date_of_birth": "2002-12-09",
  "gender": "female",
  "department": "Computer Science",
  "level": "300",
  "status": "active"
}
```

Required fields: `admission_number`, `first_name`, `last_name`, `gender`, `department`, and `level`.

Optional fields: `email`, `date_of_birth`, and `status`. Gender must be `male`, `female`, or `other`. Status must be `active`, `inactive`, `graduated`, or `suspended`. Admission numbers and email addresses must be unique within each user's records. Omit `status` to use the database default (`active`).

### List, search, filter, and paginate

`GET /api/students?per_page=10&page=1&search=grace&department=Computer%20Science&level=300&status=active`

Supported query parameters:

| Parameter | Purpose | Validation |
|---|---|---|
| `per_page` | Results per page | Integer from 1 to 100; default 15 |
| `page` | Page number | Laravel paginator parameter |
| `search` | Searches admission number, first name, last name, and email | String, max 100 characters |
| `department` | Exact department filter | String, max 100 characters |
| `level` | Exact level filter | String, max 50 characters |
| `status` | Exact status filter | `active`, `inactive`, `graduated`, or `suspended` |

Paginated responses use the standard Laravel resource collection shape with `data`, `links`, and `meta` objects.

### Update a student

`PATCH /api/students/1`

Send only the fields to change:

```json
{
  "level": "400",
  "status": "active"
}
```

At least one updatable field is required. `PUT` and `PATCH` both accept partial updates in this implementation.

### Delete a student

`DELETE /api/students/1`

Returns `204 No Content` when successful.

## Response and error conventions

- `200 OK` — successful read, update, login, or logout.
- `201 Created` — successful registration or student creation.
- `204 No Content` — successful student deletion.
- `401 Unauthorized` — missing/invalid bearer token or invalid login credentials.
- `404 Not Found` — record does not exist or belongs to a different user.
- `422 Unprocessable Entity` — validation errors, returned with `message` and an `errors` object.

Example validation response:

```json
{
  "message": "The admission number field is required.",
  "errors": {
    "admission_number": ["The admission number field is required."]
  }
}
```

## Postman

Import `postman/Student-Records-API.postman_collection.json` into Postman. The collection includes registration, login, current-user, student list/create/show/update/delete, and logout requests. It stores the bearer token returned by registration/login and the student ID returned by student creation as collection variables.

1. Start the Laravel server.
2. Open the collection's variables and confirm `baseUrl` is `http://127.0.0.1:8000/api`.
3. Run **Register** once. The request saves a bearer token automatically. The sample email is `student.api@example.com`; change it in the collection variables before registering again if that address already exists.
4. Run the remaining requests in order. Login can be used to refresh the collection token.

The collection sends `Accept: application/json` and uses bearer authentication for protected requests.

## Automated tests

Tests use SQLite in-memory and do not require your MySQL database. After `composer install`, run:

```bash
php artisan test
```

The feature tests cover registration, login, token revocation, validation, protected routes, student CRUD, pagination/filtering, and account-level record isolation.

## Project structure

```text
app/
  Http/
    Controllers/Api/       AuthController, StudentController
    Requests/              Store, update, and index validation
    Resources/             StudentResource
  Models/                  User, Student
database/
  factories/                UserFactory, StudentFactory
  migrations/               users, Sanctum tokens, students
routes/
  api.php                   API routes
postman/
  Student-Records-API.postman_collection.json
tests/Feature/              API feature tests
```

## Security notes

- Passwords are hashed before storage; the User model also uses Laravel's hashed password cast.
- Sanctum persists only hashed token values. The plain-text token is returned only at token creation.
- Protected student queries are scoped through the authenticated user's `students()` relationship. An ID belonging to another user returns `404` rather than exposing that the record exists.
- Authentication endpoints are rate-limited to 10 attempts per minute.
- Use HTTPS and strong, unique passwords when deploying. Configure database credentials through environment variables and never commit `.env` or real access tokens.
