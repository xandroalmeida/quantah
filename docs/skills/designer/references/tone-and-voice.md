# Tone & Voice — tom profissional do domínio Quantah

Como o Quantah fala com o usuário. Este documento expande o **Princípio #3** (`design-principles.md`) e complementa `voice-and-tone.md` do Design System (que é resumo aplicado no dia-a-dia).

> Os exemplos de microcopy ao longo deste documento usam vocabulário de domínio genérico marcado como exemplo (ex.: *Registro*, *Ação*, *Entidade* — substantivos neutros). Substitua pelos termos do glossário do PO do seu projeto. O método (estrutura de erro, sucesso, vazio, placeholder) é o que importa e não muda.

## A persona em uma frase

> Colaborador ou Analista B2B. Pessoa ocupada, atenção fragmentada, uma decisão concreta do seu dia de trabalho em jogo.

Essa persona molda tudo. Cada decisão de tom passa por: **isso ajuda esse usuário a fazer o trabalho dele?**

## Tom de voz — atributos

- **Profissional.** Como um profissional experiente do setor falando — não como app de delivery.
- **Direto.** Frase curta, verbo no início, sem rodeio.
- **Respeitoso.** Não condescendente, não infantilizado, não culpando o usuário.
- **Calmo.** Sem urgência fabricada ("ATENÇÃO!"). Urgência real merece urgência; o resto, não.
- **Honesto.** Não esconde o que aconteceu. Não promete o que não pode.
- **Sóbrio, mas não árido.** "Ação confirmada" — não "Ação confirmada!! 🎉" nem "Operação efetivada com sucesso conforme parâmetros previamente estabelecidos."

## O que evitar (sempre)

| Categoria | Exemplo a evitar |
|---|---|
| Emojis em microcopy | "Ação confirmada! 🎉" |
| Exclamação fabricada | "Tudo certo!!!" |
| Gírias / infantilizado | "Ops, deu ruim!", "Eita, errou!", "Bora começar!" |
| Mascotes / personagens | "Diga oi para a Defi, sua assistente!" |
| Frase culpando o usuário | "Telefone inválido. Você digitou errado." |
| Jargão técnico em microcopy | "Erro 500", "Timeout", "Null pointer", "Cache miss" |
| Vagueza | "Ocorreu um erro." (qual erro? o que faço?) |
| Urgência fabricada | "ATENÇÃO! Última chance!" |
| Burocratês | "Solicitamos que o usuário providencie a inserção dos dados." |
| Gamificação sem propósito | "🏆 Você concluiu 5 registros! Continue assim!" (se o produto já gamifica de forma funcional — ex.: uma trilha de níveis — adicionar selo decorativo no topo dela é ruído) |

## Padrões de microcopy

### CTAs (call to action)

- **Primário:** verbo no infinitivo + objeto. "Confirmar ação", "Publicar registro", "Cadastrar entidade" (exemplos de domínio).
- **Secundário:** verbo neutro. "Cancelar", "Voltar", "Fechar".
- **Destrutivo:** verbo destrutivo + objeto. "Recusar item", "Descartar alterações" (exemplos de domínio) — sempre confirmar antes de executar.

Evite: "OK", "Confirmar" sozinho (confirmar o quê?), "Enviar" genérico sem objeto, "Clique aqui".

### Labels de campo

- Substantivo curto e claro: "Telefone", "Documento", "E-mail".
- Não termine com `:` (visual já indica que é label).
- Indique obrigatório com `*` ou texto "obrigatório" associado ao label, não só com cor.

### Placeholders

- **Exemplo** do formato esperado, não instrução.
- ✅ `Ex.: 11912345678`
- ❌ `Digite seu telefone`
- Placeholder **não substitui** label (acessibilidade).

### Hint (texto auxiliar abaixo do campo)

- Quando ajuda, descreve restrição ou contexto curto.
- ✅ "Use o telefone com DDD que recebe WhatsApp."
- ❌ "Por favor, insira o número de telefone com DDD, somente dígitos, para que possamos confirmar sua candidatura via SMS."

### Mensagens de sucesso

- Curta, sem emoji, sem exclamação dupla.
- ✅ "Ação confirmada." (ex. de domínio)
- ✅ "Registro publicado." (ex. de domínio)
- ❌ "🎉 Tudo certo!! Ação confirmada com sucesso!!"

### Mensagens de erro

Estrutura: **o que aconteceu** + **o que fazer**. Específico, acionável, não culpa o usuário.

- ✅ "Não encontramos esse telefone. Confira os números e tente novamente."
- ✅ "Não conseguimos salvar agora. Tentar novamente."
- ✅ "Sua sessão expirou. Faça login para continuar."
- ❌ "Erro 500."
- ❌ "Algo deu errado."
- ❌ "Telefone inválido. Você digitou errado."

