@extends('layouts.app')

@section('title', 'Preview: ' . $template->name)

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('reports.index') }}" class="text-sm opacity-70 hover:opacity-100">&larr; Back to Reports</a>
            <h1 class="text-2xl font-bold mt-1">{{ $template->name }}</h1>
            <p class="text-sm opacity-60">Type: {{ $template->type }} · {{ $rows instanceof \Illuminate\Pagination\AbstractPaginator ? $rows->total() : $rows->count() }} results</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('report-templates.edit', $template) }}" class="btn btn-ghost btn-sm">Edit Template</a>
            <a href="{{ route('reports.pdf', $template) }}" class="btn btn-primary btn-sm">📄 PDF</a>
            <a href="{{ route('reports.csv', $template) }}" class="btn btn-secondary btn-sm">📊 CSV</a>
        </div>
    </div>

    @if(isset($unsupported_type) && $unsupported_type)
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body text-center py-10">
                <span class="text-5xl mb-4 block">🚧</span>
                <h3 class="text-lg font-medium mb-2">Preview coming soon</h3>
                <p class="opacity-60">This report type ({{ $template->type }}) doesn't support live preview yet.</p>
                <div class="mt-4">
                    <a href="{{ route('reports.index') }}" class="btn btn-primary btn-sm">Back to Reports</a>
                </div>
            </div>
        </div>
    @elseif($rows instanceof \Illuminate\Pagination\AbstractPaginator && $rows->isEmpty())
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body text-center py-10">
                <span class="text-5xl mb-4 block">📭</span>
                <h3 class="text-lg font-medium mb-2">No data</h3>
                <p class="opacity-60">No records match the current template configuration.</p>
                <div class="mt-4">
                    <a href="{{ route('report-templates.edit', $template) }}" class="btn btn-primary btn-sm">Adjust Template</a>
                </div>
            </div>
        </div>
    @else
        <div class="card bg-base-100 shadow-sm overflow-x-auto">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th class="text-xs uppercase tracking-wider opacity-60">#</th>
                            @foreach($columns as $col)
                                <th class="text-xs uppercase tracking-wider opacity-60">
                                    {{ ['name'=>'Name','email'=>'Email','phone'=>'Phone','gender'=>'Gender','country'=>'Country','status'=>'Status','position'=>'Position','employer'=>'Employer','salary'=>'Salary','source'=>'Source','agent'=>'Agent','created_at'=>'Date Created','updated_at'=>'Last Updated'][$col] ?? ucfirst($col) }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = $rows->firstItem() ?? 1; @endphp
                        @foreach($rows as $row)
                        <tr>
                            <td class="text-xs opacity-40">{{ $i++ }}</td>
                            @foreach($columns as $col)
                                <td class="text-sm">{{ $row[$col] ?? '' }}</td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if($rows->hasPages())
        <div class="bg-base-200 px-4 py-3 border-t border-base-300">
            {{ $rows->links('pagination::tailwind') }}
        </div>
        @endif
    @endif
</div>
@endsection
