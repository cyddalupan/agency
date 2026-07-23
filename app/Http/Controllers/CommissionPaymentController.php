<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\CommissionPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommissionPaymentController extends Controller
{
    private function authorizeCommission(Commission $commission): void
    {
        if ($commission->agency_id !== auth()->user()->agency_id) {
            abort(404);
        }
    }

    public function index(Commission $commission): View
    {
        $this->authorizeCommission($commission);

        $payments = $commission->commissionPayments()
            ->latest()
            ->get();

        return view('commission-payments.index', compact('commission', 'payments'));
    }

    public function create(Commission $commission): View
    {
        $this->authorizeCommission($commission);

        return view('commission-payments.create', compact('commission'));
    }

    public function store(Request $request, Commission $commission): RedirectResponse
    {
        $this->authorizeCommission($commission);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'reference_no' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['agency_id'] = $this->resolveAgencyId();
        if (! $validated['agency_id']) { return back()->withErrors(['agency' => 'No agency context. Please log in with an agency account.'])->withInput(); }
        $validated['commission_id'] = $commission->id;

        CommissionPayment::create($validated);

        $this->recalculateCommission($commission);

        return redirect()
            ->route('commissions.commission-payments.index', $commission)
            ->with('success', 'Commission payment recorded successfully.');
    }

    public function edit(Commission $commission, CommissionPayment $commissionPayment): View
    {
        $this->authorizeCommission($commission);

        return view('commission-payments.edit', compact('commission', 'commissionPayment'));
    }

    public function update(Request $request, Commission $commission, CommissionPayment $commissionPayment): RedirectResponse
    {
        $this->authorizeCommission($commission);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'reference_no' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $commissionPayment->update($validated);

        $this->recalculateCommission($commission);

        return redirect()
            ->route('commissions.commission-payments.index', $commission)
            ->with('success', 'Commission payment updated successfully.');
    }

    public function destroy(Commission $commission, CommissionPayment $commissionPayment): RedirectResponse
    {
        $this->authorizeCommission($commission);

        $commissionPayment->delete();

        $this->recalculateCommission($commission);

        return redirect()
            ->route('commissions.commission-payments.index', $commission)
            ->with('success', 'Commission payment deleted successfully.');
    }

    private function recalculateCommission(Commission $commission): void
    {
        $totalPaid = $commission->commissionPayments()->sum('amount');
        $status = $totalPaid <= 0 ? 'pending'
            : ($totalPaid >= $commission->amount ? 'paid' : 'partial');

        $commission->update([
            'paid_amount' => $totalPaid,
            'status' => $status,
        ]);
    }
}
