---
epic_id: EPIC-001
type: validation-checklist
created_at: 2026-07-02
---

# Checklist de validação — EPIC-001 (Design System em código)

> Para o **validador** (STORY-007): execute cada item em ordem. Para cada um, registre
> status `pass | fail | n/a` e evidência (link, screenshot, log, hash) no `report.md`. Não invente
> resultados. Em caso de falha, **não tente consertar** — registre e devolva para o PO.
>
> Autorado pelo PO como input da validação (o validador não edita este arquivo; só o `report.md`).
> Derivado dos CAs da STORY-007, da "Definição de épico concluído" do `epic.md` e de
> `docs/skills/po/references/quality-standards.md`.

## 1. Critérios de aceite das estórias

- [ ] STORY-004, STORY-005 e STORY-006 estão com `status: done` no `index.json`.
- [ ] Cada CA de cada `story.md` (004/005/006) foi exercido por ao menos um teste automatizado
      (não apenas nome parecido — a asserção cobre o CA de fato).
- [ ] **Lista mínima presente em `Components/`** (epic.md): botões, inputs, cards, badges, snackbar,
      empty-state, skeleton, nav. Nenhum item da lista faltando.
- [ ] **Estados por componente** implementados onde aplicável: default / hover / focus / pressed /
      disabled / loading / error.
- [ ] Cada componente referencia a spec de tela/componente do Designer (revisão do Designer registrada
      na evidência da estória).

## 2. Cobertura de testes

- [ ] Cobertura unitária do código novo do épico ≥ **80%** (evidência: relatório do CI/comando local
      com números + linhas descobertas listadas).
- [ ] Módulos de núcleo/regra (se houver lógica de componente com regra) ≥ **98%**; se não houver
      núcleo de regra de negócio neste épico de UI, registrar `n/a` com justificativa.
- [ ] Testes cobrem caminho feliz + estados inválidos/erro + bordas (não só o feliz).
- [ ] Para o frontend web: há E2E em **browser real** cobrindo a vitrine e seus componentes
      (não apenas unit simulando DOM).

## 3. Automação

- [ ] Setup local automatizado (um comando a partir de clone limpo sobe o app) — declaração/execução.
- [ ] Pipeline CI **verde** no merge que publicou a vitrine em homologação.
- [ ] Deploy para homologação disparado automaticamente pelo pipeline (sem passo manual).
- [ ] Promoção tag-based conforme `quality-standards.md` §2.2 (RC → homolog); registrar `n/a` com
      justificativa se ainda não exercido neste épico.

## 4. Funcionalidade observável

- [ ] **Vitrine acessível em homologação por HTTPS, HTTP 200** — verificado de forma **independente**
      (requisição própria, não relato da estória). (CA-2)
- [ ] Todos os componentes da lista mínima e seus estados **aparecem** na vitrine renderizada. (CA-1)
- [ ] Percurso manual da vitrine em homologação funciona (componentes interativos respondem).
- [ ] Logs/métricas básicas de saúde da aplicação coletados em homologação; `n/a` justificado se
      este épico não introduz serviço novo além do já coberto pelo EPIC-000.

## 5. Qualidade transversal (a11y + tokens + segurança)

- [ ] **Contraste AA** confirmado nos componentes interativos da vitrine (WCAG 2.1 AA). (CA-3)
- [ ] **Foco visível** em componentes interativos (teclado). (CA-3)
- [ ] **Alvo de toque ≥ 48px** nos controles interativos. (CA-3)
- [ ] **Zero valor cru** de cor/spacing fora dos tokens no código dos componentes/vitrine —
      guarda/lint de tokens **verde**. (CA-3)
- [ ] Nenhum aviso crítico de segurança aberto introduzido pelo épico (scanner do CI).
- [ ] Nenhum segredo commitado (detecção de segredos do CI/pre-commit).
- [ ] Migrações de banco: `n/a` justificado se o épico não introduz migração; caso introduza,
      reversíveis e testadas.

## 6. Documentação e estado

- [ ] Notas do agente preenchidas em STORY-004, STORY-005 e STORY-006.
- [ ] ADRs/IDRs criados durante o épico (se houver) indexados no `index.json`.
- [ ] Evidência do Designer (revisões/screenshots) presente por estória onde `requires_design: true`.
- [ ] `index.json` coerente: STORY-004/005/006 `done`; `validation_report` do EPIC-001 será
      preenchido por esta validação (CA-5).

## 7. Veredito

- [ ] **APROVADO** — todos os itens acima `pass`, `pass com ressalva` ou `n/a` justificado.
- [ ] **REPROVADO** — pelo menos um `fail` bloqueante (ver `verdict-criteria.md`).

Preencha o `report.md` com o que observou aqui: fatos, evidência e classificação de gravidade dos
fails. **Sem recomendação/planejamento** — isso é do PO.
