# 🍢 Angkringan Asli Klaten — Management System

A modern, full-stack web application designed to streamline daily operations, inventory tracking, transaction processing, debt management, and financial analytics for **Angkringan Asli Klaten**.

---

## ✨ Key Features

### 📊 Interactive Dashboard & Financial Analytics
- **Visual Analytics**: Interactive charts powered by Chart.js displaying daily revenue, profit trends, and sales performance.
- **Business Overview**: Instant metrics for total daily sales, total items sold, outstanding debts, and cash flow balance.

### 🛒 Point of Sale & Transaction Management
- **Transaction Recording**: Real-time sales logging with item selection and automatic subtotal calculation.
- **Daily Transaction Logs**: Filter transactions by date and view comprehensive breakdown per order.
- **PDF Export**: Generate and download official daily transaction reports in PDF format.

### 📦 Inventory & Stock Management
- **Stock Tracking**: Real-time catalog management for food, snacks, and beverage inventory.
- **Price History Logging**: Track historical price changes for accurate profit estimation.
- **PDF Catalog Export**: Export printable inventory stock reports.

### 💳 Debt & Receivable Management (Pencatatan Hutang)
- **Credit Tracking**: Record and manage customer debts (hutang).
- **Payment Processing**: Process partial and full debt repayments with automatic balance updates.
- **User Debt Portal**: Customer-facing view (`my-hutang`) to monitor individual debt records.

### 💰 Cash Flow Management (Kas)
- **Cash Operations**: Track cash-in and cash-out operations for daily balance reconciliations.
- **Individual Kas Overview**: Shift-based or user-based cash view (`my-kas`) for transparent shift handovers.

### 🔐 Security & Access Control
- **OTP Verification (2FA)**: Two-Factor Authentication using One-Time Passwords during login.
- **Role-Based Authorization**: Distinct access permissions for **Admin** (full management & settings) and **Kasir/Staff** (transactions & personal records).

### 🌐 Modern Landing Page
- Public-facing showcase introducing Angkringan menu offerings, operating hours, location details, and branding.

---

## 🛠️ Tech Stack

### Backend
- **Framework**: [Laravel 13](https://laravel.com/) (PHP 8.2+)
- **Authentication**: Laravel Fortify + Custom OTP Verification Middleware
- **PDF Generation**: `barryvdh/laravel-dompdf`
- **Routing & Interfacing**: `laravel/wayfinder`

### Frontend
- **Framework**: [Vue 3](https://vuejs.org/) (Composition API) + [TypeScript](https://www.typescriptlang.org/)
- **Monolith SPA Bridge**: [Inertia.js v3](https://inertiajs.com/)
- **Build Tool**: [Vite 8](https://vitejs.dev/)
- **Styling**: [Tailwind CSS v4](https://tailwindcss.com/)
- **UI Components**: Reka UI, Lucide Vue Icons, SweetAlert2, Vue Input OTP
- **Data Visualization**: Chart.js

### Code Quality & Utilities
- **PHP Linter**: Laravel Pint
- **JS/TS Linter**: ESLint 9 + Prettier
- **Type Checking**: Vue TSC

---

## 📂 System Architecture Overview

```text
app/
├── Http/
│   ├── Controllers/       # Landing, Dashboard, Barang, Transaksi, Hutang, Kas, OTP
│   └── Middleware/        # Auth, Role, & OTP Verification Middleware
├── Models/                # User, Barang, Transaksi, Hutang, RiwayatHarga
resources/
├── js/
│   ├── Components/        # Reusable UI components (Reka UI / Shadcn-style)
│   ├── Layouts/           # AppLayout, AuthenticatedLayout
│   └── Pages/             # Vue pages (Landing, Dashboard, Transaksi, Hutang, Kas, Auth)
routes/
└── web.php                # Application route declarations with role guards
