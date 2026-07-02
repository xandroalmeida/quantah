/**
 * Skeleton do DS (STORY-006). Placeholder de carregamento (nunca spinner em tela
 * vazia) para o primeiro fetch/refresh. Decorativo → `aria-hidden`. Formas: `line`,
 * `block`, `circle`.
 */
const SHAPES = {
    line: 'h-lg w-full rounded-sm',
    block: 'h-3xl w-full rounded-lg',
    circle: 'h-3xl w-3xl rounded-full',
};

export default function Skeleton({ shape = 'line', className = '', ...props }) {
    const form = SHAPES[shape] ?? SHAPES.line;

    return (
        <div
            aria-hidden="true"
            className={`animate-pulse bg-canvas-soft ${form} ${className}`}
            {...props}
        />
    );
}
