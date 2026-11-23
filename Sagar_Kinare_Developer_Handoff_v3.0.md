# SAGAR KINARE — Developer Handoff Note

**Project:** Joygivers / Sagar Kinare — Event Website, RSVP, CRM, UPI Payment, QR Tickets & Entry Management  
**Prepared by:** TRIKON Ecosystem  
**Document version:** 3.0  
**Date:** 23 November 2026  
**Status:** Development handoff; product requirements defined, commercial/event decisions pending  
**Intended readers:** PHP/Laravel developers, UI developers, QA, DevOps, and coding agents

> **Read this first.** This handoff incorporates the latest client-requested **registration → CRM → per-booking UPI payment QR → verified payment → event QR ticket → mobile check-in** journey. It updates the prior HTML/PHP Agent Pack v2.0 where that pack assumed a conventional Razorpay Checkout flow as the primary method. Do not equate a UPI **payment QR** with the separate **event admission QR**. Never issue a paid ticket based only on a screenshot, client-side redirect, or an unverified claim of payment.

---

## 1. Project objective

Build **one secure, mobile-first website** that serves two purposes:

1. **Audience website:** Joygivers shares a link; guests discover Sagar Kinare, complete RSVP/registration, receive a booking-specific UPI payment option, pay, and receive **one scannable event ticket per admission**.
2. **Back-office web application:** Organisers log in to manage registrants (CRM), bookings, payments, ticket inventory, tickets, complimentary/sponsor seats, entry scanning, and live analytics.

On the event day, authorised staff should use **their normal mobile phones and the same website** to scan tickets and mark entry. No native Android/iOS app is required for MVP.

### Product success criteria

- Every registration is recorded in the CRM with a clear booking and payment status.
- A paid event ticket is issued **only after authenticated gateway confirmation of a captured/successful payment** and stock validation.
- Each admission receives a **unique, unpredictable event-ticket QR**.
- A QR ticket can be successfully checked in **once**, never twice.
- Paid, sponsor, complimentary and organiser-held admissions all count toward the **450-capacity** ceiling.
- The dashboard accurately reports registrations, payment conversions, revenue, ticket issuance and actual attendance.
- The entire system works on common mobile screens and can be operated by nontechnical event staff.

---

## 2. Event configuration: known facts versus open decisions

| Field | Current information | Status |
|---|---|---|
| Organiser | Joygivers | Supplied |
| Working event name | Sagar Kinare | Working name; public identity approval pending |
| Event type | Live Hindi and Malayalam musical evening supporting an education initiative | Proposal context |
| Location | Kochi, Kerala | Supplied |
| Venue | **Chavara Hall** | ~95% finalised; **not formally confirmed** |
| Maximum admission capacity | **450** | Client-supplied hard ceiling |
| Ticket tier 1 | 100 × ₹2,500 | Client-supplied |
| Ticket tier 2 | 100 × ₹1,500 | Client-supplied |
| Ticket tier 3 | 250 × ₹1,000 | Client-supplied |
| Full face-value potential | **₹6,50,000** | Calculation only; **not actual revenue** |
| Payment integration | Razorpay / UPI QR-based booking | Requested; merchant & QR feature availability unverified |
| Event date and timings | Not supplied | **Open / launch blocker** |
| Seat selection | Numbered seating vs category-only admission not decided | **Open / design decision** |
| Site domain | `joygivers.org` was mentioned previously but not verified | **Open** |
| Tax treatment, included fees, refund policy | Unconfirmed | **Open / launch blockers** |
| Event logo / approved assets | In design exploration | **Open** |

**Inventory principle:** The 450 places are **one shared admission pool**, divided among the three categories. Tickets reserved for sponsors, VIPs, organisers or complimentary entry are **deducted from those category allocations**. Never create 450 paid tickets *plus* complimentary seats.

**Important context:** The older *Surmai* draft proposed JT PAC and 500–600 attendees. Those are **superseded planning assumptions**, not current event configuration.

---

## 3. Mandatory technology and deployment preference

