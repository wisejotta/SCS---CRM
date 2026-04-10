## 7. Frontend Architecture

### Directory Structure
```
resources/js/
├── @core/           # Core utilities
├── @layouts/        # Layout system
├── components/      # Vue components
├── layouts/         # Layout templates
├── navigation/      # Navigation menus
├── pages/           # Page components
├── plugins/         # Vue plugins
├── router/          # Vue Router config
├── views/           # View templates
├── app.js           # App entry
└── main.js          # Main entry
```

### Key Components
- **LeadDetails.vue** - Lead information display
- **AgentDetails.vue** - Agent profile
- **CustomerDetails.vue** - Customer profile
- **AdminLeads.vue** - Admin lead management
- **BackOfficeLeads.vue** - Back office workflow
- **CreateAgent.vue** - Agent creation form
- **UpdateAgent.vue** - Agent update form
- **Sales.vue** - Sales dashboard
- **Notifications.vue** - Notification system
- **PaymentLinkTimeline.vue** - Payment tracking

### Routing
- `/` - Dashboard
- `/agents` - Agent management
- `/leads/*` - Lead pages
- `/customer/*` - Customer portal
- `/retainer/:uuid` - Retainer view
- `/callbacks` - Callback calendar
- `/opportunities` - Sales opportunities
- `/chargebacks` - Chargeback management
- `/breaks` - Agent breaks

### State Management (Pinia)
- User authentication state
- Lead filters and pagination
- Agent performance data
- Notification queue

---

## 8. Security & Authentication

### Authentication System
- **Laravel Sanctum** for API token authentication
- **Session-based** authentication for web routes
- **Basic Auth** for external API endpoints

### User Roles
```php
enum UserRole {
    ADMIN = 0
    AGENT = 3
    CUSTOMER = 4
}
```

### Agent Types
```php
enum AgentType {
    UPGRADE = 2
    LEAD_ASSIGNER = 4
    BACK_OFFICE = 5
    CUSTOMER_SERVICE = 6
    FILE_OPENING = 7
}
```

### Middleware
- `auth` - Requires authentication
- `auth.agent` - Requires agent role
- `auth.basic` - Basic authentication
- `auth.sanctum` - API token authentication

### Security Features
- CSRF protection on web routes
- Password hashing (bcrypt)
- Soft deletes for data retention
- Input validation on all endpoints
- SQL injection prevention (Eloquent ORM)
- XSS protection (Laravel escaping)

---

## 9. Payment Integration

### Supported Gateways
1. **Stripe**
2. **Square**
3. **Authorize.net** (with 3D Secure option)

### Payment Flow
1. Agent creates payment link
2. System generates gateway-specific link
3. Link sent via Email (SendGrid) or SMS (Twilio)
4. Customer completes payment
5. Webhook processes payment status
6. Lead status updated
7. Agent notified

### Payment Statuses
```php
enum PaymentStatus {
    SENT = 0
    SUCCESSFUL = 1
    FAILED = 2
    CANCELED = 3
}
```

### Stripe Integration
- Creates checkout session
- Tracks session ID in `payments.misc`
- Success redirect to `/s/success/{payment}`
- Webhook at `/api/s/return`

### Square Integration
- Creates payment link via API
- Stores order_id in `payments.misc`
- Return URL: `/sq/return/{payment}`
- Webhook at `/api/sq/webhook`

### Authorize.net Integration
- Creates hosted payment page
- Stores token in `payments.misc`
- Custom form at `/authorize/form/{payment}`
- Webhook at `/api/authorize/webhook/{payment}`
- Webhook auto-cleanup after payment

### Link Shortening
- Bitly API for SMS links
- Reduces character count for SMS delivery

---

## 10. Business Logic

### Lead Management

#### Lead Lifecycle
1. **Created** - New lead imported/created
2. **Assigned** - Assigned to agent
3. **Contacted** - Agent makes contact
4. **Payment Sent** - Payment link sent
5. **Payment Received** - Customer pays
6. **Back Office** - Document processing
7. **Completed** - Application submitted
8. **Closed** - Case closed

#### Lead Types
- **Fresh Leads** - Never reassigned
- **Reassigned** - Previously worked
- **Collection** - Partial payment received
- **Upsales** - Upgrade opportunities
- **Recalls** - Flagged for follow-up

#### Lead Assignment
- Manual assignment by admin
- Bulk assignment via CSV
- Agent-specific product types
- Automatic unassignment on agent deletion

### Agent Management

#### Agent Performance Tracking
- Payment count and total
- Conversion rates
- Callback adherence
- Break time monitoring
- Chargeback tracking

#### Agent Targets
- Stored as JSON in `agents.targets`
- Configurable per agent
- Used for commission calculation

