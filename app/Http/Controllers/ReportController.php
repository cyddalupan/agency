<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\OfficialReceipt;
use App\Models\Commission;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function bill(Bill $bill)
    {
        if ($bill->agency_id !== auth()->user()->agency_id) {
            abort(404);
        }

        $bill->load('employer');

        $pdf = Pdf::loadView('reports.bill', compact('bill'));
        return $pdf->download("bill-{$bill->id}.pdf");
    }

    public function or(OfficialReceipt $or)
    {
        if ($or->agency_id !== auth()->user()->agency_id) {
            abort(404);
        }

        $or->load('payment.bill');

        $pdf = Pdf::loadView('reports.or', compact('or'));
        return $pdf->download("or-{$or->id}.pdf");
    }

    public function commission(Commission $commission)
    {
        if ($commission->agency_id !== auth()->user()->agency_id) {
            abort(404);
        }

        $commission->load('employer');

        $pdf = Pdf::loadView('reports.commission', compact('commission'));
        return $pdf->download("commission-{$commission->id}.pdf");
    }
}