| Layer | Choice |
|---|---|
| Guest pages and staff UI | HTML5, CSS3 (custom or Tailwind), JavaScript |
| Server-rendered views | Laravel Blade (recommended) |
| Server | **PHP 8.3+**, using a maintained Laravel version compatible with the VPS (prefer Laravel 13, verify installed compatibility) |
| Data storage | **MySQL 8/InnoDB** |
| Payment provider | Razorpay; **booking-specific UPI QR**, subject to merchant feature support |
| Event admission tickets | Server-generated high-entropy token + QR image/PDF |
| Gate scanner | Mobile browser JavaScript QR scanner + authenticated PHP verification endpoint |
| Analytics charts | Server-aggregated MySQL data + Chart.js or equivalent |
| Email | Authenticated transactional email service/SMTP + queued jobs |
| Hosting | TRIKON-approved **Hostinger VPS KVM2**, after configuration review |
| Web server | Nginx/Apache with PHP-FPM, HTTPS |
| Background jobs | Laravel queue workers and scheduler (Supervisor/systemd or equivalent) |

**Do not switch to React/Next.js, a Node.js API, PostgreSQL or a native phone app** unless the technical lead explicitly approves a revised scope. JavaScript build tooling is acceptable for static frontend assets if needed.

Do not reuse production database credentials, payment secrets or real customer contact information in local or test environments.

---

## 4. End-to-end audience journey (MVP)

### Step A — Link distribution & event discovery

- Joygivers shares a **public event URL** via WhatsApp, email, social posts, or direct invitation.
- Page shows approved event logo, description, date/time/venue **when confirmed**, ticket categories, price breakdown, policies and a clear **Register / Book Ticket** action.
- Track permitted `utm_source`, `utm_medium`, `utm_campaign`, `utm_content` and registration referrer; do **not** put sensitive guest details in campaign tracking URLs.

### Step B — Registration / RSVP and CRM creation

Visitor chooses a tier and quantity, then enters:

- Purchaser full name (**required**)
- Mobile number with country code (**required**)
- Email address (**required**)
- Ticket category and quantity (**required**)
- Names of individual attendees (**decision pending**; support per-ticket assignment)
- Organisation / special requirements (**optional and only if needed**)
- Privacy notice acknowledgement; separate opt-in for promotional follow-ups, if offered

On submission:

1. Validate input on the server and rate-limit abuse.
2. Create/update a CRM **contact** and create a distinct **booking** record (do not merge two bookings simply because the same mobile is used).
3. Calculate amount **on the server** in INR paise.
4. Reserve the requested ticket quantity for a limited checkout hold.
5. Mark the booking **`PAYMENT_PENDING`** and present payment instructions.

**CRM record ≠ confirmed ticket.** Registration alone does not confer admission.

### Step C — UPI payment QR shown/sent to guest

- The customer should receive a **booking-specific, fixed-amount, single-use UPI QR** where supported by the **approved Razorpay merchant account**.
- Show it on a secure payment page, with payable amount and countdown/expiry as appropriate.
- For same-phone purchasers, offer a supported **open UPI/payment link**; users cannot conveniently scan a QR displayed on the same phone.
- Optionally send the secure payment page link or QR via an approved email/WhatsApp provider; **do not assume WhatsApp Business API or automated messaging is enabled**.
- Every payment attempt has a unique internal ID and linked Razorpay QR/payment reference. The QR must not be a permanent static bank QR reused across unrelated bookings.

**Implementation prerequisite:** Confirm the merchant has access to the necessary Razorpay QR Codes APIs and associated event notifications. If not, use **Razorpay Checkout/hosted payment link** as a documented fallback with the **same server-side verification**, not an untracked personal UPI QR.

**Timing concern:** A QR's provider-side expiry and a temporary inventory hold are different clocks. Design a safe state machine for late payment confirmations; a booked seat must never be sold twice because a webhook arrived after a hold expired.

### Step D — Verified payment → admission ticket issuance

