/**
 * VersionStamp — carimbo discreto da versão (tag) do app no canto inferior direito,
 * presente em TODAS as telas (montado uma vez na casca do Inertia, em app.jsx).
 *
 * É referência de build para QA/homologação, não UI de produto: não-interativo
 * (`pointer-events-none`, taps atravessam para o nav.bottom), faível (`text-mute`,
 * 10px) e fora do fluxo (`fixed`). Respeita `safe-area-inset-bottom` para não colar
 * na barra de gestos. A versão vem do prop compartilhado `version` (HandleInertiaRequests).
 */
export default function VersionStamp({ version }) {
    if (!version) {
        return null;
    }

    return (
        <span
            aria-hidden="true"
            data-testid="app-version"
            className="pointer-events-none fixed bottom-[max(env(safe-area-inset-bottom),0.25rem)] right-1.5 z-50 select-none text-[10px] font-medium leading-none text-mute/70"
        >
            {version}
        </span>
    );
}
