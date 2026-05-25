<template>
  <Head title="Resume Analytics Dashboard" />

  <!-- ================= TOP NAV BAR ================= -->
  <nav class="fixed inset-x-0 top-0 z-50 border-b border-zinc-200/60 dark:border-zinc-800/60 bg-white/70 dark:bg-zinc-950/70 backdrop-blur-md h-[56px] flex items-center shrink-0">
    <div class="w-full max-w-[1600px] mx-auto flex items-center justify-between px-6 gap-4">
      <!-- Logo -->
      <Link href="/" class="flex items-center gap-2.5 text-sm font-black text-zinc-900 dark:text-white shrink-0 group">
        <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 via-violet-600 to-indigo-600 flex items-center justify-center font-black text-white text-base shadow-md shadow-indigo-500/20 group-hover:scale-105 transition-transform duration-250">✦</div>
        <span class="tracking-tight font-extrabold">{{ page.props.cvGenius?.name || 'CV Genius AI' }} <span class="bg-gradient-to-r from-indigo-500 to-violet-500 bg-clip-text text-transparent font-black">Pro</span></span>
      </Link>

      <!-- Centered Active Pill -->
      <div class="hidden sm:flex items-center gap-2.5">
        <span class="inline-flex items-center gap-1.5 text-[11px] font-black text-indigo-600 dark:text-indigo-400 bg-indigo-500/5 border border-indigo-500/15 px-3 py-1 rounded-full truncate max-w-[250px]">
          <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
          📄 {{ resume.filename || resume.title }}
        </span>
        <span class="text-[9px] font-black px-2.5 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">
          PRO SCAN ACTIVE
        </span>
      </div>

      <!-- Right actions -->
      <div class="flex items-center gap-4 shrink-0">
        <Link
          :href="route('resumes.target', resume.id)"
          class="text-[10px] font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400 hover:text-indigo-750 dark:hover:text-indigo-300 transition-colors flex items-center gap-1.5 border border-indigo-500/15 bg-indigo-500/5 px-3.5 py-1.5 rounded-xl shadow-sm"
        >
          🎯 مطابقة الوصف الوظيفي (LinkedIn/ATS Matcher)
        </Link>
        <Link
          :href="route('dashboard')"
          class="text-xs font-bold text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors flex items-center gap-1.5"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
          </svg>
          Back to Dashboard
        </Link>
      </div>
    </div>
  </nav>

  <!-- ================= DASHBOARD LAYOUT ================= -->
  <div class="flex w-full overflow-hidden bg-zinc-50/50 dark:bg-zinc-950 font-sans antialiased text-zinc-900 dark:text-zinc-100 animate-fade-in" style="height: 100dvh; padding-top: 56px;">
    
    <!-- ================= LEFT NAVIGATION SIDEBAR ================= -->
    <aside class="w-[240px] h-screen bg-white dark:bg-zinc-900/40 border-r border-zinc-200/80 dark:border-zinc-800/80 flex flex-col justify-between shrink-0 z-10 shadow-sm overflow-hidden">
      <!-- Circular Progress & Score Banner -->
      <div class="p-5 flex flex-col items-center border-b border-zinc-200/80 dark:border-zinc-800/80 bg-zinc-50/40 dark:bg-zinc-900/40">
        <!-- Circular Score Ring -->
        <div class="relative w-28 h-28 mb-3.5 flex items-center justify-center group">
          <!-- Outer glowing circle -->
          <div class="absolute inset-0 rounded-full blur-md opacity-25 scale-95 transition-all duration-700"
               :style="{ backgroundColor: scoreColor }"></div>
          
          <svg width="108" height="108" viewBox="0 0 80 80" class="relative">
            <circle cx="40" cy="40" r="33" fill="none" stroke="#f1f5f9" dark:stroke="#1e293b" stroke-width="5.5"/>
            <circle cx="40" cy="40" r="33" fill="none" 
                     :stroke="scoreColor" 
                     stroke-width="5.5"
                     stroke-linecap="round"
                     :stroke-dasharray="`${2 * Math.PI * 33}`"
                     :stroke-dashoffset="ringDashoffset"
                     style="transform: rotate(-90deg); transform-origin: center; transition: stroke-dashoffset 1.2s cubic-bezier(0.4, 0, 0.2, 1);"/>
          </svg>
          <div class="absolute inset-0 flex flex-col items-center justify-center">
            <span class="text-3.5xl font-black tracking-tight" :style="{ color: scoreColor }">
              {{ localOverallScore }}
            </span>
            <span class="text-[9px] font-black text-zinc-450 dark:text-zinc-500 tracking-widest uppercase -mt-0.5">
              ATS SCORE
            </span>
          </div>
        </div>

        <div class="text-[9.5px] text-zinc-400 dark:text-zinc-500 uppercase tracking-wider font-black text-center mt-1">
          Scoring Diagnostics
        </div>
      </div>

      <!-- Left categories menu navigation list -->
      <div class="flex-1 overflow-y-auto px-3.5 py-5 space-y-6">
        <!-- CRITICAL ISSUES -->
        <div>
          <div class="flex items-center justify-between mb-2.5 px-2.5">
            <h3 class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 tracking-widest uppercase">
              Top Categories
            </h3>
            <span class="text-[9px] font-bold text-rose-500 bg-rose-500/10 px-2 py-0.5 rounded-full shrink-0 border border-rose-500/15">
              {{ localIssueChecks.length }} Fixes
            </span>
          </div>
          <div class="space-y-1">
            <button v-for="check in visibleIssues" :key="check.id" 
                    @click="scrollToCheck(check.id)"
                    :class="[
                      'w-full text-left px-3 py-2.5 rounded-xl text-xs font-bold flex items-center justify-between transition-all duration-200 group/nav border border-transparent', 
                      activeCheck === check.id 
                        ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-md shadow-indigo-600/15' 
                        : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800/40 hover:text-zinc-900 dark:hover:text-white'
                    ]">
              <span class="truncate pr-1 flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full" :class="check.status === 'locked' ? 'bg-zinc-450' : 'bg-rose-500'"></span>
                {{ check.name }}
              </span>
              <span v-if="check.status === 'issue'" :class="[
                'text-[9px] font-bold px-1.5 py-0.5 rounded-full shrink-0 min-w-[16px] text-center border',
                activeCheck === check.id ? 'bg-white/20 text-white border-transparent' : 'bg-rose-500/10 text-rose-600 dark:text-rose-450 border-rose-500/15'
              ]">
                {{ check.issue_count }}
              </span>
              <span v-else-if="check.status === 'locked'" class="text-zinc-400 text-[9px] shrink-0">
                🔒
              </span>
            </button>
            <button v-if="issueChecks.length > 5 && !showAllIssues" 
                    @click="showAllIssues = true"
                    class="w-full text-left px-3 py-2 text-[10px] font-black text-indigo-600 dark:text-indigo-400 hover:underline">
              + {{ (issueChecks.length + lockedChecks.length) - 5 }} More Categories
            </button>
          </div>
        </div>

        <!-- COMPLETED / PASSED -->
        <div>
          <h3 class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 tracking-widest uppercase mb-2.5 px-2.5">
            Passed Checks
          </h3>
          <div class="space-y-1">
            <button v-for="check in visiblePassed" :key="check.id" 
                    @click="scrollToCheck(check.id)"
                    :class="[
                      'w-full text-left px-3 py-2.5 rounded-xl text-xs font-bold flex items-center justify-between transition-all duration-200 border border-transparent', 
                      activeCheck === check.id 
                        ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-md shadow-indigo-600/15' 
                        : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800/40 hover:text-zinc-900 dark:hover:text-white'
                    ]">
              <span class="truncate pr-1 flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                {{ check.name }}
              </span>
              <span :class="[
                'text-[8px] font-black px-1.5 py-0.5 rounded-full shrink-0 border',
                activeCheck === check.id ? 'bg-white/20 text-white border-transparent' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/15'
              ]">
                PASSED
              </span>
            </button>
            <button v-if="passedChecks.length > 3 && !showAllPassed" 
                    @click="showAllPassed = true"
                    class="w-full text-left px-3 py-2 text-[10px] font-black text-emerald-600 dark:text-emerald-400 hover:underline">
              + {{ passedChecks.length - 3 }} More Passed Scans
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar Footer -->
      <div class="p-4 border-t border-zinc-200/80 dark:border-zinc-800/80 space-y-2">
        <Link :href="route('resumes.target', resume.id)" class="w-full py-2.5 px-4 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-black uppercase tracking-wider rounded-xl flex items-center justify-center gap-2 transition-all hover:shadow-md shadow-indigo-500/10 active:scale-[0.98]">
          🎯 Job Matcher
        </Link>
        <Link :href="route('dashboard')" class="w-full py-2.5 px-4 bg-zinc-50 hover:bg-zinc-100 dark:bg-zinc-950 dark:hover:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800/80 text-zinc-700 dark:text-zinc-300 text-xs font-black uppercase tracking-wider rounded-xl flex items-center justify-center gap-2 transition-all active:scale-[0.98]">
          ← Main Dashboard
        </Link>
      </div>
    </aside>

    <!-- ================= MIDDLE PANEL (Core Premium Dashboard View) ================= -->
    <main class="flex-1 h-screen overflow-y-auto bg-transparent px-8 py-8 flex flex-col space-y-7 pb-16">
      
      <!-- Sticky/Sleek Title Banner -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h2 class="text-2.5xl font-black text-zinc-900 dark:text-white tracking-tight">
            {{ greeting }}, {{ userName }}.
          </h2>
          <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
            Scanned on {{ analyzedAt || 'Today' }} · High-fidelity real-time score assessment.
          </p>
          
          <!-- AI Ensemble Engine Badge list -->
          <div v-if="localAnalysis?.ai_providers_list && localAnalysis.ai_providers_list.length" class="mt-2.5 flex items-center gap-2 flex-wrap">
            <span class="text-[10px] text-zinc-400 dark:text-zinc-500 font-bold uppercase tracking-wider">AI Ensemble Engine:</span>
            <div class="flex items-center gap-1.5 flex-wrap">
              <span v-for="prov in localAnalysis.ai_providers_list" :key="prov" class="inline-flex items-center gap-1 rounded-md bg-indigo-500/5 px-2 py-0.5 font-bold text-[9px] text-indigo-600 dark:text-indigo-400 border border-indigo-500/10">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                {{ prov }}
              </span>
            </div>
          </div>
        </div>
        
        <!-- Action Badges -->
        <div class="flex flex-wrap gap-2.5 shrink-0">
          <Link :href="route('resumes.target', resume.id)" class="text-xs font-black uppercase tracking-wider px-4 py-2.5 rounded-xl border border-indigo-500 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400 hover:bg-indigo-500/5 transition flex items-center gap-1.5 shadow-sm active:scale-[0.98]">
            🎯 Scan Against Job Description
          </Link>
          <button type="button" @click="showTemplateModal = true" class="text-xs font-black uppercase tracking-wider px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-white hover:from-indigo-500 hover:to-violet-500 shadow-md shadow-indigo-500/10 active:scale-[0.98] transition flex items-center gap-1.5">
            ↓ Download PDF Preview
          </button>
          <Link :href="route('upload')" class="text-xs font-black uppercase tracking-wider px-4 py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:bg-zinc-50 dark:hover:bg-zinc-850 transition text-zinc-700 dark:text-zinc-300 shadow-sm active:scale-[0.98]">
            Re-upload CV
          </Link>
        </div>
      </div>

      <!-- ================= HERO SECTION (glowing diagnostics score + stats) ================= -->
      <div class="bg-gradient-to-br from-indigo-50 via-violet-50/80 to-white border border-indigo-100 rounded-3xl p-6 shadow-md shadow-indigo-100/10 flex flex-col lg:flex-row items-center gap-8 shrink-0">
        
        <!-- Circular Rings (Left) -->
        <div class="flex flex-col items-center shrink-0">
          <div class="relative w-36 h-36 flex items-center justify-center">
            <!-- Glow ring background -->
            <div class="absolute inset-0 rounded-full blur-xl opacity-25 scale-90"
                 :style="{ backgroundColor: scoreColor }"></div>
            
            <svg width="144" height="144" viewBox="0 0 80 80" class="relative">
              <circle cx="40" cy="40" r="32" fill="none" stroke="#f1f5f9" dark:stroke="#1e293b" stroke-width="5.5"/>
              <circle cx="40" cy="40" r="32" fill="none" 
                      :stroke="scoreColor" 
                      stroke-width="5.5"
                      stroke-linecap="round"
                      :stroke-dasharray="`${2 * Math.PI * 32}`"
                      :stroke-dashoffset="ringDashoffset"
                      style="transform: rotate(-90deg); transform-origin: center; transition: stroke-dashoffset 1.5s cubic-bezier(0.4, 0, 0.2, 1);"/>
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
              <span class="text-4.5xl font-black tracking-tight" :style="{ color: scoreColor }">
                {{ localOverallScore }}
              </span>
              <span class="text-[9px] font-black text-zinc-400 dark:text-zinc-500 tracking-widest uppercase -mt-0.5">
                RATING
              </span>
            </div>
          </div>
          <span class="mt-3.5 text-xs font-black px-3.5 py-1 rounded-full uppercase tracking-wider border shadow-sm"
                :style="{ color: scoreColor, backgroundColor: `${scoreColor}10`, borderColor: `${scoreColor}20` }">
            {{ localOverallScore >= 80 ? 'Elite Match' : localOverallScore >= 60 ? 'Strong Match' : 'Revisions Recommended' }}
          </span>
        </div>

        <!-- Rating Headline & Stats Grid (Right) -->
        <div class="flex-1 min-w-0 space-y-4">
          <div>
            <h3 class="text-lg font-black text-zinc-900 dark:text-white leading-tight">
              {{ localAnalysis.score_headline || 'Your ATS scoring assessment is ready.' }}
            </h3>
            <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1 leading-relaxed font-semibold">
              {{ localAnalysis.score_explanation || 'Your profile has been analyzed against the core parameters of modern Applicant Tracking Systems. Apply the highlighted modifications to unlock maximum compatibility.' }}
            </p>
          </div>

          <!-- Quick Statistics Dashboard Grid -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
            <div class="bg-white/70 dark:bg-zinc-900/30 p-3.5 rounded-2xl border border-zinc-200/80 dark:border-zinc-800/80 flex flex-col shadow-sm">
              <span class="text-[9px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider leading-none">Keyword Coverage</span>
              <span class="text-lg font-black text-zinc-900 dark:text-white mt-2 flex items-baseline gap-1">
                {{ recommendedKeywords.length ? Math.round((localAnalysis.keyword_matches?.length / recommendedKeywords.length) * 100) || 0 : 0 }}%
                <span class="text-[8.5px] text-zinc-400 dark:text-zinc-500 font-bold uppercase tracking-wider">coverage</span>
              </span>
            </div>

            <div class="bg-white/70 dark:bg-zinc-900/30 p-3.5 rounded-2xl border border-zinc-200/80 dark:border-zinc-800/80 flex flex-col shadow-sm">
              <span class="text-[9px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider leading-none">Scanned Sections</span>
              <span class="text-lg font-black text-zinc-900 dark:text-white mt-2 flex items-baseline gap-1">
                {{ Object.keys(localAnalysis.resume_sections || {}).filter(k => localAnalysis.resume_sections[k]).length }}
                <span class="text-[8.5px] text-zinc-400 dark:text-zinc-500 font-bold uppercase tracking-wider">detected</span>
              </span>
            </div>

            <div class="bg-white/70 dark:bg-zinc-900/30 p-3.5 rounded-2xl border border-zinc-200/80 dark:border-zinc-800/80 flex flex-col shadow-sm">
              <span class="text-[9px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider leading-none">Brevity Index</span>
              <span class="text-lg font-black text-zinc-900 dark:text-white mt-2 flex items-baseline gap-1">
                {{ localScores.brevity || 0 }}/100
                <span class="text-[8.5px] text-emerald-500 font-black uppercase tracking-wider">ideal</span>
              </span>
            </div>

            <div class="bg-white/70 dark:bg-zinc-900/30 p-3.5 rounded-2xl border border-zinc-200/80 dark:border-zinc-800/80 flex flex-col shadow-sm">
              <span class="text-[9px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider leading-none">Active Issues</span>
              <span class="text-lg font-black text-zinc-900 dark:text-white mt-2 flex items-baseline gap-1">
                {{ localIssueChecks.length }}
                <span class="text-[8.5px] text-rose-500 font-black uppercase tracking-wider">critical</span>
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- ================= KPI METRIC SUMMARY CARDS ================= -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5 shrink-0">
        <KpiCard v-for="kpi in kpiScores" :key="kpi.label" :label="kpi.label" :value="kpi.value" :accent="kpi.color" />
      </div>

      <!-- Radar and Executive narrative side by side -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 shrink-0">
        <!-- Executive Narrative -->
        <div v-if="executiveSummary" class="lg:col-span-8 bg-white dark:bg-zinc-900/40 border border-zinc-200/80 dark:border-zinc-800/80 rounded-2xl p-6 shadow-sm">
          <div class="flex items-center gap-2 mb-3.5">
            <div class="w-6 h-6 rounded bg-indigo-500/10 text-indigo-500 flex items-center justify-center text-xs">📝</div>
            <h3 class="text-[10px] font-black uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Executive Summary</h3>
          </div>
          <p class="text-xs text-zinc-700 dark:text-zinc-300 leading-relaxed font-semibold">
            {{ executiveSummary }}
          </p>
        </div>

        <!-- Radar Scan -->
        <div v-if="radarLabels.length" class="lg:col-span-4 bg-white dark:bg-zinc-900/40 border border-zinc-200/80 dark:border-zinc-800/80 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-6 h-6 rounded bg-indigo-500/10 text-indigo-500 flex items-center justify-center text-xs">📊</div>
            <h3 class="text-[10px] font-black uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Section balance radar</h3>
          </div>
          <div class="flex-1 flex items-center justify-center min-h-[180px]">
            <RadarChart :labels="radarLabels" :values="radarValues" :height="180" />
          </div>
        </div>
      </div>

      <!-- ================= KEYWORD GAP ANALYSIS PANEL ================= -->
      <div v-if="recommendedKeywords.length" class="bg-white dark:bg-zinc-900/40 border border-zinc-200/80 dark:border-zinc-800/80 rounded-3xl p-6 shadow-sm space-y-5 shrink-0">
        <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800/60 pb-4">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-xs border border-emerald-500/25">🔑</div>
            <div>
              <h3 class="text-sm font-black text-zinc-900 dark:text-white tracking-tight">Keywords Coverage Analysis</h3>
              <p class="text-[10px] text-zinc-400 dark:text-zinc-500 font-bold mt-0.5">Scanned and identified from target industry job listings</p>
            </div>
          </div>
          <div class="text-right shrink-0">
            <span class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-3 py-1.5 rounded-xl border border-emerald-500/20 uppercase tracking-wider">
              {{ localAnalysis.keyword_matches?.length || 0 }} / {{ recommendedKeywords.length }} Matched
            </span>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Matched Tags (Left) -->
          <div class="space-y-2.5">
            <h4 class="text-[9.5px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">✓ Matched Keywords (Optimized)</h4>
            <div class="flex flex-wrap gap-1.5 max-h-[140px] overflow-y-auto pr-1">
              <span v-for="kw in localAnalysis.keyword_matches" :key="kw" 
                    class="text-[9.5px] px-2.5 py-1.5 rounded-xl bg-emerald-500/5 text-emerald-600 dark:text-emerald-400 font-bold border border-emerald-500/15">
                {{ kw }}
              </span>
              <span v-if="!localAnalysis.keyword_matches?.length" class="text-xs text-zinc-400 italic">No matches detected. Add industry keywords below.</span>
            </div>
          </div>

          <!-- Missing Tags (Right) -->
          <div class="space-y-2">
            <h4 class="text-[10px] font-black text-rose-500 uppercase tracking-wider">✗ Missing Keywords (Core Gap)</h4>
            <div class="flex flex-wrap gap-1.5 max-h-[140px] overflow-y-auto pr-1">
              <span v-for="kw in localAnalysis.keyword_gaps" :key="kw" 
                    class="text-[9.5px] px-2.5 py-1 rounded-lg bg-white dark:bg-zinc-800 text-rose-500 font-bold border border-rose-500/30 hover:bg-rose-500/5 transition">
                + {{ kw }}
              </span>
              <span v-if="!localAnalysis.keyword_gaps?.length" class="text-xs text-emerald-500 italic">Excellent! You have cover all identified keywords.</span>
            </div>
          </div>
        </div>
      </div>

      <!-- ================= DETAILED SECTIONS ASSESSMENTS (Cards Stack) ================= -->
      <div class="space-y-4">
        <h3 class="text-xs font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">
          Detailed Section-by-Section Diagnostics
        </h3>
        
        <div class="space-y-3.5">
          <div v-for="check in allChecks" :key="check.id" :id="`check-${check.id}`"
               :class="[
                 'bg-white dark:bg-zinc-900 rounded-2xl border transition-all duration-300 p-5 shadow-sm', 
                 activeCheck === check.id 
                   ? 'ring-2 ring-indigo-600 border-transparent shadow-lg shadow-indigo-600/5' 
                   : 'border-zinc-200/80 dark:border-zinc-800/80 hover:border-zinc-300 dark:hover:border-zinc-700'
               ]">
            
            <div class="flex items-start gap-4 justify-between">
              
              <!-- Indicator Icon -->
              <div :class="[
                'w-9 h-9 rounded-xl flex items-center justify-center shrink-0 font-black text-base transition-all', 
                check.status === 'passed' ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20' : 
                check.status === 'locked' ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400' : 'bg-rose-500/10 text-rose-500 border border-rose-500/20'
              ]">
                <span v-if="check.status === 'passed'">✓</span>
                <span v-else-if="check.status === 'locked'">🔒</span>
                <span v-else>✗</span>
              </div>

              <!-- Main Details -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <h4 class="text-sm font-extrabold text-zinc-900 dark:text-white">
                    {{ check.title || check.name }}
                  </h4>
                  <span v-if="check.status === 'locked'" class="text-[8px] bg-zinc-100 dark:bg-zinc-800 text-zinc-500 font-bold px-1.5 py-0.5 rounded uppercase tracking-wider leading-none">
                    PRO FEATURE
                  </span>
                </div>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 leading-relaxed">{{ check.description }}</p>

                <!-- Target Job fit button if job fit check -->
                <div v-if="check.id === 'job_fit'" class="mt-3.5">
                  <Link :href="route('resumes.target', resume.id)" 
                        class="inline-flex items-center gap-1.5 bg-indigo-600 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-md shadow-indigo-600/15 hover:bg-indigo-700 active:scale-[0.98] transition-all">
                    🎯 Match against target Job Description
                  </Link>
                </div>

                <!-- Accordion details for check issues -->
                <div v-if="check.issues && check.issues.length > 0" class="mt-3.5">
                  <button @click="toggleCheck(check.id)" 
                          class="text-xs text-indigo-600 dark:text-indigo-400 font-bold flex items-center gap-1 hover:underline">
                    {{ expandedChecks[check.id] ? 'Hide Findings ▲' : `View ${check.issues.length} Findings ▼` }}
                  </button>

                  <div v-if="expandedChecks[check.id]" class="mt-4 space-y-3.5">
                    <div v-for="(issue, index) in check.issues" :key="index"
                         class="bg-zinc-50/50 dark:bg-zinc-950/60 rounded-xl p-4.5 border border-zinc-200/50 dark:border-zinc-800/40 space-y-3">
                      <!-- Original Weak Line -->
                      <div v-if="issue.original_line" class="border-l-3 border-rose-500 pl-3.5 py-2 bg-rose-500/5 pr-3 rounded-r-xl flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                          <span class="text-[8px] font-black text-rose-500 uppercase tracking-widest block mb-0.5">Original Line</span>
                          <p class="text-xs text-zinc-800 dark:text-zinc-200 font-mono leading-relaxed break-words">{{ issue.original_line }}</p>
                        </div>
                        <MagicRewriteButton 
                          :bullet-text="issue.original_line" 
                          :issue-id="check.id" 
                          @open="openRewriteModal"
                        />
                      </div>
                      
                      <!-- Suggested Improved Line -->
                      <div v-if="issue.improved_line" class="border-l-3 border-emerald-500 pl-3.5 py-2 bg-emerald-500/5 pr-3 rounded-r-xl">
                        <span class="text-[8px] font-black text-emerald-600 dark:text-emerald-400 tracking-widest uppercase block mb-0.5">Suggested Rewrite</span>
                        <p class="text-xs text-zinc-800 dark:text-zinc-200 font-mono font-semibold leading-relaxed">{{ issue.improved_line }}</p>
                      </div>

                      <!-- Tip / Feedback reason -->
                      <div class="text-[11px] text-zinc-500 dark:text-zinc-400 italic leading-relaxed pl-3.5">
                        💡 {{ issue.reason }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Right Score badge -->
              <div class="shrink-0 text-right">
                <span v-if="check.status === 'issue'" class="bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/25 text-[10px] font-extrabold px-3 py-1 rounded-full">
                  -{{ check.points_impact }} pts
                </span>
                <span v-else-if="check.status === 'passed'" class="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/25 text-[10px] font-extrabold px-3 py-1 rounded-full">
                  Passed
                </span>
                <span v-else-if="check.status === 'locked'" class="bg-zinc-100 border border-zinc-200 text-zinc-500 text-[10px] font-extrabold px-3 py-1 rounded-full flex items-center gap-1 cursor-pointer hover:bg-zinc-200 dark:bg-zinc-800 dark:border-zinc-700">
                  🔒 Unlock
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- ================= RIGHT PANEL (420px) — Interactive Resume Editor/Viewer ================= -->
    <aside class="w-[420px] h-screen bg-white dark:bg-zinc-900 border-l border-zinc-200/80 dark:border-zinc-800/80 flex flex-col shrink-0 z-10 shadow-sm min-h-0">
      
      <!-- Top actions -->
      <div class="p-5 border-b border-zinc-200/80 dark:border-zinc-800/80 grid grid-cols-2 gap-3 shrink-0 bg-white dark:bg-zinc-900">
        <!-- RESUME REWRITER → navigates to Upload page for a new CV -->
        <Link
          :href="route('upload')"
          class="bg-indigo-600 text-white py-2.5 px-3 rounded-xl text-xs font-bold shadow-md shadow-indigo-600/10 flex items-center justify-center gap-1.5 hover:bg-indigo-700 active:scale-[0.97] transition-all"
        >
          ✦ RESUME REWRITER
        </Link>
        <button 
          @click="applyAllMagicWrites"
          class="border border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400 py-2.5 px-3 rounded-xl text-xs font-bold hover:bg-indigo-600/5 active:scale-[0.97] transition-all"
        >
          ✎ MAGIC WRITE
        </button>
      </div>

      <!-- CV Preview Read-Only tab switcher -->
      <div class="px-4 py-2 border-b border-zinc-200/80 dark:border-zinc-800/80 bg-white dark:bg-zinc-900 flex gap-2 shrink-0">
        <button type="button" @click="cvView = 'structured'"
                :class="['flex-1 py-1.5 text-[10px] font-bold rounded-lg transition-colors', cvView === 'structured' ? 'bg-indigo-600 text-white' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400']">
          Formatted Live Preview
        </button>
        <button type="button" @click="cvView = 'full'"
                :class="['flex-1 py-1.5 text-[10px] font-bold rounded-lg transition-colors', cvView === 'full' ? 'bg-indigo-600 text-white' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400']">
          Raw Text Scan
        </button>
      </div>

      <p v-if="cvView === 'structured' && weakBulletCount > 0" class="px-4 py-2 text-[10px] text-rose-600 dark:text-rose-400 font-semibold bg-rose-500/5 border-b border-rose-500/10 shrink-0">
        ⚡ {{ weakBulletCount }} line(s) highlighted — scroll to review in real-time
      </p>

      <div class="flex-1 min-h-0 overflow-y-auto bg-zinc-50 dark:bg-zinc-950/60 p-4 pb-8 flex flex-col items-stretch">
        
        <!-- Elegant Live Preview Template Dropdown Switcher (Arabic) -->
        <div v-show="cvView === 'structured'" class="w-full mb-3 shrink-0 flex items-center justify-between gap-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-850 rounded-xl p-2.5 shadow-sm">
          <span class="text-[10px] font-extrabold text-zinc-500 dark:text-zinc-400 flex items-center gap-1">
            🎨 قالب المعاينة المباشرة:
          </span>
          <select v-model="downloadTemplate" class="text-[10.5px] py-1 pl-2 pr-7 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-800 dark:text-zinc-200 font-extrabold focus:ring-1 focus:ring-indigo-500 cursor-pointer shadow-sm">
            <option v-for="tpl in atsTemplates" :key="tpl.id" :value="tpl.id">{{ tpl.name }}</option>
          </select>
        </div>
        
        <!-- Simulated Paper Sheet for full text scan -->
        <div v-if="cvView === 'full'" class="w-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-sm rounded-xl p-4 text-[9px] leading-relaxed font-mono mb-4 space-y-0.5">
          <template v-if="fullTextLines.length">
            <p v-for="(row, idx) in fullTextLines" :key="idx"
               :class="row.reason ? 'text-rose-600 bg-rose-500/5 px-2 py-1 rounded border-l-[3px] border-rose-500 whitespace-pre-wrap' : 'text-zinc-800 dark:text-zinc-200 whitespace-pre-wrap'">
              {{ row.text || ' ' }}
              <span v-if="row.reason" class="block text-[7px] font-bold mt-0.5">← {{ row.reason }}</span>
            </p>
          </template>
          <p v-else class="text-zinc-400 text-center py-8">No text extracted from PDF.</p>
        </div>

        <!-- Live formatted structured preview -->
        <div v-show="cvView === 'structured'" 
             :class="['w-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-sm rounded-xl p-6 text-[10px] text-zinc-800 dark:text-zinc-200 flex flex-col space-y-4 relative pb-8 transition-all duration-500 overflow-hidden cv-preview', 'preview-' + downloadTemplate]">
          
          <div v-if="!localAnalysis.resume_sections" class="text-center text-zinc-400 py-20">
            No CV preview data available.
          </div>

          <template v-else>
            <!-- Header Block -->
            <div class="cv-header text-center pb-3 border-b border-zinc-100 dark:border-zinc-800">
              <h2 class="cv-name text-sm font-extrabold text-zinc-900 dark:text-white uppercase tracking-tight">
                {{ localAnalysis.resume_sections.name || userName }}
              </h2>
              <h3 class="cv-title text-[9px] font-bold text-indigo-600 dark:text-indigo-400 uppercase mt-0.5 tracking-wider">
                {{ localAnalysis.resume_sections.title || 'Candidate Profile' }}
              </h3>
              <p class="cv-contact text-[8px] text-zinc-500 dark:text-zinc-400 mt-1 font-medium">
                {{ localAnalysis.resume_sections.contact }}
              </p>
              
              <!-- External links -->
              <div v-if="localAnalysis.resume_sections.links && localAnalysis.resume_sections.links.length > 0" 
                   class="cv-links flex justify-center gap-2 mt-1.5 flex-wrap">
                <a v-for="link in localAnalysis.resume_sections.links" :key="link"
                   :href="link" target="_blank"
                   class="text-[7.5px] text-indigo-600 dark:text-indigo-400 hover:underline font-mono truncate max-w-[150px]">
                  {{ link }}
                </a>
              </div>
            </div>

            <!-- Profile Summary Section -->
            <div v-if="localAnalysis.resume_sections.summary_text" class="cv-section">
              <h4 class="cv-section-title text-[8.5px] font-black uppercase text-zinc-900 dark:text-white tracking-wider mb-1">
                Professional Summary
              </h4>
              <p class="cv-section-text text-zinc-500 dark:text-zinc-400 text-[8.5px] leading-relaxed">
                {{ localAnalysis.resume_sections.summary_text }}
              </p>
            </div>

            <!-- Experience Section -->
            <div v-if="localAnalysis.resume_sections.experience && localAnalysis.resume_sections.experience.length > 0" class="cv-section">
              <h4 class="cv-section-title text-[8.5px] font-black uppercase text-zinc-900 dark:text-white tracking-wider mb-2 border-b border-zinc-100 dark:border-zinc-800 pb-0.5">
                Work Experience
              </h4>
              
              <div class="space-y-3.5">
                <div v-for="(job, jobIdx) in localAnalysis.resume_sections.experience" :key="jobIdx" class="cv-entry">
                  <div class="cv-entry-header flex justify-between items-baseline text-[9px] font-extrabold text-zinc-900 dark:text-white">
                    <span class="cv-school">{{ job.job_title }} at {{ job.company }}</span>
                    <span class="cv-dates text-zinc-400 font-bold text-[8px]">{{ job.dates }}</span>
                  </div>
                  
                  <ul class="cv-bullets list-disc pl-3.5 mt-1 space-y-2">
                    <li v-for="(bullet, bulletIdx) in job.bullets" :key="bulletIdx"
                        :class="['cv-bullet-item text-[8.5px] leading-relaxed pl-0.5 list-none relative group/item pr-20 transition-all duration-300', bullet.is_weak ? 'text-rose-600 bg-rose-500/5 px-2 py-1.5 rounded-lg border-l-[3px] border-rose-500' : 'text-zinc-600 dark:text-zinc-400']">
                      <span class="block pr-4">{{ typeof bullet === 'string' ? bullet : bullet.text }}</span>
                      
                      <!-- Inline Hover Magic Rewrite Button -->
                      <div v-if="bullet.is_weak" class="absolute top-1.5 right-1.5 opacity-0 group-hover/item:opacity-100 transition-opacity duration-200 z-10 shrink-0">
                        <MagicRewriteButton 
                          :bullet-text="typeof bullet === 'string' ? bullet : bullet.text" 
                          :issue-id="bulletIdx" 
                          @open="openRewriteModal"
                        />
                      </div>

                      <span v-if="bullet.is_weak" class="mt-1 block text-[7px] font-bold text-rose-500">
                        ← {{ bullet.weak_reason || 'Needs improvement' }}
                      </span>
                      <span v-if="bullet.is_weak && bullet.improved_line" class="mt-1 block text-[7px] text-emerald-600 dark:text-emerald-400 font-semibold border-t border-rose-100 dark:border-rose-950/40 pt-1">
                        ✓ Suggested: {{ bullet.improved_line }}
                      </span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Skills Section -->
            <div v-if="localAnalysis.resume_sections.skills_text" class="cv-section">
              <h4 class="cv-section-title text-[8.5px] font-black uppercase text-zinc-900 dark:text-white tracking-wider mb-1 border-b border-zinc-100 dark:border-zinc-800 pb-0.5">
                Skills & Technologies
              </h4>
              <p class="cv-section-text text-zinc-600 dark:text-zinc-400 text-[8.5px] leading-relaxed">
                {{ localAnalysis.resume_sections.skills_text }}
              </p>
            </div>

            <!-- Education Section -->
            <div v-if="parsedEducation && parsedEducation.length > 0" class="cv-section">
              <h4 class="cv-section-title text-[8.5px] font-black uppercase text-zinc-900 dark:text-white tracking-wider mb-2 border-b border-zinc-100 dark:border-zinc-800 pb-0.5">
                Education
              </h4>
              <div class="space-y-2">
                <div v-for="(edu, eduIdx) in parsedEducation" :key="eduIdx" class="cv-entry">
                  <div class="cv-entry-header flex justify-between items-baseline text-[9px] font-extrabold text-zinc-900 dark:text-white">
                    <span class="cv-school">{{ edu.school || edu.degree }}</span>
                    <span class="cv-dates text-zinc-400 font-bold text-[8px]">{{ edu.dates }}</span>
                  </div>
                  <div v-if="edu.school" class="cv-degree text-zinc-500 dark:text-zinc-400 text-[8px] font-medium italic mt-0.5">
                    {{ edu.degree }}
                  </div>
                </div>
              </div>
            </div>

            <div v-if="localAnalysis.resume_sections.projects_text" class="cv-section">
              <h4 class="cv-section-title text-[8.5px] font-black uppercase text-zinc-900 dark:text-white tracking-wider mb-1 border-b border-zinc-100 dark:border-zinc-800 pb-0.5">Projects</h4>
              <p class="cv-section-text text-zinc-600 dark:text-zinc-400 text-[8.5px] leading-relaxed whitespace-pre-wrap">{{ localAnalysis.resume_sections.projects_text }}</p>
            </div>

            <div v-if="localAnalysis.resume_sections.languages_text" class="cv-section">
              <h4 class="cv-section-title text-[8.5px] font-black uppercase text-zinc-900 dark:text-white tracking-wider mb-1 border-b border-zinc-100 dark:border-zinc-800 pb-0.5">Languages</h4>
              <p class="cv-section-text text-zinc-600 dark:text-zinc-400 text-[8.5px] leading-relaxed whitespace-pre-wrap">{{ localAnalysis.resume_sections.languages_text }}</p>
            </div>

            <div v-if="localAnalysis.resume_sections.certifications_text" class="cv-section">
              <h4 class="cv-section-title text-[8.5px] font-black uppercase text-zinc-900 dark:text-white tracking-wider mb-1 border-b border-zinc-100 dark:border-zinc-800 pb-0.5">Certifications</h4>
              <p class="cv-section-text text-zinc-600 dark:text-zinc-400 text-[8.5px] leading-relaxed whitespace-pre-wrap">{{ localAnalysis.resume_sections.certifications_text }}</p>
            </div>
          </template>
        </div>
      </div>

      <!-- PDF Template choices and download triggers (Cleaned up as requested) -->
      <div class="p-4 border-t border-zinc-200/80 dark:border-zinc-800/80 bg-zinc-50 dark:bg-zinc-900 shrink-0 space-y-3">
        <button
          type="button"
          :disabled="downloadingCv"
          @click="showTemplateModal = true"
          class="w-full py-3 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-500/20 active:scale-[0.98] transition-all disabled:opacity-60 flex items-center justify-center gap-2"
        >
          <svg v-if="!downloadingCv" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l4-4m-4 4l-4-4M4 21h16" />
          </svg>
          <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          {{ downloadingCv ? 'جاري تجهيز الـ PDF...' : 'تنزيل الـ CV المُحسن (PDF)' }}
        </button>
        <p class="text-[8.5px] text-center text-zinc-400 dark:text-zinc-500 leading-relaxed font-semibold">
          ✓ عمود واحد متوافق مع نظام الـ ATS · يتضمن تحسينات الذكاء الاصطناعي التلقائية
        </p>
      </div>
    </aside>
  </div>

  <!-- Magic Rewrite Modal -->
  <MagicRewriteModal 
    :is-open="isRewriteOpen"
    :bullet-text="activeBulletText"
    :issue-id="activeIssueId"
    @close="isRewriteOpen = false"
    @apply-suggestion="handleApplySuggestion"
  />

  <!-- Success Toast -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-300 ease-out transform"
      enter-from-class="translate-y-4 opacity-0 sm:translate-y-0 sm:translate-x-4"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition duration-200 ease-in opacity-0"
    >
      <div 
        v-if="toastVisible" 
        class="fixed top-5 right-5 z-[200] max-w-sm w-full bg-zinc-950/90 dark:bg-zinc-900/90 border border-indigo-500/30 rounded-2xl p-4 shadow-xl shadow-indigo-500/5 backdrop-blur-md flex items-center gap-3.5"
      >
        <div class="w-8 h-8 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center shrink-0">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
          </svg>
        </div>
        <div class="flex-1">
          <div class="text-xs font-black text-white">Success</div>
          <div class="text-[10px] text-zinc-400 mt-0.5">{{ toastMessage }}</div>
        </div>
        <button @click="toastVisible = false" class="text-zinc-500 hover:text-white transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </Transition>
  </Teleport>

  <!-- PDF Template Selection Modal (Arabic) -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-300 ease-out transform"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-200 ease-in transform"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div v-if="showTemplateModal" class="fixed inset-0 z-[150] flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-zinc-950/60 backdrop-blur-md" @click="showTemplateModal = false"></div>
        
        <!-- Modal Content Wrapper -->
        <div class="relative bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl w-full max-w-4xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-in fade-in zoom-in-95 duration-200">
          
          <!-- Modal Header -->
          <div class="p-6 border-b border-zinc-150 dark:border-zinc-850 flex items-center justify-between shrink-0 bg-gradient-to-r from-zinc-50 to-white dark:from-zinc-950 dark:to-zinc-900">
            <div class="space-y-1 text-left">
              <h3 class="text-lg font-black text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                📄 اختر القالب المتوافق مع أنظمة الـ ATS لتنزيل الـ CV
              </h3>
              <p class="text-xs text-zinc-500 dark:text-zinc-400">
                جميع هذه القوالب معتمدة ومصممة بعمود واحد لتجاوز أنظمة الفحص والفرز الآلي بنجاح.
              </p>
            </div>
            <button @click="showTemplateModal = false" class="p-2 rounded-full hover:bg-zinc-150 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Modal Grid Content (Scrollable) -->
          <div class="p-6 overflow-y-auto grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
            <div 
              v-for="tpl in atsTemplates" 
              :key="tpl.id"
              @click="triggerTemplateDownload(tpl.id)"
              class="relative p-5 rounded-2xl border bg-zinc-50/50 hover:bg-white dark:bg-zinc-950/40 dark:hover:bg-zinc-950/80 border-zinc-200 dark:border-zinc-800 hover:border-indigo-500 dark:hover:border-indigo-400 transition-all duration-300 group cursor-pointer hover:shadow-lg flex flex-col justify-between gap-4"
            >
              <div class="space-y-2">
                <!-- Top Row -->
                <div class="flex items-start justify-between gap-3">
                  <div class="space-y-1">
                    <span class="text-sm font-black text-zinc-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                      {{ tpl.name }}
                    </span>
                    <span class="block text-[8.5px] uppercase tracking-wider font-extrabold text-zinc-400 dark:text-zinc-500">
                      خط القالب: {{ tpl.font }}
                    </span>
                  </div>
                  <!-- Radio mark or indicator -->
                  <div class="w-5 h-5 rounded-full border border-zinc-300 dark:border-zinc-700 flex items-center justify-center group-hover:border-indigo-500 dark:group-hover:border-indigo-400 group-hover:bg-indigo-50 dark:group-hover:bg-indigo-950/40 transition">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 dark:bg-indigo-400 opacity-0 group-hover:opacity-100 scale-75 group-hover:scale-100 transition-all duration-200"></span>
                  </div>
                </div>
                <!-- Description -->
                <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed font-medium">
                  {{ tpl.desc }}
                </p>
              </div>

              <!-- Action button in card -->
              <div class="pt-3 flex items-center justify-between border-t border-zinc-150 dark:border-zinc-800/50 mt-1">
                <span class="text-[9px] font-bold text-zinc-400 uppercase tracking-widest flex items-center gap-1">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                  ATS Safe
                </span>
                <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                  تنزيل الآن &larr;
                </span>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="p-5 border-t border-zinc-150 dark:border-zinc-850 flex items-center justify-between shrink-0 bg-zinc-50/50 dark:bg-zinc-950/40">
            <p class="text-[10px] text-zinc-400 dark:text-zinc-500 leading-none">
              ✓ تم الفحص وتنسيق الخطوط والمسافات تلقائياً لموافقة قارئ السيرة الذاتية (ATS parser).
            </p>
            <button @click="showTemplateModal = false" class="px-5 py-2.5 bg-zinc-200 hover:bg-zinc-300 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 text-xs font-bold rounded-xl transition">
              إلغاء
            </button>
          </div>
          
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, Head, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import KpiCard from '@/Components/CvGenius/KpiCard.vue'
import RadarChart from '@/Components/CvGenius/RadarChart.vue'
import MagicRewriteButton from '@/Components/CvGenius/MagicRewriteButton.vue'
import MagicRewriteModal from '@/Components/CvGenius/MagicRewriteModal.vue'

const page = usePage()

const props = defineProps({
    resume:       { type: Object, required: true },
    overallScore: { type: Number, default: 0 },
    scores:       { type: Object, default: () => ({}) },
    sectionScores:{ type: Object, default: () => ({}) },
    executiveSummary: { type: String, default: '' },
    strengths:    { type: Array, default: () => [] },
    weaknesses:   { type: Array, default: () => [] },
    recommendedKeywords: { type: Array, default: () => [] },
    analysis:     { type: Object, default: null },
    issueChecks:  { type: Array, default: () => [] },
    passedChecks: { type: Array, default: () => [] },
    lockedChecks: { type: Array, default: () => [] },
    analyzedAt:   { type: String, default: '' },
    userName:     { type: String, default: '' },
    parsedText:   { type: String, default: '' },
})

const cvView = ref('structured')

// Local reactive state for live editing and real-time score updates
const localAnalysis = ref(JSON.parse(JSON.stringify(props.analysis || {})))
const localOverallScore = ref(props.overallScore)
const localScores = ref(JSON.parse(JSON.stringify(props.scores || {})))
const localIssueChecks = ref(JSON.parse(JSON.stringify(props.issueChecks || [])))
const localPassedChecks = ref(JSON.parse(JSON.stringify(props.passedChecks || [])))

// Magic Rewrite Modal and Toast state
const isRewriteOpen = ref(false)
const activeBulletText = ref('')
const activeIssueId = ref(null)
const toastMessage = ref('')
const toastVisible = ref(false)
const downloadingCv = ref(false)
const downloadTemplate = ref('ats_classic')
const fixesAppliedCount = ref(0)
const showTemplateModal = ref(false)

const triggerTemplateDownload = (tplId) => {
    downloadTemplate.value = tplId
    downloadImprovedCv()
    showTemplateModal.value = false
}

const parsedEducation = computed(() => {
  const rawEducation = localAnalysis.value?.resume_sections?.education
  if (!rawEducation) return []
  if (Array.isArray(rawEducation)) return rawEducation

  const lines = String(rawEducation).split(/\r?\n/)
  const entries = []

  for (let line of lines) {
    line = line.replace(/<\/?[^>]+(>|$)/g, "").trim()
    line = line.replace(/\s+/g, ' ').trim()
    if (!line) continue

    let parts = line.split(/\s*,\s*|\s*-\s*|\s*\|\s*/)
    let degree = line
    let school = ''
    let dates = ''

    if (parts.length >= 3) {
      let dateIdx = -1
      for (let i = 0; i < parts.length; i++) {
        if (/\b(19|20)\d{2}\b|present|current|حتى|الآن/i.test(parts[i])) {
          dateIdx = i
          break
        }
      }

      if (dateIdx !== -1) {
        dates = parts[dateIdx]
        parts.splice(dateIdx, 1)
      }

      if (parts.length >= 2) {
        let schoolIdx = -1
        for (let i = 0; i < parts.length; i++) {
          if (/university|college|institute|school|academy|جامعة|معهد|كلية/i.test(parts[i])) {
            schoolIdx = i
            break
          }
        }

        if (schoolIdx !== -1) {
          school = parts[schoolIdx]
          parts.splice(schoolIdx, 1)
          degree = parts.join(', ')
        } else {
          degree = parts[0]
          school = parts[1]
        }
      } else if (parts.length === 1) {
        degree = parts[0]
      }
    } else if (parts.length === 2) {
      if (/\b(19|20)\d{2}\b|present|current/i.test(parts[1])) {
        degree = parts[0]
        dates = parts[1]
      } else if (/\b(19|20)\d{2}\b|present|current/i.test(parts[0])) {
        degree = parts[1]
        dates = parts[0]
      } else {
        if (/university|college|institute|school|academy|جامعة|معهد|كلية/i.test(parts[0])) {
          school = parts[0]
          degree = parts[1]
        } else {
          degree = parts[0]
          school = parts[1]
        }
      }
    }

    entries.push({
      degree: degree.trim(),
      school: school.trim(),
      dates: dates.trim()
    })
  }

  return entries
})

const atsTemplates = [
  {
    id: 'ats_classic',
    name: 'Harvard Classic (هارفارد الكلاسيكي)',
    font: 'Times New Roman',
    desc: 'تنسيق كلاسيكي تقليدي بخط Serif. مثالي للمجالات المالية، الاستشارات، القانون، والوظائف القيادية.'
  },
  {
    id: 'ats_modern',
    name: 'Minimalist Tech (التقني المبسط)',
    font: 'Arial / Sans-Serif',
    desc: 'تنسيق نظيف ومرتب بمحاذاة يسارية خط Sans-serif. الأفضل للشركات الناشئة، الهندسة، ووظائف المنتجات.'
  },
  {
    id: 'ats_executive',
    name: 'Executive Slate (التنفيذي الفاخر)',
    font: 'Georgia / Serif',
    desc: 'ترويسات عريضة وقوية مع فواصل أنيقة. مصمم خصيصاً للمدراء، التنفيذيين، ورؤساء الأقسام.'
  },
  {
    id: 'ats_tech',
    name: 'Tech Startup (المطور الحديث)',
    font: 'Trebuchet MS',
    desc: 'تصميم يساري مميز مع فواصل سماوية عصرية. ممتاز للمبرمجين، مصممي واجهات المستخدم، ومحترفي التقنية.'
  },
  {
    id: 'ats_elegant',
    name: 'Elegant Garamond (الأنيق الراقي)',
    font: 'Garamond / Georgia',
    desc: 'ترويسات متمركزة ومصقولة بتصميم ناعم وجذاب. رائع لقطاعات العمليات، الموارد البشرية، والوظائف الإدارية.'
  },
  {
    id: 'ats_professional',
    name: 'Classic Professional (الاحترافي المعتمد)',
    font: 'Times New Roman / Serif',
    desc: 'تنسيق مميز بخطوط واضحة جداً وجداول بيانات مرتبة. مصمم خصيصاً ليتجاوز أدق فلاتر أنظمة ATS بنجاح.'
  }
]

const downloadImprovedCv = async () => {
    if (downloadingCv.value) return
    downloadingCv.value = true
    try {
        const response = await axios.post(
            route('resumes.download.preview', props.resume.id),
            {
                resume_sections: localAnalysis.value?.resume_sections ?? {},
                overall_score: localOverallScore.value,
                parsed_text: props.parsedText,
                template: downloadTemplate.value,
            },
            { responseType: 'blob' }
        )

        const blob = new Blob([response.data], { type: 'application/pdf' })
        const url = window.URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        const base = (props.resume.filename || 'resume').replace(/\.[^.]+$/, '')
        link.download = downloadTemplate.value === 'ats' ? `${base}_ATS_CV.pdf` : `${base}_CV.pdf`
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.URL.revokeObjectURL(url)
        showToast('Your improved CV PDF is ready.')
    } catch (err) {
        const msg = err.response?.data?.message || 'Could not generate PDF. Please try again.'
        showToast(msg)
    } finally {
        downloadingCv.value = false
    }
}

const openRewriteModal = ({ bulletText, issueId }) => {
    activeBulletText.value = bulletText
    activeIssueId.value = issueId
    isRewriteOpen.value = true
}

const showToast = (message) => {
    toastMessage.value = message
    toastVisible.value = true
    setTimeout(() => {
        toastVisible.value = false
    }, 4000)
}

const handleApplySuggestion = ({ suggestion, issueId }) => {
    const originalText = activeBulletText.value

    // 1. Update bullet in experience + sync full-text preview lines
    if (localAnalysis.value?.resume_sections?.experience) {
        let found = false
        for (const job of localAnalysis.value.resume_sections.experience) {
            if (job.bullets) {
                for (let i = 0; i < job.bullets.length; i++) {
                    const bullet = job.bullets[i]
                    if (typeof bullet === 'string' && bullet.trim() === originalText.trim()) {
                        job.bullets[i] = { text: suggestion, is_weak: false, weak_reason: '' }
                        found = true
                        break
                    } else if (bullet && typeof bullet === 'object' && bullet.text.trim() === originalText.trim()) {
                        bullet.text = suggestion
                        bullet.is_weak = false
                        bullet.weak_reason = ''
                        bullet.improved_line = null
                        found = true
                        break
                    }
                }
            }
            if (found) break
        }
    }

    // 2. Remove the issue matching this original bullet from our checklists
    localIssueChecks.value = localIssueChecks.value.map(check => {
        if (check.issues && check.issues.length > 0) {
            check.issues = check.issues.filter(issue => {
                const issueLine = issue.original_line || issue.original_text
                if (issueLine && issueLine.trim() === originalText.trim()) {
                    return false
                }
                return true
            })

            check.issue_count = check.issues.length

            if (check.issue_count === 0) {
                check.status = 'passed'
                localPassedChecks.value.push(check)
                return null // Remove the check entirely from active issue checks
            }
        }
        return check
    }).filter(Boolean)

    fixesAppliedCount.value += 1
    localAnalysis.value.score_headline = `Fix applied — ${fixesAppliedCount.value} improvement(s) on your CV`
    localAnalysis.value.score_explanation =
        'Your bullet was updated in the preview and in the ATS PDF download. Upload the CV again for a new score aligned with Resume Worded and similar tools.'

    // 5. Close the modal and show success toast
    isRewriteOpen.value = false
    showToast("Bullet point upgraded successfully.")
}

const applyAllMagicWrites = () => {
    let appliedCount = 0
    const appliedTexts = []

    // ── Build a normalised lookup: original_text → improved_line from all issue checks ──
    const improvementMap = new Map()
    for (const check of [...localIssueChecks.value, ...localPassedChecks.value]) {
        for (const issue of check.issues ?? []) {
            const orig = (issue.original_line ?? issue.original_text ?? '').trim()
            const improved = (issue.improved_line ?? issue.draft_improvement ?? '').trim()
            if (orig && improved) {
                improvementMap.set(orig.toLowerCase(), improved)
            }
        }
    }

    // ── Walk every experience bullet and apply the best available suggestion ──
    if (localAnalysis.value?.resume_sections?.experience) {
        for (const job of localAnalysis.value.resume_sections.experience) {
            if (!job.bullets) continue
            for (let i = 0; i < job.bullets.length; i++) {
                const bullet = job.bullets[i]
                if (!bullet || typeof bullet !== 'object' || !bullet.is_weak) continue

                // 1. Use the bullet's own improved_line if present
                let suggestion = bullet.improved_line?.trim() || ''

                // 2. Fall back to the issue-check lookup map
                if (!suggestion) {
                    suggestion = improvementMap.get(bullet.text?.trim().toLowerCase()) ?? ''
                }

                if (!suggestion) continue

                appliedTexts.push(bullet.text)
                bullet.text = suggestion
                bullet.is_weak = false
                bullet.weak_reason = ''
                bullet.improved_line = null
                appliedCount++
            }
        }
    }

    if (appliedCount === 0) {
        showToast("No AI suggestions found to apply. Use ✦ Magic Rewrite on individual bullets to generate improvements first.")
        return
    }

    // ── Purge applied originals from issue checklists ──
    const appliedSet = new Set(appliedTexts.map(t => t.trim().toLowerCase()))

    localIssueChecks.value = localIssueChecks.value.map(check => {
        if (check.issues && check.issues.length > 0) {
            check.issues = check.issues.filter(issue => {
                const line = (issue.original_line ?? issue.original_text ?? '').trim().toLowerCase()
                return !appliedSet.has(line)
            })
            check.issue_count = check.issues.length
            if (check.issue_count === 0) {
                check.status = 'passed'
                localPassedChecks.value.push(check)
                return null
            }
        }
        return check
    }).filter(Boolean)

    fixesAppliedCount.value += appliedCount
    localAnalysis.value.score_headline = `Fixes applied — ${fixesAppliedCount.value} improvement(s) on your CV`
    localAnalysis.value.score_explanation =
        'All automated improvements have been applied to your CV preview and will be included in the PDF download.'

    showToast(`✓ Applied ${appliedCount} CV improvement${appliedCount > 1 ? 's' : ''} successfully!`)
}

const kpiScores = computed(() => {
    const s = localScores.value || {}
    return [
        { label: 'Overall', value: s.overall ?? localOverallScore.value, color: '#4f46e5' },
        { label: 'ATS Match', value: s.ats ?? 0, color: '#0ea5e9' },
        { label: 'Recruiter', value: s.recruiter ?? 0, color: '#8b5cf6' },
        { label: 'Impact', value: s.impact ?? 0, color: '#e07b39' },
        { label: 'Brevity', value: s.brevity ?? 0, color: '#38a169' },
        { label: 'Style', value: s.style ?? 0, color: '#d946ef' },
    ]
})

const radarKeys = ['experience', 'ats', 'impact', 'skills', 'grammar', 'dates']
const radarLabels = computed(() =>
    radarKeys
        .filter((k) => props.sectionScores?.[k])
        .map((k) => k.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()))
)
const radarValues = computed(() =>
    radarKeys
        .filter((k) => props.sectionScores?.[k])
        .map((k) => Number(props.sectionScores[k]?.score ?? 0))
)

