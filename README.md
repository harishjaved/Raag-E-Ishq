# Sagar Kinare — Event Operations Platform

A production-grade, mobile-first event ticketing and back-office operations platform built for **Joygivers** for the musical evening **Sagar Kinare** in Kochi, Kerala.

---

## 1. Architectural Highlights

* **100% Server-Authoritative Pricing:** All calculations are performed server-side in Indian paise. Client-supplied price tampering is mathematically impossible.
* **Shared 450-Capacity Pool:** Row-level pessimistic locking (`SELECT ... FOR UPDATE`) guarantees that concurrent purchase bursts (e.g. 10+ simultaneous requests for the last available seat) can **never oversell** beyond the 450 total ceiling or tier caps (Tier 1: 100 @ ₹2,500; Tier 2: 100 @ ₹1,500; Tier 3: 250 @ ₹1,000).
* **Payment QR vs. Admission QR Disconnect:** Educational clarity in UX and complete cryptographic separation in backend architecture.
  * **Payment QR:** Ephemeral UPI payment payload (`upi://pay?...`) with a strictly matched 10-minute hold TTL.
  * **Admission QR:** Single-use, high-entropy cryptographic token (`HMAC-SHA256(secret, ticket_id + nonce) + UUIDv4`), containing zero customer PII.
* **Signature-Verified Webhook Ingestion:** Webhook payloads are verified against the raw HTTP request body bytes (`hash_hmac`) and deduplicated via `gateway_webhook_events` to ensure idempotency.
* **Atomic Mobile Check-in:** Database `UNIQUE KEY (ticket_id)` on the `checkins` table guarantees that two phones scanning the identical QR ticket at the exact same millisecond will result in exactly **one Green (Admitted)** and **one Orange (Already Checked In)**.
* **Strict Backend RBAC:** 4 distinct roles (`SUPER_ADMIN`, `EVENT_MANAGER`, `FINANCE`, `GATE_STAFF`). Gate staff have zero access to financial ledgers, exports, or customer contact books.

---

## 2. Technology Stack

* **Language & Framework:** PHP 8.3+ / Laravel 12.x (Stable production LTS baseline).
* **Database:** MySQL 8.0 InnoDB (with strict relational integrity, foreign keys, and row-level locking).
* **Frontend:** Laravel Blade, HTML5, Vanilla JavaScript, Tailwind CSS (Mobile-First responsive design), Chart.js.
* **Gate Scanner:** Browser-native JavaScript camera stream (`html5-qrcode`) with real-time Web Audio API synthesized feedback.
* **Queues & Scheduler:** Laravel Queues (database/redis) with automatic exponential backoff for transactional emails; system scheduler for hold expiry cleanup.
* **Payment Gateway:** Razorpay (Dynamic single-use UPI QR code + mobile intent/checkout fallback).

---

## 3. Directory Structure

```text
├── app/
│   ├── Console/Commands/       # Hold expiration daemon command
│   ├── Exceptions/             # OutOfStockException
│   ├── Http/Controllers/
│   │   ├── Admin/              # Dashboard, CRM, Bookings, Payments, Allocations, Reports, Audit
│   │   ├── Auth/               # Login and Session management
│   │   ├── Public/             # Event page, Registration, Payment, Confirmation, Download, Resend
│   │   ├── Staff/              # Mobile Gate Scanner and manual lookup
│   │   └── Webhooks/           # Razorpay signature-verified raw webhook receiver
│   ├── Http/Middleware/        # RoleMiddleware (RBAC enforcement)
│   ├── Http/Requests/          # Form request validations (anti-tampering)
│   ├── Jobs/                   # Queued transactional email delivery with retry protection
│   ├── Mail/                   # Ticket confirmation Mailable
│   ├── Models/                 # 16 Eloquent models with strict typing and relationships
│   └── Services/               # Domain services: Inventory, Razorpay, Ticket, Checkin, Analytics
├── database/
│   ├── migrations/             # 8 comprehensive relational migrations
│   └── seeders/                # DatabaseSeeder (Default Event, 3 Tiers, 4 RBAC accounts, Settings)
├── docs/                       # Complete operational runbooks, matrices, and guides
│   ├── DATABASE_ERD.md
│   ├── DEPLOYMENT_HOSTINGER_VPS.md
│   ├── METRIC_DEFINITIONS.md
│   ├── OPEN_ISSUES_REGISTER.md
│   ├── RAZORPAY_INTEGRATION_NOTES.md
│   ├── ROUTE_AND_ROLE_MATRIX.md
│   ├── STAFF_OPERATING_GUIDE.md
│   └── TEST_REPORT.md
└── tests/
    └── Feature/                # 35 feature tests covering concurrency, webhooks, gate races, and E2E
```

---

## 4. Local Quick Start & Verification

### Prerequisites
* PHP 8.3+ with extensions: `pdo_mysql`, `pdo_sqlite`, `curl`, `mbstring`, `openssl`, `fileinfo`, `gd`, `zip`.
* Composer 2.x
* MySQL 8.0 Community Server

### Installation
```bash
# 1. Install dependencies
composer install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Run database migrations & seeders
php artisan migrate:fresh --seed

# 4. Execute test suite
php artisan test
```

### Default Seeded Accounts
All accounts share the default development password: `Password123!`

* **Super Admin:** `superadmin@joygivers.org` (Full system, user management, and audit logs)
* **Event Manager:** `manager@joygivers.org` (CRM, bookings, seat allocations, and gate operations)
* **Finance Officer:** `finance@joygivers.org` (Payment ledger, exception reconciliation, CSV settlement export)
* **Gate Staff:** `gate@joygivers.org` (Mobile camera QR check-in and audited manual lookup only)

---

## 5. Automated Test Suite

Run the full automated test suite containing **35 tests and 267 assertions**:

```bash
php artisan test
```

Test coverage includes:
1. `Phase1FoundationTest`: Verification of all 16 tables, 450 capacity quota sum, and RBAC permissions.
2. `Phase2RegistrationAndConcurrencyTest`: Anti-price-tampering, input validation, hold expiration, and **15-buyer concurrent race on the last seat**.
3. `Phase3RazorpayWebhookTest`: Forged signature rejection (400), duplicate delivery idempotency, out-of-order deliveries, amount mismatch routing, and late payment reconciliation.
4. `Phase4TicketIssuanceAndSecurityTest`: 1 ticket per admission, HMAC signature verification, signed URL protection, and zero PII leakage.
5. `Phase5GateOperationsTest`: Atomic gate check-in, **double-scan simultaneous race prevention**, orange duplicate detection, void ticket rejection, and audited manual lookup.
6. `Phase6BackOfficeAndAnalyticsTest`: RBAC denial verification across all 4 roles, dashboard metric calculations against fixtures, sponsor allocations, and sanitized CSV exports.
7. `EndToEndLifecycleTest`: Full end-to-end integration: `Guest link -> Registration -> CRM -> Payment QR -> Webhook -> Admission QR -> Signed Download -> Mobile Gate Scan -> Duplicate Scan -> Dashboard Accuracy`.
