                        @foreach($applicants as $applicant)
                        @php $contractRecord = $applicant->contractRecords->first(); @endphp
                        <tr class="hover transition-colors">
                            @foreach($tableColumns as $col)
                                @if($col === 'name')
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar">
                                            <div class="w-10 h-10 rounded-full bg-primary/20 text-primary flex items-center justify-center text-xs font-bold overflow-hidden">
                                                @if ($applicant->photo)
                                                    <img src="{{ $applicant->photo_url }}" class="w-full h-full object-cover" alt="{{ $applicant->full_name }}">
                                                @else
                                                    {{ strtoupper(substr($applicant->first_name, 0, 1)) }}{{ strtoupper(substr($applicant->last_name, 0, 1)) }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('applicants.show', $applicant) }}" class="link link-primary font-medium whitespace-nowrap">
                                                {{ $applicant->first_name }} {{ $applicant->last_name }}
                                            </a>
                                            @if($applicant->middle_name)
                                                <span class="opacity-40 text-sm block"> {{ $applicant->middle_name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                @elseif($col === 'contact')
                                <td class="text-sm">
                                    @if($applicant->contact)
                                        {{ $applicant->contact }}
                                    @elseif($applicant->email)
                                        {{ $applicant->email }}
                                    @else
                                        <span class="opacity-40">—</span>
                                    @endif
                                </td>
                                @elseif($col === 'gender')
                                <td class="text-sm">
                                    @if($applicant->gender)
                                        {{ ucfirst($applicant->gender) }}
                                    @else
                                        <span class="opacity-40">—</span>
                                    @endif
                                </td>
                                @elseif($col === 'age')
                                <td class="text-sm opacity-70">
                                    {{ $applicant->age ?? '—' }}
                                </td>
                                @elseif($col === 'branch')
                                <td class="text-sm">
                                    {{-- No branch = main office ("main branch"); show it instead of blank. (2026-10-16) --}}
                                    {{ $applicant->branch_label }}
                                </td>
                                @elseif($col === 'agent')
                                <td class="text-sm">
                                    {{ $applicant->agent?->name ?? '—' }}
                                </td>
                                @elseif($col === 'position')
                                <td class="text-sm">
                                    {{ $applicant->position?->name ?? '—' }}
                                </td>
                                @elseif($col === 'country')
                                <td class="text-sm">
                                    {{ $applicant->country?->name ?? '—' }}
                                </td>
                                @elseif($col === 'fra')
                                <td class="text-sm">
                                    @if($applicant->fra)
                                        {{ app_fra_options()[$applicant->fra] ?? ucfirst($applicant->fra) }}
                                    @else
                                        <span class="opacity-40">—</span>
                                    @endif
                                </td>
                                @elseif($col === 'status')
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
                                @elseif($col === 'date_applied')
                                <td class="text-sm whitespace-nowrap">{{ $applicant->created_at->format('M d, Y') }}</td>
                                @elseif($col === 'contract_signed')
                                <td class="text-sm">
                                    @if ($applicant->contract)
                                        <div class="flex flex-col gap-0.5">
                                            <a href="{{ Storage::url($applicant->contract) }}" target="_blank"
                                               class="link link-primary text-sm" title="View contract">{{ basename($applicant->contract) }}</a>
                                            @if ($contractRecord?->contract_signed)
                                                <span class="text-success font-medium">{{ $contractRecord->contract_signed->format('M d, Y') }}</span>
                                            @endif
                                        </div>
                                    @elseif ($contractRecord)
                                        <div class="flex flex-col gap-0.5">
                                            @if ($contractRecord->rfp)
                                                <span class="text-xs opacity-60">{{ $contractRecord->rfp }}</span>
                                            @endif
                                            @if ($contractRecord->contract_signed)
                                                <span class="text-success font-medium">{{ $contractRecord->contract_signed->format('M d, Y') }}</span>
                                            @else
                                                <span class="opacity-40">—</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="opacity-40">—</span>
                                    @endif
                                </td>
                                @elseif($col === 'contract_received')
                                <td class="text-sm">
                                    @php $contractReceived = $applicant->contract_received_date ?? $contractRecord?->contract_received; @endphp
                                    @if ($contractReceived)
                                        <span class="text-success font-medium">{{ $contractReceived->format('M d, Y') }}</span>
                                    @else
                                        <span class="opacity-40">—</span>
                                    @endif
                                </td>
                                @elseif($col === 'encoder')
                                <td class="text-sm">{{ $applicant->encoder ?? $applicant->creator?->name ?? '—' }}</td>
                                @endif
                            @endforeach
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
