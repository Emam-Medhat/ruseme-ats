@php
    $data = $report->full_analysis ?? $report->feedback_data ?? [];
    $sections = $resume_sections ?? ($data['resume_sections'] ?? []);
    $overall = $overall_score ?? $report->overall_score ?? 0;
    $name = $sections['name'] ?? pathinfo($resume->filename ?? $resume->title ?? 'CV', PATHINFO_FILENAME);
    $title = $sections['title'] ?? '';
    $contact = $sections['contact'] ?? '';
    $links = $sections['links'] ?? [];
    $summary = $sections['summary_text'] ?? ($data['summary_feedback'] ?? '');
    $experience = $sections['experience'] ?? [];
    $skills = $sections['skills_text'] ?? '';
    $education = $sections['education'] ?? '';
    $projects = $sections['projects_text'] ?? '';
    $languages = $sections['languages_text'] ?? '';
    $certifications = $sections['certifications_text'] ?? '';
    $fallbackText = $parsed_text ?? ($resume->parsed_text ?? '');
@endphp

<div style="text-align:center; margin-bottom:14px; border-bottom:1px solid #ddd; padding-bottom:10px;">
    <h1 style="margin:0; font-size:20px; text-transform:uppercase;">{{ $name }}</h1>
    @if($title)
        <div style="margin-top:4px; font-size:11px; font-weight:bold; color:#4338ca;">{{ $title }}</div>
    @endif
    @if($contact)
        <div style="margin-top:6px; font-size:10px; color:#444;">{{ $contact }}</div>
    @endif
    @if(!empty($links))
        <div style="margin-top:4px; font-size:9px; color:#4338ca;">
            {{ implode(' · ', array_filter($links)) }}
        </div>
    @endif
</div>

@if($summary)
    <h2 style="font-size:11px; margin:14px 0 6px 0; border-bottom:1px solid #ccc; text-transform:uppercase;">Professional Summary</h2>
    <p style="margin:0 0 10px 0;">{{ $summary }}</p>
@endif

@if(!empty($experience))
    <h2 style="font-size:11px; margin:14px 0 6px 0; border-bottom:1px solid #ccc; text-transform:uppercase;">Work Experience</h2>
    @foreach($experience as $job)
        <div style="margin-bottom:10px;">
            <div style="font-weight:bold; font-size:11px;">
                {{ $job['job_title'] ?? '' }}@if(!empty($job['company'])) — {{ $job['company'] }}@endif
                @if(!empty($job['dates']))
                    <span style="float:right; font-weight:normal; color:#555;">{{ $job['dates'] }}</span>
                @endif
            </div>
            @if(!empty($job['bullets']))
                <ul style="margin:6px 0 0 0; padding-left:16px;">
                    @foreach($job['bullets'] as $bullet)
                        @php
                            $text = is_array($bullet) ? ($bullet['text'] ?? '') : (string) $bullet;
                        @endphp
                        @if($text !== '')
                            <li style="margin-bottom:4px;">{{ $text }}</li>
                        @endif
                    @endforeach
                </ul>
            @endif
        </div>
    @endforeach
@endif

@if($skills)
    <h2 style="font-size:11px; margin:14px 0 6px 0; border-bottom:1px solid #ccc; text-transform:uppercase;">Skills</h2>
    <p style="margin:0 0 10px 0;">{{ $skills }}</p>
@endif

@if($projects)
    <h2 style="font-size:11px; margin:14px 0 6px 0; border-bottom:1px solid #ccc; text-transform:uppercase;">Projects</h2>
    <p style="margin:0 0 10px 0; white-space:pre-wrap;">{{ $projects }}</p>
@endif

@if($education)
    <h2 style="font-size:11px; margin:14px 0 6px 0; border-bottom:1px solid #ccc; text-transform:uppercase;">Education</h2>
    <p style="margin:0 0 10px 0; white-space:pre-wrap;">{{ $education }}</p>
@endif

@if($certifications)
    <h2 style="font-size:11px; margin:14px 0 6px 0; border-bottom:1px solid #ccc; text-transform:uppercase;">Certifications</h2>
    <p style="margin:0 0 10px 0; white-space:pre-wrap;">{{ $certifications }}</p>
@endif

@if($languages)
    <h2 style="font-size:11px; margin:14px 0 6px 0; border-bottom:1px solid #ccc; text-transform:uppercase;">Languages</h2>
    <p style="margin:0 0 10px 0; white-space:pre-wrap;">{{ $languages }}</p>
@endif

@if(empty($experience) && $fallbackText !== '')
    <h2 style="font-size:11px; margin:14px 0 6px 0; border-bottom:1px solid #ccc; text-transform:uppercase;">Resume</h2>
    <pre style="font-family:Arial,sans-serif; font-size:10px; white-space:pre-wrap; margin:0;">{{ $fallbackText }}</pre>
@endif

<p style="margin-top:20px; font-size:8px; color:#888; text-align:center;">
    CV Genius AI · ATS-optimized export · Score {{ $overall }}/100 · {{ now()->format('M j, Y') }}
</p>
