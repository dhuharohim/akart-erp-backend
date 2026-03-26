# Backend API

Laravel 12 backend for Akart Event ERP.

## Modules

- Auth and session token (Sanctum)
- Event and timeline management
- Vendor and employee management
- Purchase order and invoice flow
- Payment tracking and KPI reports
- Document upload and PDF generation
- Role and permission management

## Architecture

- `app/Repositories`: data access abstraction
- `app/Services`: business logic and orchestration
- `app/Http/Controllers/API/V1`: versioned API endpoints
- `app/Policies`: model authorization policies

## Local Run

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

## Main Routes

- `POST /api/v1/auth/login`
- `POST /api/v1/auth/register`
- `GET /api/v1/events`
- `GET /api/v1/vendors`
- `GET /api/v1/employees`
- `GET /api/v1/purchase-orders`
- `GET /api/v1/invoices`
- `GET /api/v1/payments`
- `GET /api/v1/reports/kpis`

## Seeded Access

- Email: `admin@akart.test`
- Password: `password`
