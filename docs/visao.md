# Quantah — Documento de Visão

**Plataforma de Inteligência de Preços via NFC-e**

**Versão:** 1.0 (Visão de Produto, Negócio e Marca)
**Data:** Julho de 2026
**Público-alvo:** Product Owner, UX/UI, Arquitetura, Desenvolvimento, Jurídico/Compliance
**Status:** Direção definida — pronto para detalhamento de produto e discovery técnico

> Este documento cobre **visão de produto, negócio e marca**. Arquitetura detalhada, modelo de dados (schema) e cronograma serão tratados em documentos posteriores. Ao longo do texto há marcações **[→ Fase técnica]** indicando os pontos que o time de Arquitetura deve aprofundar em seguida.

---

## Sumário

1. Resumo executivo
2. O problema e a oportunidade
3. Visão do produto
4. Personas e usuários
5. Funcionalidades
6. Mecânica de coleta e qualidade do dado
7. Privacidade e LGPD
8. Mecanismos de incentivo e enquadramento legal
9. Roteiro da burocracia SPA/SCPC (sorteio)
10. Modelo de negócio (monetização)
11. Marca Quantah
12. Decisões já tomadas
13. Riscos e mitigações
14. Próximos passos

---

## 1. Resumo executivo

**Quantah** é uma plataforma que coleta, de forma colaborativa (crowdsourcing), os dados das **Notas Fiscais de Consumidor Eletrônica (NFC-e)** que os consumidores brasileiros já recebem em suas compras. Cada NFC-e traz um QR Code que aponta para uma página pública da SEFAZ contendo o cupom completo: dados do estabelecimento, itens comprados, descrições, códigos e preços.

Ao incentivar consumidores a enviarem esses cupons, o Quantah constrói uma **base de dados de preços praticados no varejo, em tempo quase real**, com granularidade de item, loja, região e data. Esse ativo de dados é o produto central e será monetizado via inteligência de mercado (B2B) para indústria, varejo e terceiros de dados.

O consumidor é remunerado e engajado por três mecanismos combinados: **cashback direto**, **gamificação** e **sorteios** (este último sujeito a autorização regulatória, tratada na Seção 8).

**MVP:** aplicativo PWA, cobertura inicial apenas **Estado de São Paulo**, coleta via QR Code, extração de dados por scraping do portal público da SEFAZ-SP com migração planejada para fonte oficial.

**Nome/marca:** Quantah — carrega o duplo sentido de "**quanto** custa" e "**quantidade** de dados"; detalhado na Seção 11.

---

## 2. O problema e a oportunidade

### 2.1 O dado já existe, mas está preso
Todo dia, milhões de cupons fiscais eletrônicos são emitidos no Brasil. Cada um contém dados estruturados de preço no ponto de venda — informação valiosa para indústria, varejo, pesquisa econômica e o próprio consumidor. Hoje esse dado fica disperso, acessível apenas cupom a cupom, sem consolidação.

Vale notar que preços são informações de domínio público — sua disponibilização de forma precisa e pública é determinada pelo Código de Defesa do Consumidor, pelo Decreto 2.181/1997 e pela Lei 10.962/2004. Isso legitima o conceito de consolidar preços praticados.

### 2.2 Quem precisa desse dado
- **Indústria / bens de consumo (CPG):** precisa saber o preço praticado de seus produtos (e dos concorrentes) por rede, região e período, além de presença nas gôndolas.
- **Varejo:** precisa se comparar com o mercado (benchmark competitivo de preço).
- **Terceiros de dados:** consultorias, agências, pesquisa econômica e institutos que trabalham com inflação, comportamento de consumo e cesta de compras.
- **Consumidor:** quer saber se pagou caro, onde está mais barato e controlar seus gastos.

### 2.3 Por que agora / por que nós
A NFC-e é um padrão nacional consolidado (modelo 65), com estrutura de dados uniforme entre estados. O QR Code oferece uma fonte **estruturada** de extração — muito superior ao OCR de cupom de papel usado por soluções antigas. Isso reduz drasticamente o custo e o erro de coleta.

