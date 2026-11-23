# Route Inventory and RBAC Permissions Matrix

All role-based access controls are strictly enforced on the **PHP backend** via `App\Http\Middleware\RoleMiddleware`.

---

## 1. Role Capabilities Summary

| Capability | Super Admin | Event Manager | Finance Officer | Gate Staff | Guest (Public) |
|---|:---:|:---:|:---:|:---:|:---:|
| **Public Registration & Payment** | Yes | Yes | Yes | Yes | Yes |
| **Download Own Ticket (Signed URL)** | Yes | Yes | Yes | Yes | Yes |
| **Gate QR Scanner UI** | Yes | Yes | No | Yes | No |
| **Manual Gate Check-in (Audited)** | Yes | Yes | No | Yes | No |
| **Overview Dashboard** | Yes | Yes | Yes | No | No |
| **CRM Contact Directory** | Yes | Yes | No | No | No |
| **Bookings Ledger** | Yes | Yes | Yes | No | No |
| **Complimentary Allocations** | Yes | Yes | No | No | No |
| **Payments Ledger & Reconciliation** | Yes | No | Yes | No | No |
| **Finance Settlement CSV Export** | Yes | No | Yes | No | No |
| **Immutable Audit Log Viewer** | Yes | No | No | No | No |

---

## 2. Route Endpoints Inventory

### A. Public Endpoints
| HTTP Method | URI | Controller Action | Middleware | Notes |
|---|---|---|---|---|
| `GET` | `/` | `Public\EventController@show` | `web` | Public event page, tier list, and availability |
| `POST` | `/register` | `Public\RegistrationController@store` | `web` | Creates contact, booking, and 10-min hold |
| `GET` | `/booking/{reference}/payment` | `Public\PaymentController@show` | `web` | UPI QR and mobile payment intent |
| `GET` | `/booking/{reference}/status` | `Public\PaymentController@status` | `web` | Polling endpoint for payment confirmation |
| `GET` | `/booking/{reference}/confirmed` | `Public\ConfirmationController@show` | `web` | Verified payment success & ticket list |
| `GET` | `/tickets/download/{uuid}` | `Public\TicketDownloadController@download` | `web, signed` | Signed URL for clean printable pass (No PII) |
| `GET` | `/tickets/resend` | `Public\TicketResendController@show` | `web` | Self-service ticket recovery form |
| `POST` | `/tickets/resend` | `Public\TicketResendController@send` | `web, throttle:5,10` | Rate-limited ticket email dispatch |

### B. Gateway Webhooks
| HTTP Method | URI | Controller Action | Security | Notes |
|---|---|---|---|---|
| `POST` | `/webhooks/razorpay` | `Webhooks\RazorpayWebhookController@handle` | Raw byte signature verification | Exempt from CSRF; deduplication table |

### C. Authentication
| HTTP Method | URI | Controller Action | Notes |
|---|---|---|---|
| `GET` | `/login` | `Auth\AuthController@showLogin` | Mobile-friendly login form |
| `POST` | `/login` | `Auth\AuthController@login` | Session authentication |
| `POST` | `/logout` | `Auth\AuthController@logout` | Invalidate and flush session |

### D. Staff Gate Operations
| HTTP Method | URI | Controller Action | Required Role | Notes |
|---|---|---|---|---|
| `GET` | `/staff/scan` | `Staff\ScanController@index` | `auth, role:scan` | Mobile browser camera scanner |
| `POST` | `/staff/scan/verify` | `Staff\ScanController@verify` | `auth, role:scan` | Atomic single-admission verify |
| `POST` | `/staff/lookup/manual` | `Staff\ScanController@manualLookup` | `auth, role:scan` | Audited manual check-in with reason |

### E. Authenticated Back Office
| HTTP Method | URI | Controller Action | Required Role | Notes |
|---|---|---|---|---|
| `GET` | `/admin/dashboard` | `Admin\DashboardController@index` | `auth, role:admin` | Real-time KPI cards & charts |
| `GET` | `/admin/crm/contacts` | `Admin\ContactController@index` | `auth, role:admin` | Registrants list & search |
| `GET` | `/admin/crm/contacts/{id}` | `Admin\ContactController@show` | `auth, role:admin` | Contact profile & order history |
| `GET` | `/admin/bookings` | `Admin\BookingController@index` | `auth, role:admin` | Bookings list with status filters |
| `GET` | `/admin/bookings/{id}` | `Admin\BookingController@show` | `auth, role:admin` | Full booking detail & audit logs |
| `GET` | `/admin/allocations` | `Admin\AllocationController@index` | `auth, role:admin` | Comps & sponsor blocks list |
| `POST` | `/admin/allocations` | `Admin\AllocationController@store` | `auth, role:admin` | Create new comp allocation |
| `GET` | `/admin/payments` | `Admin\PaymentController@index` | `auth, role:finance` | Gateway payment ledger |
| `POST` | `/admin/payments/{id}/reconcile` | `Admin\PaymentController@reconcile` | `auth, role:finance` | Manual exception resolution |
| `GET` | `/admin/reports/sales` | `Admin\ReportController@sales` | `auth, role:finance` | Sales and revenue report |
| `GET` | `/admin/reports/export` | `Admin\ReportController@export` | `auth, role:finance` | Sanitized CSV settlement stream |
| `GET` | `/admin/audit-logs` | `Admin\AuditLogController@index` | `auth, role:super_admin` | Immutable system audit trail |
