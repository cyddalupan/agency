@extends('layouts.app')

@section('title', 'QR Code')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">📱 QR Code</h1>
            <p class="text-sm opacity-70 mt-1">
                Print this code. An applicant scans it and lands straight on the Add Applicant form for
                {{ $agency->name }} — nothing else is reachable from it.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4"><span>{{ session('success') }}</span></div>
    @endif

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body items-center text-center gap-4">
            <div class="bg-white p-4 rounded-xl" id="qr-canvas">
                {!! $svg !!}
            </div>

            <div class="w-full max-w-xl">
                <p class="text-xs uppercase tracking-wide opacity-50 mb-1">Scan link</p>
                <div class="flex items-center gap-2">
                    <input type="text" class="input input-bordered w-full text-xs" value="{{ $url }}" readonly onclick="this.select()">
                    <a href="{{ $url }}" target="_blank" class="btn btn-ghost btn-sm">Open ↗</a>
                </div>
                <p class="text-xs opacity-60 mt-2">
                    Anyone with this link can submit an applicant for {{ $agency->name }}. Share it only where you intend.
                </p>
            </div>

            <div class="flex flex-wrap gap-2 justify-center mt-2">
                <button type="button" onclick="downloadQr()" class="btn btn-outline btn-sm">⬇️ Download SVG</button>
                <form method="POST" action="{{ route('settings.qr-code.regenerate') }}"
                      onsubmit="return confirm('Generate a new code? Any QR already printed will stop working.');">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm">🔄 Regenerate</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function downloadQr() {
    var svg = document.querySelector('#qr-canvas svg');
    if (!svg) return;
    var data = new XMLSerializer().serializeToString(svg);
    var blob = new Blob([data], {type: 'image/svg+xml'});
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'applicant-qr.svg';
    a.click();
    URL.revokeObjectURL(a.href);
}
</script>
@endpush
@endsection
