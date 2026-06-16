@php
    $definitions = \App\Models\CustomFieldDefinition::where('agency_id', $model->agency_id)
        ->where('model_type', $modelType)
        ->orderBy('order')
        ->orderBy('name')
        ->get();

    $model->loadCustomFieldValues();
@endphp

@if($definitions->count())
    <div class="divider text-sm opacity-50">CUSTOM FIELDS</div>

    <div class="overflow-x-auto">
        <table class="table">
            <tbody>
                @foreach($definitions as $definition)
                    @php
                        $value = $model->getCustomField($definition->key);
                    @endphp
                    @if($value !== null && $value !== '' && $value !== false)
                        <tr>
                            <td class="font-medium opacity-60">{{ $definition->name }}</td>
                            <td class="text-right">
                                @switch($definition->type)
                                    @case('checkbox')
                                        <span class="badge badge-sm {{ $value === '1' ? 'badge-success' : 'badge-ghost' }}">
                                            {{ $value === '1' ? 'Yes' : 'No' }}
                                        </span>
                                        @break
                                    @case('url')
                                        <a href="{{ $value }}" target="_blank" rel="noopener noreferrer" class="link link-primary text-sm">
                                            {{ $value }}
                                        </a>
                                        @break
                                    @case('date')
                                        {{ \Carbon\Carbon::parse($value)->format('M d, Y') }}
                                        @break
                                    @default
                                        {{ $value }}
                                @endswitch
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
@endif