Para erro de campo (validação):

- Específico ao campo, associado a ele (não global).
- ✅ "Telefone deve ter DDD + 9 dígitos."
- ✅ "E-mail no formato `nome@dominio.com`."
- ❌ "Campo inválido."

### Mensagens de estado vazio

Estrutura: **o que está faltando** + **como conseguir**.

- ✅ "Você ainda não publicou registros. Publicar o primeiro." (ex. de domínio)
- ✅ "Nenhum registro corresponde a esse filtro. Limpar filtros."
- ❌ "Nenhum resultado."
- ❌ "Lista vazia."

### Confirmação de ação destrutiva

Nomeia o objeto. Sem cor sozinha — sempre texto.

- ✅ "Excluir a Entidade "Entidade exemplo"? Esta ação não pode ser desfeita." (ex. de domínio)
- ❌ "Tem certeza?"

### Loading

- Preferir **skeleton** (sem texto) — comunica visualmente.
- Quando texto for necessário: específico, sem `...` redundante se já há skeleton.
- ✅ "Carregando registros" (em ação demorada e justificada)
- ❌ "Carregando..." sozinho em tela branca

### Tooltip

- Curto, complementa — não duplica o label.
- Não use tooltip para informação **essencial** (não funciona em mobile, esconde).

## Vocabulário

**Use o `glossary.md` do PO.** Termos do domínio têm forma canônica. Exemplos genéricos (substitua pelos do seu glossário):

- `Registro` (não "Anúncio", "Oferta")
- `Entidade` (não "Cliente")
- `Atividade` (não sinônimos soltos — a não ser que seja decisão do PO)
- Use sempre os termos do domínio do Quantah — sem abreviações ambíguas em telas
- `Usuário`, `Sessão`, `Conta` — termos genéricos seguindo padrão da web

**Não rebatize.** Se o PO diz "Entidade" ou "Registro", você não escreve "Cliente" ou "Anúncio" porque "fica mais natural". A consistência terminológica é parte do tom profissional.

## Acentuação e pontuação

- **Acentuação portuguesa correta sempre.** Termos com acento (ex.: "operação", "histórico") devem ser grafados corretamente. Acento ausente em produto sério parece desleixo.
- **Sem ponto final em label de campo.** Mas com ponto final em mensagem de frase completa.
- **Sem exclamação dupla.** Uma já é demais na maioria dos casos.
- **Aspas duplas** em texto português ("texto"), não simples ('texto').
- **Travessão** em diálogos/listas: "—" (em-dash), não "-".

## Quando o usuário erra (microcopy compassivo)

A diferença entre tom profissional respeitoso e tom técnico árido aparece aqui:

❌ Árido / culpa:
- "Telefone inválido. Digite corretamente."
- "Campo obrigatório."
- "Acesso negado."

✅ Profissional / acolhe:
- "Não encontramos esse telefone. Confira os números."
- "Este campo é obrigatório."
- "Você não tem acesso a esta área. Fale com o administrador da sua conta."

(Os exemplos acima usam vocabulário neutro; ajuste ao glossário do seu projeto.)

**Princípio:** o sistema falhou em comunicar bem se o usuário errou — não o usuário em ser burro.

## Quando o sistema erra

Honestidade > polidez performática.

✅ "Não conseguimos salvar agora. Tentar novamente."
✅ "Algo deu errado do nosso lado. Já fomos notificados. Se persistir, contate o suporte."
✅ "Sua conexão caiu. Verificando..."

❌ "Erro inesperado." (vagueza)
❌ "Por favor, tente novamente mais tarde." (sem informação útil)
❌ "Ocorreu uma falha em ESC_ERROR_42." (jargão)

## Internacionalização futura

O Quantah pode ganhar i18n no futuro. Hábitos que ajudam desde já:

- **Microcopy em tabela única no spec** facilita extração futura.
- **Frase curta** traduz melhor que frase longa.
- **Evite trocadilho e expressão idiomática** — não viaja.
- **Use placeholders nomeados** (`{entidade}` em vez de concatenação): "Ação de {usuario} confirmada em {entidade}." (ex. de domínio)

## Checklist de revisão de tom

Antes de marcar spec como `ready`:

- [ ] Toda copy está em tabela única.
- [ ] Sem emoji em copy do produto.
- [ ] Sem exclamação fabricada.
- [ ] Sem gíria ou infantilização.
- [ ] Erros têm "o que aconteceu" + "o que fazer".
- [ ] Estados vazios instruem o próximo passo.
- [ ] Vocabulário bate com o glossário do PO.
- [ ] Acentuação correta em todos os termos.
- [ ] CTAs primários: verbo no infinitivo + objeto.
- [ ] Sem jargão técnico exposto ao usuário.
