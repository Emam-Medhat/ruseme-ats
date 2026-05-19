import { defineStore } from 'pinia';

export const useAppStore = defineStore('app', {
    state: () => ({
        locale: typeof window !== 'undefined'
            ? (localStorage.getItem('cv_locale') || 'en')
            : 'en',
        theme: typeof window !== 'undefined'
            ? (localStorage.getItem('cv_theme') || 'light')
            : 'light',
    }),
    getters: {
        isRtl: (state) => state.locale === 'ar',
        isDark: (state) => state.theme === 'dark',
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
            this.theme = this.theme === 'dark' ? 'light' : 'dark';
            if (typeof document !== 'undefined') {
                document.documentElement.classList.toggle('dark', this.theme === 'dark');
                localStorage.setItem('cv_theme', this.theme);
            }
        },
        initFromPage(pageProps) {
            const locale = pageProps?.cvGenius?.locale;
            if (locale) {
                this.setLocale(locale);
            }
            if (typeof document !== 'undefined') {
                document.documentElement.classList.toggle('dark', this.theme === 'dark');
            }
        },
    },
});