const weakBulletCount = computed(() => {
    const jobs = localAnalysis.value?.resume_sections?.experience ?? []
    let count = 0
    for (const job of jobs) {
        for (const b of job.bullets ?? []) {
            if (b?.is_weak) count++
        }
    }
    return count
})

const normalizeLine = (text) =>
    String(text || '')
        .toLowerCase()
        .replace(/[^\p{L}\p{N}\s]/gu, ' ')
        .replace(/\s+/g, ' ')
        .trim()

const weakLineMatchers = computed(() => {
    const matchers = []

    for (const job of localAnalysis.value?.resume_sections?.experience ?? []) {
        for (const bullet of job.bullets ?? []) {
            const text = typeof bullet === 'string' ? bullet : bullet?.text
            if (!text) continue
            matchers.push({
                norm: normalizeLine(text),
                reason: bullet.weak_reason || 'Needs improvement',
                weak: bullet.is_weak ?? false,
            })
        }
    }

    for (const check of localAnalysis.value?.checks ?? []) {
        for (const issue of check.issues ?? []) {
            const line = issue?.original_line || issue?.original_text
            if (!line) continue
            matchers.push({
                norm: normalizeLine(line),
                reason: issue.reason || issue.feedback || check.name || 'Needs improvement',
                weak: true,
            })
        }
    }

    return matchers
})

