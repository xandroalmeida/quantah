# Prompt para o Claude Code — executar o EPIC-007

Copie e cole o bloco abaixo no Claude Code, com a pasta `quatah` aberta.

---

Você vai planejar e executar por completo o **EPIC-007 — Refinamento da experiência B2C mobile** deste
projeto (Quantah). Trabalhe seguindo o método do próprio repositório — não improvise processo.

**1. Contexto obrigatório (leia antes de agir):**
- `AGENTS.md` e `docs/skills/_project.md` (stack, caminhos, convenções, glossário).
- O sistema de papéis em `docs/skills/` — atue como **arquiteto**, **designer**, **programador** e
  **validador** conforme cada estória pedir; carregue a skill do papel e siga o método dela, sem cruzar papéis.
- `docs/skills/po/references/quality-standards.md` e o `agent-task-format.md` do repositório.
- O épico e todas as estórias: `docs/project-state/epics/EPIC-007-refinamento-experiencia-b2c-mobile/`
  (`epic.md`, `design-handoff.md`, e `stories/STORY-033..037`). O `index.json` em `docs/project-state/`
  é a fonte de verdade do estado — mantenha-o atualizado.

**2. Ambiente:** prepare o ambiente e suba o preview (sub-skill `setup-ambiente`) antes de codar, para
validar em `http://localhost:8000` e rodar os testes/E2E.

**3. Plano primeiro:** antes de escrever código, me apresente um **plano de execução** das 5 estórias
respeitando dependências e a ordem sugerida do épico (033 e 035 primeiro; 034 em paralelo por ser a maior,
com dado novo + tela; 036 rápida; 037 fecha). Sinalize onde vai nascer decisão registrável — provável
**IDR** em STORY-034 (capturar o nome do emitente na extração SEFAZ + coluna `nome_emitente`) e em
STORY-035 (semântica da janela de validade). Se faltar decisão de arquitetura, faça o spike de arquiteto e
registre o ADR/IDR — não decida por baixo dos panos. Aguarde meu OK no plano; depois execute tudo.

**4. Execução — para cada estória:** siga TDD + **E2E em browser real** (Dusk), atenda todos os critérios
de aceite (CA) e a DoD da estória. STORY-034 é `requires_design`: produza a spec/protótipo da tela de
detalhe do cupom (modelo paralelo, brief no `design-handoff.md`) e valide comigo antes de finalizar.
Requisitos transversais desta onda: **100% pt-BR** (formatos R$, dd/mm/aaaa, America/Sao_Paulo), **a11y AA**
(alvos ≥48px, foco, aria), **mobile-first sem overflow**, sobre o Design System (verde como único accent),
cobertura ≥80% no código novo. **Verifique no celular/emulador**, não só no desktop.

**5. Estado e higiene:** conforme cada estória avança, atualize o `index.json` (status → `done`), preencha
as "Notas do agente" da estória (decisões, descobertas, evidências) e indexe telas/decisões novas. Ao final,
rode a **STORY-037 (validação)** como validador e emita o relatório do épico. **Não faça `git push`** —
commits ficam locais (decisão do PO); este repo não tem remote do template.

**6. Entrega:** ao terminar, quero o épico **fechável**: 033–036 `done` + validação `approved`, tudo
funcionando em homologação no celular (menu inferior fixo em todas as telas, detalhe do cupom com itens em 1
toque, cupom fora da janela rejeitado com motivo, sem excluir conta, menu de escanear renomeado). Me entregue
um resumo do que mudou, os links de evidência (testes/E2E verdes, telas) e as ressalvas que viraram wishlist.

Se algo estiver ambíguo ou bloquear, pare e me pergunte antes de assumir.
