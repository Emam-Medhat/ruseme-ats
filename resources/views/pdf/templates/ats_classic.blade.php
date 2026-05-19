<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $cv['name'] ?? 'Resume' }}</title>
    <style>
        @page {
            margin: 1.8cm 2cm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: "Times New Roman", Times, Georgia, serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000000;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* ── HEADER ── */
        .header {
            text-align: center;
            margin-bottom: 8pt;
        }
        .name {
            font-size: 18pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3pt;
        }
        .title {
            font-size: 11pt;
            margin-bottom: 3pt;
        }
        .contact {
            font-size: 10pt;
        }
        .contact a {
            color: #000000;
            text-decoration: none;
        }

        /* ── SECTIONS ── */
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 0.5pt solid #000000;
            padding-bottom: 2px;
            margin-top: 10pt;
            margin-bottom: 5pt;
            letter-spacing: 0.5px;
        }
        .section-content {
            font-size: 11pt;
            line-height: 1.3;
        }

        /* ── JOB ENTRY ── */
        .job {
            margin-bottom: 7pt;
            page-break-inside: avoid;
        }
        .job-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            width: 100%;
            margin-bottom: 1px;
        }
        .job-header-left {
            font-weight: bold;
            font-size: 11pt;
            flex: 1;
            padding-right: 8px;
        }
        .job-header-right {
            font-weight: bold;
            font-size: 11pt;
            white-space: nowrap;
            text-align: right;
        }
        .job-subheader {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            width: 100%;
            margin-bottom: 3px;
        }
        .job-subheader-left {
            font-style: italic;
            font-size: 11pt;
            flex: 1;
            padding-right: 8px;
        }
        .job-subheader-right {
            font-style: italic;
            font-size: 11pt;
            white-space: nowrap;
            text-align: right;
        }

        /* ── BULLETS ── */
        ul.bullets {
            margin: 3px 0 3px 0;
            padding-left: 16px;
            list-style-type: disc;
        }
        ul.bullets li {
            font-size: 11pt;
            line-height: 1.3;
            margin-bottom: 2px;
            text-align: justify;
        }
        .tech-line {
            font-style: italic;
            font-size: 10.5pt;
            margin-top: 2px;
            color: #111;
        }

        /* ── SKILLS ── */
        .skills-table {
            width: 100%;
            border-collapse: collapse;
        }
        .skills-table tr td {
            font-size: 11pt;
            line-height: 1.4;
            padding: 1px 0;
            vertical-align: top;
        }
        .skills-category {
            font-weight: bold;
            white-space: nowrap;
            padding-right: 6px;
            width: 1%;
        }

        /* ── PROJECTS ── */
        .project {
            margin-bottom: 6pt;
            page-break-inside: avoid;
        }
        .project-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 2px;
        }
        .project-name {
            font-weight: bold;
            font-size: 11pt;
        }
        .project-desc {
            font-size: 11pt;
            line-height: 1.3;
            margin-bottom: 2px;
        }

        /* ── EDUCATION ── */
        .edu-entry {
            margin-bottom: 4pt;
            page-break-inside: avoid;
        }
        .edu-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
        }
        .edu-left {
            font-weight: bold;
            font-size: 11pt;
            flex: 1;
            padding-right: 8px;
        }
        .edu-right {
            font-size: 11pt;
            white-space: nowrap;
            text-align: right;
        }
        .edu-sub {
            font-style: italic;
            font-size: 11pt;
        }
    </style>