const matchWeakReason = (line) => {
    const norm = normalizeLine(line)
    if (!norm) return null

    for (const m of weakLineMatchers.value) {
        if (!m.weak || !m.norm) continue
        if (norm === m.norm || norm.includes(m.norm) || m.norm.includes(norm)) {
            return m.reason
        }
    }

    return null
}

const fullTextLines = computed(() => {
    if (!props.parsedText) return []
    return props.parsedText.split(/\r?\n/).map((text) => ({
        text,
        reason: matchWeakReason(text),
    }))
})

// Navigation & expansion states
const activeCheck = ref(null)
const expandedChecks = ref({})
const showAllIssues = ref(false)
const showAllPassed = ref(false)

// Colors based on Resume Worded scores ranges
const scoreColor = computed(() => {
    if (localOverallScore.value >= 90) return '#10b981' // emerald-500
    if (localOverallScore.value >= 75) return '#6366f1' // indigo-500
    if (localOverallScore.value >= 60) return '#f59e0b' // amber-500
    return '#f43f5e' // rose-500
})

// Circular indicator math (radius = 32)
const ringDashoffset = computed(() => {
    const circumference = 2 * Math.PI * 32
    return circumference - (localOverallScore.value / 100) * circumference
})

// Real-time time greeting
const greeting = computed(() => {
    const h = new Date().getHours()
    if (h < 12) return 'Good morning'
    if (h < 18) return 'Good afternoon'
    return 'Good evening'
})

