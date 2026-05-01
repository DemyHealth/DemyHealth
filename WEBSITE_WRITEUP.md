# DemyHealth Website Write-Up

_Last updated: May 1, 2026 (UTC)._

## Core Platform Features

- 📲 **AI diagnostics + wearable health integration**
- 🧬 **Genetic testing + personalized reports**
- 🩺 **Telehealth consultations (video/chat)**
- 📦 **E-commerce for at-home kits**
- 🧠 **SEO metadata for first-page Google ranking**
- 📍 **Branch directory + quick booking**

## Next Steps We Can Build

1. **User dashboard & secure login system**
2. **Admin panel for managing tests, doctors, users**
3. **AI-driven health insights engine (custom models or integrations)**
4. **CMS to update content (services, news, etc.)**
5. **Mobile-native version using React Native or Flutter**



## Popular Health Test Packages (Website Catalog Update)

> Currency reference: all prices listed in Nigerian Naira (₦).

### DNA & Forensics

**Test Package: DNA Paternity**  
**15 tests** — Establish biological relationships, ancestry, or support legal/forensic investigations.

- DNA Paternity × 1 — ₦190,000
- DNA Paternity × 2 — ₦260,000
- DNA Paternity × 3 — ₦330,000
- DNA Profile — ₦95,000
- Legal DNA Paternity × 1 — ₦230,000
- Legal DNA Paternity × 2 — ₦260,000
- Legal DNA Paternity × 3 — ₦350,000
- Legal DNA Paternity × 4 — ₦470,000
- Legal DNA Paternity × 5 — ₦590,000
- Legal DNA Profile — ₦120,000
- Grandparent Testing (both grandparents required) — ₦450,000
- Avuncular (Uncle/Aunty Testing) — ₦350,000
- Ancestry Testing — ₦500,000
- Paternity by Close Relation — ₦250,000
- Incestuous Testing — ₦300,000

### Genetic Screening

**Test Package: Genetic Screening**

- **NIPT panel (Down Syndrome/Trisomy 21, Edwards Syndrome/Trisomy 18, Patau Syndrome/Trisomy 13)** — ₦350,000
- **Non-Invasive Prenatal Paternity Testing (NIPPT)** — ₦1,990,000
- **Aneuploidy Screening + Sex Determination (CVS, amniotic fluid, blood) + Maternal Cell Contamination (MCC)** — ₦150,000
- **EGFR (29 Mutations)** — ₦150,000

### Lifestyle & Preventive Health

**Test Package: Cardio-Vascular Health Screening**  
**20 tests**

Includes renal function panel (E/U/Cr and electrolytes), lipid profile, urinalysis, urine microscopy/culture, full blood count, HbA1c, Vitamin D, Vitamin B12, and ECG.  
**Total: ₦123,700**

### Women’s Health

**Test Package: Gold Women’s Health Package**  
**47 tests**

Includes ESR, full blood count, urine/stool analysis and cultures, fasting/random glucose, infectious disease screening (HBV/HCV/HIV), renal panel, lipid profile, malaria and Widal screening, HbA1c, Vitamin D, liver function panel, uric acid, high vaginal swab, HPV DNA PCR, Pap smear, thyroid profile (T3/T4/TSH), H. pylori, C-reactive protein, and STI PCR tests.  
**Total: ₦288,800**

### Men’s Health

**Test Package: Gold Men Health Package**  
**44 tests**

Includes full blood count, urine/stool analysis and cultures, fasting/random glucose, infectious disease screening (HBV/HCV/HIV optional), renal panel, lipid profile, HbA1c, Vitamin D, malaria and Widal screening, liver function panel (including GGT), uric acid, PSA quantitative, thyroid profile, H. pylori, CRP, and multiplex STI PCR tests.  
**Total: ₦239,800**

### Children’s Health

**Test Package: Little Champs Package**  
**6 tests**

- Blood Group
- Genotype
- Full Blood Count
- Hepatitis B Screening
- Stool Microscopy
- Urine Microscopy

### Pregnancy

**Test Package: Pregnancy**  
**8 tests**

- Blood Group
- Genotype
- Full Blood Count
- Hepatitis B Screening
- Hepatitis C Screening
- HIV Screening
- VDRL Screening
- Urinalysis

**Total: ₦36,800**

### Sexual Health

**Test Package: Sexual Health Screening (Male/Female)**  
**8 tests** — Confidential STI and reproductive health screening.

