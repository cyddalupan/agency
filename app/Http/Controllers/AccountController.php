<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        $mains = Account::mains()
            ->with('children')
            ->where('agency_id', $this->resolveAgencyId())
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(20);

        return view('accounts.index', compact('mains'));
    }

    public function create(): View
    {
        $mains = Account::mains()
            ->where('agency_id', $this->resolveAgencyId())
            ->orderBy('name')
            ->get();

        return view('accounts.create', compact('mains'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:191',
            'type'      => 'required|string|in:income,expense',
            'parent_id' => 'nullable|integer|exists:accounts,id',
        ]);

        // If a parent is given, ensure it belongs to the same agency and is a Main account,
        // and inherit the parent's type.
        if (! empty($validated['parent_id'])) {
            $parent = Account::find($validated['parent_id']);
            if (! $parent || $parent->agency_id !== $this->resolveAgencyId()) {
                return back()
                    ->withErrors(['parent_id' => 'Invalid parent account.'])
                    ->withInput();
            }
            if (! $parent->isMain()) {
                return back()
                    ->withErrors(['parent_id' => 'A Sub account cannot have a Sub parent.'])
                    ->withInput();
            }
            $validated['type'] = $parent->type;
        }

        $agencyId = $this->resolveAgencyId();
        if (! $agencyId) {
            return back()->withErrors(['agency' => 'No agency context. Please log in with an agency account.'])->withInput();
        }

        Account::create(array_merge($validated, ['agency_id' => $agencyId]));

        return redirect()->route('accounts.index')
            ->with('success', 'Account created successfully.');
    }

    public function edit(Account $account): View
    {
        abort_unless($account->agency_id === $this->resolveAgencyId(), 403);

        $mains = Account::mains()
            ->where('agency_id', $this->resolveAgencyId())
            ->where('id', '!=', $account->id)
            ->orderBy('name')
            ->get();

        return view('accounts.edit', compact('account', 'mains'));
    }

    public function update(Request $request, Account $account): RedirectResponse
    {
        abort_unless($account->agency_id === $this->resolveAgencyId(), 403);

        $validated = $request->validate([
            'name'      => 'required|string|max:191',
            'type'      => 'required|string|in:income,expense',
            'parent_id' => 'nullable|integer|exists:accounts,id',
        ]);

        // Prevent setting a Main account under itself as a Sub
        if ((int) ($validated['parent_id'] ?? 0) === $account->id) {
            return back()
                ->withErrors(['parent_id' => 'An account cannot be its own parent.'])
                ->withInput();
        }

        // A Main account that already has children cannot be turned into a Sub.
        if (! empty($validated['parent_id']) && $account->isMain() && $account->children()->exists()) {
            return back()
                ->withErrors(['parent_id' => 'A Main account with Sub accounts cannot become a Sub account.'])
                ->withInput();
        }

        if (! empty($validated['parent_id'])) {
            $parent = Account::find($validated['parent_id']);
            if (! $parent || $parent->agency_id !== $this->resolveAgencyId()) {
                return back()
                    ->withErrors(['parent_id' => 'Invalid parent account.'])
                    ->withInput();
            }
            if (! $parent->isMain()) {
                return back()
                    ->withErrors(['parent_id' => 'A Sub account cannot have a Sub parent.'])
                    ->withInput();
            }
            $validated['type'] = $parent->type;
        }

        $account->update($validated);

        return redirect()->route('accounts.index')
            ->with('success', 'Account updated successfully.');
    }

    public function destroy(Request $request, Account $account): RedirectResponse
    {
        abort_unless($account->agency_id === $this->resolveAgencyId(), 403);

        if ($account->isMain() && $account->children()->exists()) {
            return back()->withErrors(['children' => 'Remove the Sub accounts under this Main account first.']);
        }

        $account->delete();

        return redirect()->route('accounts.index')
            ->with('success', 'Account deleted successfully.');
    }
}