// Toggling Accordion
const toggleCheck = (checkId) => {
    expandedChecks.value[checkId] = !expandedChecks.value[checkId]
}

// Scrolling Smoothly to Target Card
const scrollToCheck = (checkId) => {
    activeCheck.value = checkId
    const el = document.getElementById(`check-${checkId}`)
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' })
    }
}

// Sidebar lists limit rules
const visibleIssues = computed(() => {
    const list = [...localIssueChecks.value, ...props.lockedChecks]
    if (showAllIssues.value) return list
    return list.slice(0, 5)
})

const visiblePassed = computed(() => {
    if (showAllPassed.value) return localPassedChecks.value
    return localPassedChecks.value.slice(0, 3)
})

// Combined checks array for sequential rendering in middle panel
const allChecks = computed(() => {
    return [
        ...localIssueChecks.value,
        ...localPassedChecks.value,
        ...props.lockedChecks
    ]
})
</script>

<style scoped>
/* Left border width utility in vanilla CSS for Vue single file component compatibility */
.border-l-3 {
  border-left-width: 3px;
}

/* Core CV Preview Scoped Styles - High-Fidelity physical paper emulation */
.cv-preview {
  font-size: 10.5px;
  line-height: 1.45;
  background-color: #ffffff !important;
  border: 1px solid rgba(0, 0, 0, 0.08) !important;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.18) !important;
  border-radius: 12px !important;
  padding: 36px !important;
  min-height: 297mm;
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
}