</head>
<body>

    {{-- ══ HEADER ══ --}}
    <div class="header">
        <div class="name">{{ $cv['name'] }}</div>
        @if(!empty($cv['title']))
            <div class="title">{{ $cv['title'] }}</div>
        @endif
        <div class="contact">
            @php
                $contactParts = [];
                if (!empty($cv['contact'])) {
                    $rawParts = preg_split('/\s*[\/|·•]\s*/', $cv['contact']);
                    foreach ($rawParts as $p) {
                        $p = trim($p);
                        if ($p === '') continue;
                        if (filter_var($p, FILTER_VALIDATE_EMAIL)) {
                            $contactParts[] = '<a href="mailto:' . e($p) . '">' . e($p) . '</a>';
                        } else {
                            $contactParts[] = e($p);
                        }
                    }
                }
                if (!empty($cv['links'])) {
                    foreach ($cv['links'] as $link) {
                        $displayLink = preg_replace('/^https?:\/\/(www\.)?/', '', $link);
                        $contactParts[] = '<a href="' . e($link) . '">' . e($displayLink) . '</a>';
                    }
                }
                echo implode(' &nbsp;&middot;&nbsp; ', $contactParts);
            @endphp
        </div>
    </div>

    {{-- ══ SUMMARY ══ --}}
    @if(!empty($cv['summary_text']))
        <div class="section-title">Professional Summary</div>
        <div class="section-content" style="text-align: justify;">
            {{ $cv['summary_text'] }}
        </div>
    @endif

    {{-- ══ EXPERIENCE ══ --}}
    @if(!empty($cv['experience']))
        <div class="section-title">Professional Experience</div>
        @foreach($cv['experience'] as $job)
            @php
                $company  = trim($job['company'] ?? '');
                $location = '';

                // Extract location from company string
                if (preg_match('/\(([^)]+)\)\s*$/', $company, $m)) {
                    $location = $m[1];
                    $company  = trim(str_replace($m[0], '', $company));
                } elseif (preg_match('/^(.*?)\s*,\s*([^,]+(?:,\s*[^,]+)*)$/', $company, $m)) {
                    $company  = trim($m[1]);
                    $location = trim($m[2]);
                }

                // Separate tech stack from bullets
                $bullets         = $job['bullets'] ?? [];
                $techStackBullet = null;
                $filteredBullets = [];

                foreach ($bullets as $b) {
                    $text = is_array($b) ? ($b['text'] ?? '') : (string) $b;
                    if (preg_match('/^(Technologies|Tech Stack|Tech|Skills Used):\s*(.*)$/i', $text, $m)) {
                        $techStackBullet = $m[2];
                    } else {
                        $filteredBullets[] = $text;
                    }
                }
            @endphp

            <div class="job">
                {{-- Company + Date --}}
                <div class="job-header">
                    <span class="job-header-left">{{ $company }}</span>
                    <span class="job-header-right">{{ $job['dates'] ?? '' }}</span>
                </div>
                {{-- Title + Location --}}
                <div class="job-subheader">
                    <span class="job-subheader-left">{{ $job['job_title'] ?? '' }}</span>
                    @if($location)
                        <span class="job-subheader-right">{{ $location }}</span>
                    @endif
                </div>

                {{-- Bullets --}}
                @if(!empty($filteredBullets))
                    <ul class="bullets">
                        @foreach($filteredBullets as $bulletText)
                            @php
                                $bulletHtml = e($bulletText);
                                $bulletHtml = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $bulletHtml);
                                $bulletHtml = preg_replace('/\*(.*?)\*/',     '<em>$1</em>',         $bulletHtml);
                            @endphp
                            <li>{!! $bulletHtml !!}</li>
                        @endforeach
                    </ul>
                @endif

                {{-- Tech stack --}}
                @if(!empty($techStackBullet))
                    <div class="tech-line">Technologies: {{ $techStackBullet }}</div>
                @endif
            </div>
        @endforeach
    @endif

    {{-- ══ SKILLS ══ --}}
    @if(!empty($cv['skills_text']))
        <div class="section-title">Technical Skills</div>
        <div class="section-content">
            @php
                // Parse "Category: items" lines into structured rows
                $lines = preg_split('/\r?\n|·{2,}/', $cv['skills_text']);
                $rows  = [];
                $buffer = '';

                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '') continue;

                    // Split by known category separators (· between categories)
                    $parts = preg_split('/\s+·\s+(?=[A-Za-z &]+:)/', $line);
                    foreach ($parts as $part) {
                        $part = trim($part);
                        if (preg_match('/^([A-Za-z0-9\s\&\/]+):\s*(.*)$/', $part, $m)) {
                            $rows[] = ['cat' => trim($m[1]), 'val' => trim($m[2])];
                        } elseif (!empty($rows)) {
                            // Continuation of previous row
                            $rows[count($rows)-1]['val'] .= ' ' . $part;
                        }
                    }
                }
            @endphp

            @if(!empty($rows))
                <table class="skills-table">
                    @foreach($rows as $row)
                        <tr>
                            <td class="skills-category">{{ $row['cat'] }}:</td>
                            <td>{{ $row['val'] }}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                @php
                    $skillsHtml = e($cv['skills_text']);
                    $skillsHtml = preg_replace('/\b([A-Za-z0-9\s\&]+):/m', '<strong>$1:</strong>', $skillsHtml);
                    $skillsHtml = nl2br($skillsHtml);
                @endphp
                {!! $skillsHtml !!}
            @endif
        </div>

    @elseif(!empty($cv['skills']))
        <div class="section-title">Technical Skills</div>
        <div class="section-content">
            {{ implode(' · ', $cv['skills']) }}
        </div>
    @endif

    {{-- ══ PROJECTS ══ --}}
    @if(!empty($cv['projects']))
        <div class="section-title">Projects</div>
        @php
            $projectsRaw = $cv['projects'];
            $isStructured = is_array($projectsRaw);
        @endphp

        @if($isStructured)
            @foreach($projectsRaw as $proj)
                @php
                    $projBullets = [];
                    $projTech    = null;
                    $projDesc    = $proj['description'] ?? '';
                    foreach (($proj['bullets'] ?? []) as $b) {
                        $text = is_array($b) ? ($b['text'] ?? '') : (string) $b;
                        if (preg_match('/^Technologies:\s*(.*)$/i', $text, $m)) {
                            $projTech = $m[1];
                        } else {
                            $projBullets[] = $text;
                        }
                    }
                @endphp
                <div class="project">
                    <div class="project-header">
                        <span class="project-name">{{ $proj['name'] ?? '' }}</span>
                        @if(!empty($proj['link']))
                            <span style="font-size:10pt; font-style:italic;">
                                <a href="{{ $proj['link'] }}">{{ preg_replace('/^https?:\/\//', '', $proj['link']) }}</a>
                            </span>
                        @endif
                    </div>
                    @if($projDesc)
                        <div class="project-desc">{{ $projDesc }}</div>
                    @endif
                    @if(!empty($projBullets))
                        <ul class="bullets">
                            @foreach($projBullets as $b)
                                @php
                                    $bHtml = e($b);
                                    $bHtml = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $bHtml);
                                @endphp
                                <li>{!! $bHtml !!}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if($projTech)
                        <div class="tech-line">Technologies: {{ $projTech }}</div>
                    @endif
                </div>
            @endforeach

        @else
            <div class="section-content">
                @php
                    $projectsHtml = e($projectsRaw);
                    $projectsHtml = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $projectsHtml);
                    $projectsHtml = preg_replace('/\*(.*?)\*/',     '<em>$1</em>',         $projectsHtml);
                    $projectsHtml = nl2br($projectsHtml);
                @endphp
                {!! $projectsHtml !!}
            </div>
        @endif
    @endif

    {{-- ══ EDUCATION ══ --}}
    @if(!empty($cv['education_text']))
        <div class="section-title">Education</div>
        <div class="section-content">
            @php
                $eduHtml = e($cv['education_text']);
                $eduHtml = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $eduHtml);
                $eduHtml = preg_replace('/\*(.*?)\*/',     '<em>$1</em>',         $eduHtml);
                $eduHtml = nl2br($eduHtml);
            @endphp
            {!! $eduHtml !!}
        </div>

    @elseif(!empty($cv['education']))
        <div class="section-title">Education</div>
        @foreach($cv['education'] as $edu)
            <div class="edu-entry">
                <div class="edu-header">
                    <span class="edu-left">{{ $edu['school'] ?? '' }}</span>
                    <span class="edu-right">{{ $edu['dates'] ?? '' }}</span>
                </div>
                <div class="edu-sub">{{ $edu['degree'] ?? '' }}</div>
                @if(!empty($edu['location']))
                    <div style="font-size:10.5pt; color:#222;">{{ $edu['location'] }}</div>
                @endif
            </div>
        @endforeach
    @endif

    {{-- ══ CERTIFICATIONS ══ --}}
    @if(!empty($cv['certifications']))
        <div class="section-title">Certifications</div>
        <div class="section-content">
            @php
                $certHtml = e($cv['certifications']);
                $certHtml = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $certHtml);
                $certHtml = nl2br($certHtml);
            @endphp
            {!! $certHtml !!}
        </div>
    @endif

    {{-- ══ LANGUAGES ══ --}}
    @if(!empty($cv['languages']))
        <div class="section-title">Languages</div>
        <div class="section-content">
            @php
                $langHtml = e($cv['languages']);
                $langHtml = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $langHtml);
                $langHtml = nl2br($langHtml);
            @endphp
            {!! $langHtml !!}
        </div>
    @endif

</body>
</html>
