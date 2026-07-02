/**
 * Card — superfície do DS (STORY-006). Raio `xl` (24px) e padding `xl`, assinatura
 * da marca. Elevação por **contraste de superfície** (não sombra pesada). Variantes
 * (components.md › Cards): `content` (branco padrão), `feature-sage`, `feature-green`
 * e `feature-dark` (ink + verde — momento de marca, uso pontual).
 */
const VARIANTS = {
    content: 'bg-canvas text-ink',
    'feature-sage': 'bg-canvas-soft text-ink',
    'feature-green': 'bg-primary-pale text-ink',
    'feature-dark': 'bg-ink text-primary',
};

export default function Card({ variant = 'content', className = '', children, ...props }) {
    const colors = VARIANTS[variant] ?? VARIANTS.content;

    return (
        <div className={`rounded-xl p-xl ${colors} ${className}`} {...props}>
            {children}
        </div>
    );
}