/* Lock A4 physical page preview to always white with dark text, ignoring application dark mode background overrides */
:deep(.dark) .cv-preview {
  background-color: #ffffff !important;
  color: #111827 !important;
  border-color: rgba(0, 0, 0, 0.08) !important;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45) !important;
}

/* Specific text color overrides to bypass Tailwind dark utilities inside the physical CV preview sheet */
:deep(.dark) .cv-preview *,
.cv-preview * {
  --tw-text-opacity: 1 !important;
}

:deep(.dark) .cv-preview .cv-name,
.cv-preview .cv-name {
  color: #111827 !important;
}

:deep(.dark) .cv-preview .cv-title,
.cv-preview .cv-title {
  color: #4f46e5 !important;
}

:deep(.dark) .cv-preview .cv-contact,
.cv-preview .cv-contact {
  color: #4b5563 !important;
}

:deep(.dark) .cv-preview .cv-school,
:deep(.dark) .cv-preview .cv-entry-header,
.cv-preview .cv-school,
.cv-preview .cv-entry-header {
  color: #111827 !important;
}

:deep(.dark) .cv-preview .cv-dates,
.cv-preview .cv-dates {
  color: #6b7280 !important;
}

:deep(.dark) .cv-preview .cv-bullet-item:not(.text-rose-600),
.cv-preview .cv-bullet-item:not(.text-rose-600) {
  color: #374151 !important;
}