**Concorrência existente (contexto):** já existem apps que coletam preços por leitura de QR Code de nota fiscal de forma colaborativa (ex.: Economiza Club, Meus Preços). Eles validam a viabilidade da coleta, mas se posicionam como comparadores de preço para consumidor. **O diferencial do Quantah está no produto B2B de inteligência de mercado e no modelo de incentivo combinado (cashback + gamificação + sorteio)**, não em ser mais um comparador.

---

## 3. Visão do produto

> **Visão:** Ser a maior e mais atualizada base de inteligência de preços do varejo brasileiro, construída pela colaboração dos consumidores e monetizada por quem precisa entender o mercado — devolvendo valor a quem coleta.

### 3.1 Princípios de produto
1. **Fricção mínima na coleta.** Enviar um cupom precisa ser tão fácil quanto escanear e confirmar. Cada passo a mais derruba a taxa de contribuição.
2. **O dado é o produto.** Todas as decisões priorizam volume, qualidade e frescor da base.
3. **Privacidade por design.** O objetivo nunca é identificar o consumidor. Dado pessoal é anonimizado desde a coleta (ver Seção 7).
4. **Confiança em dois lados.** O consumidor confia que será pago e respeitado; o cliente B2B confia que o dado é real, representativo e legalmente obtido.

---

## 4. Personas e usuários

### 4.1 Lado da coleta (oferta de dados)
- **O Colaborador Casual:** envia cupons esporadicamente, atraído por cashback ou pela chance no sorteio. Volume individual baixo, mas é a maioria.
- **O Colaborador Engajado ("caçador"):** envia grande volume, motivado por gamificação, ranking e acúmulo de cashback. Pode inclusive juntar cupons de terceiros (ver 4.3).
- **O Consumidor-Beneficiário:** usa o app principalmente para o benefício próprio (comparar preços, controlar gastos) e contribui como consequência.

### 4.2 Lado do consumo (demanda de dados — clientes pagantes)
- **Analista de Trade/Insights na Indústria (CPG).**
- **Gestor de Pricing/Categoria no Varejo.**
- **Analista/Pesquisador em consultoria ou instituto de dados.**

### 4.3 Nota sobre cupons de terceiros
O envio de cupons de terceiros **não é um problema — é desejável**. Quanto mais cupons na base, melhor. O modelo de incentivo por volume é intencional. A única salvaguarda necessária é a **deduplicação** (ver 6.4), já que a chave de acesso de 44 dígitos é única por cupom e impede contagem em dobro.

---

## 5. Funcionalidades

### 5.1 MVP — coleta e incentivo (Estado de SP)

**Coleta de cupom (PWA)**
- Escaneamento do QR Code da NFC-e pelo app **ou** recebimento via "compartilhar" do leitor de QR nativo do celular.
- Extração automática dos dados do cupom (estabelecimento, itens, preços, códigos).
- Confirmação visual do cupom capturado antes de creditar.
- Feedback imediato de "cupom aceito / valor creditado".

**Carteira e cashback**
- Saldo do usuário em reais.
- Regra base: **R$ 1,00 a cada R$ 1.000,00 em cupons enviados** (0,1% do valor consolidado dos cupons válidos).
- Histórico de cupons enviados e créditos.
- Fluxo de resgate/saque do saldo. **[→ Fase técnica: integração de pagamento/PIX, KYC mínimo para saque]**

**Gamificação (sem premiação material — não exige autorização)**
- Pontos, níveis e badges por volume/frequência de envio.
- Ranking (semanal/mensal), com opção de anonimato/apelido.
- Desafios e metas ("envie X cupons esta semana").

**Sorteio (previsto no MVP, ativado por gate regulatório — ver Seção 8)**
- Mecânica: cupons enviados geram números da sorte.
- **Implementado tecnicamente desde já, mas com um "gate" de ativação** que só libera a funcionalidade ao público após a autorização SPA/MF sair. Enquanto não houver autorização, a feature fica desligada por configuração.
- Apuração vinculada à Loteria Federal (mecanismo aceito pela legislação).

**Benefício ao consumidor (gancho de engajamento)**
- "Você pagou mais caro/mais barato que a média" para itens do cupom enviado — usa a própria base para dar valor imediato de volta a quem coleta.

### 5.2 Evolução — produtos derivados do dado

**Para o consumidor**
- Comparador de preços geolocalizado ("onde está mais barato perto de mim").
- Controle de gastos / categorização automática de compras.
- Alertas de preço por produto.

