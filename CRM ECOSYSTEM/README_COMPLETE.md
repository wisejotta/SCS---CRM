# SCS - CRM System - Complete Documentation

## Quick Reference

### System Overview
**SCS - CRM** is a full-stack CRM system for Visas Canada immigration services built with Laravel 9 and Vue.js 3.

### Tech Stack
- **Backend**: Laravel 9, PHP 8.0+, MySQL
- **Frontend**: Vue.js 3, Vuetify 3, Vite
- **Payments**: Stripe, Square, Authorize.net
- **Communications**: SendGrid (email), Twilio (SMS)
- **Marketing**: Mailchimp integration

---

## Installation

```bash
# Backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate

# Frontend
npm install
npm run dev
```

---

## User Roles

| Role | Value | Access |
|------|-------|--------|
| Admin | 0 | Full system access |
| Agent | 3 | Lead management, payments |
| Customer | 4 | Portal access |

## Agent Types

| Type | Value | Function |
|------|-------|----------|
| Upgrade | 2 | Upgrade sales |
| Lead Assigner | 4 | Lead distribution |
| Back Office | 5 | Document processing |
| Customer Service | 6 | Customer support |
| File Opening | 7 | New applications |

---

## Core Models & Relationships

### User → Customer → Lead
- User has one Customer
- Customer has one Lead
- Lead belongs to Customer

### Lead → Agent
- Lead belongs to Agent (assigned)
- Lead belongs to Agent (callback)
- Lead belongs to Agent (back office)
- Lead belongs to Agent (customer service)

### Lead → Payment
- Lead has many Payments
- Payment belongs to Agent

### Lead → Documents/Comments
- Lead has many Documents
- Lead has many Comments
- Lead has many Retainers

---

## API Endpoints Summary

### Authentication
- `POST /api/users/login`
- `GET /api/users/verify`

### Leads
- `POST /api/leads/{type}` - List leads
- `GET /api/leads/{lead}` - View lead
- `POST /api/leads` - Create lead
- `POST /api/leads/{lead}/payment-link` - Send payment
- `POST /api/leads/{lead}/comments` - Add comment
- `POST /api/leads/callbacks/{lead}` - Schedule callback

### Agents
- `POST /api/agents` - List
- `PUT /api/agents` - Create
- `POST /api/agents/{user}` - Update
- `DELETE /api/agents/{user}` - Delete

### Payments
- `POST /api/payments/type/{type}` - By type
- `POST /api/payments/{user}` - By agent

---

## Payment Integration

### Stripe
```php
$checkout = $stripe->checkout->sessions->create([
    'success_url' => config('app.url') . "/s/success/$payment->id",
    'line_items' => [['price' => $price->id, 'quantity' => 1]],
    'mode' => 'payment',
]);
```

### Square
```php
$response = Http::withToken(config('square.accessToken'))
    ->post($baseUrl, [
        'quick_pay' => [
            'price_money' => ['amount' => $amount, 'currency' => 'USD'],
        ],
    ]);
```

### Authorize.net
```php
$response = Http::post("$baseUrl/xml/v1/request.api", [
    'getHostedPaymentPageRequest' => [
        'transactionRequest' => [
            'transactionType' => 'authCaptureTransaction',
            'amount' => $payment->amount,
        ],
    ],
]);
```

---

## Database Schema Quick Reference

### Key Tables
- **users**: id, firstname, lastname, email, password, role
- **customers**: id, user_id, country, phone_number, dob, gender
- **agents**: id, user_id, type, targets, chargebacks
- **leads**: id, customer_id, agent_id, visa_id, status, callback
- **payments**: id, lead_id, agent_id, amount, status, misc
- **visas**: id, name, price, external_id
- **retainers**: id, lead_id, visa_id, uuid
- **documents**: id, lead_id, agent_id, file_path
- **comments**: id, lead_id, agent_id, comment
- **agent_breaks**: id, agent_id, type, active, started_at
- **chargebacks**: id, agent_id, amount, reason

---

## Business Workflows

### Lead Processing
1. Lead created/imported
2. Assigned to agent
3. Agent contacts customer
4. Payment link sent (Stripe/Square/Authorize)
5. Payment received
6. Back office processes documents
7. Retainer generated and signed
8. Application submitted

### Payment Link Flow
1. Agent selects amount and gateway
2. System generates payment link
3. Link sent via Email (SendGrid) or SMS (Twilio)
4. Customer completes payment
5. Webhook updates payment status
6. Agent receives notification
7. Lead progresses to next stage

### Back Office Document Flow
1. Lead reaches $500+ in payments
2. Back office agent assigned
3. Documents uploaded and reviewed
4. Status: required → review → pre-approved → approved
5. Progress tracked (% complete)
6. Application submitted when 100%

### Callback System
1. Agent schedules callback
2. Callback appears in calendar
3. Notification 5-10 minutes before
4. Agent contacts customer
5. Callback marked complete or rescheduled

---

## Configuration Files

### Backend Config
- `config/app.php` - Application settings
- `config/database.php` - Database connections
- `config/stripe.php` - Stripe credentials
- `config/square.php` - Square credentials
- `config/authorize.php` - Authorize.net credentials
- `config/sendgrid.php` - Email settings
- `config/twilio.php` - SMS settings
- `config/mailchimp.php` - Marketing automation

### Frontend Config
- `vite.config.js` - Build configuration
- `themeConfig.js` - UI theme settings
- `resources/js/plugins/` - Plugin configurations

