## 4. Installation & Setup

### Prerequisites
- PHP >= 8.0.2
- Composer
- Node.js & npm/yarn
- MySQL
- Redis (optional)

### Backend Setup
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

### Frontend Setup
```bash
npm install
npm run dev        # Development
npm run build      # Production
```

---

## 5. Database Schema

### users
- id, firstname, lastname, email, password, role, deleted_at

### customers
- id, user_id, country, residence, dob, gender, phone_number, office_number, profession, marital_status, education, language, occupation, experience, spouse (json)

### agents
- id, user_id, type (enum), targets (json), password_reset (json), chargebacks

### leads
- id, customer_id, agent_id, visa_id, status (enum), phone_number, email, callback, bo_callback, assigned_at, reassigned_at, callback_agent_id, callback_bo_agent_id, bo_agent_id, cs_agent_id, recall, language, reason (json), results (json), backoffice (json), mailchimp_id, sort_date

### payments
- id, lead_id, agent_id, amount, status (enum), lead_status, lan, misc (json), complete, completed_at

### visas
- id, name, price, external_id, order

### retainers
- id, lead_id, visa_id, uuid, results (json)

### comments
- id, lead_id, agent_id, comment

### documents
- id, lead_id, agent_id, original_name, file_path

### agent_breaks
- id, agent_id, type, active, started_at, ended_at

### chargebacks
- id, agent_id, amount, reason

### conversion_rates
- id, rate

---

## 6. API Documentation

### Authentication
- `POST /api/users/login` - Login
- `GET /api/users/verify` - Verify token

### Leads
- `POST /api/leads/{type}` - Get leads (fresh-leads, reassigned, collection, upsales)
- `GET /api/leads/{lead}` - Get lead details
- `POST /api/leads` - Create lead
- `PUT /api/leads/{lead}/{field}` - Update field
- `POST /api/leads/{lead}/comments` - Add comment
- `POST /api/leads/{lead}/payment-link` - Send payment link
- `POST /api/leads/callbacks/{lead}` - Add callback
- `POST /api/leads/files/{lead}` - Upload files
- `POST /api/leads/set-status/{lead}/{status}` - Set status/reason
- `POST /api/leads/back-office` - Get back office leads
- `POST /api/leads/{lead}/back-office/doc/{slug}` - Upload BO document
- `GET /api/leads/{lead}/back-office/{slug}/{status}` - Update doc status

### Agents
- `POST /api/agents` - List agents
- `GET /api/agents/{user}` - Get agent
- `PUT /api/agents` - Create agent
- `POST /api/agents/{user}` - Update agent
- `DELETE /api/agents/{user}` - Delete agent

### Payments
- `POST /api/payments/type/{type}` - Get payments by type
- `POST /api/payments/{user}` - Get agent payments
- `POST /api/payments/opportunities` - Get opportunities

### Retainers
- `POST /api/retainers/{lead}/send` - Send retainer
- `POST /api/retainers/{lead}/results` - Update results
- `GET /api/retainers/{uuid}` - Get retainer
- `POST /api/retainers/{uuid}/sign` - Sign retainer

### Breaks
- `POST /api/breaks` - List breaks
- `GET /api/breaks/start/{type}` - Start break
- `GET /api/breaks/stop` - Stop break

### Chargebacks
- `GET /api/chargebacks` - List chargebacks
- `POST /api/chargebacks` - Create chargeback

---