**Para o B2B (produto de monetização — Quantah Intelligence)**
- Painel de preço praticado por produto, rede e região.
- Share of shelf / presença de marca por região.
- Market basket analysis (correlação de compras: quem compra X compra Y).
- Índices de inflação por categoria/região, mais granulares que índices oficiais.
- Detecção de ruptura/desabastecimento por região.
- Benchmark competitivo para o varejo.

> **[→ Fase técnica]** A modelagem dos produtos B2B (agregações, anonimização estatística, APIs de entrega, dashboards) será detalhada após validação do volume de coleta no MVP.

---

## 6. Mecânica de coleta e qualidade do dado

### 6.1 Fonte do dado
Cada NFC-e possui uma **chave de acesso de 44 dígitos** e um **QR Code** que aponta para a página pública da SEFAZ do estado emissor. A página exibe o cupom completo (DANFE NFC-e) com todos os itens.

A própria URL do QR já carrega a chave de acesso, que por si só contém informação estruturada: UF, ano/mês de emissão, CNPJ do emitente e modelo do documento. Parte da validação e da deduplicação pode ser feita só a partir da URL, sem nem acessar o portal.

### 6.2 Estratégia de extração (modelo híbrido)
- **MVP:** scraping do portal público da SEFAZ-SP. Rápido de implementar, zero burocracia, valida a tese.
- **Evolução:** migração para fonte oficial (web services / captura do XML da NFC-e / credenciamento), que é mais robusta e menos frágil.
- **Risco reconhecido:** o scraping é frágil — os portais mudam de layout e têm proteção anti-bot (captcha). Mitigações fazem parte do desenho (ver 6.3). **[→ Fase técnica: arquitetura de extração resiliente, fila de reprocessamento, tratamento de captcha, monitoramento de quebra de layout.]**

### 6.3 Arquitetura de adaptadores por estado
Embora o dado seja padronizado nacionalmente (modelo 65), **cada SEFAZ tem seu próprio portal** (HTML e proteções diferentes). O desenho prevê um **"adaptador" por estado**, com um **modelo de dados de destino único**. No MVP só existe o adaptador de SP; a expansão nacional é adicionar novos adaptadores sem mudar o núcleo. A cobertura de consulta pública de NFC-e é praticamente nacional (SP, RJ, MG, RS, PR, SC, PA, PE, CE, ES, AM, AL, SE, entre outros), o que dá caminho claro de expansão.
> **[→ Fase técnica]** Definir a interface do adaptador e o modelo canônico do cupom normalizado.

### 6.4 Qualidade e anti-fraude
- **Deduplicação por chave de acesso (44 dígitos):** impede que o mesmo cupom seja contado/pago duas vezes. É a principal (e suficiente) salvaguarda contra o incentivo por volume.
- **Validação de autenticidade:** o cupom é validado contra a SEFAZ (existe e é válido), evitando cupons forjados.
- **Reconciliação de produtos:** o código do item às vezes é o código de barras global (GTIN), mas frequentemente é um código próprio da loja. Para comparar o mesmo produto entre lojas, é necessário um trabalho de **normalização/matching** (por GTIN quando disponível; por similaridade de descrição quando não). Existe um **Cadastro Centralizado de GTINs (CCG)** mantido pelas SEFAZ (centralizado na SVRS) que pode apoiar essa reconciliação. **[→ Fase técnica: estratégia de matching de produtos e uso do CCG.]**

---

## 7. Privacidade e LGPD

- **O objetivo do produto não é identificar o consumidor.** Isso orienta todo o desenho.
- Quando o consumidor coloca **CPF na nota**, esse dado passa a ser dado pessoal. A estratégia é **anonimizar o CPF desde a coleta** — o CPF não é necessário para o valor do produto (que é o preço do item, não quem comprou).
- **Base legal e consentimento:** o app deve coletar consentimento explícito do usuário para o uso dos dados dos cupons, com política de privacidade clara.
- **Dados de saldo/saque:** o mínimo necessário de dado pessoal para pagamento e obrigações fiscais deve ser tratado com base legal apropriada e segregado da base analítica.
- **Entregáveis B2B são agregados/anonimizados:** o cliente compra inteligência de preço e mercado, nunca dado individual identificável.

