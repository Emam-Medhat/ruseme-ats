import { storeToRefs } from 'pinia';
import { useAnalysisLoaderStore } from '@/stores/analysisLoader';

/** Global analysis overlay (Pinia) — survives reactivity when used from any page. */
export function useAnalysisLoader() {
    const store = useAnalysisLoaderStore();
    const { visible, progress } = storeToRefs(store);

    return {
        visible,
        progress,
        steps: store.steps,
        stepState: store.stepState,
        start: () => store.start(),
        finish: () => store.finish(),
        stop: () => store.stop(),
    };
}
