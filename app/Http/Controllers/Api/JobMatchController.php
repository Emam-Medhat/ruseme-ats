<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobMatch;
use App\Models\Resume;
use App\Services\JobDescriptionExtractor;
use App\Services\SemanticJobMatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobMatchController extends Controller
{
    public function __construct(
        protected JobDescriptionExtractor $jobExtractor,
        protected SemanticJobMatcher $matcher
    ) {}

    /**
     * POST /api/job/match
     */
    public function match(Request $request): JsonResponse
    {
        $request->validate([
            'jobUrl' => 'nullable|url|max:2000',
            'jobDescription' => 'nullable|string|max:20000',
            'jobTitle' => 'nullable|string|max:255',
            'resume_id' => 'nullable|integer|exists:resumes,id',
            'resume' => 'nullable|array',
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $resumeText = '';
        $resumeId = $request->input('resume_id');

        if ($resumeId) {
            $resume = Resume::where('id', $resumeId)->where('user_id', $user->id)->firstOrFail();
            $resumeText = (string) $resume->parsed_text;
        } elseif ($request->filled('resume.text')) {
            $resumeText = (string) $request->input('resume.text');
        } elseif ($request->filled('resume.parsed_text')) {
            $resumeText = (string) $request->input('resume.parsed_text');
        }

        if (trim($resumeText) === '') {
            return response()->json(['message' => 'Resume text is required (resume_id or resume object).'], 422);
        }

        try {
            $resolved = $this->jobExtractor->resolve(
                $request->input('jobUrl'),
                $request->input('jobDescription'),
                $request->input('jobTitle')
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if ($resolved['description'] === '') {
            return response()->json([
                'message' => $resolved['warning'] ?? 'Job description required.',
                'parsedTitle' => $resolved['title'],
                'requiresDescription' => true,
            ], 422);
        }

        if ($user->credits < 1) {
            return response()->json(['message' => 'Insufficient credits.'], 402);
        }

        $result = $this->matcher->match(
            $resumeText,
            $resolved['title'],
            $resolved['description']
        );

        if ($resumeId) {
            JobMatch::create([
                'user_id' => $user->id,
                'resume_id' => $resumeId,
                'job_title' => $resolved['title'],
                'job_description' => $resolved['description'],
                'match_score' => $result['matchScore'],
                'match_result' => $result,
            ]);
        }

        $user->decrement('credits');

        return response()->json([
            'matchScore' => $result['matchScore'],
            'missingSkills' => $result['missingSkills'],
            'keywordGaps' => $result['keywordGaps'],
            'recommendations' => $result['recommendations'],
            'summary' => $result['summary'] ?? '',
            'semanticMeta' => $result['semantic_meta'] ?? [],
            'jobTitle' => $resolved['title'],
            'warning' => $resolved['warning'],
        ]);
    }
}