> **[→ Fase técnica / Jurídico]** DPIA (relatório de impacto), fluxo de consentimento, política de retenção, anonimização e segregação de bases.

---

## 8. Mecanismos de incentivo e enquadramento legal

O Quantah usa **três mecanismos combinados**, com naturezas jurídicas diferentes. Entender a diferença é essencial porque só um deles exige autorização regulatória.

### 8.1 Cashback (R$ 1 / R$ 1.000) — **livre**
É **remuneração direta por serviço prestado** (o usuário entrega um dado, recebe por isso). **Não é sorteio nem promoção comercial.** Não depende de autorização. Entra no MVP sem trava regulatória.

### 8.2 Gamificação sem prêmio material — **livre**
Pontos, níveis, badges e ranking **não constituem distribuição de prêmio**. Não dependem de autorização. Entram no MVP livremente.
> Atenção: se, no futuro, a gamificação passar a **entregar prêmios materiais/dinheiro** vinculados ao desempenho, ela pode recair na regra de promoção comercial (8.3). Manter gamificação e premiação material conceitualmente separadas.

### 8.3 Sorteio vinculado ao envio de cupons — **exige autorização prévia**
Um sorteio em que **enviar cupons dá direito a concorrer** (mais cupons = mais chances) é, juridicamente, **"distribuição gratuita de prêmios a título de propaganda"** (Lei 5.768/71). O gatilho é a **vinculação**: o prêmio incentiva um ato comercialmente vantajoso para a empresa (alimentar a base). Isso **exige autorização prévia** da **Secretaria de Prêmios e Apostas (SPA/MF)** — antigo SECAP/SEAE — pelo sistema **SCPC**.
- Rodar sorteio sem autorização gera **multa de até 100% do valor dos prêmios**.
- Por isso o **gate de ativação técnico** (5.1) não é só burocracia: é trava de segurança do negócio.

**Nota sobre a exceção que não se aplica:** a lei dispensa autorização para concursos exclusivamente culturais/recreativos **desde que não vinculados à aquisição/uso de bens ou serviços**. Como nosso sorteio é justamente vinculado ao envio de cupons, **essa exceção não nos serve** — não vale forçá-la.

### 8.4 Estratégia de faseamento recomendada
- **Lançamento não depende da autorização.** Cashback + gamificação sobem primeiro e já começam a gerar volume de coleta.
- **Sorteio entra como alavanca de crescimento** quando a autorização sair. A burocracia SPA/SCPC corre **em paralelo**, sem ser caminho crítico do MVP.

---

## 9. Roteiro da burocracia SPA/SCPC (para o sorteio)

> O responsável de negócio possui **CNPJ ativo**. O caminho abaixo é o processo a ser tocado em paralelo ao desenvolvimento. Recomenda-se apoio de advogado/consultoria especializada em promoção comercial, sobretudo na redação do regulamento.

1. **Regularidade fiscal.** A autorização só é concedida a PJ quite com tributos federais, estaduais (SP) e municipais. Providenciar certidões negativas; resolver pendências antes de submeter.
2. **Definir mecânica e regulamento.** Como o cupom vira número da sorte, período, quantidade e valor dos prêmios, data/forma de apuração, regras para menor de idade, prazo de entrega, e o que ocorre se o ganhador não responder. O regulamento é o documento central.
3. **Vincular à Loteria Federal.** A apuração se dá com base no resultado da extração da Loteria Federal (mecanismo de transparência aceito pela lei). O regulamento define a correspondência número-da-sorte × resultado.
4. **Calcular a taxa de fiscalização.** Calculada sobre o **valor total dos prêmios** ofertados — quanto maior o prêmio, maior a taxa. Isso influencia a decisão de quanto premiar.
5. **Submeter no SCPC.** Processo 100% online (scpc.seae.fazenda.gov.br): regulamento, formulário oficial, comprovante de pagamento da taxa (via GRU) e documentação da empresa.
6. **Prazos.** Primeira resposta pode sair em até 2 dias; há **prazo mínimo de 8 dias** entre envio do pedido e início da promoção. Com o sistema atualizado, o processo total pode sair em menos de 10 dias, conforme a agilidade em reunir a documentação.
7. **Prestação de contas.** Ao fim da promoção, prestar contas no próprio SCPC, comprovando a entrega dos prêmios conforme o regulamento.

