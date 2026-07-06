# Evidência — prova ao vivo das APIs públicas de CNPJ (STORY-039, CA-2)

> Consultas **reais** feitas em 2026-07-06 pelo Arquiteto durante o spike. Sustentam a decisão
> registrada em `ADR-012`. User-Agent usado: `QuantahSpike/STORY-039`. Nenhuma credencial envolvida
> (endpoints públicos, sem autenticação).

## CNPJs consultados

| # | CNPJ | Origem | Razão social (retornada) |
|---|---|---|---|
| 1 | `43.259.548/0028-83` | **Emitente de cupom de homologação** (chave `35260743259548002883…`, fixture `danfe-sp.html`, coleta ao vivo IDR-004/STORY-034) | SUPERMERCADOS CAVICCHIOLLI LTDA |
| 2 | `45.543.915/0982-11` | **Emitente de cupom de homologação** (chave `35240845543915098211…`, evidência STORY-000 `extracao-cupom-2024.json`) | CARREFOUR COMERCIO E INDUSTRIA LTDA |
| 3 | `47.508.411/0001-56` | Emissor NFC-e real de SP (Pão de Açúcar / CBD) — **CNPJ real adicional** para caracterizar rate limit e CNAE distinto | COMPANHIA BRASILEIRA DE DISTRIBUICAO |

> **Nota de honestidade (CA-2):** a base de homologação tem **2 emitentes reais distintos** até hoje
> (coleta nascente — baseline da north-star ainda não medido, PDR-005). Os dois foram provados. O 3º é
> um emissor NFC-e real de SP incluído para completar a prova (≥3 CNPJs reais) e observar outro CNAE;
> **não** é um cupom de homologação. Isso é limitação de dado, não de arquitetura — ver "Notas do agente".

## Campos exigidos pela estória — todos retornados (BrasilAPI, CNPJ #1)

| Campo exigido (CA / EPIC-009) | Campo na API | Valor observado (CNPJ #1) |
|---|---|---|
| Razão social | `razao_social` | SUPERMERCADOS CAVICCHIOLLI LTDA |
| CNAE principal (código) | `cnae_fiscal` | `4711302` |
| CNAE principal (descrição) | `cnae_fiscal_descricao` | Comércio varejista … supermercados |
| Situação cadastral | `situacao_cadastral` + `descricao_situacao_cadastral` | `2` / ATIVA |
| Município | `municipio` | ITU |
| UF | `uf` | SP |
| (bônus, útil ao motor) | `cnaes_secundarios`, `nome_fantasia`, `porte` | presentes |

Catálogo completo de campos da BrasilAPI (CNPJ #3): `uf, cep, qsa, cnpj, pais, email, porte, bairro,
numero, ddd_fax, municipio, logradouro, cnae_fiscal, codigo_pais, complemento, codigo_porte,
razao_social, nome_fantasia, capital_social, ddd_telefone_1, municipio, cnae_fiscal_descricao,
cnaes_secundarios, natureza_juridica, situacao_cadastral, descricao_situacao_cadastral,
identificador_matriz_filial, data_inicio_atividade, data_situacao_cadastral, …` (43 campos).

## Latência e disponibilidade por fonte

| Fonte | Endpoint | CNPJ #1 | CNPJ #2 | Campos completos? |
|---|---|---|---|---|
| **BrasilAPI** | `GET brasilapi.com.br/api/cnpj/v1/{cnpj}` | 200 · 0,52s | 200 · 0,48s | ✅ todos + secundários |
| Minha Receita | `GET minhareceita.org/{cnpj}` | 200 · 0,57s | 200 · 0,30s | ✅ (mesmo shape RFB) |
| CNPJá (open) | `GET open.cnpja.com/office/{cnpj}` | 200 · 0,29s | 200 · 0,23s | ✅ (shape próprio) |
| ReceitaWS | `GET receitaws.com.br/v1/cnpj/{cnpj}` | 200 · 0,30s | 200 · 0,24s | ✅ (shape próprio) |

## Rate limit — observado empiricamente

- **BrasilAPI:** rajada de **8 chamadas consecutivas** → **8×200**, 0,09–0,16s cada. Sem throttle na
  rajada. (Camada de borda com cache; limite público generoso, não documentado como rígido.)
- **ReceitaWS (free):** **429 a partir da 3ª chamada no mesmo minuto** — confirma o limite documentado
  de **3 req/min** no plano gratuito. Inviável como primária; serve só como fallback esporádico.
- **CNPJá open / Minha Receita:** 200 nas chamadas do teste; limites free apertados/variáveis (uso
  ocasional de fallback).

## Conclusão

- **BrasilAPI** entrega **todos** os campos exigidos, com latência ~0,1–0,5s e rate limit generoso o
  bastante para consumo assíncrono em fila com cache ≥30d → **fonte primária**.
- **Minha Receita** (open source, dump completo da RFB, self-hostável) é o **fallback** natural e o
  caminho de evolução para eliminar dependência externa (princípios #3/#6).
- **ReceitaWS/CNPJá** ficam como fallback esporádico por causa do rate limit apertado.
- Comportamento para CNPJ inexistente/baixado: a API responde 404/`ERROR` → tratado como **negócio**
  (emitente sem enriquecimento, cupom NÃO trava — ver ADR-012 §fallback).

## Reprodutibilidade

```bash
UA="QuantahSpike/STORY-039"
for c in 43259548002883 45543915098211 47508411000156; do
  curl -s -A "$UA" -w "\n[%{http_code} %{time_total}s]\n" "https://brasilapi.com.br/api/cnpj/v1/$c"
done
```