- Handle Razorpay payment/QR notifications in a backend webhook receiver.
- Verify webhook signature **against the raw request body** before consuming the event.
- Persist provider event IDs and deduplicate repeated deliveries.
- Check actual gateway payment state, currency, amount, merchant account, booking association and captured/successful status.
- In a short MySQL transaction, allocate eligible tickets exactly once, or route the payment to an exception/reconciliation queue if inventory can no longer be guaranteed.
- Generate **one unique event admission QR per valid ticket**, tied to ticket ID + secure random token.
- Queue email with ticket download link/PDF; do not fail issuance simply because email delivery is delayed.
- Change booking state to **`CONFIRMED`** only when its paid entitlements are safely allocated and issued.

**Never trust:** payment screenshots, manually entered UTRs, browser redirects, callback query strings, or an unauthenticated QR scan as payment proof.

### Step E — Guest receives event ticket

Each ticket should display:

- Event logo and name
- Individual attendee name where collected
- Booking number and unique ticket ID
- Category (and assigned seat number **only if numbered seating is approved**)
- Approved venue, date and time
- **Event admission QR** (distinct from payment QR)
- Check-in guidance and approved support contact

Ticket format: responsive web ticket; downloadable PNG/PDF if feasible; email delivery required; WhatsApp delivery optional subject to channel approval. Provide a controlled **resend ticket** action from the dashboard.

### Step F — Mobile check-in at venue

- Staff opens `.../staff/checkin` in a **mobile browser over HTTPS** and signs in.
- Browser requests camera permission and scans the event admission QR.
- Backend validates the opaque token, event, payment/issuance state, cancellation/refund state and check-in state.
- **Valid and unused:** atomically mark `CHECKED_IN`, store timestamp and scanning staff account, show green confirmation.
- **Already checked in:** orange warning with time (no second entry).
- **Cancelled/void/refunded/not paid/invalid:** red rejection.
- Provide manual ticket-ID or verified guest search for damaged QR codes, protected by staff permissions and audit logs.

**Do not implement a simplistic frontend-only QR validity check.** Check-in must be a server-side atomic operation to prevent two devices admitting the same ticket concurrently.

---

## 5. Backend modules and screens

### Public-facing

1. **Event landing page** — approved details, artists/programme (only when confirmed), ticket categories and CTA.
2. **Registration / RSVP form** — contact info, tier, quantity, privacy acknowledgement.
3. **Booking summary** — line items, taxes/fees if approved, total payable, booking status.
4. **UPI payment page** — dynamic QR or approved fallback + same-phone payment option, expiry and status polling.
5. **Confirmation page** — verified status, download tickets, email sent/status notice.
6. **Secure ticket retrieval** — signed expiring link or identity challenge; protect private details.
7. **Policies/support** — privacy, cancellation/refund, contact details, terms.

### Authenticated management

1. **Admin login & user management** — named accounts, password reset, role-based permissions.
2. **Overview dashboard** — KPI cards, graphs and inventory availability.
3. **CRM / Registrations** — contacts, source, consent, registration history, filters, notes, export controls.
4. **Bookings** — pending, paid, expired, failed, cancelled, exceptional; search and view.
5. **Payments** — gateway reference, capture state, discrepancies, refunds, settlement exports.
6. **Tickets / Inventory** — issued tickets, per-category stock, comps, sponsor allocations, organiser holds, resend/cancel with approvals.
7. **Staff QR scanner** — mobile-first, fast scanning, minimal personal data.
8. **Live attendance** — checked-in count, trends and per-gate activity.
9. **Reports** — tickets sold, sales by category, registration-to-payment conversion, campaigns, attendance and reconciliation.
10. **Event configuration** — publication flags, prices, quotas, merchant mode, template settings, authorised operators.
11. **Audit log** — actor, action, target, time, reason, old/new status.

### Roles (minimum)

| Role | Access |
|---|---|
| Super Admin | Event configuration, staff, inventory, dashboard, all audit records |
| Event Manager | CRM, bookings, ticket operations, attendance, reports |
| Finance | Payment, refunds (subject to approval), finance reports and reconciliation |
| Gate Staff | Scanner and minimal necessary ticket lookup; no financial exports |

Use **least privilege** and enforce all permissions on the **PHP backend**. Prefer MFA for high-privilege accounts.

---

## 6. CRM and analytics requirements

A single contact can have multiple bookings; each booking may contain multiple tickets.

