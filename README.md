# Hardware IMS

A Laravel inventory and sales management system built for a hardware store, covering everything from purchasing to point-of-sale.

## What it does

- Products organized by department, category and brand, with per-department custom attribute schemas (so a "Paint" department and a "Tools" department can each define their own product fields)
- Multi-warehouse stock, with purchase orders, stock adjustments (with an approval step) and stock transfers between warehouses
- Barcode/label generation per product variant
- Point of sale (POS) for walk-in sales, with its own sales history and printable receipts
- Quotes that can be sent to a customer, accepted/rejected, and converted into an order
- Orders with a fulfillment workflow (confirm, partially fulfill, reject, progress) and PDF export
- Pricing calculators with configurable formulas and input/output field mapping (for products priced by custom specs rather than a flat price)
- Role-based permissions (departments, products, warehouses, suppliers, stock, orders, quotes, POS, users all gated separately)
- Analytics dashboard and audit log
- Notifications

## Stack

- Laravel 12, PHP 8.2+
- MySQL
- Blade + Vite
- spatie/laravel-permission for roles/permissions
- barryvdh/laravel-dompdf for PDF export (orders, quotes, POS receipts)
- picqer/php-barcode-generator for barcodes/labels

## Note on this copy

The original project has a licensing module (`license.check` / `license.feature` middleware, gating things like the analytics dashboard, quotes and POS behind license tiers) backed by a private internal package. That package isn't included here, so those specific middleware won't resolve as-is - remove or stub them out if you want to run this standalone.

## Getting it running locally

```bash
git clone https://github.com/thuanthropist/HARDWARE_IMS.git
cd HARDWARE_IMS
composer install
npm install
```

Copy the env file and generate an app key:

```bash
cp .env.example .env
php artisan key:generate
```

Set your database credentials in `.env`, then:

```bash
php artisan migrate --seed
npm run dev
php artisan serve
```

The app will be available at `http://localhost:8000`.

## Running tests

```bash
php artisan test
```
