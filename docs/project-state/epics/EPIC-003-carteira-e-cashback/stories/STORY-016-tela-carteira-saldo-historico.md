---
story_id: STORY-016
slug: tela-carteira-saldo-historico
title: Tela de carteira — saldo em reais + histórico de cupons/créditos (mobile)
epic_id: EPIC-003
sprint_id: null
type: implementation
target_role: programador
requires_design: true
design_screen_id: null
status: in_progress
owner_agent: claude-story016
created_at: 2026-07-03
updated_at: 2026-07-03
estimated_session_size: M
---

# STORY-016 — Tela de carteira

> **Para o agente que vai executar:** tela do **Colaborador** (superfície de marca) → exige
> **artefato de Designer** antes da implementação (diferente do painel interno da STORY-012).

## Contexto (por que esta estória existe)

O Colaborador só percebe o incentivo se **vê o saldo crescer**. Esta tela mostra o saldo em reais e
o histórico de cupons enviados e créditos recebidos — é o que fecha o loop percebido pelo usuário.

## Critérios de aceite (alto nível — refinar na execução)

- Carteira mobile em homologação exibindo **saldo em reais** e **histórico** (cupons válidos +
  créditos correspondentes), compondo componentes do DS (EPIC-001).
- Estados além do feliz: saldo zero, carregando, erro, lista vazia.
- Dados vêm da carteira/extrato da STORY-015 (nada de número hardcoded).
- Acessibilidade e microcopy Quantah (duas faces) conforme DS.
- Feature/E2E verdes (mesmo padrão da STORY-009).

## Design (requires_design)

Exige spec de tela do Designer em `design/screens/STORY-016-*` **antes** da implementação. (Não se
aplica a exceção DDR-003 — aquela vale só para telas internas de leitura.)

## Fora de escopo

- Ação de saque/resgate (STORY-017).
- Gamificação/ranking (Onda 2).

## Dependências

- **Bloqueado por:** STORY-015 (fonte do saldo/histórico); EPIC-001 (componentes).
- **Bloqueia:** STORY-018.

## Definição de pronto

Tela em homologação com estados; artefato de Designer presente e validado; testes verdes;
`index.json` = `done`; Notas do agente preenchidas.
