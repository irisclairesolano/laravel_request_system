# Laravel Request System

## Brief Description

The Laravel Request System is a web application for managing and organizing user requests. It provides a Laravel-based foundation for submitting, tracking, and maintaining request records through a centralized system.

## Student Information

```text
CHUA, Kyla - Reviewer (inspects and tests the code)
SOLANO, Iris Claire D. - Driver (implements the change, maintains the policy, controller, routes, and views)

Course: BSIT
Year: 4th Year
Section: 4-3
```

## Software Requirements

- PHP
- Composer
- Laravel
- MySQL
- phpMyAdmin
- Git
- GitHub
- Web Browser

## Laravel Installation

Clone the repository and install its dependencies:

```powershell
git clone https://github.com/irisclairesolano/laravel_request_system.git
cd laravel-request-system
composer install
```

Create the environment file:

```powershell
cp .env.example .env
```

For Windows PowerShell, you can also use:

```powershell
Copy-Item .env.example .env
```

Generate the application key:

```powershell
php artisan key:generate
```

Configure the database connection in `.env`, including the database name, host, username, and password. Do not commit database passwords or other credentials to the repository.

Run the database migrations:

```powershell
php artisan migrate
```

## Database Name

```text
laravel-request-system
```

## Database Import Instructions

This project uses Laravel migrations, so the database structure can be recreated without importing a SQL file. After creating the database named `laravel-request-system` and configuring `.env`, run:

```powershell
php artisan migrate
```

## Team Setup

Team members can set up the project by following the Laravel Installation and Database Import Instructions above.

Make sure to:
- Use the database name `laravel-request-system`.
- Configure the local `.env` file with the correct MySQL settings.
- Run the required Laravel migrations.
- Do not commit the actual `.env` file because it may contain sensitive or local configuration.

## Request Data Model

The project includes a `requests` table for storing submitted requests.

### Migration

```powershell
php artisan make:migration create_requests_table
php artisan migrate
```

### Verification

```powershell
php artisan migrate:status
```

The `requests` table can also be inspected through phpMyAdmin.

### Request Fields

| Field | Type | Description |
| --- | --- | --- |
| id | bigint unsigned | Unique record identifier |
| requester_name | varchar(100) | Full name of the person submitting the request |
| requester_email | varchar(255) | Email address of the requester |
| item_name | varchar(150) | Name of the requested item |
| quantity | unsigned integer | Quantity requested |
| purpose | text | Reason or purpose for the request |
| status | varchar(20) | Request status, default `pending` |
| created_at | timestamp | Date and time the request was created |
| updated_at | timestamp | Date and time the request was last updated |

## User Stories

### Requester

As a requester, I want to submit a request with my information, requested item, quantity, and purpose, so that my request can be properly recorded and reviewed.

**Acceptance Criteria:**
- The requester must provide a name, email, item name, quantity, and purpose.
- The quantity must be greater than zero.
- A new request must have a default status of `pending`.

### Staff Reviewer

As a staff reviewer, I want to view the submitted request details and current status, so that I can properly review and process the request.

**Acceptance Criteria:**
- The staff reviewer can view the requester name, email, item, quantity, purpose, and status.
- Each request must have a unique ID.
- The request status must be available for checking.

### Record Keeper

As a record keeper, I want each request to have creation and update timestamps, so that I can track when a request was created or changed.

**Acceptance Criteria:**
- Each request must contain `created_at` and `updated_at`.
- Each request must be stored in the `requests` table.
- Each request must have a unique ID for identification.

## Request Authorization

All request routes require authentication. Students may create requests and list
only requests whose `user_id` matches their signed-in account. Request ownership
is determined by `user_id`, not by the requester name or email. Administrators
may list and view all requests, and may update a request status. Students cannot
set the owner, requester identity, role, or initial status: those values come
from the authenticated account and the server.

