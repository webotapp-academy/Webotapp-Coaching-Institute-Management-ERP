<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController, DashboardController, EnquiryController, AdmissionController,
    StudentController, CourseController, BatchController, AttendanceController,
    FeeController, ExamController, LmsController, CertificateController,
    ExpenseController, ReportController, SettingsController, VideoLearningController
};

// Public Routes
Route::post('/webhooks/livekit', [VideoLearningController::class, 'webhook'])->name('webhooks.livekit');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/quick-login/{type}', [AuthController::class, 'quickLogin'])->name('quick-login');
Route::get('/verify-certificate/{code}', [CertificateController::class, 'verify'])->name('certificates.verify');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // CRM & Enquiries
    Route::resource('enquiries', EnquiryController::class);
    Route::post('/enquiries/{enquiry}/followup', [EnquiryController::class, 'addFollowup'])->name('enquiries.followup');

    // Admissions
    Route::resource('admissions', AdmissionController::class);

    // Students
    Route::resource('students', StudentController::class);

    // Courses & Syllabus
    Route::resource('courses', CourseController::class);
    Route::post('/courses/{course}/subjects', [CourseController::class, 'addSubject'])->name('courses.subjects.store');

    // Batches
    Route::resource('batches', BatchController::class);
    Route::post('/batches/{batch}/enroll', [BatchController::class, 'enrollStudent'])->name('batches.enroll');

    // Attendance
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    // Fees & Payments
    Route::get('/fees', [FeeController::class, 'index'])->name('fees.index');
    Route::get('/fees/payments', [FeeController::class, 'payments'])->name('fees.payments');
    Route::get('/fees/collect', [FeeController::class, 'collect'])->name('fees.collect');
    Route::post('/fees/pay', [FeeController::class, 'storePayment'])->name('fees.pay');
    Route::get('/fees/receipt/{payment}', [FeeController::class, 'receipt'])->name('fees.receipt');

    // Examinations
    Route::resource('exams', ExamController::class);
    Route::post('/exams/{exam}/marks', [ExamController::class, 'saveMarks'])->name('exams.marks.store');
    Route::get('/exams/{exam}/report-card/{student}', [ExamController::class, 'reportCard'])->name('exams.report-card');

    // LMS & Digital Learning
    Route::get('/lms/assignments', [LmsController::class, 'assignments'])->name('lms.assignments');
    Route::post('/lms/assignments', [LmsController::class, 'storeAssignment'])->name('lms.assignments.store');
    Route::get('/lms/materials', [LmsController::class, 'materials'])->name('lms.materials');
    Route::post('/lms/materials', [LmsController::class, 'storeMaterial'])->name('lms.materials.store');

    // Video Learning, Recorded Lessons & Live Classroom
    Route::get('/video-learning', [VideoLearningController::class, 'index'])->name('video-learning.index');
    Route::post('/video-learning/courses', [VideoLearningController::class, 'storeCourse'])->name('video-learning.courses.store');
    Route::post('/video-learning/courses/{videoCourse}/lessons', [VideoLearningController::class, 'storeLesson'])->name('video-learning.lessons.store');
    Route::get('/video-learning/lessons/{lesson}', [VideoLearningController::class, 'showLesson'])->name('video-learning.lessons.show');
    Route::post('/video-learning/lessons/{lesson}/progress', [VideoLearningController::class, 'saveProgress'])->name('video-learning.progress');
    Route::post('/video-learning/live-classes', [VideoLearningController::class, 'storeLiveClass'])->name('video-learning.live-classes.store');
    Route::post('/video-learning/live-classes/{liveClass}/start', [VideoLearningController::class, 'start'])->name('video-learning.live-classes.start');
    Route::post('/video-learning/live-classes/{liveClass}/join', [VideoLearningController::class, 'join'])->name('video-learning.live-classes.join');
    Route::get('/video-learning/recordings/{recording}/play', [VideoLearningController::class, 'playRecording'])->name('video-learning.recordings.play');

    // Certificates
    Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
    Route::post('/certificates', [CertificateController::class, 'store'])->name('certificates.store');
    Route::get('/certificates/{certificate}', [CertificateController::class, 'show'])->name('certificates.show');

    // Expenses
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');

    // Reports & Analytics
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Settings & Master Management
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/branches', [SettingsController::class, 'storeBranch'])->name('settings.branches.store');
    Route::post('/settings/users', [SettingsController::class, 'storeUser'])->name('settings.users.store');
    Route::post('/settings/general', [SettingsController::class, 'updateSettings'])->name('settings.general.update');
});
