# Revisão de fidelidade — Designer · STORY-002

**Papel:** Designer · **Data:** 2026-07-02 · **Escopo:** `requires_design: true` — validar a
**fidelidade do mapeamento** dos tokens do DS para o tema (Tailwind) e o respeito às regras de
ouro na demonstração (hello-world). Não é veredito de implementação (esse é do Validador no fim
do épico) — é a confirmação de fidelidade que a estória pede.

## Método

- Cross-check automatizado token-a-token: `docs/project-state/design/system/tokens.md`
  × `app/tailwind.config.js`.
- Verificação visual em **browser real** (Dusk), viewports **mobile 375px** e **desktop 1280px**
  (`tema-mobile.png`, `tema-desktop.png` nesta pasta).
- Regras de ouro do DS (`design/system/README.md`) conferidas na página renderizada.

## Fidelidade dos tokens — **100% fiel**

- **Cor:** 22/22 tokens presentes com o hex canônico exato (marca, superfície, tinta, semânticas,
  accents terciários).
- **Tipografia:** 12/12 tokens de escala com tamanho **e** entrelinha exatos; família **Inter**
  (display e sans); display no peso **900** (DDR-001) confirmado por estilo computado no browser.
- **Spacing (base-4):** 8/8 (xxs…3xl). **Raio:** 7/7, com **xl=24px** (assinatura). **Breakpoints:**
  md=768 / lg=1024. **Motion:** fast/base/slow = 100/200/300ms. **Elevação:** presente (contraste
  de superfície + hairline).

## Regras de ouro — **respeitadas**

1. Verde `primary` só no **único** CTA ("Enviar cupom"); não é usado como "sucesso". ✓
2. Sem segundo accent de marca (nenhum laranja/ciano na tela). ✓
3. Raio `xl` (24px) no botão e no card; nenhum CTA de canto reto. ✓
4. Ritmo de superfície: página sage (`canvas-soft`) → card branco (`canvas`); elevação por
   contraste, sem sombra pesada. ✓
5. Display em peso 900 no título; 600 no restante (eyebrow/label). ✓
6. Texto do CTA em `on-primary` (near-black) sobre `primary` — nunca branco sobre verde;
   contraste AA (≥ 4.5:1) medido no rgb real; foco por teclado visível. ✓
7. Paridade mobile/desktop: título colapsa de `display-xl` (desktop) para `display-md` (mobile);
   card e CTA reflow sem "desktop encolhido". ✓

## Divergências

- **Nenhuma.** Nada volta para `in_progress`.

## Observações (não-bloqueantes, para telas futuras — EPIC-001)

- Num botão curto, o raio de 24px fica quase "pill" (esperado pela spec `button.primary`); em
  botões largos a assinatura de canto arredondado aparece melhor. Sem ação — é o token correto.
- A biblioteca de componentes React do DS (Button, Card, etc.) é o EPIC-001; aqui o CTA é markup
  de demonstração. Ao criar `<Button variant="primary">`, herdar exatamente estas classes de token.

## Verdicto

> **Fidelidade do mapeamento CONFIRMADA.** O tema reproduz os tokens canônicos 1:1 e a
> demonstração respeita as regras de ouro do DS em mobile e desktop. Gate de design da STORY-002
> satisfeito.