**Restrições de custo/risco a considerar no dimensionamento dos prêmios:**
- O valor total de prêmios distribuídos por mês **não pode exceder 5% da média mensal da receita operacional**. Para empresa nova, o cálculo é feito sobre o capital realizado — logo, o **teto de prêmio no início tende a ser baixo**.
- Sorteio sem autorização = multa de até 100% do valor dos prêmios (reforça o gate técnico).

---

## 10. Modelo de negócio (monetização)

- **Ativo central:** a base de preços em tempo quase real.
- **Custo de aquisição do dado:** cashback (0,1% do valor dos cupons) + custo de prêmios (quando o sorteio estiver ativo) + infraestrutura de coleta.
- **Receita:** produtos B2B de inteligência de preço/mercado (**Quantah Intelligence**) para os três públicos pagantes (indústria/CPG, varejo, terceiros de dados). O painel B2B é onde está a receita principal; o app de consumidor é o mecanismo de coleta.
- **Sequência recomendada:** provar coleta e volume no MVP → estruturar produto B2B → monetizar.

> **[→ Fase de negócio]** Precificação dos produtos B2B, modelo de contrato/assinatura, e definição de qual dos três públicos pagantes é o primeiro alvo comercial.

---

## 11. Marca Quantah

### 11.1 O nome
**Quantah** — marca inventada, derivada de "Quanta" (plural de *quantum*), com o "h" final para torná-la distintiva, registrável e própria.

**Por que funciona:**
- **Duplo sentido natural:** evoca "**quanto** custa" (preço) e "**quantidade** de dados" — exatamente o que a plataforma faz.
- **Sonoridade tech:** o radical *quantum* remete a precisão, medição e ciência de dados.
- **Curto e memorável:** duas sílabas, fácil de falar e escrever.
- **Marca inventada:** por não ser palavra comum, é mais fácil de proteger juridicamente e de ranquear em buscas do que "Quanta" puro (que colide com dezenas de empresas globais).
- **Neutra entre os dois lados:** serve ao app de consumidor e à plataforma B2B, sem soar infantil nem burocrática.

**Verificação de conflito (feita):** busca web ampla não encontrou nenhuma empresa chamada "Quantah" em nenhum setor, no Brasil ou no exterior. Existem nomes parecidos mas distintos no espaço de software genérico (Quantasoft, Quanta Software Solutions, Quantyca, Quantix), nenhum concorrente direto em dados de preço e nenhum homônimo. Sinal verde preliminar.

**Verificação pendente (obrigatória antes de fixar):**
1. **Domínio (Registro.br):** confirmar `quantah.com.br` e, se possível, `quantah.com`.
2. **Marca (INPI):** buscar "Quantah" e semelhantes (colidência gráfica/fonética, ex. "Quanta") nas classes **42** (software, SaaS, plataforma tecnológica) e **35** (dados/informações comerciais de negócios, publicidade).

### 11.2 Tagline
- **"Cada nota conta."** — duplo sentido (a nota fiscal + "conta/importa"); recomendada para o app de consumidor.
- **"Do cupom ao insight."** — resume a jornada do dado; recomendada para o material B2B.
- Outras opções: "O preço, medido." / "Inteligência de preço, feita por todos."

### 11.3 Arquitetura de marca (app + B2B)
Marca-mãe única (Quantah) com dois nomes de produto, para concentrar reconhecimento e reduzir custo de marca no início.

| Face | Público | Nome sugerido | Tom |
|---|---|---|---|
| App de coleta | Consumidor | **Quantah** (app principal) | Amigável, simples, recompensador |
| Plataforma de dados | Indústria/CPG, varejo, terceiros | **Quantah Intelligence** | Sério, credível, analítico |

### 11.4 Direção de identidade visual (briefing para UX/UI)
- **Personalidade:** preciso, confiável, moderno, brasileiro sem ser caricato. Equilíbrio entre "app de recompensa simpático" e "plataforma de dados séria".
- **Conceito visual:** unir *medição* e *dado* — pontos que formam padrões, uma unidade que se multiplica (o cupom individual virando base coletiva), precisão/grid.
- **Evitar:** cara de "app de cupom de desconto" (categoria lotada, baixo valor percebido) e cara de "banco/fintech" genérico.

