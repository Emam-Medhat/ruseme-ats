import { ref, watch, nextTick } from 'vue'
import axios from 'axios'

const MAX_RETRIES = 2
const RETRY_DELAY_MS = 1200

/**
 * useMagicRewrite — central composable for the ✦ AI Magic Rewrite feature.
 *
 * Encapsulates:
 *  - API request lifecycle (loading / error / suggestions)
 *  - Automatic retry with exponential back-off
 *  - AbortController to cancel in-flight requests when modal is closed
 *  - Focus-trap helpers (firstFocusable / lastFocusable)
 */
export function useMagicRewrite() {
    const suggestions = ref([])
    const loading = ref(false)
    const error = ref(null)
    const retryCount = ref(0)

    /** @type {AbortController|null} */
    let abortController = null

    // ─── Private helpers ────────────────────────────────────────────────────

    const sleep = (ms) => new Promise((r) => setTimeout(r, ms))

    async function _fetchWithRetry(bulletText, attempt = 0) {
        abortController = new AbortController()

        try {
            const response = await axios.post(
                route('ai.rewrite-bullet'),
                { bullet: bulletText },
                { signal: abortController.signal }
            )

            const raw = response.data?.suggestions ?? []
            // Guard: ensure we always have exactly 3 non-empty strings
            if (!Array.isArray(raw) || raw.length === 0) {
                throw new Error('No suggestions returned from server.')
            }
            suggestions.value = raw.filter((s) => typeof s === 'string' && s.trim().length > 0).slice(0, 3)
            error.value = null
        } catch (err) {
            if (axios.isCancel(err)) return // Intentionally aborted — silent

            const isNetworkError = !err.response
            const isServerError = err.response?.status >= 500

            if ((isNetworkError || isServerError) && attempt < MAX_RETRIES) {
                retryCount.value = attempt + 1
                await sleep(RETRY_DELAY_MS * (attempt + 1))
                return _fetchWithRetry(bulletText, attempt + 1)
            }

            const serverMsg = err.response?.data?.message
            error.value = serverMsg || 'Could not reach the AI. Please check your connection and try again.'
        }
    }

    // ─── Public API ──────────────────────────────────────────────────────────

    async function fetchSuggestions(bulletText) {
        if (!bulletText?.trim()) {
            error.value = 'No bullet point text provided.'
            return
        }

        // Cancel any in-flight request
        abortController?.abort()

        loading.value = true
        error.value = null
        suggestions.value = []
        retryCount.value = 0

        try {
            await _fetchWithRetry(bulletText)
        } finally {
            loading.value = false
        }
    }

    function abort() {
        abortController?.abort()
        loading.value = false
    }

    function reset() {
        abort()
        suggestions.value = []
        error.value = null
        retryCount.value = 0
    }

    // ─── Focus-trap helpers ──────────────────────────────────────────────────

    const FOCUSABLE_SELECTORS = [
        'a[href]',
        'button:not([disabled])',
        'input:not([disabled])',
        'select:not([disabled])',
        'textarea:not([disabled])',
        '[tabindex]:not([tabindex="-1"])',
    ].join(',')

    function getFocusableElements(container) {
        if (!container) return []
        return Array.from(container.querySelectorAll(FOCUSABLE_SELECTORS)).filter(
            (el) => !el.closest('[aria-hidden="true"]')
        )
    }

    function trapFocus(container, event) {
        const focusable = getFocusableElements(container)
        if (focusable.length === 0) return

        const first = focusable[0]
        const last = focusable[focusable.length - 1]

        if (event.shiftKey) {
            if (document.activeElement === first) {
                event.preventDefault()
                last.focus()
            }
        } else {
            if (document.activeElement === last) {
                event.preventDefault()
                first.focus()
            }
        }
    }

    async function focusFirst(container) {
        await nextTick()
        const focusable = getFocusableElements(container)
        focusable[0]?.focus()
    }

    return {
        // State
        suggestions,
        loading,
        error,
        retryCount,
        // Actions
        fetchSuggestions,
        abort,
        reset,
        // Focus-trap
        trapFocus,
        focusFirst,
    }
}
