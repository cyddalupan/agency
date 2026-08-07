<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $agencyId = $this->resolveAgencyId();

        $query = Expense::with(['account', 'encoder'])
            ->where('agency_id', $agencyId);

        // Filter by account (must belong to this agency)
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->integer('account_id'));
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date('date_to'));
        }

        $expenses = $query->orderByDesc('date')->paginate(20)->withQueryString();

        $accounts = Account::mains()
            ->with('children')
            ->where('agency_id', $agencyId)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $total = (clone $query)->sum('amount');

        return view('expenses.index', compact('expenses', 'accounts', 'total'));
    }

    public function create(): View
    {
        $accounts = $this->accountsForAgency();

        return view('expenses.create', compact('accounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $agencyId = $this->resolveAgencyId();

        $validated = $this->validateExpense($request);

        if (! $this->accountBelongsToAgency($validated['account_id'], $agencyId)) {
            return back()->withErrors(['account_id' => 'Invalid account for this agency.'])->withInput();
        }

        Expense::create(array_merge($validated, [
            'agency_id' => $agencyId,
            'user_id'   => auth()->id(),
        ]));

        return redirect()->route('expenses.index')
            ->with('success', 'Expense recorded successfully.');
    }

    public function edit(Expense $expense): View
    {
        abort_unless($expense->agency_id === $this->resolveAgencyId(), 403);

        $accounts = $this->accountsForAgency();

        return view('expenses.edit', compact('expense', 'accounts'));
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        abort_unless($expense->agency_id === $this->resolveAgencyId(), 403);

        $validated = $this->validateExpense($request);

        if (! $this->accountBelongsToAgency($validated['account_id'], $expense->agency_id)) {
            return back()->withErrors(['account_id' => 'Invalid account for this agency.'])->withInput();
        }

        $expense->update($validated);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        abort_unless($expense->agency_id === $this->resolveAgencyId(), 403);

        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    private function validateExpense(Request $request): array
    {
        return $request->validate([
            'account_id'   => 'required|integer|exists:accounts,id',
            'amount'       => 'required|numeric|min:0.01',
            'date'         => 'required|date',
            'payee'        => 'nullable|string|max:191',
            'method'       => 'nullable|string|in:'.implode(',', Expense::METHODS),
            'reference_no' => 'nullable|string|max:191',
            'notes'        => 'nullable|string',
        ]);
    }

    private function accountBelongsToAgency(int $accountId, int $agencyId): bool
    {
        return Account::where('id', $accountId)->where('agency_id', $agencyId)->exists();
    }

    private function accountsForAgency()
    {
        return Account::mains()
            ->with('children')
            ->where('agency_id', $this->resolveAgencyId())
            ->orderBy('type')
            ->orderBy('name')
            ->get();
    }
}