- STI by PCR Method
- Neisseria gonorrhoeae PCR
- Chlamydia trachomatis PCR
- Ureaplasma urealyticum PCR
- Herpes Simplex Virus 1 & 2 PCR
- HIV 1 & 2 Test
- VDRL
- Urinalysis
- Urine Microscopy, Urine Culture

**Total: ₦76,300**

### Premarital Screening

**Test Package: Premarital Screening (Male)**  
**10 tests** — Compatibility and reproductive planning support.

- Blood Group
- Genotype
- Hepatitis B Screening
- Hepatitis C Screening
- HIV 1 & 2
- VDRL
- Urinalysis
- Urine Microscopy, Urine Culture
- Seminal Fluid Analysis

**Total: ₦47,100**

**Test Package: Premarital Screening (Female)**  
**9 tests** — Compatibility and reproductive planning support.

- Blood Group
- Genotype
- Hepatitis B Screening
- Hepatitis C Screening
- HIV 1 & 2
- VDRL
- Urinalysis
- Urine Microscopy, Urine Culture
- Pregnancy Test

**Total: ₦44,100**

### Pre-Employment Screening

**Test Package: Pre-Employment (Male)**  
**7 tests** — Baseline employee fitness/compliance profile.

- Genotype
- Blood Group
- Hepatitis B Screening
- Hepatitis C Screening
- HIV 1 & 2
- VDRL
- Urinalysis

**Total: ₦36,400**

**Test Package: Pre-Employment (Female)**  
**8 tests** — Baseline employee fitness/compliance profile.

- Genotype
- Blood Group
- Hepatitis B Screening
- Hepatitis C Screening
- HIV 1 & 2
- VDRL
- Urinalysis
- Pregnancy Test

**Total: ₦39,400**

**Test Package: Domestic Staff Screening**  
**7 tests**

- HIV Screening
- Widal Test
- Pregnancy Test
- VDRL
- Urinalysis
- Hepatitis A
- Hepatitis C

**Total: ₦29,600**

### Coming Soon

- Cancer predisposition testing via Multiplex PCR and MiSeq NGS
- DNA MMR gene mutations (MLH1, MSH2) for endometrial and colorectal cancer risk
- BRCA1 & BRCA2 mutation testing for hereditary breast and ovarian cancer (HBOC)
- Fetal genotype testing

## Backend Integration Blueprint

### 1) Appointments (booking + management)

**Core capabilities**
- Patient self-booking with branch, specialty, date/time, and visit type.
- Doctor/admin schedule management with availability slots.
- Appointment lifecycle states: `requested`, `confirmed`, `completed`, `cancelled`, `no_show`.
- Automated reminders via email/SMS/WhatsApp.

**Suggested API surface**
- `POST /api/v1/appointments` create appointment
- `GET /api/v1/appointments?patientId=&status=` list appointments
- `PATCH /api/v1/appointments/:id` reschedule/cancel/update status
- `GET /api/v1/availability?doctorId=&branchId=&date=` fetch slots

### 2) Results (labs + reports)

**Core capabilities**
- Secure upload/ingestion of result files and structured values.
- Patient dashboard with downloadable PDF reports and interpretation summaries.
- Result timeline with trend view for recurring metrics.
- Access controls for patient, doctor, and admin roles.

**Suggested API surface**
- `POST /api/v1/results` upload/create result record
- `GET /api/v1/results?patientId=` list patient results
- `GET /api/v1/results/:id` retrieve a specific result
- `GET /api/v1/results/:id/download` download report PDF

### 3) Payments (cards + wallets + invoices)

**Core capabilities**
- Checkout for tests, consultations, and kits.
- Payment intents, webhook confirmation, and reconciliation.
- Invoice and receipt generation for each successful payment.
- Refund flow for cancelled services.

**Suggested API surface**
- `POST /api/v1/payments/intent` create payment intent
- `POST /api/v1/payments/webhook` provider callback endpoint
- `GET /api/v1/payments?patientId=` list transactions
- `POST /api/v1/payments/:id/refund` request refund

### 4) Chat (patient support + telehealth messaging)

**Core capabilities**
- Real-time chat between patient and support/doctor.
- Persistent chat history and attachment support.
- Escalation to teleconsultation when needed.
- Basic moderation and abuse/reporting controls.

