# K Hotel Loyalty Program - Member Tiers Comparison

This document outlines the operational and technical differences between a **Regular Guest (K Plus)** and a **Booker (K Reward)** within the loyalty program ecosystem.

---

## Member Comparison Matrix

| Feature / Metric | Regular Guest (K Plus) | Booker (K Reward) |
| :--- | :--- | :--- |
| **Membership Type** | `K Plus` | `K Reward` |
| **Card Types (Tiers)** | **Silver**, **Gold**, or **Brown** | **Booker** (Corporate / Booker) |
| **Voucher Model** | **Pre-assigned Voucher Package:** Gets predefined vouchers directly upon enrollment (e.g. Free Massage, Brunch, Room vouchers). | **Points Redemption:** Receives no initial vouchers. Instead, earns points and uses the points to claim reward vouchers. |
| **Rewards Earning** | Does not earn points. Instead, guest spending is tracked to check if they exceed the Gold Upgrade threshold. | **Earns Points:** Receives points dynamically when spending is logged at hotel departments (based on points rules set in settings). |
| **Voucher Source** | Initial registration or GM approval (for Gold upgrades). | Redeemed from the catalogue by submitting a **Redemption Request** which must be approved by the admin. |
| **Staff Incentives** | Staff members who sell/enrol K Plus Silver cards receive incentive commissions. | Not applicable for staff commissions. |

---

## Functional Rules

### 1. Regular Guests (K Plus)
* **Enrollment & Sales:** Silver cards cost `55.000 BHD`. The staff member who logs the sale receives a pre-configured commission percentage.
* **Tier Progression:** Spending is tracked automatically. When a member passes the Gold Upgrade threshold (e.g. `500.000 BHD`), they can be upgraded to a Gold Card, which requires General Manager (GM) approval.
* **Wallet:** Holds active vouchers, used vouchers, and expired vouchers.

### 2. Corporate Bookers (K Reward)
* **Points Ledger:** Receives points dynamically on spend logs:
  $$\text{Points Earned} = \left\lfloor \frac{\text{Spend Amount}}{\text{Department Threshold}} \right\rfloor \times \text{Points Rate}$$
* **Redemptions:** Bookers select vouchers from the Rewards Catalogue. The admin reviews and approves the redemption request.
* **Voucher Validity:** Upon approving the request, the Admin specifies the validity duration (e.g., 1 Month, 3 Months, 1 Year), after which the voucher expires.
