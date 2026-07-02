# Ofício do screen spec

Como escrever um spec de tela que **evita retrabalho** e funciona com o Programador trabalhando em paralelo. Use junto com `templates/screen-spec.md`.

## O que torna um spec bom

Um spec bom tem três propriedades:

1. **Auto-suficiente.** O Programador implementa a tela inteira sem precisar perguntar nada de UX em nenhum estado.
2. **Honesto sobre estados.** Caminho feliz não é a tela — é só um dos estados. Spec sem vazio, loading e erro é meio spec.
3. **Vivo até o merge.** Spec acompanha a estória; mudança é registrada com data e motivo.

Spec ruim típico:

- Só caminho feliz desenhado, estados de erro como afterthought.
- Layout só desktop ("mobile a gente adapta").
- Microcopy genérico ("Erro!", "Sucesso!", "Carregando...").
- Identificadores de teste ausentes — E2E acopla a estrutura frágil do DOM.
- Sem referência ao DS — componentes "fantasma".
- Sem critério para o tablet — Programador chuta.

## Sequência de produção

A ordem importa. Pular passo gera o tipo errado de spec.

### 1. Leia a estória inteira

CAs, contexto, "fora de escopo", documentos referenciados. Você **não duplica** CAs no spec — você os atende.

### 2. Identifique os estados

Antes de qualquer sketch, **liste os estados** desta tela:

- Caminho feliz / preenchido
- Loading (primeiro fetch, refresh)
- Vazio (primeira-vez sem dados, ou filtro sem resultado)
- Erro (por tipo previsível: rede, permissão, dado inválido, inesperado)
- Sem permissão
- Parcial / degradado (quando parte dos dados falha)
- Primeira-vez vs recorrente (se aplicável)

Estados esquecidos viram bugs em produção. Liste antes de desenhar.

### 3. Rabisco mobile primeiro

Sketch ASCII/SVG grosseiro do **caminho feliz mobile**. Não polido. Resolve fluxo e hierarquia, não pixel.

### 4. Rabisco desktop

Sketch ASCII/SVG do mesmo estado em desktop. O espaço extra tem propósito — declare qual.

### 5. Sync com Programador (≤15 min)

Veja `collaboration-with-developer.md`. Ajuste o rabisco com base nas limitações técnicas conhecidas.

### 6. Detalhe os estados restantes

Em ordem: vazio → erro → loading → sem permissão → parcial. Cada um com sketch + microcopy.

### 7. Microcopy completo na tabela

Toda copy visível listada **em um só lugar** (Seção 5 do template). Revisar tom usando `tone-and-voice.md`.

### 8. Identificadores estáveis para teste

Sugira **nomes lógicos** (ex.: `item-card-acao-btn`) para os elementos que os testes (unit/integração/E2E) da stack (Inertia + React) vão precisar ancorar — é um **contrato no spec**. O Programador materializa cada nome lógico no identificador de teste estável da stack ativa (a API concreta está na sub-skill de FE). Convenção sugerida no template.

### 9. Notas de acessibilidade específicas

Além do piso (`accessibility-basics.md`), o que **desta tela** merece atenção? Foco inicial, ordem de tab não óbvia, live regions, ícones-ação.

### 10. Exceções ao DS, se houver

Toda divergência do DS é declarada com justificativa. Sem justificativa = desvio, não exceção.

### 11. Protótipo HTML fiel navegável

Antes do `ready`, você produz o protótipo HTML em `design/screens/STORY-XXX-<slug>/index.html` cobrindo **todos** os estados especificados e ambos os viewports. Microcopy = exatamente a tabela da seção 5. Tokens reais do DS. Sem build, sem CDN — abre clicando. Detalhe completo em `html-prototype-craft.md`.

Apresente o protótipo ao humano via `mcp__cowork__present_files` para captura de validação **antes** de marcar `ready`. Sem validação humana do protótipo, o spec continua em `draft`.

### 12. Marque `status: ready` e avise no PR/estória

Spec sai de `draft` quando todos os estados estão cobertos, microcopy está completo, **protótipo HTML foi entregue e validado pelo humano** (com data registrada em `prototype_last_validated_at` no frontmatter).

## Como escrever cada seção

### Objetivo da tela

