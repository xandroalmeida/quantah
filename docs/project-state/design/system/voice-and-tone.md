# Voice & Tone — Design System Quantah

> Como o Quantah fala com o usuário. Alinha o tom "amigável e recompensador" (app do
> Colaborador) com o tom "sério e analítico" (Quantah Intelligence, B2B) — ver `docs/visao.md`
> §11.3. Vocabulário de domínio vem do glossário do PO; não rebatizar termos.

## Tom

- **Direto, simples, respeitoso.** O usuário típico é não-técnico (Colaborador ou Analista
  B2B). Fale como um colega prestativo, não como sistema.
- **Sem entusiasmo performático.** "Cupom aceito." > "Uhuu! Deu certo! 🎉"
- **Sem culpar o usuário.** "Não conseguimos ler esse cupom." > "Cupom inválido — verifique."
- **Sem jargão técnico.** "Não conseguimos enviar agora. Tente em alguns minutos." > "Erro 500."
- **Frase curta vence frase elegante.** O Colaborador lê no celular, em pé, entre compras.

## Duas faces, uma marca

| Face | Público | Tom |
|---|---|---|
| App Quantah (coleta) | Colaborador | Amigável, simples, recompensador. Celebra com discrição. |
| Quantah Intelligence | Analista B2B | Sério, credível, analítico. Preciso, sem informalidade. |

Taglines de referência (visão §11.2): app → "Cada nota conta."; B2B → "Do cupom ao insight."

## Padrões de microcopy

| Situação | Padrão | Exemplo |
|---|---|---|
| CTA primário | verbo infinitivo curto | "Enviar cupom" |
| CTA secundário | verbo neutro | "Cancelar" |
| Confirmação destrutiva | nomeia o objeto | "Remover este cupom?" |
| Sucesso | curto, sem emoji | "Cupom aceito. R$ 0,03 creditados." |
| Erro recuperável | o que houve + o que fazer | "Não foi possível ler o QR. Tentar de novo." |
| Vazio | o que falta + como conseguir | "Você ainda não enviou cupons. Enviar o primeiro." |
| Loading | preferir skeleton, sem texto | — |
| Placeholder | exemplo, não instrução | "Ex.: 11912345678" |

## Emojis e ilustração

Sem emojis no produto. Ilustração só quando comunica (estado vazio com instrução, onboarding) —
nunca decorativa. Gamificação (pontos, ranking) usa a linguagem visual do DS, com sobriedade —
sem "cara de app de cupom de desconto" (visão §11.4).
