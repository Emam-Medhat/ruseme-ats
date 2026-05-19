import { computed } from 'vue';
import { useAppStore } from '@/stores/app';
import en from '@/i18n/en.json';
import ar from '@/i18n/ar.json';

const catalogs = { en, ar };

export function useI18n() {
    const store = useAppStore();
    const messages = computed(() => catalogs[store.locale] || en);

    const t = (key) => {
        const parts = key.split('.');
        let node = messages.value;
        for (const part of parts) {
            node = node?.[part];
        }
        return node ?? key;
    };

    return { t, locale: computed(() => store.locale), setLocale: store.setLocale };
}
