@php $universe = config('app.universe', 1); @endphp
<!DOCTYPE html>
<html lang="en" data-theme="{{ $universe == 2 ? 'universe-2' : 'corporate' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>📊 Agent Dashboard — {{ app_brand_name() }}</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>{{ app_brand_favicon_emoji() }}</text></svg>" type="image/svg+xml">
    <link rel="apple-touch-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>{{ app_brand_favicon_emoji() }}</text></svg>">
    @vite('resources/css/app.css')
</head>
<body class="bg-base-200 min-h-screen">
    {{-- Top bar --}}
    <div class="navbar bg-base-100/80 backdrop-blur-sm border-b border-base-200 px-4 lg:px-6 min-h-14 sticky top-0 z-30">
        <div class="flex-1 flex items-center gap-2">
            <span class="text-xl">{{ app_brand_icon() }}</span>
            <span class="font-bold text-lg">{{ app_brand_name() }}</span>
            <span class="badge badge-ghost badge-sm ml-2">Agent</span>
        </div>
        <div class="flex-none flex items-center gap-3">
            <div class="avatar placeholder">
                <div class="w-9 h-9 rounded-full bg-primary text-primary-content flex items-center justify-center text-sm font-bold">
                    {{ strtoupper(substr($agent->name, 0, 1)) }}
                </div>
            </div>
            <span class="text-sm opacity-70 hidden sm:inline">{{ $agent->name }}</span>
            <form method="POST" action="{{ route('agent.logout') }}" class="inline">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm text-error">🚪 Logout</button>
            </form>
        </div>
    </div>

    <main class="p-4 lg:p-6 max-w-6xl mx-auto">
        {{-- Agent Info Card --}}
        <div class="card bg-base-100 shadow-sm mb-6">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold">Welcome, {{ $agent->name }}! 🎉</h2>
                        <p class="text-sm opacity-60">{{ $agent->email }}</p>
                    </div>
                    <div class="badge badge-lg {{ $agent->commission_rate ? 'badge-primary' : 'badge-ghost' }}">
                        Commission: {{ $agent->commission_rate ?? 'N/A' }}%
                    </div>
                </div>
            </div>
        </div>

        {{-- Status Pipeline --}}
        <div class="card bg-base-100 shadow-sm mb-4">
            <div class="card-body">
                <h3 class="card-title text-lg mb-3">📊 Deployment Pipeline</h3>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('applicants.index') }}"
                      class="badge badge-lg {{ request()->query('status') === null ? 'badge-primary' : 'badge-ghost' }}">
                        📋 All
                        <span class="ml-1">{{ $statusCounts->sum() }}</span>
                    </a>
                    @foreach($statusCodes as $sc)
                        @php $count = $statusCounts->get($sc->code, 0); @endphp
                        @if($count > 0)
                        <a href="{{ route('applicants.index', ['status' => $sc->code]) }}"
                           class="badge badge-lg {{ request('status') === (string)$sc->code ? 'badge-primary' : '' }}" style="{{ request('status') === (string)$sc->code ? '' : 'background-color: ' . ($sc->color ?? '#e5e7eb') . '; color: #fff;' }}">
                            {{ $sc->label }}
                            <span class="ml-1">{{ $count }}</span>
                        </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Referred Applicants --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="card-title text-lg mb-4">👥 My Referred Applicants</h3>

                @if ($applicants->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Country</th>
                                    <th>Position</th>
                                    <th>Status</th>
                                    <th>Referred On</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($applicants as $applicant)
                                <tr>
                                    <td class="font-medium">{{ $applicant->first_name }} {{ $applicant->last_name }}</td>
                                    <td>{{ $applicant->country?->name ?? 'N/A' }}</td>
                                    <td>{{ $applicant->position?->name ?? 'N/A' }}</td>
                                    <td>
                                        @if ($code = $applicant->statusCode)
                                            <span class="badge" style="background-color: {{ $code->color ?? '#e5e7eb' }}20; color: {{ $code->color ?? '#374151' }}">{{ $code->label }}</span>
                                        @else
                                            <span class="badge badge-ghost">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-sm opacity-60">{{ $applicant->created_at->format('Y-m-d') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $applicants->links() }}
                    </div>
                @else
                    <div class="text-center py-10 opacity-50">
                        <span class="text-4xl block mb-3">📭</span>
                        <p class="text-lg">No referred applicants yet.</p>
                        <p class="text-sm mt-1">Applicants referred by you will appear here.</p>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <footer class="px-4 lg:px-6 py-3 text-center text-xs opacity-40 border-t border-base-300 mt-6">
        {{ app_brand_icon() }} {{ app_brand_name() }} &bull; Powered by TOYBITS
    </footer>
</body>
</html>
