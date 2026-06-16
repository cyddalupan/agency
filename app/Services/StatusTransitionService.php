<?php

namespace App\Services;

use App\Models\StatusTransition;

class StatusTransitionService
{
    /**
     * Check if a transition from $fromCode to $toCode is allowed.
     */
    public function canTransition(int $fromCode, int $toCode): bool
    {
        return StatusTransition::where('from_code', $fromCode)
            ->where('to_code', $toCode)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get all allowed next status codes from a given status.
     */
    public function allowedTransitions(int $fromCode): array
    {
        return StatusTransition::where('from_code', $fromCode)
            ->where('is_active', true)
            ->pluck('to_code')
            ->toArray();
    }

    /**
     * Get the full transition map (from_code => [to_code, ...]).
     */
    public function getAllTransitions(): array
    {
        $transitions = StatusTransition::where('is_active', true)
            ->get()
            ->groupBy('from_code')
            ->map(fn($items) => $items->pluck('to_code')->toArray())
            ->toArray();

        return $transitions;
    }

    /**
     * Validate and return error message if transition is invalid.
     * Returns null if valid, error string if invalid.
     */
    public function validateTransition(int $fromCode, int $toCode): ?string
    {
        if (!StatusCodeService::exists($fromCode) || !StatusCodeService::exists($toCode)) {
            return 'Invalid status code.';
        }

        if ($fromCode === $toCode) {
            return 'Status is already set to this value.';
        }

        if (!$this->canTransition($fromCode, $toCode)) {
            $fromLabel = StatusCodeService::label($fromCode);
            $toLabel = StatusCodeService::label($toCode);
            return "Cannot transition from '{$fromLabel}' to '{$toLabel}'.";
        }

        return null;
    }
}
