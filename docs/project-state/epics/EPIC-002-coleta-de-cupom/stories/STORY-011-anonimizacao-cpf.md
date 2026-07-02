---
story_id: STORY-011
slug: anonimizacao-cpf
title: Anonimização de CPF na coleta (LGPD)
epic_id: EPIC-002
sprint_id: null
type: implementation
target_role: programador
requires_design: false
design_screen_id: null
status: draft
owner_agent: null
created_at: 2026-07-02
updated_at: 2026-07-02
estimated_session_size: M
---

# STORY-011 — Anonimização de CPF na coleta (LGPD)

> **Para o agente que vai executar:** leia por inteiro. Estória de **conformidade LGPD** — o objetivo
> é garantir que nenhum CPF em claro entre/permaneça na base. Siga a ADR-006 (STORY-008).

## Contexto (por que esta estória existe)

A NFC-e pode conter o **CPF do consumidor** ("CPF na nota"). O Quantah não precisa do CPF para a base
de preços e não pode retê-lo em claro sem base legal e risco. A coleta precisa **anonimizar/descartar**
o CPF já na ingestão, com segregação de bases (LGPD, visao §7).

- Épico: `epics/EPIC-002-coleta-de-cupom/epic.md`
- Documentos a ler ANTES: `docs/visao.md` §7 (Privacidade e LGPD); ADR-006 (anonimização/segregação);
  ADR-001 (modelo canônico — onde o CPF entraria).

## O quê (objetivo desta estória)

Garantir que, quando o CPF estiver presente na nota, ele seja **anonimizado ou descartado na coleta**
(nunca persistido em claro no modelo canônico), conforme a decisão da ADR-006.

## Por quê (valor para o usuário)

Protege o consumidor e o Quantah: minimização de dado pessoal reduz risco jurídico e de vazamento, e
sustenta a confiança necessária para a coleta colaborativa.

## Critérios de aceite

- [ ] **CA-1:** Quando a nota traz CPF, o valor **não é persistido em claro** em nenhuma tabela do
      modelo canônico (verificado por teste que inspeciona o que foi gravado).
- [ ] **CA-2:** O tratamento segue a ADR-006 (anonimização — ex.: hash/pseudonimização com segregação —
      **ou** descarte, conforme a decisão); o teste prova o comportamento decidido.
- [ ] **CA-3:** A dedução/dedup do cupom (STORY-010) **não depende** do CPF — funciona sem ele.
- [ ] **CA-4:** Logs/telemetria **não vazam** CPF (nem em texto de log, nem em mensagens de erro).
- [ ] **CA-5:** Há teste de regressão que falha se, no futuro, algum caminho passar a gravar CPF em claro.

## Fora de escopo

- Política de retenção/portal de titular (direitos LGPD além da minimização na coleta) — evolução.
- Outros dados pessoais além do CPF na nota (não há previsão de coleta na onda).

## Padrões de qualidade exigidos

Segue `quality-standards.md` + seção LGPD/Segurança. Cobertura alta no caminho de tratamento de dado
pessoal; nenhum CPF em claro em base/log/fixtures versionadas.

## Dependências

- **Bloqueada por:** STORY-008 (ADR-006) e STORY-010 (persistência sobre a qual o tratamento atua).
- **Bloqueia:** STORY-013 (validação — item LGPD do checklist).

## Decisões já tomadas (não as reabra)

- ADR-006 (anonimização/segregação). Minimização de dado é princípio (visao §7). Só SP.

## Definição de Pronto (DoD)

- [ ] CA-1 a CA-5 passam; teste de regressão anti-CPF-em-claro presente e verde.
- [ ] Nenhum CPF em claro em base/log/fixtures; pipeline verde.
- [ ] IDR se houve decisão técnica; `index.json` = `done`; "Notas do agente" preenchida.

## Protocolo do agente (obrigatório)

Siga `agent-task-format.md`. Dúvida sobre base legal/decisão LGPD que não esteja na ADR-006 →
`blocked` + escalar ao PO (é decisão de produto/jurídica, não do agente).

## Notas do agente (preenchido durante/após execução)

### Decisões tomadas
- 

### Descobertas
- 

### Bloqueios encontrados
- 

### Links de evidência
- 
