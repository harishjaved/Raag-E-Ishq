# Metric Definitions and Dashboard Calculation Specifications

All dashboard metrics are computed in `App\Services\AnalyticsService` directly from authoritative database records.

---

## 1. KPI Metric Definitions

### A. Registrations
* **Total Contacts:** Count of distinct customer profiles in the `contacts` table (`COUNT(id)`).
* **Total Bookings:** Count of total booking intents created across all statuses (`COUNT(id)` from `bookings`).

### B. Payment Conversion
* **Paid Bookings:** Count of bookings with authoritative gateway payment confirmation (`status = 'CONFIRMED'`).
* **Pending Payments:** Count of active checkout reservations awaiting payment confirmation (`status = 'PAYMENT_PENDING'`).
* **Registration-to-Payment Conversion Rate:**
  $$\text{Conversion Rate (\%)} = \frac{\text{Paid Bookings}}{\text{Total Bookings}} \times 100$$

### C. Collections & Revenue
* **Gross Verified Collections (INR):** Sum of all paid bookings in INR, converted from paise:
  $$\text{Gross Revenue} = \frac{\sum \text{total\_amount\_paise (for status = 'CONFIRMED')}}{100}$$
  *Rule:* Never counts unverified screenshots, pending holds, or expected face value as revenue.

### D. Admissions & Seating Capacity
* **Paid Admissions Issued:** Count of valid tickets issued to paid attendees (`status IN ('ISSUED', 'CHECKED_IN')` where booking is `CONFIRMED`).
* **Complimentary & Sponsor Admissions:** Sum of allocated comp/sponsor seats from `ticket_allocations` table (`SUM(quantity)`).
* **Total Capacity Allocated:**
  $$\text{Allocated} = \text{Paid Tickets Issued} + \text{Active Inventory Holds} + \text{Comp/Sponsor Allocations}$$
* **Global Inventory Remaining:**
  $$\text{Remaining} = 450 - \text{Total Capacity Allocated}$$
  *Guaranteed Constraint:* Always $\ge 0$. Never oversells beyond 450.

### E. Venue Attendance
* **Actual Attendance (Check-ins):** Count of successfully admitted tickets in the `checkins` table (`COUNT(id)`).
* **Show-up Rate:**
  $$\text{Show-up Rate (\%)} = \frac{\text{Actual Attendance}}{\text{Paid Admissions Issued}} \times 100$$

### F. Campaign Attribution
* Aggregated grouping by `contacts.utm_source` (defaulting to `Direct / Organic` if blank):
  * Contacts acquired per source
  * Bookings generated per source
  * Verified gross revenue in INR per source
