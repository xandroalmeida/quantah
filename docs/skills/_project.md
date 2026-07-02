# _project.md — Configuração do projeto (Quantah)

Este arquivo é a **única fonte de valores específicos do projeto** para todo o sistema de skills.
Ao abrir uma sessão, o agente lê este arquivo primeiro. Os valores abaixo já foram **assados** nas
skills (o texto delas já traz os valores reais do Quantah, não marcadores).

## 0. Fase do projeto

O sistema tem **duas fases** e a fase decide **quais skills estão ativas**.

| Campo | Valor |
|---|---|
| Fase | **`MVP`** |

| Fase | Quem atua | Stack | Objetivo |
|---|---|---|---|
| **`POC`** | só o `idealizador` (os 5 papéis dormentes) | Laravel + Livewire + SQLite | Pessoa não-técnica prova viabilidade comercial |
| **`MVP`** | os 5 papéis profissionais (`po`, `arquiteto`, `designer`, `programador`, `validador`) | perfil da seção 2 | Time profissional transforma o requisito em produto de verdade |

Este projeto **nasceu em `MVP`** (engenharia desde o dia 1), a partir do documento de visão em
`docs/visao.md`. O `idealizador` fica dormente.

## 1. Identidade do produto

| Campo | Valor |
|---|---|
| Nome do produto | **Quantah** |
| Descrição (pitch) | Plataforma de inteligência de preços via NFC-e que coleta cupons fiscais de forma colaborativa (crowdsourcing) para construir uma base de preços do varejo em tempo quase real, monetizada como inteligência de mercado (B2B). Duplo sentido do nome: "**quanto** custa" + "**quantidade** de dados". |
| Público-alvo | Consumidores brasileiros que recebem NFC-e nas compras (lado da coleta) e clientes B2B — indústria/CPG, varejo e terceiros de dados (lado da demanda). |
| Persona A | **Colaborador** — consumidor que envia cupons, movido por cashback, gamificação e (futuramente) sorteio. Inclui o Colaborador Casual, o Colaborador Engajado ("caçador") e o Consumidor-Beneficiário. |
| Persona B | **Analista B2B** — analista de trade/insights na indústria (CPG), gestor de pricing/categoria no varejo, ou analista/pesquisador em consultoria/instituto de dados. Consome inteligência de preços. |
| Quem aprova | **Alexandro** (dono do produto / aprovador humano; responsável de negócio com CNPJ ativo). |

> O produto tem **dois lados distintos**: quem oferta o dado (Colaborador) e quem consome/paga pelo
> dado (Analista B2B). O app de consumidor é o mecanismo de coleta; o painel B2B (Quantah Intelligence)
> é onde está a receita.

## 2. Stack ativa

A opinião de tecnologia vive em **sub-skills de stack** atômicas, em `stacks/`. Os papéis são
agnósticos e carregam as sub-skills ativas para o como-fazer idiomático.

**Perfil escolhido:** **web-rica** (alinhado à decisão de plataforma **PWA** da visão, §12).

| Campo | Valor | Sub-skill |
|---|---|---|
| Banco principal | **PostgreSQL** | `stacks/database/postgres` |
| Backend | **Laravel** | `stacks/laravel` |
| Frontend | **Inertia + React** (PWA instalável) | `stacks/inertia-react` |

**Sub-skills ativas:** `postgres` · `laravel` · `inertia-react`.

> A stack é decisão **revisável** do Arquiteto: o perfil web-rica está registrado como **default** em
> `docs/project-state/decisions/adr/ADR-000-stack-default.md` (status `proposed`), a ratificar ou trocar.
> A visão (§6) prevê extração via scraping da SEFAZ-SP com "adaptador por estado" e modelo canônico do
> cupom — aprofundamento técnico é trabalho do Arquiteto (pontos **[→ Fase técnica]** da visão).

**Decisões de método embutidas** (dos papéis, valem para qualquer stack):

- **TDD + E2E inegociáveis** — teste vermelho antes do código; E2E em ambiente real para cada fluxo.
- **Tudo começa em monolito** — microsserviços só com evidência concreta.
- **Datastore-first** — provar com números antes de adicionar outro armazenamento além do banco primário.
- **Simples é o belo / coesão alta, acoplamento baixo.**

## 3. Layout do estado do projeto

| Campo | Valor |
|---|---|
| Pasta do código do app (`APP_PATH`) | `app/` (API Laravel + Inertia/React) |
| Raiz do estado do projeto (`STATE_PATH`) | `docs/project-state/` |
| Raiz da especificação durável (`SPEC_PATH`) | `docs/especificacao/` |
| Raiz do protótipo de referência (`PROTOTYPE_PATH`) | `docs/prototipo/` |

Layout do projeto:

```
quatah/
  app/                     ← o app (Laravel + Inertia/React; scaffold via setup-ambiente no Claude Code)
  docs/
    visao.md               ← documento de visão (fonte de verdade de produto/negócio/marca)
    skills/                ← os papéis e as sub-skills de stack
    project-state/         ← estado vivo (épicos, sprints, decisões, design, bugs…)
    especificacao/         ← especificação durável
    prototipo/             ← protótipo/handoff de referência
  COMECE-AQUI.md           ← ponto de entrada didático (humano)
  AGENTS.md                ← ponto de entrada do agente
```

Subárvore sob `docs/project-state/`: `epics/`, `sprints/`, `decisions/{adr,pdr,ddr,idr}/`,
`design/{system,screens}/`, `bugs/`, `wishlist/`, `reports/`, `roadmap/`, `index.json`.

## 4. Glossário de domínio

| Termo | Definição no Quantah |
|---|---|
| NFC-e | Nota Fiscal de Consumidor Eletrônica (modelo 65). Traz um QR Code que aponta para o cupom público na SEFAZ. É a fonte do dado. |
| Chave de acesso | Código de 44 dígitos único por cupom (UF, ano/mês, CNPJ do emitente, modelo). Base da deduplicação e da validação. |
| SEFAZ | Secretaria da Fazenda estadual. Hospeda a página pública do cupom (DANFE NFC-e). No MVP, apenas SEFAZ-SP. |
| Cashback | Remuneração direta ao colaborador: R$ 1,00 a cada R$ 1.000,00 em cupons válidos enviados (0,1%). É serviço prestado, não sorteio — livre de autorização. |
| GTIN | Código de barras global do produto. Quando disponível, permite comparar o mesmo item entre lojas; senão, matching por similaridade de descrição. |
| Deduplicação | Salvaguarda anti-fraude: a chave de acesso de 44 dígitos impede contar/pagar o mesmo cupom duas vezes. |
| Quantah Intelligence | Face B2B da marca: painel de preço praticado, share of shelf, market basket, índices de inflação por região. É onde está a receita. |
| Gate de ativação | Trava de configuração que mantém o sorteio desligado até sair a autorização SPA/SCPC (Lei 5.768/71). |

---

### Referência de método

A opinião de tecnologia foi extraída das skills de papel para sub-skills atômicas em `stacks/`
(`postgres`, `sqlite`, `laravel`, `livewire`, `inertia-react`, `flutter`). Os papéis trazem o método
agnóstico e deferem o como-fazer às sub-skills da stack ativa. Trocar a stack = trocar o perfil na
seção 2 e ativar outra sub-skill (via ADR do Arquiteto) — os papéis não mudam.
