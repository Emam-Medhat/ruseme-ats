<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\AnalysisReport;
use App\Models\JobMatch;
use App\Models\Resume;
use App\Services\ResumeAnalysisService;
use App\Services\SemanticJobMatcher;
use App\Services\JobDescriptionExtractor;
use App\Support\ResumeAnalysisNormalizer;
use App\Support\ResumePdfAssembler;
use App\Support\ResumePreviewBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ResumeController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ResumeAnalysisService $analysisService,
        protected SemanticJobMatcher $semanticJobMatcher,
        protected JobDescriptionExtractor $jobDescriptionExtractor
    ) {}

    /**
     * Job match history (JSON API).
     */
    public function index()
    {
        $jobMatches = auth()->user()
            ->jobMatches()
            ->with('resume:id,title,filename')
            ->latest()
            ->get()
            ->map(function (JobMatch $jobMatch) {
                return [
                    'id' => $jobMatch->id,
                    'resume_id' => $jobMatch->resume_id,
                    'resume_title' => $jobMatch->resume?->filename ?? $jobMatch->resume?->title,
                    'job_title' => $jobMatch->job_title,
                    'match_score' => $jobMatch->match_score,
                    'created_at' => $jobMatch->created_at,
                ];
            });

        return response()->json($jobMatches);
    }

    /**
     * Inertia history page for job matches.
     */
    public function history()
    {
        $jobMatches = auth()->user()
            ->jobMatches()
            ->with('resume:id,title,filename')
            ->latest()
            ->get();

        return Inertia::render('History', [
            'jobMatches' => $jobMatches,
        ]);
    }

    /**
     * Handle the upload and analysis of a resume (full flow in one DB transaction).
     */
    public function upload(Request $request)
    {
        $maxKb = (int) config('cvgenius.upload.max_kb', 5120);

        $request->validate([
            'resume' => 'required|file|mimes:pdf,docx|max:'.$maxKb,
        ]);

        $user = auth()->user();

        if ($user->credits < 1) {
            return back()->withErrors(['credits' => 'Insufficient credits.']);
        }

        $file = $request->file('resume');
        $storedPath = null;

        try {
            $redirect = DB::transaction(function () use ($request, $user, $file, &$storedPath) {
                $storedPath = $file->store('resumes', 'local');

                $extension = strtolower($file->getClientOriginalExtension());
                $parsedText = $this->analysisService->extractText($storedPath, $extension);

                $resume = Resume::create([
                    'user_id' => $user->id,
                    'filename' => $file->getClientOriginalName(),
                    'file_path' => $storedPath,
                    'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'original_file_path' => $storedPath,
                    'parsed_text' => $parsedText,
                    'status' => 'processing',
                ]);

                $result = $this->analysisService->analyzeResume($resume);

                $readability = 70;
                $growth = 70;
                $spelling = 70;
                if (isset($result['checks']) && is_array($result['checks'])) {
                    $readability = collect($result['checks'])->firstWhere('id', 'readability')['score'] ?? 70;
                    $growth = collect($result['checks'])->firstWhere('id', 'growth_signals')['score'] ?? 70;
                    $spelling = collect($result['checks'])->firstWhere('id', 'spelling')['score'] ?? 70;
                }

                AnalysisReport::create([
                    'resume_id' => $resume->id,
                    'user_id' => $user->id,
                    'overall_score' => (int) ($result['overall_score'] ?? 70),
                    'impact_score' => [
                        'score' => (int) ($result['impact_score'] ?? $growth),
                        'feedback' => '',
                    ],
                    'brevity_score' => [
                        'score' => (int) ($result['brevity_score'] ?? $readability),
                        'feedback' => '',
                    ],
                    'style_score' => [
                        'score' => (int) ($result['style_score'] ?? $spelling),
                        'feedback' => '',
                    ],
                    'ats_score' => (int) ($result['ats_score'] ?? $result['ats_compatibility_score'] ?? 70),
                    'recruiter_score' => (int) ($result['recruiter_score'] ?? 0),
                    'keyword_match_score' => (int) ($result['keyword_match_score'] ?? 0),
                    'executive_summary' => (string) ($result['executive_summary'] ?? ''),
                    'feedback_data' => $result,
                    'full_analysis' => $result,
                ]);

                $resume->update(['status' => 'completed']);
                $user->decrement('credits');

                return redirect()->route('resumes.report', $resume->id)
                    ->with('success', 'Analysis complete!');
            });

            return $redirect;
        } catch (\Throwable $e) {
            if ($storedPath) {
                Storage::disk('local')->delete($storedPath);
            }
            \Log::error('Resume analysis failed: '.$e->getMessage());

            return back()->withErrors(['resume' => 'Failed to analyze your resume: '.$e->getMessage()]);
        }
    }

    /**
     * Show the detailed analysis report page.
     */
    public function report(Resume $resume)
    {
        $this->authorize('view', $resume);

        $report = $resume->latestReport;

        if (! $report) {
            return redirect()->route('dashboard')
                ->with('error', 'No analysis report found. Please re-upload your resume.');
        }

        $raw = $report->full_analysis ?? $report->feedback_data;
        $analysis = ResumeAnalysisNormalizer::normalize($raw, $report);

        if (! is_array($analysis)) {
            $analysis = [
                'overall_score' => (int) ($report->overall_score ?? 0),
                'score_headline' => 'Analysis data could not be loaded.',
                'score_explanation' => 'Please re-upload your resume for a fresh scan.',
                'checks' => [],
            ];
        }

        $previewBuilder = new ResumePreviewBuilder();
        $analysis = $previewBuilder->ensureChecks($analysis, $report);
        $analysis['resume_sections'] = $previewBuilder->build(
            (string) $resume->parsed_text,
            $analysis,
            $report
        );

        if (empty($analysis['score_headline'])) {
            $score = (int) ($analysis['overall_score'] ?? $report->overall_score ?? 0);
            $analysis['score_headline'] = "Your resume scored {$score}/100.";
        }
        if (empty($analysis['score_explanation'])) {
            $analysis['score_explanation'] = $analysis['summary_feedback']
                ?? 'Review highlighted lines on your CV preview and apply the recommended fixes.';
        }

        $issueChecks = collect($analysis['checks'] ?? [])
            ->where('status', 'issue')
            ->sortBy('points_impact')
            ->values()
            ->all();

        $passedChecks = collect($analysis['checks'] ?? [])
            ->where('status', 'passed')
            ->values()
            ->all();

        $lockedChecks = collect($analysis['checks'] ?? [])
            ->where('status', 'locked')
            ->values()
            ->all();

        return Inertia::render('Resumes/Report', [
            'resume' => $resume->only(['id', 'title', 'filename', 'created_at']),
            'overallScore' => (int) ($analysis['overall_score'] ?? $report->overall_score ?? 0),
            'scores' => [
                'overall' => (int) ($analysis['overall_score'] ?? $report->overall_score ?? 0),
                'ats' => (int) ($analysis['ats_score'] ?? $report->ats_score ?? 0),
                'recruiter' => (int) ($analysis['recruiter_score'] ?? $report->recruiter_score ?? 0),
                'impact' => (int) ($analysis['impact_score'] ?? 0),
                'brevity' => (int) ($analysis['brevity_score'] ?? 0),
                'style' => (int) ($analysis['style_score'] ?? 0),
                'keyword' => (int) ($analysis['keyword_match_score'] ?? $report->keyword_match_score ?? 0),
            ],
            'sectionScores' => $analysis['section_scores'] ?? [],
            'executiveSummary' => (string) ($analysis['executive_summary'] ?? $report->executive_summary ?? ''),
            'strengths' => $analysis['strengths'] ?? [],
            'weaknesses' => $analysis['weaknesses'] ?? [],
            'recommendedKeywords' => $analysis['recommended_keywords'] ?? [],
            'analysis' => $analysis,
            'parsedText' => (string) $resume->parsed_text,
            'issueChecks' => $issueChecks,
            'passedChecks' => $passedChecks,
            'lockedChecks' => $lockedChecks,
            'analyzedAt' => $report->created_at->format('M j, Y'),
            'userName' => auth()->user()->name,
        ]);
    }

    /**
     * Download improved CV as PDF (ATS-friendly templates).
     */
    public function downloadImproved(Request $request, Resume $resume, ?string $template = null)
    {
        $this->authorize('view', $resume);

        $report = $resume->analysisReports()->latest()->first();
        if (! $report) {
            abort(404, 'No report found.');
        }

        $analysis = $report->full_analysis ?? $report->feedback_data ?? [];

        return $this->streamResumePdf(
            $resume,
            $report,
            $analysis['resume_sections'] ?? [],
            (int) ($request->input('overall_score') ?? $report->overall_score ?? 0),
            (string) $resume->parsed_text,
            $template ?: $request->query('template', 'clean')
        );
    }

    /**
     * Download CV with live edits from the report UI (applied magic rewrites).
     * POST /resumes/{resume}/download
     */
    public function downloadImprovedPreview(Request $request, Resume $resume)
    {
        $this->authorize('view', $resume);

        $request->validate([
            'resume_sections' => 'required|array',
            'overall_score' => 'nullable|integer|min:0|max:100',
            'parsed_text' => 'nullable|string',
            'template' => 'nullable|string|in:ats,clean,modern,executive,ats_classic,ats_modern,ats_executive,ats_tech,ats_elegant',
        ]);

        $report = $resume->analysisReports()->latest()->first();
        if (! $report) {
            abort(404, 'No report found.');
        }

        return $this->streamResumePdf(
            $resume,
            $report,
            $request->input('resume_sections'),
            (int) $request->input('overall_score', $report->overall_score ?? 0),
            (string) $request->input('parsed_text', $resume->parsed_text ?? ''),
            $request->input('template', 'ats_classic')
        );
    }

    /**
     * @param  array<string, mixed>  $resumeSections
     */
    private function streamResumePdf(
        Resume $resume,
        AnalysisReport $report,
        array $resumeSections,
        int $overallScore,
        string $parsedText,
        string $template = 'ats_classic'
    ) {
        // Map legacy template choices to the new premium ATS templates
        $template = match ($template) {
            'ats', 'clean' => 'ats_classic',
            'modern' => 'ats_modern',
            'executive' => 'ats_executive',
            default => $template,
        };

        $allowed = ['ats_classic', 'ats_modern', 'ats_executive', 'ats_tech', 'ats_elegant'];
        $template = in_array($template, $allowed, true) ? $template : 'ats_classic';

        $assembler = new ResumePdfAssembler();
        $cv = $assembler->assemble($resumeSections, $parsedText ?: (string) $resume->parsed_text);

        $safeName = preg_replace('/[^\w\-]+/', '_', pathinfo($resume->filename ?? 'resume', PATHINFO_FILENAME)) ?: 'resume';

        $view = 'pdf.templates.' . $template;
        $pdf = Pdf::loadView($view, ['cv' => $cv]);

        $pdf->setPaper('A4', 'portrait');

        $suffix = '_CV';

        return $pdf->download($safeName.$suffix.'.pdf');
    }

    /**
     * Show the target job description input form.
     */
    public function showTargetForm(Resume $resume)
    {
        $this->authorize('view', $resume);

        return Inertia::render('Resume/TargetMatch', [
            'resume' => $resume,
        ]);
    }

    /**
     * Process Job Description target matching via AI.
     */
    public function processTargetMatch(Request $request, Resume $resume)
    {
        $this->authorize('view', $resume);

        $request->validate([
            'job_title' => 'nullable|string|max:255',
            'job_description' => 'nullable|string|max:20000',
            'job_url' => 'nullable|url|max:2000',
        ]);

        $user = auth()->user();

        try {
            $resolved = $this->jobDescriptionExtractor->resolve(
                $request->input('job_url'),
                $request->input('job_description'),
                $request->input('job_title')
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['job_description' => $e->getMessage()]);
        }

        if ($resolved['description'] === '') {
            return back()->withErrors([
                'job_description' => $resolved['warning'] ?? 'Paste the full job description (LinkedIn URLs cannot be scraped automatically).',
            ]);
        }

        $jobTitle = $resolved['title'];
        $jobDescription = $resolved['description'];

        $cachedMatch = JobMatch::where('user_id', $user->id)
            ->where('resume_id', $resume->id)
            ->where('job_title', $jobTitle)
            ->latest()
            ->first();

        if ($cachedMatch) {
            return Inertia::render('Resume/TargetMatch', [
                'resume' => $resume,
                'matchResult' => $cachedMatch->toMatchResult(),
            ]);
        }

        if ($user->credits < 1) {
            return back()->withErrors(['job_description' => 'You do not have enough credits. Please upgrade your subscription.']);
        }

        try {
            $matchResult = $this->semanticJobMatcher->match(
                (string) $resume->parsed_text,
                $jobTitle,
                $jobDescription
            );

            $weakSentences = collect($matchResult['tailoring_suggestions'] ?? [])
                ->map(fn (array $suggestion) => [
                    'section' => $suggestion['section'] ?? 'Experience',
                    'original_bullet' => $suggestion['original_bullet'] ?? '',
                    'feedback' => $suggestion['feedback'] ?? '',
                ])
                ->values()
                ->all();

            $improvedSentences = collect($matchResult['tailoring_suggestions'] ?? [])
                ->map(fn (array $suggestion) => [
                    'section' => $suggestion['section'] ?? 'Experience',
                    'suggested_bullet' => $suggestion['suggested_bullet'] ?? '',
                ])
                ->values()
                ->all();

            JobMatch::create([
                'user_id' => $user->id,
                'resume_id' => $resume->id,
                'job_title' => $jobTitle,
                'job_description' => $jobDescription,
                'match_score' => (int) ($matchResult['match_score'] ?? 0),
                'missing_keywords' => $matchResult['missing_keywords'] ?? [],
                'weak_sentences' => $weakSentences,
                'improved_sentences' => $improvedSentences,
                'ai_raw_response' => $matchResult,
            ]);

            $user->decrement('credits');

            return Inertia::render('Resume/TargetMatch', [
                'resume' => $resume,
                'matchResult' => $matchResult,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Target matching failed: '.$e->getMessage());

            return back()->withErrors(['job_description' => 'Failed to match against target job description: '.$e->getMessage()]);
        }
    }

    /**
     * Rewrite a weak resume bullet point via AI.
     * POST /api/ai/rewrite-bullet
     */
    public function rewriteBullet(Request $request)
    {
        set_time_limit(45);

        $request->validate([
            'bullet' => [
                'required',
                'string',
                'min:5',
                'max:600',
            ],
        ], [
            'bullet.required' => 'Please provide a bullet point text to rewrite.',
            'bullet.min'      => 'The bullet point is too short to rewrite effectively.',
            'bullet.max'      => 'The bullet point exceeds the maximum allowed length.',
        ]);

        try {
            $result = $this->analysisService->rewriteBullet(
                $request->string('bullet')->trim()->value()
            );

            $suggestions = $result['suggestions'] ?? [];

            if (empty($suggestions)) {
                return response()->json([
                    'message'     => 'No suggestions could be generated for this bullet point.',
                    'suggestions' => [],
                ], 422);
            }

            return response()->json([
                'suggestions' => array_values($suggestions),
            ]);

        } catch (\Throwable $e) {
            \Log::error('[CVGenius] Bullet rewrite endpoint failed: '.$e->getMessage(), [
                'bullet_preview' => substr($request->input('bullet', ''), 0, 80),
            ]);

            return response()->json([
                'message'     => 'The AI rewrite service is temporarily unavailable. Please try again in a moment.',
                'suggestions' => [],
            ], 503);
        }
    }
}