Every single-record read and status write is authorized on the server. A student
who requests another student's record receives **404 Not Found**, consistently,
so the response does not disclose whether that record exists. Other authorization
denials (including a student's status update) return **403 Forbidden**.

### Request Routes

| Method | Path | Access and behavior |
| --- | --- | --- |
| `GET` | `/requests` | Authenticated list; students see their own requests, administrators see all. |
| `POST` | `/requests` | Students create a request; server sets owner, requester identity, and `pending` status. |
| `GET` | `/requests/{serviceRequest}` | Owner or administrator only; another student's record returns 404. |
| `PATCH` | `/requests/{serviceRequest}/status` | Administrator only; accepts `pending`, `approved`, or `rejected`. |

Browser requests render escaped Blade pages and use CSRF-protected forms.
JSON clients can request JSON responses using the `Accept: application/json`
header. The status form uses PATCH method spoofing and submits through the same
authenticated, policy-protected route.

### File Responsibilities

| File | Responsibility |
| --- | --- |
| `app/Policies/ServiceRequestPolicy.php` | Defines list, view, create, and administrator status-update authorization. |
| `app/Providers/AppServiceProvider.php` | Explicitly registers the service-request model policy. |
| `app/Http/Controllers/ServiceRequestController.php` | Applies policy checks, validates allowlisted input, scopes lists by owner, and handles HTML/JSON responses. |
| `routes/web.php` | Declares authenticated request and profile routes under Laravel's `web` middleware, including CSRF protection. |
| `resources/views/requests/index.blade.php` | Displays the scoped list and student request form with `@csrf`. |
| `resources/views/requests/show.blade.php` | Displays escaped request details and the administrator status form with `@csrf` and `@method('PATCH')`. |
| `app/Models/ServiceRequest.php` | Maps the model to the `requests` table and explicitly allowlists assignable attributes. |
| `database/migrations/2026_09_30_215253_create_requests_table.php` | Defines Laboratory 2 request field types and lengths. |
| `database/migrations/2026_10_07_093407_add_user_id_to_requests_table.php` | Adds request ownership through `user_id`. |
| `tests/Feature/ServiceRequestAuthorizationTest.php` | Tests request policy, ownership scoping, creation, and status updates. |
| `tests/Feature/ServiceRequestAccessInputMatrixTest.php` | Exercises the T01–T10 access and input test cases. |
| `docs/LAB3-ACCESS-INPUT-TEST-MATRIX.md` | Records expected and actual results, pass/fail status, evidence, and live CSRF check instructions. |

## Verify the Requests Table

To verify that the `requests` table was created correctly:

1. Make sure the Laravel project is connected to MySQL.
2. Run:

```powershell
php artisan migrate
```

## Laboratory 3 Setup and Testing

Keep the Laboratory 1 database name, `laravel-request-system`, and configure
its local connection in `.env`. For a new local setup, create the database in
MySQL first, then run the migrations (and optional development seed data):

```powershell
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

Do not run `migrate:fresh` against a database containing work you need; it
drops existing tables. Use the PHPUnit suite for automated checks:

```powershell
php artisan test
php artisan test --filter=ServiceRequestAccessInputMatrixTest
```

The matrix report documents all ten cases and results:
[`docs/LAB3-ACCESS-INPUT-TEST-MATRIX.md`](docs/LAB3-ACCESS-INPUT-TEST-MATRIX.md).
Laravel normally bypasses CSRF middleware during PHPUnit runs; reproduce the
missing/invalid token cases using the isolated SQLite database and live local
HTTP-server steps in that report, not the regular project database.

Before sharing or deploying, set `APP_DEBUG=false` in the deployed environment.
The tracked `.env.example` contains placeholders only; `.env` and common local
environment variants are excluded by `.gitignore`. Never include credentials,
SQL errors, or debug stack traces in screenshots.

## Run the Project

Start the Laravel development server:

```powershell
php artisan serve
```

Open the project in a web browser at:

http://127.0.0.1:8000

##laboratory 3 verification 


Verification instruction: Test administrator access and administrator-only status updates. 

