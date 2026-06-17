<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resume - {{ $applicant->full_name }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; line-height: 1.4; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #1a56db; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { font-size: 18px; color: #1a56db; margin: 5px 0; }
        .photo { width: 120px; height: 140px; object-fit: cover; border: 1px solid #ccc; margin: 0 auto 10px; display: block; }
        .section { margin-bottom: 12px; }
        .section-title { font-size: 14px; font-weight: bold; color: #1a56db; border-bottom: 1px solid #ccc; padding-bottom: 3px; margin-bottom: 8px; }
        .info-row { margin-bottom: 3px; }
        .info-label { font-weight: bold; display: inline-block; width: 120px; }
        .item { margin-bottom: 6px; }
        .item-title { font-weight: bold; font-size: 12px; }
        .item-subtitle { font-size: 11px; color: #666; }
        .item-dates { font-size: 10px; color: #999; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        .label-cell { width: 130px; font-weight: bold; color: #555; }
        .footer { text-align: center; font-size: 10px; color: #999; margin-top: 20px; border-top: 1px solid #eee; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        @if ($applicant->photo)
            <img src="{{ storage_path('app/public/' . $applicant->photo) }}" alt="Photo" class="photo">
        @endif
        <h1>{{ $applicant->full_name }}</h1>
    </div>

    <!-- Personal Information -->
    <div class="section">
        <div class="section-title">Personal Information</div>
        <table>
            <tr><td class="label-cell">Full Name:</td><td>{{ $applicant->first_name }} {{ $applicant->middle_name }} {{ $applicant->last_name }}</td></tr>
            <tr><td class="label-cell">Gender:</td><td>{{ $applicant->gender }}</td></tr>
            <tr><td class="label-cell">Birthdate:</td><td>{{ $applicant->birthdate ? $applicant->birthdate->format('F d, Y') : '' }}</td></tr>
            <tr><td class="label-cell">Contact:</td><td>{{ $applicant->contact }}</td></tr>
            <tr><td class="label-cell">Email:</td><td>{{ $applicant->email }}</td></tr>
            <tr><td class="label-cell">Address:</td><td>{{ $applicant->address }}</td></tr>
            @if($applicant->country)
                <tr><td class="label-cell">Country:</td><td>{{ $applicant->country->name }}</td></tr>
            @endif
        </table>
    </div>

    <!-- Education -->
    @if($applicant->education->isNotEmpty())
        <div class="section">
            <div class="section-title">Education</div>
            @foreach($applicant->education as $edu)
                <div class="item">
                    <div class="item-title">{{ $edu->degree ?? '' }}</div>
                    <div class="item-subtitle">{{ $edu->school }}</div>
                    <div class="item-dates">{{ $edu->year_start ?? '' }} - {{ $edu->year_end ?? $edu->year_graduated ?? '' }}</div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Work Experience -->
    @if($applicant->workExperiences->isNotEmpty())
        <div class="section">
            <div class="section-title">Work Experience</div>
            @foreach($applicant->workExperiences as $exp)
                <div class="item">
                    <div class="item-title">{{ $exp->position }}</div>
                    <div class="item-subtitle">{{ $exp->company }}</div>
                    <div class="item-dates">
                        {{ ($exp->from_date ?? $exp->date_from ?? '') ? \Carbon\Carbon::parse($exp->from_date ?? $exp->date_from)->format('M Y') : '' }}
                        -
                        {{ ($exp->to_date ?? $exp->date_to ?? '') ? \Carbon\Carbon::parse($exp->to_date ?? $exp->date_to)->format('M Y') : 'Present' }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Certificates -->
    @if($applicant->certificates->isNotEmpty())
        <div class="section">
            <div class="section-title">Certifications & Training</div>
            @foreach($applicant->certificates as $cert)
                <div class="item">
                    <div class="item-title">{{ $cert->name ?? $cert->certificate_name ?? $cert->certificate_no }}</div>
                    <div class="item-subtitle">{{ $cert->issued_by ?? $cert->institution ?? '' }}</div>
                    <div class="item-dates">{{ $cert->issued_date ? \Carbon\Carbon::parse($cert->issued_date)->format('M d, Y') : ($cert->issue_date ? \Carbon\Carbon::parse($cert->issue_date)->format('M d, Y') : '') }}</div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- References -->
    @if($applicant->references->isNotEmpty())
        <div class="section">
            <div class="section-title">References</div>
            @foreach($applicant->references as $ref)
                <div class="item">
                    <div class="item-title">{{ $ref->name }}</div>
                    <div class="item-subtitle">{{ $ref->position }}</div>
                    <div class="item-subtitle">{{ $ref->company ?? '' }}</div>
                    <div class="item-dates">Contact: {{ $ref->contact }}</div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="footer">
        Generated on {{ now()->format('F d, Y \a\t h:i A') }}
    </div>
</body>
</html>
