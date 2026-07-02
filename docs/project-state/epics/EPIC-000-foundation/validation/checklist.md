# Checklist de validação — EPIC-000 (Foundation)

Preenchido pelo **validador** na STORY-003. Cada item exige evidência (link/print) no
`report.md`. Veredito só é `approved` com todos os itens marcados.

## Ambiente e deploy

- [ ] Hello-world acessível na URL de homologação (HTTP 200).
- [ ] Deploy para homologação é automatizado (dispara no merge, sem passo manual).
- [ ] Ambiente de dev sobe com **um** comando a partir de clone limpo, com seed.

## Pipeline

- [ ] CI roda suíte de testes + build a cada PR/push.
- [ ] Pipeline **barra o merge** quando teste/build falha.
- [ ] Pipeline verde no merge que gerou o deploy validado.

## Design System aplicado

- [ ] Paleta do DS aplicada (verde `primary` só em CTA; superfícies sage/branco).
- [ ] Tipografia Inter carregada (400/600/900); display em peso 900 (DDR-001).
- [ ] Raio 24px em botão; nenhum valor cru de cor/spacing no JSX da hello-world.

## Qualidade

- [ ] Cobertura ≥80% no código novo das estórias de implementação.
- [ ] Ao menos um E2E em browser real cobrindo a hello-world em homologação.
- [ ] A11y mínima: contraste AA no botão, foco visível.
- [ ] Nenhum segredo commitado.

## Estado

- [ ] `index.json` coerente: STORY-000/001/002 `done`; STORY-003 conclui o épico.
- [ ] Notas do agente preenchidas em cada estória.

## Veredito

- [ ] **approved** / [ ] **rejected** — motivos no `report.md`.
