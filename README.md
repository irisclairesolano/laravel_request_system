
# Laravel Request System

## Brief Description

The Laravel Request System is a web application for managing and organizing user requests. It provides a Laravel-based foundation for submitting, tracking, and maintaining request records through a centralized system.

## Student Information

```text
Students:
- Iris Claire Deverla Solano
- Kyla Chua

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

This project uses Laravel migrations, so the database structure can be recreated without importing a SQL file. After creating the database named ` laravel-request-system` and configuring `.env`, run:

```powershell
php artisan migrate
```

## Team Setup

Team members can set up the project by following the Laravel Installation and Database Import Instructions above.

Make sure to:
- Use the database name ` laravel-request-system`.
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

## Verify the Requests Table

To verify that the `requests` table was created correctly:

1. Make sure the Laravel project is connected to MySQL.
2. Run:

```powershell
php artisan migrate


## Run the Project

Start the Laravel development server:

```powershell
php artisan serve
```

Open the project in a web browser at:

http://127.0.0.1:8000
