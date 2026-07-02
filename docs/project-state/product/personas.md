# Personas — Quantah

> Persona não é "usuário genérico" — é alguém com contexto, dor e gatilho de uso concreto.
> Fonte: `docs/visao.md` §4. No MVP, o foco de produto é o **lado da coleta** (a demanda B2B
> existe, mas só é atacada após validar volume — ver `north-star.md`).

---

## P1 — Colaborador (lado da coleta) — PERSONA PRIMÁRIA DO MVP

### P1a — Colaborador Casual
**Quem é.** Consumidor brasileiro em SP que faz compras de rotina (mercado, farmácia, posto)
e recebe NFC-e com QR Code. Não é técnico, usa o celular para o dia a dia.

**Job-to-be-done.** "Quando termino uma compra e recebo o cupom, quero ganhar algo de volta
sem esforço — nem que seja saber se paguei caro — para não sentir que joguei o cupom (e o
dado) no lixo."

**Dor.** O cupom hoje não serve pra nada depois da compra; controlar gasto dá trabalho; não
há retorno por gerar um dado que outros lucram.

**Gatilho de uso.** Acabou de comprar, cupom na mão (ou QR na tela do PDV). Precisa ser
escanear-e-pronto. Cashback e a curiosidade de "paguei caro?" puxam o primeiro envio;
notificação/sorteio puxam a recorrência.

**Sucesso para ele.** Enviou em segundos, viu o crédito na hora, e às vezes descobre que
pagou acima/abaixo da média. Volume individual baixo — mas é a **maioria**.

### P1b — Colaborador Engajado ("caçador")
**Quem é.** Usuário que otimiza recompensas; envia grande volume, inclusive juntando cupons
de terceiros (comportamento **desejável**, salvaguardado pela deduplicação).

**Job-to-be-done.** "Quero maximizar cashback, pontos e posição no ranking enviando o máximo
de cupons válidos possível."

**Dor.** Falta de um alvo/meta claro; atrito em enviar muitos cupons em sequência; medo de
não ser pago ou de o cupom ser rejeitado sem explicação.

**Gatilho de uso.** Gamificação (ranking, desafios, metas), acúmulo de saldo, sorteio quando
ativo. Envia em lote, com frequência.

**Sucesso para ele.** Envio em lote fluido, feedback claro de aceite/recusa por cupom,
progresso visível de pontos/ranking e saldo resgatável.

**Por que essa persona importa no MVP.** É quem gera **densidade de dados** rapidamente — o
que a north-star de volume mede. O desenho de gamificação e do fluxo de envio deve favorecê-la
sem sacrificar a simplicidade que o Casual precisa.

---

## P2 — Analista B2B (lado do consumo) — PERSONA DE RECEITA, PÓS-MVP

**Quem é.** Analista de Trade/Insights na indústria (CPG), gestor de Pricing/Categoria no
varejo, ou pesquisador em consultoria/instituto de dados.

**Job-to-be-done.** "Quero saber o preço praticado dos meus produtos e dos concorrentes por
rede, região e período — com frescor e granularidade que os índices oficiais não dão — para
tomar decisão de preço, sortimento e trade."

**Dor.** Dado de preço praticado é caro, defasado, pouco granular ou obtido por pesquisa
manual de campo.

**Gatilho de uso.** Ciclo de decisão de pricing/trade; necessidade de benchmark competitivo;
análise de inflação/cesta.

**Sucesso para ele.** Recebe inteligência agregada e anônima (nunca dado individual
identificável), confiável e representativa, entregue por painel/API.

**Nota de sequência.** No MVP não construímos os produtos B2B — apenas garantimos que a base
coletada tenha os atributos (item, loja, região, data, preço) que tornam esses produtos
possíveis depois. As estórias de produto B2B só entram após a validação de volume.
