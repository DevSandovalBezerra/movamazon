/**
 * Script de correção em lote: mojibake -> UTF-8 em arquivos .js e .php do frontend.
 * Uso: node scripts/fix_utf8_batch.js [--dry-run] [--phase=N]
 * --phase=1 a 5: só processa arquivos da fase N. Sem --phase: processa todas.
 * --dry-run: só reporta, não grava.
 */

const fs = require('fs');
const path = require('path');

const DRY_RUN = process.argv.includes('--dry-run');
const PHASE_ARG = process.argv.find(a => a.startsWith('--phase='));
const PHASE = PHASE_ARG ? parseInt(PHASE_ARG.split('=')[1], 10) : 0;

const ROOT = path.join(__dirname, '..');
const FRONTEND = path.join(ROOT, 'frontend');

// Prefixo longo (UTF-8 lido como Latin-1 e re-codificado) - code points do arquivo
const P = '\u00c3\u0192\u00c6\u2019\u00c3\u2020\u00e2\u20ac\u2122\u00c3\u0192\u00e2\u20ac\u0161\u00c3\u201a\u00c2';

const LONG_MAP = [
  [P + '\u00a3', '\u00e3'],   // ã
  [P + '\u00a7', '\u00e7'],   // ç
  [P + '\u00a1', '\u00e1'],   // á
  [P + '\u00ad', '\u00ed'],   // í
  [P + '\u00b3', '\u00f3'],   // ó
  [P + '\u00a2', '\u00e2'],   // â
  [P + '\u00aa', '\u00aa'],   // ª
  [P + '\u00a9', '\u00e9'],   // é
  [P + '\u00ba', '\u00ba'],   // º
  [P + '\u00b4', '\u00f4'],   // ô
];

// Padrões curtos (2–3 caracteres) - ordem: mais longos primeiro
const SHORT_MAP = [
  ['Ã§Ã£o', 'ção'],
  ['Ã£o', 'ão'],
  ['Ã­vel', 'ível'],
  ['Ã¡vel', 'ável'],
  ['Ã§Ã£', 'çã'],
  ['Ãª', 'ê'],
  ['Ã©', 'é'],
  ['Ãº', 'ú'],
  ['Ãµ', 'õ'],
  ['Ã³', 'ó'],
  ['Ã¡', 'á'],
  ['Ã­', 'í'],
  ['Ã¢', 'â'],
  ['Ã£', 'ã'],
  ['Ã§', 'ç'],
  ['Ã‡', 'Ç'],
];

