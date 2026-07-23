<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        $payments = Payment::with(['bill', 'bill.employer'])
            ->latest()
            ->paginate(15);

        return view('payments.index', compact('payments'));
    }

    public function create(): View
    {
        $bills = Bill::where('agency_id', auth()->user()->agency_id)
            ->latest()
            ->get();

        return view('payments.create', compact('bills'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bill_id'      => 'nullable|exists:bills,id',
            'amount'       => 'required|numeric|min:0.01',
            'category'     => 'required|string|max:50',
            'type'         => 'nullable|string|max:50',
            'reference_no' => 'nullable|string|max:100',
            'status'       => 'nullable|string|max:50',
            'payment_date' => 'nullable|date',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $validated['agency_id'] = $this->resolveAgencyId();
        if (! $validated['agency_id']) { return back()->withErrors(['agency' => 'No agency context. Please log in with an agency account.'])->withInput(); }

        Payment::create($validated);

        return redirect()->route('payments.index')
            ->with('success', 'Payment recorded successfully.');
    }

    public function show(Payment $payment): View
    {
        $payment->load(['bill', 'bill.employer', 'officialReceipt']);
        return view('payments.show', compact('payment'));
    }

    public function edit(Payment $payment): View
    {
        $bills = Bill::where('agency_id', auth()->user()->agency_id)
            ->latest()
            ->get();

        return view('payments.edit', compact('payment', 'bills'));
    }

    public function update(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'bill_id'      => 'nullable|exists:bills,id',
            'amount'       => 'required|numeric|min:0.01',
            'category'     => 'required|string|max:50',
            'type'         => 'nullable|string|max:50',
            'reference_no' => 'nullable|string|max:100',
            'status'       => 'nullable|string|max:50',
            'payment_date' => 'nullable|date',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $payment->update($validated);

        return redirect()->route('payments.index')
            ->with('success', 'Payment updated successfully.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $payment->delete();

        return redirect()->route('payments.index')
            ->with('success', 'Payment deleted successfully.');
    }
}
