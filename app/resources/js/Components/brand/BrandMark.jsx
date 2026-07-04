import { ReceiptIcon } from '@/Components/icons';

/**
 * BrandMark — marca do Quantah (`brand.mark` do DS, DDR-004). Tile quadrado raio `xl`
 * em `primary` (verde) com o glifo de nota fiscal (`ReceiptIcon`) em `on-primary` (ink).
 * Comunica o domínio (cupom/nota), não decora. Substitui o `ApplicationLogo` do Laravel.
 *
 * A11y: decorativo quando acompanha o wordmark (aria-hidden) — o nome acessível vem do
 * texto "Quantah" ao lado (ver BrandLockup).
 */
export default function BrandMark({ className = '', tile = 'h-3xl w-3xl' }) {
    return (
        <span
            aria-hidden="true"
            className={`inline-flex items-center justify-center rounded-xl bg-primary text-on-primary ${tile} ${className}`}
        >
            <ReceiptIcon className="h-xl w-xl" />
        </span>
    );
}
