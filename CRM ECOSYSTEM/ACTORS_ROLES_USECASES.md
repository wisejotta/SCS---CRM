# SCS - CRM: Actors, Roles & Use Cases

## System Actors

### 1. ADMIN (Role: 0)
**Description**: System administrator with full access to all features and data.

**Responsibilities**:
- Manage all agents and users
- Assign leads to agents
- Import leads via CSV
- Monitor system performance
- View all leads and payments
- Manage conversion rates
- Reset agent passwords
- Remove agents from system
- Access all reports and analytics

**Use Cases**:

#### UC-1.1: Import Leads from CSV
1. Upload CSV file with lead data
2. Map CSV headers to system fields
3. Validate data format
4. Review errors if any
5. Confirm import
6. System creates leads and customers

#### UC-1.2: Assign Leads to Agents
1. View unassigned leads
2. Select leads (single or bulk)
3. Choose target agent
4. Confirm assignment
5. System updates lead.agent_id and lead.assigned_at

#### UC-1.3: Create New Agent
1. Enter agent details (name, email, type)
2. Set agent type (Upgrade, File Opening, Back Office, etc.)
3. Configure targets and permissions
4. System creates user and agent records
5. Agent receives credentials

#### UC-1.4: View System Analytics
1. Access admin dashboard
2. View total leads, conversions, revenue
3. Filter by date range, agent, visa type
4. Export reports
5. Monitor agent performance

#### UC-1.5: Manage Closed Leads
1. View closed/disqualified leads
2. Filter by reason (not interested, no funds, etc.)
3. Put leads back into system
4. Permanently delete leads
5. View Mailchimp sync status

---

### 2. AGENT - UPGRADE (Type: 2)
**Description**: Sales agent focused on upselling existing customers to premium visa services.

**Responsibilities**:
- Contact assigned upgrade leads
- Sell premium visa packages
- Send payment links
- Track customer interactions
- Schedule callbacks
- Add comments to leads
- Upload customer documents

**Use Cases**:

#### UC-2.1: Work Fresh Lead
1. View assigned fresh leads list
2. Select lead to contact
3. Review customer information
4. Call customer using provided phone number
5. Discuss visa options
6. Add comment about conversation
7. Schedule callback if needed

#### UC-2.2: Send Payment Link
1. Open lead details
2. Select visa product
3. Enter payment amount
4. Choose payment gateway (Stripe/Square/Authorize)
5. Select delivery method (Email/SMS)
6. Choose language (EN/ES/FR)
7. System generates and sends link
8. Track payment status in timeline

#### UC-2.3: Handle Callback
1. View callback calendar
2. Receive notification 5-10 minutes before
3. Call customer at scheduled time
4. Update lead status
5. Send payment link or reschedule
6. Mark callback complete

#### UC-2.4: Track Upsale Opportunity
1. View leads with payment history
2. Identify upgrade opportunities
3. Contact customer about premium services
4. Process additional payment
5. Update visa type

---

### 3. AGENT - FILE OPENING (Type: 7)
**Description**: Agent handling new visa applications and initial customer intake.

**Responsibilities**:
- Process new visa applications
- Collect initial customer information
- Send initial payment links
- Verify customer eligibility
- Transfer to back office after payment

**Use Cases**:

#### UC-3.1: Create New Lead
1. Receive customer inquiry
2. Enter customer details (name, email, country, DOB)
3. Select visa type
4. Enter contact information
5. System creates customer and lead
6. Lead appears in agent's queue

#### UC-3.2: Process Initial Payment
1. Verify customer information
2. Explain visa process and fees
3. Send payment link for initial fee
4. Wait for payment confirmation
5. Transfer to back office when paid $500+

#### UC-3.3: Handle Collection
1. View leads with partial payments
2. Contact customer for remaining balance
3. Send follow-up payment link
4. Track payment progress
5. Complete when full amount received

---

### 4. AGENT - LEAD ASSIGNER (Type: 4)
**Description**: Supervisor who distributes leads to appropriate agents.

**Responsibilities**:
- View all unassigned leads
- Assign leads based on agent capacity
- Monitor agent workload
- Reassign leads when needed
- View all callbacks system-wide

**Use Cases**:

#### UC-4.1: Distribute New Leads
1. View unassigned leads queue
2. Check agent availability and targets
3. Select leads to assign
4. Choose target agent
5. Bulk assign leads
6. System notifies agent

