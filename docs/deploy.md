# Deploy — Quantah

Como disparar deploys e como versionar. **O deploy não é mais automático no push da `main`** — ele é
disparado por **tag** de git.

## Fluxos

| Ambiente         | Formato da tag              | Exemplo         | Status          |
| ---------------- | --------------------------- | --------------- | --------------- |
| **Homologação**  | `vMAJOR.MINOR.PATCH-rc-N`   | `v0.1.1-rc-0`   | ✅ ativo         |
| **Produção**     | `vMAJOR.MINOR.PATCH`        | `v0.1.1`        | 🚧 ainda não    |

Formato clássico **`major.minor.patch`**. O `-rc-N` (release candidate) marca a build de homologação da
versão que está sendo preparada; a mesma versão sem `-rc-N` é a promoção para produção.

Push na `main` continua rodando **testes + build + E2E** (CI). O que ele **não** faz mais é deploy.

## Regras de versão

- **MAJOR** e **MINOR**: só incrementam **quando o Alexandro pedir explicitamente**. Nunca por conta própria.
- **PATCH**: incrementa +1 para começar o ciclo de uma nova versão (sobre a última versão de produção).
- **N (rc)**: começa em `0` numa versão nova; +1 a cada nova build de homologação da **mesma** versão
  (correções em cima do mesmo `MAJOR.MINOR.PATCH`).

Ou seja, o caminho normal de trabalho mexe só no **rc** (e no **patch** ao abrir um ciclo). Subir minor ou
major é sempre um pedido consciente.

## Disparar deploy de homologação

1. Veja a última tag:

   ```bash
   git tag --sort=-v:refname | head
   ```

2. Escolha a próxima tag seguindo as regras acima. Exemplos:
   - Iterando a mesma versão em homolog: `...-rc-0` → `...-rc-1` → `...-rc-2`
   - Abrindo o ciclo de uma nova versão (sobre a última de prod `v0.1.0`): `v0.1.1-rc-0`

3. Crie e envie a tag (isso dispara o workflow):

   ```bash
   git tag v0.1.1-rc-0
   git push origin v0.1.1-rc-0
   ```

O GitHub Actions roda testes + E2E e, com tudo verde, faz o deploy para
`https://quantah-homolog.<host>.sslip.io`.

## Disparar deploy de produção

Ainda não implementado. Quando houver infra de produção, a promoção será: pegar o `rc` aprovado e criar a
tag **sem** sufixo (`v0.1.1`), habilitando o gatilho de produção no workflow.

## Onde isso está configurado

`.github/workflows/ci-cd.yml` — o gatilho por tag está em `on.push.tags` e a condição do job `deploy`.
