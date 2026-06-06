# POS Self-Order System (Self-Ordering Restaurant POS)

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vue.js)](https://vuejs.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css)](https://tailwindcss.com)
[![PrimeVue](https://img.shields.io/badge/PrimeVue-Aura-green?style=for-the-badge&logo=primevue)](https://primevue.org)
[![Supabase](https://img.shields.io/badge/Supabase-Database%20%26%20Storage-3ECF8E?style=for-the-badge&logo=supabase)](https://supabase.com)
[![Xendit](https://img.shields.io/badge/Xendit-Payment%20Gateway-blue?style=for-the-badge)](https://www.xendit.co/id/)

A premium, modern, and real-time self-ordering Point of Sale (POS) system designed for restaurants. Customers can scan a table-specific QR code, input their basic information, browse the digital menu, place orders, and choose their preferred payment method. Kitchen staff receive orders in real-time, and cashiers manage active tables and payments dynamically.

---

## 🍽️ System Overview & Goals

The main goal of this system is to streamline the restaurant ordering process, minimize manual errors, and provide a seamless dining experience. By utilizing self-service ordering and real-time updates, we bridge the gap between customers, the kitchen, and the cashier.

```mermaid
sequenceDiagram
    autonumber
    actor Customer as Customer (Table)
    actor Kitchen as Kitchen Staff
    actor Cashier as Cashier
    actor Admin as Admin

    %% Setup phase
    Admin->>Admin: Create Menu & Tables
    Admin->>Admin: Generate & Print Table QR Codes

    %% Customer Phase
    Customer->>Customer: Scans Table QR Code
    Customer->>Customer: Inputs Name & Phone Number
    Customer->>Customer: Browse Menu & Add items to Cart
    Customer->>Customer: Submits Order (Status: Pending)
    
    %% Kitchen Phase
    Note over Kitchen: Receives order in real-time (Laravel Reverb)
    Kitchen->>Kitchen: Updates status to Cooking
    Kitchen->>Kitchen: Updates status to Ready

    %% Customer Requesting Bill
    Customer->>Customer: Receives "Ready" notification
    Customer->>Customer: Clicks "Request Bill"
    Note over Cashier: Receives bill request in real-time

    %% Payment Phase
    alt Option A: Pay Cash to Cashier
        Customer->>Cashier: Pays manually at the cashier counter
        Cashier->>Cashier: Input amount & processes payment
        Cashier->>Customer: Prints PDF receipt
    else Option B: Pay Online (Xendit)
        Customer->>Customer: Clicks "Pay Online"
        Customer->>Customer: Redirected to Xendit Invoice page
        Customer->>Customer: Completes payment (QRIS/VA/E-Wallet)
        Xendit->>Cashier: Sends Webhook (Auto-approves payment)
    end

    %% Completion Phase
    Note over Cashier, Customer: Order Status updated to Completed
    Note over Cashier, Customer: Table Status reset to Available
```

---

## 👥 Roles & Key User Flows

### 📱 1. Customer Flow
* **Scan QR Code:** Scanning the table QR code redirects the customer to their table's specific ordering page.
* **Identify & Access:** Customers enter their name and phone number to create a session (order status: `draft`).
* **Interactive Menu & Cart:** Browse categories, customize items with notes, and add them to their interactive cart.
* **Submit Order:** Orders are sent to the kitchen in real-time. Customers can continuously append new items to their active order.
* **Request Bill:** Requesting the bill notifies the cashier instantly.
* **Payment Choice:**
  * **Cash:** Notify cashier to pay manually at the counter.
  * **Online (Xendit):** Instant checkout via QRIS, Virtual Accounts, or E-Wallets.

---

### 🍳 2. Kitchen Flow (Real-time Dashboard) [Route on `/dapur`]
* **Real-time Queue:** View incoming orders automatically as they are submitted (broadcasted via Laravel Reverb).
* **Order Status Transition:** Transition orders from `pending` ➡️ `cooking` ➡️ `ready` with intuitive action buttons.
* **Detailed Info:** View item names, quantities, and customer-specific notes (e.g., "no onions").

---

### 💵 3. Cashier Flow [Route on `/kasir`]
* **Table Grid:** Visual layout displaying all tables, color-coded by occupancy status (`available` vs `occupied`).
* **Real-time Notifications:** Receive distinct visual alerts when a table clicks "Minta Bill" (Request Bill).
* **Manual Checkout:** Select a table, view the itemized summary, input the cash amount received, calculate change, and complete the order.
* **Receipt Generation:** Stream/download print-ready PDF receipts for customers.

---

### ⚙️ 4. Admin Panel [Route on `/admin/menu` and `/admin/reports`]
* **Menu Management:** Full CRUD operations on categories and menu items (with image uploads hosted on Supabase Storage).
* **Table Management:** Manage tables, generate dynamic table-specific QR codes, and download them as PNGs.
* **User Management:** Create and manage user credentials for Cashier, Kitchen, and Admin roles.
* **Financial Reports:** View daily, weekly, or custom date-range sales reports. Export data directly to Excel or download PDF summaries.

---

## 📸 Interface Screenshots & Visuals

### A. Customer Experience (Mobile-Optimized)

<p align="center">
  <img src="docs/customer/cust.png" width="500" alt="Table Selection Page" />
  <br>
</p>
<strong>Table Selection Page:</strong> Customers choose an empty table to start ordering. Occupied tables are marked with a dark color and "Sedang Terisi" (Occupied) status.

---

<p align="center">
  <img src="docs/customer/cust_1.png" width="500" alt="Customer Registration & Onboarding" />
  <br>
</p>
<strong>Customer Registration & Onboarding:</strong> Form to input full name and WhatsApp number to verify customer identity at the table before accessing the menu.

---

<p align="center">
  <img src="docs/customer/cust_2.png" width="500" alt="Self-Order Menu & Cart" />
  <br>
</p>
<strong>Self-Order Menu:</strong> Interactive menu listing with fast search, categories (Main Course, Drinks, etc.), kitchen queue estimation, and active order status.

---

<p align="center">
  <img src="docs/customer/cust_3.png" width="500" alt="Shopping Cart (Your Order)" />
  <br>
</p>
<strong>Shopping Cart (Your Order):</strong> Modal showing details of customer's active order to review selected items and custom notes (e.g. spicy levels) before placing order.

---

<p align="center">
  <img src="docs/customer/cust_4.png" width="500" alt="Payment Method Options" />
  <br>
</p>
<strong>Payment Method Options:</strong> Modal for selecting checkout method, offering online payment (QRIS/E-Wallet/Bank Transfer via Xendit) or cash payment directly to the cashier.

### B. Staff & Admin Dashboards (Desktop-Optimized)

<p align="center">
  <img src="docs/ktichen/dapur_1.png" width="500" alt="Real-time Kitchen Queue" />
  <br>
</p>
<strong>Real-time Kitchen Queue:</strong> Dedicated kitchen dashboard updated in real-time via Laravel Reverb, showing incoming orders with custom notes and action buttons (e.g., "Selesai Masak").

---

<p align="center">
  <img src="docs/cashier/kasir_1.png" width="500" alt="Cashier Dashboard (Table Monitoring)" />
  <br>
</p>
<strong>Cashier Dashboard (Table Monitoring):</strong> Interactive visual table layout with color indicators for real-time operational status (Available, Waiting for Kitchen, Cooking, Ready to Serve, Requesting Bill).

---

<p align="center">
  <img src="docs/cashier/kasir_2.png" width="500" alt="Cashier Payment Detail & Checkout" />
  <br>
</p>
<strong>Cashier Payment Detail & Checkout:</strong> Interface for cashier when processing payments, including order details, total bill, cash received input, auto change calculation, and payment confirmation.

---

<p align="center">
  <img src="docs/cashier/struk_1.png" width="500" alt="Receipt / Invoice (PDF)" />
  <br>
</p>
<strong>Receipt / Invoice (PDF):</strong> Print-ready payment receipt (including thermal printing format) containing customer name, table details, itemized orders with custom notes, total paid, change, and completed/paid status.

---

<p align="center">
  <img src="docs/admin/admin_1.png" width="500" alt="Admin Dashboard & Report" />&nbsp;&nbsp;&nbsp;
  <img src="docs/admin/admin_2.png" width="500" alt="Admin Dashboard & Report" />&nbsp;&nbsp;&nbsp;
  <img src="docs/admin/admin_3.png" width="500" alt="Admin Dashboard & Report" />
  <br>
</p>
<strong>Admin Dashboard & Sales Reports:</strong> Financial analytics page for restaurant owner. Provides transaction date filters, revenue metrics, and instant exports to Excel or PDF format.

---

<p align="center">
  <img src="docs/admin/report_1.png" width="450" height="auto" alt="Sales Report PDF" />
  <br>
</p>
<strong>Sales Report PDF:</strong> Sample of the exported sales report in PDF format.

---

## ⚡ Tech Stack & Libraries

* **Backend Framework:** Laravel 12 (Core API, Eloquent, Authentication)
* **Frontend Library:** Vue 3 (Composition API) with Inertia.js 2
* **Styling Framework:** Tailwind CSS
* **UI Components:** PrimeVue (using the modern **Aura** preset theme)
* **Real-time Server:** **Laravel Reverb** (WebSockets native broadcasting)
* **Database & Storage:** Supabase (PostgreSQL & Cloud Object Storage)
* **Payment Gateway:** Xendit PHP SDK (QRIS, VA, E-Wallet integration)
* **QR Generator:** Simple QR Code (`simplesoftwareio/simple-qrcode`)
* **Reports & Exports:** Laravel DomPDF & Maatwebsite Excel

---

## 🚀 Quick Setup & Installation

### Prerequisites
* PHP >= 8.2
* Node.js & NPM
* PostgreSQL database (Supabase recommended)

### Step-by-Step Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/rahulken96/pos-self-order-system.git
   cd pos-self-order-system
   ```

2. **Install PHP & JavaScript dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Configure Environment Variables:**
   Copy the example environment file and fill in your Supabase connection parameters, Laravel Reverb keys, and Xendit API secret.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Run Database Migrations & Seeders:**
   Deploy the tables and seed default users (Admin, Kasir, Dapur) and 10 default tables.
   ```bash
   php artisan migrate --seed
   ```

5. **Start Dev Servers:**
   Launch the Laravel backend server, the Reverb WebSocket server, and the Vite compilation server.

   * **Terminal 1 (Laravel Server):**
     ```bash
     php artisan serve
     ```
   * **Terminal 2 (Vite Asset compiler):**
     ```bash
     npm run dev
     ```
   * **Terminal 3 (Reverb WebSockets):**
     ```bash
     php artisan reverb:start --debug
     ```
   * **Terminal 4 (Queue Jobs):**
     ```bash
     php artisan queue:work
     ```

6. **Access the App:**
   * Go to `http://localhost:8000/login` to log in as Admin (`admin@pos.com`), Cashier (`kasir@pos.com`), or Kitchen (`dapur@pos.com`) with the password `password`.
   * Scan or browse `http://localhost:8000/order/1` to test the customer flow on Table 1.