**Suggested API surface**
- `POST /api/v1/chats` create chat session
- `GET /api/v1/chats?userId=` list conversations
- `POST /api/v1/chats/:id/messages` send message
- `GET /api/v1/chats/:id/messages` fetch message history

### Shared backend foundations

- **AuthN/AuthZ:** JWT + refresh tokens, RBAC (`patient`, `doctor`, `admin`, `support`).
- **Data security:** encryption at rest, signed URLs for report downloads, audit logs.
- **Observability:** centralized logs, tracing, uptime alerts, and retry queues.
- **Compliance readiness:** consent tracking and immutable access logs for medical records.



## DH Automation Integration (Website + Operations)

To integrate the DH Automation scope into the website experience, add these modules across product pages, booking, and patient account flows:

### Website-facing automation features

- **Smart intake forms:** dynamic forms that change based on selected service, age band, symptoms, and chronic-risk profile.
- **Auto-triage routing:** automatically route users to at-home kit, teleconsultation, or in-clinic booking based on answers and urgency.
- **Automated communication journeys:** trigger confirmations, reminders, pre-test instructions, and post-result follow-ups.
- **Result-status notifications:** event-driven alerts when samples are received, processing starts, and reports are ready.
- **Support automation:** chatbot-first support with seamless handoff to live agent/doctor chat when confidence is low.

### Internal automation workflows

- **Appointment orchestration:** reserve slots, enforce buffer windows, and auto-release stale reservations.
- **Lab workflow automation:** barcode/sample tracking, status transitions, and anomaly escalation rules.
- **Billing automation:** invoice generation, payment reconciliation, retry logic for failed payments, and refund workflows.
- **Care-plan automation:** trigger personalized next steps from results (retest interval, referral, nutrition/lifestyle guidance).
- **Admin workflow automation:** SLA timers, queue prioritization, branch load balancing, and performance dashboards.

### Recommended automation stack (implementation-ready)

- **Workflow engine:** Temporal / n8n / Camunda for long-running health and payment workflows.
- **Event bus:** Kafka / RabbitMQ / SQS for reliable domain events (`appointment.created`, `result.ready`, `payment.failed`).
- **Notification providers:** multi-channel abstraction for email, SMS, WhatsApp, and push.
- **Rule engine layer:** configurable business rules editable by admins (no code deploy required for basic policy changes).
- **Audit + replay:** immutable workflow logs with replay support for incident recovery.

## SEO Strategy: Sitemap + Keyword Plan

### Proposed sitemap

- `/` (Home)
- `/about`
- `/services`
  - `/services/genetic-testing`
  - `/services/telehealth-consultation`
  - `/services/ai-diagnostics`
  - `/services/wearable-integration`
  - `/services/at-home-kits`
- `/tests`
  - `/tests/<test-slug>`
- `/doctors`
  - `/doctors/<doctor-slug>`
- `/branches`
  - `/branches/<city-or-branch-slug>`
- `/book`
- `/results-login`
- `/shop`
  - `/shop/<product-slug>`
- `/blog`
  - `/blog/<article-slug>`
- `/faq`
- `/contact`
- `/privacy-policy`
- `/terms`

### Page-level SEO metadata framework

For each indexable page, define:
- Unique `title` (50–60 chars target)
- Unique `meta description` (140–160 chars target)
- Canonical URL
- Open Graph + Twitter cards
- FAQ schema / service schema where relevant

### Initial keyword clusters

**Commercial intent (conversion pages)**
- "genetic testing near me"
- "at-home health test kits"
- "online doctor consultation"
- "telehealth consultation [city]"
- "book lab test online"

**Informational intent (blog/content hub)**
- "how wearable devices detect health risks"
- "understanding DNA health reports"
- "when to use telehealth vs in-person visit"
- "best preventive health tests by age"
- "how to read blood test results"

**Local SEO intent (branch pages)**
- "diagnostic center in [city]"
- "health checkup clinic [city]"
- "genetic testing lab [city]"
- "telehealth services [city]"

### 90-day SEO execution sequence

1. Publish sitemap.xml + robots.txt and submit to Google Search Console.
2. Launch optimized service pages and branch landing pages.
3. Publish 2–3 high-intent blog posts per week mapped to keyword clusters.
4. Implement schema markup and internal linking between service, branch, and blog pages.
5. Track rankings, CTR, and conversions; refresh underperforming pages monthly.
