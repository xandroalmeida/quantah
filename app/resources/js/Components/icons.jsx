/**
 * Ícones do DS (STORY-006). SVG stroke/fill = `currentColor` — a cor vem do token
 * aplicado no elemento pai (feedback nunca só cor: ícone + texto). Sem valor cru.
 */
function Svg({ children, ...props }) {
    return (
        <svg
            className="h-lg w-lg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
            aria-hidden="true"
            {...props}
        >
            {children}
        </svg>
    );
}

export function CheckIcon(props) {
    return (
        <Svg {...props}>
            <path d="M20 6 9 17l-5-5" />
        </Svg>
    );
}

export function XIcon(props) {
    return (
        <Svg {...props}>
            <path d="M18 6 6 18M6 6l12 12" />
        </Svg>
    );
}

export function AlertIcon(props) {
    return (
        <Svg {...props}>
            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
            <path d="M12 9v4M12 17h.01" />
        </Svg>
    );
}

export function InfoIcon(props) {
    return (
        <Svg {...props}>
            <circle cx="12" cy="12" r="10" />
            <path d="M12 16v-4M12 8h.01" />
        </Svg>
    );
}

export function InboxIcon(props) {
    return (
        <Svg {...props}>
            <path d="M22 12h-6l-2 3h-4l-2-3H2" />
            <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11Z" />
        </Svg>
    );
}

export function HomeIcon(props) {
    return (
        <Svg {...props}>
            <path d="M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1Z" />
        </Svg>
    );
}

export function ReceiptIcon(props) {
    return (
        <Svg {...props}>
            <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1V2l-2 1-2-1-2 1-2-1-2 1-2-1Z" />
            <path d="M8 7h8M8 11h8M8 15h5" />
        </Svg>
    );
}

export function WalletIcon(props) {
    return (
        <Svg {...props}>
            <path d="M20 12V8H6a2 2 0 0 1 0-4h12v4" />
            <path d="M4 6v12a2 2 0 0 0 2 2h14v-4" />
            <path d="M18 12a2 2 0 0 0 0 4h4v-4Z" />
        </Svg>
    );
}

export function UserIcon(props) {
    return (
        <Svg {...props}>
            <circle cx="12" cy="8" r="4" />
            <path d="M4 21a8 8 0 0 1 16 0" />
        </Svg>
    );
}

export function QrIcon(props) {
    return (
        <Svg {...props}>
            <path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4z" />
            <path d="M14 14h2v2h-2zM18 14h2v2h-2zM14 18h2v2h-2zM18 18h2v2h-2z" />
        </Svg>
    );
}

export function PlusIcon(props) {
    return (
        <Svg {...props}>
            <path d="M12 5v14M5 12h14" />
        </Svg>
    );
}
