---
adr_id: ADR-000
slug: stack-default
title: Stack default do MVP — web-rica (Laravel + Inertia/React + PostgreSQL)
status: accepted  # proposed | accepted | superseded | rejected | deferred
decided_at: 2026-07-02  # YYYY-MM-DD quando virar accepted
decided_by: arquiteto
approved_by: Alexandro  # "Alexandro" — preenchido na aprovação humana
supersedes: null
superseded_by: null
related_adrs: []
related_pdrs: [PDR-001]
related_epics: []
created_at: 2026-07-02
updated_at: 2026-07-02
---

# ADR-000 — Stack default do MVP (web-rica)

> **Stub gerado na inicialização.** Registra a stack default escolhida na entrevista do `init-project`
> como ponto de partida a ser **ratificado ou trocado** pelo Arquiteto. Não é uma decisão fechada — é
> o gancho para a primeira decisão arquitetural real do MVP.

## Contexto

O Quantah nasce em fase `MVP` a partir do documento de visão (`docs/visao.md`), que já fixa decisões de
produto relevantes para a arquitetura: plataforma **PWA** (§12), coleta via **QR Code da NFC-e**,
extração **híbrida** (scraping da SEFAZ-SP no MVP → fonte oficial depois) com **adaptador por estado** e
um **modelo canônico de cupom** (§6), incentivos combinados (**cashback** livre + **gamificação** livre +
**sorteio** com gate regulatório, §8) e requisitos de **LGPD/anonimização de CPF na coleta** (§7).

Na inicialização, o perfil de stack escolhido foi **web-rica**, por alinhar com a decisão de plataforma
PWA da visão (um PWA instalável, uma única base de código web). Isto define os defaults: **PostgreSQL**
(banco), **Laravel** (backend) e **Inertia + React** (frontend). Este ADR-000 registra esse ponto de
partida; o Arquiteto deve ratificá-lo ou substituí-lo com análise, sobretudo nos pontos marcados
**[→ Fase técnica]** da visão (arquitetura de extração resiliente, deduplicação, anti-fraude, matching
de produtos, integração de pagamento/PIX).

## Forças (drivers) da decisão

- **F1 — Alinhamento com a visão (PWA):** a §12 fixa "Plataforma: PWA (por enquanto)". A stack precisa
  entregar um app web instalável com boa interatividade (câmera/scan de QR, confirmação de cupom).
- **F2 — Velocidade de MVP:** cobertura inicial só SP, um adaptador; tempo até coletar volume real é o
  que valida a tese. Framework opinativo e produtivo reduz o caminho.
- **F3 — Peso de dados / backend robusto:** o valor do produto é o pipeline de dados (extração,
  deduplicação, normalização/GTIN, agregações B2B). O backend carrega o peso; o frontend é o coletor.
- **F4 — Reversibilidade:** a decisão deve poder ser revista sem reescrever o núcleo (monolito primeiro,
  datastore-first).

## Opções consideradas

### Opção A — web-rica (Laravel + Inertia + React + PostgreSQL) — *default proposto*
- **Resumo:** monolito Laravel servindo um front React via Inertia, empacotável como PWA. Postgres como
  datastore primário.
- **Prós concretos:** UI rica para o fluxo de scan/confirmação; uma base de código; Laravel cobre bem
  filas (reprocessamento de scraping), scheduler e integrações; Postgres forte para agregações B2B.
- **Contras concretos:** PWA tem limites de acesso a hardware/câmera e de distribuição frente a app
  nativo; SEO/telas não são requisito aqui.

### Opção B — mobile nativo (Laravel API + Flutter + PostgreSQL)
- **Resumo:** API Laravel headless + app Flutter nativo.
- **Prós:** melhor acesso a câmera/notificações; presença em loja de apps.
- **Contras:** diverge da decisão "PWA" da visão; duas bases (API + app); mais custo de MVP.

### Opção C — web-padrão (Laravel + Livewire + PostgreSQL)
- **Resumo:** web server-side mais simples com Livewire.
- **Prós:** stack mais enxuta; menos build de frontend.
- **Contras:** interação rica (scan de QR, feedback imediato) fica menos fluida que React; PWA possível
  mas menos idiomática.

### Opção D — Status quo / não decidir agora
- **Consequência:** sem stack ratificada, o time não abre a primeira estória de implementação.
- **Custo de adiar:** baixo se resolvido no início do EPIC-000; alto se arrastar.

## Decisão proposta

> **Proposta: Opção A (web-rica).** Ratificar ou substituir é tarefa do Arquiteto no EPIC-000.

Adotar Laravel + Inertia/React + PostgreSQL como stack default do MVP, empacotada como PWA, por alinhar
com a decisão de plataforma da visão e concentrar o peso no backend de dados.

## Justificativa

O produto é, no fundo, um **pipeline de dados** com um coletor leve na ponta. Laravel entrega o backend
produtivo (filas para reprocessar scraping frágil, scheduler, Sanctum) e Postgres sustenta as agregações
do Quantah Intelligence. Inertia+React dá a interatividade do fluxo de coleta sem fragmentar em duas
bases de código, mantendo a promessa de PWA. Os trade-offs (limites de PWA vs. nativo) são aceitáveis no
MVP e reversíveis — se o volume provar que o app nativo é necessário, reabre-se esta decisão (a Opção B
fica documentada como caminho).

## Consequências

### Positivas
- Uma base de código web, empacotável como PWA; alinhada à visão.
- Backend forte para o que é o núcleo de valor (extração, deduplicação, agregação).

### Negativas / trade-offs aceitos
- Acesso a hardware (câmera) e distribuição em loja ficam limitados frente ao nativo.

