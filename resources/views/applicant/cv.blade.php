<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ strtoupper($applicant->last_name) }}_{{ strtoupper($applicant->first_name) }}_CV</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11pt; color: #333; line-height: 1.5; }
        .page { width: 210mm; min-height: 297mm; padding: 15mm 12mm; position: relative; }

        /* Header with photo */
        .header { display: flex; gap: 15px; align-items: flex-start; margin-bottom: 15px; padding-bottom: 12px; border-bottom: 3px solid #2563eb; }
        .header .photo-area { flex-shrink: 0; width: 120px; }
        .header .photo-area img { width: 120px; height: auto; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
        .header .info { flex: 1; }
        .header .info h1 { font-size: 22pt; color: #1e3a5f; margin-bottom: 4px; }
        .header .info .subtitle { font-size: 10pt; color: #666; margin-bottom: 6px; }
        .header .info .contact-line { font-size: 9pt; color: #888; }

        /* Section titles */
        .section { margin-bottom: 12px; }
        .section-title { font-size: 13pt; font-weight: bold; color: #2563eb; border-bottom: 1px solid #ddd; padding-bottom: 3px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; }

        /* Two-column layout */
        .row { display: flex; gap: 20px; }
        .col-left { width: 65%; }
        .col-right { width: 35%; }

        /* Entry items */
        .entry { margin-bottom: 8px; }
        .entry-title { font-weight: bold; font-size: 11pt; color: #1e3a5f; }
        .entry-sub { font-size: 10pt; color: #2563eb; }
        .entry-date { font-size: 9pt; color: #999; }
        .entry-desc { font-size: 10pt; color: #555; margin-top: 2px; padding-left: 10px; border-left: 2px solid #e5e7eb; }

        /* Personal details */
        .detail-row { display: flex; justify-content: space-between; padding: 2px 0; font-size: 10pt; border-bottom: 1px dotted #f0f0f0; }
        .detail-label { color: #888; width: 45%; }
        .detail-value { color: #333; width: 55%; text-align: right; }

        /* Skills */
        .skill-tag { display: inline-block; background: #e0e7ff; color: #1e3a5f; padding: 2px 8px; border-radius: 3px; font-size: 9pt; margin: 2px; }

        /* Full body photo (right column bottom) */
        .body-photo { margin-top: 15px; text-align: center; }
        .body-photo img { max-width: 100%; max-height: 320px; object-fit: contain; border-radius: 4px; border: 1px solid #ddd; }

        .footer { position: absolute; bottom: 10mm; left: 12mm; right: 12mm; text-align: center; font-size: 8pt; color: #ccc; border-top: 1px solid #eee; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="page">

        {{-- Header --}}
        <div class="header">
            @if ($applicant->photo)
            <div class="photo-area">
                <img src="{{ storage_path('app/public/' . $applicant->photo) }}" alt="Photo">
            </div>
            @endif
            <div class="info">
                <h1>{{ $applicant->first_name }} {{ $applicant->middle_name ? $applicant->middle_name . ' ' : '' }}{{ $applicant->last_name }} {{ $applicant->suffix }}</h1>
                <div class="subtitle">
                    @if ($applicant->position)
                        {{ $applicant->position->name }}
                    @else
                        Applicant
                    @endif
                </div>
                <div class="contact-line">
                    {{ $applicant->contact ?? '' }}
                    @if ($applicant->contact && $applicant->email) | @endif
                    {{ $applicant->email ?? '' }}
                    @if ($applicant->address) | {{ $applicant->address }} @endif
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Left Column --}}
            <div class="col-left">

                {{-- Work Experience --}}
                @if($applicant->workExperiences->count())
                <div class="section">
                    <div class="section-title">Work Experience</div>
                    @foreach($applicant->workExperiences as $exp)
                    <div class="entry">
                        <div class="entry-title">{{ $exp->position_title }}</div>
                        <div class="entry-sub">{{ $exp->company }}</div>
                        <div class="entry-date">
                            {{ $exp->start_date ? date('M Y', strtotime($exp->start_date)) : '' }}
                            — {{ $exp->end_date ? date('M Y', strtotime($exp->end_date)) : 'Present' }}
                        </div>
                        @if($exp->description)
                        <div class="entry-desc">{{ $exp->description }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Education --}}
                @if($applicant->educations->count())
                <div class="section">
                    <div class="section-title">Education</div>
                    @foreach($applicant->educations as $edu)
                    <div class="entry">
                        <div class="entry-title">{{ $edu->degree ?? $edu->course }}</div>
                        <div class="entry-sub">{{ $edu->school }}</div>
                        @if($edu->start_year || $edu->end_year)
                        <div class="entry-date">
                            {{ $edu->start_year ?? '' }}
                            @if($edu->start_year && $edu->end_year) — @endif
                            {{ $edu->end_year ?? '' }}
                        </div>
                        @endif
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
                        <div class="entry-title">{{ $cert->name }}</div>
                        @if($cert->issued_by)
                        <div class="entry-sub">{{ $cert->issued_by }}</div>
                        @endif
                        @if($cert->issued_date)
                        <div class="entry-date">{{ date('M Y', strtotime($cert->issued_date)) }}</div>
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
                        <div class="entry-sub">{{ $ref->position }}</div>
                        @if($ref->contact)
                        <div class="entry-date">{{ $ref->contact }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

            </div>

            {{-- Right Column --}}
            <div class="col-right">

                {{-- Personal Details --}}
                <div class="section">
                    <div class="section-title">Details</div>
                    <div class="detail-row">
                        <span class="detail-label">Gender</span>
                        <span class="detail-value">{{ ucfirst($applicant->gender ?? '—') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Birthdate</span>
                        <span class="detail-value">{{ $applicant->birthdate ? date('M d, Y', strtotime($applicant->birthdate)) : '—' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Civil Status</span>
                        <span class="detail-value">{{ $applicant->civilStatus?->label ?? '—' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Nationality</span>
                        <span class="detail-value">{{ $applicant->nationality?->name ?? '—' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Religion</span>
                        <span class="detail-value">{{ $applicant->religion?->name ?? '—' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Passport</span>
                        <span class="detail-value">{{ $applicant->has_passport === 'with' ? 'Yes' : ($applicant->has_passport === 'without' ? 'No' : '—') }}</span>
                    </div>
                </div>

                {{-- Preferred Country --}}
                @if($applicant->country)
                <div class="section">
                    <div class="section-title">Destination</div>
                    <div style="font-size:10pt;">{{ $applicant->country->name }}</div>
                </div>
                @endif

                {{-- Skills --}}
                @if($applicant->skills->count())
                <div class="section">
                    <div class="section-title">Skills</div>
                    <div>
                        @foreach($applicant->skills as $skill)
                            <span class="skill-tag">{{ $skill->name }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Full Body Photo --}}
                @if($applicant->full_body_photo)
                <div class="section">
                    <div class="section-title">Photo</div>
                    <div class="body-photo">
                        <img src="{{ storage_path('app/public/' . $applicant->full_body_photo) }}" alt="Full body photo">
                    </div>
                </div>
                @endif

            </div>
        </div>

        <div class="footer">
            CV generated from {{ config('app.name') }} on {{ date('F d, Y') }}
        </div>

    </div>
</body>
</html>
