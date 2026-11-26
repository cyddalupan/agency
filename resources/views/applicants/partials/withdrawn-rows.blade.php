                        @foreach($applicants as $applicant)
                        <tr class="hover transition-colors">
                            <td>
                                <div class="avatar">
                                    <div class="w-10 h-10 rounded-full bg-primary/20 text-primary flex items-center justify-center text-xs font-bold overflow-hidden">
                                        @if ($applicant->photo)
                                            <img src="{{ $applicant->photo_url }}" class="w-full h-full object-cover" alt="{{ $applicant->full_name }}">
                                        @else
                                            {{ strtoupper(substr($applicant->first_name, 0, 1)) }}{{ strtoupper(substr($applicant->last_name, 0, 1)) }}
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-sm whitespace-nowrap">{{ $applicant->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('applicants.show', $applicant) }}" class="link link-primary font-medium">
                                    {{ $applicant->first_name }} {{ $applicant->last_name }}
                                </a>
                                @if($applicant->middle_name)
                                    <span class="opacity-40 text-sm"> {{ $applicant->middle_name }}</span>
                                @endif
                            </td>
                            <td>
                                @if($applicant->statusCode)
                                    <span class="badge badge-sm whitespace-nowrap"
                                        style="background-color: {{ $applicant->statusCode->color ?? '#e5e7eb' }}20; color: {{ $applicant->statusCode->color ?? '#374151' }}">
                                        {{ $applicant->statusCode->label }}
                                    </span>
                                @else
                                    <span class="badge badge-sm badge-ghost whitespace-nowrap">📋 Pending</span>
                                @endif
                            </td>
                            <td class="text-sm opacity-70">
                                {{ $applicant->age ?? '—' }}
                            </td>
                            <td class="text-sm">
                                @if($applicant->contact)
                                    {{ $applicant->contact }}
                                @elseif($applicant->email)
                                    {{ $applicant->email }}
                                @else
                                    <span class="opacity-40">—</span>
                                @endif
                            </td>
                            <td class="text-sm">
                                {{ $applicant->position?->name ?? '—' }}
                            </td>
                            <td class="text-sm">
                                {{-- No branch = main office ("main branch"); show it instead of blank. (2026-10-16) --}}
                                {{ $applicant->branch_label }}
                            </td>
                            <td class="text-sm">
                                {{ $applicant->agent?->name ?? '—' }}
                            </td>
                            <td class="text-sm">
                                @php $contractRecord = $applicant->contractRecords->first(); @endphp
                                @if ($applicant->contract)
                                    <a href="{{ Storage::url($applicant->contract) }}" target="_blank"
                                       class="link link-primary text-sm" title="View contract">📄 View</a>
                                @elseif ($contractRecord)
                                    <a href="{{ route('applicants.show', $applicant) }}" class="link link-primary text-sm"
                                       title="Contract: {{ $contractRecord->rfp ?: $contractRecord->sponsor }}">📄 Added</a>
                                @else
                                    <span class="opacity-40">—</span>
                                @endif
                            </td>
                            <td class="text-sm">
                                @php $contractReceived = $applicant->contract_received_date ?? $contractRecord?->contract_received; @endphp
                                @if ($contractReceived)
                                    <span class="text-success font-medium">{{ $contractReceived->format('M d, Y') }}</span>
                                @else
                                    <span class="opacity-40">—</span>
                                @endif
                            </td>
                            <td class="text-sm">{{ $applicant->encoder ?? $applicant->creator?->name ?? '—' }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('applicants.show', $applicant) }}" class="btn btn-ghost btn-xs btn-square" title="View">👁️</a>
                                    <a href="{{ route('applicants.edit', $applicant) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">✏️</a>
                                    <button type="button" class="btn btn-ghost btn-xs btn-square text-error" title="Delete"
                                        onclick="document.getElementById('delete-applicant-{{ $applicant->id }}').showModal()">🗑️</button>
                                </div>
                                <dialog id="delete-applicant-{{ $applicant->id }}" class="modal">
                                    <div class="modal-box">
                                        <h3 class="font-bold text-lg mb-2">Delete Applicant</h3>
                                        <p>Are you sure you want to delete <strong>{{ $applicant->first_name }} {{ $applicant->last_name }}</strong>?</p>
                                        <p class="text-sm opacity-60 mt-2">This action cannot be undone. All associated data (education, work history, documents, etc.) will also be removed.</p>
                                        <div class="modal-action">
                                            <form method="POST" action="{{ route('applicants.destroy', $applicant) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-error">🗑️ Delete</button>
                                            </form>
                                            <button class="btn btn-ghost" onclick="document.getElementById('delete-applicant-{{ $applicant->id }}').close()">Cancel</button>
                                        </div>
                                    </div>
                                    <form method="dialog" class="modal-backdrop">
                                        <button>close</button>
                                    </form>
                                </dialog>
                            </td>
                        </tr>
                        @endforeach