#### UC-4.2: Reassign Inactive Leads
1. View leads with no activity
2. Identify stale leads (no contact in X days)
3. Unassign from current agent
4. Reassign to different agent
5. Mark as reassigned (lead.reassigned_at)

#### UC-4.3: Monitor Agent Performance
1. View agent dashboard
2. Check conversion rates
3. Review callback adherence
4. Identify underperforming agents
5. Redistribute workload

---

### 5. AGENT - BACK OFFICE (Type: 5)
**Description**: Document processor handling visa application paperwork.

**Responsibilities**:
- Process leads with $500+ payments
- Request required documents
- Review uploaded documents
- Update document status
- Track application progress
- Communicate with immigration authorities

**Use Cases**:

#### UC-5.1: Review Lead Documents
1. View back office leads queue
2. Select lead with sufficient payment
3. Review required documents checklist
4. Check uploaded documents
5. Update status (required/review/pre-approved/approved)

#### UC-5.2: Request Missing Documents
1. Identify missing documents
2. Contact customer via email/phone
3. Provide document specifications
4. Set callback for follow-up
5. Track document submission

#### UC-5.3: Upload Customer Documents
1. Receive documents from customer
2. Upload to lead profile
3. Categorize by document type
4. Update document status to "review"
5. Notify customer of receipt

#### UC-5.4: Process Application
1. Verify all documents approved
2. Generate retainer agreement
3. Send retainer for signature
4. Submit application to authorities
5. Update lead results with tracking info

#### UC-5.5: Track Application Progress
1. Monitor application status
2. Update lead.results with milestones
3. Communicate updates to customer
4. Handle additional document requests
5. Notify customer of approval/rejection

---

### 6. AGENT - CUSTOMER SERVICE (Type: 6)
**Description**: Support agent handling customer inquiries and issues.

**Responsibilities**:
- Answer customer questions
- Resolve payment issues
- Update customer information
- Handle complaints
- Coordinate with other departments

**Use Cases**:

#### UC-6.1: Handle Customer Inquiry
1. Receive customer contact
2. Look up lead by ID/email/phone
3. Review lead history
4. Answer questions about status
5. Add comment with inquiry details

#### UC-6.2: Resolve Payment Issue
1. Customer reports payment problem
2. Check payment timeline
3. Verify payment gateway status
4. Resend payment link if needed
5. Escalate to admin if unresolved

#### UC-6.3: Update Customer Information
1. Customer requests info change
2. Verify customer identity
3. Update fields (phone, email, address)
4. Save changes
5. Confirm update with customer

---

### 7. CUSTOMER (Role: 4)
**Description**: End user applying for visa services.

**Responsibilities**:
- Complete payment
- Upload required documents
- Sign retainer agreement
- Respond to agent requests
- Track application status

**Use Cases**:

#### UC-7.1: Complete Payment
1. Receive payment link via email/SMS
2. Click link to payment page
3. Enter payment details
4. Submit payment
5. Receive confirmation
6. System updates payment status

#### UC-7.2: Sign Retainer Agreement
1. Receive retainer link via email
2. Review retainer terms
3. Sign electronically using signature pad
4. Submit signed retainer
5. Receive copy via email

#### UC-7.3: Access Customer Portal
1. Login with credentials
2. View application status
3. See required documents
4. Upload documents
5. View payment history

#### UC-7.4: Upload Documents
1. Login to customer portal
2. View document checklist
3. Select document type
4. Upload file (PDF/JPG)
5. Receive upload confirmation

---

## Actor Interaction Flows

### Flow 1: New Lead to Closed Deal
```
Admin → Creates/Imports Lead
  ↓
Lead Assigner → Assigns to File Opening Agent
  ↓
File Opening Agent → Contacts customer, sends payment link
  ↓
Customer → Completes payment
  ↓
Back Office Agent → Processes documents
  ↓
Customer → Signs retainer
  ↓
Back Office Agent → Submits application
  ↓
Customer Service → Provides status updates
  ↓
Back Office Agent → Closes lead with results
```

### Flow 2: Upgrade Sale
```
Lead Assigner → Assigns lead to Upgrade Agent
  ↓
Upgrade Agent → Contacts customer about premium service
  ↓
Customer → Agrees to upgrade
  ↓
Upgrade Agent → Sends payment link for difference
  ↓
Customer → Completes payment
  ↓
Back Office Agent → Processes upgraded application
```

