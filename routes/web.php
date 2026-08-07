<?php

use App\Http\Controllers\AccountingController;
use App\Http\Controllers\AgencyDashboardController;
use App\Http\Controllers\AgencyRegistrationController;
use App\Http\Controllers\ApplicantAuthController;
use App\Http\Controllers\ApplicantOtpAuthController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\ApplicantDocumentController;
use App\Http\Controllers\ApplicantJobController;
use App\Http\Controllers\ApplicantPortalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\CommissionPaymentController;
use App\Http\Controllers\CustomFieldDefinitionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployerAuthController;
use App\Http\Controllers\EmployerController;
use App\Http\Controllers\EmployerDashboardController;
use App\Http\Controllers\EmployerBillingController;
use App\Http\Controllers\JobPositionController;
use App\Http\Controllers\MarketingAgencyController;
use App\Http\Controllers\MarketingAgentController;
use App\Http\Controllers\AgencyController;
use App\Http\Controllers\OfficialReceiptController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PersonalInformationBasicController;
use App\Http\Controllers\PersonalInformationRequirementsController;
use App\Http\Controllers\PortalDocumentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Api\CaseController;
use App\Http\Controllers\SubTableController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ReportsIndexController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\ReportTemplateController;
use App\Http\Controllers\ReceivablesController;
use App\Http\Controllers\ReceivableController;
use App\Http\Controllers\AgentReportController;
use App\Http\Controllers\ExpenseRequestController;
use Illuminate\Support\Facades\Route;

