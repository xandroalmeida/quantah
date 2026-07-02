# Acessibilidade — piso obrigatório

Acessibilidade no Quantah **não é modo separado**. É a única forma de desenhar. Este documento descreve o **piso obrigatório** — o que toda tela atende, sem exceção — e dá heurísticas práticas para o Designer aplicar e verificar.

> Este documento descreve **princípios e o piso WCAG** — agnósticos de frontend. As APIs concretas de acessibilidade (semântica, rótulo, foco) e a forma de testá-las vivem na sub-skill da stack de FE ativa (ver `_project.md` › Stack ativa; ex.: `stacks/flutter/SKILL.md`, `stacks/livewire/SKILL.md`, `stacks/inertia-react/SKILL.md`). O piso WCAG e o método de verificação são os mesmos em qualquer stack.

Para o produto, vale o **WCAG 2.1 nível AA** como referência. Você não precisa decorar a especificação inteira — precisa internalizar os pontos abaixo.

## Por que importa para o Quantah

A persona inclui Colaborador e Analista B2B de qualquer idade — incluindo pessoas com baixa visão, dificuldade motora, fadiga ocular ao fim do dia. Acessibilidade não é "para deficientes" — é para todos os contextos reais de uso (incluindo seu, num celular ao sol, com uma mão segurando o café).

Acessibilidade insuficiente não é trade-off — é bug.

## Os 7 pontos do piso

### 1. Contraste

| Tipo de texto | Contraste mínimo |
|---|---|
| Texto normal (<18pt regular ou <14pt bold) | **4.5:1** |
| Texto grande (≥18pt regular ou ≥14pt bold) | **3:1** |
| Ícone significativo, borda de componente interativo | **3:1** |
| Texto decorativo, logo, desabilitado | (isento, mas evite ofender) |

**Como verificar:** ferramenta de contraste do browser, plugin (axe DevTools, Stark) ou WebAIM Contrast Checker. Toda combinação **cor de texto / cor de fundo** usada em token passa por verificação.

**Sinais de erro:**
- Texto cinza claro (`#C0C0C0`) sobre branco — falha.
- Placeholder cinza claro como fonte primária de informação — falha (placeholder ≠ label).
- Cor primária sobre cor de marca saturada — verificar caso a caso.

### 2. Foco sempre visível

- Todo elemento interativo (botão, campo, checkbox, switch, rádio, item de menu, área tocável custom) tem **indicador de foco visível** quando recebe foco.
- A stack de FE ativa normalmente entrega o indicador por padrão (ver sub-skill). Você **não desliga** isso por estética.
- Na web, foco aparece quando o usuário usa teclado; em mobile, aparece com switch control / focus assistivo.

**Sinal de erro:** "removi o overlay de foco do componente porque ficou feio." → falhou.

### 3. Navegação por teclado completa (web)

- **Tab** percorre todos os interativos na ordem visual (top→bottom, left→right em LTR). A stack de FE ativa normalmente cuida disso para componentes nativos; componente custom precisa de tratamento explícito de foco (ver sub-skill).
- **Shift+Tab** percorre para trás.
- **Enter / Space** ativam botões.
- **Setas** navegam dentro de listas, menus, grupos de seleção (segmented, rádio em grupo).
- **Esc** fecha dialog / bottom-sheet modal / gaveta. Componentes nativos da stack normalmente já fazem isso.
- **Foco-trap em dialog/sheet modal** costuma vir por padrão; componente custom precisa de tratamento explícito (ver sub-skill).
- **Skip link** ("Pular para conteúdo principal") em telas com navegação extensa na web — sugira o nome lógico no spec.

**Como verificar:** rode na web, desplugue o mouse. Consegue fazer a tarefa? Se não, falhou.

### 4. Semântica correta dos elementos

Você desenha — Programador implementa. Mas você sugere no spec **o papel semântico** de cada elemento (o componente concreto que materializa cada papel está na sub-skill de FE):

- **Botão de ação** = componente de botão semântico da stack, nunca uma área tocável "crua" estilizada de botão.
- **Link de navegação** (web) = elemento de link com roteamento real, anunciado como link para o leitor de tela.
- **Label de campo** = campo de formulário com label **associado** ao input (não placeholder no lugar de label).
- **Cabeçalho de seção** = texto marcado como título (heading) para que leitores de tela anunciem como tal e a ordem de headings faça sentido.
- **Lista** = container com semântica de lista.
- **Tabela tabular** = componente que anuncia cabeçalho/linhas corretamente.

Na web, esses elementos exportam ARIA correto; em mobile, viram nós da árvore de acessibilidade nativa (TalkBack/VoiceOver). A forma concreta de marcar cada papel na stack ativa está na sub-skill de FE.

Semântica correta = leitor de tela funciona, busca do browser funciona, comportamento padrão (atalhos, foco) funciona.

### 5. Erros não são só cor

- Borda vermelha sozinha **não basta** — daltonismo é comum.
- Mensagem de erro **textual** vinculada ao campo (associada via a API de validação/erro da stack, que anuncia para leitor de tela — ver sub-skill de FE).
- Ícone de erro com rótulo acessível quando não acompanhar texto visível.
- Resumo de erros no topo do form (form longo) com foco que pula para cada campo com erro.

**Sinal de erro:** "campo fica vermelho quando dá erro" — sem mensagem textual associada, daltônico não sabe.

### 6. Ícone sozinho como ação tem label

