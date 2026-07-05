/**
 * Band — superfície full-bleed de marketing/seção (bands do DS: `hero-band`, `hero-band-dark`,
 * `content-band` — components.md › Bands / hero). Define apenas a superfície (fundo + tinta +
 * padding vertical de band, `3xl`); o conteúdo e as colunas são compostos pela página. Renderiza
 * um `<section>`. Só tokens do DS.
 */
const VARIANTS = {
    hero: 'bg-canvas-soft text-ink', // hero-band — sage, mood da marca
    'hero-dark': 'bg-ink text-primary', // hero-band-dark — headline verde sobre ink
    content: 'bg-canvas text-ink', // content-band — branco
};

export default function Band({ variant = 'content', className = '', children, ...props }) {
    const colors = VARIANTS[variant] ?? VARIANTS.content;

    return (
        <section className={`px-lg py-3xl lg:px-3xl ${colors} ${className}`} {...props}>
            {children}
        </section>
    );
}
