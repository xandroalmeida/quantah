---
idr_id: IDR-008
slug: credito-cashback-via-evento-de-dominio-listener-enfileirado
title: Crédito de cashback via evento de domínio CupomValidado + listener enfileirado
status: accepted
decided_at: 2026-07-03
decided_by: programador
owner_agent: claude-programador-story015
related_story: STORY-015
related_adrs: [ADR-005, ADR-006, ADR-003, ADR-002]
related_idrs: [IDR-004]
supersedes: null
superseded_by: null
created_at: 2026-07-03
updated_at: 2026-07-03
---

# IDR-008 — Crédito de cashback via evento de domínio `CupomValidado` + listener enfileirado

## Contexto

A STORY-015 precisa creditar o cashback (0,1%) ao Colaborador **quando o cupom fica
válido-único-novo**. Essa validação acontece **de forma assíncrona** (ADR-002): o
`ExtrairCupomJob` roda no worker e chama `IngestaoCupomService::normalizarEpersistir`, que
seta `status = validado`. Ou seja, o crédito ocorre fora do request e precisa se conectar ao
fato "cupom validou" sem acoplar o contexto de **Coleta** (base analítica, EPIC-002) ao
contexto de **Cashback** (base de pagamento segregada, ADR-006).

Precisei decidir **como** disparar o crédito a partir da validação, dado que (a) crédito é
dinheiro e não pode duplicar nem se perder, e (b) uma falha no crédito não deve derrubar a
extração do cupom (nem provocar novo fetch na SEFAZ).

## Decisão

> **Decidi disparar um evento de domínio `App\Domain\Coleta\Events\CupomValidado` (pós-commit
> da validação) e consumi-lo com um listener ENFILEIRADO `CreditarCashbackAoValidar`
> (`ShouldQueue`), que delega ao `CreditarCashbackService` — idempotente por cupom.**

O evento é **produzido pela fronteira de Coleta** (quem conhece o fato) e **consumido pelo
Cashback** (quem reage). O registro do listener é **explícito** em `AppServiceProvider::boot`
(`Event::listen`), porque o listener vive em `app/Domain/Cashback/Listeners` — fora de
`app/Listeners`, onde o auto-discovery do Laravel 11 procura.

## Por quê

- **Desacoplamento de contextos (coesão alta, acoplamento baixo).** Coleta não referencia
  Cashback; só anuncia um fato de domínio. Preserva a segregação de bases da ADR-006 no nível
  de código, não só de schema.
- **Confiabilidade do dinheiro (ADR-005, F4/F7).** Listener **enfileirado** = o crédito tem
  retry próprio e roda desacoplado do sucesso da extração. Se o crédito falhasse dentro do
  `ExtrairCupomJob`, o retry do job re-bateria na SEFAZ **e** o guard de estado terminal do
  job (`validado`) puldaria a revalidação — o crédito nunca aconteceria. Com listener na fila,
  o crédito reprocessa sozinho; como é **idempotente** (índice único parcial
  `carteira_transacoes(cupom_id) WHERE tipo='credito_cashback'` + checagem sob lock), o retry
  é seguro.
- **Roda em homolog/prod.** É disparado por **evento** (não por `schedule()`), então roda no
  **worker de fila** — que é o processo que o ambiente de fato executa (ver gotcha "fila vs.
  scheduler" da sub-skill Laravel). Em teste a fila é `sync`, então roda inline.
- **Pós-commit.** O evento é disparado **depois** que a transação de validação fecha, para o
  listener nunca ver um cupom "meio-validado".

## Alternativas consideradas

- **Chamada direta `IngestaoCupomService` → `CreditarCashbackService`**: mais simples, mas
  acopla Coleta a Cashback e amarra o sucesso do crédito ao fluxo de extração (o problema de
  confiabilidade acima). Descartada.
- **Listener síncrono (sem `ShouldQueue`)**: rodaria dentro do job de extração; uma exceção no
  crédito falharia o job e, no retry, o guard de estado terminal impediria o recrédito.
  Descartada pela confiabilidade.
- **Auto-discovery de listener**: exigiria colocar o listener em `app/Listeners`, quebrando a
  organização por contexto de domínio (`app/Domain/<Contexto>`). Preferi registro explícito.

## Consequências

### Para outros agentes
- **Efeito colateral de domínio cross-contexto = evento + listener enfileirado**, com o
  registro explícito em `AppServiceProvider::boot`. Novos contextos que reajam a um fato de
  Coleta devem assinar o evento, não chamar o serviço direto.
- Eventos de domínio ficam em `app/Domain/<Contexto>/Events`; listeners em
  `app/Domain/<Contexto>/Listeners`. Não use `app/Listeners`.
- Todo efeito que envolve **dinheiro** disparado por evento deve ser **idempotente** (o retry
  da fila é esperado, não excepcional).

### Para o projeto
- +0 dependências (usa eventos/filas nativos do Laravel + fila Postgres já existente, ADR-002).
- O crédito depende do **worker de fila** estar vivo em homolog/prod (já é requisito da coleta).

### Trade-offs aceitos
- Registro manual do listener (uma linha) em vez de auto-discovery — custo pequeno pela
  organização por contexto.
- Latência: o crédito não é rigorosamente síncrono à validação (é o próximo item da fila).
  Aceitável — o saldo aparece em segundos e o épico não exige crédito instantâneo.

## Como verificar

- `tests/Feature/Cashback/CashbackNaValidacaoTest.php` prova: evento disparado ao validar,
  crédito ao coletor, idempotência no reprocessamento, e no-op sem atribuição/cupom rejeitado.
- Se o Laravel passar a autodescobrir listeners fora de `app/Listeners`, ou se o projeto adotar
  um EventServiceProvider central, reabrir esta IDR para simplificar o registro.

## Tipo

- [x] **Convenção interna**: efeito de domínio cross-contexto via evento + listener enfileirado, registro explícito.
- [ ] Padrão transversal
- [ ] Workaround
- [ ] Otimização
- [ ] Refatoração estrutural

---

## Histórico

- 2026-07-03 — criada como `accepted` por programador (sessão claude-programador-story015) durante STORY-015.
