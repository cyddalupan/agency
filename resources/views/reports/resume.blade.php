<!DOCTYPE html>
<html lang="{{ $locale ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <title>{{ $applicant->full_name }} — {{ __('resume.title', [], 'en') }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', 'Noto Naskh Arabic', 'WenQuanYi Zen Hei', Arial, sans-serif;
            font-size: 9.5pt;
            color: #1f2937;
            margin: 0;
        }
        .page { width: 210mm; min-height: 270mm; padding: 9mm 11mm 10mm; }

        /* Letterhead */
        .letterhead {
            text-align: center;
            border-bottom: 3px double #1a365d;
            padding-bottom: 3mm;
            margin-bottom: 4mm;
        }
        .letterhead img { max-height: 20mm; max-width: 100%; }
        .letterhead .agency-name { font-size: 15pt; font-weight: 700; color: #1a365d; line-height: 1.15; }
        .letterhead .agency-sub { font-size: 8pt; color: #6b7280; margin-top: 0.5mm; }

        .doc-title {
            text-align: center;
            font-size: 12pt;
            font-weight: 700;
            letter-spacing: 2px;
            color: #1a365d;
            text-transform: uppercase;
            margin: 1mm 0 4mm;
        }

        /* Bio-data layout */
        table.bio { width: 100%; border-collapse: collapse; }
        table.bio > tbody > tr > td { vertical-align: top; }

        td.photo-cell { width: 42mm; }
        .photo-frame {
            width: 38mm; height: 45mm;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            text-align: center;
        }
        .photo-frame img { width: 38mm; height: 45mm; object-fit: cover; }
        .photo-frame .no-photo { color: #94a3b8; font-size: 7.5pt; padding-top: 19mm; display: block; }

        .body-photo-wrap { margin-top: 3mm; text-align: center; }
        .body-photo-wrap img { width: 38mm; border: 1px solid #cbd5e1; }

        td.fields-cell { padding-left: 5mm; }

        table.fields { width: 100%; border-collapse: collapse; }
        table.fields td { padding: 1.4mm 2mm; border-bottom: 1px solid #eef2f7; vertical-align: top; }
        table.fields td.lbl {
            width: 42mm;
            color: #1a365d;
            font-weight: 700;
            white-space: nowrap;
        }
        table.fields td.lbl .tr {
            display: block;
            color: #64748b;
            font-weight: 400;
            font-size: 8.5pt;
        }
        table.fields td.val { color: #111827; }

        .sec-title {
            font-size: 9.5pt;
            font-weight: 700;
            color: #1a365d;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #1a365d;
            padding-bottom: 0.8mm;
            margin: 5mm 0 2mm;
        }

        table.grid { width: 100%; border-collapse: collapse; margin-bottom: 2mm; }
        table.grid th {
            background: #1a365d;
            color: #fff;
            font-size: 8.5pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1.6mm 2mm;
            text-align: left;
        }
        table.grid th .tr { display: block; font-weight: 400; opacity: .85; text-transform: none; }
        table.grid td { padding: 1.5mm 2mm; border: 1px solid #dbe2ea; font-size: 9pt; }

        table.chk { width: 100%; border-collapse: collapse; }
        table.chk td { padding: 1.4mm 2mm; border: 1px solid #dbe2ea; font-size: 9pt; width: 50%; }
        table.chk td .yn { font-weight: 700; color: #1a365d; }

        .badge {
            display: inline-block;
            border: 1px solid #cbd5e1;
            background: #f1f5f9;
            padding: 0.4mm 2mm;
            margin: 0 1mm 1mm 0;
            font-size: 8.5pt;
        }

        .footer {
            margin-top: 6mm;
            padding-top: 2mm;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 7.5pt;
            color: #94a3b8;
        }
    </style>
</head>
<body>
@php
    $locale = $locale ?? app()->getLocale();

    // Bilingual label: English first, translated label beneath (matches sample).
    $label = function (string $key) use ($locale) {
        $en = e(__('resume.' . $key, [], 'en'));
        $out = $en;
        if ($locale && $locale !== 'en') {
            $tr = __('resume.' . $key, [], $locale);
            if ($tr && $tr !== __('resume.' . $key, [], 'en')) {
                $out .= '<span class="tr">' . e($tr) . '</span>';
            }
        }
        return $out;
    };

    // Engine-agnostic <img> from a storage/app/public relative path (base64).
    $img = function (?string $relative, string $class = '') {
        if (! $relative) {
            return '';
        }
        $path = storage_path('app/public/' . ltrim($relative, '/'));
        if (! is_file($path)) {
            return '';
        }
        $mime = @mime_content_type($path) ?: 'image/png';
        return '<img class="' . e($class) . '" src="data:' . $mime . ';base64,' . base64_encode(file_get_contents($path)) . '">';
    };

    $eduLevels = [
        'high_school' => 'High School',
        'vocational'  => 'Vocational / Associate',
        'bachelor'    => "Bachelor's Degree",
        'master'      => "Master's Degree",
    ];

    $gender = $applicant->gender ? ucfirst(strtolower($applicant->gender)) : null;
    $passport = $applicant->has_passport === 'with' ? __('resume.yes', [], 'en')
        : ($applicant->has_passport === 'without' ? __('resume.no', [], 'en') : null);

    $rows = [];
    $rows[] = ['name', $applicant->full_name];
    $rows[] = ['position', $applicant->position?->name];
    $rows[] = ['salary', $applicant->expected_salary];
    $rows[] = ['passport', $passport];
    $rows[] = ['contact', $applicant->contact];
    $rows[] = ['address', $applicant->address];
    $rows[] = ['nationality', $applicant->nationality?->name];
    $rows[] = ['date_of_birth', $applicant->birthdate ? \Illuminate\Support\Carbon::parse($applicant->birthdate)->format('d M Y') : null];
    $rows[] = ['gender', $gender];
    $rows[] = ['age', $applicant->age];
    $rows[] = ['religion', $applicant->religion?->name];
    $rows[] = ['marital_status', $applicant->civilStatus?->label];
    $rows[] = ['education', $applicant->education_level ? ($eduLevels[$applicant->education_level] ?? ucfirst(str_replace('_', ' ', $applicant->education_level))) : null];
    $rows = array_values(array_filter($rows, fn ($r) => ! is_null($r[1]) && $r[1] !== ''));

    $languages = $applicant->relationLoaded('languages') ? $applicant->languages : collect();
@endphp

<div class="page">

    {{-- ── LETTERHEAD ── --}}
    <div class="letterhead">
        @if ($agency && $agency->logo && ($logoImg = $img($agency->logo)))
            {!! $logoImg !!}
        @else
            <div class="agency-name">{{ $agency?->name ?? config('app.name') }}</div>
            @if ($agency?->city)
                <div class="agency-sub">{{ $agency->city }}</div>
            @endif
        @endif
    </div>

    <div class="doc-title">{!! $label('title') !!}</div>

    {{-- ── IDENTITY + PERSONAL FIELDS ── --}}
    <table class="bio">
        <tr>
            <td class="photo-cell">
                <div class="photo-frame">
                    @if ($applicant->photo && ($photoImg = $img($applicant->photo)))
                        {!! $photoImg !!}
                    @else
                        <span class="no-photo">{{ __('resume.photo', [], 'en') }}</span>
                    @endif
                </div>
                @if ($applicant->full_body_photo && ($bodyImg = $img($applicant->full_body_photo)))
                    <div class="body-photo-wrap">{!! $bodyImg !!}</div>
                @endif
            </td>
            <td class="fields-cell">
                <table class="fields">
                    @foreach ($rows as [$key, $value])
                        <tr>
                            <td class="lbl">{!! $label($key) !!}</td>
                            <td class="val">{{ $value }}</td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
    </table>

    {{-- ── POSITION / DESTINATION ── --}}
    <div class="sec-title">{!! $label('destination') !!}</div>
    <table class="grid">
        <tr>
            <th>{!! $label('position') !!}</th>
            <th>{!! $label('country') !!}</th>
            <th>{!! $label('duration') !!}</th>
        </tr>
        <tr>
            <td>{{ $applicant->position?->name ?? '—' }}</td>
            <td>{{ $applicant->country?->name ?? '—' }}</td>
            <td>{{ $applicant->remarks ?: '—' }}</td>
        </tr>
    </table>

    {{-- ── LANGUAGES ── --}}
    @if ($languages->count())
        <div class="sec-title">{!! $label('languages') !!}</div>
        <table class="chk">
            <tr>
                @foreach ($languages as $i => $lang)
                    <td>{{ $lang->name }} <span class="yn">— {{ __('resume.yes', [], 'en') }}</span></td>
                    @if ($i % 2 === 1)</tr><tr>@endif
                @endforeach
                @if ($languages->count() % 2 === 1)<td></td>@endif
            </tr>
        </table>
    @endif

    {{-- ── SKILLS ── --}}
    @if ($applicant->skills->count())
        <div class="sec-title">{!! $label('skills') !!}</div>
        <div>
            @foreach ($applicant->skills as $skill)
                <span class="badge">{{ $skill->name }}</span>
            @endforeach
        </div>
    @endif

    {{-- ── WORK EXPERIENCE ── --}}
    @if ($applicant->workExperiences->count())
        <div class="sec-title">{{ __('resume.position', [], 'en') }} / Experience</div>
        <table class="grid">
            @foreach ($applicant->workExperiences as $exp)
                <tr>
                    <td>
                        <strong>{{ $exp->position_title ?? $exp->position }}</strong>
                        @if ($exp->company) — {{ $exp->company }} @endif
                    </td>
                </tr>
            @endforeach
        </table>
    @endif

    <div class="footer">
        {{ __('resume.generated_on', [], 'en') }} {{ now()->format('F d, Y') }}
        @if ($agency) &nbsp;•&nbsp; {{ $agency->name }} @endif
    </div>

</div>
</body>
</html>