// === Applicant Portal ===
Route::prefix('portal')->name('portal.')->group(function () {
    // Guest (not logged in as applicant)
    Route::get('/register', [ApplicantAuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [ApplicantAuthController::class, 'register'])->name('register.post');
    Route::get('/login', [ApplicantAuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [ApplicantAuthController::class, 'login'])->name('login.post');

    // OTP Authentication
    Route::get('/login/otp', [ApplicantOtpAuthController::class, 'showOtpLoginForm'])->name('login.otp');
    Route::post('/login/otp/send', [ApplicantOtpAuthController::class, 'sendOtp'])->name('login.otp.send');
    Route::post('/login/otp/verify', [ApplicantOtpAuthController::class, 'verifyOtp'])->name('login.otp.verify');

    // Authenticated as applicant
    Route::middleware('auth:applicant')->group(function () {
        Route::post('/logout', [ApplicantAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [ApplicantPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [ApplicantPortalController::class, 'profile'])->name('profile');
        Route::get('/jobs', [ApplicantJobController::class, 'index'])->name('jobs.index');
        Route::get('/jobs/{job}', [ApplicantJobController::class, 'show'])->name('jobs.show');
        Route::post('/documents/upload', [PortalDocumentController::class, 'upload'])->name('documents.upload');
        Route::get('/documents/{document}/download', [PortalDocumentController::class, 'download'])->name('documents.download');
    });
});

// === Employer Portal ===
Route::prefix('employer')->name('employer.')->group(function () {
    // Guest
    Route::middleware('guest')->group(function () {
        Route::get('/login', [EmployerAuthController::class, 'loginForm'])->name('login');
        Route::post('/login', [EmployerAuthController::class, 'login'])->name('login.post');
    });

    // Authenticated
    Route::middleware(['auth:web', 'employer'])->group(function () {
        Route::post('/logout', [EmployerAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [EmployerDashboardController::class, 'index'])->name('dashboard');

        // Billing
        Route::get('/billing', [EmployerBillingController::class, 'index'])->name('billing.index');
        Route::get('/billing/soa', [EmployerBillingController::class, 'soa'])->name('billing.soa');
        Route::get('/billing/applicant/{applicant}', [EmployerBillingController::class, 'applicant'])->name('billing.applicant');

        // Employer Job Positions
        Route::get('/job-positions', [JobPositionController::class, 'employerIndex'])->name('job-positions.index');
        Route::get('/job-positions/create', [JobPositionController::class, 'employerCreate'])->name('job-positions.create');
        Route::post('/job-positions', [JobPositionController::class, 'employerStore'])->name('job-positions.store');
        Route::get('/job-positions/{jobPosition}', [JobPositionController::class, 'employerShow'])->name('job-positions.show');
        Route::get('/job-positions/{jobPosition}/edit', [JobPositionController::class, 'employerEdit'])->name('job-positions.edit');
        Route::put('/job-positions/{jobPosition}', [JobPositionController::class, 'employerUpdate'])->name('job-positions.update');
        Route::delete('/job-positions/{jobPosition}', [JobPositionController::class, 'employerDestroy'])->name('job-positions.destroy');

        // Employer Applicants
        Route::get('/applicants', [EmployerDashboardController::class, 'applicants'])->name('applicants');
    });
});

// === FRA (Foreign Recruitment Agency) Portal ===
Route::prefix('fra')->name('fra.')->group(function () {
    // Public (no auth required)
    Route::get('/language/{locale}', function ($locale) {
        if (array_key_exists($locale, config('app.supported_languages', []))) {
            session(['locale' => $locale]);
            app()->setLocale($locale);
        }
        return redirect()->back();
    })->name('language.switch');

    // Guest
    Route::middleware('guest')->group(function () {
        Route::get('/login', [\App\Http\Controllers\FraAuthController::class, 'loginForm'])->name('login');
        Route::post('/login', [\App\Http\Controllers\FraAuthController::class, 'login'])->name('login.post');
        Route::get('/password/reset', [\App\Http\Controllers\PasswordResetController::class, 'fraRequestForm'])->name('password.request');
        Route::post('/password/email', [\App\Http\Controllers\PasswordResetController::class, 'fraSendResetLink'])->name('password.email');
        Route::get('/password/reset/{token}', [\App\Http\Controllers\PasswordResetController::class, 'fraResetForm'])->name('password.reset');
        Route::post('/password/reset', [\App\Http\Controllers\PasswordResetController::class, 'fraReset'])->name('password.update');
    });

    // Authenticated
    Route::middleware(['fra'])->group(function () {
        Route::post('/logout', [\App\Http\Controllers\FraAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [\App\Http\Controllers\FraDashboardController::class, 'index'])->name('dashboard');
        Route::get('/line-up/export', [\App\Http\Controllers\FraDashboardController::class, 'lineupExport'])->name('lineup.export');
        Route::get('/line-up', [\App\Http\Controllers\FraDashboardController::class, 'lineup'])->name('lineup');
        Route::post('/line-up/{applicant}/select', [\App\Http\Controllers\FraDashboardController::class, 'selectApplicant'])->name('lineup.select');
        Route::get('/line-up/{applicant}', [\App\Http\Controllers\FraDashboardController::class, 'viewApplicant'])->name('lineup.view');
        Route::get('/selected', [\App\Http\Controllers\FraDashboardController::class, 'selected'])->name('selected');
        Route::post('/selected/bulk-remove', [\App\Http\Controllers\FraDashboardController::class, 'bulkRemoveSelected'])->name('selected.bulk-remove');
        Route::get('/on-process/export', [\App\Http\Controllers\FraDashboardController::class, 'onprocessExport'])->name('onprocess.export');
        Route::get('/on-process', [\App\Http\Controllers\FraDashboardController::class, 'onprocess'])->name('onprocess');
        Route::get('/cancelled', [\App\Http\Controllers\FraDashboardController::class, 'cancelled'])->name('cancelled');
        Route::get('/account', [\App\Http\Controllers\FraDashboardController::class, 'account'])->name('account');
        Route::post('/account/language', [\App\Http\Controllers\FraDashboardController::class, 'updateLanguage'])->name('account.language.update');
    });

    // Dashboard if logged in, login if not
    Route::get('/', function () {
        if (auth()->check() && auth()->user()->user_type === 'employer') {
            return redirect()->route('fra.dashboard');
        }
        return redirect()->route('fra.login');
    });
});

// === Sponsor Portal ===
Route::prefix('sponsor')->name('sponsor.')->group(function () {
    // Public (no auth required)
    Route::get('/language/{locale}', function ($locale) {
        if (array_key_exists($locale, config('app.supported_languages', []))) {
            session(['locale' => $locale]);
            app()->setLocale($locale);
        }
        return redirect()->back();
    })->name('language.switch');

    // Guest (no auth required) — custom middleware to avoid redirecting to /agency
    Route::middleware('sponsor.guest')->group(function () {
        Route::get('/register', [\App\Http\Controllers\SponsorAuthController::class, 'registerForm'])->name('register');
        Route::post('/register', [\App\Http\Controllers\SponsorAuthController::class, 'register'])->name('register.post');
        Route::get('/login', [\App\Http\Controllers\SponsorAuthController::class, 'loginForm'])->name('login');
        Route::post('/login', [\App\Http\Controllers\SponsorAuthController::class, 'login'])->name('login.post');
        Route::get('/forgot-password', [\App\Http\Controllers\SponsorAuthController::class, 'forgotPasswordForm'])->name('password.request');
        Route::post('/forgot-password', [\App\Http\Controllers\SponsorAuthController::class, 'sendResetLink'])->name('password.email');
        Route::get('/reset-password/{token}', [\App\Http\Controllers\SponsorAuthController::class, 'resetForm'])->name('password.reset');
        Route::post('/reset-password', [\App\Http\Controllers\SponsorAuthController::class, 'resetPassword'])->name('password.update');
    });

    // Authenticated
    Route::middleware('sponsor')->group(function () {
        Route::post('/logout', [\App\Http\Controllers\SponsorAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [\App\Http\Controllers\SponsorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/line-up/export', [\App\Http\Controllers\SponsorDashboardController::class, 'lineupExport'])->name('lineup.export');
        Route::post('/select', [\App\Http\Controllers\SponsorDashboardController::class, 'select'])->name('select');
        Route::post('/unselect', [\App\Http\Controllers\SponsorDashboardController::class, 'unselect'])->name('unselect');
        Route::get('/my-applicants', [\App\Http\Controllers\SponsorDashboardController::class, 'myApplicants'])->name('my-applicants');
        Route::post('/account/language', [\App\Http\Controllers\SponsorDashboardController::class, 'updateLanguage'])->name('account.language.update');
    });

    // Dashboard if logged in, login if not
    Route::get('/', function () {
        if (auth()->check() && auth()->user()->user_type === 'sponsor') {
            return redirect()->route('sponsor.dashboard');
        }
        return redirect()->route('sponsor.login');
    });
});

// === Public Landing Page (before auth redirect) ===
Route::get('/', function () {
    return view('welcome');
});

// === Agency Public Registration ===
Route::get('/agency/register', [AgencyRegistrationController::class, 'showRegistrationForm'])->name('agency.register');
Route::post('/agency/register', [AgencyRegistrationController::class, 'register'])->name('agency.register.post');
Route::get('/agency-register', [AgencyRegistrationController::class, 'showRegistrationForm'])->name('agency.register.alt');
Route::get('/agency/pending-approval', [AgencyRegistrationController::class, 'pendingApproval'])->name('agency.pending-approval');

// Agent routes (guest)
Route::prefix('agent')->name('agent.')->group(function () {
    Route::middleware('guest:agent')->group(function () {
        Route::get('/login', [\App\Http\Controllers\AgentAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [\App\Http\Controllers\AgentAuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware('agent')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\AgentAuthController::class, 'dashboard'])->name('dashboard');
        Route::post('/logout', [\App\Http\Controllers\AgentAuthController::class, 'logout'])->name('logout');
    });
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Agency login (non-subdomain testing)
    Route::get('/agency-login', [AuthController::class, 'agencyLoginForm'])->name('agency.login');
    Route::post('/agency-login', [AuthController::class, 'agencyLogin'])->name('agency.login.post');

    // Password Reset
    Route::get('/password/reset', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/password/email', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/password/reset/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('password.update');
});

// Authenticated routes
Route::middleware('auth:web')->group(function () {
    Route::match(['GET', 'POST'], '/logout', [AuthController::class, 'logout'])->name('logout');

    // Super admin dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    // Notification center (all authenticated users)
    Route::get("/notifications", [NotificationController::class, "index"])->name("notifications.index");
    Route::get("/notifications/unread-count", [NotificationController::class, "unreadCount"])->name("notifications.unread-count");
    Route::post("/notifications/{notification}/mark-as-read", [NotificationController::class, "markAsRead"])->name("notifications.mark-as-read");
    Route::post("/notifications/mark-all-as-read", [NotificationController::class, "markAllAsRead"])->name("notifications.mark-all-as-read");

    // AI Assistant — natural language to SQL query
    Route::post('/ai/assistant/query', [App\Http\Controllers\AiAssistantController::class, 'query'])
        ->name('ai.assistant.query')
        ->middleware('ai.rate');

    // AI Assistant — pre-built analytics templates
    Route::get('/ai/assistant/templates', [App\Http\Controllers\AiAssistantController::class, 'templates'])
        ->name('ai.assistant.templates');

    // AI Assistant — execute a pre-built template query
    Route::get('/ai/assistant/template/{template}', [App\Http\Controllers\AiAssistantController::class, 'executeTemplate'])
        ->name('ai.assistant.template');

    // AI Assistant — export query results as CSV
    Route::get('/ai/assistant/export', [App\Http\Controllers\AiAssistantController::class, 'export'])
        ->name('ai.assistant.export');

    // Agency dashboard
    Route::get('/agency/dashboard', [AgencyDashboardController::class, 'index'])->name('agency.dashboard');

    // User management (admin, super_admin only)
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::resource('users', UserController::class);

        // Role & permission assignment UI
        Route::get('/users/{user}/permissions', [UserController::class, 'permissions'])->name('users.permissions');
        Route::put('/users/{user}/permissions', [UserController::class, 'updatePermissions'])->name('users.permissions.update');

        // User activation / suspension
        Route::put('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
        Route::put('/users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
        Route::put('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    });

    // Agency management (admin, super_admin only)
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::get('/agencies', [AgencyController::class, 'index'])->name('agencies.index');
        Route::get('/agencies/create', [AgencyController::class, 'create'])->name('agencies.create');
        Route::post('/agencies', [AgencyController::class, 'store'])->name('agencies.store');
        Route::get('/agencies/{agency}', [AgencyController::class, 'show'])->name('agencies.show');
        Route::get('/agencies/{agency}/edit', [AgencyController::class, 'edit'])->name('agencies.edit');
        Route::put('/agencies/{agency}', [AgencyController::class, 'update'])->name('agencies.update');
        Route::put('/agencies/{agency}/deactivate', [AgencyController::class, 'deactivate'])->name('agencies.deactivate');
        Route::put('/agencies/{agency}/activate', [AgencyController::class, 'activate'])->name('agencies.activate');

        // Agency branding (logos, colors, favicon)
        Route::get('/agencies/{agency}/branding', App\Http\Controllers\AgencyBrandingController::class)->name('agencies.branding');
        Route::put('/agencies/{agency}/branding', [App\Http\Controllers\AgencyBrandingController::class, 'update'])->name('agencies.branding.update');

        // Agency-scoped user management (users belonging to the given agency)
        Route::resource('agencies.users', App\Http\Controllers\AgencyUserController::class)
            ->except(['show']);
    });

    // Applicant routes - recruiter and other agency roles
    Route::middleware('role:admin,super_admin,recruiter,staff,processor,coordinator,interviewer,manager,marketer,director')->group(function () {
        Route::get('applicants/export', [ApplicantController::class, 'export'])->name('applicants.export');
        Route::patch('applicants/{applicant}/status', [ApplicantController::class, 'updateStatus'])->name('applicants.status');
        Route::get('applicants/{applicant}/soa', [ApplicantController::class, 'soa'])->name('applicants.soa');
        Route::resource('applicants', ApplicantController::class);

        // Applicant document uploads (before wildcard sub-store routes)
        Route::prefix('applicants/{applicant}')->name('applicants.')->group(function () {
            Route::post('/documents', [ApplicantDocumentController::class, 'store'])->name('documents.store');
            Route::delete('/documents/{document}', [ApplicantDocumentController::class, 'destroy'])->name('documents.destroy');
        });

        // Applicant sub-table routes
        Route::prefix('applicants/{applicant}')->name('applicants.')->group(function () {
            // Basic Information tab "Save Update" (before wildcard sub-store routes)
            Route::patch('/personal-information/basic', [PersonalInformationBasicController::class, 'update'])->name('basic.update');

            Route::patch('/personal-information/requirements', [PersonalInformationRequirementsController::class, 'update'])->name('requirements.update');

            Route::post('/{type}', [SubTableController::class, 'store'])->name('sub.store');
            Route::put('/{type}/{id}', [SubTableController::class, 'update'])->name('sub.update');
            Route::delete('/{type}/{id}', [SubTableController::class, 'destroy'])->name('sub.destroy');
        });
    });

    // Employer routes (admin, super_admin only)
    Route::middleware('role:admin,super_admin,staff')->group(function () {
        Route::get('employers/{employer}/soa', [EmployerController::class, 'soa'])->name('employers.soa');
        Route::resource('employers', EmployerController::class);

        // Job Position routes (nested under employers)
        Route::resource('employers.job-positions', JobPositionController::class);
    });

    // Marketing Agency routes
    Route::resource('marketing-agencies', MarketingAgencyController::class);

    // Marketing Agent routes (nested under marketing agencies)
    Route::resource('marketing-agencies.marketing-agents', MarketingAgentController::class);

    // Billing routes (admin, super_admin, billing)
    Route::middleware('role:admin,super_admin,billing')->group(function () {
        Route::resource('bills', BillController::class);
        Route::resource('payments', PaymentController::class);
        Route::resource('official-receipts', OfficialReceiptController::class);
        Route::resource('commissions', CommissionController::class);

        // Commission payment routes (nested under commissions)
        Route::resource('commissions.commission-payments', CommissionPaymentController::class);
    });

    // Custom field management (admin, super_admin only)
    Route::middleware('role:admin,super_admin')->group(function () {
        // Agent management
    Route::resource('agents', \App\Http\Controllers\AgentController::class);

    Route::resource('custom-fields', CustomFieldDefinitionController::class);

        // Custom field definitions (alias route names for test compatibility)
        Route::name('custom-field-definitions.')->group(function () {
            Route::get('/custom-field-definitions', [CustomFieldDefinitionController::class, 'index'])->name('index');
            Route::post('/custom-field-definitions', [CustomFieldDefinitionController::class, 'store'])->name('store');
            Route::put('/custom-field-definitions/{custom_field}', [CustomFieldDefinitionController::class, 'update'])->name('update');
            Route::delete('/custom-field-definitions/{custom_field}', [CustomFieldDefinitionController::class, 'destroy'])->name('destroy');
        });

        // Reference CRUD modules (admin/super_admin)
        Route::resource('expenses', \App\Http\Controllers\ExpenseController::class)->except('show');
        Route::resource('branches', \App\Http\Controllers\BranchController::class);
        Route::resource('languages', \App\Http\Controllers\LanguageController::class);
        Route::resource('skills', \App\Http\Controllers\SkillController::class);
        Route::resource('countries', \App\Http\Controllers\CountryController::class);
        Route::resource('positions', \App\Http\Controllers\PositionController::class);
        Route::resource('status-codes', \App\Http\Controllers\StatusCodeController::class);
    });

    // Accounting routes
    Route::prefix('accounting')->name('accounting.')->group(function () {
        // Agency finance dashboard — restricted to admin/super_admin/billing
        Route::get('/', [AccountingController::class, 'dashboard'])->name('dashboard')
            ->middleware('role:admin,super_admin,billing');
        Route::get('/export', [AccountingController::class, 'export'])->name('export')
            ->middleware('role:admin,super_admin,billing');
        Route::get('/receivables', [ReceivablesController::class, 'receivables'])->name('receivables')
            ->middleware('role:admin,super_admin,billing');
        Route::get('/employer/{employer}', [AccountingController::class, 'employer'])->name('employer');
        Route::get('/worker/{applicant}', [AccountingController::class, 'worker'])->name('worker');
        Route::get('/marketing-agency/{marketingAgency}', [AccountingController::class, 'marketingAgency'])->name('marketing-agency');
        Route::get('/marketing-agent/{marketingAgent}', [AccountingController::class, 'marketingAgent'])->name('marketing-agent');
        Route::get('/recruitment-agent/{recruitmentAgent}', [AccountingController::class, 'recruitmentAgent'])->name('recruitment-agent');
    });

    // Receivable & Payments module — Tab 1: Receivable
    Route::prefix('receivable')->name('receivable.')->group(function () {
        Route::get('/', [ReceivableController::class, 'index'])->name('index')
            ->middleware('role:admin,super_admin,billing');
        Route::get('/create', [ReceivableController::class, 'create'])->name('create')
            ->middleware('role:admin,super_admin,billing');
        Route::post('/', [ReceivableController::class, 'store'])->name('store')
            ->middleware('role:admin,super_admin,billing');
        Route::get('/{receivable}', [ReceivableController::class, 'show'])->name('show')
            ->middleware('role:admin,super_admin,billing');
        Route::patch('/{receivable}/status', [ReceivableController::class, 'updateStatus'])->name('status')
            ->middleware('role:admin,super_admin,billing');
    });

    // Receivable & Payments module — Tab 2: Expenses & Payments
    Route::prefix('expense-request')->name('expense_request.')->group(function () {
        Route::get('/', [ExpenseRequestController::class, 'index'])->name('index')
            ->middleware('role:admin,super_admin,billing');
        Route::get('/create', [ExpenseRequestController::class, 'create'])->name('create')
            ->middleware('role:admin,super_admin,billing');
        Route::post('/', [ExpenseRequestController::class, 'store'])->name('store')
            ->middleware('role:admin,super_admin,billing');
        Route::get('/{expense_request}', [ExpenseRequestController::class, 'show'])->name('show')
            ->middleware('role:admin,super_admin,billing');
        Route::patch('/{expense_request}/status', [ExpenseRequestController::class, 'updateStatus'])->name('status')
            ->middleware('role:admin,super_admin,billing');
    });

    // Receivable & Payments module — Tab 3: Agents Report
    Route::prefix('agents-report')->name('agent_report.')->group(function () {
        Route::get('/', [AgentReportController::class, 'index'])->name('index')
            ->middleware('role:admin,super_admin,billing');
    });

    // Report Template CRUD
    Route::resource('report-templates', ReportTemplateController::class)->except(['show']);

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');

    // Accounts module — renamed from "Chart of Accounts", now lives inside Settings.
    // URI nested under /settings but resource route names stay accounts.* for compatibility.
    Route::prefix('settings')->middleware('role:admin,super_admin')->group(function () {
        Route::resource('accounts', \App\Http\Controllers\AccountController::class)->except('show');
    });

    Route::get('/settings/applicant-form-defaults', [SettingsController::class, 'applicantFormDefaults'])->name('settings.applicant-form-defaults');
    Route::post('/settings/applicant-form-defaults', [SettingsController::class, 'updateApplicantFormDefaults'])->name('settings.applicant-form-defaults.update');

    // Reports index
    Route::get('/reports', [ReportsIndexController::class, 'index'])->name('reports.index');
    Route::get('/reports/preview/{reportTemplate}', [ReportsIndexController::class, 'preview'])->name('reports.preview');
    Route::get('/reports/pdf/{reportTemplate}', [ReportsIndexController::class, 'downloadPdf'])->name('reports.pdf');
    Route::get('/reports/csv/{reportTemplate}', [ReportsIndexController::class, 'downloadCsv'])->name('reports.csv');

    // Transaction history
    Route::get('/transactions', [ReportController::class, 'transactions'])->name('transactions.index');

    // Report PDF routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/applicants', [ReportController::class, 'applicants'])->name('applicants');
        Route::get('/applicants/export', [ReportController::class, 'applicantsExport'])->name('applicants.export');
        Route::get('/agents', [ReportController::class, 'agents'])->name('agents');
        Route::get('/agents/export', [ReportController::class, 'agentsExport'])->name('agents.export');
        Route::get('/bill/{bill}', [ReportController::class, 'bill'])->name('bill');
        Route::get('/or/{or}', [ReportController::class, 'or'])->name('or');
        Route::get('/commission/{commission}', [ReportController::class, 'commission'])->name('commission');
        Route::get('/resume/{applicant}', [ReportController::class, 'resume'])->name('resume');
        Route::get('/statistics', [ReportController::class, 'statistics'])->name('statistics');
    });

    // Case Management API
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('cases/search', [CaseController::class, 'search'])->name('cases.search')->middleware('throttle:59,1');
        Route::get('cases/{case}', [CaseController::class, 'show'])->name('cases.show');
        Route::put('cases/{case}', [CaseController::class, 'update'])->name('cases.update');
        Route::delete('cases/{case}', [CaseController::class, 'destroy'])->name('cases.destroy');
        Route::get('cases', [CaseController::class, 'index'])->name('cases.index');
        Route::post('cases', [CaseController::class, 'store'])->name('cases.store')->middleware('throttle:29,1');
    });
});
