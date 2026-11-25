# Webotapp Coaching Institute Management ERP
## Enterprise Multi-Branch Coaching, Academy & Learning Management System
### Built with Laravel 11, PHP 8.4, MySQL 8, Tailwind CSS, Alpine.js & Chart.js

---

## 1. Executive Summary & Capabilities
**Webotapp Coaching Institute Management ERP** is an enterprise operations management suite built specifically for educational institutions, test-prep academies (IIT-JEE / NEET / UPSC), IT bootcamps, and vocational training centers.

The application manages the entire educational operations lifecycle:

```text
Lead Inquiries ➔ Follow-ups ➔ Counselling ➔ Admission ➔ Batch Allocation ➔ Installment Dues 
➔ Cashier Payments ➔ Biometric Attendance ➔ LMS & Assignments ➔ Examinations ➔ Report Cards ➔ QR-Verified Certificates
```

---

## 2. Default Demo Login Credentials
The system comes pre-seeded with relationally consistent enterprise demo data matching Section 45 of the specification:

| Role | Email Address | Password | Capabilities |
|---|---|---|---|
| **Super Administrator** | `admin@coaching.com` | `password123` | Unrestricted global access, branch settings, financial reports, all campuses |
| **Branch Manager** | `manager.central@webotapp.com` | `password123` | Campus-level operations, batch timetables, admissions |
| **Academic Counsellor** | `counsellor1@webotapp.com` | `password123` | Leads CRM, pipeline, follow-up interactions, new admissions |
| **Chief Accountant** | `accounts@webotapp.com` | `password123` | Fee dues ledger, cashier collection desk, payment receipts, branch expenses |
| **Senior Faculty** | `alok.nath@webotapp.com` | `password123` | Daily attendance register, examinations, marks entry, LMS study materials |
| **Student** | `student1.aarav@apexcoach.test` | `password123` | 360-degree student profile, fees ledger, scorecards, certificates |

*Tip: The login page includes quick **One-Click Demo Login** buttons for instant role switching.*

---

## 3. Verified System Data (Section 45 Compliance)
- **Company**: 1 (`Webotapp EdTech Global Pvt Ltd`)
- **Institutes**: 2 (`Webotapp Academy`, `Webotapp Junior Science`)
- **Campuses / Branches**: 3 (`Indiranagar Central`, `Hebbal North`, `Digital / Hybrid Hub`)
- **Courses**: 10 (Digital Marketing, Web Development, Flutter, AI/ML, Python, Computer Fundamentals, Spoken English, Class 10 Tuition, JEE/NEET Prep, Tally Prime Accounting)
- **Subjects**: 30 Master Curriculum Subjects
- **Batches**: 20 Active Cohorts (Morning, Evening, Weekend)
- **Classrooms**: 10 High-tech Labs & Lecture Theatres
- **Students**: 100 Enrolled Students
- **Admissions / Enquiries**: 100 Leads & 100 Confirmed Admissions
- **Attendance Records**: 100 Session Biometric Attendance Logs
- **Fee Invoices & Payments**: 75 Fee Invoices & 50 Verified Cashier Payment Receipts
- **Examinations**: 10 Comprehensive Assessments & 100 Evaluated Scorecards
- **Assignments**: 20 Practical Lab Projects
- **Study Materials**: 30 Curated Handouts & Video Lectures
- **Certificates**: 10 Digitally Verifiable Credentials
- **Operating Expenses**: 10 Branch Administrative Vouchers

---

## 4. Key Functional Modules
1. **Executive Dashboard (`/dashboard`)**:
   - Real-time KPI metric cards (Students, Leads, Conversion Rate, Gross Fee Collections, Expenses, Net EBITDA Surplus).
   - Interactive Chart.js Cashflow Bar Chart & CRM Funnel Doughnut Chart.
   - Campus switcher dropdown supporting consolidated or campus-isolated views.
2. **Enquiries & Leads CRM (`/enquiries`)**:
   - Complete pipeline tracking (New, Contacted, Counselling Scheduled, Demo Attended, Converted, Dropped).
   - Priority badges (Urgent, High, Medium, Low).
   - Interaction history timeline with conversation logging and next follow-up dates.
   - 1-Click "Convert to Admission" pipeline bridge.
3. **Admissions & Enrolments (`/admissions`)**:
   - Official admission contract generation with automated Roll Number & Admission Number.
   - Dynamic fee calculator with scholarship/waiver deduction and installment split (1, 2, 3, 4, or 6 periods).
   - Optional instant down-payment cashier settlement right at enrollment desk.
4. **Student 360° Profile (`/students/{student}`)**:
   - Consolidated single pane of glass: Demographics, Guardian info, Assigned batches, Live attendance percentage, Fee dues ledger, Receipts, Exam scorecards, and Certificates.
5. **Courses & Syllabus (`/courses`)**:
   - Multi-category curriculum, duration definitions, base fee + 18% GST tax computation, subject mapping with theory vs practical lab tags.
6. **Batches & Timetable (`/batches`)**:
   - Physical classroom seating capacity tracking, faculty mentor assignment, weekly lecture timetable grid.
7. **Daily Attendance Register (`/attendance`)**:
   - Cohort attendance sheet with date picker, "All Present" / "All Absent" bulk toggles, late check-in tracking with arrival remarks.
8. **Fee Management & Cashier Desk (`/fees`)**:
   - Installment schedule ledger, collection modal supporting UPI, Cash, POS Card, IMPS/NEFT, and Cheque.
   - Instant generation of official printable Fee Receipts with institute GSTIN, itemized breakdown, and amount in words.
