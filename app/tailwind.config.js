import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/**
 * Tema Quantah — os tokens do Design System viram a config do Tailwind.
 * Fonte canônica dos valores: docs/project-state/design/system/tokens.md (PDR-001).
 * Este arquivo é o ÚNICO lugar onde o valor cru legitimamente aparece: token → config.
 * Fonte Inter (900/600/400) por DDR-001. Não redefina token aqui — isso é DDR do Designer.
 *
 * @type {import('tailwindcss').Config}
 */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],

    theme: {
        extend: {
            // --- Cor (tokens.md › Cor) ---
            colors: {
                // Marca & accent
                primary: '#9fe870',
                'on-primary': '#0e0f0c',
                'primary-active': '#cdffad',
                'primary-neutral': '#c5edab',
                'primary-pale': '#e2f6d5',
                // Superfície
                canvas: '#ffffff',
                'canvas-soft': '#e8ebe6',
                // Texto (tinta)
                ink: '#0e0f0c',
                'ink-deep': '#163300',
                body: '#454745',
                mute: '#868685',
                // Semânticas
                positive: '#2ead4b',
                'positive-deep': '#054d28',
                warning: '#ffd11a',
                'warning-deep': '#b86700',
                'warning-content': '#4a3b1c',
                negative: '#d03238',
                'negative-deep': '#a72027',
                'negative-darkest': '#a7000d',
                'negative-bg': '#320707',
                // Accents terciários (só ilustração/gráfico)
                'accent-orange': '#ffc091',
                'accent-cyan': '#38c8ff',
            },

            // --- Tipografia (tokens.md › Tipografia). Inter para tudo; peso é utilitário. ---
            fontFamily: {
                display: ['Inter', ...defaultTheme.fontFamily.sans],
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                'display-mega': ['126px', '107px'],
                'display-xxl': ['96px', '82px'],
                'display-xl': ['64px', '54px'],
                'display-lg': ['47px', '70px'],
                'display-md': ['40px', '34px'],
                'display-sm': ['32px', '38px'],
                'display-xs': ['24px', '31px'],
                'body-lg': ['20px', '30px'],
                'body-md': ['16px', '24px'],
                'body-sm': ['14px', '20px'],
                caption: ['12px', '16px'],
                'button-md': ['16px', '24px'],
            },

            // --- Espaçamento base-4 (tokens.md › Espaçamento) ---
            spacing: {
                xxs: '2px',
                xs: '4px',
                sm: '8px',
                md: '12px',
                lg: '16px',
                xl: '24px',
                '2xl': '32px',
                '3xl': '48px',
            },

            // --- Raio (tokens.md › Raio). xl=24px é a assinatura de botões e cards. ---
            borderRadius: {
                none: '0px',
                sm: '8px',
                md: '12px',
                lg: '16px',
                xl: '24px',
                pill: '9999px',
                full: '9999px',
            },

            // --- Elevação (tokens.md › Elevação). O DS usa contraste de superfície;
            //     elev-1 = hairline ink, elev-2 = card sutil. ---
            boxShadow: {
                'elev-0': 'none',
                'elev-1': 'inset 0 0 0 1px #0e0f0c',
                'elev-2': '0 1px 2px 0 rgb(14 15 12 / 0.04)',
            },

            // --- Breakpoints (tokens.md › Breakpoints) alinhados ao Tailwind. ---
            screens: {
                md: '768px',
                lg: '1024px',
            },

            // --- Motion (tokens.md › Motion) ---
            transitionDuration: {
                fast: '100ms',
                base: '200ms',
                slow: '300ms',
            },
        },
    },

    plugins: [forms],
};