---

## Environment Variables

```env
# App
APP_NAME="SCS - CRM"
APP_ENV=production
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=scs_crm
DB_USERNAME=root
DB_PASSWORD=

# Stripe
STRIPE_KEY=pk_live_xxx
STRIPE_SECRET=sk_live_xxx

# Square
SQUARE_ACCESS_TOKEN=xxx
SQUARE_LOCATION_ID=xxx
SQUARE_ENV=production

# Authorize.net
AUTHORIZE_BASE_URL=https://api.authorize.net
AUTHORIZE_MERCHANT_NAME=xxx
AUTHORIZE_TRANSACTION_KEY=xxx

# SendGrid
SENDGRID_API_KEY=xxx
SENDGRID_TEMPLATE_ID=xxx

# Twilio
TWILIO_SID=xxx
TWILIO_AUTH_TOKEN=xxx
TWILIO_NUMBER=+1234567890

# Mailchimp
MAILCHIMP_API_KEY=xxx
MAILCHIMP_BASE_URL=https://us1.api.mailchimp.com/3.0
MAILCHIMP_LIST_ID=xxx

# Bitly
BITLY_API_KEY=xxx
```

---

## Common Tasks

### Create New Lead
```php
POST /api/leads
{
    "firstname": "John",
    "lastname": "Doe",
    "email": "john@example.com",
    "country": "USA",
    "residence": "USA",
    "phone_number": "+1234567890",
    "visa_id": 1,
    "agent_id": 1
}
```

### Send Payment Link
```php
POST /api/leads/{lead}/payment-link
{
    "amount": 500,
    "product": 1,
    "email": "customer@example.com",
    "phone_number": "+1234567890",
    "method": "stripe",
    "lan": "en",
    "sendVia": "Email"
}
```

### Add Comment
```php
POST /api/leads/{lead}/comments
{
    "comment": "Customer requested callback tomorrow"
}
```

### Schedule Callback
```php
POST /api/leads/callbacks/{lead}
{
    "date": "2024-01-15 14:00:00"
}
```

---

## File Structure

```
CRM ECOSYSTEM/
├── app/
│   ├── Console/Commands/      # Artisan commands
│   ├── Enums/                 # Enum definitions
│   ├── Http/
│   │   ├── Controllers/       # API controllers
│   │   └── Middleware/        # Custom middleware
│   ├── Models/                # Eloquent models
│   └── Providers/             # Service providers
├── config/                    # Configuration files
├── database/
│   ├── migrations/            # Database migrations
│   ├── seeders/               # Database seeders
│   └── factories/             # Model factories
├── public/                    # Public assets
├── resources/
│   ├── js/                    # Vue.js application
│   │   ├── components/        # Vue components
│   │   ├── pages/             # Page components
│   │   ├── layouts/           # Layout templates
│   │   └── plugins/           # Vue plugins
│   ├── views/                 # Blade templates
│   └── styles/                # SCSS styles
├── routes/
│   ├── api.php                # API routes
│   └── web.php                # Web routes
├── storage/                   # File storage
├── tests/                     # PHPUnit tests
├── .env                       # Environment variables
├── composer.json              # PHP dependencies
├── package.json               # JS dependencies
└── vite.config.js             # Vite configuration
```

---

## Security Best Practices

1. **Authentication**: All API routes protected with Sanctum
2. **CSRF**: Enabled on all web routes
3. **Validation**: Input validation on all endpoints
4. **SQL Injection**: Prevented via Eloquent ORM
5. **XSS**: Laravel automatic escaping
6. **Soft Deletes**: Data retention for audit
7. **Password Hashing**: Bcrypt algorithm
8. **Environment Variables**: Sensitive data in .env

---

## Performance Optimization

1. **Eager Loading**: Use `with()` to prevent N+1 queries
2. **Pagination**: All list endpoints paginated
3. **Indexing**: Database indexes on foreign keys
4. **Caching**: Redis for session and cache
5. **Asset Optimization**: Vite for bundling and minification
6. **Query Optimization**: Select only needed columns

---

## Troubleshooting

### Payment Link Not Sending
- Check SendGrid/Twilio credentials
- Verify email/phone number format
- Check webhook configuration

### Webhook Not Working
- Verify webhook URL is accessible
- Check webhook signature validation
- Review webhook logs

### Lead Not Appearing
- Check agent assignment
- Verify lead status
- Check soft delete status

### Document Upload Failing
- Verify storage permissions
- Check file size limits
- Confirm file type allowed

---

## Support & Maintenance

### Logs
- Laravel logs: `storage/logs/laravel.log`
- Web server logs: Check Apache/Nginx logs
- Database logs: Check MySQL slow query log

### Monitoring
- Payment success rate
- Agent performance metrics
- System response times
- Error rates

### Backup
- Database: Daily automated backups
- Files: Storage directory backups
- Configuration: Version control

---

## Documentation Files

1. **DOCUMENTATION.md** - Part 1: Overview, Architecture, Tech Stack
2. **DOCUMENTATION_PART2.md** - Part 2: Installation, Database, API
3. **DOCUMENTATION_PART3.md** - Part 3: Frontend, Security, Business Logic
4. **README_COMPLETE.md** - This file: Quick reference and summary

---

**Last Updated**: 2024
**Version**: 1.0
**Maintainer**: SCS Development Team
