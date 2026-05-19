<?php

namespace App\Support;

class CvGeniusAnalysisPrompt
{
    public static function build(string $resumeText): string
    {
        return <<<PROMPT
You are an expert ATS (Applicant Tracking System) resume analyzer.

Analyze the following resume and return ONLY a valid JSON object with no additional text, 
no markdown, no code blocks, no explanation.

SCORING WEIGHTS (must add up to 100):
- ats_compatibility: 25%
- keywords:          20%
- impact_metrics:    20%
- formatting:        15%
- brevity:           10%
- grammar:            5%
- sections:           5%

For each section, give a score from 0 to 100.

The FINAL score must be calculated as:
(ats_compatibility × 0.25) + (keywords × 0.20) + (impact_metrics × 0.20) + 
(formatting × 0.15) + (brevity × 0.10) + (grammar × 0.05) + (sections × 0.05)

STRICT RULES:
- Final score must be between 10 and 95 (never 100, never below 10)
- If critical issues >= 2: final score must not exceed 70
- If critical issues >= 4: final score must not exceed 50
- Never inflate scores — be strict and realistic

Return ONLY this JSON structure:
{
  "final_score": 0,
  "sections": {
    "ats_compatibility": {
      "score": 0,
      "issues": ["issue 1", "issue 2"],
      "suggestions": ["suggestion 1"]
    },
    "keywords": {
      "score": 0,
      "missing_keywords": ["keyword 1", "keyword 2"],
      "found_keywords": ["keyword 1"],
      "suggestions": ["suggestion 1"]
    },
    "impact_metrics": {
      "score": 0,
      "weak_bullets": ["bullet 1"],
      "suggestions": ["suggestion 1"]
    },
    "formatting": {
      "score": 0,
      "issues": ["issue 1"],
      "suggestions": ["suggestion 1"]
    },
    "brevity": {
      "score": 0,
      "issues": ["issue 1"],
      "suggestions": ["suggestion 1"]
    },
    "grammar": {
      "score": 0,
      "issues": ["issue 1"]
    },
    "sections": {
      "score": 0,
      "missing_sections": ["section 1"],
      "present_sections": ["section 1"]
    }
  },
  "critical_issues": [],
  "top_suggestions": ["suggestion 1", "suggestion 2", "suggestion 3"]
}

Resume to analyze:
{$resumeText}
PROMPT;
    }
}