### Flow 3: Payment Issue Resolution
```
Customer → Reports payment problem
  ↓
Customer Service → Investigates issue
  ↓
Customer Service → Resends payment link or escalates
  ↓
Admin → Resolves gateway issue if needed
  ↓
Customer → Completes payment
  ↓
Original Agent → Continues process
```

---

## Permission Matrix

| Feature | Admin | Upgrade | File Opening | Lead Assigner | Back Office | Customer Service | Customer |
|---------|-------|---------|--------------|---------------|-------------|------------------|----------|
| View All Leads | ✓ | - | - | ✓ | - | - | - |
| View Assigned Leads | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | - |
| Create Lead | ✓ | ✓ | ✓ | ✓ | - | - | - |
| Assign Lead | ✓ | - | - | ✓ | - | - | - |
| Send Payment Link | ✓ | ✓ | ✓ | - | - | - | - |
| Process Payment | - | - | - | - | - | - | ✓ |
| Upload Documents | ✓ | ✓ | ✓ | - | ✓ | - | ✓ |
| Review Documents | ✓ | - | - | - | ✓ | - | - |
| Generate Retainer | ✓ | - | - | - | ✓ | - | - |
| Sign Retainer | - | - | - | - | - | - | ✓ |
| Add Comments | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | - |
| Schedule Callback | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | - |
| View All Callbacks | ✓ | - | - | ✓ | - | - | - |
| Manage Agents | ✓ | - | - | - | - | - | - |
| Import CSV | ✓ | - | - | - | - | - | - |
| View Reports | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | - |
| Manage Chargebacks | ✓ | - | - | - | - | - | - |
| Delete Leads | ✓ | - | - | - | - | - | - |
| Access Customer Portal | - | - | - | - | - | - | ✓ |

---

## Agent Type Product Access

### Upgrade Agent (Type: 2)
**Products**: All visas EXCEPT File Opening visas (10, 11, 12, 13, 16, 17, 18)
- Premium visa packages
- Express processing
- Family sponsorship
- Work permits (premium)

### File Opening Agent (Type: 7)
**Products**: File Opening visas ONLY (10, 11, 12, 13, 16, 17, 18)
- Study visa
- Tourism visa
- Work visa (basic)
- Express entry

### Lead Assigner (Type: 4)
**Products**: ALL visas
- Can assign any visa type to appropriate agent

### Back Office (Type: 5)
**Products**: ALL visas
- Processes all visa types

### Customer Service (Type: 6)
**Products**: ALL visas (view only)
- Supports all visa types

---

## Typical Daily Workflows

### Admin Daily Tasks
1. Review overnight lead imports
2. Assign new leads to agents
3. Monitor payment success rates
4. Review agent performance metrics
5. Handle escalated issues
6. Update conversion rates if needed

### Upgrade Agent Daily Tasks
1. Check callback calendar
2. Contact scheduled callbacks
3. Work fresh leads queue
4. Follow up on sent payment links
5. Identify upsale opportunities
6. Update lead comments
7. Take scheduled breaks

### File Opening Agent Daily Tasks
1. Process new lead inquiries
2. Send initial payment links
3. Follow up on partial payments
4. Transfer paid leads to back office
5. Handle collection leads
6. Schedule callbacks

### Back Office Agent Daily Tasks
1. Review leads with $500+ payments
2. Request missing documents
3. Review uploaded documents
4. Update document statuses
5. Generate retainers for complete applications
6. Submit applications to authorities
7. Update application tracking

### Customer Service Daily Tasks
1. Respond to customer inquiries
2. Resolve payment issues
3. Update customer information
4. Coordinate with other agents
5. Handle complaints
6. Provide status updates

---

## Key Performance Indicators (KPIs)

### Admin KPIs
- Total leads in system
- Conversion rate (leads to paid)
- Revenue per agent
- Average time to close
- System uptime

### Agent KPIs
- Leads contacted per day
- Conversion rate
- Average payment amount
- Callback adherence
- Customer satisfaction
- Chargebacks

### Back Office KPIs
- Documents processed per day
- Average processing time
- Application approval rate
- Document error rate

### Customer Service KPIs
- Response time
- Issue resolution rate
- Customer satisfaction
- Escalation rate

---

**Summary**: The SCS-CRM system supports 7 distinct actor types, each with specific roles, responsibilities, and use cases designed to streamline the visa application process from initial contact through final approval.