:deep(.dark) .cv-preview .cv-section-title,
.cv-preview .cv-section-title {
  border-color: #e5e7eb !important;
  color: #111827 !important;
}

:deep(.dark) .cv-preview .cv-section-text,
.cv-preview .cv-section-text {
  color: #4b5563 !important;
}

/* ────────────────────────────────────────────────────────
   THEME 1: HARVARD CLASSIC (.preview-ats_classic)
   ──────────────────────────────────────────────────────── */
.preview-ats_classic {
  font-family: "Times New Roman", Times, Georgia, serif !important;
  color: #111827 !important;
  background-color: #ffffff !important;
}
.preview-ats_classic .cv-name {
  font-family: "Times New Roman", Times, Georgia, serif !important;
  font-size: 16px !important;
  font-weight: bold !important;
  letter-spacing: 0.5px;
  color: #111827 !important;
}
.preview-ats_classic .cv-title {
  color: #374151 !important;
  font-size: 10px !important;
  font-weight: bold !important;
  font-style: italic;
  letter-spacing: 0.5px;
}
.preview-ats_classic .cv-contact {
  color: #4b5563 !important;
  font-size: 8.5px !important;
  border-bottom: none !important;
}
.preview-ats_classic .cv-section-title {
  font-size: 9.5px !important;
  font-weight: bold !important;
  text-transform: uppercase !important;
  border-bottom: 1px solid #111827 !important;
  padding-bottom: 2px !important;
  margin-top: 12px !important;
  margin-bottom: 6px !important;
  letter-spacing: 0.5px;
  color: #111827 !important;
}
.preview-ats_classic .cv-entry-header {
  font-size: 9.5px !important;
  font-weight: bold !important;
  color: #111827 !important;
}
.preview-ats_classic .cv-school {
  font-weight: bold !important;
}
.preview-ats_classic .cv-degree {
  font-style: italic !important;
  color: #374151 !important;
}
.preview-ats_classic .cv-dates {
  color: #111827 !important;
  font-weight: bold !important;
}
.preview-ats_classic .cv-bullet-item:not(.text-rose-600) {
  color: #374151 !important;
}