#### Break Management
- Lunch, bathroom, meeting types
- Active break tracking
- Duration calculation
- Break history

### Back Office Workflow

#### Required Documents
- Retainer agreement
- Letter of acceptance
- Employment letter
- IELTS results
- Digital photo
- Eligibility form
- Passport copy
- Family documents
- Affidavit letter
- Reference letters
- Study plan
- Diploma/transcripts
- IMM forms (1294, 5645, 5476)
- Proof of funds
- Stamped passport pages

#### Document Statuses
- `required` - Not uploaded
- `review` - Under review
- `pre-approved` - Preliminary approval
- `approved` - Final approval

#### Progress Tracking
- Percentage based on pre-approved documents
- Visual progress bar in UI
- Agent notifications on status changes

### Retainer Generation

#### Process
1. Lead reaches payment threshold ($500+)
2. System generates PDF retainer
3. Retainer sent via email
4. Customer signs electronically
5. Signed retainer stored
6. Processing begins

#### Retainer Types
- File Opening (visas 10, 11, 12, 13, 16, 17, 18)
- Upgrade (all other visas)

### Callback System

#### Features
- Agent-specific callbacks
- Back office callbacks (separate)
- Calendar view integration
- Notification 5-10 minutes before
- Recall flagging for priority

#### Calendar Events
- 30-minute time slots
- Color-coded by lead status
- Filterable by agent
- UTC timezone handling

### Mailchimp Integration

#### Sync Triggers
- Lead disqualified
- Lead status changed
- Reason recorded

#### Synced Data
- Customer name and email
- Phone number
- Date of birth
- Country
- Lead number
- Disqualification reason
- Status tags

### CSV Import

#### Features
- Bulk lead creation
- Header mapping
- Validation and error reporting
- Agent assignment
- Language detection

#### Language Detection
- English-speaking countries auto-detected
- Spanish-speaking countries auto-detected
- Manual override available

### Commission & Chargebacks

#### Commission Tracking
- Per-agent payment totals
- Date range filtering
- Export functionality

#### Chargeback Management
- Record chargeback amount
- Link to agent
- Deduct from commission
- Historical tracking

---

## 11. Key Controllers

### LeadController
- Lead CRUD operations
- Payment link generation
- Document upload
- Comment management
- Callback scheduling
- Status updates
- Mailchimp sync

### AgentController
- Agent CRUD
- Performance metrics
- Target management
- Password reset

### PaymentController
- Payment tracking
- Opportunity identification
- Agent payment reports

### RetainerController
- Retainer generation
- PDF manipulation
- Signature capture
- Result tracking

### AdminController
- Lead assignment
- CSV import
- Customer management
- Credential management

### StripeController
- Checkout session creation
- Webhook handling
- Payment verification

### SquareController
- Payment link creation
- Webhook processing
- Order tracking

### AuthorizeController
- Hosted payment page
- Webhook management
- 3D Secure support

---

## 12. Enums

### LeadStatus
- `UPGRADE = 2`
- `FILE_OPENING = 5`

### UserRole
- `ADMIN = 0`
- `AGENT = 3`
- `CUSTOMER = 4`

### AgentType
- `UPGRADE = 2`
- `LEAD_ASSIGNER = 4`
- `BACK_OFFICE = 5`
- `CUSTOMER_SERVICE = 6`
- `FILE_OPENING = 7`

### PaymentStatus
- `SENT = 0`
- `SUCCESSFUL = 1`
- `FAILED = 2`
- `CANCELED = 3`

---

## 13. External Integrations

### SendGrid (Email)
- Template-based emails
- Payment link delivery
- Retainer delivery
- Dynamic content

### Twilio (SMS)
- Payment link SMS
- Multi-language support
- Bitly link shortening

### Mailchimp (Marketing)
- Lead synchronization
- Tag-based segmentation
- Automated campaigns

### Bitly (URL Shortening)
- SMS link optimization
- Click tracking

---

## 14. Commands (Artisan)

### FixLanguage
- Corrects language codes
- Batch processing

### GenerateRetainers
- Bulk retainer generation

### RemoveAuthorizeWebhooksCommand
- Cleanup old webhooks

### ResetChargebacks
- Reset chargeback counters

### ResetPasswords
- Bulk password reset

### UnassignLeads
- Bulk lead unassignment

---

## 15. Development Notes

### Code Style
- PSR-12 for PHP
- ESLint for JavaScript
- Stylelint for CSS

### Testing
- PHPUnit for backend
- Feature tests in `tests/Feature/`
- Unit tests in `tests/Unit/`

### Deployment
- Production check: `App::isProduction()`
- Environment-specific configs
- Asset compilation via Vite

### Performance
- Eager loading relationships
- Query optimization
- Redis caching (optional)
- Pagination on large datasets

---
