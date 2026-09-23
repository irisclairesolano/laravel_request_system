# laravel_request_system

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

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
