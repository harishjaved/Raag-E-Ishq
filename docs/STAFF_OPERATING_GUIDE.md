# Staff Operating Guide: Sagar Kinare Event Day & Back Office

---

## 1. Gate Staff Guide: Mobile Scanner & Entry Operations

### A. Device Setup & Signing In
1. On your phone (Android Chrome or iOS Safari), open `https://event.joygivers.org/login`.
2. Enter your authorized gate credentials:
   * **Email:** `gate@joygivers.org` (or assigned named staff email)
   * **Password:** Provided by Event Ops Supervisor.
3. The platform will automatically route you to the **Gate Operations Scanner**.
4. When prompted by the browser, tap **"Allow"** for Camera permissions.

---

### B. Understanding Scan Results

| Response Card | Visual & Sound Feedback | Gate Staff Action |
|---|---|---|
| **GREEN (Valid)** | Green checkmark, high-pitch chime. Displays Attendee Name, Tier (e.g. Tier 1), and Ticket ID. | **Admit Attendee.** Tap "Scan Next Ticket". |
| **ORANGE (Already Used)** | Amber warning triangle, double warning beep. Displays exact prior scan timestamp (e.g. `19:14:02`). | **Do NOT Admit.** Explain politely: *"This ticket was already scanned and admitted earlier. Re-entry is not permitted."* Direct to Helpdesk if disputed. |
| **RED (Invalid / Void)** | Red cross mark, low buzz tone. Displays specific reason (e.g. Counterfeit signature, Void/Refunded, Unpaid). | **Do NOT Admit.** Direct guest to Event Helpdesk with their booking confirmation email. |

---

### C. Manual Lookup for Damaged / Unreadable QR Codes
If a guest's phone screen is shattered or water-damaged:
1. Tap the **"Manual Lookup"** dropdown on the scanner page.
2. Ask the guest for their **Ticket Number** (starts with `TKT-`, located in their confirmation email).
3. Verify their identity by checking a valid Government Photo ID (Aadhaar, Driving License, Passport).
4. Enter the **Audit Reason** (e.g., *"Cracked screen, verified Aadhaar ending in 1234"*).
5. Tap **"Admit via Manual Override"**. The system records an audited admission tied to your named account.

---

### D. Venue Internet Outage Fallback Protocol
If cellular data and hall Wi-Fi drop simultaneously:
1. **Never make unrecorded admissions.**
2. The Gate Supervisor opens the **Pre-Exported Digital Gate Manifest** (downloaded prior to gate opening onto offline supervisor tablets).
3. Check the guest's Ticket ID against the manifest and cross-check Govt ID.
4. Record all admitted Ticket IDs in the **Emergency Paper Gate Log**.
5. As soon as connectivity returns, use the supervisor manual check-in endpoint to sync all entries into the platform database.

---

## 2. Event Manager & Helpdesk Guide

### Resolving Customer Registration Enquiries
1. Log in to `/admin/crm/contacts`.
2. Search by guest name, email, or 10-digit phone number.
3. Open the contact profile to inspect their booking history, payment status, and issued tickets.
4. If a guest lost their email, use the **"Resend Tickets"** action from `/tickets/resend` or the booking detail view.

---

## 3. Finance Officer Guide: Reconciliation & Settlement

### Handling Flagged Payments (`RECONCILIATION_REQUIRED`)
Payments are routed to `RECONCILIATION_REQUIRED` in two non-negotiable safety cases:
1. **Late Payment After Hold Expiry with Category Exhausted:** The buyer paid at minute 11, after their hold expired and the category reached 100% capacity.
2. **Amount Discrepancy:** Razorpay reports an amount that does not match `booking->total_amount_paise`.

**Resolution Procedure:**
1. Log in to `/admin/payments` and filter by **"Exceptions"**.
2. Compare the gateway reference against the bank statement in the Razorpay Dashboard.
3. If valid:
   * **To Seat:** If another category has seats, change the booking tier and tap **"Force Confirm"**.
   * **To Refund:** If the event is at full 450 capacity, tap **"Refund"** to initiate a direct gateway refund back to the customer's UPI account. Never create an unallocated 451st ticket.
