# Padrões compostos — Design System Quantah

> Combinações recorrentes de componentes. Reduzem a carga cognitiva do usuário não-técnico e
> evitam reinventar a roda. Materializados na stack via `stacks/inertia-react/SKILL.md`.

## `pattern.form`

Campos empilhados verticalmente, label flutuante, erro associado ao campo, `button.primary` no
rodapé.

- Form >5 campos → considerar `pattern.wizard`.
- Validação inline no **blur**, não a cada tecla (a validação canônica é do servidor via
  `useForm`; cliente só faz validação leve de UX).
- Erro nunca é só cor — texto associado, e a mensagem **diz o que fazer**: "Use um e-mail com
  @ e domínio" > "E-mail inválido".
- Campo de formato conhecido usa `input.masked`; data/hora usa `input.datetime` (seletor).

## `pattern.wizard`

Fluxo em estágios. Usar o `stepper`. Mostra progresso ("Passo 2 de 3") e permite voltar sem
perder dado. **Chave para o fluxo de Coleta** (escanear → confirmar → creditar) — cada passo é
uma decisão clara.

## `pattern.listing`

Filtros (bottom-sheet em mobile, gaveta lateral em web), lista virtualizada/paginada com cards,
estado vazio próprio, ordenação. Tabela >5 colunas vira lista de cards no mobile (nunca scroll
horizontal). Ex.: histórico de cupons enviados.

## `pattern.empty`

`empty-state` padronizado com CTA contextual. Cada listagem tem o seu.

## `pattern.error`

Erro recuperável → `snackbar` com ação "Tentar de novo". Erro de tela → página dedicada com
instrução clara e caminho de saída. Em SPA (Inertia), gerenciar foco explicitamente ao trocar
conteúdo.

## `pattern.surface-rhythm` (assinatura visual)

O ritmo de superfície da marca: **página sage (`canvas-soft`) → cards brancos (`canvas`)**. O
contraste carrega a elevação; evita sombra pesada. Momentos de marca usam `card.feature-dark` /
`hero-band-dark` (ink + verde) com parcimônia — é o "tempero", não o prato.
