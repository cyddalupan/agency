<?php

namespace App\Http\Controllers;

use App\Models\CustomFieldDefinition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Resolve the agency ID from the authenticated user or tenant context.
     * Returns null if neither is available.
     */
    protected function resolveAgencyId(): ?int
    {
        return auth()->user()->agency_id ?? tenant_agency()?->id;
    }

    /**
     * Validate that the current user has an agency context.
     * Redirects back with error if not.
     */
    protected function requireAgencyContext(): ?RedirectResponse
    {
        if (! $this->resolveAgencyId()) {
            return back()->withErrors(['agency' => 'No agency context. Please log in with an agency account.'])->withInput();
        }
        return null;
    }

    /**
     * Validate required custom fields for a given model type.
     */
    protected function validateCustomFields(Request $request, string $modelType): void
    {
        if (! auth()->check()) {
            return;
        }

        $agencyId = auth()->user()->agency_id;

        $requiredFields = CustomFieldDefinition::where('agency_id', $agencyId)
            ->where('model_type', $modelType)
            ->where('required', true)
            ->get();

        $rules = [];

        foreach ($requiredFields as $field) {
            $rule = match ($field->type) {
                'number' => 'required|numeric',
                'date'   => 'required|date',
                'url'    => 'required|url',
                'email'  => 'required|email',
                default  => 'required|string|max:255',
            };

            $rules[$field->key] = $rule;
        }

        if (! empty($rules)) {
            $request->validate($rules);
        }
    }
}
