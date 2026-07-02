// PoC DESCARTÁVEL — STORY-000 (spike). NÃO é código de produção.
// Dada a URL do QR de uma NFC-e de SP, abre o portal público da SEFAZ-SP num
// browser headless (o portal é ASP.NET WebForms e renderiza a DANFE via
// __doPostBack no load), e extrai: chave de acesso (44 díg.), estabelecimento
// (razão social + CNPJ) e os itens (descrição, qtd, valor).
//
// Uso:  node extrair.mjs "<URL_DO_QR>"
import { chromium } from 'playwright';
import fs from 'node:fs';

const url = process.argv[2];
if (!url) { console.error('uso: node extrair.mjs "<URL_DO_QR>"'); process.exit(2); }

const OUT = './evidencia';
fs.mkdirSync(OUT, { recursive: true });

const chaveDaUrl = (() => {
  const m = url.match(/chNFe=(\d{44})/) || url.match(/[?&]p=(\d{44})/);
  return m ? m[1] : null;
})();

const browser = await chromium.launch({ headless: true });
const ctx = await browser.newContext({
  userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125 Safari/537.36',
  locale: 'pt-BR',
});
const page = await ctx.newPage();

const result = { url, chaveDaUrl, ok: false, erroPortal: null, estabelecimento: null, cnpj: null, chave: null, total: null, itens: [] };

try {
  await page.goto(url, { waitUntil: 'networkidle', timeout: 45000 });

  // O portal pode exibir um diálogo "Deseja prosseguir?" (botão PROSSEGUIR).
  // Tentamos disparar/confirmar sem quebrar se não existir.
  for (const sel of ['#btnConfirmar', 'input[value*="Prosseguir" i]', 'a:has-text("Prosseguir")', '#btnProsseguir']) {
    const el = await page.$(sel);
    if (el) { await el.click().catch(()=>{}); await page.waitForLoadState('networkidle',{timeout:20000}).catch(()=>{}); break; }
  }

  // dá tempo do postback do WebForms popular a DANFE
  await page.waitForTimeout(2500);

  // erro visível do portal (QR inválido, chave inexistente, etc.)
  result.erroPortal = await page.$eval('#spnErroMaster, #divErroMaster', e => e.textContent.trim()).catch(() => null);
  if (result.erroPortal && !result.erroPortal.length) result.erroPortal = null;

  // ---- extração estruturada (markup padrão do portal NFC-e) ----
  const dados = await page.evaluate(() => {
    const T = (el) => (el ? el.textContent.replace(/\s+/g,' ').trim() : null);
    const clean = (el, label) => { let t = T(el); if (t && label) t = t.replace(label, '').trim(); return t || null; };
    const q = (s) => document.querySelector(s);
    const qa = (s) => Array.from(document.querySelectorAll(s));

    const estab = T(q('#u20 .txtTopo')) || T(q('.txtTopo'));
    // CNPJ costuma vir num <div class="text"> logo abaixo do emitente
    let cnpj = null;
    for (const d of qa('#u20 .text, .txtTopo ~ *, #conteudo .text')) {
      const m = (d.textContent||'').match(/CNPJ[:\s]*([\d./-]{14,18})/i);
      if (m) { cnpj = m[1]; break; }
    }
    const chave = (T(q('.chave')) || '').replace(/\D/g,'') || null;

    // itens: tabela id="tabResult"
    const itens = qa('#tabResult tr').map(tr => {
      const desc = T(tr.querySelector('.txtTit, .txtTit2'));
      const qtd  = clean(tr.querySelector('.Rqtd'),  /Qtde\.:/);
      const un   = clean(tr.querySelector('.RUN'),   /UN:\s*/);
      const vun  = clean(tr.querySelector('.RvlUnit'), /Vl\. Unit\.:/);
      const vtot = T(tr.querySelector('.valor'));
      const cod  = T(tr.querySelector('.RCod'));
      return { descricao: desc, codigo: (cod ? cod.replace(/\D+/g,'') : null), qtd, unidade: un, valorUnit: vun, valorTotal: vtot };
    }).filter(i => i.descricao);

    // total "Valor a pagar R$" = .totalNumb.txtMax
    const total = T(q('.totalNumb.txtMax')) || null;
    // nº de itens (rótulo "Qtd. total de Itens")
    const qtdItens = (() => { for (const el of qa('.totalNumb')) { const t=T(el); if (/^\d+$/.test(t)) return t; } return null; })();

    return { estab, cnpj, chave, itens, total, qtdItens, textoPagina: document.body.innerText.slice(0, 4000) };
  });

  result.estabelecimento = dados.estab;
  result.cnpj = dados.cnpj;
  result.chave = dados.chave || chaveDaUrl;
  result.itens = dados.itens;
  result.total = dados.total;
  result.qtdItens = dados.qtdItens;

  // fallback por regex se a extração estruturada não achou itens
  if (!result.itens.length) {
    const txt = dados.textoPagina || '';
    if (!result.cnpj) { const m = txt.match(/CNPJ[:\s]*([\d./-]{14,18})/i); if (m) result.cnpj = m[1]; }
    result._textoAmostra = txt.slice(0, 800);
  }

  result.ok = !!(result.chave && (result.itens.length > 0 || result.estabelecimento));

  await page.screenshot({ path: `${OUT}/danfe.png`, fullPage: true }).catch(()=>{});
  fs.writeFileSync(`${OUT}/danfe.html`, await page.content());
} catch (e) {
  result.excecao = String(e && e.message || e);
} finally {
  fs.writeFileSync(`${OUT}/resultado.json`, JSON.stringify(result, null, 2));
  await browser.close();
}

console.log(JSON.stringify(result, null, 2));
