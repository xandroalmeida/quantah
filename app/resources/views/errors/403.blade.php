<!DOCTYPE html>
{{-- Barreira de área do Quantah (STORY-023 · ADR-010 §3). Página 403 branded, em pt-BR,
     mostrada quando um Coletador tenta alcançar o Backoffice (Gate `operar-saques`, ADR-009).
     Fora do Inertia (é página de erro do framework) — estilos inline com a paleta do DS. --}}
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Acesso restrito · Quantah</title>
        <style>
            :root {
                --canvas: #ffffff;
                --canvas-soft: #e8ebe6;
                --primary: #9fe870;
                --ink: #0e0f0c;
                --body: #454745;
                --mute: #868685;
            }
            * { box-sizing: border-box; }
            body {
                margin: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1.5rem;
                background: var(--canvas-soft);
                color: var(--ink);
                font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
                text-align: center;
            }
            .card {
                width: 100%;
                max-width: 30rem;
                background: var(--canvas);
                border-radius: 24px;
                padding: 2.5rem 2rem;
                box-shadow: 0 8px 24px rgba(14, 15, 12, 0.08);
            }
            .badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 4rem;
                height: 4rem;
                border-radius: 20px;
                background: var(--primary);
                color: var(--ink);
                font-weight: 900;
                font-size: 1.25rem;
                letter-spacing: 0.02em;
            }
            h1 {
                margin: 1.25rem 0 0.5rem;
                font-size: 1.75rem;
                font-weight: 900;
            }
            p {
                margin: 0 auto;
                max-width: 24rem;
                color: var(--body);
                font-size: 1.05rem;
                line-height: 1.6;
            }
            a.voltar {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                margin-top: 1.75rem;
                min-height: 3rem;
                padding: 0.75rem 1.5rem;
                border-radius: 16px;
                background: var(--primary);
                color: var(--ink);
                font-weight: 600;
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <main class="card" role="main" data-testid="barreira-403">
            <span class="badge" aria-hidden="true">403</span>
            <h1>Acesso restrito</h1>
            <p>
                Esta área é da operação do Quantah e você não tem permissão para acessá-la. Se
                acha que isso é um engano, fale com a equipe responsável.
            </p>
            <a class="voltar" href="{{ url('/') }}">Voltar ao início</a>
        </main>
    </body>
</html>
