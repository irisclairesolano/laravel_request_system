
# Laravel Request System

## Brief Description

The Laravel Request System is a web application for managing and organizing user requests. It provides a Laravel-based foundation for submitting, tracking, and maintaining request records through a centralized system.

## Student Information

```text
Student: Iris Claire Deverla Solano
Course: BSIT
Year: 4th Year
Section: [Your Section]
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
laravel_request_system_db
```

## Database Import Instructions

This project uses Laravel migrations, so the database structure can be recreated without importing a SQL file. After creating the database named `laravel_request_system_db` and configuring `.env`, run:

```powershell
php artisan migrate
```

Do not put your database password in this README.

## Run the Project

Start the Laravel development server:

```powershell
php artisan serve
```

Open the project in a web browser at:

http://127.0.0.1:8000
