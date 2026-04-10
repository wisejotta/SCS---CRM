# SCS - CRM System Documentation

## Table of Contents
1. [Project Overview](#project-overview)
2. [System Architecture](#system-architecture)
3. [Technology Stack](#technology-stack)
4. [Installation & Setup](#installation--setup)
5. [Database Schema](#database-schema)
6. [API Documentation](#api-documentation)
7. [Frontend Architecture](#frontend-architecture)
8. [Security & Authentication](#security--authentication)
9. [Payment Integration](#payment-integration)
10. [Business Logic](#business-logic)

---

## 1. Project Overview

**SCS - CRM** is a comprehensive Customer Relationship Management system designed for Visas Canada, specializing in immigration visa processing and customer management.

### Purpose
- Manage immigration leads and customer applications
- Track visa processing workflows
- Handle payment processing through multiple gateways
- Manage agent performance and commissions
- Generate and track retainer agreements
- Monitor customer documentation and status

### Key Features
- Multi-role user management (Admin, Agent, Back Office, Customer Service)
- Lead assignment and tracking system
- Payment processing (Stripe, Square, Authorize.net)
- Document management and retainer generation
- Real-time agent break tracking
- Callback scheduling system
- Chargeback management
- Commission tracking
- CSV bulk import functionality

---

## 2. System Architecture

### Architecture Pattern
**Monolithic Full-Stack Application** with:
- **Backend**: Laravel 9 (PHP 8.0+)
- **Frontend**: Vue.js 3 with Vuetify
- **Database**: MySQL
- **Cache**: Redis
- **Build Tool**: Vite

### Application Layers

```
┌─────────────────────────────────────┐
│         Frontend (Vue.js 3)         │
│    - Vuetify UI Components          │
│    - Vue Router (SPA)               │
│    - Pinia State Management         │
└─────────────────────────────────────┘
                 ↓ HTTP/API
┌─────────────────────────────────────┐
│      API Layer (Laravel Routes)     │
│    - RESTful Endpoints              │
│    - Sanctum Authentication         │
└─────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────┐
│    Business Logic (Controllers)     │
│    - Lead Management                │
│    - Payment Processing             │
│    - Agent Management               │
└─────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────┐
│      Data Layer (Eloquent ORM)      │
│    - Models & Relationships         │
└─────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────┐
│         Database (MySQL)            │
└─────────────────────────────────────┘
```

---

## 3. Technology Stack

### Backend Technologies
| Technology | Version | Purpose |
|------------|---------|---------|
| PHP | ^8.0.2 | Server-side language |
| Laravel | ^9.19 | PHP framework |
| Laravel Sanctum | ^3.2 | API authentication |
| Guzzle HTTP | ^7.2 | HTTP client |
| Doctrine DBAL | ^3.6 | Database abstraction |

### Payment Integrations
| Service | Package | Purpose |
|---------|---------|---------|
| Stripe | stripe/stripe-php ^10.6 | Payment processing |
| Square | Custom integration | Payment processing |
| Authorize.net | Custom integration | Payment processing |

### Communication Services
| Service | Package | Purpose |
|---------|---------|---------|
| Twilio | twilio/sdk ^7.9 | SMS notifications |
| SendGrid | sendgrid/sendgrid ^8.0 | Email delivery |

### PDF Generation
| Package | Purpose |
|---------|---------|
| setasign/fpdf ^1.8 | PDF creation |
| setasign/fpdi-fpdf ^2.3 | PDF manipulation |

### Frontend Technologies
| Technology | Version | Purpose |
|------------|---------|---------|
| Vue.js | ^3.3.0 | Frontend framework |
| Vuetify | 3.4.0 | Material Design UI |
| Vue Router | ^4.1.5 | SPA routing |
| Pinia | ^2.0.24 | State management |
| Axios | 1.1.2 | HTTP client |
| Vite | ^3.2.4 | Build tool |

### Development Tools
| Tool | Purpose |
|------|---------|
| ESLint | JavaScript linting |
| Stylelint | CSS/SCSS linting |
| PHPUnit | PHP testing |
| Laravel Pint | PHP code style |

---
