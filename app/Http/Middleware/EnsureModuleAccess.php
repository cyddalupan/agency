<?php

namespace App\Http\Middleware;

use App\Support\ModuleAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces the 2026-09-24 access tiers:
 *   - Accounting (`billing`): admin access minus ModuleAccess::ACCOUNTING_DENIED.
 *   - Rest of Account: allowlisted modules only (403 otherwise).
 *   - FRA is read-only for Rest of Account; Reports is Applicant Reports only.
 *   - super_admin/admin keep full access.
 */
class EnsureModuleAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $type = (string) $user->user_type;

        // Full-access roles.
        if (in_array($type, ['super_admin', 'admin'], true)) {
            return $next($request);
        }

        $route  = $request->route();
        $name   = $route ? (string) $route->getName() : '';
        $module = ModuleAccess::moduleFor($name, $request->path());

        if (! ModuleAccess::allows($type, $module)) {
            abort(403, 'Your account does not have access to this module.');
        }

        // Accounting keeps full FRA/Reports; the read-only limits apply to Rest of Account.
        if (ModuleAccess::isRestOfAccount($type)) {
            if ($module === 'employers' && ! $this->employerViewAllowed($name)) {
                abort(403, 'You can view FRA records but cannot add, edit, or remove them.');
            }

            if ($module === 'reports' && ! $this->applicantReportAllowed($name)) {
                abort(403, 'Your account can only access Applicant Reports.');
            }
        }

        return $next($request);
    }

    /** Read-only FRA: list, view, statement of account, and job-position viewing. */
    private function employerViewAllowed(string $name): bool
    {
        return in_array($name, [
            'employers.index',
            'employers.show',
            'employers.soa',
            'employers.job-positions.index',
            'employers.job-positions.show',
        ], true);
    }

    /** Reports tier: the Reports index + the Applicant Reports + its export only. */
    private function applicantReportAllowed(string $name): bool
    {
        return in_array($name, [
            'reports.index',
            'reports.applicants',
            'reports.applicants.export',
        ], true);
    }
}
