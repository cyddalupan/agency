<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CountryController extends Controller
{
    public function index(): View
    {
        $countries = Country::orderBy('name')->paginate(20);

        return view('countries.index', compact('countries'));
    }

    public function create(): View
    {
        return view('countries.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:countries,name',
            'code'        => 'nullable|string|max:2',
            'nationality' => 'nullable|string|max:255',
        ]);

        Country::create($validated);

        return redirect()->route('countries.index')
            ->with('success', 'Country added successfully.');
    }

    public function edit(Country $country): View
    {
        return view('countries.edit', compact('country'));
    }

    public function update(Request $request, Country $country): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:countries,name,' . $country->id,
            'code'        => 'nullable|string|max:2',
            'nationality' => 'nullable|string|max:255',
        ]);

        $country->update($validated);

        return redirect()->route('countries.index')
            ->with('success', 'Country updated successfully.');
    }

    public function destroy(Country $country): RedirectResponse
    {
        $country->delete();

        return redirect()->route('countries.index')
            ->with('success', 'Country deleted successfully.');
    }
}
