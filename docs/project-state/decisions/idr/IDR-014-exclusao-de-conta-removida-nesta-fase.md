---
idr_id: IDR-014
slug: exclusao-de-conta-removida-nesta-fase
title: Exclusão de conta removida (rota + UI), não apenas escondida — fluxo LGPD vira estória futura
status: accepted  # proposed | accepted | superseded
decided_at: 2026-07-05
decided_by: programador
owner_agent: claude-programador
related_story: STORY-036
related_adrs: []
related_idrs: [IDR-010]
related_ddrs: [DDR-007]
supersedes: null
superseded_by: null
created_at: 2026-07-05
updated_at: 2026-07-05
---

# IDR-014 — Exclusão de conta removida nesta fase (não só escondida)

> Implementation Decision Record. Registra que a exclusão de conta foi **removida** (não bloqueada),
> para que outro agente não a reintroduza sem querer nem procure a rota `profile.destroy`.

## Contexto

O Perfil herdava do Breeze o `DeleteUserForm` + rota `DELETE /profile` (`profile.destroy`) +
`ProfileController@destroy`. No piloto isso é superfície de risco (auto-exclusão acidental) sem valor.
A STORY-036 CA-2 pede que a ação "não fique acessível por rota direta" e que a decisão de remover vs
apenas bloquear seja registrada.

## Decisão

> **Decidi remover** o `DeleteUserForm` (UI), a rota `profile.destroy` e o método
> `ProfileController@destroy` — em vez de apenas ocultar o formulário. `DELETE /profile` passa a
> responder 405 (a URI existe para GET/PATCH). As demais ações do Perfil (dados, senha) seguem.

## Por quê

- **Remover > esconder:** esconder o form deixaria a rota viva e explorável (curl/console) — contraria
  o CA-2 ("não acessível por rota direta"). Remover fecha a superfície de verdade.
- **Menos código morto:** sem form/rota/método órfãos herdados do scaffolding.

## Alternativas consideradas

- **Bloquear a rota (abort 403/middleware):** descartada — mantém código morto e uma rota que sugere
  que a função existe; remover é mais honesto e limpo.
- **Manter escondendo só a UI:** descartada — rota continuaria executável.

## Consequências

### Para outros agentes
- Não existe exclusão de conta nem `profile.destroy` nesta fase. Se o produto precisar de exclusão
  (LGPD/atendimento), é **estória nova** com fluxo próprio (confirmação forte, retenção, trilha) —
  não reviver o `DeleteUserForm` do Breeze.
- Menu de escanear: o rótulo é a chave i18n `Scan` → "Escanear" (IDR-010); a seção continua `cupons`
  e o destino `/coletar` (não confundir rótulo com identidade da seção).

### Para o projeto
- Menos uma rota/controller/method e um componente React a manter.

### Trade-offs aceitos
- Usuário não consegue se auto-excluir no piloto (aceitável — exclusão passa por atendimento até
  existir o fluxo LGPD dedicado).

## Como verificar

- `tests/Feature/ProfileTest`: `DELETE /profile` → 405 e conta persiste; `profile.destroy` não existe
  no roteador.
- `tests/Browser/CoerenciaJornadaTest`: Perfil não mostra "Excluir Conta"; menu mostra "Escanear" com
  destino/seção intactos.

## Tipo

- [x] **Convenção interna**: exclusão de conta ausente por decisão; reintroduzir exige estória própria.

---

## Histórico

- 2026-07-05 — criada como `accepted` por programador (sessão claude-programador) durante STORY-036.