**CRM states (illustrative):** `NEW_REGISTRATION`, `PAYMENT_PENDING`, `PAID`, `TICKET_ISSUED`, `ATTENDED`, `CANCELLED`; treat these as projections from authoritative bookings/payments/tickets rather than one overloaded field.

**Dashboard calculations:**

| Metric | Definition |
|---|---|
| Registrations | Number of successful registration records/bookings (show distinct contacts separately) |
| Pending payment | Bookings with no captured payment and still awaiting payment |
| Paid bookings | Bookings with verified payment |
| Paid admissions issued | Count of active issued paid tickets, not count of buyers |
| Complimentary/sponsor admissions | Active allocated or issued non-paid admissions by category |
| Inventory remaining | Capacity less active allocations/blocks/holds, computed consistently |
| Gross verified collections | Verified payments received, excluding unverified screenshots or pending states |
| Refunds | Refunded amount and corresponding ticket adjustments, shown separately |
| Net collections (before/after fees) | Show according to documented approved finance formula; do not label face value as revenue |
| Attendance | Successfully checked-in admission tickets |
| Registration-to-payment conversion | Paid bookings ÷ eligible registrations in the selected interval |
| Show-up rate | Checked-in valid admissions ÷ issued valid admissions |
| Campaign contribution | UTM/source-based registrations, paid bookings and gross collections |

Use daily/time-series charts and category breakdowns. Avoid claiming Meta ads conversions are exact unless tracking, consent and attribution are configured and validated. Allow CSV export to authorised users, excluding unnecessary personal data.

---

## 7. Database model (proposed logical schema)

Use migrations, `utf8mb4`, foreign keys, meaningful unique indexes and InnoDB transactions.

| Table | Key responsibilities |
|---|---|
| `events` | Name, timezone, venue, publication status, capacity, dates |
| `ticket_categories` | Event, public label, capacity, `price_paise` |
| `contacts` | Guest name, email, mobile, opt-in and source metadata |
| `bookings` | Buyer, total amount, currency, status, hold deadline, source |
| `booking_items` | Category, quantity, unit price and amount snapshots |
| `inventory_holds` | Category/seat allocation, quantity, hold expiration, state |
| `payment_attempts` | Booking, provider QR/order/payment reference, amount, expiry, state |
| `gateway_webhook_events` | Unique provider event ID, signed receipt metadata, processed state/retries |
| `tickets` | Booking/item/event, random token hash, status, issue/cancel/check-in data |
| `ticket_allocations` | Comp/sponsor/organiser allocations against category capacity |
| `checkins` | Ticket ID (**unique for admitted event ticket**), user, timestamp, gate |
| `users` / `roles` / `permissions` | Authenticated dashboard access and RBAC |
| `audit_logs` | Material user/system actions with safe metadata |
| `email_deliveries` | Ticket/confirmation sending attempts and status |
| `refunds` | Provider refund reference, state, amount and reason |
| `event_settings` | Operational flags and approved policy/version pointers |

Add `seats`/`seat_assignments` **only if numbered seating is approved**. Do not hardcode a seat map from an unconfirmed venue layout.

### Non-negotiable data integrity rules

- `(issued and reserved valid entitlements + active holds + organiser/sponsor/comp blocks) <= 450` at the event level, and corresponding per-category ceilings.
- A payment belongs to **one booking/payment attempt**, and no gateway transaction is fulfilled twice.
- A ticket token is cryptographically random; store a hash if practical and never place email/mobile/payment details directly in the QR payload.
- A ticket can have **one accepted check-in**. Enforce this with transactions and constraints, not just button disabling.
- Financial records and refund history must remain auditable; avoid destructive deletion of payment rows.
- Refunds do not automatically free stock until tickets are explicitly voided and resale rules permit release.

---

## 8. State machines and race conditions

**Booking:** `DRAFT` → `PAYMENT_PENDING` → `VERIFYING_PAYMENT` → `CONFIRMED` → (`CANCELLED` / `REFUNDED` as permitted). Side paths: `HOLD_EXPIRED`, `PAYMENT_FAILED`, `PAYMENT_EXCEPTION`.

