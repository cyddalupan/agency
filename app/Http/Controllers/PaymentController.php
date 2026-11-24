<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Payment;
use App\Services\TransactionEditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        $payments = Payment::with(['bill', 'bill.employer'])
            ->latest()
            ->paginate(print_per_page(15));

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

        // Card 6b056e02: payments are a real third edit surface
        // (Route::resource('payments')) and this write was silent.
        $original = $payment->getAttributes();
        $payment->update($validated);

        $log = app(TransactionEditLog::class);
        $changes = $log->paymentChanges($payment, $original);

        if ($changes !== null) {
            // Keep the edit visible on the Payments screen's own log; the
            // audit trail (audit_events) records the same diff separately.
            try {
                \App\Services\SensitiveActionLogger::log(
                    'payment.updated',
                    subject: $payment,
                    description: $log->editedNote(auth()->user()->name ?? 'user', $changes),
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }

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
