<!DOCTYPE html>
<html lang="{{ $locale ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <title>{{ $applicant->full_name }} — {{ __('resume.title', [], 'en') }}</title>
    <style>
        /* ─────────────────────────────────────────────────────────────────
         * Bilingual "bio-data" resume — design copied from the agency
         * sample: full-width letterhead, gold section bars, 3-column
         * EN-label / value / AR-label field rows, black-bordered tables,
         * worker photo on the right, + page 2 worker summary.
         *  (Mjolnir "LANDAS: Resume" 2026-10-10)
         * ───────────────────────────────────────────────────────────────*/
        @page { size: A4; margin: 3mm 14mm 6mm; }
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9pt;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .page { width: auto; }
        .page.break { page-break-before: always; }

        /* ── Letterhead ── */
        .letterhead { text-align: center; margin-bottom: 1mm; }
        .letterhead img { max-height: 30mm; max-width: 100%; }
        .letterhead .agency-name { font-size: 16pt; font-weight: 700; color: #b33c00; }
        .letterhead .agency-sub { font-size: 8.5pt; color: #333; }

        /* ── Gold blocks ── */
        .goldbar { background: #daa520; color: #000; font-weight: 700; }
        .goldbar .en { display: block; line-height: 1.15; }
        .goldbar .ar { display: block; font-size: 8pt; font-weight: 400; line-height: 1.15; direction: rtl; }
        .goldbar.center { text-align: center; text-transform: uppercase; letter-spacing: .5px; padding: 0.6mm 2mm; }

        /* ── Header (FULL NAME / POSITION) ── */
        table.head { width: 100%; border-collapse: collapse; margin-bottom: 2mm; table-layout: fixed; }
        table.head td { vertical-align: top; }
        table.head td.c1 { width: 99mm; }
        .headbar { display: inline-block; vertical-align: top; padding: 1mm 2mm; text-align: center; }
        .headval { display: inline-block; vertical-align: top; padding: 1mm 0 0 3mm; font-size: 11pt; font-weight: 700; }
        .headval.small { font-size: 9.5pt; }

        /* ── Main two-column body: fields (left) + photo (right) ── */
        table.body2 { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.body2 > tbody > tr > td { vertical-align: top; }
        td.fields-cell { width: 112mm; }
        td.photo-cell { width: 70mm; text-align: center; }
        .photo-frame { width: 62mm; height: 110mm; border: 1px solid #000; overflow: hidden; margin: 0 auto; }
        .photo-frame img { width: 62mm; height: 110mm; object-fit: cover; }
        .photo-frame .no-photo { display: block; color: #999; font-size: 8pt; padding-top: 50mm; }

        /* ── Field rows: EN label | value | AR label ── */
        table.fields { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.fields td { padding: 0.6mm 1mm; vertical-align: top; font-size: 9pt; }
        table.fields td.lbl { width: 34mm; font-weight: 700; }
        table.fields td.val { width: 56mm; }
        table.fields td.ar { width: 22mm; text-align: right; direction: rtl; font-weight: 700; }

        /* ── Gold section header bar ── */
        .secbar {
            background: #daa520; color: #000; font-weight: 700;
            text-transform: uppercase; letter-spacing: .3px; font-size: 9pt;
            padding: 0.8mm 2mm; margin: 2.5mm 0 1.2mm;
        }
        .secbar .ar { font-weight: 400; text-transform: none; direction: rtl; float: right; }

        /* ── Data tables ── */
        table.grid { width: 100%; border-collapse: collapse; margin-bottom: 1mm; }
        table.grid th {
            background: #f3e2b8; border: 1px solid #000; font-size: 8pt;
            text-transform: uppercase; padding: 0.8mm 2mm; text-align: left;
        }
        table.grid th .ar { display: block; font-weight: 400; text-transform: none; direction: rtl; text-align: right; }
        table.grid td { border: 1px solid #000; padding: 0.8mm 2mm; font-size: 9pt; }

        /* ── Skills / language check grid ── */
        table.chk { width: 100%; border-collapse: collapse; }
        table.chk td { border: 1px solid #000; padding: 0.8mm 2mm; font-size: 8.5pt; }
        table.chk td.k { font-weight: 700; width: 24%; }
        table.chk td.v { text-align: center; width: 26%; }
        table.chk td.a { direction: rtl; text-align: right; width: 24%; font-size: 8pt; }

        .summary td { font-size: 9.5pt; }
        .summary .k { width: 55mm; font-weight: 700; }
        .summary .v { }
        .passport-scan { text-align: center; margin-top: 4mm; }
        .passport-scan img { max-width: 90mm; border: 1px solid #000; }

        .footer { margin-top: 4mm; border-top: 1px solid #ddd; padding-top: 1.5mm; text-align: center; font-size: 7.5pt; color: #888; }
    </style>
</head>
<body>
@php
    $locale = $locale ?? app()->getLocale();
    $agency = $agency ?? null;

    $tr = function (string $key) use ($locale) {
        $args = func_get_args();
        array_shift($args);
        $out = __($key, [], $locale);
        if ($args) {
            $params = [];
            foreach ($args as $i => $v) { $params[':' . ($i + 1)] = $v; }
            $out = strtr($out, $params);
        }
        return e($out);
    };

    // <img> from a storage/app/public relative path (base64, engine-agnostic).
    $img = function (?string $relative) {
        if (! $relative) { return ''; }
        $path = storage_path('app/public/' . ltrim($relative, '/'));
        if (! is_file($path)) { return ''; }
        $mime = @mime_content_type($path) ?: 'image/png';
        return '<img src="data:' . $mime . ';base64,' . base64_encode(file_get_contents($path)) . '">';
    };

    $eduLevels = [
        'high_school'   => 'HIGH SCHOOL',
        'vocational'    => 'VOCATIONAL',
        'bachelor'      => 'COLLEGE GRADUATE',
        'master'        => "MASTER'S DEGREE",
        'college'       => 'COLLEGE UNDERGRADUATE',
        'elementary'    => 'ELEMENTARY',
    ];

    $ynYes = __('resume.yes', [], 'en');

    $rows = [];
    $rows[] = ['monthly_salary', $applicant->expected_salary !== null
        ? rtrim(rtrim(number_format((float) $applicant->expected_salary, 2), '0'), '.') . ' USD' : null];
    $rows[] = ['mobile', $applicant->contact];
    $rows[] = ['contract_period', $applicant->contract];
    $rows[] = ['passport', optional($applicant->passport)->passport_no
        ?? optional($applicant->passport)->passport_number];
    $rows = array_values(array_filter($rows, fn ($r) => $r[1] !== null && $r[1] !== ''));

    $nok = $applicant->relationLoaded('emergencyContacts')
        ? $applicant->emergencyContacts->first() : null;

    $personal = [];
    $personal[] = ['nationality', $applicant->nationality?->name];
    $personal[] = ['address', $applicant->address];
    $personal[] = ['date_of_birth', $applicant->birthdate
        ? \Illuminate\Support\Carbon::parse($applicant->birthdate)->format('d M Y') : null];
    $personal[] = ['age', $applicant->birthdate ? \Illuminate\Support\Carbon::parse($applicant->birthdate)->age : null];
    $personal[] = ['religion', $applicant->religion?->name];
    $personal[] = ['marital_status', $applicant->civilStatus?->label ?? $applicant->civilStatus?->name];
    $personal[] = ['number_of_children', $applicant->number_of_children ?? null];
    $personal[] = ['education', $applicant->education_level
        ? ($eduLevels[$applicant->education_level] ?? strtoupper(str_replace('_', ' ', $applicant->education_level))) : null];
    $personal = array_values(array_filter($personal, fn ($r) => $r[1] !== null && $r[1] !== ''));

    $languages = $applicant->relationLoaded('languages') ? $applicant->languages : collect();
    $skills    = $applicant->relationLoaded('skills') ? $applicant->skills : collect();
    $work      = $applicant->relationLoaded('workExperiences') ? $applicant->workExperiences : collect();
@endphp

{{-- ══════════════════════════ PAGE 1 ══════════════════════════ --}}
<div class="page">

    <div class="letterhead">
        @if ($agency && $agency->logo && ($logoImg = $img($agency->logo)))
            {!! $logoImg !!}
        @elseif ($agency)
            <div class="agency-name">{{ $agency->name }}</div>
            @if ($agency->city) <div class="agency-sub">{{ $agency->city }}</div> @endif
        @endif
    </div>

    <table class="head">
        <tr>
            <td class="c1">
                <span class="headbar goldbar">
                    <span class="en">{{ $tr('resume.full_name') }}</span>
                    <span class="ar" dir="rtl">{{ $tr('resume.full_name_ar') }}</span>
                </span>
                <span class="headval">{{ $applicant->full_name }}</span>
            </td>
            <td>
                <span class="headbar goldbar">
                    <span class="en">{{ $tr('resume.position') }}</span>
                    <span class="ar" dir="rtl">{{ $tr('resume.position_ar') }}</span>
                </span>
                <span class="headval small">{{ $applicant->position?->name ?? '—' }}</span>
            </td>
        </tr>
    </table>

    <table class="body2">
        <tr>
            <td class="fields-cell">
                <table class="fields">
                    @foreach ($rows as [$key, $value])
                        <tr>
                            <td class="lbl">{{ $tr('resume.' . $key) }}</td>
                            <td class="val">{{ $value }}</td>
                            <td class="ar" dir="rtl">{{ $tr('resume.ar.' . $key) }}</td>
                        </tr>
                    @endforeach

                    @if ($nok)
                        <tr><td colspan="3"><div class="secbar center">{{ $tr('resume.next_of_kin') }}</div></td></tr>
                        <tr>
                            <td class="lbl">{{ $tr('resume.nok_name') }}</td>
                            <td class="val">{{ $nok->name }}</td>
                            <td class="ar" dir="rtl">{{ $tr('resume.ar.name') }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">{{ $tr('resume.nok_contact') }}</td>
                            <td class="val">{{ $nok->contact }}</td>
                            <td class="ar" dir="rtl">{{ $tr('resume.ar.contact') }}</td>
                        </tr>
                    @endif

                    @if (count($personal))
                        <tr><td colspan="3"><div class="secbar center">{{ $tr('resume.personal_details') }}</div></td></tr>
                        @foreach ($personal as [$key, $value])
                            <tr>
                                <td class="lbl">{{ $tr('resume.' . $key) }}</td>
                                <td class="val">{{ $value }}</td>
                                <td class="ar" dir="rtl">{{ $tr('resume.ar.' . $key) }}</td>
                            </tr>
                        @endforeach
                    @endif
                </table>
            </td>
            <td class="photo-cell">
                <div class="photo-frame">
                    @if ($applicant->photo && ($photoImg = $img($applicant->photo)))
                        {!! $photoImg !!}
                    @else
                        <span class="no-photo">{{ __('resume.photo', [], 'en') }}</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <div class="secbar">{{ $tr('resume.prev_employment') }}</div>
    <table class="grid">
        <tr>
            <th>{{ $tr('resume.position') }}</th>
            <th>{{ $tr('resume.country') }}</th>
            <th>{{ $tr('resume.duration') }}</th>
        </tr>
        @forelse ($work as $exp)
            <tr>
                <td>{{ $exp->position ?? '—' }}</td>
                <td>{{ $exp->company ?? '—' }}</td>
                <td>{{ ($exp->date_from || $exp->date_to) ? trim(($exp->date_from ?: '') . ' - ' . ($exp->date_to ?: ''), ' -') : '—' }}</td>
            </tr>
        @empty
            <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
        @endforelse
    </table>

    <div class="secbar">{{ $tr('resume.knowledge_languages') }}</div>
    <table class="grid">
        <tr>
            <th>{{ $tr('resume.ar.language') }}</th>
            <th style="width:20mm" class="">{{ __('resume.proficiency', [], 'en') }}</th>
        </tr>
        @forelse ($languages as $lang)
            <tr>
                <td>{{ strtoupper($lang->name) }}</td>
                <td>{{ strtoupper($lang->proficiency ?: $ynYes) }}</td>
            </tr>
        @empty
            <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
        @endforelse
    </table>

    <div class="secbar">{{ $tr('resume.skills_experience') }}</div>
    <table class="chk">
        @forelse ($skills->chunk(2) as $pair)
            <tr>
                @foreach ($pair as $skill)
                    <td class="k">{{ strtoupper($skill->skill_name) }}</td>
                    <td class="v">{{ strtoupper($skill->proficiency ?: $ynYes) }}</td>
                    <td class="a" dir="rtl">{{ $tr('resume.ar.skill') }}</td>
                @endforeach
                @if ($pair->count() === 1)
                    <td class="k"></td><td class="v"></td><td class="a"></td>
                @endif
            </tr>
        @empty
            <tr><td class="k">&nbsp;</td><td class="v">&nbsp;</td><td class="a">&nbsp;</td>
                <td class="k">&nbsp;</td><td class="v">&nbsp;</td><td class="a">&nbsp;</td></tr>
        @endforelse
    </table>

    @if ($applicant->relationLoaded('education') && $applicant->education->count())
        <div class="secbar">{{ $tr('resume.education_history') }}</div>
        <table class="grid">
            <tr>
                <th>{{ $tr('resume.school') }}</th>
                <th>{{ $tr('resume.degree') }}</th>
                <th style="width:30mm">{{ $tr('resume.year') }}</th>
            </tr>
            @foreach ($applicant->education as $edu)
                <tr>
                    <td>{{ $edu->school }}</td>
                    <td>{{ $edu->degree ?: $edu->course }}</td>
                    <td>{{ trim(($edu->year_start ?: '') . ' - ' . ($edu->year_end ?: ($edu->year_graduated ?: '')), ' -') }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @if ($applicant->relationLoaded('certificates') && $applicant->certificates->count())
        <div class="secbar">{{ $tr('resume.certificates') }}</div>
        <table class="grid">
            <tr>
                <th>{{ $tr('resume.certificate') }}</th>
                <th>{{ $tr('resume.issued_by') }}</th>
                <th style="width:30mm">{{ $tr('resume.year') }}</th>
            </tr>
            @foreach ($applicant->certificates as $cert)
                <tr>
                    <td>{{ $cert->name ?: $cert->certificate_name }}</td>
                    <td>{{ $cert->issued_by ?: $cert->institution }}</td>
                    <td>{{ $cert->issued_date ? \Illuminate\Support\Carbon::parse($cert->issued_date)->format('Y') : ($cert->date_obtained ? \Illuminate\Support\Carbon::parse($cert->date_obtained)->format('Y') : '') }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @if ($applicant->relationLoaded('references') && $applicant->references->count())
        <div class="secbar">{{ $tr('resume.references') }}</div>
        <table class="grid">
            <tr>
                <th>{{ $tr('resume.name') }}</th>
                <th>{{ $tr('resume.contact') }}</th>
                <th>{{ $tr('resume.position') }}</th>
            </tr>
            @foreach ($applicant->references as $ref)
                <tr>
                    <td>{{ $ref->name }}</td>
                    <td>{{ $ref->contact }}</td>
                    <td>{{ $ref->position }}{{ $ref->company ? ' — ' . $ref->company : '' }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <div class="footer">
        {{ __('resume.generated_on', [], 'en') }} {{ now()->format('F d, Y') }}
        @if ($agency) &nbsp;•&nbsp; {{ $agency->name }} @endif
    </div>
</div>

{{-- ══════════════════════════ PAGE 2 ══════════════════════════ --}}
<div class="page break">
    <table class="grid summary">
        <tr><td class="k">{{ $tr('resume.name_of_worker') }}</td><td class="v">{{ strtoupper($applicant->full_name) }}</td></tr>
        <tr><td class="k">{{ $tr('resume.position') }}</td><td class="v">{{ strtoupper($applicant->position?->name ?? '') }}</td></tr>
        <tr><td class="k">{{ $tr('resume.address') }}</td><td class="v">{{ strtoupper($applicant->address ?? '') }}</td></tr>
        <tr><td class="k">{{ $tr('resume.marital_status') }}</td><td class="v">{{ strtoupper($applicant->civilStatus?->label ?? '') }}</td></tr>
        <tr><td class="k">{{ $tr('resume.have_kids') }}</td><td class="v">{{ $applicant->number_of_children ?: '' }}</td></tr>
        <tr><td class="k">{{ $tr('resume.contact') }}</td><td class="v">{{ $applicant->contact }}</td></tr>
        <tr><td class="k">{{ $tr('resume.passport') }}</td><td class="v">{{ optional($applicant->passport)->passport_no ?? optional($applicant->passport)->passport_number }}</td></tr>
        <tr><td class="k">{{ $tr('resume.date_of_issue') }}</td><td class="v">{{ optional($applicant->passport)->issue_date ? \Illuminate\Support\Carbon::parse($applicant->passport->issue_date)->format('M. d, Y') : '' }}</td></tr>
        <tr><td class="k">{{ $tr('resume.place_of_issue') }}</td><td class="v">{{ optional($applicant->passport)->place_of_issue }}</td></tr>
        <tr><td class="k">{{ $tr('resume.name_of_nok') }}</td><td class="v">{{ $nok->name ?? '' }}</td></tr>
        <tr><td class="k">{{ $tr('resume.contact_number_of_nok') }}</td><td class="v">{{ $nok->contact ?? '' }}</td></tr>
        <tr><td class="k">{{ $tr('resume.language_spoken') }}</td><td class="v">{{ $languages->pluck('name')->map(fn ($n) => strtoupper($n))->implode(', ') }}</td></tr>
        <tr><td class="k">{{ $tr('resume.religion') }}</td><td class="v">{{ strtoupper($applicant->religion?->name ?? '') }}</td></tr>
        <tr><td class="k">{{ $tr('resume.employment_status') }}</td><td class="v">{{ strtoupper($applicant->firstimer_type ?? '') }}</td></tr>
        <tr><td class="k">{{ $tr('resume.skills') }}</td><td class="v">{{ $skills->pluck('skill_name')->map(fn ($n) => strtoupper($n))->implode(', ') }}</td></tr>
    </table>

    @if ($applicant->passport && ($passImg = $img($applicant->passport->file_path)))
        <div class="passport-scan">{!! $passImg !!}</div>
    @endif

    <div class="footer">
        {{ __('resume.generated_on', [], 'en') }} {{ now()->format('F d, Y') }}
        @if ($agency) &nbsp;•&nbsp; {{ $agency->name }} @endif
    </div>
</div>
</body>
</html>