### Neutras
- Exige toolchain de frontend (Vite/React) além do PHP — coberto pela `setup-ambiente`.

### Para o time
- **Impacto:** destrava a primeira estória de implementação do EPIC-000.
- **Spike de validação:** recomendado — "hello world" da stack subindo (Laravel+Inertia+React+Postgres)
  + um scan de QR de teste lendo a URL da SEFAZ-SP.

## Plano de verificação

- **Como verificar conformidade:** o app em `app/` roda a stack acordada; PRs de UI usam React via Inertia.
- **Sinais de revisão (quando reabrir):** se os requisitos de coleta exigirem hardware/nativo que o PWA
  não entregue; se o volume/latência de agregações B2B pedir outro armazenamento (aí, ADR próprio,
  datastore-first).
- **Spike proposto:** STORY-000-spike-stack — sobe a stack e um scan de QR de ponta a ponta.

---

## Ratificação do Arquiteto (2026-07-02)

Revisei o stub proposto na inicialização. **Recomendo ratificar a Opção A (web-rica: Laravel +
Inertia/React + PostgreSQL, empacotada como PWA)** — sem alterar a stack. A decisão é coerente
com a visão e com os princípios arquiteturais; abaixo o registro da análise e os ajustes que
incorporei.

### Gate dos 6 princípios centrais

| Princípio | Veredito |
|---|---|
| 1. Simples é o belo | ✅ Um monolito, uma base de código web. Sem microsserviços, sem stack montada à mão. |
| 2. Tudo começa em monolito | ✅ Laravel monolítico servindo React via Inertia. |
| 3. Datastore primário primeiro | ✅ PostgreSQL como único armazenamento no MVP. Qualquer store extra (cache, fila dedicada, search) só via ADR próprio provando com números que o Postgres não basta. |
| 4. Frameworks opinativos | ✅ Laravel é opinativo e produtivo; Inertia elimina o boilerplate de API+SPA. |
| 5. Coesão alta / acoplamento baixo | ✅ Viável — o pipeline de dados (extração/dedup/normalização) deve nascer como módulo coeso e desacoplado da camada web (ver ADR de acompanhamento). |
| 6. 100% local | ✅ Sobe inteiro via `setup-ambiente` (PHP+Postgres+Vite); SEFAZ-SP mockável em teste. |

Complementares relevantes: **#7 reversibilidade** — decisão reversível (Opção B/nativo fica
documentada como saída); **#10 TDD/E2E** — a stack comporta (Pest/PHPUnit + Playwright);
**#11 custo** — sem custo recorrente de unicórnio.

### Incorporação do PDR-001 (design system)

O PDR-001 (adoção do design system Wise-derived) impõe uma restrição de FE que esta stack
**atende bem**: os tokens do DS (cor, tipografia, spacing, raios) mapeiam para o tema da stack
Inertia+React via **`tailwind.config.js` + variáveis CSS** — que é o mecanismo idiomático de
tema desse frontend. Confirmo que a stack ratificada suporta o DS sem adaptação estrutural. O
mapeamento concreto tokens→tema é detalhe do EPIC-000 (Programador segue a sub-skill
`inertia-react`); o DS canônico em si é entrega do Designer (DDR). Não há conflito entre este
ADR e o PDR-001.

### ADRs de acompanhamento (deferred, com gatilho de retomada)

Os pontos **[→ Fase técnica]** da visão **não** são resolvidos aqui — cada um é uma decisão
arquitetural própria, deliberadamente adiada para não decidir sem contexto. Ordem provável e
gatilho:

1. **ADR-001 — Módulo de ingestão e modelo canônico do cupom** (topológico + persistência).
   *Gatilho:* abertura da estória de Coleta. Cobre a interface do adaptador por estado, o
   cupom normalizado e a fronteira ingestão↔web.
2. **ADR-002 — Extração resiliente SEFAZ-SP** (integração). Fila de reprocessamento, tratamento
   de captcha, monitoramento de quebra de layout. *Gatilho:* junto do ADR-001.
3. **ADR-003 — Deduplicação e validação por chave de acesso** (persistência). *Gatilho:* ADR-001.
4. **ADR-004 — Matching de produtos / uso do CCG-GTIN** (persistência). *Gatilho:* quando houver
   volume que justifique reconciliação entre lojas — pode ficar `deferred` além do MVP inicial.
5. **ADR-005 — Integração de pagamento/PIX + KYC de saque** (integração/segurança). *Gatilho:*
   abertura da estória de resgate da Carteira.
6. **ADR-006 — Anonimização de CPF e segregação de bases (LGPD)** (persistência/segurança).
   *Gatilho:* junto do ADR-001 (a coleta já toca CPF).

Manter esses como ADRs separados preserva o princípio #1 (não decidir complexidade imaginada) e
o planejamento em ondas. O **STORY-000-spike-stack** (abaixo) continua sendo o primeiro passo
técnico concreto.

### Recomendação

**Ratificar como `accepted`**, condicionado ao seu aceite humano (registro abaixo). Sem
condicionantes técnicas — a stack está apta a destravar o EPIC-000.

---

## Aprovação humana

- **Status final:** ✅ aceito
- **Aprovado por:** Alexandro
- **Data:** 2026-07-02
- **Forma do aceite:** aprovação explícita em sessão de Cowork (papel Arquiteto)
- **Condicionantes do aceite:** nenhuma

---

## Histórico

- 2026-07-02 — criada como `proposed` (stub de inicialização) — stack default do perfil web-rica, a ratificar pelo Arquiteto.
- 2026-07-02 — ratificada pelo Arquiteto (gate dos 6 princípios + incorporação do PDR-001 + ADRs de acompanhamento mapeados) e **aceita** por Alexandro → `accepted`.
