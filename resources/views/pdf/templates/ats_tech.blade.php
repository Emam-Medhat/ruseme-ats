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
            font-family: "Trebuchet MS", "Lucida Grande", "Lucida Sans Unicode", sans-serif;
            font-size: 10pt;
            line-height: 1.35;
            color: #1a202c;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* ── HEADER ── */
        .header {
            text-align: left;
            margin-bottom: 10pt;
        }
        .name {
            font-size: 22pt;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: -0.5px;
            margin-bottom: 2pt;
        }
        .title {
            font-size: 11pt;
            font-weight: bold;
            color: #0284c7;
            letter-spacing: 0.5px;
            margin-bottom: 4pt;
        }
        .contact {
            font-size: 9pt;
            color: #475569;
        }
        .contact a {
            color: #0284c7;
            text-decoration: none;
            font-weight: bold;
        }

        /* ── SECTIONS ── */
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 1.5px solid #0284c7;
            padding-bottom: 2px;
            margin-top: 12pt;
            margin-bottom: 5pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .section-content {
            font-size: 10pt;
            line-height: 1.35;
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
            font-size: 10pt;
            color: #0f172a;
            flex: 1;
            padding-right: 8px;
        }
        .job-header-right {
            font-weight: bold;
            font-size: 10pt;
            color: #0284c7;
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
            font-size: 10pt;
            color: #475569;
            flex: 1;
            padding-right: 8px;
        }
        .job-subheader-right {
            font-style: italic;
            font-size: 9.5pt;
            color: #64748b;
            white-space: nowrap;
            text-align: right;
        }

        /* ── BULLETS ── */
        ul.bullets {
            margin: 3px 0;
            padding-left: 14px;
            list-style-type: circle;
        }
        ul.bullets li {
            font-size: 9.5pt;
            line-height: 1.35;
            margin-bottom: 2.5px;
            color: #334155;
            text-align: justify;
        }
        .tech-line {
            font-family: Consolas, "Courier New", monospace;
            font-size: 8.5pt;
            font-weight: bold;
            margin-top: 3px;
            color: #0284c7;
            background-color: #f0f9ff;
            padding: 2px 6px;
            border-radius: 3px;
            display: inline-block;
        }

        /* ── SKILLS ── */
        .skills-table {
            width: 100%;
            border-collapse: collapse;
        }
        .skills-table tr td {
            font-size: 9.5pt;
            line-height: 1.4;
            padding: 2px 0;
            vertical-align: top;
        }
        .skills-category {
            font-weight: bold;
            color: #0f172a;
            white-space: nowrap;
            padding-right: 8px;
            width: 1%;
            font-family: "Trebuchet MS", sans-serif;
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
            font-size: 10pt;
            color: #0f172a;
        }
        .project-desc {
            font-size: 9.5pt;
            line-height: 1.35;
            color: #334155;
            margin-bottom: 2px;
        }

        /* ── EDUCATION ── */
        .edu-entry {
            margin-bottom: 5pt;
            page-break-inside: avoid;
        }
        .edu-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
        }
        .edu-left {
            font-weight: bold;
            font-size: 10pt;
            color: #0f172a;
            flex: 1;
            padding-right: 8px;
        }
        .edu-right {
            font-size: 10pt;
            color: #0f172a;
            white-space: nowrap;
            text-align: right;
        }
        .edu-sub {
            font-style: italic;
            font-size: 10pt;
            color: #475569;
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
                echo implode(' &nbsp;&bull;&nbsp; ', $contactParts);
            @endphp
        </div>
    </div>

    {{-- ══ SUMMARY ══ --}}
    @if(!empty($cv['summary_text']))
        <div class="section-title">Technical Profile</div>
        <div class="section-content" style="text-align: justify; color: #334155;">
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

                if (preg_match('/\(([^)]+)\)\s*$/', $company, $m)) {
                    $location = $m[1];
                    $company  = trim(str_replace($m[0], '', $company));
                } elseif (preg_match('/^(.*?)\s*,\s*([^,]+(?:,\s*[^,]+)*)$/', $company, $m)) {
                    $company  = trim($m[1]);
                    $location = trim($m[2]);
                }

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
                <div class="job-header">
                    <span class="job-header-left">{{ $company }}</span>
                    <span class="job-header-right">{{ $job['dates'] ?? '' }}</span>
                </div>
                <div class="job-subheader">
                    <span class="job-subheader-left">{{ $job['job_title'] ?? '' }}</span>
                    @if($location)
                        <span class="job-subheader-right">{{ $location }}</span>
                    @endif
                </div>

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

                @if(!empty($techStackBullet))
                    <div class="tech-line">Tech Stack: {{ $techStackBullet }}</div>
                @endif
            </div>
        @endforeach
    @endif

    {{-- ══ SKILLS ══ --}}
    @if(!empty($cv['skills_text']))
        <div class="section-title">Technical Expertise</div>
        <div class="section-content">
            @php
                $lines = preg_split('/\r?\n|·{2,}/', $cv['skills_text']);
                $rows  = [];

                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '') continue;

                    $parts = preg_split('/\s+·\s+(?=[A-Za-z &]+:)/', $line);
                    foreach ($parts as $part) {
                        $part = trim($part);
                        if (preg_match('/^([A-Za-z0-9\s\&\/]+):\s*(.*)$/', $part, $m)) {
                            $rows[] = ['cat' => trim($m[1]), 'val' => trim($m[2])];
                        } elseif (!empty($rows)) {
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
                            <td style="color: #334155;">{{ $row['val'] }}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                @php
                    $skillsHtml = e($cv['skills_text']);
                    $skillsHtml = preg_replace('/\b([A-Za-z0-9\s\&]+):/m', '<strong>$1:</strong>', $skillsHtml);
                    $skillsHtml = nl2br($skillsHtml);
                @endphp
                <div style="color: #334155;">{!! $skillsHtml !!}</div>
            @endif
        </div>
    @endif

    {{-- ══ PROJECTS ══ --}}
    @if(!empty($cv['projects']))
        <div class="section-title">Technical Projects</div>
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
                            <span style="font-size:9pt; font-style:italic;">
                                <a href="{{ $proj['link'] }}" style="color: #0284c7;">{{ preg_replace('/^https?:\/\//', '', $proj['link']) }}</a>
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
                        <div class="tech-line">Tech: {{ $projTech }}</div>
                    @endif
                </div>
            @endforeach
        @else
            <div class="section-content" style="color: #334155;">
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
        <div class="section-content" style="color: #334155;">
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
                    <div style="font-size:9.5pt; color:#475569;">{{ $edu['location'] }}</div>
                @endif
            </div>
        @endforeach
    @endif

    {{-- ══ CERTIFICATIONS ══ --}}
    @if(!empty($cv['certifications']))
        <div class="section-title">Certifications</div>
        <div class="section-content" style="color: #334155;">
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
        <div class="section-content" style="color: #334155;">
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
