---
epic_id: EPIC-000
slug: foundation
title: Foundation — ambiente, pipeline e tokens do DS em produção
wave: WAVE-2026-01
status: ready
owner_role: po
created_at: 2026-07-02
updated_at: 2026-07-02
target_completion: 2026-07-31
---

# EPIC-000 — Foundation

## Por que existimos (problema do usuário)

Antes de qualquer funcionalidade, o Quantah precisa existir de forma deployável e observável.
Sem ambiente automatizado, pipeline em verde e a fundação visual aplicada, todo o resto vira
"vamos subir depois" — o que contraria o princípio de entrega em produção desde o dia 1. O
usuário final ainda não toca nada aqui, mas este épico é o que torna os próximos demonstráveis.

## Resultado esperado (outcome)

Ao fim deste épico, qualquer pessoa acessa um "hello world" do Quantah em **homologação**, já com
os **tokens do design system** aplicados, e todo commit passa por um pipeline automatizado.

## Métrica de sucesso (como saberemos que funcionou)

- Métrica primária: pipeline CI/CD em verde e deploy automático para homologação a cada merge.
- Métrica de qualidade: ambiente sobe 100% local com um comando; zero passo manual repetido.

## Entregável visível no fim do épico

- [ ] "Hello world" do Quantah acessível na URL de homologação, com paleta/tipografia do DS.
- [ ] Pipeline CI/CD (testes + build + deploy homolog) verde e automatizado.
- [ ] `tailwind.config.js` mapeando os tokens do DS (cor, tipografia, spacing, raio) — Inter
      carregada conforme DDR-001.
- [ ] Ambiente de dev sobe com um comando (documentado/automatizado).

## Fora de escopo (explicitamente)

- Qualquer tela de produto (Coleta, Carteira) — isso é EPIC-002/003.
- Biblioteca de componentes React do DS — isso é EPIC-001 (aqui só o tema/tokens).
- Ambiente de produção final (homologação é o alvo desta onda; produção ao fim do 1º épico de
  valor, conforme o princípio — a confirmar no fechamento da onda).

## Referências da especificação

- `docs/especificacao/design-system.md` — tokens a mapear no tema.
- `docs/project-state/decisions/adr/ADR-000-stack-default.md` — stack ratificada.
- `docs/project-state/decisions/ddr/DDR-001-substituicao-fonte-display.md` — fonte (Inter).

## Dependências

- **Bloqueia:** EPIC-001, EPIC-002, EPIC-003 (tudo depende do ambiente de pé).
- **Bloqueado por:** nada.
- **Decisões arquiteturais necessárias:** ADR-000 (✅ accepted). Abrir **STORY-000 spike de
  stack** (`target_role: arquiteto`) como primeira estória — sobe a stack + um scan de QR de
  teste lendo a URL da SEFAZ-SP ponta a ponta.

## Estórias

Decompostas no Fluxo B (2026-07-02). Sequência: spike → pipeline → tema → validação.

- [ ] STORY-000 (spike, arquiteto) — validar stack scaffoldada + viabilidade de leitura do QR → `stories/STORY-000-spike-stack.md`
- [ ] STORY-001 (programador) — pipeline CI/CD + deploy homolog com hello-world → `stories/STORY-001-pipeline-deploy-homolog.md`
- [ ] STORY-002 (programador, requires_design) — tema Tailwind a partir dos tokens do DS (Inter) → `stories/STORY-002-tema-tailwind-tokens-ds.md`
- [ ] STORY-003 (validação, validador) — validação final do épico → `stories/STORY-003-validacao-epic-000.md`

## Validação final

Critérios em `validation/checklist.md`. Relatório em `validation/report.md`.

**Definição de épico concluído:** hello world em homologação com tokens do DS + pipeline verde +
ambiente automatizado; todas as estórias `done` e validação `approved`.

## Histórico

- 2026-07-02 — criado por PO (Fluxo A, WAVE-2026-01).
