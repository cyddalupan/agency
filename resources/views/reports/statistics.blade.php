@extends('layouts.app')

@section('title', 'Deployment Statistics')

@push('styles')
<style>
@media print {
    .no-print { display: none !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; break-inside: avoid; }
    .container-fluid { max-width: 100% !important; padding: 0 !important; }
    body { font-size: 11pt; }
    table { font-size: 9pt; width: 100%; }
    th { background: #f5f5f5 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <h1 class="h3 mb-4">Deployment Statistics Dashboard
        <button onclick="window.print()" class="btn btn-outline btn-sm ms-2 no-print">🖨️ Print</button>
    </h1>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Applicants</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalApplicants }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Applicants by Status -->
    <div class="row mb-4">
        <div class="col-xl-6 col-md-12 mb-3">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Applicants by Status</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th class="text-center">Count</th>
                                    <th class="text-center">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($applicantsByStatus as $status)
                                    <tr>
                                        <td>
                                            <span class="badge" style="background-color: {{ $status->color ?? '#6c757d' }}; color: #fff;">
                                                {{ $status->label }}
                                            </span>
                                        </td>
                                        <td class="text-center">{{ $status->total }}</td>
                                        <td class="text-center">
                                            {{ $totalApplicants > 0 ? round(($status->total / $totalApplicants) * 100, 1) : 0 }}%
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">No applicants found</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Destinations -->
        <div class="col-xl-6 col-md-12 mb-3">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Destination Countries</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Country</th>
                                    <th class="text-center">Applicants</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topDestinations as $dest)
                                    <tr>
                                        <td>{{ $dest->name }}</td>
                                        <td class="text-center">{{ $dest->total }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted">No destination data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Deployment Trends -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Deployment Trends (Last 12 Months)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th class="text-center">Deployments</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($monthlyDeployments as $dep)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($dep->month . '-01')->format('F Y') }}</td>
                                        <td class="text-center">{{ $dep->total }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted">No deployment data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
