<?php

namespace App\Http\Controllers;

use App\Models\CustomFieldDefinition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomFieldDefinitionController extends Controller
{
    public function index(): View
    {
        $fields = CustomFieldDefinition::where('agency_id', auth()->user()->agency_id)
            ->orderBy('model_type')
            ->orderBy('order')
            ->orderBy('name')
            ->paginate(15);

        return view('custom-fields.index', compact('fields'));
    }

    public function create(): View
    {
        return view('custom-fields.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'model_type' => 'required|string|max:100',
            'name'       => 'required|string|max:255',
            'type'       => 'required|string|in:text,textarea,number,date,select,checkbox,url',
            'options'    => 'nullable|string',
            'required'   => 'nullable|boolean',
            'order'      => 'nullable|integer|min:0',
        ]);

        $validated['agency_id'] = auth()->user()->agency_id;

        // Convert newline-separated options into array
        if (! empty($validated['options']) && is_string($validated['options'])) {
            $validated['options'] = array_values(
                array_filter(array_map('trim', explode("\n", $validated['options'])))
            );
        } else {
            $validated['options'] = null;
        }

        $validated['required'] ??= false;
        $validated['order'] ??= 0;

        CustomFieldDefinition::create($validated);

        return redirect()->route('custom-fields.index')
            ->with('success', 'Custom field created successfully.');
    }

    public function edit(CustomFieldDefinition $customField): View
    {
        return view('custom-fields.edit', compact('customField'));
    }

    public function update(Request $request, CustomFieldDefinition $customField): RedirectResponse
    {
        $validated = $request->validate([
            'model_type' => 'required|string|max:100',
            'name'       => 'required|string|max:255',
            'type'       => 'required|string|in:text,textarea,number,date,select,checkbox,url',
            'options'    => 'nullable|string',
            'required'   => 'nullable|boolean',
            'order'      => 'nullable|integer|min:0',
        ]);

        // Convert newline-separated options into array
        if (! empty($validated['options']) && is_string($validated['options'])) {
            $validated['options'] = array_values(
                array_filter(array_map('trim', explode("\n", $validated['options'])))
            );
        } else {
            $validated['options'] = null;
        }

        $validated['required'] ??= false;
        $validated['order'] ??= 0;

        $customField->update($validated);

        return redirect()->route('custom-fields.index')
            ->with('success', 'Custom field updated successfully.');
    }

    public function destroy(CustomFieldDefinition $customField): RedirectResponse
    {
        $customField->delete();

        return redirect()->route('custom-fields.index')
            ->with('success', 'Custom field deleted successfully.');
    }
}
