<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PositionController extends Controller
{
    public function index(): View
    {
        $positions = Position::orderBy('name')->paginate(20);

        return view('positions.index', compact('positions'));
    }

    public function create(): View
    {
        return view('positions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:positions,name',
            'description' => 'nullable|string|max:255',
        ]);

        Position::create($validated);

        return redirect()->route('positions.index')
            ->with('success', 'Position added successfully.');
    }

    public function edit(Position $position): View
    {
        return view('positions.edit', compact('position'));
    }

    public function update(Request $request, Position $position): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:positions,name,' . $position->id,
            'description' => 'nullable|string|max:255',
        ]);

        $position->update($validated);

        return redirect()->route('positions.index')
            ->with('success', 'Position updated successfully.');
    }

    public function destroy(Position $position): RedirectResponse
    {
        $position->delete();

        return redirect()->route('positions.index')
            ->with('success', 'Position deleted successfully.');
    }
}
