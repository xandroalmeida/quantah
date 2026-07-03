---
idr_id: IDR-005
slug: anonimizacao-cpf-no-conteudo-do-qr
title: Anonimização de CPF também no conteúdo do QR (qr_conteudo), na fronteira de ingestão
status: accepted
decided_at: 2026-07-03
decided_by: programador
owner_agent: claude-programador-story011
related_story: STORY-011
related_adrs: [ADR-006, ADR-001]
related_idrs: []
supersedes: null
superseded_by: null
created_at: 2026-07-03
updated_at: 2026-07-03
---

# IDR-005 — Anonimização de CPF também no `qr_conteudo`, na fronteira de ingestão

## Contexto

A ADR-006 (Opção A: descartar CPF) posiciona o `AnonimizadorCpf` no **ponto de normalização**
(dentro do `SpSefazAdapter`), escovando o **retorno da SEFAZ** antes de qualquer persistência.
Ao implementar a STORY-011 (CA-1: "nenhum CPF em claro em nenhuma tabela do modelo canônico"),
apareceu um segundo vetor que a ADR não endereça explicitamente: a coluna **`cupons.qr_conteudo`**
(criada na STORY-010) guarda o **conteúdo bruto do QR colado pelo usuário**, e é gravada no
`IngestaoCupomService::persistirPendente` — **antes** e **fora** do caminho do adaptador.

O QR legítimo de SP (`p=chave|versão|amb|token|hash`) não carrega CPF. Mas o campo aceita colagem
livre (STORY-009): um usuário pode colar uma URL/texto com um CPF grudado como lixo (`&cpf=...`).
Como `qr_conteudo` é coluna de tabela canônica, deixá-lo passar sem escovar violaria o CA-1.

## Decisão

> **Escovo o `qr_conteudo` com `AnonimizadorCpf::limparTexto()` na fronteira de ingestão
> (`persistirPendente`), antes de gravar — estendendo a defesa da ADR-006 do ponto de
> normalização para o ponto de ingestão.**

Para isso, expus a rotina de escovar texto como método público estático
(`AnonimizadorCpf::limparTexto()`), reutilizado pelo escovar interno do payload. A chave assinada
(`p=chave|...`) é **preservada** — só o padrão de CPF vira `[CPF-REMOVIDO]`. A extração posterior
continua funcionando porque nem a chave de 44 dígitos nem o `token`/`hash` do QR real casam com o
padrão de CPF (11 dígitos com fronteira de palavra), verificado em teste.

## Por quê

- **CA-1 é absoluto** ("nenhuma tabela do modelo canônico"): a garantia tem de cobrir toda coluna
  persistida, não só as que vêm do retorno da SEFAZ. `qr_conteudo` é uma delas.
- **Minimização (visão §7):** dado pessoal que não precisa existir não deve ser gravado, venha de
  onde vier. Defesa em profundidade barata e alinhada ao princípio.
- **Automatizável > documentável (ADR-006 F6):** trava por teste de regressão
  (`AnonimizacaoCpfLgpdTest::test_cpf_no_conteudo_do_qr_nao_e_persistido`), não por aviso.
- **Não reabre a ADR-006:** é a mesma decisão (descartar), aplicada num segundo ponto de entrada.
  Não muda a estratégia (não vira hash/cifra), só amplia a cobertura.

## Alternativas consideradas

- **Escovar só o retorno da SEFAZ (status quo da ADR-006):** deixaria `qr_conteudo` como brecha —
  reprovada pelo CA-1.
- **Injetar o `AnonimizadorCpf` no `IngestaoCupomService` (instância):** mudaria o construtor
  (`SefazAdapter ...$adaptadores`) e todos os call sites de teste. Método estático resolve com
  churn mínimo e sem tocar a fronteira de DI — a escova de texto é função pura, sem estado.
- **Escovar na camada web (controller):** mais frágil (todo caminho de entrada teria de lembrar);
  a fronteira de ingestão é o ponto único por onde todo cupom passa.

## Consequências

### Para outros agentes
- Todo novo campo persistido que possa carregar texto livre do usuário deve passar pela escova na
  fronteira de ingestão — não confie só na anonimização do adaptador.
- `AnonimizadorCpf::limparTexto()` é o utilitário público para escovar texto avulso.

### Para o projeto
- Zero dependência nova. Um método estático a mais no anonimizador existente.
- Regressão automatizada cobre o vetor (varredura de banco inteiro + asserção específica no QR).

### Trade-offs aceitos
- Escova roda em todo `qr_conteudo`, mesmo quando não há CPF (custo desprezível: um `preg_replace`).
- Teórico falso positivo se um `token` de QR fosse 11 dígitos isolados — não ocorre no QR real de
  SP (token curto; chave de 44 dígitos e hash hex não casam o padrão). Revisável se surgir outro
  estado com QR de formato diferente (aí é trabalho de adaptador/ADR-002).

## Como verificar

- `Tests\Feature\Coleta\AnonimizacaoCpfLgpdTest` — `test_cpf_no_conteudo_do_qr_nao_e_persistido`
  (o CPF colado não sobrevive, a chave assinada sim) e `test_regressao_banco_inteiro_sem_cpf...`
  (varredura de todas as tabelas canônicas). Se alguém remover a escova, os testes quebram.

## Tipo

- [x] **Padrão transversal**: onde a anonimização de CPF roda (fronteira de ingestão além do adaptador).
- [ ] **Workaround**
- [ ] **Convenção interna**
- [ ] **Otimização**
- [ ] **Refatoração estrutural**

---

## Histórico

- 2026-07-03 — criada como `accepted` por programador (sessão claude-programador-story011)
  durante STORY-011.
