<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $cv['name'] ?? 'Resume' }}</title>
    <style>
        @page {
            margin: 1.5cm 1.8cm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: "Trebuchet MS", "Lucida Grande", "Lucida Sans Unicode", sans-serif;
            font-size: 9.5pt;
            line-height: 1.35;
            color: #1a202c;
        }

        /* ── HEADER ── */
        .name {
            font-size: 20pt;
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
            margin-bottom: 8pt;
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
            font-size: 9.5pt;
            line-height: 1.35;
        }

        /* ── JOB ENTRY ── */
        .job {
            margin-bottom: 7pt;
        }
        .entry-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .entry-table td {
            padding: 0;
            vertical-align: baseline;
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
        }

        /* ── PROJECTS & EDUCATION ── */
        .project {
            margin-bottom: 6pt;
        }
        .edu-entry {
            margin-bottom: 5pt;
        }
    </style>
</head>
<body>

    {{-- ══ HEADER ══ --}}
    <div style="margin-bottom: 8pt; border-bottom: 2px solid #e2e8f0; padding-bottom: 6pt;">
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

            <div class="job" style="page-break-inside: avoid;">
                <table class="entry-table" style="font-weight: bold; font-size: 9.5pt; color: #0f172a;">
                    <tr>
                        <td style="text-align: left;">{{ $company }}</td>
                        <td style="text-align: right; color: #0284c7; white-space: nowrap;">{{ $job['dates'] ?? '' }}</td>
                    </tr>
                </table>
                <table class="entry-table" style="font-size: 9.5pt; margin-bottom: 2px;">
                    <tr>
                        <td style="text-align: left; font-style: italic; color: #475569;">{{ $job['job_title'] ?? '' }}</td>
                        @if($location)
                            <td style="text-align: right; font-style: italic; color: #64748b; white-space: nowrap;">{{ $location }}</td>
                        @endif
                    </tr>
                </table>

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
                    <div><span class="tech-line">Tech Stack: {{ $techStackBullet }}</span></div>
                @endif
            </div>
        @endforeach
    @endif

    {{-- ══ SKILLS ══ --}}
    @if(!empty($cv['skills_text']))
        <div class="section-title" style="page-break-inside: avoid;">Technical Expertise</div>
        <div class="section-content" style="page-break-inside: avoid;">
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
        <div class="section-title" style="page-break-inside: avoid;">Technical Projects</div>
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
                <div class="project" style="page-break-inside: avoid;">
                    <table class="entry-table">
                        <tr>
                            <td style="text-align: left; font-weight: bold; color: #0f172a;">{{ $proj['name'] ?? '' }}</td>
                            @if(!empty($proj['link']))
                                <td style="text-align: right; font-size: 9pt;">
                                    <a href="{{ $proj['link'] }}" style="color: #0284c7;">{{ preg_replace('/^https?:\/\//', '', $proj['link']) }}</a>
                                </td>
                            @endif
                        </tr>
                    </table>
                    @if($projDesc)
                        <div style="font-size: 9.5pt; color: #334155; margin-bottom: 2px;">{{ $projDesc }}</div>
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
            <div class="section-content" style="color: #334155; page-break-inside: avoid;">
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
    @if(!empty($cv['education']) && collect($cv['education'])->contains(fn($e) => !empty($e['school']) || !empty($e['dates'])))
        <div class="section-title" style="page-break-inside: avoid;">Education</div>
        @foreach($cv['education'] as $edu)
            <div class="edu-entry" style="page-break-inside: avoid;">
                <table class="entry-table">
                    <tr>
                        <td style="text-align: left; font-weight: bold; color: #0f172a;">{{ $edu['school'] ?: $edu['degree'] }}</td>
                        <td style="text-align: right; color: #0f172a; font-weight: bold;">{{ $edu['dates'] ?? '' }}</td>
                    </tr>
                    @if(!empty($edu['school']))
                    <tr>
                        <td style="text-align: left; font-style: italic; color: #475569;">
                            {{ $edu['degree'] ?? '' }}
                            @if(!empty($edu['honors']))
                                &nbsp;&middot;&nbsp; <span style="font-weight: normal; font-style: normal; color: #64748b; font-size: 8.5pt;">{{ $edu['honors'] }}</span>
                            @endif
                        </td>
                        @if(!empty($edu['location']))
                            <td style="text-align: right; font-style: italic; color: #64748b;">{{ $edu['location'] }}</td>
                        @endif
                    </tr>
                    @endif
                </table>
            </div>
        @endforeach
    @elseif(!empty($cv['education_text']))
        <div class="section-title" style="page-break-inside: avoid;">Education</div>
        <div class="section-content" style="color: #334155; page-break-inside: avoid;">
            @php
                $eduHtml = e($cv['education_text']);
                $eduHtml = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $eduHtml);
                $eduHtml = preg_replace('/\*(.*?)\*/',     '<em>$1</em>',         $eduHtml);
                $eduHtml = nl2br($eduHtml);
            @endphp
            {!! $eduHtml !!}
        </div>
    @endif

    {{-- ══ CERTIFICATIONS ══ --}}
    @if(!empty($cv['certifications']))
        <div class="section-title" style="page-break-inside: avoid;">Certifications</div>
        <div class="section-content" style="color: #334155; page-break-inside: avoid;">
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
        <div class="section-title" style="page-break-inside: avoid;">Languages</div>
        <div class="section-content" style="color: #334155; page-break-inside: avoid;">
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
