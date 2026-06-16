<?php

namespace App\Http\Controllers;

use App\Models\OfficialReceipt;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfficialReceiptController extends Controller
{
    public function index(): View
    {
        $officialReceipts = OfficialReceipt::with(['payment', 'payment.bill', 'payment.bill.employer'])
            ->latest()
            ->paginate(15);

        return view('official-receipts.index', compact('officialReceipts'));
    }

    public function create(): View
    {
        $payments = Payment::where('agency_id', auth()->user()->agency_id)
            ->latest()
            ->get();

        return view('official-receipts.create', compact('payments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_id'    => 'nullable|exists:payments,id',
            'or_no'         => 'required|string|max:100|unique:official_receipts,or_no',
            'amount'        => 'required|numeric|min:0.01',
            'issue_date'    => 'nullable|date',
            'issued_to'     => 'required|string|max:50',
            'issued_to_name' => 'required|string|max:255',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $validated['agency_id'] = auth()->user()->agency_id;

        OfficialReceipt::create($validated);

        return redirect()->route('official-receipts.index')
            ->with('success', 'Official Receipt issued successfully.');
    }

    public function show(OfficialReceipt $officialReceipt): View
    {
        $officialReceipt->load(['payment', 'payment.bill', 'payment.bill.employer']);
        return view('official-receipts.show', compact('officialReceipt'));
    }

    public function edit(OfficialReceipt $officialReceipt): View
    {
        $payments = Payment::where('agency_id', auth()->user()->agency_id)
            ->latest()
            ->get();

        return view('official-receipts.edit', compact('officialReceipt', 'payments'));
    }

    public function update(Request $request, OfficialReceipt $officialReceipt): RedirectResponse
    {
        $validated = $request->validate([
            'payment_id'    => 'nullable|exists:payments,id',
            'or_no'         => 'required|string|max:100|unique:official_receipts,or_no,' . $officialReceipt->id,
            'amount'        => 'required|numeric|min:0.01',
            'issue_date'    => 'nullable|date',
            'issued_to'     => 'required|string|max:50',
            'issued_to_name' => 'required|string|max:255',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $officialReceipt->update($validated);

        return redirect()->route('official-receipts.index')
            ->with('success', 'Official Receipt updated successfully.');
    }

    public function destroy(OfficialReceipt $officialReceipt): RedirectResponse
    {
        $officialReceipt->delete();

        return redirect()->route('official-receipts.index')
            ->with('success', 'Official Receipt deleted successfully.');
    }
}