/* ────────────────────────────────────────────────────────
   THEME 2: MINIMALIST TECH (.preview-ats_modern)
   ──────────────────────────────────────────────────────── */
.preview-ats_modern {
  font-family: "Inter", "Segoe UI", Arial, sans-serif !important;
  background-color: #fafafa !important;
}
.preview-ats_modern .cv-header {
  text-align: left !important;
}
.preview-ats_modern .cv-links {
  justify-content: flex-start !important;
}
.preview-ats_modern .cv-name {
  font-family: "Inter", "Segoe UI", Arial, sans-serif !important;
  font-size: 15px !important;
  font-weight: 800 !important;
  letter-spacing: -0.2px;
  color: #09090b !important;
}
.preview-ats_modern .cv-title {
  color: #4f46e5 !important;
  font-size: 9.5px !important;
  font-weight: 700 !important;
  letter-spacing: 0.2px;
}
.preview-ats_modern .cv-contact {
  color: #71717a !important;
}
.preview-ats_modern .cv-section-title {
  font-size: 9.5px !important;
  font-weight: 800 !important;
  text-transform: uppercase !important;
  border-bottom: 2px solid #e4e4e7 !important;
  padding-bottom: 3px !important;
  margin-top: 14px !important;
  margin-bottom: 6px !important;
  color: #18181b !important;
}
.preview-ats_modern .cv-entry-header {
  font-size: 9.5px !important;
  font-weight: 700 !important;
  color: #18181b !important;
}
.preview-ats_modern .cv-dates {
  color: #71717a !important;
}
.preview-ats_modern .cv-degree {
  color: #52525b !important;
}
.preview-ats_modern .cv-bullet-item:not(.text-rose-600) {
  color: #27272a !important;
}

