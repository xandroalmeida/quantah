---
idr_id: IDR-009
slug: validacao-cpf-propria-sem-pacote
title: Validação de CPF própria (value object) em vez de pacote dedicado
status: accepted
decided_at: 2026-07-04
decided_by: programador
owner_agent: claude-story017
related_story: STORY-017
related_adrs: [ADR-005, ADR-006]
related_idrs: []
supersedes: null
superseded_by: null
created_at: 2026-07-04
updated_at: 2026-07-04
---

# IDR-009 — Validação de CPF própria (value object) em vez de pacote dedicado

## Contexto

O KYC mínimo do saque (STORY-017, ADR-005) exige validar o **CPF** do titular (que é também a chave PIX
do tipo CPF). A sub-skill de stack (`stacks/laravel/SKILL.md`) registra como **default do template** um
*pacote de rules dedicado* para documentos BR (ex.: `laravel-validation-rules/cpf-cnpj`), e manda registrar
em **IDR** quando se diverge disso. O projeto **não** tinha esse pacote instalado.

## Decisão

> **Decidi implementar a validação de CPF como um value object próprio (`App\Domain\Saque\Cpf`), sem
> adicionar dependência externa.**

`Cpf` normaliza (só dígitos, canônico — `database-method.md`), valida os dígitos verificadores (mod 11),
rejeita repetidos, e oferece máscara de exibição (`mascarar`, para minimização de PII na tela).

## Por quê

- **Algoritmo fechado e pequeno.** A validação de CPF é um cálculo determinístico de ~30 linhas; a
  disciplina de bibliotecas (`library-discipline.md`) manda preferir stdlib/código próprio quando "dá pra
  fazer com clareza" — menos uma dependência para auditar/atualizar (princípios #1/#11).
- **Testável a 100%.** Como VO puro, cobre-se feliz/inválido/borda facilmente (o núcleo do KYC tem
  cobertura alta exigida).
- **Sem CNPJ agora.** O único documento BR necessário nesta onda é o CPF (o Analista B2B/CNPJ é outra
  fase). Trazer um pacote de CPF **e** CNPJ seria carregar mais do que o necessário.

## Alternativas consideradas

- **Pacote `laravel-validation-rules/cpf-cnpj` (default do template):** robusto e cobre CNPJ, mas adiciona
  dependência transversal para um algoritmo trivial e um documento só. Descartado por ora.
- **Rule inline no FormRequest sem VO:** funcionaria, mas o VO concentra normalização + validação +
  máscara num lugar reusável e testável isoladamente.

## Consequências

### Para outros agentes
- **CPF valida-se via `App\Domain\Saque\Cpf`** (`ehValido`/`apenasDigitos`/`mascarar`) — reuse, não
  reimplemente nem traga pacote concorrente sem novo IDR.
- Quando surgir **CNPJ** (perfil B2B), reavaliar: ou estende-se o VO (`Cnpj`/`DocumentoBR`), ou aí sim se
  justifica o pacote `cpf-cnpj` — **reabrir este IDR** nesse momento.

### Para o projeto
- Zero dependência nova. Se o VO precisar migrar para um pacote depois, o ponto de troca é único (`Cpf`).

### Trade-offs aceitos
- Mantemos ~30 linhas de algoritmo no projeto (com testes) em vez de delegar a um pacote. Aceito pela
  simplicidade e por ser código estável (regra de CPF não muda).

## Como verificar
- `tests/Unit/Saque/CpfTest.php` cobre válido (com/sem máscara), DV inválido, repetidos, tamanho, lixo,
  normalização e máscara. Se entrar CNPJ, este IDR é o gatilho de reavaliação (pacote vs. estender VO).

## Tipo
- [x] **Convenção interna**: `Cpf` VO é o ponto único de validação/normalização/máscara de CPF.
- [ ] Padrão transversal · [ ] Workaround · [ ] Otimização · [ ] Refatoração estrutural

---

## Histórico
- 2026-07-04 — criada como `accepted` por programador (STORY-017).
