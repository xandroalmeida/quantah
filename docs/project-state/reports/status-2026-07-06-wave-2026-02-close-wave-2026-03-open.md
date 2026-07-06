# Status report — 2026-07-06 — Fechamento da WAVE-2026-02 e abertura da WAVE-2026-03

- **Tipo:** fechamento de onda + abertura de onda
- **Gerado por:** PO (Alexandro / Claude)
- **Referências:** PDR-004 (modelo de pontos), PDR-005 (escopo da Onda 3)

## Resumo executivo

A WAVE-2026-02 ("De POC a produto") fecha com **5/5 épicos done**: landings B2C/B2B, identidade e
acesso do Coletador (Google + e-mail/senha), 3 áreas segmentadas por RBAC, jornada B2C mobile completa
em pt-BR e PWA instalável. Única pendência: **gate manual de device do EPIC-008** (install Android/iOS,
SW ativo, Lighthouse) — com o Alexandro, não bloqueante (IDR-016).

Abre a **WAVE-2026-03 ("Remuneração por pontos gamificados")**: o Alexandro decidiu (PDR-004) substituir
o rate fixo de cashback (R$ 1/R$ 1.000) por **pontos por cupom**, calculados por motor de regras
configurável (CNAE do emitente, itens únicos, valor, itens com bônus) e convertidos em R$ por resgate
manual com mínimo (taxa vigente no resgate). O saldo legado será **convertido em pontos** pela taxa
inicial. Mudanças de parâmetro valem só dali pra frente, administradas em tela do Backoffice.

## Fechamento — WAVE-2026-02

| Épico | Veredito | Observação |
|---|---|---|
| EPIC-004 Acesso e áreas | approved_with_pending | F-NB-1 → WISH-004 |
| EPIC-005 Portas de entrada | approved | ressalvas → WISH-005/006 |
| EPIC-006 Jornada do Coletador | approved | R1–R6 não-bloqueantes |
| EPIC-007 Refinamento B2C mobile | approved | — |
| EPIC-008 PWA instalável | pending_device | gate manual com o PO (IDR-016) |

**Aprendizado da onda:** o funil B2C existe de ponta a ponta; o que falta para o piloto não é
superfície, é **incentivo definitivo** — mudou a prioridade do rascunho de next-wave (piloto adiado em
favor dos pontos, racional no PDR-005).

**Dívidas que seguem abertas:** WISH-004 (scanner CI), WISH-005 (chaves i18n), WISH-006
(observabilidade), gate device EPIC-008, restyle Breeze do Perfil (R5).

## Abertura — WAVE-2026-03

- **Hipótese:** incentivo paramétrico por pontos sustenta recorrência com custo de aquisição
  controlável, mantendo a tese de remuneração por serviço (premissa regulatória do PDR-004).
- **Épicos (em ordem):** EPIC-009 Enriquecimento cadastral do emitente (ready, decomposto em
  STORY-039 spike arquiteto → 040 → 041 → 042 validação) · EPIC-010 Motor de pontos (draft) ·
  EPIC-011 Conversão, resgate e migração (draft) · EPIC-012 Configuração no Backoffice (draft).
- **Próximo passo imediato:** executar a **STORY-039** (spike do Arquiteto — API pública RFB, filas,
  cache, motor/ledger).

## Riscos identificados na abertura

1. **API pública de CNPJ** (disponibilidade/limites) — mitigação: fila + cache ≥ 30d + fallback;
   sinal de revisão registrado no PDR-004.
2. **Percepção do Colaborador na migração** saldo R$ → pontos — mitigação: evento auditável no
   extrato e comunicação clara (CA do EPIC-011).
3. **Fronteira regulatória** pontos-com-valor × gamificação — mitigação: premissa registrada no
   PDR-004 (sem sorte, regras públicas, resgate como direito); qualquer regra aleatória para e
   reavalia com jurídico.
4. **Calibragem inicial dos parâmetros** (custo/cupom) — decisão de produto pendente do PO antes do
   EPIC-010 entrar em execução (valores semeados).
