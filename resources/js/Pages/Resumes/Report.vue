<template>
  <Head title="Resume Analysis Report" />

  <!-- ================= TOP NAVBAR ================= -->
  <nav class="fixed inset-x-0 top-0 z-50 border-b border-[#e5e3df] bg-white/95 backdrop-blur-sm h-[52px] flex items-center shrink-0">
    <div class="w-full flex items-center justify-between px-5 gap-4">
      <!-- Logo -->
      <Link href="/" class="flex items-center gap-2 text-sm font-black text-[#4f46e5] shrink-0">
        <span>⚡</span>
        <span class="hidden sm:inline">CV Genius AI</span>
      </Link>

      <!-- Nav Links -->
      <div class="hidden md:flex items-center gap-5 text-xs font-semibold text-[#6b7280]">
        <Link :href="route('dashboard')" class="hover:text-[#4f46e5] transition-colors">Dashboard</Link>
        <Link :href="route('upload')" class="hover:text-[#4f46e5] transition-colors">Upload</Link>
        <Link :href="route('history')" class="hover:text-[#4f46e5] transition-colors">History</Link>
        <Link :href="route('pricing')" class="hover:text-[#4f46e5] transition-colors">Pricing</Link>
      </div>

      <!-- Right: Resume title pill + user -->
      <div class="flex items-center gap-3 shrink-0">
        <span class="hidden sm:inline-flex items-center gap-1.5 text-[10px] font-bold text-[#4f46e5] bg-[#eef2ff] border border-[#c7d2fe] px-3 py-1 rounded-full truncate max-w-[200px]">
          📄 {{ resume.filename || resume.title }}
        </span>
        <Link
          :href="route('dashboard')"
          class="text-[10px] font-bold text-[#6b7280] hover:text-[#1a1a2e] transition-colors flex items-center gap-1"
        >
          ← Dashboard
        </Link>
      </div>
    </div>
  </nav>

  <div class="flex w-full overflow-hidden bg-[#f8f7f5] font-sans antialiased text-[#1a1a2e]" style="height: 100dvh; padding-top: 52px;">
    
    <!-- ================= LEFT SIDEBAR (220px) ================= -->
    <aside class="w-[220px] h-screen bg-white border-r border-[#e5e3df] flex flex-col justify-between shrink-0 z-10 shadow-sm">
      <div class="p-5 flex flex-col items-center border-b border-[#e5e3df]">
        <!-- Circular Score Ring -->
        <div class="relative w-24 h-24 mb-3 flex items-center justify-center">
          <svg width="88" height="88" viewBox="0 0 80 80">
            <circle cx="40" cy="40" r="32" fill="none" stroke="#f1efe9" stroke-width="6"/>
            <circle cx="40" cy="40" r="32" fill="none" 
                    :stroke="scoreColor" 
                    stroke-width="6"
                    stroke-linecap="round"
                    :stroke-dasharray="`${2 * Math.PI * 32}`"
                    :stroke-dashoffset="ringDashoffset"
                    style="transform: rotate(-90deg); transform-origin: center; transition: stroke-dashoffset 1.2s ease-in-out;"/>
          </svg>
          <div class="absolute inset-0 flex flex-col items-center justify-center">
            <span class="text-3xl font-extrabold text-[#1a1a2e]" :style="{ color: scoreColor }">
              {{ localOverallScore }}
            </span>
            <span class="text-[9px] font-bold text-[#6b7280] tracking-wider uppercase -mt-0.5">
              Overall
            </span>
          </div>
        </div>
        <div class="text-[10px] text-[#6b7280] uppercase tracking-widest font-bold">
          CV Genius Score
        </div>
      </div>

      <!-- Scrollable checklist categories -->
      <div class="flex-1 overflow-y-auto px-3 py-4 space-y-5">
        <!-- TOP FIXES -->
        <div>
          <h3 class="text-[10px] font-extrabold text-[#9ca3af] tracking-widest uppercase mb-2 px-2">
            Top Fixes
          </h3>
          <div class="space-y-1">
            <button v-for="check in visibleIssues" :key="check.id" 
                    @click="scrollToCheck(check.id)"
                    :class="['w-full text-left px-2.5 py-2 rounded-xl text-xs font-semibold flex items-center justify-between transition-all duration-200', activeCheck === check.id ? 'bg-[#f1efe9] text-[#1a1a2e]' : 'text-[#6b7280] hover:bg-[#f8f7f5] hover:text-[#1a1a2e]']">
              <span class="truncate pr-1">{{ check.name }}</span>
              <span v-if="check.status === 'issue'" class="bg-[#e53e3e] text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full shrink-0 min-w-[16px] text-center">
                {{ check.issue_count }}
              </span>
              <span v-else-if="check.status === 'locked'" class="text-zinc-400 text-[10px] shrink-0">
                🔒
              </span>
            </button>
            <button v-if="issueChecks.length > 5 && !showAllIssues" 
                    @click="showAllIssues = true"
                    class="w-full text-left px-2.5 py-1.5 text-[10px] font-bold text-[#4f46e5] hover:underline">
              {{ (issueChecks.length + lockedChecks.length) - 5 }} MORE ISSUES +
            </button>
          </div>
        </div>

        <!-- COMPLETED -->
        <div>
          <h3 class="text-[10px] font-extrabold text-[#9ca3af] tracking-widest uppercase mb-2 px-2">
            Completed
          </h3>
          <div class="space-y-1">
            <button v-for="check in visiblePassed" :key="check.id" 
                    @click="scrollToCheck(check.id)"
                    :class="['w-full text-left px-2.5 py-2 rounded-xl text-xs font-semibold flex items-center justify-between transition-all duration-200', activeCheck === check.id ? 'bg-[#f1efe9] text-[#1a1a2e]' : 'text-[#6b7280] hover:bg-[#f8f7f5] hover:text-[#1a1a2e]']">
              <span class="truncate pr-1">{{ check.name }}</span>
              <span class="bg-[#38a169]/10 text-[#38a169] text-[9px] font-extrabold px-1.5 py-0.5 rounded-full shrink-0">
                10
              </span>
            </button>
            <button v-if="passedChecks.length > 3 && !showAllPassed" 
                    @click="showAllPassed = true"
                    class="w-full text-left px-2.5 py-1.5 text-[10px] font-bold text-[#38a169] hover:underline">
              {{ passedChecks.length - 3 }} MORE CHECKS +
            </button>
          </div>
        </div>
      </div>

      <!-- Dashboard Back link -->
      <div class="p-4 border-t border-[#e5e3df]">
        <Link :href="route('dashboard')" class="w-full py-2.5 px-4 bg-[#f8f7f5] border border-[#e5e3df] text-[#1a1a2e] text-xs font-bold rounded-xl flex items-center justify-center gap-2 hover:bg-[#f1efe9] transition-all">
          ← Dashboard
        </Link>
      </div>
    </aside>

    <!-- ================= MIDDLE PANEL (flex-1) ================= -->
    <main class="flex-1 h-screen overflow-y-auto bg-[#f8f7f5] px-8 py-7 flex flex-col space-y-6">
      
      <!-- Greeting Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-extrabold text-[#1a1a2e] tracking-tight">
            {{ greeting }}, {{ userName }}.
          </h2>
          <p class="text-xs text-[#6b7280] mt-0.5">Welcome to your real-time resume review.</p>
          
          <!-- AI Contributors Ensemble Section -->
          <div v-if="localAnalysis?.ai_providers_list && localAnalysis.ai_providers_list.length" class="mt-2 flex items-center gap-1.5 flex-wrap">
            <span class="text-[10px] text-[#6b7280] font-medium">Analyzed using:</span>
            <div class="flex items-center gap-1 flex-wrap">
              <span v-for="prov in localAnalysis.ai_providers_list" :key="prov" class="inline-flex items-center gap-1 rounded bg-[#4f46e5]/10 px-2 py-0.5 font-bold text-[9px] text-[#4f46e5] border border-[#4f46e5]/20">
                <span class="h-1.5 w-1.5 rounded-full bg-[#38a169]"></span>
                {{ prov }}
              </span>
            </div>
          </div>
        </div>
        <button class="px-3.5 py-1.5 border border-[#e5e3df] bg-white hover:bg-zinc-50 rounded-lg text-[10px] font-bold tracking-wider text-[#6b7280] uppercase transition-colors">
          How It Works
        </button>
      </div>

      <div class="flex flex-wrap gap-2 shrink-0">
        <Link :href="route('resumes.target', resume.id)" class="text-[10px] font-bold px-3 py-1.5 rounded-lg border border-[#4f46e5] text-[#4f46e5] hover:bg-[#4f46e5]/5">Job match</Link>
        <button type="button" @click="downloadImprovedCv" class="text-[10px] font-bold px-3 py-1.5 rounded-lg bg-[#4f46e5] text-white hover:bg-[#3f37c9]">
          ↓ Download CV
        </button>
        <Link :href="route('upload')" class="text-[10px] font-bold px-3 py-1.5 rounded-lg border border-[#e5e3df] hover:bg-white">Re-upload</Link>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 shrink-0">
        <KpiCard v-for="kpi in kpiScores" :key="kpi.label" :label="kpi.label" :value="kpi.value" :accent="kpi.color" />
      </div>

      <div v-if="executiveSummary" class="bg-white rounded-2xl border border-[#e5e3df] p-4 shadow-sm shrink-0">
        <h3 class="text-[10px] font-black uppercase tracking-wider text-[#6b7280] mb-1">Executive summary</h3>
        <p class="text-xs text-[#1a1a2e] leading-relaxed">{{ executiveSummary }}</p>
      </div>

      <div v-if="radarLabels.length" class="bg-white rounded-2xl border border-[#e5e3df] p-4 shadow-sm shrink-0">
        <h3 class="text-[10px] font-black uppercase tracking-wider text-[#6b7280] mb-2">Section radar</h3>
        <RadarChart :labels="radarLabels" :values="radarValues" :height="200" />
      </div>

      <!-- Score Tabs -->
      <div class="flex border-b border-[#e5e3df] gap-6 text-xs font-bold shrink-0">
        <button class="pb-2 border-b-2 border-[#4f46e5] text-[#4f46e5]">Latest Score</button>
        <button class="pb-2 text-zinc-400 cursor-not-allowed" disabled>Previous Score</button>
      </div>

      <!-- Score Banner Card -->
      <div class="bg-white rounded-2xl border border-[#e5e3df] p-6 shadow-sm flex flex-col">
        <h3 class="text-base font-extrabold text-[#1a1a2e] mb-1">
          {{ localAnalysis.score_headline || 'Your resume is being evaluated.' }}
        </h3>
        <p class="text-xs text-[#6b7280] leading-relaxed">
          {{ localAnalysis.score_explanation || 'Please follow our key fixes below to immediately increase your score.' }}
        </p>

        <!-- Gradient Score Bar -->
        <div class="relative w-full h-3 rounded-full bg-gradient-to-r from-[#e53e3e] via-[#e07b39] to-[#38a169] mb-5 mt-5">
          <!-- Marker at localOverallScore% -->
          <div class="absolute w-5 h-5 rounded-full bg-[#4f46e5] border-2 border-white shadow-md top-1/2 -translate-y-1/2 -translate-x-1/2 flex items-center justify-center transition-all duration-1000"
               :style="{ left: `${localOverallScore}%` }">
            <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
          </div>
        </div>

        <!-- Yellow Tip Box -->
        <div class="bg-[#fef8e6] border border-[#f5e3b5] rounded-xl p-4 flex items-start gap-3 mt-1">
          <span class="text-base leading-none">💡</span>
          <p class="text-[11px] text-[#856404] leading-relaxed">
            <span class="font-bold">Recommendation:</span> 80% of candidates increase their score by 20+ points with 3 minor revisions. Reword passive verbs, add metrics, and tailor your profile for maximum recruiter impact.
          </p>
        </div>
      </div>

      <!-- Steps Checklist List -->
      <div class="space-y-4">
        <h3 class="text-xs font-black text-[#6b7280] uppercase tracking-wider">
          Steps to increase your score
        </h3>
        
        <div class="space-y-3">
          <div v-for="check in allChecks" :key="check.id" :id="`check-${check.id}`"
               :class="['bg-white rounded-2xl border border-[#e5e3df] p-5 shadow-sm transition-all duration-300', activeCheck === check.id ? 'ring-2 ring-[#4f46e5] border-transparent' : '']">
            
            <div class="flex items-start gap-4 justify-between">
              
              <!-- Check Left Icon Indicator -->
              <div :class="['w-8 h-8 rounded-full flex items-center justify-center shrink-0 font-extrabold text-sm', 
                            check.status === 'passed' ? 'bg-[#f0faf4] text-[#38a169]' : 
                            check.status === 'locked' ? 'bg-zinc-100 text-zinc-400' : 'bg-[#fff5f5] text-[#e53e3e]']">
                <span v-if="check.status === 'passed'">✓</span>
                <span v-else-if="check.status === 'locked'">🔒</span>
                <span v-else>✗</span>
              </div>

              <!-- Check Content Details -->
              <div class="flex-1 min-w-0">
                <h4 class="text-sm font-bold text-[#1a1a2e] flex items-center gap-2">
                  {{ check.title || check.name }}
                  <span v-if="check.status === 'locked'" class="text-[9px] bg-zinc-100 text-zinc-500 font-bold px-1.5 py-0.5 rounded uppercase tracking-wider">
                    PRO FEATURE
                  </span>
                </h4>
                <p class="text-xs text-[#6b7280] mt-0.5 leading-relaxed">{{ check.description }}</p>

                <div v-if="check.id === 'job_fit'" class="mt-3">
                  <Link :href="route('resumes.target', resume.id)" 
                        class="inline-flex items-center gap-1.5 bg-[#4f46e5] text-white text-xs font-bold px-4 py-2 rounded-xl shadow-md shadow-[#4f46e5]/15 hover:bg-[#3f37c9] active:scale-[0.98] transition-all">
                    🎯 Scan against Job Description
                  </Link>
                </div>

                <!-- Accordion details for check issues -->
                <div v-if="check.issues && check.issues.length > 0" class="mt-3">
                  <button @click="toggleCheck(check.id)" 
                          class="text-xs text-[#4f46e5] font-bold flex items-center gap-1 hover:underline">
                    {{ expandedChecks[check.id] ? 'Hide details ▲' : 'Show details ▼' }}
                  </button>

                  <div v-if="expandedChecks[check.id]" class="mt-4 space-y-3">
                    <div v-for="(issue, index) in check.issues" :key="index"
                         class="bg-[#f8f7f5] rounded-xl p-4 border border-[#e5e3df]/60 space-y-3">
                      <!-- Original Line -->
                      <div v-if="issue.original_line" class="border-l-3 border-[#e53e3e] pl-3 py-1.5 bg-[#fff5f5]/60 pr-3 rounded-r-lg flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                          <span class="text-[9px] font-black text-[#e53e3e] uppercase tracking-wider block mb-0.5">Original Line</span>
                          <p class="text-xs text-[#1a1a2e] font-mono leading-relaxed break-words">{{ issue.original_line }}</p>
                        </div>
                        <MagicRewriteButton 
                          :bullet-text="issue.original_line" 
                          :issue-id="check.id" 
                          @open="openRewriteModal"
                        />
                      </div>
                      <!-- Improved Line -->
                      <div v-if="issue.improved_line" class="border-l-3 border-[#38a169] pl-3 py-1 bg-[#f0faf4]/60 pr-2 rounded-r-lg">
                        <span class="text-[9px] font-black text-[#38a169] uppercase tracking-wider block mb-0.5">Suggested Rewrite</span>
                        <p class="text-xs text-[#1a1a2e] font-mono font-semibold leading-relaxed">{{ issue.improved_line }}</p>
                      </div>
                      <!-- Feedback Reason -->
                      <div class="text-[11px] text-[#6b7280] italic leading-relaxed pl-3">
                        💡 {{ issue.reason }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Right badge points value -->
              <div class="shrink-0 text-right">
                <span v-if="check.status === 'issue'" class="bg-[#fff5f5] text-[#e53e3e] border border-[#ef4444]/20 text-[10px] font-extrabold px-2.5 py-1 rounded-full">
                  {{ check.points_impact }} pts
                </span>
                <span v-else-if="check.status === 'passed'" class="bg-[#f0faf4] text-[#38a169] border border-[#38a169]/20 text-[10px] font-extrabold px-2.5 py-1 rounded-full">
                  +10 pts
                </span>
                <span v-else-if="check.status === 'locked'" class="bg-zinc-50 border border-zinc-200 text-zinc-500 text-[10px] font-extrabold px-2.5 py-1 rounded-full flex items-center gap-1 cursor-pointer hover:bg-zinc-100">
                  🔒 Unlock
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- ================= RIGHT PANEL (420px) — full CV + inline errors ================= -->
    <aside class="w-[420px] h-screen bg-white border-l border-[#e5e3df] flex flex-col shrink-0 z-10 shadow-sm min-h-0">
      
      <!-- Top Action Buttons -->
      <div class="p-5 border-b border-[#e5e3df] grid grid-cols-2 gap-3 shrink-0 bg-white">
        <!-- RESUME REWRITER → navigates to Upload page for a new CV -->
        <Link
          :href="route('upload')"
          class="bg-[#4f46e5] text-white py-2 px-3 rounded-xl text-xs font-bold shadow-md shadow-[#4f46e5]/10 flex items-center justify-center gap-1.5 hover:bg-[#3f37c9] active:scale-[0.97] transition-all"
        >
          ✦ RESUME REWRITER
        </Link>
        <button 
          @click="applyAllMagicWrites"
          class="border border-[#4f46e5] text-[#4f46e5] py-2 px-3 rounded-xl text-xs font-bold hover:bg-[#4f46e5]/5 active:scale-[0.97] transition-all"
        >
          ✎ MAGIC WRITE
        </button>
      </div>

      <!-- CV Preview Read-Only Container -->
      <div class="px-4 py-2 border-b border-[#e5e3df] bg-white flex gap-2 shrink-0">
        <button type="button" @click="cvView = 'structured'"
                :class="['flex-1 py-1.5 text-[10px] font-bold rounded-lg', cvView === 'structured' ? 'bg-[#4f46e5] text-white' : 'bg-[#f8f7f5] text-zinc-500']">
          Formatted CV
        </button>
        <button type="button" @click="cvView = 'full'"
                :class="['flex-1 py-1.5 text-[10px] font-bold rounded-lg', cvView === 'full' ? 'bg-[#4f46e5] text-white' : 'bg-[#f8f7f5] text-zinc-500']">
          Full text
        </button>
      </div>

      <p v-if="cvView === 'structured' && weakBulletCount > 0" class="px-4 py-1.5 text-[10px] text-[#e53e3e] font-semibold bg-[#fff5f5] border-b border-[#fecaca] shrink-0">
        {{ weakBulletCount }} line(s) highlighted — scroll to see your full CV
      </p>

      <div class="flex-1 min-h-0 overflow-y-auto bg-[#f8f7f5] p-4 pb-8 flex flex-col items-stretch">
        
        <!-- Simulated Paper Sheet -->
        <div v-if="cvView === 'full'" class="w-full bg-white border border-[#e5e3df] shadow-sm rounded-xl p-4 text-[9px] leading-relaxed font-mono mb-4 space-y-0.5">
          <template v-if="fullTextLines.length">
            <p v-for="(row, idx) in fullTextLines" :key="idx"
               :class="row.reason ? 'text-[#e53e3e] bg-[#fff5f5] px-2 py-1 rounded border-l-[3px] border-[#e53e3e] whitespace-pre-wrap' : 'text-[#1a1a2e] whitespace-pre-wrap'">
              {{ row.text || ' ' }}
              <span v-if="row.reason" class="block text-[7px] font-bold mt-0.5">← {{ row.reason }}</span>
            </p>
          </template>
          <p v-else class="text-zinc-400 text-center py-8">No text extracted from PDF.</p>
        </div>

        <div v-show="cvView === 'structured'" class="w-full bg-white border border-[#e5e3df] shadow-sm rounded-xl p-5 text-[10px] text-[#1a1a2e] flex flex-col space-y-4 relative pb-8">
          
          <div v-if="!localAnalysis.resume_sections" class="text-center text-zinc-400 py-20">
            No CV preview data available.
          </div>

          <template v-else>
            <!-- Header Block -->
            <div class="text-center pb-2 border-b border-[#f1efe9]">
              <h2 class="text-sm font-extrabold text-[#1a1a2e] uppercase tracking-tight">
                {{ localAnalysis.resume_sections.name || userName }}
              </h2>
              <h3 class="text-[9px] font-bold text-[#4f46e5] uppercase mt-0.5 tracking-wider">
                {{ localAnalysis.resume_sections.title || 'Candidate Profile' }}
              </h3>
              <p class="text-[8px] text-[#6b7280] mt-1 font-medium">
                {{ localAnalysis.resume_sections.contact }}
              </p>
              
              <!-- External links -->
              <div v-if="localAnalysis.resume_sections.links && localAnalysis.resume_sections.links.length > 0" 
                   class="flex justify-center gap-2 mt-1.5 flex-wrap">
                <a v-for="link in localAnalysis.resume_sections.links" :key="link"
                   :href="link" target="_blank"
                   class="text-[7.5px] text-[#4f46e5] hover:underline font-mono truncate max-w-[150px]">
                  {{ link }}
                </a>
              </div>
            </div>

            <!-- Profile Summary Section -->
            <div v-if="localAnalysis.resume_sections.summary_text">
              <h4 class="text-[8.5px] font-black uppercase text-[#1a1a2e] tracking-wider mb-1">
                Professional Summary
              </h4>
              <p class="text-[#6b7280] text-[8.5px] leading-relaxed">
                {{ localAnalysis.resume_sections.summary_text }}
              </p>
            </div>

            <!-- Experience Section -->
            <div v-if="localAnalysis.resume_sections.experience && localAnalysis.resume_sections.experience.length > 0">
              <h4 class="text-[8.5px] font-black uppercase text-[#1a1a2e] tracking-wider mb-2 border-b border-[#f1efe9] pb-0.5">
                Work Experience
              </h4>
              
              <div class="space-y-3.5">
                <div v-for="(job, jobIdx) in localAnalysis.resume_sections.experience" :key="jobIdx">
                  <div class="flex justify-between items-center text-[9px] font-extrabold text-[#1a1a2e]">
                    <span>{{ job.job_title }} at {{ job.company }}</span>
                    <span class="text-[#6b7280] font-bold text-[8px]">{{ job.dates }}</span>
                  </div>
                  
                  <ul class="list-disc pl-3.5 mt-1 space-y-2">
                    <li v-for="(bullet, bulletIdx) in job.bullets" :key="bulletIdx"
                        :class="['text-[8.5px] leading-relaxed pl-0.5 list-none relative group/item pr-20', bullet.is_weak ? 'text-[#e53e3e] bg-[#fff5f5] px-2 py-1.5 rounded-lg border-l-[3px] border-[#e53e3e]' : 'text-[#555a64]']">
                      <span class="block pr-4">{{ typeof bullet === 'string' ? bullet : bullet.text }}</span>
                      
                      <!-- Inline Hover Magic Rewrite Button! -->
                      <div v-if="bullet.is_weak" class="absolute top-1.5 right-1.5 opacity-0 group-hover/item:opacity-100 transition-opacity duration-200 z-10 shrink-0">
                        <MagicRewriteButton 
                          :bullet-text="typeof bullet === 'string' ? bullet : bullet.text" 
                          :issue-id="bulletIdx" 
                          @open="openRewriteModal"
                        />
                      </div>

                      <span v-if="bullet.is_weak" class="mt-1 block text-[7px] font-bold text-[#e53e3e]">
                        ← {{ bullet.weak_reason || 'Needs improvement' }}
                      </span>
                      <span v-if="bullet.is_weak && bullet.improved_line" class="mt-1 block text-[7px] text-[#38a169] font-semibold border-t border-[#fecaca]/50 pt-1">
                        ✓ Suggested: {{ bullet.improved_line }}
                      </span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Skills Section -->
            <div v-if="localAnalysis.resume_sections.skills_text">
              <h4 class="text-[8.5px] font-black uppercase text-[#1a1a2e] tracking-wider mb-1 border-b border-[#f1efe9] pb-0.5">
                Skills & Technologies
              </h4>
              <p class="text-[#555a64] text-[8.5px] leading-relaxed">
                {{ localAnalysis.resume_sections.skills_text }}
              </p>
            </div>

            <!-- Education Section -->
            <div v-if="localAnalysis.resume_sections.education">
              <h4 class="text-[8.5px] font-black uppercase text-[#1a1a2e] tracking-wider mb-1 border-b border-[#f1efe9] pb-0.5">
                Education
              </h4>
              <p class="text-[#555a64] text-[8.5px] leading-relaxed whitespace-pre-wrap">
                {{ localAnalysis.resume_sections.education }}
              </p>
            </div>

            <div v-if="localAnalysis.resume_sections.projects_text">
              <h4 class="text-[8.5px] font-black uppercase text-[#1a1a2e] tracking-wider mb-1 border-b border-[#f1efe9] pb-0.5">Projects</h4>
              <p class="text-[#555a64] text-[8.5px] leading-relaxed whitespace-pre-wrap">{{ localAnalysis.resume_sections.projects_text }}</p>
            </div>

            <div v-if="localAnalysis.resume_sections.languages_text">
              <h4 class="text-[8.5px] font-black uppercase text-[#1a1a2e] tracking-wider mb-1 border-b border-[#f1efe9] pb-0.5">Languages</h4>
              <p class="text-[#555a64] text-[8.5px] leading-relaxed whitespace-pre-wrap">{{ localAnalysis.resume_sections.languages_text }}</p>
            </div>

            <div v-if="localAnalysis.resume_sections.certifications_text">
              <h4 class="text-[8.5px] font-black uppercase text-[#1a1a2e] tracking-wider mb-1 border-b border-[#f1efe9] pb-0.5">Certifications</h4>
              <p class="text-[#555a64] text-[8.5px] leading-relaxed whitespace-pre-wrap">{{ localAnalysis.resume_sections.certifications_text }}</p>
            </div>
          </template>
        </div>
      </div>

      <!-- Locked Score Info / Upgrade call-out -->
      <div class="p-4 border-t border-[#e5e3df] bg-[#f8f7f5] shrink-0 space-y-3">
        <label class="text-[9px] font-bold text-[#6b7280] uppercase tracking-wider block">
          Select ATS-Safe Template (Choose 1 of 5)
        </label>
        
        <!-- Premium Visual Selector Grid -->
        <div class="grid grid-cols-1 gap-2 max-h-[220px] overflow-y-auto pr-1">
          <button 
            v-for="tpl in atsTemplates" 
            :key="tpl.id"
            type="button"
            @click="downloadTemplate = tpl.id"
            :class="[
              'w-full text-left p-2.5 rounded-xl border transition-all duration-200 flex items-start justify-between gap-3 group',
              downloadTemplate === tpl.id 
                ? 'border-[#4f46e5] bg-[#4f46e5]/5 shadow-sm shadow-[#4f46e5]/5 ring-1 ring-[#4f46e5]/30' 
                : 'border-[#e5e3df] bg-white hover:bg-[#f1efe9]/30 hover:border-zinc-300'
            ]"
          >
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-[11px] font-bold text-[#1a1a2e]">{{ tpl.name }}</span>
                <span class="text-[7.5px] px-1 py-0.2 rounded bg-[#f8f7f5] text-[#6b7280] border border-[#e5e3df] font-mono leading-none">
                  {{ tpl.font }}
                </span>
              </div>
              <p class="text-[8.5px] text-[#6b7280] mt-1 leading-relaxed group-hover:text-zinc-700">
                {{ tpl.desc }}
              </p>
            </div>
            
            <!-- Checkmark Indicator -->
            <div :class="[
              'w-4 h-4 rounded-full flex items-center justify-center shrink-0 border transition-all',
              downloadTemplate === tpl.id
                ? 'bg-[#4f46e5] border-[#4f46e5] text-white scale-110 shadow-sm shadow-[#4f46e5]/20'
                : 'border-[#e5e3df] bg-[#f8f7f5]'
            ]">
              <span v-if="downloadTemplate === tpl.id" class="text-[8px] font-bold">✓</span>
            </div>
          </button>
        </div>

        <button
          type="button"
          :disabled="downloadingCv"
          @click="downloadImprovedCv"
          class="w-full py-3 bg-[#4f46e5] text-white text-xs font-bold rounded-xl shadow-md shadow-[#4f46e5]/20 hover:bg-[#3f37c9] transition-all disabled:opacity-60 flex items-center justify-center gap-2"
        >
          <svg v-if="!downloadingCv" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l4-4m-4 4l-4-4M4 21h16" />
          </svg>
          <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          {{ downloadingCv ? 'Preparing PDF…' : 'Download improved CV (PDF)' }}
        </button>
        <p class="text-[8.5px] text-center text-[#6b7280] leading-relaxed">
          ✓ Single column · ATS-optimized format · includes applied Magic Write improvements
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
        class="fixed top-5 right-5 z-[200] max-w-sm w-full bg-zinc-950/90 border border-violet-500/30 rounded-2xl p-4 shadow-xl shadow-violet-500/5 backdrop-blur-md flex items-center gap-3.5"
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
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, Head } from '@inertiajs/vue3'
import axios from 'axios'
import KpiCard from '@/Components/CvGenius/KpiCard.vue'
import RadarChart from '@/Components/CvGenius/RadarChart.vue'
import MagicRewriteButton from '@/Components/CvGenius/MagicRewriteButton.vue'
import MagicRewriteModal from '@/Components/CvGenius/MagicRewriteModal.vue'

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

