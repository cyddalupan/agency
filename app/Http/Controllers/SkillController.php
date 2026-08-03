<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SkillController extends Controller
{
    public function index(): View
    {
        $skills = Skill::orderBy('name')->paginate(15);

        return view('skills.index', compact('skills'));
    }

    public function create(): View
    {
        return view('skills.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:skills,name',
        ]);

        Skill::create($validated);

        return redirect()->route('skills.index')
            ->with('success', 'Skill added successfully.');
    }

    public function edit(Skill $skill): View
    {
        return view('skills.edit', compact('skill'));
    }

    public function update(Request $request, Skill $skill): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:skills,name,' . $skill->id,
        ]);

        $skill->update($validated);

        return redirect()->route('skills.index')
            ->with('success', 'Skill updated successfully.');
    }

    public function destroy(Skill $skill): RedirectResponse
    {
        $skill->delete();

        return redirect()->route('skills.index')
            ->with('success', 'Skill deleted successfully.');
    }
}