**Payment attempt:** `CREATED` → `AWAITING_UPI` → `CAPTURED` / `FAILED` / `EXPIRED` / `RECONCILIATION_REQUIRED`.

**Ticket:** `ISSUED` → `CHECKED_IN` or `VOID` / `CANCELLED` (allowed transitions only).

**Critical implementation detail:** The public availability calculation and the transition from held inventory to paid/issued inventory must be protected by **MySQL transactions plus row-level locking** or an equivalent provably safe allocation scheme. Expiry jobs and payment webhooks can race; use a reconciliation/exception state if any ambiguity remains. Every webhook/fulfilment operation must be idempotent.

**Proposed hold TTL:** 10 minutes, **not client-approved**. Confirm Razorpay QR expiry behaviour and provider account capabilities before setting the actual timer. A late confirmed payment must never silently create a 451st admission; issue only if valid allocated stock is still reserved, otherwise notify finance and flag for resolution/refund.

---

## 9. Payment integration instructions

1. **Validate Razorpay account readiness**: legal merchant, settlement bank, live/test keys, permitted QR API, enabled methods, webhook events and notification delivery.
2. Create booking and short-lived inventory allocation *before* creating its payment intent/QR.
3. Generate a **fixed-amount single-use QR** where supported; store provider QR ID, expected amount (paise), expiry, booking/payment-attempt ID, provider metadata and status.
4. Implement an approved alternate **Razorpay Checkout or hosted payment flow** if QR API is not available to this merchant. Use the same backend verification and fulfilment service.
5. Keep API keys and webhook secrets in server-side secrets/environment config, never HTML/JavaScript.
6. Verify signed callbacks using raw bytes; accept duplicate and out-of-order webhooks; verify captured state and exact amount/currency with the provider as required.
7. Mark ticket fulfilment idempotently in a DB transaction; dispatch mail jobs after commit.
8. Add periodic reconciliation jobs for payments successful at Razorpay but not yet fulfilled or mismatched.
9. Track refunds independently and only invalidate/release tickets in approved, auditable ways.
10. Do not allow a staff user to mark a booking `PAID` just by uploading a payment screenshot. Any manual exception must be reviewed by finance with independent settlement evidence.

**Source documentation (developer verification, not client approval):**
- Razorpay QR overview: https://razorpay.com/docs/payments/qr-codes/
- Razorpay QR API create: https://razorpay.com/docs/api/payments/qr-codes/create/
- Razorpay webhook validation: https://razorpay.com/docs/webhooks/validate-test/
- Laravel 13 releases / supported PHP: https://laravel.com/docs/13.x/releases

> Razorpay QR functionality and some expiration/notification features can depend on merchant enablement. Confirm against the **merchant's actual enabled APIs** in the current official documentation; do not assume every merchant supports the same QR workflow.

---

## 10. User experience and creative integration

The event website should reflect the approved **Sagar Kinare** creative identity, with a **premium contemporary, cinematic and legible** aesthetic, not a generic NGO design. Preserve mobile usability above decoration:

- Clear single primary booking CTA; minimal fields and mobile keyboard types.
- Strong price and ticket quantity visibility; taxes/fees shown before payment.
- Accessible colour contrast, large tap targets, informative loading and failure states.
- Explain the **difference between payment QR and entry ticket QR** in plain language.
- Handle payment-in-progress, QR expiry and browser-close recovery without losing booking reference.
- The event page may include a poetic tagline, but **do not use “A Musical Evening for a Cause” as the headline tagline**; final copy is subject to creative approval.
- Keep **“Joygivers Presents”** as supporting brand attribution; never imply charity funds go elsewhere.

Event branding, performance marketing campaigns and the sponsorship pitch deck are **parallel TRIKON creative/marketing workstreams**, not automatically part of the PHP application's codebase. The website must support importing approved creative assets and campaign tracking.

---

## 11. Security, privacy and reliability