const atsTemplates = [
  {
    id: 'ats_classic',
    name: 'Harvard Classic',
    font: 'Times New Roman',
    desc: 'Traditional serif layout. Perfect for finance, consulting, legal, and executive roles.'
  },
  {
    id: 'ats_modern',
    name: 'Minimalist Tech',
    font: 'Arial / Sans-Serif',
    desc: 'Clean left-aligned sans-serif layout. Best for startups, engineering, and product roles.'
  },
  {
    id: 'ats_executive',
    name: 'Executive Slate',
    font: 'Georgia / Serif',
    desc: 'Strong bold headers with thin dividers. Tailored for directors, managers, and leaders.'
  },
  {
    id: 'ats_tech',
    name: 'Tech Startup',
    font: 'Trebuchet MS',
    desc: 'Crisp left-aligned layout with modern sky-blue dividers. Excellent for developers.'
  },
  {
    id: 'ats_elegant',
    name: 'Elegant Garamond',
    font: 'Garamond / Georgia',
    desc: 'Centered refined headings with elegant minimal layout. Great for operations & HR.'
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
        { label: 'ATS', value: s.ats ?? 0, color: '#0ea5e9' },
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
                weak: Boolean(bullet.is_weak),
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
    if (localOverallScore.value >= 75) return '#38a169'
    if (localOverallScore.value >= 50) return '#e07b39'
    return '#e53e3e'
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
</style>