9. **Examinations & Report Cards (`/exams`)**:
   - Offline, Online CBT, and Hybrid exam scheduling.
   - Question bank curation with MCQs and explanations.
   - Rapid marks entry sheet with automatic percentage, grade (A+, A, B, C, F), and pass/fail determination.
   - Printable individual Student Scorecards / Academic Progress Reports.
10. **LMS & Assignments (`/lms/assignments` & `/lms/materials`)**:
    - Project deadlines, lab deliverables, lecture slide handouts, and video links.
11. **Certificates & Public Verification (`/certificates`)**:
    - Gold-bordered completion certificates with verification QR codes.
    - Public employer verification endpoint: `/verify-certificate/{code}`.
12. **Operating Expenses (`/expenses`)**:
    - Vouchers categorized under Utilities, Rent, Internet, Marketing, Printing, and Guest Faculty.
13. **Executive Analytics & Settings (`/reports` & `/settings`)**:
    - Cross-campus revenue distribution, payment method split, marketing lead sources attribution.
    - Multi-branch administration, staff user accounts, and system master parameters.

---

## 5. Verification & Automated Test Suite
Run the automated test suite anytime using:
```bash
php artisan test
```
All 17 integration and workflow feature tests pass with 61 assertions covering role dashboard isolation, authentication, CRM leads, student admissions, fee payments, attendance marking, report cards, and public certificate verification.

---

## 6. Local Server Running
The local application server is active on:
```
http://127.0.0.1:8000
```
- Direct access: `http://127.0.0.1:8000/login`
- Sample Certificate Verification: `http://127.0.0.1:8000/verify-certificate/VERIF-PVQUKXJXJKBP`


---

## 7. Video Learning & Live Classroom

The `feature/video-learning-live-classes` branch adds a Video Learning Hub at `/video-learning`.

### Included in this branch
- Role-aware recorded-course library linked to existing ERP courses.
- Course and lesson management for Admin, Branch Manager and Faculty.
- Recorded lesson player for YouTube, Vimeo and direct MP4/WebM/OGG URLs.
- Student lesson progress checkpoints and completion status.
- Batch-aware live-class scheduling and access.
- Integrated LiveKit browser classroom with faculty publishing controls and student listen/watch mode.
- Automatic MP4 recording through LiveKit Egress to S3-compatible storage.
- Live attendance updates from participant webhooks.
- Previous-class recording library with short-lived signed playback URLs.

### LiveKit and recording prerequisites
Live classes and automatic recording are disabled until the following values are configured in the application environment:

```dotenv
APP_TIMEZONE=Asia/Kolkata

LIVEKIT_URL=wss://YOUR_LIVEKIT_HOST
LIVEKIT_API_KEY=YOUR_LIVEKIT_API_KEY
LIVEKIT_API_SECRET=YOUR_LIVEKIT_API_SECRET

AWS_ACCESS_KEY_ID=YOUR_RECORDING_STORAGE_ACCESS_KEY
AWS_SECRET_ACCESS_KEY=YOUR_RECORDING_STORAGE_SECRET
AWS_DEFAULT_REGION=YOUR_BUCKET_REGION
AWS_BUCKET=YOUR_RECORDING_BUCKET
AWS_ENDPOINT=
AWS_USE_PATH_STYLE_ENDPOINT=false

LIVEKIT_RECORDING_BUCKET=${AWS_BUCKET}
LIVEKIT_RECORDING_ACCESS_KEY=${AWS_ACCESS_KEY_ID}
LIVEKIT_RECORDING_SECRET_KEY=${AWS_SECRET_ACCESS_KEY}
LIVEKIT_RECORDING_REGION=${AWS_DEFAULT_REGION}
LIVEKIT_RECORDING_ENDPOINT=${AWS_ENDPOINT}
LIVEKIT_RECORDING_FORCE_PATH_STYLE=${AWS_USE_PATH_STYLE_ENDPOINT}
```

Use a private S3-compatible bucket. The LiveKit Egress service needs permission to write recordings to it, and the Laravel application needs permission to create short-lived playback URLs. For a custom S3-compatible service, set the same endpoint in `AWS_ENDPOINT` and `LIVEKIT_RECORDING_ENDPOINT`.

Configure the LiveKit webhook to call:

```text
https://YOUR_PUBLIC_HTTPS_HOST/webhooks/livekit
```

Enable the `room_finished`, `participant_joined`, `participant_left` and `egress_ended` events. The endpoint validates LiveKit's signed webhook token and raw-body hash before updating class attendance, room status or recording status.

For LiveKit Cloud, use the project URL and API credentials from its dashboard. For self-hosting, deploy LiveKit Egress as well as the LiveKit server; the current `StartEgress` API requires LiveKit server v1.13.5 or later when self-hosted.

### Run locally
After cloning this branch:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure your database and run:

```bash
php artisan migrate --seed
php artisan optimize:clear
php artisan serve
```

Open `http://127.0.0.1:8000/video-learning` after signing in. Use the seeded Admin, Faculty and Student demo accounts listed above only in a local development environment. A faculty member schedules a class and clicks **Start & record**; students in the eligible course/batch can then join. The recording appears in Previous Class Recordings after LiveKit sends the completed Egress webhook.

**Note:** Recorded lessons currently accept externally hosted video URLs. This branch does not yet implement direct large-file upload, DRM or adaptive HLS packaging for uploaded course videos. Live classroom and automatic recording also require valid LiveKit and object-storage credentials; the UI alone does not provision those services.
