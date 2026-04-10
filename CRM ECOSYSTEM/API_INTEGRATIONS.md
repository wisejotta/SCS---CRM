# FlowState CRM — API Integrations & Platform Compatibility

This document provides a consolidated reference for the external APIs FlowState CRM works with, the internal API surface the platform exposes, and how the platform accommodates different operational models.

---

## 1) External APIs & Services

### Payments
| Service | Purpose | Config File | Key Environment Variables |
|---|---|---|---|
| Stripe | Checkout/session payments and webhook confirmation | `config/stripe.php` | `STRIPE_SECRET_KEY`, `STRIPE_ENDPOINT_SECRET`, `STRIPE_PUBLISHABLE_KEY` |
| Square | Payment link creation and webhook handling | `config/square.php` | `SQUARE_APPLICATION_ID`, `SQUARE_ACCESS_TOKEN`, `SQUARE_LOCATION_ID`, `SQUARE_ENVIRONMENT` |
| Authorize.net | Hosted payment flow and webhook events | `config/authorize.php` | `AUTHORIZE_BASE_URL`, `AUTHORIZE_PAYMENT_BASE_URL`, `AUTHORIZE_MERCHANT_NAME`, `AUTHORIZE_TRANSACTION_KEY` |

### Communications & Marketing
| Service | Purpose | Config File | Key Environment Variables |
|---|---|---|---|
| SendGrid | Transactional emails (payment links, contracts/retainers) | `config/sendgrid.php` | `SENDGRID_API_KEY`, `SENDGRID_TEMPLATE_ID`, `SENDGRID_CONTRACT_TEMPLATE_ID` |
| Twilio | SMS delivery for customer/lead communication | `config/twilio.php` | `TWILIO_ACCOUNT_SID`, `TWILIO_AUTH_TOKEN`, `TWILIO_NUMBER` |
| Mailchimp | Audience/list automation and marketing sync | `config/mailchimp.php` | `MAILCHIMP_API_KEY`, `MAILCHIMP_BASE_URL`, `MAILCHIMP_LIST_ID` |
| Bitly | Link shortening and tracking support | `config/bitly.php` | `BITLY_API_KEY` |

### Optional Service Connectors (Laravel defaults)
| Service | Purpose | Config File | Key Environment Variables |
|---|---|---|---|
| Mailgun | Mail transport option | `config/services.php` | `MAILGUN_DOMAIN`, `MAILGUN_SECRET`, `MAILGUN_ENDPOINT` |
| Postmark | Mail transport option | `config/services.php` | `POSTMARK_TOKEN` |
| AWS SES | Mail transport option | `config/services.php` | `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION` |

---

## 2) Public/Internal API Surface

FlowState CRM exposes REST endpoints under `routes/api.php` for operational modules:

- **Auth & User Lifecycle** (`/api/users/*`, `/api/user`)
- **Lead Management** (`/api/leads/*`)
- **Payments & Opportunities** (`/api/payments/*`)
- **Agent Operations** (`/api/agents/*`, `/api/breaks/*`)
- **Customer Portal** (`/api/customers/*`)
- **Retainers** (`/api/retainers/*`)
- **Admin Operations** (`/api/admin/*`)
- **Chargeback Management** (`/api/chargebacks/*`)
- **Conversion Rates** (`/api/rates/*`)

There is also an authenticated ingestion endpoint for external systems:

- `POST /api/v1/leads` (protected by `auth.basic`)

---

## 3) Webhook/Callback Endpoints

These endpoints support asynchronous updates from payment providers:

- `POST /api/s/return` (Stripe return handling)
- `POST /api/sq/webhook` (Square webhook)
- `POST /api/authorize/webhook/{payment}` (Authorize.net webhook)

---

## 4) Platform Compatibility & Accommodation

FlowState CRM is built to accommodate multiple business operating styles:

1. **Role-driven operations**  
   Supports Admin, Agent, and Customer user models, plus specialized agent types (upgrade, lead assigner, back office, customer service, file opening).

2. **Multi-gateway payment operations**  
   Payment flow can be routed through Stripe, Square, or Authorize.net based on geography, fee profile, or operational preference.

3. **Channel-flexible communication**  
   Email (SendGrid) and SMS (Twilio) allow customer communication based on urgency and channel preference.

4. **Marketing + sales handoff**  
   Mailchimp/Bitly integrations support capture → nurture → conversion workflows.

5. **Back-office document workflows**  
   Lead and retainer document milestones support structured fulfillment and post-sale processing.

6. **Integration-friendly architecture**  
   Config-driven credentials and modular controllers/routes allow additional gateways/providers with minimal core disruption.

---

## 5) Onboarding Checklist for a New Deployment

1. Configure `.env` API credentials for required providers.
2. Validate payment webhooks for Stripe/Square/Authorize.net.
3. Verify SendGrid template IDs and Twilio sender number.
4. Configure Mailchimp list and base URL (if marketing automation is enabled).
5. Smoke-test critical API routes:
   - auth login/verify
   - lead creation + payment-link generation
   - webhook callbacks
   - retainer send/sign flow

