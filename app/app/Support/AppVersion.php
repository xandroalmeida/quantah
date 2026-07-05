<?php

namespace App\Support;

/**
 * Versão do app para exibição e auto-atualização (PWA).
 *
 * - `label()`: rótulo humano (tag), de `config('app.version')` — APP_VERSION → arquivo
 *   VERSION → 'dev'. É o que aparece no carimbo de versão.
 * - `asset()`: assinatura do bundle buildado (hash do `build/manifest.json`). Muda a cada
 *   build/deploy — é o sinal canônico de "o servidor atualizou". O cliente compara a sua
 *   assinatura de boot com a do endpoint `/version` e recarrega quando diverge.
 *
 * O runtime não depende de `.git` (ausente no container Sail/imagem).
 */
final class AppVersion
{
    /** Rótulo humano da versão (tag). */
    public static function label(): string
    {
        return (string) config('app.version', 'dev');
    }

    /** Assinatura do bundle atual (hash curto do manifest do Vite); 'dev' sem build. */
    public static function asset(): string
    {
        $manifest = public_path('build/manifest.json');

        return is_file($manifest) ? substr((string) md5_file($manifest), 0, 12) : 'dev';
    }
}
