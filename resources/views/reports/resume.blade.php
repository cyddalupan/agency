<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>CV - {{ $applicant->full_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9pt; color: #333; line-height: 1.4; }

        .page { width: 210mm; }

        table.frame { width: 100%; border-collapse: collapse; }
        table.frame td { vertical-align: top; }

        /* ── LEFT COLUMN ── */
        td.left-col {
            width: 95mm;
            background-color: #f8f9fa;
            padding: 10mm 6mm 8mm;
            border-right: 1px solid #e0e0e0;
        }
        td.left-col .portrait {
            width: 55mm;
            height: 65mm;
            object-fit: cover;
            border: 2px solid #ddd;
            display: block;
            margin: 0 auto 4mm;
        }
        td.left-col h1 {
            font-size: 14pt;
            font-weight: 700;
            color: #1a365d;
            text-align: center;
            line-height: 1.2;
            margin-bottom: 1mm;
        }
        td.left-col .subtitle {
            font-size: 8pt;
            color: #6b7280;
            text-align: center;
            margin-bottom: 4mm;
        }
        .sb-line {
            width: 30mm;
            height: 1px;
            background: #d1d5db;
            margin: 2mm auto;
        }
        td.left-col .contact-row {
            font-size: 7.5pt;
            color: #555;
            text-align: center;
            margin-bottom: 0.5mm;
            word-break: break-word;
        }

        td.left-col .sb-title {
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #1a365d;
            margin: 3mm 0 1.5mm;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 1mm;
        }
        td.left-col .sb-item {
            font-size: 7.5pt;
            color: #555;
            margin: 0 0 1mm 0;
            line-height: 1.3;
        }
        td.left-col .sb-item strong { color: #333; }

        td.left-col .skill-badge {
            display: inline-block;
            background: #e5e7eb;
            color: #333;
            padding: 0.5mm 2mm;
            border: 1px solid #d1d5db;
            font-size: 7pt;
            margin: 0.3mm 0.8mm 0.3mm 0;
        }

        td.left-col .body-photo-wrap {
            text-align: center;
            margin-top: 4mm;
        }
        td.left-col .body-photo-wrap img {
            max-width: 100%;
            max-height: 160mm;
            object-fit: contain;
            border: 1px solid #ddd;
        }

        /* ── RIGHT COLUMN ── */
        td.right-col {
            padding: 10mm 8mm 8mm;
        }
        .section { margin-bottom: 4mm; }
        .section-title {
            font-size: 10pt;
            font-weight: 700;
            color: #1a365d;
            border-bottom: 2px solid #1a365d;
            padding-bottom: 0.8mm;
            margin-bottom: 2mm;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .entry { margin-bottom: 3mm; }
        .entry-title { font-weight: 700; font-size: 9.5pt; color: #1a365d; }
        .entry-sub { font-size: 8.5pt; color: #2563eb; }
        .entry-date { font-size: 7.5pt; color: #888; margin-bottom: 0.3mm; }
        .entry-desc { font-size: 8pt; color: #555; margin-top: 0.5mm; padding-left: 2mm; border-left: 1.5px solid #d1d5db; }

        .footer {
            text-align: center;
            font-size: 6.5pt;
            color: #bbb;
            margin-top: 6mm;
            border-top: 1px solid #eee;
            padding-top: 2mm;
        }
    </style>
</head>
<body>
    <div class="page">

        <table class="frame" cellpadding="0" cellspacing="0">
        <tr>

        {{-- ── LEFT COLUMN ── --}}
        <td class="left-col">

            @if ($applicant->photo)
                <img src="{{ storage_path('app/public/' . $applicant->photo) }}" alt="Photo" class="portrait">
            @endif

            <h1>{{ strtoupper($applicant->last_name) }}<br>{{ ucfirst($applicant->first_name) }}{{ $applicant->middle_name ? ' ' . strtoupper(substr($applicant->middle_name,0,1)) . '.' : '' }}</h1>

            <div class="subtitle">
                @if ($applicant->position)
                    {{ $applicant->position->name }}
                @else
                    Applicant
                @endif
            </div>

            <div class="sb-line"></div>

            {{-- Contact --}}
            <div class="sb-title">Contact</div>
            @if ($applicant->contact)
                <div class="contact-row">{{ $applicant->contact }}</div>
            @endif
            @if ($applicant->email)
                <div class="contact-row">{{ $applicant->email }}</div>
            @endif
            @if ($applicant->address)
                <div class="contact-row">{{ $applicant->address }}</div>
            @endif

            {{-- Personal Details --}}
            <div class="sb-title">Details</div>
            <div class="sb-item"><strong>Gender:</strong> {{ ucfirst($applicant->gender ?? '—') }}</div>
            <div class="sb-item"><strong>Age:</strong> {{ $applicant->age ?? '—' }}</div>
            <div class="sb-item"><strong>Birthdate:</strong> {{ $applicant->birthdate ? date('M d, Y', strtotime($applicant->birthdate)) : '—' }}</div>
            <div class="sb-item"><strong>Branch:</strong> {{ $applicant->branch ?? '—' }}</div>
            @if($applicant->agent)
            <div class="sb-item"><strong>Agent:</strong> {{ $applicant->agent->name }}</div>
            @endif
            <div class="sb-item"><strong>FRA:</strong>
                @switch($applicant->fra)
                    @case('for_fra') For FRA @break
                    @case('fra_completed') FRA Completed @break
                    @case('none') No FRA @break
                    @default —
                @endswitch
            </div>
            <div class="sb-item"><strong>Status:</strong> {{ $applicant->statusCode?->label ?? $applicant->status ?? '—' }}</div>
            <div class="sb-item"><strong>Firstimer/Ex-Abroad:</strong>
                @switch($applicant->firstimer_type)
                    @case('firstimer') Firstimer @break
                    @case('ex-abroad') Ex-Abroad @break
                    @default —
                @endswitch
            </div>
            <div class="sb-item"><strong>Encoder:</strong> {{ $applicant->encoder ?? '—' }}</div>
            <div class="sb-item"><strong>Civil Status:</strong> {{ $applicant->civilStatus?->label ?? '—' }}</div>
            <div class="sb-item"><strong>Nationality:</strong> {{ $applicant->nationality?->name ?? '—' }}</div>
            @if($applicant->religion)
            <div class="sb-item"><strong>Religion:</strong> {{ $applicant->religion->name }}</div>
            @endif
            <div class="sb-item"><strong>Passport:</strong> {{ $applicant->has_passport === 'with' ? 'Yes' : ($applicant->has_passport === 'without' ? 'No' : '—') }}</div>

            @if($applicant->country)
            <div class="sb-title">Destination</div>
            <div class="sb-item">{{ $applicant->country->name }}</div>
            @endif

            {{-- Skills --}}
            @if($applicant->skills->count())
            <div class="sb-title">Skills</div>
            <div>
                @foreach($applicant->skills as $skill)
                    <span class="skill-badge">{{ $skill->name }}</span>
                @endforeach
            </div>
            @endif

            {{-- Full Body Photo --}}
            @if($applicant->full_body_photo)
            <div class="body-photo-wrap">
                <img src="{{ storage_path('app/public/' . $applicant->full_body_photo) }}" alt="Full body">
            </div>
            @endif

        </td>

        {{-- ── RIGHT COLUMN ── --}}
        <td class="right-col">

            {{-- Work Experience --}}
            @if($applicant->workExperiences->count())
            <div class="section">
                <div class="section-title">Work Experience</div>
                @foreach($applicant->workExperiences as $exp)
                <div class="entry">
                    <div class="entry-title">{{ $exp->position_title ?? $exp->position }}</div>
                    <div class="entry-sub">{{ $exp->company }}</div>
                    <div class="entry-date">
                        {{ $exp->from_date ?? $exp->date_from ? \Carbon\Carbon::parse($exp->from_date ?? $exp->date_from)->format('M Y') : '' }}
                        —
                        {{ $exp->to_date ?? $exp->date_to ? \Carbon\Carbon::parse($exp->to_date ?? $exp->date_to)->format('M Y') : 'Present' }}
                    </div>
                    @if($exp->description)
                    <div class="entry-desc">{{ $exp->description }}</div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            {{-- Education --}}
            @if($applicant->education->count())
            <div class="section">
                <div class="section-title">Education</div>
                @foreach($applicant->education as $edu)
                <div class="entry">
                    <div class="entry-title">{{ $edu->degree ?? $edu->course }}</div>
                    <div class="entry-sub">{{ $edu->school }}</div>
                    <div class="entry-date">
                        {{ $edu->year_start ?? '' }}
                        @if($edu->year_start && $edu->year_end) — @endif
                        {{ $edu->year_end ?? $edu->year_graduated ?? '' }}
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Certificates --}}
            @if($applicant->certificates->count())
            <div class="section">
                <div class="section-title">Certificates</div>
                @foreach($applicant->certificates as $cert)
                <div class="entry">
                    <div class="entry-title">{{ $cert->name ?? $cert->certificate_name ?? $cert->certificate_no }}</div>
                    <div class="entry-sub">{{ $cert->issued_by ?? $cert->institution ?? '' }}</div>
                    @if($cert->issued_date ?? $cert->issue_date)
                    <div class="entry-date">{{ \Carbon\Carbon::parse($cert->issued_date ?? $cert->issue_date)->format('M d, Y') }}</div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            {{-- References --}}
            @if($applicant->references->count())
            <div class="section">
                <div class="section-title">References</div>
                @foreach($applicant->references as $ref)
                <div class="entry">
                    <div class="entry-title">{{ $ref->name }}</div>
                    <div class="entry-sub">{{ $ref->position }}{{ $ref->company ? ' — '.$ref->company : '' }}</div>
                    @if($ref->contact)
                    <div class="entry-date">{{ $ref->contact }}</div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            <div class="footer">
                CV generated on {{ now()->format('F d, Y') }}
            </div>

        </td>

        </tr>
        </table>

    </div>
</body>
</html>
