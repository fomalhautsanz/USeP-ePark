# USeP ePark — Parking Management System

## Project Overview

USeP ePark is a QR-based parking management system developed for the University of Southeastern Philippines — Tagum Campus. The system automates vehicle entry and exit tracking, parking slot reservations, fee calculation, and reporting to improve parking efficiency and reduce manual processes.

This repository contains the Front-End Development of the system built using HTML, CSS, JavaScript, php, and mySQL covering both:

* Admin Controls
* User Controls

---

## System Modules

## Admin Controls

The Admin Panel allows administrators to monitor and manage parking operations.

### Features

* Dashboard overview
* Vehicle management
* Slot monitoring
* Reservation management
* Entry/exit logs
* Reports generation
* User access control

### Admin Pages

```id="admin-pages"
login.html  
dashboard.html  
vehicles.html  
slots.html  
reservations.html  
logs.html  
reports.html  
users.html  
```

---

## User Controls

The User Panel allows students, staff, and visitors to interact with the parking system.

### Features

* Vehicle registration
* QR code generation
* Parking reservation
* Entry/exit QR scanning
* Fee display and digital receipt

### User Pages

```id="user-pages"
index.html              ← User landing/home page  
register.html           ← Vehicle registration  
qr-code.html            ← Display generated QR code  
reserve.html            ← Reserve parking slot  
reservation-status.html ← View reservation details  
scan-entry.html         ← QR entry confirmation  
scan-exit.html          ← QR exit and fee display  
receipt.html            ← Digital receipt  
profile.html            ← User information  
```

---

## Project Structure

```id="project-structure"
epark-system/
│
├── admin/
│   ├── login.html
│   ├── dashboard.html
│   ├── vehicles.html
│   ├── slots.html
│   ├── reservations.html
│   ├── logs.html
│   ├── reports.html
│   └── users.html
│
├── user/
│   ├── index.html
│   ├── register.html
│   ├── qr-code.html
│   ├── reserve.html
│   ├── reservation-status.html
│   ├── scan-entry.html
│   ├── scan-exit.html
│   ├── receipt.html
│   └── profile.html
│
├── css/
├── js/
├── assets/
├── components/
└── README.md
```

---

## Technologies Used

* HTML5 — Structure
* CSS3 — Styling and layout
* JavaScript (Vanilla JS) — Interactivity
* PHP — Server-side scripting and backend processing
* MySQL — Database management system
* Visual Studio Code — Development environment

---

## UI Design Principles

* Minimalist and function-focused interface
* Simple navigation using sidebar and navbar
* Grid and Flexbox layout
* Clear icons and structure

---

## Security (Front-End Scope)

* Login interface for administrators
* Role-based navigation (Admin/User)
* Controlled access to pages

---

## Getting Started

### 1. Clone the repository

```id="clone"
git clone https://github.com/your-username/epark-system.git
```

### 2. Open in VS Code

Open the project folder using Visual Studio Code.

### 3. Run the project

Open the desired page in your browser:

* Admin → admin/login.html
* User → user/index.html

---

This repository serves as the foundation of the USeP ePark System.