/* ────────────────────────────────────────────────────────
   THEME 3: EXECUTIVE SLATE (.preview-ats_executive)
   ──────────────────────────────────────────────────────── */
.preview-ats_executive {
  font-family: "Georgia", Times, serif !important;
  background-color: #ffffff !important;
}
.preview-ats_executive .cv-header {
  text-align: left !important;
}
.preview-ats_executive .cv-links {
  justify-content: flex-start !important;
}
.preview-ats_executive .cv-name {
  font-family: "Georgia", Times, serif !important;
  font-size: 16px !important;
  font-weight: 900 !important;
  color: #0f172a !important;
  border-bottom: 2px solid #334155 !important;
  padding-bottom: 4px;
}
.preview-ats_executive .cv-title {
  color: #475569 !important;
  font-size: 10px !important;
  font-weight: bold !important;
}
.preview-ats_executive .cv-contact {
  color: #475569 !important;
}
.preview-ats_executive .cv-section-title {
  font-size: 10px !important;
  font-weight: bold !important;
  text-transform: uppercase !important;
  color: #0f172a !important;
  border-bottom: 1px double #94a3b8 !important;
  padding-bottom: 3px !important;
  margin-top: 14px !important;
  margin-bottom: 8px !important;
}
.preview-ats_executive .cv-entry-header {
  font-size: 10px !important;
  font-weight: bold !important;
  color: #0f172a !important;
}
.preview-ats_executive .cv-dates {
  color: #475569 !important;
  font-weight: bold !important;
}
.preview-ats_executive .cv-degree {
  font-style: italic !important;
  color: #334155 !important;
}
.preview-ats_executive .cv-bullet-item:not(.text-rose-600) {
  color: #334155 !important;
}

/* ────────────────────────────────────────────────────────
   THEME 4: TECH STARTUP (.preview-ats_tech)
   ──────────────────────────────────────────────────────── */
.preview-ats_tech {
  font-family: "Trebuchet MS", "Lucida Grande", sans-serif !important;
  background-color: #f8fafc !important;
}
.preview-ats_tech .cv-header {
  text-align: left !important;
  border-left: 4px solid #0ea5e9;
  padding-left: 12px !important;
  border-bottom: none !important;
}
.preview-ats_tech .cv-links {
  justify-content: flex-start !important;
}
.preview-ats_tech .cv-name {
  font-family: "Trebuchet MS", sans-serif !important;
  font-size: 16px !important;
  font-weight: bold !important;
  color: #0f172a !important;
}
.preview-ats_tech .cv-title {
  color: #0284c7 !important;
  font-size: 9.5px !important;
  font-weight: bold !important;
}
.preview-ats_tech .cv-contact {
  color: #64748b !important;
}
.preview-ats_tech .cv-section-title {
  font-size: 9.5px !important;
  font-weight: 800 !important;
  text-transform: uppercase !important;
  color: #0369a1 !important;
  border-bottom: 2px solid #0ea5e9 !important;
  padding-bottom: 2px !important;
  margin-top: 14px !important;
  margin-bottom: 6px !important;
}
.preview-ats_tech .cv-entry-header {
  font-size: 9.5px !important;
  font-weight: 700 !important;
  color: #0f172a !important;
}
.preview-ats_tech .cv-dates {
  color: #0284c7 !important;
  font-weight: bold !important;
}
.preview-ats_tech .cv-degree {
  color: #475569 !important;
}
.preview-ats_tech .cv-bullet-item:not(.text-rose-600) {
  color: #334155 !important;
}

/* ────────────────────────────────────────────────────────
   THEME 5: ELEGANT GARAMOND (.preview-ats_elegant)
   ──────────────────────────────────────────────────────── */
.preview-ats_elegant {
  font-family: "Garamond", Georgia, serif !important;
  background-color: #ffffff !important;
}
.preview-ats_elegant .cv-name {
  font-family: "Garamond", Georgia, serif !important;
  font-size: 17px !important;
  font-weight: 500 !important;
  letter-spacing: 1px;
  color: #1c1917 !important;
}
.preview-ats_elegant .cv-title {
  color: #78716c !important;
  font-size: 9.5px !important;
  font-style: italic !important;
  letter-spacing: 0.5px;
}
.preview-ats_elegant .cv-contact {
  color: #78716c !important;
}
.preview-ats_elegant .cv-section-title {
  font-size: 10px !important;
  font-weight: 600 !important;
  text-transform: capitalize !important;
  font-style: italic !important;
  color: #44403c !important;
  border-bottom: 1px solid #d6d3d1 !important;
  padding-bottom: 3px !important;
  margin-top: 14px !important;
  margin-bottom: 8px !important;
  letter-spacing: 0.5px;
}
.preview-ats_elegant .cv-entry-header {
  font-size: 10px !important;
  font-weight: bold !important;
  color: #1c1917 !important;
}
.preview-ats_elegant .cv-dates {
  color: #78716c !important;
  font-style: italic !important;
}
.preview-ats_elegant .cv-degree {
  font-style: italic !important;
  color: #57534e !important;
}
.preview-ats_elegant .cv-bullet-item:not(.text-rose-600) {
  color: #44403c !important;
}

/* ────────────────────────────────────────────────────────
   THEME 6: CLASSIC PROFESSIONAL (.preview-ats_professional)
   ──────────────────────────────────────────────────────── */
.preview-ats_professional {
  font-family: "Times New Roman", Times, serif !important;
  color: #000000 !important;
  background-color: #ffffff !important;
}
.preview-ats_professional .cv-name {
  font-family: "Times New Roman", Times, serif !important;
  font-size: 17px !important;
  font-weight: bold !important;
  letter-spacing: 0.5px;
  color: #000000 !important;
}
.preview-ats_professional .cv-title {
  color: #000000 !important;
  font-size: 10px !important;
  font-weight: bold !important;
}
.preview-ats_professional .cv-contact {
  color: #000000 !important;
  font-size: 8.5px !important;
}
.preview-ats_professional .cv-section-title {
  font-size: 10px !important;
  font-weight: bold !important;
  text-transform: uppercase !important;
  border-bottom: 1.5px solid #000000 !important;
  padding-bottom: 2px !important;
  margin-top: 14px !important;
  margin-bottom: 6px !important;
  letter-spacing: 0.5px;
  color: #000000 !important;
}
.preview-ats_professional .cv-entry-header {
  font-size: 10px !important;
  font-weight: bold !important;
  color: #000000 !important;
}
.preview-ats_professional .cv-school {
  font-weight: bold !important;
}
.preview-ats_professional .cv-degree {
  font-style: italic !important;
  color: #000000 !important;
}
.preview-ats_professional .cv-dates {
  color: #000000 !important;
  font-weight: bold !important;
}
.preview-ats_professional .cv-bullet-item:not(.text-rose-600) {
  color: #000000 !important;
}
</style>
