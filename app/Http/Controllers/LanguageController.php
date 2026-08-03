<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LanguageController extends Controller
{
    public function index(): View
    {
        $languages = Language::orderBy('name')->paginate(15);

        return view('languages.index', compact('languages'));
    }

    public function create(): View
    {
        return view('languages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:languages,name',
        ]);

        Language::create($validated);

        return redirect()->route('languages.index')
            ->with('success', 'Language added successfully.');
    }

    public function edit(Language $language): View
    {
        return view('languages.edit', compact('language'));
    }

    public function update(Request $request, Language $language): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:languages,name,' . $language->id,
        ]);

        $language->update($validated);

        return redirect()->route('languages.index')
            ->with('success', 'Language updated successfully.');
    }

    public function destroy(Language $language): RedirectResponse
    {
        $language->delete();

        return redirect()->route('languages.index')
            ->with('success', 'Language deleted successfully.');
    }
}
