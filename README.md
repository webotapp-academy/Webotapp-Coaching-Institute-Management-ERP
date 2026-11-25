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

## 7. Video Learning: Zoom Live Sessions + Google Drive Courses

The `feature/video-learning-live-classes` branch adds a Video Learning Hub at `/video-learning`.

### Live sessions (Zoom invitation links)
- Admin / Branch Manager / Faculty schedules a live session in Webotapp.
- Paste the Zoom invitation URL, session title, batch, faculty, start/end time and the live-session price.
- Eligible students see the session details and click **Join Zoom meeting**. The Zoom app or browser opens.
- Webotapp does not host the video call and does not need Zoom API credentials.
- Zoom attendance is not automatically imported. Faculty should use Zoom's participant report or mark attendance through the ERP attendance module.

### Recorded video courses (Google Drive)
- Create a recorded course linked to an ERP course and set one price for that recorded course.
- Upload each MP4 video to Google Drive and paste its sharing link as a lesson.
- Drive videos open in the Webotapp lesson player using Google Drive's preview player.
- Faculty can also add a Google Drive replay link to a past Zoom live session.
- Lesson completion is marked by the student because an embedded Google Drive player does not expose playback progress to the Webotapp application.


**Pricing implementation note:** The recorded-course and live-session prices are stored and displayed separately. This branch does not yet include checkout, payment collection, payment verification, or paid-access locking. Complete those workflows before using the displayed prices to sell content; until then, links are visible to eligible enrolled students.

### Google Drive sharing checklist
For embedded playback, set the video file's Google Drive **General access** to **Anyone with the link — Viewer**, unless you individually share it with every student who needs access. A file set to Restricted will not play for students who do not have permission.

**Important privacy / resale note:** Anyone with the link can open and potentially forward an Anyone-with-the-link video URL. This is simple and does not require paid hosting, but it is not DRM or strong paid-content protection. Restrict sharing or manually share to named student Google accounts for more control.

### Free-plan limitations
- Zoom Basic meetings are generally limited to 40 minutes and 100 participants. Check the meeting host's Zoom plan before selling longer classes.
- Zoom Basic supports local computer recording in the desktop app; cloud recording is a paid-plan feature. Faculty can record locally, then upload the MP4 to Google Drive and paste the Drive link into Webotapp.
- Google Drive storage is shared with Gmail and Google Photos under the Google account's storage quota.
- No LiveKit, Jibri, AWS S3, paid video CDN or Google Drive API credentials are required by this module.

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

Sign in and open `http://127.0.0.1:8000/video-learning`. Admin / Branch Manager / Faculty can create recorded courses and schedule Zoom sessions. Students see the courses and sessions available to their branch / enrolled course / batch.
