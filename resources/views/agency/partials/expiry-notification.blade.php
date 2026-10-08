{{--
    Expiry pop-up (Mjolnir "LANDAS: POP-UP Expire notification", 2026-10-08).

    Shows once per session on the agency dashboard:
      • Medical — expiring within 2 weeks
      • Visa    — expiring within 1 month

    Dismissal is stored in sessionStorage so it does not nag on every reload.
--}}
@if (($expiringMedicals ?? collect())->isNotEmpty() || ($expiringVisas ?? collect())->isNotEmpty())
    <dialog id="expiry-notification-modal" class="modal">
        <div class="modal-box max-w-3xl">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>

            <h3 class="font-bold text-lg flex items-center gap-2">
                <span>⏰</span> Expiring Documents
            </h3>
            <p class="text-sm opacity-60 mt-1 mb-4">
                These records are about to expire. Please take action.
            </p>

            @if (($expiringMedicals ?? collect())->isNotEmpty())
                <div class="mb-5">
                    <h4 class="font-semibold text-sm uppercase tracking-wide text-error mb-2">
                        🩺 Medical — expiring within 2 weeks ({{ $expiringMedicals->count() }})
                    </h4>
                    <div class="overflow-x-auto">
                        <table class="table table-sm table-zebra">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Clinic</th>
                                    <th>Issue Date</th>
                                    <th>Expire Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($expiringMedicals as $medical)
                                    <tr>
                                        <td>{{ optional($medical->applicant)->full_name ?? '—' }}</td>
                                        <td>{{ $medical->clinic_name ?: '—' }}</td>
                                        <td>{{ $medical->issue_date ? \Illuminate\Support\Carbon::parse($medical->issue_date)->format('M j, Y') : '—' }}</td>
                                        <td class="text-error font-medium">{{ $medical->expiry_date ? \Illuminate\Support\Carbon::parse($medical->expiry_date)->format('M j, Y') : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if (($expiringVisas ?? collect())->isNotEmpty())
                <div class="mb-2">
                    <h4 class="font-semibold text-sm uppercase tracking-wide text-warning mb-2">
                        🛂 Visa — expiring within 1 month ({{ $expiringVisas->count() }})
                    </h4>
                    <div class="overflow-x-auto">
                        <table class="table table-sm table-zebra">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Branch</th>
                                    <th>FRA</th>
                                    <th>Visa No.</th>
                                    <th>Expire Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($expiringVisas as $visa)
                                    <tr>
                                        <td>{{ optional($visa->applicant)->full_name ?? '—' }}</td>
                                        <td>{{ optional(optional($visa->applicant)->branch)->name ?? '—' }}</td>
                                        <td>{{ optional(optional($visa->applicant)->employer)->name ?? '—' }}</td>
                                        <td>{{ $visa->visa_no ?: '—' }}</td>
                                        <td class="text-warning font-medium">{{ $visa->expiry_date ? \Illuminate\Support\Carbon::parse($visa->expiry_date)->format('M j, Y') : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="modal-action">
                <form method="dialog">
                    <button class="btn btn-primary">Got it</button>
                </form>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    @push('scripts')
        <script>
            (function () {
                const KEY = 'expiry_notification_dismissed';
                const modal = document.getElementById('expiry-notification-modal');
                if (!modal) return;
                if (sessionStorage.getItem(KEY) === '1') return;
                modal.showModal();
                modal.addEventListener('close', function () {
                    sessionStorage.setItem(KEY, '1');
                });
            })();
        </script>
    @endpush
@endif
