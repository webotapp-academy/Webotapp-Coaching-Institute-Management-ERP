<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} — {{ $appInstituteName ?? 'Webotapp Coaching ERP' }}</title>
    <!-- Tailwind CSS CDN for instant styling resilience and fast preview -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        /* Global High-Density Compact ERP */
        html { font-size: 13.5px; }
    </style>
</head>
<body class="h-full text-slate-800 antialiased flex flex-col font-sans" x-data="{ sidebarOpen: false }">

    <div class="flex h-screen overflow-hidden bg-slate-100">
        <!-- SIDEBAR -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
               class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-300 flex flex-col transition-transform duration-300 ease-in-out md:static md:translate-x-0 shadow-xl">
            
            <!-- Brand Logo -->
            <div class="flex items-center justify-between px-3.5 py-3 border-b border-slate-800 bg-slate-950/50">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-brand-600 to-indigo-500 flex items-center justify-center text-white shadow-md shadow-brand-500/30 shrink-0">
                        <i class="fa-solid fa-graduation-cap text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="font-bold text-white text-sm tracking-wide truncate">
                            {{ $appInstituteName ?? 'Webotapp Academy' }}
                        </div>
                        <div class="text-[10px] text-slate-400 truncate">{{ $systemSettings['institute_tagline'] ?? $appInstitute?->tagline ?? 'Coaching Institute Platform' }}</div>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto px-3 py-3 space-y-4 text-xs font-medium">
                @php
                    $u = auth()->user();
                    $isAdmin = $u && ($u->isSuperAdmin() || $u->isBranchManager());
                    $isCounsellor = $u && $u->isCounsellor();
                    $isAccountant = $u && $u->isAccountant();
                    $isFaculty = $u && $u->isFaculty();
                    $isStudent = $u && $u->isStudent();
                @endphp

                <!-- Group 1: Overview -->
                <div>
                    <div class="px-2.5 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        @if($isStudent)
                            MY PORTAL
                        @elseif($isFaculty)
                            FACULTY CONSOLE
                        @elseif($isAccountant)
                            ACCOUNTS CONSOLE
                        @elseif($isCounsellor)
                            CRM & LEADS CONSOLE
                        @else
                            CORE DASHBOARD
                        @endif
                    </div>
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('dashboard') }}" 
                               class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-brand-600 text-white font-semibold shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-gauge-high w-4 text-center"></i>
                                <span>
                                    @if($isStudent)
                                        Student Dashboard
                                    @elseif($isFaculty)
                                        Teaching Dashboard
                                    @elseif($isAccountant)
                                        Accounts Dashboard
                                    @elseif($isCounsellor)
                                        Counsellor Dashboard
                                    @else
                                        Executive Dashboard
                                    @endif
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div>
                    <div class="px-2.5 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">VIDEO LEARNING</div>
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('video-learning.index') }}"
                               class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('video-learning*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-circle-play w-4 text-center"></i>
                                <span>Recorded & Live Classes</span>
                            </a>
                        </li>
                    </ul>
                </div>

                @if($isStudent)
                    <!-- STUDENT EXCLUSIVE MENU -->
                    <div>
                        <div class="px-2.5 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">MY ACADEMICS</div>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('batches.index') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('batches*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-users-rectangle w-4 text-center"></i>
                                    <span>My Classes & Batches</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('attendance.index') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('attendance*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-clipboard-user w-4 text-center"></i>
                                    <span>My Attendance Record</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <div class="px-2.5 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">FINANCES & RECEIPTS</div>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('fees.index') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('fees.index') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-file-invoice-dollar w-4 text-center"></i>
                                    <span>My Fee Installments</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('fees.payments') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('fees.payments') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-receipt w-4 text-center"></i>
                                    <span>My Payment Receipts</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <div class="px-2.5 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">LEARNING & EXAMS</div>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('exams.index') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('exams*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-pen-to-square w-4 text-center"></i>
                                    <span>Exams & Scorecards</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('lms.assignments') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('lms.assignments*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-tasks w-4 text-center"></i>
                                    <span>My Homework Assignments</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('lms.materials') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('lms.materials*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-folder-open w-4 text-center"></i>
                                    <span>Digital Study Handouts</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('certificates.index') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('certificates*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-certificate w-4 text-center"></i>
                                    <span>My Issued Certificates</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                @else

                    @if($isAdmin || $isCounsellor)
                    <!-- Student Acquisition -->
                    <div>
                        <div class="px-2.5 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">STUDENT ACQUISITION</div>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('enquiries.index') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('enquiries*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-funnel-dollar w-4 text-center"></i>
                                    <span>Enquiries & Leads CRM</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admissions.index') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('admissions*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-id-card-clip w-4 text-center"></i>
                                    <span>Admissions & Enroll</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('students.index') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('students*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-user-graduate w-4 text-center"></i>
                                    <span>Student Directory</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @elseif($isAccountant)
                    <div>
                        <div class="px-2.5 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">STUDENT DIRECTORY</div>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('admissions.index') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('admissions*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-id-card-clip w-4 text-center"></i>
                                    <span>Admissions Register</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('students.index') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('students*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-user-graduate w-4 text-center"></i>
                                    <span>Student Directory</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif

                    @if($isAdmin || $isFaculty || $isCounsellor)
                    <!-- Academics -->
                    <div>
                        <div class="px-2.5 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">ACADEMICS & BATCHES</div>
                        <ul class="space-y-1">
                            @if(!$isFaculty)
                            <li>
                                <a href="{{ route('courses.index') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('courses*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-book-open w-4 text-center"></i>
                                    <span>Courses & Syllabus</span>
                                </a>
                            </li>
                            @endif
                            <li>
                                <a href="{{ route('batches.index') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('batches*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-users-rectangle w-4 text-center"></i>
                                    <span>{{ $isFaculty ? 'My Cohorts & Timetable' : 'Batches & Timetable' }}</span>
                                </a>
                            </li>
                            @if(!$isCounsellor)
                            <li>
                                <a href="{{ route('attendance.index') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('attendance*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-clipboard-user w-4 text-center"></i>
                                    <span>Daily Attendance Register</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                    @endif

                    @if($isAdmin || $isAccountant)
                    <!-- Finance -->
                    <div>
                        <div class="px-2.5 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">FINANCES & CASHIER</div>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('fees.index') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('fees.index') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-file-invoice-dollar w-4 text-center"></i>
                                    <span>Fee Dues & Installments</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('fees.collect') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('fees.collect') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-cash-register w-4 text-center"></i>
                                    <span>Cashier Collection Desk</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('fees.payments') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('fees.payments') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-receipt w-4 text-center"></i>
                                    <span>Fee Payment Receipts</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('expenses.index') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('expenses*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-money-bill-transfer w-4 text-center"></i>
                                    <span>Operating Expenses</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif

                    @if($isAdmin || $isFaculty)
                    <!-- Learning & Exams -->
                    <div>
                        <div class="px-2.5 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">EVALUATION & LMS</div>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('exams.index') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('exams*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-pen-to-square w-4 text-center"></i>
                                    <span>Examinations & Results</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('lms.assignments') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('lms.assignments*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-tasks w-4 text-center"></i>
                                    <span>Assignments & Homework</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('lms.materials') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('lms.materials*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-folder-open w-4 text-center"></i>
                                    <span>Study Materials & LMS</span>
                                </a>
                            </li>
                            @if($isAdmin)
                            <li>
                                <a href="{{ route('certificates.index') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('certificates*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-certificate w-4 text-center"></i>
                                    <span>Certificates & Verification</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                    @endif

                    @if($isAdmin || $isAccountant)
                    <!-- Management & Reports -->
                    <div>
                        <div class="px-2.5 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">MANAGEMENT & ANALYTICS</div>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('reports.index') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('reports*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-chart-line w-4 text-center"></i>
                                    <span>Reports & Business Analytics</span>
                                </a>
                            </li>
                            @if($isAdmin)
                            <li>
                                <a href="{{ route('settings.index') }}" 
                                   class="flex items-center gap-3 px-2.5 py-1.5 rounded-lg transition {{ request()->routeIs('settings*') ? 'bg-brand-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-sliders w-4 text-center"></i>
                                    <span>Branches & User Settings</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                    @endif

                @endif
            </nav>

            <!-- Bottom User Profile Card -->
            <div class="p-2.5 border-t border-slate-800 bg-slate-950/70 flex items-center justify-between">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-full bg-brand-700 text-white font-bold flex items-center justify-center shrink-0 border border-brand-500/30 text-xs">
                        {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                    </div>
                    <div class="truncate">
                        <div class="text-xs font-semibold text-white truncate">{{ auth()->user()->name ?? 'Administrator' }}</div>
                        <div class="text-[10px] text-brand-300 capitalize">{{ str_replace('_', ' ', auth()->user()->user_type ?? 'Staff') }}</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Logout" class="p-1.5 text-slate-400 hover:text-rose-400 transition text-xs">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            <!-- TOP NAVBAR -->
            <header class="bg-white border-b border-slate-200 sticky top-0 z-40 px-4 sm:px-5 py-2 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <button @click="sidebarOpen = true" class="md:hidden p-1.5 text-slate-600 hover:text-slate-900 focus:outline-none">
                        <i class="fa-solid fa-bars text-base"></i>
                    </button>

                    <!-- Institute Brand Pill -->
                    <div class="flex items-center gap-1.5 bg-slate-100 px-2.5 py-1 rounded-full text-[11px] font-medium text-slate-700">
                        <i class="fa-solid fa-building-columns text-brand-600"></i>
                        <span class="font-bold text-slate-900 truncate max-w-[180px]">{{ $appInstituteName ?? 'Webotapp Academy' }}</span>
                    </div>

                    <!-- Academic Session Badge -->
                    <span class="hidden sm:inline-flex items-center gap-1 bg-brand-50 border border-brand-200 text-brand-700 text-[11px] px-2 py-0.5 rounded-full font-medium">
                        <i class="fa-solid fa-calendar-days text-[10px]"></i>
                        {{ $systemSettings['academic_session_current'] ?? 'AY 2026-2027' }}
                    </span>
                </div>

                <!-- Top Right Actions -->
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <!-- Quick Demo Role Switcher (Pills) -->
                    <div class="hidden lg:flex items-center gap-1 bg-slate-100 p-0.5 rounded-lg text-[10px]">
                        <span class="text-slate-400 px-1 font-bold uppercase text-[9px]">Role Demo:</span>
                        <a href="{{ route('quick-login', 'admin') }}" class="px-1.5 py-0.5 rounded {{ auth()->user()?->user_type === 'super_admin' ? 'bg-white shadow font-bold text-brand-700' : 'text-slate-600 hover:text-slate-900' }}">Admin</a>
                        <a href="{{ route('quick-login', 'counsellor') }}" class="px-1.5 py-0.5 rounded {{ auth()->user()?->user_type === 'counsellor' ? 'bg-white shadow font-bold text-brand-700' : 'text-slate-600 hover:text-slate-900' }}">Counsellor</a>
                        <a href="{{ route('quick-login', 'accountant') }}" class="px-1.5 py-0.5 rounded {{ auth()->user()?->user_type === 'accountant' ? 'bg-white shadow font-bold text-brand-700' : 'text-slate-600 hover:text-slate-900' }}">Accounts</a>
                        <a href="{{ route('quick-login', 'faculty') }}" class="px-1.5 py-0.5 rounded {{ auth()->user()?->user_type === 'faculty' ? 'bg-white shadow font-bold text-brand-700' : 'text-slate-600 hover:text-slate-900' }}">Faculty</a>
                        <a href="{{ route('quick-login', 'student') }}" class="px-1.5 py-0.5 rounded {{ auth()->user()?->user_type === 'student' ? 'bg-white shadow font-bold text-brand-700' : 'text-slate-600 hover:text-slate-900' }}">Student</a>
                    </div>

                    @if($isAdmin || $isCounsellor)
                        <a href="{{ route('enquiries.create') }}" class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-700 px-2.5 py-1 rounded-lg text-xs font-semibold transition">
                            <i class="fa-solid fa-user-plus text-slate-500"></i>
                            <span class="hidden sm:inline">+ Lead</span>
                        </a>

                        <a href="{{ route('admissions.create') }}" class="inline-flex items-center gap-1 bg-brand-600 hover:bg-brand-700 text-white px-2.5 py-1 rounded-lg text-xs font-semibold shadow-xs shadow-brand-600/30 transition">
                            <i class="fa-solid fa-graduation-cap"></i>
                            <span>+ Admission</span>
                        </a>
                    @elseif($isAccountant)
                        <a href="{{ route('fees.collect') }}" class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 py-1 rounded-lg text-xs font-semibold shadow-xs transition">
                            <i class="fa-solid fa-cash-register"></i>
                            <span>+ Collect Fee</span>
                        </a>
                        <a href="{{ route('expenses.index') }}" class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-700 px-2.5 py-1 rounded-lg text-xs font-semibold transition">
                            <i class="fa-solid fa-receipt text-slate-500"></i>
                            <span class="hidden sm:inline">Expenses</span>
                        </a>
                    @elseif($isFaculty)
                        <a href="{{ route('attendance.index') }}" class="inline-flex items-center gap-1 bg-brand-600 hover:bg-brand-700 text-white px-2.5 py-1 rounded-lg text-xs font-semibold shadow-xs transition">
                            <i class="fa-solid fa-clipboard-user"></i>
                            <span>Mark Attendance</span>
                        </a>
                    @elseif($isStudent)
                        <a href="{{ route('fees.index') }}" class="inline-flex items-center gap-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 px-2.5 py-1 rounded-lg text-xs font-semibold transition">
                            <i class="fa-solid fa-wallet"></i>
                            <span>Fee Ledger</span>
                        </a>
                        <a href="{{ route('exams.index') }}" class="inline-flex items-center gap-1 bg-brand-600 hover:bg-brand-700 text-white px-2.5 py-1 rounded-lg text-xs font-semibold transition">
                            <i class="fa-solid fa-laptop-code"></i>
                            <span>Online Tests</span>
                        </a>
                    @endif
                </div>
            </header>

            <!-- FLASH ALERTS -->
            <div class="px-4 sm:px-5 pt-3">
                @if(session('success'))
                    <div class="mb-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-3 py-2 rounded-lg flex items-center justify-between text-xs font-medium shadow-xs" x-data="{ show: true }" x-show="show">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-check text-emerald-600"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="text-emerald-500 hover:text-emerald-800"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="mb-3 bg-sky-50 border border-sky-200 text-sky-800 px-3 py-2 rounded-lg flex items-center justify-between text-xs font-medium shadow-xs" x-data="{ show: true }" x-show="show">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-info text-sky-600"></i>
                            <span>{{ session('info') }}</span>
                        </div>
                        <button @click="show = false" class="text-sky-500 hover:text-sky-800"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-3 bg-rose-50 border border-rose-200 text-rose-800 px-3 py-2 rounded-lg text-xs shadow-xs">
                        <div class="font-bold flex items-center gap-2 mb-0.5">
                            <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                            <span>Please resolve the following errors:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 text-rose-700 pl-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- PAGE CONTENT -->
            <main class="flex-1 px-4 sm:px-5 py-3">
                @yield('content')
            </main>

            <!-- FOOTER -->
            <footer class="bg-white border-t border-slate-200 px-4 py-2 text-center text-[11px] text-slate-400">
                {{ $appInstituteName ?? 'Webotapp Academy' }} ERP &copy; {{ date('Y') }} &bull; Enterprise Coaching & Academy Management System
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>