function listFilesPhase(phase) {
  const list = [];
  function add(dir, base = '') {
    if (!fs.existsSync(dir)) return;
    const entries = fs.readdirSync(dir, { withFileTypes: true });
    for (const e of entries) {
      const rel = path.join(base, e.name);
      const full = path.join(dir, e.name);
      if (e.isDirectory()) {
        if (e.name === 'node_modules' || e.name === 'inscricao_EXEMPLO') continue;
        add(full, rel);
      } else if (e.isFile() && (e.name.endsWith('.js') || e.name.endsWith('.php')) && !e.name.endsWith('.min.js')) {
        list.push(path.relative(ROOT, full).replace(/\\/g, '/'));
      }
    }
  }

  if (phase === 1) {
    const phase1Dirs = [
      path.join(FRONTEND, 'paginas', 'inscricao'),
      path.join(FRONTEND, 'paginas', 'inscricao', 'includes'),
      path.join(FRONTEND, 'paginas', 'participante'),
      path.join(FRONTEND, 'js', 'inscricao'),
      path.join(FRONTEND, 'js', 'participante'),
    ];
    for (const d of phase1Dirs) {
      add(d, path.relative(ROOT, d));
    }
    // Participante: só arquivos de pagamento e os listados no plano
    const allowed = new Set([
      'frontend/paginas/participante/pagamento-sucesso.php',
      'frontend/paginas/participante/pagamento-erro.php',
      'frontend/paginas/participante/pagamento-pendente.php',
      'frontend/paginas/participante/pagamento-inscricao.php',
    ]);
    return list.filter(f => f.startsWith('frontend/paginas/inscricao') || f.startsWith('frontend/js/inscricao') || allowed.has(f) || (f.startsWith('frontend/js/participante') && f.includes('pagamento')));
  }

  if (phase === 2) {
    add(path.join(FRONTEND, 'paginas', 'participante'), 'frontend/paginas/participante');
    add(path.join(FRONTEND, 'paginas', 'auth'), 'frontend/paginas/auth');
    add(path.join(FRONTEND, 'js', 'participante'), 'frontend/js/participante');
    const authJs = [path.join(FRONTEND, 'js', 'auth-register.js'), path.join(FRONTEND, 'js', 'auth-handler.js')];
    authJs.forEach(f => { if (fs.existsSync(f)) list.push(path.relative(ROOT, f).replace(/\\/g, '/')); });
    return [...new Set(list)].filter(f => !listFilesPhase(1).includes(f));
  }

  if (phase === 3) {
    add(path.join(FRONTEND, 'paginas', 'admin'));
    add(path.join(FRONTEND, 'paginas', 'organizador'));
    add(path.join(FRONTEND, 'paginas', 'public'));
    add(path.join(FRONTEND, 'js', 'admin'));
    add(path.join(FRONTEND, 'js', 'organizador'));
    add(path.join(FRONTEND, 'js', 'public'));
    ['eventos.js', 'programacao.js', 'organizador-eventos.js', 'organizador-criar-evento.js'].forEach(name => {
      const f = path.join(FRONTEND, 'js', name);
      if (fs.existsSync(f)) list.push(path.relative(ROOT, f).replace(/\\/g, '/'));
    });
    return [...new Set(list)];
  }

  if (phase === 4) {
    add(path.join(FRONTEND, 'includes'));
    return list;
  }

  if (phase === 5) {
    add(path.join(FRONTEND, 'js', 'utils'));
    add(path.join(FRONTEND, 'js', 'api'));
    add(path.join(FRONTEND, 'js', 'components'));
    add(path.join(FRONTEND, 'js'));
    return list.filter(f => f.endsWith('.js') && (f.includes('utils') || f.includes('api') || f.includes('components') || (!f.includes('inscricao') && !f.includes('participante') && !f.includes('admin') && !f.includes('organizador') && !f.includes('public'))));
  }

  // all
  add(path.join(FRONTEND, 'paginas'));
  add(path.join(FRONTEND, 'includes'));
  add(path.join(FRONTEND, 'js'));
  return list.filter(f => !f.includes('inscricao_EXEMPLO') && !f.includes('node_modules') && !f.endsWith('.min.js'));
}

function getFiles() {
  if (PHASE >= 1 && PHASE <= 5) {
    return listFilesPhase(PHASE);
  }
  return listFilesPhase(0);
}

function applyReplacements(content) {
  let out = content;
  let total = 0;
  for (const [from, to] of LONG_MAP) {
    const parts = out.split(from);
    const n = parts.length - 1;
    if (n > 0) {
      out = parts.join(to);
      total += n;
    }
  }
  for (const [from, to] of SHORT_MAP) {
    const parts = out.split(from);
    const n = parts.length - 1;
    if (n > 0) {
      out = parts.join(to);
      total += n;
    }
  }
  return { content: out, count: total };
}

function main() {
  const files = getFiles();
  if (process.env.DEBUG) console.error('Files:', files.length, files.slice(0, 5));
  const log = [];
  let totalFiles = 0;
  let totalReplacements = 0;

  for (const rel of files) {
    const full = path.join(ROOT, rel);
    if (!fs.existsSync(full)) continue;
    let content;
    try {
      content = fs.readFileSync(full, 'utf8');
    } catch (e) {
      log.push(`${rel}: erro leitura ${e.message}`);
      continue;
    }
    const { content: newContent, count } = applyReplacements(content);
    if (count > 0) {
      totalFiles++;
      totalReplacements += count;
      log.push(`${rel}: ${count} substituições`);
      if (!DRY_RUN) {
        fs.writeFileSync(full, newContent, { encoding: 'utf8' });
      }
    }
  }

  console.log(DRY_RUN ? '[DRY-RUN] ' : '');
  console.log(`Arquivos alterados: ${totalFiles}`);
  console.log(`Total de substituições: ${totalReplacements}`);
  log.forEach(l => console.log(l));
}

main();
