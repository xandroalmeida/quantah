---
epic_id: EPIC-002
type: validation-checklist
created_at: 2026-07-02
---

# Checklist de validação — EPIC-002 (Coleta de cupom)

> Para o **validador** (STORY-013): execute cada item em ordem. Para cada um, registre
> status `pass | pass com ressalva | fail | n/a` e evidência (link, log, hash, screenshot) no
> `report.md`. Não invente resultados. Em caso de falha, **não tente consertar** — registre e devolva
> ao PO. Veredito segue `docs/skills/validador/references/verdict-criteria.md`.
>
> Autorado pelo PO já na decomposição do épico (o validador não edita este arquivo; só o `report.md`).
> Derivado dos CAs das STORY-008..013, da "Definição de épico concluído" do `epic.md`, de
> `docs/visao.md` §6–§7 e de `docs/skills/po/references/quality-standards.md`.

## 1. Critérios de aceite das estórias

- [ ] STORY-008 (spike), 009, 010, 011 e 012 estão com `status: done` no `index.json`.
- [ ] Cada CA de cada `story.md` (008–012) foi exercido por ao menos um teste automatizado — a
      **asserção cobre o CA de fato** (não apenas nome parecido).
- [ ] ADR-001 (ingestão/modelo), ADR-002 (extração resiliente SP), ADR-003 (dedup/validação por
      chave) e ADR-006 (anonimização CPF/LGPD) existem, estão `accepted` e **indexados** em `decisions.adr`.
- [ ] O **modelo canônico do cupom** (ADR-001) está documentado e é o que as estórias 010/011 usam.

## 2. Cobertura de testes

- [ ] Cobertura unitária do código novo do épico ≥ **80%** (evidência: relatório do CI/comando local).
- [ ] **Núcleo de regra de negócio** (validação SEFAZ + **deduplicação** — STORY-010) ≥ **98%**
      (evidência: relatório com números e linhas descobertas).
- [ ] Testes cobrem: caminho feliz + **cupom duplicado** (dedup idempotente) + **falha de extração**
      (reprocessamento) + **chave malformada/não-SP** — não só o feliz.
- [ ] Frontend web: há E2E em **browser real** cobrindo o fluxo de captura (STORY-009), incluindo o
      caminho de erro (QR inválido).

## 3. Automação

- [ ] Setup local automatizado (um comando a partir de clone limpo sobe o app) — declaração/execução.
- [ ] Pipeline CI **verde** no merge que publicou a coleta em homologação.
- [ ] Deploy para homologação disparado **automaticamente** pelo pipeline (sem passo manual).
- [ ] **Fila de reprocessamento** da extração (ADR-002) roda de forma automatizada (worker/schedule),
      não como passo manual; exercida em teste.
- [ ] Promoção tag-based / gate de produção conforme `quality-standards.md` §2.2 — `n/a` justificado
      se produção segue fora de escopo da onda (cruza com WISH-003).

## 4. Funcionalidade observável

- [ ] **Fluxo de coleta acessível em homologação (mobile), HTTP 200** — verificado de forma
      **independente** (requisição/checagem própria, não relato da estória). (STORY-009 CA-1..3)
- [ ] Um cupom de exemplo de SP percorre **end-to-end** em homologação: captura → validação SEFAZ →
      dedup → persistência → "cupom aceito". (STORY-009/010)
- [ ] **Deduplicação demonstrável:** reenviar a mesma chave de 44 dígitos não gera segundo registro
      nem conta em dobro. (STORY-010 CA-2)
- [ ] Painel/rota interna da **north-star** mostra cupons válidos-únicos-novos/semana + taxa de
      sucesso de envio, em homologação. (STORY-012)
- [ ] Logs e métricas básicas de saúde/uso coletados (sem dado pessoal). (STORY-012 CA-5)

## 5. Qualidade transversal (LGPD + dados + segurança)

- [ ] **CPF não persistido em claro** em nenhuma tabela do modelo canônico — verificado por inspeção
      do que foi gravado. (STORY-011 CA-1)
- [ ] Tratamento de CPF segue a **ADR-006** (anonimização ou descarte) e há **teste de regressão** que
      falha se algum caminho voltar a gravar CPF em claro. (STORY-011 CA-2/CA-5)
- [ ] **Nenhum vazamento de CPF** em logs, mensagens de erro, fixtures ou telemetria. (STORY-011 CA-4)
- [ ] Dedup/contagem **não dependem** de CPF (funcionam sem ele). (STORY-011 CA-3)
- [ ] **Migrações de banco reversíveis** e testadas em homologação (modelo canônico novo).
- [ ] Nenhum aviso crítico de segurança aberto introduzido pelo épico; nenhum **segredo/credencial**
      de acesso à SEFAZ versionado (deploy via secrets). Se o CI não tem scanner, registrar como
      **Limitação** e verificar por inspeção direta (como no EPIC-001).

## 6. Documentação e estado

- [ ] Notas do agente preenchidas em STORY-008..012.
- [ ] ADRs (001/002/003/006) e IDRs criados durante o épico indexados no `index.json`.
- [ ] Evidência do Designer presente para as estórias `requires_design: true` (009, 012).
- [ ] `index.json` coerente: STORY-008..012 `done`; `validation_report` do EPIC-002 será preenchido
      por esta validação (STORY-013 CA-5).

## 7. Veredito

- [ ] **APROVADO** — todos os itens `pass`, `pass com ressalva` ou `n/a` justificado.
- [ ] **REPROVADO** — ao menos um `fail` bloqueante (ver `verdict-criteria.md`).

Preencha o `report.md` com fatos, evidência e classificação de gravidade dos fails.
**Sem recomendação/planejamento** — isso é do PO.
