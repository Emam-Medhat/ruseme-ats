import { defineStore } from 'pinia';

const STEPS = [
    { id: 'parse', label: 'Parsing CV structure...' },
    { id: 'ats', label: 'Checking ATS readiness...' },
    { id: 'keywords', label: 'Reviewing keyword targeting...' },
    { id: 'format', label: 'Detecting formatting issues...' },
    { id: 'impact', label: 'Evaluating impact and readability...' },
    { id: 'hierarchy', label: 'Assessing section hierarchy...' },
    { id: 'compile', label: 'Compiling your audit preview...' },
];

const MIN_VISIBLE_MS = 2800;

export const useAnalysisLoaderStore = defineStore('analysisLoader', {
    state: () => ({
        visible: false,
        progress: 0,
        activeIndex: 0,
        startedAt: 0,
        _timer: null,
    }),

    getters: {
        steps: () => STEPS,
        stepState: (state) => (index) => {
            if (index < state.activeIndex) return 'done';
            if (index === state.activeIndex) return 'active';
            return 'pending';
        },
    },

    actions: {
        _clearTimer() {
            if (this._timer) {
                clearInterval(this._timer);
                this._timer = null;
            }
        },

        _tick() {
            if (this.activeIndex < STEPS.length - 1) {
                this.activeIndex += 1;
                this.progress = Math.min(92, Math.round(((this.activeIndex + 1) / STEPS.length) * 100));
            }
        },

        start() {
            this._clearTimer();
            this.visible = true;
            this.progress = 10;
            this.activeIndex = 0;
            this.startedAt = Date.now();
            this._timer = setInterval(() => this._tick(), 1800);
        },

        finish() {
            this._clearTimer();
            this.activeIndex = STEPS.length - 1;
            this.progress = 100;
        },

        stop() {
            this._clearTimer();
            const elapsed = Date.now() - this.startedAt;
            const wait = Math.max(0, MIN_VISIBLE_MS - elapsed);

            setTimeout(() => {
                this.visible = false;
                this.progress = 0;
                this.activeIndex = 0;
                this.startedAt = 0;
            }, wait);
        },
    },
});