- HTTPS everywhere; HSTS after domain and TLS rollout are proven.
- Strong admin authentication, hashed passwords, session rotation, login throttling, server-side RBAC, CSRF protection and secure cookies.
- Protect booking lookups and resend links with signed short-lived tokens or equivalent identity checks; rate-limit email/SMS sending.
- Sanitise output; validate and parameterise database input; limit uploads; audit sensitive changes.
- Collect only necessary PII; show privacy notice, obtain appropriate contact permissions, limit data access and define a retention/deletion policy with Joygivers.
- Separate the CRM from any future marketing outreach list unless guest consent supports promotional contact.
- Do not put guest contact details in QR payloads, application logs, analytics events or URL query strings.
- Encrypted off-server backups, regular restore testing, queue monitoring, disk/DB/CPU checks, structured application logs and alerting.
- **Event-day internet failure:** rehearse a supervisor-led controlled fallback (protected exported manifest/manual exception log) with later reconciliation. Do not promise safe fully offline multi-device check-in without designing conflict resolution.
- Host payment and CRM data in an environment isolated enough from unrelated VPS applications to avoid credential/configuration leaks.

---

## 12. MVP delivery plan

| Phase | Tasks | Exit evidence |
|---|---|---|
| **0 — Discovery/approval register** | Confirm venue/date/tiers/seating, merchant QR capability, tax/fees, legal seller, policies, branding, email channel | Written decision log + launch blockers |
| **1 — Foundation** | PHP/Laravel project, MySQL migrations, local/staging, roles, event config, UI skeleton | Auth/login and migrations demonstrated |
| **2 — CRM & booking** | Registration, contacts, booking items, transactional holds, expiry jobs | Registration and no-oversell concurrency tests |
| **3 — UPI payments** | Razorpay test integration, per-booking QR/fallback, signed webhooks, reconciliation | Successful/failed/delayed/duplicate payment tests |
| **4 — Event ticket issuance** | Unique admission QR, email/ticket retrieval, void/refund rules | One ticket per paid admission, idempotent issuance |
| **5 — Gate operations** | Mobile scanner, manual lookup, role permissions, atomic check-in | Duplicate/parallel scan tests on multiple phones |
| **6 — Dashboard** | CRM, inventory, sales graphs, campaign attribution, attendance, finance exports | Metrics cross-checked against DB fixtures |
| **7 — Launch rehearsal** | VPS hardening, backup restore, staff training, venue Wi-Fi/cellular tests, load/recovery tests | Sign-off checklist and written release approval |

**Delivery instruction for coding agents:** First inspect any existing repository and environment; preserve functioning work; create an implementation plan and migrations; implement in small testable increments. Do not claim a module is complete without executable test evidence. No production deployments or real payments without explicit owner approval.

---

## 13. Minimum acceptance test matrix

- [ ] Guest can register from a mobile browser; CRM and booking created once.
- [ ] Price and amount cannot be edited by manipulating frontend parameters.
- [ ] Quantity, hold duration and per-tier capacity enforced on server.
- [ ] Ten or more concurrent purchases for the final available seat **cannot oversell**.
- [ ] A registration without payment produces **no valid admission ticket**.
- [ ] Booking-specific UPI QR is correctly associated with exact expected amount.
- [ ] Same-phone user has an alternative payment-link action.
- [ ] Invalid/forged gateway callback fails; no ticket issued.
- [ ] Valid **captured** payment issues exactly one ticket per entitled attendee.
- [ ] Duplicate webhook events and browser refresh do not issue duplicate tickets.
- [ ] Webhooks received out of order result in correct final state.
- [ ] Successful debit after expired hold is safely reconciled; never oversells.
- [ ] Ticket email failure is retried without regenerating tickets.
- [ ] Ticket retrieval/resend does not reveal another customer's personal data.
- [ ] Complimentary/sponsor allocations reduce the available 450 seats.
- [ ] Cancelled/refunded/void ticket is rejected at the gate.
- [ ] Valid ticket scans on ordinary Android and iOS mobile browsers over HTTPS.
- [ ] Same QR scanned simultaneously on two phones results in **one accepted check-in**.
- [ ] Scanner rejects invalid, already-used and wrong-event tokens.
- [ ] Finance can reconcile gateway payment totals with internal booking totals.
- [ ] Dashboard distinguishes registrants, bookings, paid admissions and checked-in guests.
- [ ] Unauthorised gate staff cannot access CRM exports, refunds or payment credentials.
- [ ] Backups restore cleanly in a test environment; incident and internet fallback rehearsed.
- [ ] Public sale remains disabled until **all launch blockers** are signed off.

