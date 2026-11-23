# Open Decisions & Launch Blockers Register

The following 12 items from Handoff v3.0 Section 14 require formal sign-off before enabling public sales (`event_publication_enabled=true`) and live payment gateways (`live_payments_enabled=true`).

---

| # | Item | Current Status | Assumed Working Default | Action Required Before Launch |
|---|---|---|---|---|
| **1** | **Event Name, Logo & Creative Artwork** | In Review | "Sagar Kinare" (Joygivers Presents). Poetic copy loaded; "A Musical Evening for a Cause" avoided as primary headline. | Joygivers + TRIKON Creative to approve final hero banner and ticket visual assets. |
| **2** | **Event Date & Timings** | **OPEN / Launch Blocker** | Displayed as "To Be Announced" on public page. | Joygivers to confirm event date and doors-open time. |
| **3** | **Venue Confirmation** | ~95% Confirmed | Chavara Hall, Near South Railway Station, Kochi, Kerala. | Formal contract / hall booking confirmation with Chavara Cultural Centre. |
| **4** | **Legal Seller & Merchant KYC** | Pending | Configured in Razorpay Test Mode with test credentials. | Joygivers entity PAN/GST, settlement bank verification, and live API keys. |
| **5** | **Tax / GST Treatment** | Pending | Ticket prices (₹2,500, ₹1,500, ₹1,000) treated as inclusive of all applicable taxes. | CA / Joygivers confirmation on charitable tax exemption vs GST registration. |
| **6** | **Refund & Cancellation Policy** | In Review | All sales final unless cancelled by organiser. Exception payments routed to reconciliation. | Formal terms and conditions copy approved for site footer. |
| **7** | **Hold TTL & Expiration** | Implemented | 10 minutes (`600s`), strictly reconciled with Razorpay `close_by` parameter. | Ops sign-off on 10 minutes vs 15 minutes. |
| **8** | **Comps & Sponsor Allocations** | Implemented | Managed via `/admin/allocations` by Event Manager; deducted from 450 pool. | Initial allocation lists for title sponsors, artists, and VIPs. |
| **9** | **Attendee Name Collection** | Implemented | Primary purchaser name mandatory; sequential guest numbering (`Guest #2`) by default. | Confirm whether full names and IDs are mandatory for all attendees at checkout. |
| **10** | **Production Domain & Email** | Open | `event.joygivers.org` with SMTP transactional sender `events@joygivers.org`. | DNS configuration (SPF, DKIM, DMARC) for reliable ticket email delivery. |
| **11** | **Gate Scanners & Internet Backup** | Implemented | Minimum 2 mobile scanners running over HTTPS; offline supervisor manifest protocol ready. | Briefing and rehearsal with venue gate staff. |
| **12** | **Production Hosting & Sign-off** | Ready | Hostinger KVM2 VPS Runbook prepared with automated daily encrypted backups. | Production deployment approval and live smoke test. |
