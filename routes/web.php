<?php

use App\Http\Controllers\AccountingController;
use App\Http\Controllers\AgencyDashboardController;
use App\Http\Controllers\ApplicantAuthController;
use App\Http\Controllers\ApplicantOtpAuthController;
use App\Http\Controllers\ApplicantController;
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
use App\Http\Controllers\OfficialReceiptController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PortalDocumentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Api\CaseController;
use App\Http\Controllers\SubTableController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ReportsIndexController;
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
    Route::get('/login', [EmployerAuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [EmployerAuthController::class, 'login'])->name('login.post');

    // Authenticated
    Route::middleware(['auth:web', 'employer'])->group(function () {
        Route::post('/logout', [EmployerAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [EmployerDashboardController::class, 'index'])->name('dashboard');

        // Billing
        Route::get('/billing', [EmployerBillingController::class, 'index'])->name('billing.index');
        Route::get('/billing/soa', [EmployerBillingController::class, 'soa'])->name('billing.soa');
        Route::get('/billing/applicant/{applicant}', [EmployerBillingController::class, 'applicant'])->name('billing.applicant');
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
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Super admin dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Agency dashboard
    Route::get('/agency/dashboard', [AgencyDashboardController::class, 'index'])->name('agency.dashboard');

    // User management (admin, super_admin only)
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::resource('users', UserController::class);
    });

    // Applicant routes - recruiter and other agency roles
    Route::middleware('role:admin,super_admin,recruiter,staff,processor,coordinator,interviewer,manager,marketer,director')->group(function () {
        Route::get('applicants/export', [ApplicantController::class, 'export'])->name('applicants.export');
        Route::patch('applicants/{applicant}/status', [ApplicantController::class, 'updateStatus'])->name('applicants.status');
        Route::get('applicants/{applicant}/soa', [ApplicantController::class, 'soa'])->name('applicants.soa');
        Route::resource('applicants', ApplicantController::class);

        // Applicant sub-table routes
        Route::prefix('applicants/{applicant}')->name('applicants.')->group(function () {
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

    Route::resource('custom-fields', CustomFieldDefinitionController::class);

    // Accounting routes
    Route::prefix('accounting')->name('accounting.')->group(function () {
        Route::get('/employer/{employer}', [AccountingController::class, 'employer'])->name('employer');
        Route::get('/worker/{applicant}', [AccountingController::class, 'worker'])->name('worker');
        Route::get('/marketing-agency/{marketingAgency}', [AccountingController::class, 'marketingAgency'])->name('marketing-agency');
        Route::get('/marketing-agent/{marketingAgent}', [AccountingController::class, 'marketingAgent'])->name('marketing-agent');
        Route::get('/recruitment-agent/{recruitmentAgent}', [AccountingController::class, 'recruitmentAgent'])->name('recruitment-agent');
    });

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');

    // Reports index
    Route::get('/reports', [ReportsIndexController::class, 'index'])->name('reports.index');

    // Transaction history
    Route::get('/transactions', [ReportController::class, 'transactions'])->name('transactions.index');

    // Report PDF routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/applicants', [ReportController::class, 'applicants'])->name('applicants');
        Route::get('/bill/{bill}', [ReportController::class, 'bill'])->name('bill');
        Route::get('/or/{or}', [ReportController::class, 'or'])->name('or');
        Route::get('/commission/{commission}', [ReportController::class, 'commission'])->name('commission');
        Route::get('/resume/{applicant}', [ReportController::class, 'resume'])->name('resume');
        Route::get('/statistics', [ReportController::class, 'statistics'])->name('statistics');
    });

    // Case Management API
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('cases/search', [CaseController::class, 'search'])->name('cases.search');
        Route::resource('cases', CaseController::class)->parameters(['cases' => 'case'])->except(['create', 'edit']);
    });

    // Redirect root to dashboard
    Route::get('/', function () {
        if (auth()->user()->agency_id) {
            return redirect()->route('agency.dashboard');
        }
        return redirect()->route('dashboard');
    });
});