---

## 14. Decisions required from Joygivers / TRIKON before go-live

| # | Decision / approval | Suggested owner |
|---|---|---|
| 1 | Final Sagar Kinare name, approved logo, copy and ticket artwork | Joygivers + TRIKON Creative |
| 2 | Event date, schedule, confirmed venue/address, permitted entry capacity | Joygivers |
| 3 | Category-only access or numbered seat selection and final category names | Joygivers Event Ops |
| 4 | Legal seller, Razorpay merchant account, QR API enablement, bank settlement | Joygivers + Finance |
| 5 | Whether ₹2,500/₹1,500/₹1,000 include taxes; booking fee payer | Noufal + CA + Joygivers |
| 6 | Refund/cancellation/postponement policy, payment support contact | Joygivers + Legal/Finance |
| 7 | Hold TTL, per-order max, payment QR expiry and late-payment handling | TRIKON Tech + Joygivers |
| 8 | Sponsor/complimentary/organiser allocations by tier and issuing authority | Joygivers Event Ops |
| 9 | Attendee names per ticket; contact permissions and retention policy | Joygivers |
| 10 | Approved domain, sender email, whether WhatsApp Business integration is available | Joygivers + TRIKON Tech |
| 11 | Number of scanners, check-in supervisor, internet/accessibility backup plan | Event management partner + Joygivers |
| 12 | Production hosting approvals, backup/monitoring owner, final UAT and release | TRIKON Tech + Joygivers |

**Default release flags:** `event_publication_enabled=false`, `live_payments_enabled=false` until signed off. Build and QA can proceed on dummy event configuration and Razorpay test credentials.

---

## 15. Developer deliverables / handover checklist

Provide all of the following to TRIKON, not only a deployed website:

- Full source code and dependency manifest, clean repository and README.
- MySQL migrations/seeders and a database ERD.
- `.env.example` **without secrets** plus configuration guide.
- Screens/routes/API endpoint inventory and role-access matrix.
- Razorpay QR and/or approved fallback integration notes; webhook event lifecycle and idempotency design.
- Sample branded ticket templates, secure admission QR implementation, scanner UI.
- Test suite and test report, including concurrency and QR double-scan evidence.
- Dashboard metric definitions and reconciliation/export samples.
- Deployment runbook for the approved VPS; backups, monitoring, log locations and restore procedure.
- Staff operating guide for registration helpdesk, refunds/escalations and event-day gate.
- Open-issues register and explicit production-launch sign-off record.

**Definition of done:** A complete end-to-end test proves **link → registration → CRM → unique payment QR/approved fallback → verified payment → one admission QR per ticket → emailed/downloadable ticket → phone-based scan → one recorded entry → accurate dashboard**. A pretty landing page or mock dashboard alone is **not** project completion.

---

## 16. Sources & precedence

**Primary inputs:** User-supplied client ticketing brief; subsequent client clarification on guest-link registration, CRM, UPI QR, automatic ticket generation, phone scanning, analytics, and login-protected dashboard. The 12 November 2026 TRIKON HTML/PHP Agent Pack v2.0 provides earlier technical constraints; this handoff supersedes its assumed **checkout-first payment presentation** where the newly required QR-first journey differs.

**Reference implementation guidance:** Official Laravel 13 release and database documentation, and Razorpay's official QR Codes, payment and webhook documentation. Specific merchant features, current APIs, webhook event names and payment timing must be validated during integration.

**Not approved by this document:** Live payment activation, final legal/tax treatment, a particular external domain, artist details, numbered seating, approved branding or event date.

---

**TRIKON instruction to developers:** Build this as a secure, reliable **event operations platform**, not just a registration form. Prioritise payment correctness, data integrity, privacy and fast venue entry. Keep the code modular enough for future Joygivers events, but do not add a broad multi-client SaaS platform to the first release.
