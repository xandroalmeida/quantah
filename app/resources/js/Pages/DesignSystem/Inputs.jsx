import { Head } from '@inertiajs/react';
import { useState } from 'react';
import TextField from '@/Components/inputs/TextField';
import MaskedField from '@/Components/inputs/MaskedField';
import DateTimeField from '@/Components/inputs/DateTimeField';
import SelectField from '@/Components/inputs/SelectField';
import Checkbox from '@/Components/inputs/Checkbox';
import Radio from '@/Components/inputs/Radio';
import Switch from '@/Components/inputs/Switch';

/**
 * Vitrine dos inputs do DS (STORY-005 / CA-5). Base plugável da kitchen sink completa
 * (STORY-006). Segue o ritmo de superfície do DS (página sage → clusters em card
 * branco). Os displays de "valor canônico" provam a regra de ouro da stack: masked
 * guarda o valor **unmasked**, datetime guarda **ISO 8601**.
 */
export default function Inputs() {
    const [nfceKey, setNfceKey] = useState('');
    const [date, setDate] = useState('');
    const [uf, setUf] = useState('SP');
    const [terms, setTerms] = useState(false);
    const [channel, setChannel] = useState('app');
    const [notify, setNotify] = useState(true);

    return (
        <>
            <Head title="DS — Inputs" />
            <main className="min-h-screen bg-canvas-soft px-lg py-3xl text-ink">
                <div className="mx-auto flex max-w-2xl flex-col gap-2xl">
                    <header className="flex flex-col gap-sm">
                        <h1
                            data-testid="showcase-title"
                            className="font-display text-display-md font-black"
                        >
                            Inputs do Design System
                        </h1>
                        <p className="text-body-md text-body">
                            Campos de formulário com label, hint, estados e erro acessível — tudo
                            por token. Máscara guarda o valor sem máscara; data guarda ISO 8601.
                        </p>
                    </header>

                    {/* Text / masked / datetime / select — chrome de caixa */}
                    <section className="flex flex-col gap-lg">
                        <h2 className="text-display-xs">Texto, máscara, data e seleção</h2>
                        <div className="flex flex-col gap-xl rounded-xl bg-canvas p-xl shadow-elev-2">
                            <TextField
                                label="Nome completo"
                                hint="Como aparece no seu documento."
                                hintTestId="text-hint"
                                data-testid="text-input"
                                autoComplete="off"
                            />

                            <TextField
                                label="E-mail"
                                error="Informe um e-mail válido."
                                errorTestId="text-error-msg"
                                data-testid="text-error-input"
                                defaultValue="quantah@"
                            />

                            <TextField
                                label="Campo desabilitado"
                                hint="Não editável neste estado."
                                data-testid="text-disabled-input"
                                disabled
                            />

                            <div>
                                <MaskedField
                                    label="Chave de acesso da NFC-e"
                                    hint="44 dígitos. A máscara é só ajuda visual."
                                    value={nfceKey}
                                    onAccept={setNfceKey}
                                    data-testid="masked-input"
                                />
                                <p className="mt-xs text-body-sm text-body">
                                    Valor canônico (unmasked):{' '}
                                    <span data-testid="masked-value" className="font-semibold text-ink">
                                        {nfceKey}
                                    </span>
                                </p>
                            </div>

                            <div>
                                <DateTimeField
                                    label="Data da compra"
                                    hint="Escolha no calendário."
                                    value={date}
                                    onChange={setDate}
                                    data-testid="datetime-input"
                                />
                                <p className="mt-xs text-body-sm text-body">
                                    Valor canônico (ISO 8601):{' '}
                                    <span data-testid="datetime-value" className="font-semibold text-ink">
                                        {date}
                                    </span>
                                </p>
                            </div>

                            <SelectField
                                label="UF"
                                hint="Estado do estabelecimento."
                                value={uf}
                                onChange={(event) => setUf(event.target.value)}
                                data-testid="select-input"
                            >
                                <option value="SP">São Paulo</option>
                                <option value="RJ">Rio de Janeiro</option>
                                <option value="MG">Minas Gerais</option>
                            </SelectField>
                        </div>
                    </section>

                    {/* Escolha — checkbox / radio / switch */}
                    <section className="flex flex-col gap-lg">
                        <h2 className="text-display-xs">Escolha</h2>
                        <div className="flex flex-col gap-lg rounded-xl bg-canvas p-xl shadow-elev-2">
                            <Checkbox
                                label="Aceito os termos de uso"
                                fieldTestId="checkbox-field"
                                data-testid="checkbox-input"
                                checked={terms}
                                onChange={(event) => setTerms(event.target.checked)}
                            />

                            <fieldset className="flex flex-col gap-sm">
                                <legend className="text-body-sm font-semibold text-ink">
                                    Canal preferido
                                </legend>
                                <Radio
                                    label="Aplicativo"
                                    name="channel"
                                    value="app"
                                    fieldTestId="radio-field-a"
                                    data-testid="radio-a"
                                    checked={channel === 'app'}
                                    onChange={(event) => setChannel(event.target.value)}
                                />
                                <Radio
                                    label="E-mail"
                                    name="channel"
                                    value="email"
                                    fieldTestId="radio-field-b"
                                    data-testid="radio-b"
                                    checked={channel === 'email'}
                                    onChange={(event) => setChannel(event.target.value)}
                                />
                            </fieldset>

                            <Switch
                                label="Receber avisos de cashback"
                                fieldTestId="switch-field"
                                data-testid="switch-input"
                                checked={notify}
                                onChange={setNotify}
                            />
                        </div>
                    </section>
                </div>
            </main>
        </>
    );
}
