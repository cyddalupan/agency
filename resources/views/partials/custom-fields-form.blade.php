@php
    $definitions = \App\Models\CustomFieldDefinition::where('agency_id', auth()->user()->agency_id)
        ->where('model_type', $modelType)
        ->orderBy('order')
        ->orderBy('name')
        ->get();
@endphp

@if($definitions->count())
    <div class="divider text-sm opacity-50">CUSTOM FIELDS</div>

    @foreach($definitions as $definition)
        @php
            $oldValue = old($definition->key);
            $currentValue = $oldValue !== null ? $oldValue : ($model ? $model->getCustomField($definition->key) : null);
            $requiredAttr = $definition->required ? 'required' : '';
            $requiredStar = $definition->required ? ' <span class="text-error">*</span>' : '';
        @endphp

        @switch($definition->type)
            @case('textarea')
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{!! $definition->name !!}{!! $requiredStar !!}</legend>
                    <textarea name="{{ $definition->key }}" {{ $requiredAttr }}
                        class="textarea w-full" rows="3"
                        placeholder="{{ $definition->name }}">{{ $currentValue }}</textarea>
                    @error($definition->key) <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
                </fieldset>
                @break

            @case('number')
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{!! $definition->name !!}{!! $requiredStar !!}</legend>
                    <input type="number" name="{{ $definition->key }}" value="{{ $currentValue }}"
                        step="any" {{ $requiredAttr }}
                        class="input w-full" placeholder="{{ $definition->name }}">
                    @error($definition->key) <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
                </fieldset>
                @break

            @case('date')
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{!! $definition->name !!}{!! $requiredStar !!}</legend>
                    <input type="date" name="{{ $definition->key }}" value="{{ $currentValue }}"
                        {{ $requiredAttr }} class="input w-full">
                    @error($definition->key) <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
                </fieldset>
                @break

            @case('select')
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{!! $definition->name !!}{!! $requiredStar !!}</legend>
                    <select name="{{ $definition->key }}" {{ $requiredAttr }} class="select w-full">
                        <option value="">Select {{ $definition->name }}...</option>
                        @foreach($definition->options ?? [] as $option)
                            <option value="{{ $option }}" @selected($currentValue === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                    @error($definition->key) <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
                </fieldset>
                @break

            @case('checkbox')
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{!! $definition->name !!}</legend>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="{{ $definition->key }}" value="0">
                        <input type="checkbox" name="{{ $definition->key }}" value="1"
                            class="checkbox checkbox-sm" @checked($currentValue === '1' || $currentValue === true)>
                        <span class="text-sm">{{ $definition->name }}</span>
                    </label>
                    @error($definition->key) <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
                </fieldset>
                @break

            @case('url')
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{!! $definition->name !!}{!! $requiredStar !!}</legend>
                    <input type="url" name="{{ $definition->key }}" value="{{ $currentValue }}"
                        {{ $requiredAttr }} class="input w-full"
                        placeholder="https://example.com">
                    @error($definition->key) <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
                </fieldset>
                @break

            @default
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{!! $definition->name !!}{!! $requiredStar !!}</legend>
                    <input type="text" name="{{ $definition->key }}" value="{{ $currentValue }}"
                        {{ $requiredAttr }} class="input w-full"
                        placeholder="{{ $definition->name }}">
                    @error($definition->key) <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
                </fieldset>
        @endswitch
    @endforeach
@endif
