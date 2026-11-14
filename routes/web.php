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
use App\Http\Controllers\CompanyProfileController;
use App\Http\Controllers\CustomFieldDefinitionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployerAuthController;
use App\Http\Controllers\EmployerController;
use App\Http\Controllers\EmployerDashboardController;
use App\Http\Controllers\EmployerBillingController;
use App\Http\Controllers\JobPositionController;
use App\Http\Controllers\MarketingAgencyController;
use App\Http\Controllers\AgentController;
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
use App\Http\Controllers\GeneralLedgerController;
use Illuminate\Support\Facades\Route;

// === Health Check ===
Route::get('/health', function () {
    return response()->json([
        'status'    => 'healthy',
        'timestamp' => now()->toIso8601String(),
    ]);
});

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
Route::middleware(['auth:web', 'module.access'])->group(function () {
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
    Route::group([], function () {
        Route::resource('users', UserController::class);

        // User activation / suspension
        Route::put('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
        Route::put('/users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
        Route::put('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    });

    // Agency management (admin, super_admin only)
    Route::group([], function () {
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
    Route::group([], function () {
        Route::get('applicants/export', [ApplicantController::class, 'export'])->name('applicants.export');
        // WELFARE module — lives under BACKOUT, CANCELLED & REPAT (card 6af81f6a).
    // CSDI and FINAS only, enforced in WelfareController::agencyId().
    Route::prefix('welfare')->name('welfare.')->group(function () {
        Route::get('/', [\App\Http\Controllers\WelfareController::class, 'index'])->name('index');
        Route::get('/export', [\App\Http\Controllers\WelfareController::class, 'export'])->name('export');
        Route::get('/lookup-applicant', [\App\Http\Controllers\WelfareController::class, 'lookupApplicant'])->name('lookup');
        Route::get('/create', [\App\Http\Controllers\WelfareController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\WelfareController::class, 'store'])->name('store');
        Route::get('/{welfare}/edit', [\App\Http\Controllers\WelfareController::class, 'edit'])->name('edit');
        Route::put('/{welfare}', [\App\Http\Controllers\WelfareController::class, 'update'])->name('update');
        Route::delete('/{welfare}', [\App\Http\Controllers\WelfareController::class, 'destroy'])->name('destroy');
        Route::get('/{welfare}/document', [\App\Http\Controllers\WelfareController::class, 'document'])->name('document');
    });

    Route::get('applicants/withdrawn-repat/export', [ApplicantController::class, 'withdrawnExport'])->name('applicants.withdrawn.export');
        Route::get('applicants/withdrawn-repat', [ApplicantController::class, 'withdrawn'])->name('applicants.withdrawn');
        // Bulk CSV upload (must precede the resource route so 'bulk' is not captured as an applicant id)
        Route::get('applicants/bulk', [ApplicantController::class, 'bulkUpload'])->name('applicants.bulk');
        Route::get('applicants/bulk/template', [ApplicantController::class, 'bulkTemplate'])->name('applicants.bulk.template');
        Route::post('applicants/bulk/import', [ApplicantController::class, 'bulkImport'])->name('applicants.bulk.import');
        // Live duplicate check for the Add Applicant form (must precede the resource route)
        Route::post('applicants/check-duplicates', [ApplicantController::class, 'checkDuplicates'])->name('applicants.check_duplicates');
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
    Route::group([], function () {
        // Bulk CSV upload (must precede the resource route so 'bulk' is not captured as an employer id)
        Route::get('employers/export', [EmployerController::class, 'export'])->name('employers.export');
        Route::get('employers/bulk', [EmployerController::class, 'bulkUpload'])->name('employers.bulk');
        Route::get('employers/bulk/template', [EmployerController::class, 'bulkTemplate'])->name('employers.bulk.template');
        Route::post('employers/bulk/import', [EmployerController::class, 'bulkImport'])->name('employers.bulk.import');
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
    Route::group([], function () {
        Route::resource('bills', BillController::class);
        Route::resource('payments', PaymentController::class);
        Route::resource('official-receipts', OfficialReceiptController::class);
        Route::resource('commissions', CommissionController::class);

        // Commission payment routes (nested under commissions)
        Route::resource('commissions.commission-payments', CommissionPaymentController::class);
    });

    // Custom field management (admin, super_admin only)
    Route::group([], function () {
        // Agent management
        // Bulk CSV upload (must precede the resource route so 'bulk' is not captured as an agent id)
        Route::get('agents/bulk', [AgentController::class, 'bulkUpload'])->name('agents.bulk');
        Route::get('agents/bulk/template', [AgentController::class, 'bulkTemplate'])->name('agents.bulk.template');
        Route::post('agents/bulk/import', [AgentController::class, 'bulkImport'])->name('agents.bulk.import');
        // Gulf-only destructive action (CYD 2026-11-04): wipe every registered agent.
        Route::post('agents/delete-all', [AgentController::class, 'deleteAll'])->name('agents.delete_all');
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
        // NOTE: legacy expenses CRUD (ExpenseController) removed 2026-08-11 —
        // replaced by Tab 2 Expense Request module (expense_request.*). See
        // LegacyExpenseModuleDeprecationTest.
        Route::resource('branches', \App\Http\Controllers\BranchController::class);
        Route::resource('languages', \App\Http\Controllers\LanguageController::class);
        Route::resource('skills', \App\Http\Controllers\SkillController::class);
        Route::resource('countries', \App\Http\Controllers\CountryController::class);
        // Positions CSV: define fixed paths before the resource so
        // /positions/export and /positions/bulk are not captured as {position}.
        Route::get('positions/export', [\App\Http\Controllers\PositionController::class, 'export'])->name('positions.export');
        Route::get('positions/bulk', [\App\Http\Controllers\PositionController::class, 'bulkUpload'])->name('positions.bulk');
        Route::get('positions/bulk/template', [\App\Http\Controllers\PositionController::class, 'bulkTemplate'])->name('positions.bulk.template');
        Route::post('positions/bulk/import', [\App\Http\Controllers\PositionController::class, 'bulkImport'])->name('positions.bulk.import');

        Route::resource('positions', \App\Http\Controllers\PositionController::class);
        Route::resource('status-codes', \App\Http\Controllers\StatusCodeController::class);
    });

    // Accounting routes
    Route::prefix('accounting')->name('accounting.')->group(function () {
        // Agency finance dashboard — restricted to admin/super_admin/billing
        Route::get('/', [AccountingController::class, 'dashboard'])->name('dashboard')
            ;
        Route::get('/export', [AccountingController::class, 'export'])->name('export')
            ;
        Route::get('/receivables', [ReceivablesController::class, 'receivables'])->name('receivables')
            ;
        Route::get('/employer/{employer}', [AccountingController::class, 'employer'])->name('employer');
        Route::get('/worker/{applicant}', [AccountingController::class, 'worker'])->name('worker');
        Route::get('/marketing-agency/{marketingAgency}', [AccountingController::class, 'marketingAgency'])->name('marketing-agency');
        Route::get('/marketing-agent/{marketingAgent}', [AccountingController::class, 'marketingAgent'])->name('marketing-agent');
        Route::get('/recruitment-agent/{recruitmentAgent}', [AccountingController::class, 'recruitmentAgent'])->name('recruitment-agent');
    });

    // General Ledger (Mjolnir card, 2026-11-05) — placed below Agents Report.
    Route::prefix('general-ledger')->name('general_ledger.')->group(function () {
        Route::get('/', [GeneralLedgerController::class, 'index'])->name('index');
        Route::get('/export', [GeneralLedgerController::class, 'export'])->name('export');
        Route::get('/account/{account}', [GeneralLedgerController::class, 'review'])->name('review');
        Route::get('/account/{account}/export', [GeneralLedgerController::class, 'reviewExport'])->name('review.export');
    });

    // Receivable & Payments module — Tab 1: Receivable
    Route::prefix('receivable')->name('receivable.')->group(function () {
        Route::get('/', [ReceivableController::class, 'index'])->name('index')
            ;
        Route::get('/create', [ReceivableController::class, 'create'])->name('create')
            ;
        Route::post('/', [ReceivableController::class, 'store'])->name('store')
            ;
        // Bulk CSV upload (must precede the {receivable} show route so 'bulk' is not captured as an id).
        Route::get('/bulk', [ReceivableController::class, 'bulkUpload'])->name('bulk')
            ;
        Route::get('/bulk/template', [ReceivableController::class, 'bulkTemplate'])->name('bulk.template')
            ;
        Route::post('/bulk/import', [ReceivableController::class, 'bulkImport'])->name('bulk.import')
            ;
        Route::get('/{receivable}', [ReceivableController::class, 'show'])->name('show')
            ;
        // Admin + Accounting may edit a transaction (Cyd 2026-11-01)
        Route::get('/{receivable}/edit', [ReceivableController::class, 'edit'])->name('edit')
            ;
        Route::put('/{receivable}', [ReceivableController::class, 'update'])->name('update')
            ;
        Route::patch('/{receivable}/status', [ReceivableController::class, 'updateStatus'])->name('status')
            ;
        // Batch status change from the index checkboxes (Toybits 2026-08-31).
        Route::post('/bulk-status', [ReceivableController::class, 'bulkUpdateStatus'])->name('bulk_status')
            ;
        Route::delete('/{receivable}', [ReceivableController::class, 'destroy'])->name('destroy')
            ;
    });

    // Receivable & Payments module — Tab 2: Expenses & Payments
    Route::prefix('expense-request')->name('expense_request.')->group(function () {
        Route::get('/', [ExpenseRequestController::class, 'index'])->name('index')
            ;
        Route::get('/create', [ExpenseRequestController::class, 'create'])->name('create')
            ;
        Route::post('/', [ExpenseRequestController::class, 'store'])->name('store')
            ;
        // Bulk CSV upload (must precede the {expense_request} show route so 'bulk' is not captured as an id).
        Route::get('/bulk', [ExpenseRequestController::class, 'bulkUpload'])->name('bulk')
            ;
        Route::get('/bulk/template', [ExpenseRequestController::class, 'bulkTemplate'])->name('bulk.template')
            ;
        Route::post('/bulk/import', [ExpenseRequestController::class, 'bulkImport'])->name('bulk.import')
            ;
        Route::post('/check-duplicates', [ExpenseRequestController::class, 'checkDuplicates'])->name('check_duplicates')
            ;
        // Batch status change from the index checkboxes (Toybits 2026-08-31).
        Route::post('/bulk-status', [ExpenseRequestController::class, 'bulkUpdateStatus'])->name('bulk_status')
            ;
        Route::get('/{expense_request}', [ExpenseRequestController::class, 'show'])->name('show')
            ;
        // Admin + Accounting may edit a transaction (Cyd 2026-11-01)
        Route::get('/{expense_request}/edit', [ExpenseRequestController::class, 'edit'])->name('edit')
            ;
        Route::put('/{expense_request}', [ExpenseRequestController::class, 'update'])->name('update')
            ;
        Route::patch('/{expense_request}/status', [ExpenseRequestController::class, 'updateStatus'])->name('status')
            ;
        // Admin-only delete (Cyd 2026-10-24)
        Route::delete('/{expense_request}', [ExpenseRequestController::class, 'destroy'])->name('destroy')
            ;
    });

    // Receivable & Payments module — Tab 3: Agents Report
    // NOTE: static/creation routes MUST stay above the /{agent} wildcard or
    // Laravel will bind them to the Agent model (route-model binding 404s).
    Route::prefix('agents-report')->name('agent_report.')->group(function () {
        Route::get('/', [AgentReportController::class, 'index'])->name('index')
            ;

        // Tab 3: Deductions (bulk routes must precede the {agent_deduction} wildcard)
        Route::get('/deductions/create', [AgentReportController::class, 'deductionCreate'])->name('deduction.create')
            ;
        Route::get('/deductions/bulk', [AgentReportController::class, 'deductionBulkUpload'])->name('deduction.bulk')
            ;
        Route::get('/deductions/bulk/template', [AgentReportController::class, 'deductionBulkTemplate'])->name('deduction.bulk.template')
            ;
        Route::post('/deductions/bulk/import', [AgentReportController::class, 'deductionBulkImport'])->name('deduction.bulk.import')
            ;
        Route::post('/deductions', [AgentReportController::class, 'deductionStore'])->name('deduction.store')
            ;
        Route::get('/deductions/{agent_deduction}', [AgentReportController::class, 'deductionShow'])->name('deduction.show')
            ;
        // Edit a deduction transaction — Admin and Accounting only (card 6af170f5)
        Route::get('/deductions/{agent_deduction}/edit', [AgentReportController::class, 'deductionEdit'])->name('deduction.edit')
            ;
        Route::put('/deductions/{agent_deduction}', [AgentReportController::class, 'deductionUpdate'])->name('deduction.update')
            ;
        // Admin-only delete from the Deductions tab (Cyd 2026-11-01)
        Route::delete('/deductions/{agent_deduction}', [AgentReportController::class, 'deductionDestroy'])->name('deduction.destroy')
            ;

        // Tab 2: Payments — bulk upload (Cyd 2026-11-01)
        Route::get('/payments/bulk', [AgentReportController::class, 'paymentBulkUpload'])->name('payment.bulk')
            ;
        Route::get('/payments/bulk/template', [AgentReportController::class, 'paymentBulkTemplate'])->name('payment.bulk.template')
            ;
        Route::post('/payments/bulk/import', [AgentReportController::class, 'paymentBulkImport'])->name('payment.bulk.import')
            ;
        // Admin-only delete from the Payments tab (Cyd 2026-11-01)
        Route::delete('/payments/{expense_request_item}', [AgentReportController::class, 'paymentDestroy'])->name('payment.destroy')
            ;

        // Tab 4: Starting Balance (bulk routes must precede the {starting_balance} wildcard)
        Route::get('/starting-balances/bulk', [AgentReportController::class, 'startingBalanceBulkUpload'])->name('starting_balance.bulk')
            ;
        Route::get('/starting-balances/bulk/template', [AgentReportController::class, 'startingBalanceBulkTemplate'])->name('starting_balance.bulk.template')
            ;
        Route::post('/starting-balances/bulk/import', [AgentReportController::class, 'startingBalanceBulkImport'])->name('starting_balance.bulk.import')
            ;
        Route::get('/starting-balances/create', [AgentReportController::class, 'startingBalanceCreate'])->name('starting_balance.create')
            ;
        Route::post('/starting-balances', [AgentReportController::class, 'startingBalanceStore'])->name('starting_balance.store')
            ;
        Route::get('/starting-balances/{starting_balance}', [AgentReportController::class, 'startingBalanceShow'])->name('starting_balance.show')
            ;

        // Tab 5: Report — print + CSV export
        Route::get('/print', [AgentReportController::class, 'print'])->name('print')
            ;
        Route::get('/export', [AgentReportController::class, 'export'])->name('export')
            ;

        // Tab 5: Agent Ledger — single-agent detail view (clickable Name) + print/PDF
        Route::get('/ledger/{agent}', [AgentReportController::class, 'show'])->name('show')
            ;
        Route::get('/ledger/{agent}/print', [AgentReportController::class, 'showPrint'])->name('show.print')
            ;
    });

    // EMPLOYEE module — CSDI and FINAS only (card 6af6fe94, 2026-11-13).
    // Fixed paths come first so 'settings'/'create' are not read as {employee}.
    Route::prefix('employee-settings')->name('employees.settings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\EmployeeController::class, 'settings'])->name('index');
        Route::post('/', [\App\Http\Controllers\EmployeeController::class, 'storeSetting'])->name('store');
        Route::put('/{setting}', [\App\Http\Controllers\EmployeeController::class, 'updateSetting'])->name('update');
        Route::delete('/{setting}', [\App\Http\Controllers\EmployeeController::class, 'destroySetting'])->name('destroy');
    });

    Route::get('employees/export', [\App\Http\Controllers\EmployeeController::class, 'export'])->name('employees.export');
    Route::get('employees/{employee}/document/{field}', [\App\Http\Controllers\EmployeeController::class, 'download'])->name('employees.document');
    Route::resource('employees', \App\Http\Controllers\EmployeeController::class)->except(['show']);

    // Report Template CRUD
    Route::resource('report-templates', ReportTemplateController::class)->except(['show']);

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');

    // Accounts module — renamed from "Chart of Accounts", now lives inside Settings.
    // URI nested under /settings but resource route names stay accounts.* for compatibility.
    Route::prefix('settings')->group(function () {
        Route::resource('accounts', \App\Http\Controllers\AccountController::class)->except('show');
    });

    Route::get('/settings/applicant-form-defaults', [SettingsController::class, 'applicantFormDefaults'])->name('settings.applicant-form-defaults');
    Route::post('/settings/applicant-form-defaults', [SettingsController::class, 'updateApplicantFormDefaults'])->name('settings.applicant-form-defaults.update');

    // Per-agency Applicants table column selection
    Route::get('/settings/applicants-table-columns', [SettingsController::class, 'applicantTableColumns'])->name('settings.applicants-table-columns');
    Route::post('/settings/applicants-table-columns', [SettingsController::class, 'updateApplicantTableColumns'])->name('settings.applicants-table-columns.update');

    // Per-agency site display: ALL CAPS toggle
    Route::get('/settings/site-display', [SettingsController::class, 'siteDisplay'])->name('settings.site-display');
    Route::post('/settings/site-display', [SettingsController::class, 'updateSiteDisplay'])->name('settings.site-display.update');

    // Per-agency Color Theme (changes the system colour palette)
    Route::get('/settings/color-theme', [SettingsController::class, 'colorTheme'])->name('settings.color-theme');
    Route::post('/settings/color-theme', [SettingsController::class, 'updateColorTheme'])->name('settings.color-theme.update');

    // Per-agency Agency Settings (sidebar visibility toggles)
    Route::get('/settings/agency', [SettingsController::class, 'agencySettings'])->name('settings.agency');
    Route::post('/settings/agency', [SettingsController::class, 'updateAgencySettings'])->name('settings.agency.update');

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
        // Card 6af81c30 part 3: Welfare Report replaces Agent Report on FINAS/CSDI.
        Route::get('/welfare', [ReportController::class, 'welfare'])->name('welfare');
        Route::get('/welfare/export', [ReportController::class, 'welfareExport'])->name('welfare.export');
        Route::get('/bill/{bill}', [ReportController::class, 'bill'])->name('bill');
        Route::get('/or/{or}', [ReportController::class, 'or'])->name('or');
        Route::get('/commission/{commission}', [ReportController::class, 'commission'])->name('commission');
        Route::get('/resume/{applicant}', [ReportController::class, 'resume'])->name('resume');
        Route::get('/expense-report/{applicant}', [ReportController::class, 'expenseReport'])->name('expense-report');
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

    // Per-agency Company Profile page (4 tabs, one WYSIWYG block per tab).
    // Default (no tab) routes are declared first so /company-profile/edit is not
    // swallowed by the {tab} pattern.
    Route::get('/company-profile', [CompanyProfileController::class, 'show'])->name('company-profile.show');
    Route::get('/company-profile/edit', [CompanyProfileController::class, 'edit'])->name('company-profile.edit');
    Route::post('/company-profile', [CompanyProfileController::class, 'update'])->name('company-profile.update');
    Route::post('/company-profile/image', [CompanyProfileController::class, 'uploadImage'])->name('company-profile.image');
    Route::get('/company-profile/{tab}/edit', [CompanyProfileController::class, 'edit'])
        ->whereIn('tab', \App\Support\CompanyProfileTabs::slugs())->name('company-profile.tab.edit');
    Route::post('/company-profile/{tab}', [CompanyProfileController::class, 'update'])
        ->whereIn('tab', \App\Support\CompanyProfileTabs::slugs())->name('company-profile.tab.update');
    Route::get('/company-profile/{tab}', [CompanyProfileController::class, 'show'])
        ->whereIn('tab', \App\Support\CompanyProfileTabs::slugs())->name('company-profile.tab');
});