### 11.5 Passo a passo de registro (em paralelo)
**Domínio:** acessar `registro.br`, consultar `quantah.com.br`, registrar se livre (~R$40/ano) antes de divulgar o nome. Opcional: `quantah.com` internacional.
**Marca (INPI):** acessar `busca.inpi.gov.br`, buscar "Quantah" e variações nas classes 42 e 35; se livre, depositar (recomenda-se agente de PI). A data de depósito garante prioridade — depositar cedo.
**Redes:** reservar @quantah (ou @quantahapp) no mesmo dia em que travar o nome.

> **Recomendação:** travar em Quantah condicionado às duas verificações oficiais. Se `.com.br` livre e sem marca impeditiva nas classes 42/35, registrar domínio e depositar marca **na mesma semana**, antes de qualquer divulgação pública.

---

## 12. Decisões já tomadas (para o time não reabrir)

| Tema | Decisão |
|---|---|
| Nome/marca | Quantah (pendente verificação INPI + Registro.br) |
| Plataforma | PWA (por enquanto) |
| Modelo de coleta | App: escaneamento de QR Code / compartilhamento da URL |
| Extração de dados | Híbrido: scraping no MVP → fonte oficial depois |
| Cobertura do MVP | Apenas Estado de São Paulo (1 adaptador) |
| Clientes pagantes | Os três públicos (indústria/CPG, varejo, terceiros de dados) |
| Incentivo | Cashback R$1/R$1.000 + gamificação + sorteio |
| Sorteio | Previsto e implementado desde já, com gate de ativação regulatório; burocracia SPA em paralelo |
| Cupons de terceiros | Permitidos e desejáveis (quanto mais, melhor) |
| Anti-fraude | Deduplicação por chave de acesso + validação SEFAZ |
| Privacidade | CPF anonimizado na coleta; não se identifica o consumidor |

---

## 13. Riscos e mitigações

| Risco | Mitigação |
|---|---|
| Fragilidade do scraping (mudança de layout, captcha) | Adaptadores isolados por estado, fila de reprocessamento, monitoramento, migração para fonte oficial |
| Reação do varejo (dado de preço é sensível) | Entregáveis agregados/anonimizados; preços são de domínio público (CDC, Lei 10.962/2004) |
| Incentivo por volume estimular fraude | Deduplicação por chave de acesso única + validação SEFAZ |
| LGPD (CPF na nota) | Anonimização na coleta, consentimento explícito, segregação de bases |
| Sorteio sem autorização (multa) | Gate de ativação técnico + processo SPA/SCPC em paralelo |
| Teto de prêmio baixo no início (regra dos 5%) | Começar com prêmios modestos; priorizar cashback/gamificação como incentivo principal no início |
| Baixa adesão de coleta | Benefício imediato ao consumidor ("você pagou mais caro que a média") + gamificação |
| Concorrentes já existentes (comparadores) | Diferenciação pelo produto B2B e incentivo combinado, não competir como "mais um comparador" |
| Conflito de marca | Verificação INPI + Registro.br antes de divulgar; grafia "Quantah" (com H) distintiva |

---

## 14. Próximos passos sugeridos

1. **Discovery de produto (PO + UX/UI):** desenhar o fluxo de coleta (scan → confirmação → crédito) com fricção mínima e as telas de carteira, gamificação e sorteio (com estado "em breve" antes do gate). Aplicar identidade visual do Quantah (Seção 11.4).
2. **Discovery técnico (Arquitetura):** aprofundar os pontos marcados **[→ Fase técnica]** — modelo canônico do cupom, adaptador de extração de SP, deduplicação, anti-fraude, integração de pagamento.
3. **Trilha jurídica (paralela):** iniciar regularidade fiscal e redação do regulamento do sorteio; abrir processo no SCPC. Verificar e registrar marca Quantah (INPI classes 42/35) e domínio.
4. **Definição comercial (paralela):** escolher o primeiro público B2B a atacar e esboçar o produto de dados (Quantah Intelligence) correspondente.

---

*Documento de visão unificado — escopo de produto, negócio e marca. Arquitetura detalhada, schema de dados e cronograma serão elaborados em documentos subsequentes. Verificação legal de marca e identidade visual final a cargo dos times de Jurídico e UX/UI.*
