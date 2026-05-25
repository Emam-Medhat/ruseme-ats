import { defineStore } from 'pinia';

export const useAppStore = defineStore('app', {
    state: () => ({
        locale: typeof window !== 'undefined'
            ? (localStorage.getItem('cv_locale') || 'en')
            : 'en',
        theme: 'light',
    }),
    getters: {
        isRtl: (state) => state.locale === 'ar',
        isDark: (state) => false,
    },
    actions: {
        setLocale(locale) {
            this.locale = locale;
            if (typeof document !== 'undefined') {
                document.documentElement.lang = locale;
                document.documentElement.dir = locale === 'ar' ? 'rtl' : 'ltr';
                localStorage.setItem('cv_locale', locale);
            }
        },
        toggleTheme() {
            // Force light mode
            this.theme = 'light';
            if (typeof document !== 'undefined') {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('cv_theme', 'light');
            }
        },
        initFromPage(pageProps) {
            const locale = pageProps?.cvGenius?.locale;
            if (locale) {
                this.setLocale(locale);
            }
            if (typeof document !== 'undefined') {
                document.documentElement.classList.remove('dark');
            }
        },
    },
});