Uma frase. Se você precisa de duas, a tela está complexa demais (Princípio #1). Exemplo:

✅ "Permitir ao Analista B2B criar um novo *Registro* (ex. de domínio) para uma *Entidade* já cadastrada."

❌ "Tela de *Registros*, mostrando entidades cadastradas, com possibilidade de filtrar, ordenar, criar novo registro, ver histórico de atividades anteriores e iniciar uma nova, além de exportar histórico."

(Esta última é 5 telas em uma.)

### Fluxo

Em três blocos:

- **Entrada:** de onde chega; o que precisa ser verdade antes (sessão, permissão, dado pré-carregado).
- **Ações possíveis:** primárias e secundárias, com destino de cada uma.
- **Saída:** o que acontece após sucesso/cancelamento/erro recuperável.

### Layout

Sketches simples. Não precisa ser bonito; precisa ser preciso quanto a:

- Hierarquia (o que vem primeiro visualmente, o que é secundário).
- Componentes do DS usados (referencie pelos ids: `button.primary`, `card`, etc).
- Espaçamento e alinhamento em prosa curta (não precisa medir em pixel — usa tokens).
- Como o espaço extra do desktop é usado (não é "mobile esticado").

Exemplo de sketch ASCII funcional (os rótulos descrevem o **papel** de cada componente — estrutura de tela, navegação inferior/lateral; o componente concreto da stack ativa vive na sub-skill de FE. Vocabulário de domínio genérico marcado como exemplo — *Registro*, *Entidade*):

```
Mobile (compact ≤599dp) — estrutura de tela + navegação inferior
+----------------------------------+
| ← Voltar       Novo Registro      |
+----------------------------------+
|                                  |
| Escolha a Entidade               |
|                                  |
| [ 🔍  Buscar...                ] |
|                                  |
| ┌────────────────────────────┐  |
| │ • Entidade exemplo A       │  |
| │   Local exemplo            │  |
| └────────────────────────────┘  |
| ┌────────────────────────────┐  |
| │ • Entidade exemplo B       │  |
| │   Local exemplo            │  |
| └────────────────────────────┘  |
|                                  |
+----------------------------------+
| [ Publicar Registro           ]  |
+----------------------------------+
```

```
Web/Desktop (large ≥1200dp) — estrutura de tela + navegação lateral
+--------+-----------------------------------------------------+
|        | Novo Registro                                       |
| nav    +-----------------------------------------------------+
| rail   |                                                     |
|        |  Escolha a Entidade                                 |
|        |                                                     |
|        |  [ 🔍  Buscar...                                  ] |
|        |                                                     |
|        |  +------------------+ +------------------+         |
|        |  | Entidade A       | | Entidade B       |         |
|        |  | Local exemplo    | | Local exemplo    |         |
|        |  +------------------+ +------------------+         |
|        |                                                     |
|        |                          [ Publicar Registro    ]   |
+--------+-----------------------------------------------------+
```

### Estados

Para cada estado:

- **Quando ocorre.** Condição precisa que dispara.
- **Sketch.** Como fica visualmente.
- **Microcopy.** Texto exato.
- **Caminho de saída.** O que o usuário pode fazer dali.

### Microcopy

Toda copy visível em **uma tabela única**. Facilita:

- Revisão de tom (lê de cima a baixo, pega inconsistência).
- Tradução futura (i18n).
- Cruzar com glossário do PO (vocabulário do domínio).

Campo com formato conhecido (CPF, CNPJ, PIS, telefone, CEP, dinheiro) **declara sua máscara/formato** junto do placeholder na tabela — é contrato no spec (o protótipo e a implementação seguem), evita improviso. A máscara é ajuda de UX; a validação canônica e o valor persistido (sem máscara) são do servidor.

Campo de **data, hora ou data+hora** entra por **seletor** (não por digitação livre de formato) e **declara na tabela** a **granularidade** (só-data / só-hora / data+hora), os **limites** (min/max — ex.: "não permite data futura") e o **fuso de exibição**. É contrato no spec. O seletor é ajuda de UX; o valor canônico é ISO 8601 e a validação/persistência com fuso são do servidor. Máscara de texto (`dd/mm/aaaa`) fica só como fallback de digitação.

### Identificadores de teste

Convenção sugerida: `screen-<slug>-<elemento>` onde possível. Programador pode mudar se o framework tem padrão próprio (ele decide); o que importa é que existam **estáveis**, para o E2E não acoplar à estrutura do DOM.

### Exceções ao DS

Tabela: o que diverge | por quê | vai virar DDR?

Se a coluna "vai virar DDR" tem dois ou mais "sim" no mesmo spec, pare — você está propondo design novo demais para uma estória. Discuta com o PO.

## Sinais de spec pronto

- Você consegue dizer, sem olhar a estória, o objetivo da tela em uma frase.
- Cada estado tem sketch + microcopy + caminho de saída.
- Layout mobile e desktop ambos presentes; tablet só se mudar comportamento.
- Microcopy completo na tabela; tom revisado.
- Identificadores de teste sugeridos.
- Acessibilidade revisada (contraste, foco, teclado, alvos de toque).
- Exceções ao DS declaradas com justificativa.
- **Protótipo HTML em `STORY-XXX-<slug>/index.html`** existe, cobre todos os estados, abre clicando, microcopy bate com a tabela, foi apresentado e validado pelo humano.

## Sinais de spec mal feito

- "Tá quase, faltam só os erros."
- "Mobile a gente vê depois."
- Microcopy "lorem ipsum" ou genérico ("Erro!").
- Sem referência a componente do DS — tudo "inventado para esta tela".
- Spec atualizado pela última vez antes do código começar — não acompanhou a evolução.
- **Spec marcado `ready` sem protótipo HTML** — ou com protótipo gerado e nunca apresentado ao humano para validação.
- **Protótipo divergente do spec** (microcopy diferente, estado faltando, fluxo errado) — engana o validador.
