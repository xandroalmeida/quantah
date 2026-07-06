# Checklist de validação — EPIC-009

> Materializado dos critérios de aceite das STORY-039/040/041 + requisitos transversais do `epic.md`.
> É o **o quê** a validação confirma. O veredito e as evidências vivem em `report.md`.

## Bloco 1 — Critérios de aceite (por estória)

- 1.1 — STORY-039/040/041 com `status: done` no `index.json`; ADRs do spike registrados e `accepted`.
- 1.2 — **STORY-040 (serviço):** consulta assíncrona funciona; cache respeitado dentro do TTL (zero
  chamada externa); TTL parametrizável (default 30 dias) sem mudança de código; retry/backoff conforme
  ADR; CNPJ não encontrado/sem CNAE em estado distinto e consultável.
- 1.3 — **STORY-041 (pipeline + Backoffice):** enriquecimento disparado automaticamente pós-validação
  do cupom, sem afetar a latência do envio; detalhe do cupom no Backoffice exibe razão social, CNAE
  (código + descrição), município/UF e situação; estados pendente/indisponível claros; emitente único
  por CNPJ (sem duplicação).

## Bloco 2 — Cobertura de testes

- 2.1 — Cobertura ≥ 80% no código novo; ≥ 98% nas regras de cache/fallback.
- 2.2 — TDD evidenciado no histórico de commits.
- 2.3 — Testes independentes da API externa real (dublês); E2E Dusk do fluxo cupom → Backoffice.
- 2.4 — Caminho feliz + falha da API + TTL vencido + CNPJ inexistente cobertos.
- 2.5 — Suíte completa verde sobre o sha deployado.

## Bloco 3 — Transversais da WAVE-2026-03

- 3.1 — Falha do enriquecimento **nunca** visível como erro para o Colaborador nem perde cupom.
- 3.2 — Backoffice em pt-BR, padrão DS, acesso restrito por RBAC (ADR-009).
- 3.3 — Nenhuma credencial/segredo de API em código ou log.
- 3.4 — Fila com observabilidade mínima definida no ADR (profundidade/falhas consultáveis).

## Bloco 4 — Funcionalidade observável

- 4.1 — Demonstração em homologação: enviar cupom real → emitente enriquecido visível no Backoffice.
- 4.2 — Segundo cupom do mesmo CNPJ dentro do TTL → evidência de acerto de cache (sem chamada externa).
- 4.3 — Sem regressão do loop da Onda 1/2 (coletar → saldo → extrato → saque).
