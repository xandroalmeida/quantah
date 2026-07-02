# app/ — o código do Quantah virá aqui

Esta pasta receberá o esqueleto do app: **Laravel + Inertia/React + PostgreSQL**.

O scaffold **não** foi criado no Cowork porque exige PHP/Composer, que não existem no sandbox do chat
(é o esperado — ver `AGENTS.md` na raiz). Ele é montado no **Claude Code**, na sua máquina, com um
comando só:

> Abra esta pasta no Claude Code e diga: **"prepare o ambiente e suba o preview"**.

A sub-skill `docs/skills/setup-ambiente/` instala o toolchain, roda `composer create-project`, instala
Inertia + React (Breeze), configura o Postgres (dev + banco de teste separado), liga o hot reload do
Vite e sobe o preview em http://localhost:8000. Depois disso, esta pasta terá o app Laravel completo e
este arquivo pode ser removido.