- Ícone-ação com **rótulo acessível descritivo** (a stack tem o mecanismo — tooltip que vira label, atributo de acessibilidade etc.; ver sub-skill de FE).
- Em mobile, considerar **label visível** (texto curto abaixo do ícone) para clareza — ajuda usuário não-técnico mesmo que enxergue bem.
- Tooltip **não substitui** rótulo semântico — componente nativo de ícone-botão costuma cuidar disso; área tocável custom precisa de rótulo acessível explícito (ver sub-skill).

**Convenção sugerida ao Programador:** rótulo acessível com verbo + objeto, ex.: "Recusar item" (ex. de domínio).

### 7. Alvo de toque adequado

- Mínimo **48×48 dp** em mobile (WCAG aceita ≥44; convém ≥48).
- Alvos próximos ≥ 8dp de distância para evitar toque errado.
- A stack de FE ativa normalmente tem uma configuração que garante o piso de toque em componentes nativos (ver sub-skill); confirme que está ativa.
- Botões de ação primária visualmente maiores que secundários.

**Sinal de erro:** ícone-ação pequeno (ex.: 16px) sem padding → área de toque insuficiente.

## Heurísticas extras (boas práticas além do piso)

- **Live regions** — toast/banner que muda dinamicamente é anunciado ao leitor de tela (na web, `aria-live="polite"`; a stack tem o equivalente — ver sub-skill).
- **Dialog / bottom-sheet modais** já trazem semântica correta (foco-trap, anúncio como dialog) nos componentes nativos. Não desligar.
- **Form com agrupamento** — campos relacionados anunciados como grupo rotulado (ex.: "Endereço").
- **Máscara de entrada** (campos formatados: documento, telefone, CEP, dinheiro) — a máscara **não** pode impedir colar nem editar o valor, sugere o teclado adequado (numérico para documentos) e **não** substitui o label nem a mensagem de erro textual. Placeholder de formato ≠ label. O atributo concreto (teclado por tipo de campo) vive na sub-skill de FE.
- **Seletor de data/hora** (data, hora, data+hora) — o seletor precisa ser **navegável por teclado**, ter **label associado** e anunciado por leitor de tela, e **sempre permitir digitação como alternativa** ao gesto de calendário/relógio (não obrigar o mouse). Não substitui o label nem a mensagem de erro textual. O controle nativo do browser já entrega isso; seletor custom tem de replicar. A forma concreta vive na sub-skill de FE.
- **Imagem decorativa** — excluída da árvore de semântica (não anunciada).
- **Imagem com conteúdo** — com rótulo acessível descritivo.
- **Animação respeitando "reduzir movimento"** — usuário que pediu menos movimento (preferência do SO) recebe menos. Sugira no spec que animações importantes consultem essa preferência (a stack expõe o sinal — ver sub-skill).

## Como o Designer verifica (antes do merge)

> O checklist abaixo é agnóstico de frontend. As ferramentas/APIs concretas de inspeção de acessibilidade da stack ativa estão na sub-skill de FE (ver `_project.md` › Stack ativa).

Checklist rápido na revisão do que o Programador implementou:

- [ ] Contraste de cada combinação cor texto/fundo verificado (ferramenta de contraste).
- [ ] Tab percorre na ordem visual na web; foco visível em todo elemento.
- [ ] Mouse desconectado na web: consegue completar a tarefa principal só com teclado?
- [ ] Ícone-ação tem rótulo acessível? Área tocável custom tem rótulo semântico?
- [ ] Erros de form têm mensagem textual associada ao campo?
- [ ] Alvos de toque ≥ 48dp em mobile?
- [ ] Dialog/sheet modal fecha com Esc (web) e volta foco ao gatilho?
- [ ] Live region anunciada onde aplicável (toast, banner dinâmico)?
- [ ] Esquema de cor não é o único canal de informação (sempre tem ícone + texto)?
- [ ] Funciona com TalkBack (Android) e VoiceOver (iOS) lendo a tela em ordem coerente?

Se algum ❌, é bloqueio do PR.

## Ferramentas úteis

- **Ferramenta de contraste** (WebAIM Contrast Checker ou equivalente) — verificação de contraste dos tokens de cor antes mesmo de virar tema da stack.
- **Inspetor de acessibilidade da stack ativa** — mostra a árvore de semântica que a stack está expondo (a ferramenta concreta está na sub-skill de FE).
- **Web rodando + axe DevTools / Lighthouse** — confere ARIA gerado no DOM. Bom para piso, não para tudo.
- **TalkBack (Android)** e **VoiceOver (iOS)** — teste real ocasional, principalmente em fluxos críticos (ex. de domínio: cadastro, confirmação de ação, pagamento). Vale ouro.
- **Teclado sozinho na web** — o teste mais simples e mais revelador.
- **Modo "Acessibilidade > Tamanho de fonte: maior" no SO** — verifique se a tela respira com fonte aumentada (a stack normalmente respeita a escala de fonte do SO — ver sub-skill).

## O que NÃO é desculpa

- "É só primeira versão" — acessibilidade básica é cara de adicionar depois.
- "Ninguém vai usar com leitor de tela" — você não sabe.
- "Designer aprovou visualmente" — visualmente ≠ acessivelmente.
- "Componente da lib não suporta" — Programador pode escalar; lib que impede acessibilidade básica é decisão errada de ADR (escala para Arquiteto).
- "Vai retardar a entrega" — bug de acessibilidade é bug. Não entra em produção.

## Quando dúvida, o piso vence

Acessibilidade entra no Princípio #5 (`design-principles.md`). O **piso WCAG 2.1 AA é intransponível** — vence qualquer outro princípio em conflito. Conflito aparente com simplicidade ou tom geralmente se resolve com mais cuidado de design, não com remoção de acessibilidade. Refinamentos acima do piso (boas práticas extras) entram na hierarquia normal de conflito.
