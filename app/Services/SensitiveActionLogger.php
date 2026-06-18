<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SensitiveActionLogger
{
    /**
     * Log a sensitive action to the activity_logs table.
     */
    public static function log(
        string $action,
        ?Model $subject = null,
        ?string $description = null,
        ?array $metadata = null,
        ?int $agencyId = null,
        ?int $userId = null,
    ): ActivityLog {
        $request = request();
        $currentUser = $request?->user();

        // Collect metadata
        $meta = $metadata ?? [];
        if (!isset($meta['ip']) && $request) {
            $meta['ip'] = $request->ip();
        }
        if (!isset($meta['user_agent']) && $request) {
            $meta['user_agent'] = $request->userAgent();
        }

        return ActivityLog::create([
            'agency_id'    => $agencyId ?? $currentUser?->agency_id ?? self::findAgencyFromSubject($subject),
            'user_id'      => $userId ?? $currentUser?->id,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject?->getKey(),
            'action'       => $action,
            'description'  => $description,
            'metadata'     => !empty($meta) ? $meta : null,
        ]);
    }

    /**
     * Log a login event.
     */
    public static function login(Model $user): void
    {
        self::log('login', subject: $user, description: $user->email . ' logged in.');
    }

    /**
     * Log a logout event.
     */
    public static function logout(Model $user): void
    {
        self::log('logout', subject: $user, description: $user->email . ' logged out.');
    }

    /**
     * Log a failed login attempt.
     */
    public static function failedLogin(string $email, ?int $agencyId = null): void
    {
        self::log(
            'failed_login',
            description: "Failed login attempt for {$email}.",
            metadata: ['target_email' => $email],
            agencyId: $agencyId,
        );
    }

    /**
     * Log a role change.
     */
    public static function roleChanged(Model $user, string $oldRole, string $newRole, ?User $changedBy = null): void
    {
        self::log(
            'role_changed',
            subject: $user,
            description: ($changedBy?->name ?? 'System') . " changed role of {$user->name} from {$oldRole} to {$newRole}.",
            metadata: ['old_role' => $oldRole, 'new_role' => $newRole],
            userId: $changedBy?->id,
        );
    }

    /**
     * Log a data export.
     */
    public static function dataExport(string $exportType, string $description): void
    {
        self::log(
            'data_export',
            description: $description,
            metadata: ['export_type' => $exportType],
        );
    }

    /**
     * Log a deletion.
     */
    public static function deletion(Model $subject): void
    {
        self::log(
            'deleted',
            subject: $subject,
            description: get_class($subject) . ' #' . $subject->getKey() . ' was deleted.',
        );
    }

    /**
     * Log an agency status change.
     */
    public static function agencyStatusChange(Model $agency, string $newStatus): void
    {
        self::log(
            $newStatus === 'active' ? 'agency_activated' : 'agency_deactivated',
            subject: $agency,
            description: "Agency {$agency->name} was {$newStatus}.",
        );
    }

    /**
     * Log suspicious activity.
     */
    public static function suspiciousActivity(string $description, array $metadata = [], ?int $agencyId = null): void
    {
        self::log(
            'suspicious_activity',
            description: $description,
            metadata: $metadata,
            agencyId: $agencyId,
        );
    }

    /**
     * Attempt to derive agency_id from a subject model.
     */
    private static function findAgencyFromSubject(?Model $subject): ?int
    {
        if (!$subject) {
            return null;
        }

        if (isset($subject->agency_id)) {
            return $subject->agency_id;
        }

        return null;
    }
}
